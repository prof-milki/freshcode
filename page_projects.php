<?php
/**
 * type: page
 * title: Project detail view
 * description: List project entry with all URLs and releases
 * license: AGPL
 * version 0.5
 * 
 * Shows:
 *   → General project description
 *   → Sidebar with project links, submitter, management links, social share count
 *   → Release history and changelogs
 * Adds:
 *   → RSS/Atom links to header template
 *
 */

// Current project id
$name = $_REQUEST->proj_name["name"];

#-- Fetch project/release entries
$releases = db("
        SELECT *, MAX(t_changed)
          FROM release
         WHERE name = ?
           AND flag < 5
           AND NOT deleted
      GROUP BY version
      ORDER BY t_published DESC, t_changed DESC
", $name);


// Retrieve most current project version
if ($entry = $releases->fetch()) {


    // prepare HTML header with injected RSS/Atom links
    $header_add = "<link rel=alternate type=application/rss+xml href=/feed/$name.rss>\n"
                . "<link rel=alternate type=application/atom+xml href=/feed/$name.atom>\n"
                . "<link rel=alternate type=json/vnd.freshcode.club href=/feed/$name.json>";
    $title = input::html($entry["title"]) . " - freshcode.club";
    include("template/header.php");


    // Show sidebar + long project description
    prepare_output($entry);
    include("template/projects_sidebar.php");
    include("template/projects_description.php");


    #-- Display all other released versions
    ?> <article class=release-list>  <h4>Recent Releases</h4> <?php
    do {
        prepare_output($entry);
        include("template/projects_release_entry.php");
    }
    while ($entry = $releases->fetch());
    ?> </article> <?php

    // html tail
    include("template/bottom.php");
}


// No entry found
else {
    exit($error = "Project name doesn't exist." and include("page_error.php"));
}


?>