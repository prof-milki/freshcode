<?php
/**
 * type: page
 * title: Search function
 * description: Scans packages for description, tags, license, user names
 * license: AGPL
 * version 0.2
 * 
 * Builds a search query from multiple input params:
 *   → ?user=
 *   → ?tags[]= or ?tag=
 *   → ?trove[]= for ANDed tags
 *   → ?license=
 *   → ?q= for actual text search
 *
 */



include("template/header.php");
?> <section id=main> <?php


// Display form
if ($_GET->no("tag,tags,trove,user,license,q")) {

    include("template/search_form.php");

}

// Actual search request
else {

    // Wrap search params into arrays
    $tags = array_filter(array_merge($_GET->array->words["tags"], $_GET->words->p_csv["tag"]));
    $trove = $_GET->array->words["trove"] and $trove = [$trove, count($trove)];
    $user = $_GET->words["user"] and $user = ["$user%"];
    $license = $_GET->array->words["license"] and $license = array_filter($license);
    $search = $_GET->text["q"] and $search = ["%$search%"];
    // primary search term (for exact matches)
    $primary = (count($tags) == 1) ? current($tags) : ($search ? $_GET->text["q"] : NULL);

    // Run SQL
#   db()->test = 1;
    $result = db("
        SELECT release.name AS name, title, SUBSTR(description,1,500) AS description,
               version, image, homepage, download, submitter, submitter_image,
               release.tags AS tags, scope, changes,
               license, state, t_published, flag, hidden, deleted, MAX(t_changed)
          FROM release
         WHERE NOT deleted AND flag < 5
      GROUP BY release.name
        HAVING 1=1
               :*  :*  :*  :*  :*  :*
      ORDER BY t_published DESC, t_changed DESC
         LIMIT 100 ",
            // expr :* placeholders only interpolate when inner array contains params
            [" AND description LIKE ? ",  $search],
            [" AND submitter LIKE ? ", $user],
            [" AND license IN (??) ",   $license],
            [" AND name IN (SELECT name FROM tags WHERE tag IN (??)) ", $tags],
            [" AND name IN (SELECT name FROM tags WHERE tag IN (??)
               GROUP BY name HAVING COUNT(tag) = 1*?) ", $trove],
            // project name == tags
            [" OR name IN (??)", $tags]
    );



    // Show sidebar + long project description
    foreach ($result as $entry) {
        prepare_output($entry);
        
        // show project listing for exact match or search entry
        if ($entry["name"] == $primary) {
            include("template/index_project.php");
            print "<br>";
        }
        else {
            include("template/search_entry.php");
        }
    }

}


include("template/bottom.php");

?>