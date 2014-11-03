<?php
/**
 * api: cron
 * title: Update `tags` table
 * description: Splits out tags from according column in project `release`.
 * version: 0.3
 *
 * Manually update tags table.
 *   - Splits up comma separated release.tags field
 *   - Maximum of 10 tags each
 *   - Populates separate tags table with name=>tag list.
 *
 */

chdir(dirname(__DIR__)); 
include("config.php");

/**
 * Scan each project,
 * split up `tags` as CSV and just fille up according tags table.
 *
 */
foreach (db("SELECT name, tags, MAX(t_changed) FROM release_versions GROUP BY name")->fetchAll() as $entry) {

    $name = $entry["name"];
    $tags = array_slice(array_filter(p_csv($entry["tags"])), 0, 10);

    db("DELETE FROM tags WHERE name=?", $name);
    foreach ($tags as $t) {
        db("INSERT INTO tags (name, tag) VALUES (?, ?)", $name, $t);
    }

}
