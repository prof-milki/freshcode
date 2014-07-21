<?php
/**
 * title: Article feeds
 * description: Queries a few online resources for article links
 * version: 0.1
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

        switch ($name) {
        
            case "sourceforge":
                if (preg_match("~^(http://sourceforge.net/projects/(\w+))/files/.+?(\d+(\.\d)+).+?/download$~", $item->link, $m)) {
                    $html .= "<a href=\"$m[1]\">$m[2] <em>$m[3]</em></a>\n";
                    if ($i++ >= 20) { break 2; }
                }
            break;

            default:
            case "reddit":
            case "linuxcom":
            case "linuxgames":
                if (strlen($item->link) and strlen($item->title)) {
                    $html .="<a href=\"" . htmlspecialchars($item->link)
                        . "\">" . htmlspecialchars($item->title) . "</a>"
                        . "\n";
                    if ($i++ >= 20) { break 2; }
                }
        }
    }
    
    // save
    file_put_contents("./template/feed.$name.htm", $html);

}

