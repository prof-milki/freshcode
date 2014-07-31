<?php
/**
 * title: Article feeds
 * description: Queries a few online resources for article links
 * version: 0.4
 *
 * Highlights version numbers in news feeds,
 * and populates templates/feed.*.htm for sidebar display.
 *
 */


// switch to webroot
chdir(dirname(__DIR__));


#-- RSS
$feeds = array(
    "reddit" => "http://www.reddit.com/r/linux/.rss",
    "linuxcom" => "http://www.linux.com/news/software?format=feed&type=rss",
    "linuxgames" => "http://www.linuxgames.com/feed",
    "sourceforge" => "http://sourceforge.net/directory/release_feed/",
    "distrowatch" => "http://distrowatch.com/news/dwd.xml",
);
$filter = array(
    "Please 'report' off-topic submissions and spam comments.",
);

#-- Traverse and collect entries
foreach ($feeds as $name=>$url) {

    // data
    $html = "";
    $x = file_get_contents($url);
    $x = preg_replace("/[^\x20-\x7F\s]/", "", $x);
    $x = simplexml_load_string($x);
    
    // append
    $i = 0;
    foreach ($x->channel->item as $item) {
    
        # pre-filter
        list($title, $link) = array( htmlspecialchars($item->title),  htmlspecialchars($item->link) );
        if (empty($title) or empty($link) or in_array($title, $filter)) {
            continue;
        }

        # per feed
        switch ($name) {

            // Extract project base names and version numbers
            case "sourceforge":
                if (preg_match("~^(http://sourceforge.net/projects/(\w+))/files/.+?(\d+(\.\d)+).+?/download$~", $item->link, $m)) {
                    $html .= "<a href=\"$m[1]\">$m[2] <em>$m[3]</em></a>\n";
                    $i++;
                }
                break;

            // Extract project base names and version numbers
            case "distrowatch":
                if (preg_match("~^(\d+/\d+)\s(\D+)\s+(.+)$~", $title, $m)) {
                    $html .= "<a href=\"$link\"><small style=color:grey>$m[1]</small> $m[2] <em>$m[3]</em></a>\n";
                }
                break;

            // Titles as is
            default:
            case "reddit":
            case "linuxcom":
            case "linuxgames":
                if (strlen($item->link) and strlen($item->title)) {
                    $title = preg_replace("~(\d+\.[\d-.]+)~", "<em>$0</em>", $title);
                    $html .="<a href=\"$link\">$title</a>\n";
                    $i++;
                }
                break;
        }

        if ($i >= 20) { break; }
    }
    
    // save
    file_put_contents("./template/feed.$name.htm", $html);

}

