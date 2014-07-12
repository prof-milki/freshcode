<?php
/**
 * type: page
 * title: Search function
 * description: Scans packages for description, tags, license, user names
 * license: AGPL
 * version 0.1
 * 
 * Builds a search query from multiple input params:
 *   → ?user=
 *   → ?tags[]= or ?tag=
 *   → ?license=
 *   → ?q= for actual text search
 *
 */



include("template/header.php");
?> <section id=main> <?php


// Display form
if ($_GET->no("tag,tags,user,license,q")) {

    include("template/search_form.php");

}

// Actual search request
else {

    #-- Collect search terms
    $WHERE = "";
    $params = array();

    // List from ?tags[]= or single ?tag=
    if ($tags = array_filter(array_merge($_GET->array->words["tags"], $_GET->words->p_csv["tag"]))) {
        $WHERE .= " AND name IN (SELECT name FROM tags WHERE tag IN (??))";
        $params[] = $tags;
    }
    // Select specific ?user=
    if ($user = $_GET->words["user"]) {
        $WHERE .= " AND submitter LIKE ?";
        $params[] = "$user%";
    }
    // Only ?license= results
    if ($license = $_GET->words["license"]) {
        $WHERE .= " AND license = ?";
        $params[] = $license;
    }
    // And finally the actual ?q= search string // Note switch to FTS
    if ($q = $_GET->text["q"]) {
        $WHERE .= " AND description LIKE ?";
        $params[] = "%$q%";
    }


    // Run SQL
    #db()->test = 1;
    $db_arg = db(); // Bypass hybrid db() function to directly invoke $db{} wrapper with list of params
    $result = $db_arg("
        SELECT release.name AS name, title, SUBSTR(description,1,500) AS description,
               version, image, homepage, download, submitter, release.tags AS tags,
               license, state, t_published, flag, hidden, deleted, MAX(t_changed)
          FROM release
         WHERE NOT deleted AND flag < 5
      GROUP BY release.name
        HAVING 1=1 $WHERE
      ORDER BY t_published DESC, t_changed DESC
         LIMIT 100
    ", $params);



    // Show sidebar + long project description
    foreach ($result as $entry) {
        prepare_output($entry);
        include("template/search_entry.php");
    }

}


include("template/bottom.php");

?>