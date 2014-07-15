<?php
/**
 * type: page
 * title: Project detail view
 * description: List project entry with all URLs and releases
 * license: AGPL
 * version 0.4
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


// HTML header with injected RSS/Atom links
$header_add = "<link rel=alternate type=application/rss+xml href=/feed/$name.rss>\n"
            . "<link rel=alternate type=application/atom+xml href=/feed/$name.atom>\n"
            . "<link rel=alternate type=json/vnd.freshcode.club href=/feed/$name.json>";
$title = "$name - freshcode.club";
include("template/header.php");



#-- Fetch most current project/release entry
$releases = db("
    SELECT *
      FROM release_versions
     WHERE name = ?
       AND NOT deleted
     LIMIT 1
", $name);

// Show sidebar + long project description
if ($entry = $releases->fetch()) {
    prepare_output($entry);
    include("template/projects_sidebar.php");
    include("template/projects_description.php");
}



#-- Retrieve all released versions
$releases = db("
    SELECT *, MAX(t_changed)
      FROM release
     WHERE name = ?
       AND flag < 5
       AND NOT deleted
  GROUP BY version
  ORDER BY t_published DESC, t_changed DESC
", $name);

?> <article class=release-list>  <h4>Recent Releases</h4> <?php
while ($entry = $releases->fetch()) {
    prepare_output($entry);
    include("template/projects_release_entry.php");
}
?> </article> <?php


// html tail
include("template/bottom.php");



?>