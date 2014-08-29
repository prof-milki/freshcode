<?php
/**
 * api: freshcode
 * title: Submit API
 * description: Implements the Freecode JSON Rest API for release updates
 * version: 0.3
 * type: handler
 * category: API
 * doc: http://fossil.include-once.org/freshcode/wiki/Freecode+JSON+API
 * author: mario
 * license: AGPL
 *
 * This utility code emulates the freecode.com API, to support release
 * submissions via freecode-submit and similar tools. The base features
 * fit well with the freshcode.club database scheme.
 *
 * Our RewriteRules map following Freecode API request paths:
 *
 *       GET   projects/<name>.json                query
 *       PUT   projects/<name>.json                update_core
 *      POST   projects/<name>/releases.json       publish
 *       GET   projects/<name>/releases/<w>.json   version_GET, id=
 *    DELETE   projects/<name>/releases/<i>.json   version_DELETE
 *
 * From the ridiculous amount of URL manipulation calls, we just keep:
 *
 *   GET/PUT   projects/<name>/urls.json           urls  (assoc array)
 *
 *
 * Retrieval requests usually come with an ?auth_code= token. For POST
 * or PUT access it's part of the JSON request body. Which comes with
 * varying payloads depending on request type:
 *
 *   { "auth_code": "pw123",
 *     "project": {
 *       "license_list": "GNU GPL",
 *       "project_tag_list": "kernel,operating-system",
 *       "oneliner": "Linux kernel desc",
 *       "description": "Does this and that.."
 *   } }
 *
 * Any crypt(3) password hash in a projects `lock` field will be checked
 * against the plain auth_code.
 *
 * At this point everything went through index.php already; runtime env
 * thus initialized. Therefore API methods can be invoked directly, which
 * either retrieve or store project data, and prepare a JSON response.
 *
 */


/*
 @Test @sh

 @t query
   wget http://freshcode/projects/linux.json?auth_code=unused -O-
   ./fc-submit -q linux

 @t change_core
   ./fc-submit -P linux -D "new proj" -S "oneliner" -L "GNU GPL" -T "kernel,linux" -n -V

 @t publish
   ./fc-submit -P linux -v "3.55.1" -c "Change all the things" -t "major,bugfix" -n -V

 @t delete
   ./fc-submit -P linux -v "3.55.1" -d -n -V

 @t urls
   wget http://freshcode/projects/linux/urls.json?auth_code=0 -O-
*/


// Wraps API methods and utility code
class FreeCode_API {


    // HTTP method
    var $method;

    // API function
    var $api;

    // Project name
    var $name;

    // Optional revision ID (just used for releases/; either "pending" or t_published timestamp) 
    var $rev;
    
    // inner @array from JSON request body
    var $body;
    
    // Optional auth_code (from URL or JSON body)
    var $auth_code;
    
    
    // Logging
    var $log = TRUE;
    var $timestamp = 0;




