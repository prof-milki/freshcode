<?php
/**
 * type: page
 * title: Project release listing
 * description: Front page for listing recently submitted projects and their releases
 * version: 0.3
 * license: AGPL
 *
 * Frontpage.
 * Just shows the most recent projects and their released versions.
 *
 * Shows:
 *   → Recent projects and their released versions.
 *   → Visually trimmed descriptions and changelogs.
 *   → Small boxed tags.
 * Sidebar:
 *   → Newsfeeds (e.g. linux.com, /r/linux)
 * HTML:
 *   → RSS/Atom links for update feed comprised of all projects.
 *
 */


$header_add = "<link rel=alternate type=application/rss+xml href=/feed/xfer.rss>\n<link rel=alternate type=application/atom+xml href=/feed/xfer.atom>";
include("template/header.php");
include("template/index_sidebar.php");
?> <section id=main> <?php


// query projects
$page_no = $_GET->int->range…1…100["n"];
$releases = db("
    SELECT *
      FROM release_versions
     WHERE flag < 5
       AND NOT hidden
     LIMIT 40
    OFFSET 40*?
", $page_no - 1);


// Convert entries to HTML output
foreach ($releases as $entry) {
    prepare_output($entry);
    include("template/index_project.php");
}

// Add pseudo pagination for further pages.
pagination($page_no, "n");


include("template/bottom.php");

?>