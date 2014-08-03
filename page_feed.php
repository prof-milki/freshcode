<?php
/**
 * api: freshcode
 * title: json feeds
 * description: exchange protocol and per-project feeds
 * version: 1.1
 * license: CC-BY-SA
 * depends: php:json, feeder
 *
 * Generates /xfer stream and per-/project release feeds.
 * Returns JSON (interchange format) and RSS or Atom feeds.
 *
 * The URL schemes:
 *    http://freshcode.club/feed/projectname    (.json optional)
 *    http://freshcode.club/feed/projectname.rss
 *    http://freshcode.club/feed/projectname.atom
 * For the complete site update list:
 *    http://freshcode.club/feed/xfer         (.json/.atom/.rss)
 *
 * No Content-Negotiation here, as nobody is even bothering
 * anymore. The .htaccess dispatching adds the ?ext=rss if an
 * extension (.json / .atom / .rss) was appended.
 *
 *
 * JSON FORMAT
 *
 *   Is still susceptible to changes. Currently freshcode.club
 *   seems the only FM-reimplementation. But obviously the data
 *   format should converge to facilitate proper synchronization.
 *
 *   /feed/xfer also doesn't provide the raw DB contents. OpenID
 *   handles are stripped, and personally identifyable infos 
 *   dropped (e.g. gravatar email).
 *   Otherwise it's similar to the internal database structure.
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
        "published" => gmdate(DateTime::ISO8601, $row["t_published"]),
    );
}

#-- exchange data
function feed_xfer($row) {
    return array(
        "hidden" => $row["hidden"],
        "changed" => gmdate(DateTime::ISO8601, $row["t_published"]),
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


    #-- Output JSON
    if ($ext = $_GET->name->default…json["ext"] == "json") {
        header("Content-Type: json/vnd.freshcode.club; charset=UTF-8");
        exit(json_encode($feed, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }

    
    #-- Else convert into RSS or Atom
    else {

        /**
         * It's obviously super long-winded to restructure the JSON xfer
         * or per-project data into RSS/Atom snippets here afterwards.
         *
         * @todo: restructure
         *
         */

        $f = new Feeder();
        $f->channel()->setfromarray(array(
            "title"       => "$name",
            "description" => "Open Source project updates",
            "author"      => "freshcode.club",
            "license"     => $feed["\$feed-license"],
            "icon"        => "http://freshcode.club/img/changes.png",
            "logo"        => "http://freshcode.club/logo.png",
        ));
        
        foreach ($feed["releases"] as $i=>$row) {
            $f->entry($i, new FeedEntry(@array(
                "title"   => ($row["title"] ?: $feed["title"]) . " $row[version]",
                "published" => $row["published"],
                "author"  => $row["submitter"] ?: $feed["submitter"],
                "content" => $row["changes"],
                "permalink" => $row["homepage"] ?: $feed["homepage"],
            )));
        }

        #-- Output
        $o = ($ext == "atom") ? new AtomFeed() : new Rss20Feed();
        $o->output($f);
    }
}


#-- else print an info page
else {
    include("template/header.php");
    ?>
    <section id=main>
       <h4>Feeds</h4>
       <p>
          You can get any projects <b>releases.json</b> feed using
          <ul>
             <li> <tt>http://freshcode.club/feed/<em>projectname</em><var style="color: #ccc">.json</var></tt>
          </ul>
          Alternatively as RSS/Atom feed
          <ul>
             <li> <tt>http://freshcode.club/feed/<em>projectname</em>.rss</tt>
             <li> <tt>http://freshcode.club/feed/<em>projectname</em>.atom</tt>
          </ul>
       </p>
       <p>
          To get all project updates instead use
          <ul>
             <li> <tt>http://freshcode.club/feed/<b>xfer</b><var style="color: #ccc">.json</var></tt>
             <li> <tt>http://freshcode.club/projects.rss</tt>
             <li> <tt>http://freshcode.club/projects.atom</tt>
          </ul>
       </p>
       <p>
          JSON feeds are using a post-1.0 MIME type of <em>json/vnd.freshcode.club</em> for now.
       </p>
    <?php
    include("template/bottom.php");
}

