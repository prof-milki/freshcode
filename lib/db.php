<?php
/**
 * title: database
 * description: basic db() interface for parameterized SQL and result folding
 * api: php
 * type: database
 * version: 0.9.1
 * depends: pdo
 * license: Public Domain
 * author: Mario Salzer
 * doc: http://fossil.include-once.org/hybrid7/wiki/db
 *
 *
 * QUERY
 *
 * Provides simple database queries with enumerated / named parameters. It's
 * flexible in accepting plain PDO scalar arguments or arrays. Array args get
 * merged, or transcribed when special placeholders are present:
 *
 *   $r = db("SELECT * FROM tbl WHERE a>=? AND b IN (??)", $a, array($b, $c));
 *
 * Extended placeholder syntax:
 *
 *      ??    Interpolation of indexed arrays, useful for IN clauses.
 *      ::    Turns associative arrays into a :named, :value, :list.
 *      :?    Interpolates key names (doesn't add values).
 *      :&    Becomes a name=:value list, joined by AND; for WHERE clauses.
 *      :|    Becomes a name=:value list, joined by OR; for WHERE clauses.
 *      :,    Becomes a name=:value list, joined by , commas; for UPDATEs.
 *
 * Configurable {TOKENS} from db()->tokens[] are also substituted..
 *
 *
 * RESULT
 *
 * The returned result can be accessed as single data row, when fetching just
 * one:
 *       $result->column
 *       $result["column"]
 *
 * Or just traversed row-wise normally by iterationg with
 *
 *       foreach (db("...") as $row)
 *
 * Alternatively by object-wrapping (unlike plain PDO->fetchObject() this
 * hydrates the object using its normal constructor) the result set with:
 *
 *       foreach ($result->into("ArrayObject") as $row)
 *
 * And all PDO ->fetch() methods are still available for use on the result obj.
 *
 *
 * CONNECT  
 *
 * The db() interface binds the global "$db" variable. It ought to be
 * initialized with:
 *
 *       db(new PDO(...));
 * 
 *
 * RECORD WRAPPER
 *
 * There's also a simple table data gateway wrapper implemented here. It
 * accepts db() queries for single entries, and allows ->save()ing back, or
 * to ->delete() records.
 * You should only use it in conjuction with sql2php and its simpler wrappers.
 *
 */



/**
 * Hybrid instantiation / query function.
 * Couples `$db` in the shared/global scope.
 *
 */
function db($sql=NULL, $params=NULL) {

    #-- shared PDO handle
    $db = & $GLOBALS["db"];
    
    #-- open database
    if (is_object($sql)) {
    
        // use passed param
        $db = new db_wrap($sql);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, is_int(stripos($db->getAttribute(PDO::ATTR_DRIVER_NAME), "mysql")));
        $db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
        $db->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
    
        // save settings
        $db->tokens = array("PREFIX"=>""); // or reference global $config
        $db->in_clause = $db->getAttribute(PDO::ATTR_DRIVER_NAME) == "sqlite"
                     and $db->getAttribute(PDO::ATTR_CLIENT_VERSION) < 3.6;
    }
    
    #-- return PDO handle
    elseif (empty($sql)) {
        return $db;
    }
    
    #-- just dispatch to the wrapper
    else {
        $args = array_slice(func_get_args(), 1);
        return $db($sql, $args);
    }

}


/**
 * Binds PDO handle, allows original calls and extended placeholder use.
 *
 */
class db_wrap {


    function __construct($pdo) {
        $this->pdo = $pdo;
    }

    function __call($func, $args) {
        return call_user_func_array(array($this->pdo, $func), $args);
    }

