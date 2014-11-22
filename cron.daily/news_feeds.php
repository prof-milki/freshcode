<?php
/**
 * title: Article feeds
 * description: Queries a few online resources for article links
 * version: 0.5
 * category: template
 * api: cli
 * type: cron
 * x-cron: 12 *\/4 * * * 
 *
 * Highlights version numbers in news feeds,
 * and populates templates/feed.*.htm for sidebar display.
 *
 * Some of the collected entries (*games) are displayed togerther in sidebar blocks.
 *
 */


// switch to webroot
chdir(dirname(__DIR__));


#-- RSS
$feeds = array(
    "linuxcom,7" => "http://www.linux.com/news/software?format=feed&type=rss",
    "reddit,17" => "http://www.reddit.com/r/linux/.rss",
    "linuxgames,5" => "http://www.linuxgames.com/feed",
    "gamingonlinux,4" => "http://www.gamingonlinux.com/article_rss.php",
    "freegamer,3" => "http://freegamer.blogspot.com/feeds/posts/default?alt=rss",
    "sourceforge,22" => "http://sourceforge.net/directory/release_feed/",
    "distrowatch,15" => "http://distrowatch.com/news/dwd.xml",
    "beopen,7" => "http://beopen.bplaced.net/category/projects/feed/",
);
$filter = 
    "/Please 'report' off-topic|namelessrom|machomebrew/"
;

#-- Traverse and collect entries
foreach ($feeds as $name=>$url) {

    // data
    list($name, $max) = str_getcsv($name);
    $output = "";
    $x = file_get_contents($url);
    $x = preg_replace("/[^\x20-\x7F\s]/", "", $x);
    $x = simplexml_load_string($x);
    
    // append
    $i = 0;
    foreach ($x->channel->item as $item) {
    
        # pre-filter
        list($title, $link) = array( htmlspecialchars($item->title),  htmlspecialchars($item->link) );
        if (empty($title) or empty($link) or preg_match($filter, $title) or preg_match($filter, $link)) {
            continue;
        }

        # per feed
        switch ($name) {

            // Extract project base names and version numbers
            case "sourceforge":
                if (preg_match("~^(http://sourceforge.net/projects/(\w+))/files/.+?(\d+(\.\d+)+([-_. ](rc|beta|alpha|dev)([-._]?\d[.\d]*)?)?).+?/download$~", urldecode($item->link), $m)) {
                    $output .= "<a href=\"$m[1]\">$m[2] <em>$m[3]</em></a>\n";
                    $i++;
                }
                break;

            // Extract project base names and version numbers
            case "distrowatch":
                if (preg_match("~^(\d+/\d+)\s(\D+)\s+(.+)$~", $title, $m)) {
                    $output .= "<a href=\"$link\"><small>$m[1]</small> $m[2] <em>$m[3]</em></a>\n";
                }
                break;

            // Titles as is
            default:
            case "reddit":
            case "linuxcom":
            case "linuxgames":
                if (strlen($item->link) and strlen($item->title)) {
                    $title = preg_replace("~(\d+\.[\d-.]+)~", "<em>$0</em>", $title);
                    $output .="<a href=\"$link\">$title</a>\n";
                    $i++;
                }
                break;
        }

        if ($i >= $max) { break; }
    }
    
    // save
    strlen($output) and
    file_put_contents("./template/feed.$name.htm", $output);

}


