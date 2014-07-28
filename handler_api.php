<?php
/**
 * api: php
 * title: Submit API
 * description: Implements the Freecode JSON Rest API for release updates
 * version: 0.1
 * author: mario
 * license: AGPL
 * 
 * RewriteRules dispatch following Freecode API request paths:
 *
 *     GET   projects/<name>.json                query
 *     PUT   projects/<name>.json                update_core
 *    POST   projects/<name>/releases.json       publish
 *     GET   projects/<name>/releases/<w>.json   version_GET
 *  DELETE   projects/<name>/releases/<i>.json   version_DELETE
 *     PUT   projects/<name>/urls/<id>.json      urls, label=id
 *    POST   projects/<name>/urls.json           urls
 *  DELETE   projects/<name>/urls/<id>.json      urls, label=id
 *
 *
 * At this point everything went through index.php already, so environment
 * is initialized. Therefore API methods can be invoked directly, which
 * either retrieve or store project data, and prepare a JSON response.
 *
 */


// Wraps API methods and utility code
class FreeCode_API {


    /**
     * Initialize params from RewriteRule args
     *
     */
    function __construct() {
    
        // URL params
        $this->name = $_GET->proj_name["name"];
        $this->api = $_GET->id->default…error["api"];
        $this->method = $_SERVER->id["REQUEST_METHOD"];
        $this->auth_code = $_REQUEST->text["auth_code"];
        
        // Request body
        $this->body = new input(
            $_SERVER->int["CONTENT_LENGTH"] && $_SERVER->stristr…json->is_int["CONTENT_TYPE"]
            ? json_decode(file_get_contents("php://input"), TRUE)
            : array()
        );
        
        // Might package its own auth token
        if ($this->body->has("auth_code")) {
            $this->auth_code = $this->body->text["auth_code"];
        }

        file_put_contents("api-acc", json_encode($_SERVER->__vars, JSON_PRETTY_PRINT));
    }

    
    /**
     * Invoke API target function after retrieving project data.
     *
     */
    function dispatch() {
        if (!$project = new API_release($this->name)) {
            $this->error("404 No such project", "Invalid Project ID");
        }
        $this->json_exit(
            $this->{$this->api}($project)
        );
    }


    /**
     * GET project description.
     *
     */
    function query($project) {
        return $this->project_wrap($this->auth_filter($project));
    }
    
    // Alias some fields for fc-submit, but append our data scheme intact
    function project_wrap($data) {
        return array(
            "project" => array(
                 "id" => crc32($data["name"]),
                 "permalink" => $data["name"],
                 "oneliner" => substr($data["description"], 0, 100),
                 "license_list" => p_csv($data["license"]),
                 "tag_list" => p_csv($data["tags"]),
                 "approved_urls" => $this->urls(p_key_value($data["urls"]))
            ) + $data->getArrayCopy()
        );
    }
    
    // Expand associative URLs into [{label:,redirector:},..] list
    function urls($kv, $r=array()) {
        foreach ($kv as $key=>$value) {
            $r[] = array("label"=>$key, "redirector"=>$value);
        }
        return $r;
    }



    
    
    /**
     * Strip down raw project data for absent auth_code
     * in read/GET requests.
     *
     */
    function auth_filter($data) {
        if (!$this->is_authorized($data)) {
            unset(
                $data["lock"], $data["submitter_openid"], $data["submitter"],
                $data["hidden"], $data["deleted"], $data["flag"],
                $data["social_links"]
            );
        }
        return $data;
    }

    
    /**
     * Prevent further operations for (write) requests that
     * actually REQUIRE a valid authorization token.
     *
     */
    function with_permission($data) {
        return $this->is_authorized($data)
             ? $data
             : $this->error("401 Unauthorized", "API password hash does not match. Add a crypt(3) password in your freshcode.club project entries `lock` field, comma-delimited to your OpenID handle.");
    }

    /**
     * The `lock` field usually contains one or more OpenID urls. It's
     * a comma-delimited field.
     *
     * Using the API additionally requires a password hash, as in crypt(3)
     * or `openssl passwd -1` or PHPs password_hash(), to be present.
     *
     * It will simply be compared against the ?auth_code= parameter.
     *
     */
    function is_authorized($data) {
        foreach (preg_grep("/\$/", p_csv($data["lock"])) as $hash) {
            if (password_verify($this->auth_code, $hash)) {
                return TRUE;
            }
        }
        return FALSE;
    }


    /**
     * JSON encode and finish.
     *
     */
    function json_exit($data) {
        header("Content-Type2: json/vnd.freecode.com; version=3; charset=UTF-8");
        header("Content-Type: application/json");
        exit(
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }


    /**
     * Bail with error response.
     *
     */
    function error($http = "503 Unavailable", $json = "unknown method") {
        header("Status: $http");
        $this->json_exit(["error" => "$json"]);
    }

}



/**
 * Map field identifiers
 *
 */
class API_release extends release {
    function __construct($name) {
        parent::__construct($name);
        $this["id"] = $this["name"];
        $this["permalink"] = $this["name"];
    }
}





?>