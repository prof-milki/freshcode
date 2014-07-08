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
?> <section id=main> <?php




#-- search by tags
if ($_GET->words["name"]) {

    print "<h2>Projects with tags: {$_GET->words->html['name']}</h2><p><dl>";
    
    $result = db("
        SELECT release.name, SUBSTR(description,1,222) AS description, version, MAX(t_changed)
        FROM release
        LEFT JOIN tags ON release.name = tags.name
        WHERE tags.tag IN (??)
        GROUP BY release.name LIMIT 50",
        $_GET->words->p_csv["name"]
    );
    foreach ($result as $p) {
        print<<<HTML
           <dt><a href="/projects/$p->name">$p->name</a> <em>$p->version</em></dt>
           <dd>$p->description</dd>
HTML;
    }
}


#-- print tag cloude
else {

    print "<h2>Tags</h2>
    <p>";

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
            print " <a href=\"/tags/$t[tag]\" class=tag style=\"font-size: $q%;\"> $t[tag]</a> ";
        }

    }
    print "</p>";
}


include("template/bottom.php");


?>