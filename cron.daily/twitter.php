<?php
/**
 * api: cli
 * title: Twitter bridge
 * description: Posts recent releases on twitter
 * version: 0.2
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
    ", time()-11200
)->fetchAll();



// query recent/previous status messages
$prev = twurl("statuses/user_timeline.json?count=30");
$prev = twurl_text_urls($prev);
print $prev;

// condense individual tweets
foreach ($rel as $row) {

    // skip existing
    if (tweet_exists($prev, $row)) {
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
    
    // check for project Twitter= URL, add @mention
    if (preg_match("~https?://twitter.com/@?(\w{2,15})~", $row["urls"], $uu)) {
        $msg .= " @$uu[1]";
    }
    
    // add tags
    $tags = p_csv($row["tags"]);
    shuffle($tags);
    foreach (array_slice($tags, 0, 5) as $tag) {
        $tag = preg_replace("/^(\w)\+\+/", "\\1pp", $tag);
        $tag = preg_replace("/-/", "_", $tag);
        $msg .= " #$tag";
    }
    
    // check for tweet size limit (140 minus URL-length [22] for appended image)
    while (tweet_length($msg) > 118) {
        $msg = preg_replace("/\s\S+$/s", "", $msg);
    }
    
    // send
    print_r("\ntweet($msg) ");
    print_r(twurl_with_img($msg, "img/screenshot/$row[name].jpeg"));
}
print "\n";



/**
 * twurl API
 *
 */
function twurl($api) {
   return json_decode(exec("twurl '/1.1/$api'"), TRUE);
}


/**
 * Send MSG+IMG via twurl
 *
 */
function twurl_with_img($msg, $img) {
   $msg = escapeshellarg($msg);
   return exec("twurl -X POST '/1.1/statuses/update_with_media.json' --file $img --file-field 'media[]' -d status=$msg");
}


/**
 * Calculate tweet size without URLs.
 * 
 */
function tweet_length($msg) {
    $msg = preg_replace("~http://\S+(?<![,.])~", "http://t.co/abcDEFGHIJ", $msg);
    return strlen($msg);
    
}


/**
 * Collect `text:` and `expanded_urls:`
 *
 */
function twurl_text_urls($json, $text="") {
    foreach ($json as $tweet) {
        $text .= "{$tweet['text']} → {$tweet['entities']['urls'][0]['expanded_url']}\n";
    }
    return $text;
}
// Compare titles and homepage links with previous feeds.
function tweet_exists($prev, $row) {
    # escape
    $title = preg_quote($row["title"], "~");
    $homepage = preg_quote($row["homepage"], "~")
    or $homepage = "http://freshcode.club/projects/$row[name]";
    # check mentions
    return
        preg_match("~^$title~mi", $prev)
      or
        preg_match("~$homepage~", $prev);
}

?>