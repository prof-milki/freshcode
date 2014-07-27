<?php
/**
 * title: PDO wrapper
 * description: Hybrid db() interface for extended SQL parameterization and result folding
 * api: php
 * type: database
 * version: 0.9.9
 * depends: php:pdo
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
 *      ??    Interpolation of indexed arrays - useful for IN clauses.
 *      ::    Turns associative arrays into a :named, :value, :list.
 *      :?    Interpolates key names (ignores values).
 *
 *      :&    Becomes a `name`=:value list, joined by AND - for WHERE clauses.
 *      :|    Becomes a `name`=:value list, joined by OR - for WHERE clauses.
 *      :,    Becomes a `name`=:value list, joined by , commas - for UPDATEs.
 *
 *      :*    Expression placeholder, where the associated argument should
 *            contain an array ["AND foo IN (??)", $params] - which only
 *            interpolates if $params contains any value.  Can be nested.
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
 * Or just traversed row-wise as usual by iteration
 *
 *       foreach (db("...") as $row)
 *
 * Alternatively by object-wrapping (unlike plain PDO->fetchObject() this
 * hydrates the object using its normal constructor) the result set with:
 *
 *       foreach ($result->into("ArrayObject") as $row)
 *
 * Yet all PDO ->fetch() methods are still available for use on the result obj.
 *
 *
 * CONNECT  
 *
 * The db() interface binds the global "$db" variable. It ought to be
 * initialized with:
 *
 *       db(new PDO(...));
 *
 * It's wrapped PDO handle can also be retrieved with just $pdo = db(); then.
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
    $db = & $GLOBALS["db"];   // Alternatively could be just static to hide it behind db()
    
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


    /**
     * Keep PDO handle.
     *
     */
    public $pdo = NULL;
    function __construct($pdo) {
        $this->pdo = $pdo;
    }


    /**
     * Chain to plain PDO if any other method invoked.
     *
     */
    function __call($func, $args) {
        return call_user_func_array(array($this->pdo, $func), $args);
    }


    /**
     * Prepares and executes query after extended placeholders and parameter unpacking.
     *
     */
    function __invoke($sql, $args=array()) {

        // $sql may contain associative SQL parts and parameters
        if (is_array($sql)) {
            list($sql, $args) = $this->join($sql);
        }

        // reject plain strings in SQL
        if (strpos($sql, "'")) {
            trigger_error("SQL query contained raw data. DO NOT WANT", E_USER_WARNING);
            return NULL;
        }

        // flatten array arguments and extended placeholders
        list($sql, $args) = $this->fold($sql, $args);
        
        // placeholders
        if (!empty($this->tokens) && strpos($sql, "{")) {
            $sql = preg_replace_callback("/\{(\w+)(.*?)\}/", array($this, "token"), $sql);
        }
        // older SQLite workaround
        if (!empty($this->in_clause) && strpos($sql, " IN (")) { // only for ?,?,?,? enum params
            $sql = preg_replace_callback("/(\S+)\s+IN\s+\(([?,]+)\)/", array($this, "in_clause"), $sql);
        }
        // just debug output
        if (!empty($this->test)) { 
            print json_encode($args)." => " . trim($sql) . "\n"; return;
        }
    
        // run
        $s = $this->prepare($sql)
        and
        $r = $s->execute($args);

        // wrap        
        return $s && $r ? new db_result($s) : $s;
    }


    /**
     * Expands the extended placeholders and flattens arrays from parameter list.
     *
     */
    function fold($sql, $args) {
    
        // output parameter list
        $flat_params = array();
        
        #-- flattening sub-arrays (works for ? enumarated and :named params)
        foreach ($args as $i=>$a) {

            // subarray that corresponds to special syntax placeholder?
            if (is_array($a)
            and preg_match("/  \?\?  |  : [?:*  &,|]  /x", $sql, $capture, PREG_OFFSET_CAPTURE))
            {
                list($token, $pos) = current($capture);

                // placeholder substitution, possibly changing $a params
                $replace = $this->{self::$expand[$token]}($a);

                // update SQL string
                $sql = substr($sql, 0, $pos) . $replace . substr($sql, $pos + strlen($token));
            }

            // unfold into plain parameter list
            if (is_array($a)) {
                $flat_params = array_merge($flat_params, $a);
            }
            else {
                $flat_params[] = $a;
            }
        }
        
        return array($sql, $flat_params);
    }


    /**
     * Syntax expansion callbacks
     *
     */
    static $expand = array(
        "??" => "expand_list",
        ":?" => "expand_keys",
        "::" => "expand_named",
        ":," => "expand_assoc_comma",
        ":&" => "expand_assoc_and",
        ":|" => "expand_assoc_or",
        ":*" => "expand_expr",
    );

    // ?? array placeholders
    function expand_list($a) {
        return implode(",", array_fill(0, count($a), "?"));
    }

    // :? name placeholders, transforms list into enumerated params
    function expand_keys(&$a) {
        $enum = array_keys($a) === range(0, count($a) - 1);
        $r = implode(",", $this->db_identifier($enum ? $a : array_keys($a), "`"));
        $a = array();
        return $r;
    }

    // :: becomes :named,:value,:list
    function expand_named($a) {
        return ":" . implode(",:", $this->db_identifier(array_keys($a)) );
    }

    // for :, expand COMMA-separated key=:key,bar=:bar associative array
    function expand_assoc_comma($a, $fill = ", ", $replace=array()) {
        foreach ($this->db_identifier(array_keys($a)) as $key) {
            $replace[] = "`$key` = :$key";
        }
        return implode($fill, $replace);
    }
    // for :& AND-chained assoc foo=:foo AND bar=:bar
    function expand_assoc_and($a) {
        return $this->expand_assoc_comma($a, " AND ");
    }
    // for :| OR-chained assoc foo=:foo OR bar=:bar
    function expand_assoc_or($a) {
        return $this->expand_assoc_comma($a, " OR ");
    }

    // while :* holds an optional expression and subvalue list
    function expand_expr(&$a) {
        foreach (array_chunk($a, 2) as $pair) if (list($sql, $args) = $pair) {
            // substitute subexpression as if it were a regular SQL string
            if (is_array($args) && count($args)) {
                $args = array_sum(array_map("is_array", $args)) ? $args : array($args);
                list ($replace, $a) = $this->fold($sql, $args);
                return $replace;
            }
        }
        $a = array();  // else replace with nothing and omit current data for flattened $params2
    }

    
    
    /**
     * For readability input SQL may come as associative clause => params list.
     *   ["SELECT ?" => $num,
     *    "FROM :?"  => [$tbl],
     *    "WHERE :&" => $match
     *   ]
     * Which is separated here into keys as $sql string and $args from values.
     *
     */
    function join($sql_args, $sql="", $args=array()) {
        foreach ($sql_args as $s=>$a) {
            $sql .= $s . "\n  ";
            $args[] = $a;
        }
        return array($sql, $args);
    }



    // This is a restrictive filter function for column/table name identifiers.
    // Can only be foregone if it's ensured that none of the passed named db() $arg keys originated from http/user input.
    function db_identifier($as, $wrap="") {
        return preg_replace(array("/[^\w\d_.]/", "/^|$/"), array("_", $wrap), $as);
    }

    
    // Regex callbacks
    function token($m) {
        list($m, $tok, $ext) = $m;
        return isset($this->token[$tok]) ? $this->token[$tok].$ext : $this->token["$tok$ext"];
    }
    function in_clause($m) {
        list($m, $key, $vals) = $m;
        $num = substr_count($vals, "?");
        return "($key=" . implode("OR $key=", array_fill(0, $num, "? ")) . ")";
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
        list($class, $a2, $a3, $a4, $a5) = array_merge($this->into, [NULL, NULL, NULL, NULL]);
        return new $class($this->row, $a2);
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