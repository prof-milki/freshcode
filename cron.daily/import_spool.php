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
 * Then randomly inserts 1 or 2 new entries.
 *
 * The incoming/ text files are simple `key: value` lists, each
 * naming one database field. Multi-line entries just should start
 * with a space (or not `^\w+:`) to be recognized.
 *
 */

chdir(dirname(__DIR__));
include("./config.php");


// fresh insert attempts only every 3.5 hours
$last_release = db("SELECT t_published FROM release ORDER BY t_published DESC LIMIT 1")->t_published;
if (time() > $last_release + 3.5*24*3600) {


    // check files
    foreach (glob("incoming/*") as $fn) {

        // parse RFC-style text format
        $p = parse_release_fields(file_get_contents($fn));
        
        // skip if entry exists
        if (empty($p["name"]) or release::exists($p["name"], $p["version"])) {
            continue;
        }
        
        // randomize import times;
        if (!rand(0,3)) {
        
            // store new project/release entry
            $rel = new release($p["name"]);
            $rel->update($p);
            $rel->store();
            print_r($rel);

            // finish or continue
            if (rand(0,5)) {
               exit;
            }
            else {
               continue;
            }
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

