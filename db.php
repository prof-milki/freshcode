<?php
/**
 * title: database
 * description: basic db() interface for parameterized SQL and result folding
 * api: php
 * type: database
 * version: 0.8
 * depends: pdo
 * license: Public Domain
 * author: Mario Salzer
 * url: http://php7framework.sourceforge.net/
 *
 *
 * QUERY
 *
 * Provides simple database queries with enumerated or named parameters. It's
 * flexible in accepting scalar arguments and arrays. Array args get merged,
 * or transcribed when special placeholders are present:
 *
 *   $r = db("SELECT * FROM tbl WHERE a>=? AND b IN (??)", $a, array($b));
 *
 * Two ?? are used for interpolating arrays, which is useful for IN clauses.
 * The placeholder :? interpolates key names (doesn't add values).
 * And :& or :, or :| become a name=:assign list grouped by AND, comma, OR.
 * Whereas :: turns into a simple :named,:value,:list (for IN clauses).
 * Also configurable {TOKENS} are replaced automatically (db()->tokens[]).
 *
 * RESULT
 *
 * The returned result can be accessed as single data row, with $data->column
 * or using $data["column"].
 * Or if it's a result list, foreach() can iterate over all returned rows.
 * And all PDO ->fetch() methods are still available for use on the result obj.
 * ArrayObjects cannot be used like real arrays in all contexts; typecasting
 * the data out is not possible, in string context curly braces "{$a->x}" are
 * necessary, and in sub-loops needed object syntax "foreach ($a->subarray as)"
 *
 * CONNECT  
 *
 * The db() interface utilizes the global "$db" variable. Which could also be
 * instantiated separately or using:
 * db("connect", array("mysql:host=localhost;dbname=test","username","password"));
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
 * SQL query.
 *
 */
function db($sql=NULL, $params="...") {
    global $db;
    
    #-- open database
    if ($sql == "connect") {
    
        // DSN
        $params = is_array($params) ? array_values($params) : array($params,"","");
        $db = new PDO($params[0], $params[1], $params[2]);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // save settings
        $db->tokens = array("PREFIX"=>""); // or reference global $config
        #$db->in_clause = strstr($params[0], "sqlite");
    }
    
    #-- singleton use
    elseif (empty($sql)) {
        return $db;
    }
    
    #-- reject SQL
    elseif (strpos($sql, "'")) {
        trigger_error("SQL query contained raw data. DO NOT WANT", E_USER_WARNING);
    }
    
    #-- execute SQL
    else {
    
        #-- get $params
        $params2 = array();
        $args = func_get_args();
        array_shift($args);

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
                            $replace = implode(",", db_identifier($enum ? $a : array_keys($a), "`"));
                            $enum = 1;  $a = array();   // do not actually add values
                            break;

                        case "::":  // inject :named,:value,:list
                            $replace = ":" . implode(",:", db_identifier(array_keys($a)) );
                            break;

                        case ":&":  // associative params - becomes "key=:key AND .."
                        case ":,":  // COMMA-separated
                        case ":|":  // OR-separated
                            $fill = array(":&"=>" AND ", ":,"=>" , ", ":|"=>" OR ");
                            $replace = array(); foreach (db_identifier(array_keys($a)) as $key) { $replace[] = "`$key`=:$key"; }
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
        if (empty(!$db->tokens) && strpos($sql, "{")) {
            $sql = preg_replace_callback("/\{(\w+)(.*?)\}/e", function($m) use ($db) {
                return isset($db->token["$m[1]"]) ? $db->token["$m[1]"]."$m[2]" : $db->token["$m[1]$m[2]"];
            }, $sql);
        }
        
        #-- SQL incompliance workarounds
        if (!empty($db->in_clause) && strpos($sql, " IN (")) { // only for ?,?,?,? enum params
            $sql = preg_replace_calback("/(\S+)\s+IN\s+\(([?,]+)\)/", function($m) {
               return "($m[1]=" . implode("OR $m[1]=", array_fill(0, 1+strlen("$m[2]")/2, "? ")) . ")";
            }, $sql);
        }

if (isset($db->test)) { print json_encode($params2)." => " . trim($sql) . "\n"; return; }
    
        #-- run
        $s = $db->prepare($sql);
        $s->setFetchMode(PDO::FETCH_ASSOC);
        $r = $s->execute($params2);

        #-- wrap        
        return $r ? new db_result($s) : $s;
    }
}

// This is a restrictive filter function for column/table name identifiers.
// Can only be foregone if it's ensured that none of the passed named db() $arg keys originated from http/user input.
function db_identifier($as, $wrap="") {
    return preg_replace(array("/[^\w\d_.]/", "/^|$/"), array("_", $wrap), $as);
}


/**
 * Allows list access, or fetches first result[0]
 *
 */
class db_result extends ArrayObject implements IteratorAggregate {

    function __construct($results) {
        $this->results = $results;
        parent::__construct(array(), 2);
    }
    
    // single access
    function __get($name) {
    
        // get first result, transfuse into $this
        if ($this->results) {
            foreach ($this->results->fetch(PDO::FETCH_ASSOC) as $key=>$value) {
                $this->{$key} = $value;
            }
            unset($this->results);
        }
        
        // suffice __get
        return $this->{$name};
    }
    
    // used as PDO statement
    function __call($func, $args) {
        return call_user_func_array(array($this->results, $func), $args);
    }
    
    // iterator
    function getIterator() {
        if (isset($this->results)) {
            $this->results->setFetchMode(PDO::FETCH_CLASS, "ArrayObject", array(array(), 2));
            return $this->results;
        }
        else return new ArrayIterator($this);
    }

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