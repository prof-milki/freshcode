<?php
/**
 * api: freshcode
 * title: release/project data wrapper
 * description: Database scheme / versioned model abstraction for project releases
 * version: 0.2
 * depends: db
 * license: MITL
 * 
 * With `release` the database model, its versioning and value constraining are
 * consolidated somewhat. It's used by submission / autoupdate / and API interfaces.
 * It's best not to consider this a WebPMVC "model", but table data gateway.
 *
 * It can either be instantiated by project $name, fetching the last entry.
 * Or be populated from a database result array; using db()->into("release")
 *
 *
 *
 *
 */



/** 
 * Encases project/release data, keeps fields accessible per array["name"] syntax;
 * can clean up column formats before ->store()ing it back.
 *
 * Adds a couple of static calls to return specific entries object-wrapped, or lists
 * thereof as plain arrays (because commonly just used for template output).
 *
 */
class release extends ArrayObject {


    /**
     * Can be instanatiated by project name (latest version will be fetched),
     * or from a DB result array.
     *
     * @return release{}
     *
     */
    function __construct($namedata, $uu=NULL) {
    
        // fetch from DB
        if (is_string($namedata)) {
            $namedata = release::latest($namedata);
        }

        // unwrap previous AO or release obj
        if ($namedata instanceof ArrayObject) {
            $namedata = $namedata->getArrayCopy();
        }

        // populate ArrayObject
        if (is_array($namedata)) {
            unset($namedata["_order"]);
            $this->exchangeArray($namedata);
        }
    }
    
    
    /**
     * Prepare new release submission.
     * Merges in flags (hidden, deleted, submitter_*, etc) from latest entry;
     * but retains t_published associated to `version` if it existed before.
     *
     * Filters $newdata to match expected database constraints. For page_submit,
     * $newdata just equals $_POST, and is already an input{} array object.
     *
     * $prefill and $override are used by submission / autoupdate / api callers
     * to define flags.
     *
     */
    function update($newdata, $prefill_flags=array(), $override_flags=array(), $partial=FALSE) {

        // Format constraints via input filter
        $newdata instanceof input  or  $newdata = new input($newdata, "\$newdata");
        $newkeys = $newdata->keys();
        $newdata->nocontrol->trim->always();
        $newdata = array(
                 "name"     => $newdata->proj_name         ->length…3…33["name"],
                 "homepage" => $newdata->ascii->trim->http  ->length…250["homepage"],
                 "download" => $newdata->ascii->trim->url   ->length…250["download"],
                 "image"    => $newdata->ascii->trim->http  ->length…250["image"],
           "autoupdate_url" => $newdata->ascii->trim->http  ->length…250["autoupdate_url"],
                 "title"    => $newdata->text               ->length…100["title"],
              "description" => $newdata                    ->length…2000["description"],
                 "license"  => $newdata->words               ->length…30["license"],
                 "tags"     => $newdata->words->strtolower  ->length…150["tags"],
                 "version"  => $newdata->words               ->length…30["version"],
                 "state"    => $newdata->words->strtolower   ->length…30["state"],
                 "scope"    => $newdata->words->strtolower   ->length…30["scope"],
                 "changes"  => $newdata->text              ->length…2000["changes"],
                "submitter" => $newdata->text               ->length…100["submitter"],
                 "urls"     => $newdata                    ->length…2000["urls"],
                 "lock"     => $newdata->raw               ->length…2000["lock"],
        "autoupdate_module" => $newdata->id                  ->length…30["autoupdate_module"],
         "autoupdate_regex" => $newdata->raw               ->length…2000["autoupdate_regex"],
        );

        // Declare some automatic system flags
        $auto_flags = array(
            // Hidden releases are either tagged that way, or have too short of a `changes:` summary
            "hidden" => intval(stripos($newdata["scope"], "hidden")),
            // Increase associated publishing timestamp if hereunto unknown release
            "t_published" => $this->exists($newdata["name"], $newdata["version"]) ?: time(),
             // Whereas the update timestamp is always adapted
            "t_changed" => time(),
        );

        // Array excerpt if input didn't come from page_submit but Autoupdate or API
        if ($partial) {
            $newdata = array_intersect_key($newdata, array_flip($newkeys));
        }

        // Merge and apply input
        $this->exchangeArray(array_merge(
             $this->getArrayCopy(),   // any previous/extraneous control data is kept
             $prefill_flags,
             $newdata,
             $auto_flags,
             $override_flags
        ));
        
        // chainable call
        return $this;
    }

    
    /**
     * Store current data bag into `release` table.
     * Is to be invoked after ->update().
     *
     */
    function store($INSERT="INSERT") {
#db()->test = 1;
        $data = $this->getArrayCopy();
        return db("$INSERT INTO release (:?) VALUES (::)", $data, $data);
    }


    /**
     * Retrieve latest published release version.
     *
     * @return array
     */
    static function latest($name) {
        $r = db("
            SELECT *
              FROM release
             WHERE name = ?
             ORDER BY t_published DESC, t_changed DESC
             LIMIT 1", $name
        );
        return $r ? $r->fetch() : array();
    }


    /**
     * Check for existence of specific release version,
     * return t_published timestamp if.
     *
     * @return int
     */
    static function exists($name, $version) {
        $r = db("
            SELECT t_published
              FROM release
             WHERE name=? AND version=?",
            $name, $version
        );
        $t = $r ? $r->fetchColumn(0) : 0;
       # print "<b>exists=$t</b>\n";
        return intval($t);
    }


    /**
     * Check current login against `lock` field,
     * which can be a comma-separated list of OpenID handles, or
     * contain password_hash() literals for API auth.
     *
     */
    function permission($data, $authwith) {
        global $moderator_ids;

        return empty($data["lock"])
            or in_array($authwith, array_merge(p_csv($data["lock"]), $moderator_ids));
    }

}






?>