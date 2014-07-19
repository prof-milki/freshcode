<?php
/**
 * type: page
 * title: Error info
 * description: Generic error page
 * version: 0.1
 * license: -
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


include("template/header.php");
?> <section id=main> <?php

print "<h2>Error</h2>\n";

print isset($error) ? "<p>$error</p>" : "<p>Some problem occured (entry not accessible etc.)</p>";


include("template/bottom.php");

?>