    /**
     * Handles extended placeholders and parameter unpacking.
     *
     */
    function __invoke($sql, $args=array()) {

        #-- reject SQL
        if (strpos($sql, "'")) {
            trigger_error("SQL query contained raw data. DO NOT WANT", E_USER_WARNING);
            return NULL;
        }
        
        #-- get $params
        $params2 = array();

        #-- flattening sub-arrays (works for ? enumarated and :named params)
        foreach ($args as $i=>$a) {
            if (is_array($a)) {
                $enum = is_int(end(array_keys($a)));

                // subarray corresponds to special syntax placeholder?
                if (preg_match("/\?\?|:\?|::|:&|:,|&\|/", $sql, $uu, PREG_OFFSET_CAPTURE)) {
                    list($token, $pos) = $uu[0];
                    switch ($token) {

                        case "??":  // replace ?? array placeholders
                            $replace = implode(",", array_fill(0, count($a), "?"));
                            break;

                        case ":?":  // and :? name placeholder, transforms list into enumerated params
                            $replace = implode(",", $this->db_identifier($enum ? $a : array_keys($a), "`"));
                            $enum = 1;  $a = array();   // do not actually add values
                            break;

                        case "::":  // inject :named,:value,:list
                            $replace = ":" . implode(",:", db_identifier(array_keys($a)) );
                            break;

                        case ":&":  // associative params - becomes "key=:key AND .."
                        case ":,":  // COMMA-separated
                        case ":|":  // OR-separated
                            $fill = array(":&"=>" AND ", ":,"=>" , ", ":|"=>" OR ");
                            $replace = array();
                            foreach ($this->db_identifier(array_keys($a)) as $key) {
                                $replace[] = "`$key`=:$key";
                            }
                            $replace = implode($fill[$token], $replace);

                    }
                    // update SQL string
                    $sql = substr($sql, 0, $pos) . $replace . substr($sql, $pos + strlen($token));
                }

                // unfold
                if ($enum) {
                   $params2 = array_merge($params2, $a);
                } else {
                   $params2 = array_merge($params2, $a);
                }
            }
            else {
                $params2[] = $a;
            }
        }

        #-- placeholders
        if (empty(!$this->tokens) && strpos($sql, "{")) {
            $sql = preg_replace_callback("/\{(\w+)(.*?)\}/e", function($m) use ($db) {
                return isset($this->token["$m[1]"]) ? $this->token["$m[1]"]."$m[2]" : $this->token["$m[1]$m[2]"];
            }, $sql);
        }
        
        #-- SQL incompliance workarounds
        if (!empty($this->in_clause) && strpos($sql, " IN (")) { // only for ?,?,?,? enum params
            $sql = preg_replace_calback("/(\S+)\s+IN\s+\(([?,]+)\)/", function($m) {
               return "($m[1]=" . implode("OR $m[1]=", array_fill(0, 1+strlen("$m[2]")/2, "? ")) . ")";
            }, $sql);
        }

        #-- just debug
        if (!empty($this->test)) { 
            print json_encode($params2)." => " . trim($sql) . "\n"; return;
        }
    
        #-- run
        $s = $this->prepare($sql)
        and
        $r = $s->execute($params2);

        #-- wrap        
        return $s && $r ? new db_result($s) : $s;
    }

    // This is a restrictive filter function for column/table name identifiers.
    // Can only be foregone if it's ensured that none of the passed named db() $arg keys originated from http/user input.
    function db_identifier($as, $wrap="") {
        return preg_replace(array("/[^\w\d_.]/", "/^|$/"), array("_", $wrap), $as);
    }

}



/**
 * Allows traversing result sets as arrays or hydrated objects,
 * or fetches only first result row on ->column_name accesses.
 *
 */
class db_result extends ArrayObject implements IteratorAggregate {

    protected $results = NULL;

    function __construct($results) {
        parent::__construct(array(), 2);
        $this->results = $results;
    }
    // used as PDO statement
    function __call($func, $args) {
        return call_user_func_array(array($this->results, $func), $args);
    }

    // Single column access
    function __get($name) {
    
        // get first result, transfuse into $this
        if (is_object($this->results)) {
            $this->exchangeArray($this->results->fetch());
            unset($this->results);
        }
        
        // suffice __get
        return $this[$name];
    }

