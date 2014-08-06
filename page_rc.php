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

");



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
        
            $diff = htmlDiff($entry["prev_$fn"], $entry["crnt_$fn"]);
            print "<tr><td>$fn</td><td class=trimmed>$diff</td></tr>\n";
        }
    }
    print "</table>\n";
}


// page footer
include("template/bottom.php");


?><?php
/*
    Paul's Simple Diff Algorithm v 0.1
    (C) Paul Butler 2007 <http://www.paulbutler.org/>
    May be used and distributed under the zlib/libpng license.
    
    This code is intended for learning purposes; it was written with short
    code taking priority over performance. It could be used in a practical
    application, but there are a few ways it could be optimized.
    
    Given two arrays, the function diff will return an array of the changes.
    I won't describe the format of the array, but it will be obvious
    if you use print_r() on the result of a diff on some test data.
    
    htmlDiff is a wrapper for the diff command, it takes two strings and
    returns the differences in HTML. The tags used are <ins> and <del>,
    which can easily be styled with CSS.  
*/

function diff($old, $new){
    $matrix = array();
    $maxlen = 0;
    foreach($old as $oindex => $ovalue){
        $nkeys = array_keys($new, $ovalue);
        foreach($nkeys as $nindex){
            $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
            if($matrix[$oindex][$nindex] > $maxlen){
                $maxlen = $matrix[$oindex][$nindex];
                $omax = $oindex + 1 - $maxlen;
                $nmax = $nindex + 1 - $maxlen;
            }
        }   
    }
    if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
    return array_merge(
        diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
        array_slice($new, $nmax, $maxlen),
        diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
}

function htmlDiff($old, $new){
    $ret = '';
    $diff = diff(preg_split("/[\s]+/", $old), preg_split("/[\s]+/", $new));
    foreach($diff as $k){
        if(is_array($k))
            $ret .=
                (!empty($k['d']) ? "<del>" . input::html(implode(' ',$k['d'])) . "</del> " : '').
                (!empty($k['i']) ? "<ins>" . input::html(implode(' ',$k['i'])) . "</ins> " : '');
        else $ret .= $k . ' ';
    }
    return $ret;
}


?>