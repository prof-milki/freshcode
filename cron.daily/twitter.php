<?php
/**
 * api: cli
 * title: Twitter bridge
 * description: Posts recent releases on twitter
 * version: 0.1
 * category: rpc
 * type: cron
 * x-cron: 50 * * * *
 *
 * Summarize new releases for Twitter feed.
 * Currently using `twidge` (pre-configured in $HOME),
 * which doesn't support //t.co/ inline images yet.
 *
 */


chdir(dirname(__DIR__));
include("config.php");



/**
 * Releases within the last hour
 *
 */
$rel = db("
    SELECT *
      FROM release_versions
     WHERE t_published >= ?
    ", time()-7300
)->fetchAll();



// query recent/previous status messages
$prev = twit("lsarchive");

// condense individual tweets
foreach ($rel as $row) {

    // skip existing
    if (is_int(strpos($prev, $row["title"]))) {
    print "skip($row[name]) ";
        continue;
    }
    
    // homepage
    if (empty($row["homepage"]) or strlen($row["homepage"] > 80)) {
        $row["homepage"] = "http://freshcode.club/projects/$row[name]";
    }

    // prepare post
    $msg = "$row[title] $row[version] released. $row[homepage]";
    $msg = preg_replace("/\s+/", " ", $msg);
    
    // add tags
    $tags = p_csv($row["tags"]);
    shuffle($tags);
    foreach ($tags as $tag) {
        $tag = preg_replace("/^(\w)\+\+/", "\\1pp", $tag);
        $tag = preg_replace("/-/", "_", $tag);
        $msg .= " #$tag";
    }
    
    // cut to limit
    while (strlen($msg) > 140) {
        $msg = preg_replace("/\s\S+$/s", "", $msg);
    }
    
    // send
    print_r("$msg\n");
    twit("update", $msg);
}




/**
 * Invoke cmdline twitter client
 *
 */
function twit() {
    $cmd = "twidge";
    foreach (func_get_args() as $param) {
        $cmd .= " " . escapeshellarg($param);
    }
    return ` $cmd `;
}


?>