<?php
/**
 * title: import spool
 * description: randomly publish new entries from incoming/ spool
 * version: 0.2
 * type: cron
 * api: cli
 * category: source
 *
 * Cron runs this hourly.
 * Script checks for 3.5 hour delay between published releases.
 * Then randomly inserts 1, sometimes 2, seldomly 3 new entries.
 *
 * The incoming/ text files are simple `key: value` lists, each
 * naming one database field. Multi-line entries just should start
 * with a space (or not `^\w+:`) to be recognized.
 *
 */

chdir(dirname(__DIR__));
include("./config.php");


// import delays, 33% chance of skipping to next cron slot
if (rand(0,100) < 33) {
    return;
}
// minimum pause between imports
$pause = 3.35
       + (date("N") >= 6)
       - (count(glob("incoming/*")) >= 7);
print $pause;
// delay to last published project/version
$last_release = db("SELECT t_published FROM release WHERE NOT hidden AND NOT deleted ORDER BY t_published DESC LIMIT 1")->t_published;
if (time() < ($last_release + $pause * 3600)) {
   return;
}


// fresh insert attempts only every 3.5 hours
if (TRUE) {

    // read filenames, sort newest first
    $files = array_filter(glob("incoming/*"), "is_file");
    $files = array_diff($files, ["incoming/.htaccess"]);
#    shuffle($files);
    $files = array_combine($files, array_map("filemtime", $files));
    asort($files);
    $files = array_keys($files);
#   print_r($files); exit;


    // loop over import files
    foreach ($files as $fn) {

        // parse RFC-style text format
        $p = parse_release_fields(file_get_contents($fn));
        
        // skip if entry exists
        if (empty($p["name"]) or release::exists($p["name"], $p["version"])) {
            rename($fn, "incoming/incomplete/" . basename($fn));
            continue;
        }
        
        // store new project/release entry
        $rel = new release($p["name"]);
        $rel->update($p, [], ["hidden"=>intval(!empty($p["hidden"]))], TRUE);
        $rel->store();
        print_r($rel);
        rename($fn, "incoming/done/" . basename($fn));

        // finish or continue
        if (rand(0,9)) {
           exit;
        }
        else {
           continue;
        }
    }

}


// extract from text file
function parse_release_fields($txt) {

    // key: text lines
    preg_match_all("/^(\w+):\h*((?:\V+|\R(?!\w+:))+)/m", $txt, $p);
    
    // remove leading empty space
    return
        array_map(function ($s) {
            return preg_replace("/^\h+/m", "", ltrim($s));
        },
        array_combine($p[1], $p[2])
    );
}


