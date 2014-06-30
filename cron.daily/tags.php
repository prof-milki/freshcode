<?php
/**
 * api: cron
 * title: Update `tags` table
 * description: Splits out tags from according column in project `release`.
 * version: 0.1
 *
 *
 * Manually update tags table.
 *
 */

chdir(dirname(__DIR__)); 
include("config.php");

/**
 * Scan each project,
 * split up `tags` as CSV and just fille up according tags table.
 *
 */
foreach (db("SELECT * FROM release_versions GROUP BY name") as $entry) {

    print_r($entry);
    
    $name = $entry->name;
    $tags = array_slice(p_csv($entry->tags), 0, 7);

    db("DELETE FROM tags WHERE name=?", $name);
    foreach ($tags as $t) {
        db("INSERT INTO tags (name, tag) VALUES (?, ?)", $name, $t);
    }

}
