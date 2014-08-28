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
$filter = 
    "/Please 'report' off-topic|namelessrom|machomebrew/"
;

#-- Traverse and collect entries
foreach ($feeds as $name=>$url) {

    // data
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
                if (preg_match("~^(http://sourceforge.net/projects/(\w+))/files/.+?(\d+(\.\d+)+).+?/download$~", $item->link, $m)) {
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

        if ($i >= 20) { break; }
    }
    
    // save
    file_put_contents("./template/feed.$name.htm", $output);

}


#-- Scraping

// Fossies
include("./shared.phar");
curl::$defaults["useragent"] = "freshcode/0.6 (Linux x86-64; curl) projects-autoupdate/0.5 (screenshots,changelog,regex,xpath) +http://freshcode.club/";
if ($html = curl("http://fossies.org/linux/misc/index_n.html")->exec()
and preg_match_all("~<TR>.+?</TR>~s", $html, $line))
{
    $output = "";
    # <TR><TD VALIGN="top"><A HREF="openmpi-1.8.2.tar.gz"><IMG SRC="/dl.gif"
    # class="dl" title="[Download]" ALT=""></A></TD><TD> <A
    # HREF="openmpi-1.8.2.tar.gz/" title="Contents, browsing \&amp; more
    # ..."><B>openmpi-1.8.2.tar.gz</B></A> (25 Aug 19:39, 19779476 Bytes) <IMG
    # SRC="/warix/new1.gif" class="new_nb" ALT="*NEW*"><BR><DIV class="desc"><A
    # HREF="http://www.open-mpi.org/">Open&nbsp;MPI</A> - A High Performance
    # Message Passing Library.  Open MPI is a project combining technologies and
    # resources from several other projects (FT-MPI, LA-MPI, LAM/MPI, and
    # PACX-MPI) in order to build the best MPI library available. 
    # </DIV></TD></TR>
    foreach (array_slice($line[0], 0, 15) as $html) {

        // package name and version
        preg_match("~HREF=\"([\w-]+?)-(\d[\w._-]+?)(\.(zip|tar|gz|xz|bz2|pax|tgz|txt|tbz2|7z|exe))*/\"~", $html, $pkg);
        if (count($pkg) < 3) { continue; }
        list(, $pkg, $ver, ) = $pkg;

        // package title
        preg_match("~>([^<>]+)</A>~", $html, $title);
        $title = $title[1];

        // convert date string
        preg_match("~\((\d+ \w\w\w) \d\d:\d\d~", $html, $date);
        $date = strftime("%d/%m", strtotime($date[1]));
        
        // description
        preg_match("~</A>[\s-]*([^<>]+)</DIV>~", $html, $desc);
        $desc = htmlentities($desc[1]);
                
        // combine
        $output .= "<a href=\"http://fossies.org/$pkg\" title=\"$desc\">"
                .  "<small>$date</small> $title <em>$ver</em></a>\n";
    }

    // save
    file_put_contents("./template/feed.fossies.htm", $output);
}

