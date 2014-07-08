<?php
/**
 * title: Article feeds
 * description: Queries a few online resources for article links
 * version: 0.1
 *
 * Currently populates:
 *   → ./template/feed.linuxcom.htm
 *   → ./template/feed.reddit.htm
 *
 * Both are just copied by template/sidebar_index.php for the
 * frontpage.
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
    $x = simplexml_load_file($url);
    
    // append
    foreach ($x->channel->item as $item) {
        $html .='<a href="' . htmlspecialchars($item->link)
              . '">' . htmlspecialchars($item->title) . '</a>'
              . "\n";
    }
    
    // save
    file_put_contents("./template/feed.$name.htm", $html);

}
