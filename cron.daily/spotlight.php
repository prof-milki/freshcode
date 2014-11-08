<?php
/**
 * api: cli
 * title: Create random picks for project spotlight
 * description: Randomly picks out a few projects for the footer
 * version: 0.1
 * category: template
 * type: cron
 * x-cron: 20 *\/6 * * *
 *
 * Picks three projects for display in footer as projects of the day.
 * (Actually renewed three times a day.)
 *
 */

chdir(dirname(__DIR__)); 
include("config.php");


/**
 * Scan each project,
 * pick random three.
 *
 */
$r = db("
     SELECT name, title, SUBSTR(description, 0, 150) AS description,
     MAX(t_changed) AS t FROM release
     GROUP BY name
     ORDER BY random() 
     LIMIT 3;
"); 

// combine into HTML blob
$html = ""; 
foreach ($r as $entry) {

    $entry = array_map("input::_html", $entry);
#    $entry["description"] = preg_replace("/\.[^.]*$|[,;][^,;]*$|\S*$/", "", $entry["description"]);
    $html .= <<<EOF
   <a class=project-spotlight href="/projects/$entry[name]">
      <img src="/img/screenshot/$entry[name].jpeg" width=120 height=90 alt=$entry[name]>
      <b> $entry[title] </b>
      <small class=description>$entry[description]</small> 
   </a>

EOF;
}

// store as template
file_put_contents("./template/spotlight.htm", $html);

