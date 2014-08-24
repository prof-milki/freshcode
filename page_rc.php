<?php
/**
 * type: page
 * title: Recent Changes
 * description: Provides a revision diff
 * version: 0.1
 *
 * Show differences between incremental revisions.
 * (To detect sneak spam while we're not requiring OpenID logons.)
 *
 */


// page header
include("template/header.php");
?><section id=main><?php


/**
 * Fields to inspect/diff.
 * Using different sets depending on publicness.
 *
 */
if (TRUE) {  // Public
    $fields = "name,t_changed,title,version,t_published,license,tags,state,scope,homepage,download,urls,description,changes,submitter";
}
if ($_SESSION["openid"]) {  // For logged in users
    $fields .= ",autoupdate_module,autoupdate_url,autoupdate_regex";
}
if (in_array($_SESSION["openid"], $moderator_ids)) {   // Reveal control/privacy-related fields only to moderators
    $fields .= ",submitter_image,submitter_openid,lock,hidden,deleted";
}


/**
 * Prepare SQL field aliases
 * (because sqlite-pdo driver doesn't support it)
 *
 *   description →  crnt.description AS crnt_description
 *   description →  prev.description AS prev_description
 *
 */
$crnt_fields_alias = preg_replace("/\w+/", "crnt.$0 AS crnt_$0", $fields);
$prev_fields_alias = preg_replace("/\w+/", "prev.$0 AS prev_$0", $fields);
$prev_fields_empty = preg_replace("/\w+/", "   NULL AS prev_$0", $fields);

// Also turn CSV list into array
$fields = array_diff(str_getcsv($fields), array("t_changed"));



/**
 * Retrieve two consecutive revisions each.
 *
 *
 */
$rc = db("

    SELECT $crnt_fields_alias, $prev_fields_alias,
           MAX(prev.t_changed)
      FROM release crnt
 LEFT JOIN release prev
        ON crnt.name = prev.name
     WHERE prev.t_changed < crnt.t_changed
--           ( SELECT MAX(t_changed)
--               FROM release
--              WHERE name = crnt.name
--                AND t_changed < crnt.t_changed )
  GROUP BY crnt.name, crnt.t_changed
  ORDER BY crnt.t_changed DESC
     LIMIT 100*?, 100

", $_REQUEST->int->range…1…10["n"] - 1);



/**
 * Iterate over all results to display differences
 *
 */
foreach ($rc as $entry) {

    #-- Prepare fields
    $name = $entry["crnt_name"];
    $date = strftime("%Y-%m-%d %H:%M:%S", $entry["crnt_t_changed"]);
    $time_diff =  $entry["crnt_t_changed"] - $entry["prev_t_changed"];

    #-- Table
    print "\n\n<table class=rc><tr><th><a href=/projects/$name>$name</a></th><th>$date <small>¤$time_diff</small> <span class=funcs><a href=/submit/$name>edit</a> <a href=/admin/$name>admin</a></span></th></tr>\n";
    foreach ($fields as $fn ) {

        // Diff only if there are differences, obviously
        if ($entry["prev_$fn"] !== $entry["crnt_$fn"]) {
        
            $diff = pdiff::htmlDiff($entry["prev_$fn"], $entry["crnt_$fn"]);
            print "<tr><td>$fn</td><td class=trimmed>$diff</td></tr>\n";
        }
    }
    print "</table>\n";
}


// page footer
include("template/bottom.php");


?>