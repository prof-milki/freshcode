<?php
/**
 * title: Fossies.org extraction
 * description: Retrieves from fossies.org and stores as sidebar feed .html.
 * version: 0.5
 * category: template
 * api: cli
 * type: cron
 * x-cron: 11 *\/4 * * * 
 *
 * Scrapes from fossies.org/linux/misc/index_n.html,
 * extracts title, version, time, and description.
 *
 * Highlights version numbers and date, adds title= description.
 *
 * Stored in ./template/feed.fossies.htm for frontpage sidebar.
 *
 */


// switch to webroot
chdir(dirname(__DIR__));


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
    foreach (array_slice($line[0], 0, 22) as $html) {

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

