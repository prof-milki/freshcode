<?php
/**
 * type: page
 * title: Browse Projects by Name
 * description: Alphabetical project lists
 * version: 0.2
 *
 * Minimal column styling, just project base names used,
 *
 */


include("template/header.php");
?><section id=main>
</article>
<aside class=pagination-links style="margin-bottom: 20pt;">
  <a href="names/AE">A-E</a>
  <a href="names/FH">F-H</a>
  <a href="names/IN">I-N</a>
  <a href="names/OQ">O-Q</a>
  <a href="names/RT">R-T</a>
  <a href="names/UZ">U-Z</a>
  <a href="names/09">0-9</a>
</aside>
<article class=project-name-columns><?php


// Letter slicing (AZ or 09)
$letters = $_GET->name->length…2->strtolower->default("name", "ae");
$letters = range($letters[0], $letters[1]);

// Fetch project names from letter group
$names = db("
   SELECT DISTINCT name
     FROM release
    WHERE substr(name, 1, 1) IN (??)
 ORDER BY name
", $letters);

// Show
foreach ($names as $id) {
    print "<a href=/projects/$id[name]><img src='img/screenshot/$id[name].jpeg' width=100 height=75 align=top> $id[name]</a> <br> ";
}


include("template/bottom.php");


?>