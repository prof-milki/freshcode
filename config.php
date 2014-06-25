<?php
/**
 * api: freshcode
 * title: Freshcode.club config
 * description: initialization code
 * version: 0.1
 * plugin-register: include_once("$FN");
 * 
 *
 */

define("INPUT_QUIET", 1);
define("INPUT_DIRECT", "trim");
include_once("input.php");

include_once("db.php");
db("connect", "sqlite:freshcode.db");

define("LOGIN_REQUIRED", 1);
define("HTTP_HOST", $_SERVER->id["HTTP_HOST"]);
include_once("deferred_openid_session.php");


// List of moderator OpenID handles
$moderator_ids = array(
);



#-- Additional input filters

// String from 3 to 33 chars
function length_3to33($s) {
    return (strlen($s) >= 3 and strlen($s) <= 33) ? $s : NULL;
}
// Length of strings in arrays > 100
function min_length_100($a) {
    return array_sum(array_map("strlen", $a)) >= 100;
}


#-- Template helpers

// Wrap tag list into links
function wrap_tags($tags, $r="") {
    foreach (str_getcsv($tags) as $id) {
        $id = trim($id);
        $r .= "<a href=\"/tags/$id\">$id</a>";
    }
    return $r;    
}
// Return DAY MONTH and TIME or YEAR for older entries
function date_fmt($time) {
    $lastyear = time() - $time > 250*24*3600;
    return strftime($lastyear ? "%d %b %Y" : "%d %b %H:%M", $time);
}

// Substitute `$version` placeholders in URLs
function versioned_url($url, $version) {
    return preg_replace("/([\$%])(version|Version|VERSION)\b\\1?/", $version, $url);
}

// General output preparation
function prepare_output(&$entry) {
    $entry["download"] = versioned_url($entry["download"], $entry["version"]);
    $entry["image"] or $entry["image"] = "/img/nopreview.png";
    $entry["formatted_date"] = date_fmt($entry["t_published"]);
    $entry = array_map("input::_html", $entry);
}

#-- Data handling
function p_key_value($str) {
#    preg_match_all("~ [%\$]?(\w+)  \s*[:=]+\s*  (\S+) (?<![,.;]) ~imsx", $str, $m);
    preg_match_all("~ (\w+)  \s*=\s*  (\S+) ~imsx", $str, $m);
    return array_combine($m[1], $m[2]);
}



?>