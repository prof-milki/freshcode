<?php
/**
 * api: php7
 * title: fluent curl
 * description: simple wrapper around curl functions
 * version: 0.2
 * license: Public Domain
 *
 *
 * This simple wrapper class and its factory function allow
 * compact invocations of curl. You can pass either just an
 * URL, an option array, a previously wrapped curl() or a
 * raw curl resource handle.
 * Methods and options (CURL_ constants) lose their prefix
 * once wrapped, and can be easily chained:
 *
 *   curl($url)->followlocation(1)->verbose(1)->exec();
 *
 * Option names are case-insensitive too. The exec() and any
 * setting retrieval terminate the fluent call.
 *
 *
 * Individual curl() wrappers can also be combined into a
 * parallel-request curl_multi() instance:
 *
 *   curl_multi($url1, $url2, $url3)->verbose(1)->exec();
 *
 * Here the handles may be passed as associative array or
 * indexed list. exec() will wait until all finish, then
 * directly return an array of result contents.
 * In-between option calls are applied to all bound handles.
 *
 */



# hybrid constructor
function curl($url=NULL) {
    return is_a($url, "curl") ? $url : new curl($url);
}



/**
 * Fluent curl wrapper which obviates CURL_ prefixes.
 *
 * Makes curl_functions available as methods. Previous constants
 * are easily accessible through chainable method calls as well.
 * Only getinfo() options are mapped as virtual properties.
 *
 */
class curl {


    /**
     * resource / curl handle
     *
     */
    public $handle = NULL;


    /**
     * Initialize, where $params is usually just an $url, or an [OPT=>val] list
     *
     */
    function __construct($params) {
    
        // create
        $this->handle = is_object($params) ? $params : curl_init();
        
        // default options
        $this->returntransfer(1)
             ->httpget(1)
             ->http200aliases(array(200,201,202,203))
             ->header(0)
             ->UserAgent("freshcode/0.5 (U) +http://freshcode.club/")
             ->followlocation(!ini_get("safe_mode"));
             
        // option is just URL string
        if (is_string($params)) {
            $this->url($params);
        }
        // multiple [url=>$url, post=>$bool, ..] options
        elseif (is_array($params)) foreach ($params as $cmd=>$opt) {
            $this->__call($cmd, array($opt));
        }
    }

    
    /**
     * Most invocations are mapped onto CURL_CONST settings,
     * while some are just plain function calls:
     *
     * @method exec()
     * @method errno()
     * @method error()
     * @method getinfo()
     */
    function __call($opt, $args) {

        # CURLOPT_*** constant
        if (defined($const = "CURLOPT_" . strtoupper($opt))) {
            curl_setopt($this->handle, constant($const), $args[0]);
        }
        # curl_action function
        elseif (function_exists($func = "curl_$opt")) {
            return call_user_func_array($func, array_merge(array($this->handle), $args));
        }
        # neither exists
        else {
            trigger_error("curl: unknown '$opt' option", E_USER_ERROR);
        }
        
        return $this;
    }

    
    /**
     * retrieve constants 
     *
     */
    function __get($opt) {
        // @implicit error message from constant() for non-existant symbols
        return curl_getinfo($this->handle, constant($const = "CURLINFO_" . strtoupper($opt)));
    }
    
    
}




# hybrid constructor
function curl_multi($url=NULL) {
    return new curl_multi($url);
}


/**
 * Associative handler for multiple concurrent curl requests.
 * Not so fluent.
 *
 */
class curl_multi {


    /**
     * Indexed or associative list
     *
     */
    public $list = array();

    
    /**
     * Multi-handle
     *
     */
    public $mh = NULL;


    /**
     * Bind list of curl handles.
     *
     * An associative array can be passed here, either of prepared curl() handles,
     * just URLs, or an curl option array each.
     *
     */
    function __construct($list) {
    
        // optionally get indexed params as list
        if (func_num_args() >= 2) {
           $list = func_get_args();
        }
        
        // auto-wrap curl() handlers
        $list = array_map("curl", $list);

        // copy handle list
        $this->list = $list;
        
        // bind actual curl handles
        $this->mh = curl_multi_init();
        foreach ($list as $cw) {
            curl_multi_add_handle($this->mh, $cw->handle);
        }
    }
    
    
    /**
     * Run until all individual curl() handles are finished.
     *
     * Implicitly returns a list of result contents (associative array as well).
     *
     */
    function exec($timeout=1.50, $t=0.0, $running=1) {

        // process within timeframe
        while (($timeout > $t += $timeout/50) and $running) {

            curl_multi_exec($this->mh, $running);
            curl_multi_select($this->mh, $timeout/50);
        }

        // fetch results, then close handles
        return $this->close( $this->getcontent() );
    }

    
    /**
     * Retrieve results, associative array.
     *
     */
    function getcontent() {
        return array_map("curl_multi_getcontent", array_map("current",/*retrieve first property (->handle) each*/ $this->list));
    }
    

    /**
     * Close all curl() handles in $list, and the curl-multi wrapper.
     *
     */
    function close($r=NULL) {
        $this->__call("close", array());
        curl_multi_close($this->mh);
        return $r;
    }


    /**
     * Applies options on each curl handle.
     *
     */
    function __call($opt, $args) {
        foreach ($this->list as $cw) {
            $cw->__call($opt, $args);
        }
    }

}


?>