    // Just let PDOStatement handle the Traversable
    function getIterator() {
        return isset($this->results)
             ? $this->results
             : new ArrayIterator($this);
    }

    // Or hydrate specific result objects ourselves
    function into() {
        $into = func_get_args() ?: array("ArrayObject", 2);
        return new db_result_iter($this->results, $into);
    }
}


/**
 * More wrapping for hydrated iteration.
 *
 */
class db_result_iter implements Iterator {

    // Again keep PDOStatement and class specifier
    protected $results = NULL;
    protected $into = array();
    function __construct($results, $into) {
        $this->results = $results;
        $this->into = $into;
    }
    
    // Iterator just fetches and converts on traversal
    protected $row = NULL;
    public function current()
    {
        list($class, $arg2) = $this->into;
        return new $class($this->row, $arg2);
    }
    function valid() {
        return !empty($this->row = $this->results->fetch());
    }
    
    // unused for normal `foreach` operation
    function next() { return NULL; }
    function rewind() { return NULL; }
    function key() { return NULL; }
}



/**
 * Table data gateway. Don't use directly.
 *
 * Keeps ->_meta->table name and ->_meta->fields,
 * uses extendable tables with [ext] field serialization.
 * Doesn't cope with table joins. (yet?)
 *
 * Allows to ->set() and ->save() record back.
 */
class db_record /*resembles db_result*/ extends ArrayObject {

    // this is not purposelessly private, but to not pollute (array) typecasts with decorative data
    private $_meta;

    // initialize from db() result or array
    function __construct($results, $table, $fields, $keys) {
        
        // meta
        $this->_meta = new stdClass();
        $this->_meta->table = $table;
        $this->_meta->fields = array_unique(array_merge(array_keys($fields), array_keys($results)));
        $this->_meta->keys = $keys;
        
        // db query result
        if (is_array($results)) {
            $this->_meta->new = 1;  // instantiate from defaults or given row values
        }
        else {
            //if (is_string($results)) {   // queries are handled in wrapper
            //    $results = db($results);
            //}
            $results = $results->fetch();  // just get first result row
            $this->_meta->new = 0;
        }

        // unfold .ext
        if ($this->_meta->ext = isset($results["ext"])) {
            $results = array_merge($results, unserialize($results["ext"]));
        }

        // copy data
        // and turn object==array
        parent::__construct((array)$results, 2); //ArrayObject::ARRAY_AS_PROPS

        // fluent (hybrid constructor wrapper)
        return $this;
    }
    
    // set field
    function set($key, $val) {
        $this->{$key} = $val;
        return $this;  // fluent
    }

    // store table back to DB
    function save($row=NULL) {
    
        // source
        if (empty($row)) {
            $row = $this->getArrayCopy();
        }
        else {
            $row = array_merge($this->getArrayCopy(), is_array($row) ? $row : $row->getArrayCopy());
        }
    
        // fold .ext
        if ($this->_meta->ext) {
            $ext = array();
            foreach ($row as $key=>$val) {
                if (!in_array($key, $this->_meta->fields)) {
                    $ext[$key] = $val;
                    unset($row[$key]);
                }
            }
            $row["ext"] = serialize($ext);
        }
        
        // store
        if ($this->_meta->new) {
            db("INSERT INTO {$this->_meta->table} (:?) VALUES (??)", $row, $row);
            $this->_meta->new = 0;
        }
        // update
        else {
            $keys = $this->keys($row, 1);
            db("UPDATE {$this->_meta->table} SET :, WHERE :&", $row, $keys);
        }
        
        return $this;  // fluent
    }

    // split $keys from $row/$this
    function keys(&$row, $unset=0) {
        $keys = array();
        foreach ($this->_meta->keys as $key) { 
            $keys[$key] = $row[$key];
            if ($unset) unset($row[$key]);
        } 
        return $keys;
    }
    
    // oh noooes
    function delete() {
        db("DELETE FROM {$this->_meta->table} WHERE :&", $this->keys($this));
        return $this;  // well
    }
}




?>