<?php
/**
 * api: cli
 * title: import spool
 * description: randomly publish new entries from incoming/ spool
 * version: 0.3
 * category: source
 * type: cron
 * x-cron: 05,25,45 * * * *
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


// elapsed time since last published project/version
$diff_t_pub = time()
            - db("SELECT t_published FROM release
                  WHERE NOT hidden AND NOT deleted
                  ORDER BY t_published DESC LIMIT 1")->t_published;

         // minimum pause between imports
$pause = 3.75

         // extra hour jump on weekends
       + 0.75 * (date("N") >= 6)

         // less delay when spool is reasonably filled
       - 0.5 * (count(glob("incoming/*")) >= 10)

         // add 33% chance of just skipping to next cron slot
       + 9.9 * (rand(0,100) < 33)

         // prevent minute-slot matching (alternate between HH:05, HH:25, HH:45)
       + 2.0 * ($diff_t_pub % 3600 < 5);


// if there's enough delay to last published entry, submit a new incoming/* release
if ($diff_t_pub > $pause * 3600) {
   insert_from_spool();
}



// fresh insert attempts only every 3.5 hours
function insert_from_spool() {

    // read filenames
    $files = array_diff( array_filter( glob("incoming/*"), "is_file"), ["incoming/.htaccess"]);
    // sort newest first
    $files = array_combine($files, array_map("filemtime", $files));
    asort($files);
    $files = array_keys($files);


    // loop over import files
    foreach ($files as $fn) {

        // parse RFC-style text format
        $p = parse_release_fields(file_get_contents($fn));
        
        // skip if entry exists
        $alt_fn = basename($fn) . "." . time();
        if (empty($p["name"]) or release::exists($p["name"], $p["version"])) {
            rename($fn, "incoming/incomplete/$alt_fn");
            continue;
        }
        
        // store new project/release entry
        $rel = new release($p["name"]);
        $rel->update($p, [], ["hidden"=>intval(!empty($p["hidden"])), "via"=>"spool"], TRUE);
        $rel->store();
        print_r($rel);
        rename($fn, "incoming/done/$alt_fn");

        // finish or continue importing another entry
        if (rand(0,27)) {
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
    $p = array_map(
        function ($s) {
             return preg_replace("/^\h+/m", "", ltrim($s));
        },
        array_combine($p[1], $p[2])
    );
    // description field does not need linebreaks at all (but changes, urls, etc. should retain them)
    if (!empty($p["description"])) {
        $p["description"] = input::spaces($p["description"]);
    }

    return $p;
}


