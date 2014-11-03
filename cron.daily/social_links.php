<?php
/**
 * api: freshcode
 * title: Social links count
 * description: Queries api.i-o/links for project homepages
 * version: 0.1
 *
 * Retrieve social media sharing site links for project homepages.
 * Stores them in `release`.`social_links`
 *
 * Only updates latest DB entry, so a versioned history of link counts
 * will be retained. (Not that it's needed, ...)
 *
 */

// deps
chdir(dirname(__DIR__)); 
include("config.php");

// use "remotely" for implicit caching
define("IO_LINKS", "http://api.include-once.org/links/social.ajax.php");

// traverse projects
foreach (db("SELECT *, MAX(t_changed) FROM release_versions GROUP BY name ORDER BY t_published DESC")->fetchAll() as $project) {

    // homepage
    $url = $project["homepage"];
    
    // request counts
    $counts = curl()
        ->url(IO_LINKS . "?url=$url")
        ->UserAgent("cron.daily/social_links (0.1; http://freshcode.club/)")
        ->exec();
    
    // summarize
    $counts = json_decode($counts, TRUE);
    $counts = array_sum($counts);
    print "$url = $counts\n";
    
    // store
    $project["social_links"] = $counts;
    #$project->store("REPLACE");
    db("UPDATE release SET social_links=? WHERE :&",
        $counts,
        array(
            "name" => $project["name"],
            "t_changed" => $project["t_changed"],
            "t_published" => $project["t_published"],
        )
    );

}


