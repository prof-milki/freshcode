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
);


#-- Traverse and collect entries
foreach ($feeds as $name=>$url) {

    // data
    $html = "";
    $x = file_get_contents($url);
    $x = preg_replace("/[^\x20-\x7F\s]/", "", $x);
    $x = simplexml_load_string($x);
    
    // append
    foreach ($x->channel->item as $item) {
        $html .='<a href="' . htmlspecialchars($item->link)
              . '">' . htmlspecialchars($item->title) . '</a>'
              . "\n";
    }
    
    // save
    file_put_contents("./template/feed.$name.htm", $html);

}
