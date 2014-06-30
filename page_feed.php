<?php
/**
 * api: freshcode
 * title: json feeds
 * description: exchange protocol and per-project feeds
 * version: 1.0
 * license: CC-BY-SA
 * depends: json, db
 *
 * Generates /xfer stream and per-/project release feeds.
 * Both only JSON for now.
 * (No content-negotiation, as for RSS/ATOM a different
 * content summary probably made more sense.)
 *
 * This is still susceptible to changes. Currently freshcode.club
 * seems the only FM-reimplementation. But obviously the data
 * format should converge to facilitate proper synchronization.
 *
 */


/**
 * group and rename internal columns into feed structure
 *
 */

#-- general project description
function feed_project($row) {
    return array(
        "name" => $row["name"],
        "title" => $row["title"],
        "description" => $row["description"],
        "homepage" => $row["homepage"],
        "license" => $row["license"],
        "tags" => $row["tags"],
        "image" => $row["image"],
        "submitter" => $row["submitter"],
        "urls" => p_key_value($row["urls"]),
    );
}

#-- version/release blocks
function feed_release($row) {
    return array(
        "version" => $row["version"],
        "state" => $row["state"],
        "scope" => $row["scope"],
        "changes" => $row["changes"],
        "download" => versioned_url($row["download"], $row["version"]),
        "published" => date(DateTime::ISO8601, $row["t_published"]),
    );
}

#-- exchange data
function feed_xfer($row) {
    return array(
        "hidden" => $row["hidden"],
        "changed" => date(DateTime::ISO8601, $row["t_published"]),
        "autoupdate_module" => $row["autoupdate_module"],
        "autoupdate_url" => $row["autoupdate_url"],
        "autoupdate_regex" => $row["autoupdate_regex"],
        // following fields will not be transferred for privacy reasons
       # "submitter_openid" => $row["submitter_openid"],
       # "lock" => $row["submitter_lock"],
    );
}




#-- something was requested
if ($name = $_GET->proj_name["name"]) {

    $feed = array(
        "\$feed-origin" => "http://freshcode.club/",
        "\$feed-license" => "CC-BY-SA 3.0",
    );


    #-- exchange data
    if ($name == "xfer") {
        $feed["releases"] = array();
        
        $r = db("SELECT * FROM release_versions LIMIT ?", $_GET->int->default…100->range…5…1000["num"]);
        while ( $row = $r->fetch() ) {
            $feed["releases"][] = feed_project($row) + feed_release($row) + feed_xfer($row);
        }
    }

    
    #-- per project
    else {
        $r = db("SELECT * FROM release_versions WHERE name=? LIMIT 10", $name);
        while ( $row = $r->fetch() ) {

            // project description
            isset($feed["releases"]) or $feed += feed_project($row) + array("releases" => array());

            // versions
            $feed["releases"][] = feed_release($row);
        }
    }

    header("Content-Type: json/vnd.freshcode.club; charset=UTF-8");
    exit(json_encode($feed, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
}


#-- else print an info page
else {
    include("layout_header.php");
    ?>
    <section id=main>
       <h4>Feeds</h4>
       <p>
          You can get any projects <b>releases.json</b> feed using<br><tt>http://freshcode.club/feed/<i>projectname</i></tt>.
       </p>
       <p>
          Whereas using <i>xfer</i> will return the whole recent changes list.
       </p>
    <?php
    include("layout_bottom.php");
}

