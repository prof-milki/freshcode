<?php
/**
 * type: page
 * title: Tags
 * description: Tag cloud
 * version: 0.2
 *
 * This frontend code is utilizing a separate `tags` table, which
 * gets populated per cron script (rather than at insertion or via
 * trigger [sqlite seems insufficient to handle that).
 *
 * Currently just outputs a plain search result list. Later versions
 * should delegate this to a proper /search feature.
 *
 */


include("layout_header.php");
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
        $avg = array_sum($count) / count($count);

        // Print tag cloud
        foreach ($tags as $t) {

            // average
            $n=$q = 1.0*$t["cnt"] / 1.0*$avg;
            
            /**
             * Qantify
             * - Values below 1.0 are transitioned into the 0.5 to 1 range
             * - Values above 2.0 get capped around 3
             */
            if ($q < 1.0) {
               $q *= 0.156*$q*$q +0.72*$q -0.43;
            }
            else while ($q >= 3.5) {
               $q *= 0.85;
            }
            
            // font size
            $q = sprintf("%.1f", $q * 100);

            // output
            print " <a href=\"/tags/$t[tag]\" class=tag style=\"font-size: $q%\"> $t[tag] </a> ";
        }

    }
    print "</p>";
}


include("layout_bottom.php");


?>