    /**
     * Initialize params from RewriteRule args.
     *
     */
    function __construct() {
    
        // URL params
        $this->name = $_GET->proj_name["name"];
        $this->api = $_GET->id->strtolower->in_array("api", "query,update_core,publish,urls,version_get,version_delete");
        $this->method = $_SERVER->id->strtoupper["REQUEST_METHOD"];
        $this->auth_code = $_REQUEST->text["auth_code"];
        $this->rev = $_REQUEST->text["id"];  // optional param
        
        // Request body is only copied, because it comes with varying payloads (release, project, urls)
        if ($_SERVER->int["CONTENT_LENGTH"] && $_SERVER->stripos…json->is_int["CONTENT_TYPE"]) {
            $this->body = json_decode(file_get_contents("php://input"), TRUE);
            $this->auth_code = $this->body["auth_code"];
        }
        
        // Logging
        $this->timestamp = sprintf("%s (%s)", time(), gmdate(DATE_ISO8601, time()));
        $this->log($this, "\n\n\n/* << REQUEST */");
    }
    
    
    /**
     * Log incoming request, outgoing response, or data set as prepared prior updating DB.
     *
     */
    function log($what, $prefix) {
        if ($this->log) {
            file_put_contents("api-log.txt", "$prefix\n" . json_encode($what, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND);
        }
        return $what;
    }


    
    /**
     * Invoke API target function.
     * After retrieving current project data.
     *
     */
    function dispatch() {
    
        // Fetch latest revision
        $project = new release($this->name);
        if (!$project["name"]) {
            $this->error_exit(NULL, "404 No such project", "Invalid Project ID");
        }
        
        // Run dialed method, then output JSON response.
        $this->json_exit(
            $this->{ $this->api ?: "error_exit" }($project)
        );
    }



    /**
     * GET project description
     * -----------------------
     *
     * @unauthorized
     *
     * Just returns the current releases project description
     * and basic fields.
     * Freecode API clients expect:
     *   → id         (something numeric, which we don't have, so just a CRC32 here)
     *   → permalink  (=name)
     *   → oneliner   (we just extract the first line)
     *   → license_list
     *   → tag_list
     *   → approved_urls
     *
     * Also appends extraneous freshcode fields.
     *
     */
    function query($project) {
    
        // Everyone can access this, but only the owner will see private fields
        $data = $this->auth_filter($project);
    
        // Alias some fields for fc-submit, but append our data scheme intact
        return array(
            "project" => array(
                 "id" => crc32($data["name"]),
                 "permalink" => $data["name"],
                 "oneliner" => substr($data["description"], 0, 100),
                 "license_list" => p_csv($data["license"]),
                 "tag_list" => p_csv($data["tags"]),
                 "approved_urls" => $this->array_uncolumn(p_key_value($data["urls"], NULL))
            ) + $data->getArrayCopy()
        );
    }
    
    // Expand associative URLs into [{label:,redirector:},..] list
    function array_uncolumn($kv, $ind="label", $dat="redirector", $r=array()) {
        foreach ($kv as $key=>$value) {
            $r[] = array($ind=>$key, $dat=>$value);
        }
        return $r;
    }



    /**
     * PUT project base fields
     * -----------------------
     *
     * @auth-required
     *
     * Whereas the project ->body contains:
     *   → license_list
     *   → project_tag_list
     *   → oneliner  (IGNORED)
     *   → description
     * Additionally we'd accept:
     *   → state
     *   → download (URL)
     *   → homepage
     *
     */
    function update_core($project) {
        $core = new input($this->body["project"], "core");
        // extract fields
        $new = array(
            // standard FC API fields
            "license" => tags::map_license(p_csv($core->words["license_list"])[0]),
            "tags" => f_tags(join(" ", $core->text["project_tag_list"])),
            "description" => $core->text["description"],
            // additional overrides
            "homepage" => $core->url->http["homepage"],
            "download" => $core->url->http["download"],
            "state" => tags::state_tag($core->name["state"]),
        );
        return $this->insert($project, $new);
    }


    /**
     * POST release/version
     * --------------------
     *
     * @auth-required
     *
     * Here the release body contains:
     *  → version
     *  → changelog
     *  → tag_list
     *  → hidden_from_frontpage
     * We'd also accept:
     *  → state
     *  → download
     *
     */
    function publish($project) {
        $rel = new input($this->body["release"], "rel");
        // extract fields
        $new = array(
            "version" => $rel->text["version"],
            "changes" => $rel->text["changelog"],
            "scope" => tags::scope_tags($rel->text["tag_list"]),
            "state" => tags::state_tag($rel->text["state"] . $rel->text["tag_list"]),
            "download" => $rel->url->http["download"],
        );
        $flags = array(
            "hidden" => $rel->int["hidden_from_frontpage"],
            "deleted" => 0,
        );
        return $this->insert($project, $new, $flags);
    }
    

    /**
     * Check for "pending" releases
     * ----------------------------
     *
     * @unauthorized
     *
     * We don't have a pre-approval scheme on Freshcode currently,
     * so this just returns a history of released versions.
     *
     * For the `id` we're just using the `t_published` timestamp.
     * Thus a "withdraw" request could reference it.
     *
     */
    function version_GET($project) {
        assert($this->rev === "pending");

        // Query release revisions
        $list = db("
           SELECT name, version, t_published, MAX(t_changed) AS t_changed,
                  scope, hidden, changes
             FROM release
            WHERE name=?
         GROUP BY version
            LIMIT 10", $this->name
        );

        // Assemble oddly nested result array
        $r = [];
        foreach ($list as $row) {
            $r[] = array("release" => array(
                "id" => $row["t_published"],
                "version" => $row["version"],
                "tag_list" => explode(" ", $row["scope"]),
                "hidden_from_frontpage" => (bool)$row["hidden"],
                "approved_at" => gmdate(DateTime::ISO8601, $row["t_changed"]),
                "created_at" =>  gmdate(DateTime::ISO8601, $row["t_published"]),
                "changelog" => $row["changes"],
            ));
        }
        return $r;
    }



    /**
     * "Withdraw" a "pending" release
     * ------------------------------
     *
     * @auth-required
     *
     * We're faking two things here. Firstly that the review process
     * was enabled by default. Secondly that you could delete things.
     * (The database is designed to be somewhat "immutable", we just
     * pile up revisions normally.)
     *
     * So withdrawing a release just means it gets marked "deleted"
     * (formerly just "hidden" and/or flagged for moderator attention.)
     * This somewhat still may terminate a project lifeline (due to VIEW
     * revision grouping), but can be undone by submitting the release
     * anew.
     *
     * The reasoning being that withdrawn releases are really just
     * authors making last minute fixes; commonly retracted releases
     * are just resent later, or with a different changelog.
     *
     */
    function version_DELETE($project) {

        // Obviously requires a valid `lock` hash
        $this->requires_permission($project);
        assert(is_numeric($this->rev));

        // Hide all entries for revision
        $r = db([
         " UPDATE release ",
         "    SET :,  " => ["hidden" => 1, "deleted" => 1, "flag" => 0],
         "  WHERE :&  " => ["name" => $this->name, "t_published" => $this->rev]
        ]);

        return $r ? array("success" => TRUE) : $this->error_exit(NULL);
    }


    /**
     * URL editing
     * -----------
     *
     * @auth-required
     *
     * Here we deviate from the overflowing Freecode API with individual
     * URL manipulation. (PUT,POST,DELETE /projects/<name>/urls/targz.json)
     * That's just double the work for client and API.
     *
     * Instead on freshcode there is but one associative URL blob for
     * reading and updating all URLs at once.
     *
     *   GET /projects/name/urls.json   { "urls" : { "src": "http://..",
     *                                       "github": "http://.." }    }
     *   PUT /projects/name/urls.json   { "urls": { "txz":, "doc":.. }  }
     *
     * Our labels use the tag-form, so incoming labels will be adapted.
     * ("Tar/BZ2" becomes "tar-bz2".)
     *
     * Internally the urls are stored in an INI-style key=value text blob.
     * (But the API stays somewhat more RESTy with an associative dict.)
     *
     */
    function urls($project) {
    
        /**
         * For a GET query just mirror "Other URLs" as dict
         *
         * @unauthorized
         *
         */
        if ($this->method == "GET") {
            $urls = p_key_value($project["urls"], NULL);
            $urls["homepage"] = $project["homepage"];
            $urls["download"] = $project["download"];
            return array("urls" => $urls);
        }
        
        /**
         * Updates may come as PUT, POST, PUSH request
         *
         * @auth-required
         *
         */
        else {

            // Filter incoming URLs
            $urls = new input($this->body["urls"], "urls");
            $urls = $urls->list->url[$urls->keys()];

            $new["urls"] = "";
            foreach ($urls as $label => $url) {

                // Remove non-alphanumeric characters
                $label = trim(preg_replace("/\W+/", "-", $label), "-");
                $lower = strtolower($label);
                in_array($lower, ["home-page", "website"]) and $lower = "homepage";

                // Split homepage, download URL into separate fields,
                if ($lower == "homepage" or $lower == "download") {
                    $new[$lower] = $url;
                }
                // While remaining go into `urls` key=value block, retain case-sensitivity here
                else {
                    $new["urls"] .= "$label = $url\r\n";
                }
            }

            // Update DB
            return $this->insert($project, $new);
        }
    }


    
    
    /**
     * Perform partial update
     *
     * @auth-required
     *
     */
    function insert($project, $new, $flags=[]) {

        // Write permissions required obviously.
        $this->requires_permission($project);
 
        // Log data
        $this->log($new, "/* ++ STORE DATA */");

        // Add new fields to $project
        $project->update(array_filter($new, "strlen"), $flags, [], TRUE);

        // Store or return JSON API error.
        return ($project->store() and (header("Status: 201 Created") + 1))
             ? array("success" => TRUE)
             : $this->error_exit(NULL, "500 Internal Issues", "Database mistake");
    }
    
    
    /**
     * Strip down raw project data for absent auth_code
     * in read/GET requests.
     *
     */
    function auth_filter($data) {
        if (!$this->is_authorized($data)) {
            unset(
                $data["lock"],
                $data["submitter_openid"], $data["submitter"], $data["submitter_image"],
                $data["hidden"], $data["deleted"], $data["flag"],
                $data["social_links"],
                $data["autoupdate_regex"], $data["autoupdate_url"],
                $data["t_changed"]
            );
        }
        return $data;
    }

    
    /**
     * Prevent further operations for (write) requests that
     * actually REQUIRE a valid authorization token.
     *
     * @exit if unauthorized
     *
     */
    function requires_permission($data) {
        return $this->is_authorized($data)
             ? $data
             : $this->error_exit(NULL, "401 Unauthorized", "No matching API auth_token hash. Add a crypt(3) password in your freshcode.club project entries `lock` field, comma-delimited to your OpenID handle. See http://fossil.include-once.org/freshcode/wiki/Freecode+JSON+API");
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
        foreach (preg_grep("/^[^:]+$/", p_csv($data["lock"])) as $hash) {
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
        $this->log($data, "/* >> RESPONSE */");
        exit(
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }


    /**
     * Bail with error response.
     *
     */
    function error_exit($data, $http = "503 Unavailable", $json = "unknown method") {
        header("Status: $http");
        $this->json_exit(["error" => "$json"]);
    }

}



?>