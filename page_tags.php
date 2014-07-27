<?php
/**
 * type: page
 * title: Tags
 * description: Tag cloud
 * version: 0.3
 *
 * This frontend code is utilizing a separate `tags` table, which
 * gets populated per cron script (rather than at insertion or via
 * trigger [sqlite seems insufficient to handle that).
 *
 * Currently just outputs a plain search result list. Later versions
 * should delegate this to a proper /search feature.
 *
 */


include("template/header.php");


#-- sidebar with Trove list
?><aside id=sidebar>
<div id=trove_tags class=pick-tags>
<?php
print tags::trove_select(tags::$tree);
?>
</div>
</aside><?php



#-- print tag cloud
?>
<section id=main>
<h2>Tags</h2>
<p id=tag_cloud>
<?php

// Query `tags` table to generate a cloud
$tags = db("SELECT COUNT(name) AS cnt, tag FROM tags GROUP BY tag")->fetchAll();
$count = array_column($tags, "cnt");
if ($count) {
    $avg = count($count) / array_sum($count);

    // Print tag cloud
    foreach ($tags as $t) {

        // average
        $n = 
        $q = 1.0*$t["cnt"] / 1.0*$avg;
        
        /**
         * Qantify
         * - Values 0.1 - 20.0 are transitioned into the range 0.3 - 2.0
         */
        $q = atan($q * 0.75 + 0.1) * 1.55;
        
        // font size
        $q = sprintf("%.1f", $q * 100);

        // output
        print " <a href=\"/search?tag=" . urlencode($t["tag"])
            . "\" class=tag style=\"font-size: $q%;\"> $t[tag]</a> ";
    }

}
?></p><?php


include("template/bottom.php");


?>