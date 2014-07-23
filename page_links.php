<?php
/**
 * title: Links to other directories
 * description: Collection/overview of other software tracking / link lists.
 * version: 0.3
 *
 *
 * ToDo
 *  + http://www.datamation.com/open-source/open-source-software-the-mega-list-1.html
 *  + http://www.datamation.com/osrc/article.php/3925806/Open-Source-Software-Top-59-Sites.htm
 *  + http://www.reddit.com/r/coolgithubprojects
 *  + http://sourceforge.net/new/
 *  + http://freeopensourcesoftware.org/
 *
 */

#-- preferred languages
header("Vary: Accept-Language");
preg_match_all("/\b(\w\w)([-,\s;]\w*|$)/", $_SERVER->text["HTTP_ACCEPT_LANGUAGE"], $langs)
and $langs = $langs[1];


include("template/header.php");
?>

 <style>
    #sidebar dl, #sidebar dl dd, #sidebar ul { margin: 0; padding: 0; }
    #sidebar dl dt { font-weight: 700; }
 </style>

 <aside id=sidebar class="absolute-float community-web">
    <section><h5>Ecosystem</h5></section>
    <p>
      Open Source development is more than just software and coding. User enthusiasm
      and interaction have an even larger stake in its progress.
    </p>
    <p>
      The interaction playground is technically comprised of:
    </p>
    <section>
      <b>News sites / Blogs</b>
      <li> <a href="http://slashdot.org/">slashdot</a>
      <li> <a href="http://lxer.com/">LXer</a>
      <li> <a href="http://lwn.net/">LWN</a>
      <li> <a href="http://osdir.com/">OSdir</a>
      <li> <a href="http://www.linuxtoday.com/">LinuxToday</a>
      <li> <a href="http://www.phoronix.com/">Phoronix</a>
      <li> <a href="http://www.osnews.com/">OSnews</a>
      <li> <a href="http://www.omgubuntu.co.uk/">OMG!Ubuntu</a>
      <li> <a href="http://www.webupd8.org/">Web Upd8</a>
      <li> <a href="http://ostatic.com/">OStatic</a>
    </section>
    <section>
      <b>Boards / Forums</b>
      <li> <a href="http://www.linuxquestions.org/">LinuxQuestions</a>
      <li> <a href="http://www.linuxforums.org/forum/">LinuxBoards</a>
    </section>
    <section>
      <b>Support / Q&amp;A</b>
      <li> <a onclick="return confirm('If you want to find something specific, don\'t be vague. Instead of asking for \'best\' software, provide a constrained set of feature requirements.');" href="http://softwarerecs.stackexchange.com/">Software Recommendations</a>
      <li> <a onclick="return confirm('AskUbuntu is for technical questions, not a Google substitute for finding software.');" href="http://askubuntu.com/">askubuntu.com</a>
    </section>
    <section>
      <b>Wikis / Howtos</b>
      <li> <a href="https://wiki.archlinux.org/">Arch Linux Wiki</a>
      <li> <a href="https://wiki.ubuntu.com/">Ubuntu Wiki</a>
      <li> <a href="http://www.howtoforge.com/">HowtoForge</a>
    </section>
    <section>
      <b>Chat channels</b>
      <li> <a href="http://freenode.net/">Freenode</a><br> &nbsp;&nbsp; and its <a href="https://webchat.freenode.net/">web chat</a>
    </section>
    <p>
      Programming-language specific developer hubs and package repositories
      often allow uncovering new software as well.
    </p>
    <section>
      <dl>
        <dt>Python</dt>
        <dd>
          <li> <a href="https://pypi.python.org/" title="Python Package Index">PyPI</a>
        </dd>
        <dt>PHP
        <dd>
          <li> <a href="https://packagist.org/explore/">Packagegist</a>
          <li> <a href="http://phptrends.com/">PHP Trends</a>
        </dd>
        <dt>Vala
        <dd>
          <li> <a href="https://bbs.archlinux.org/viewtopic.php?id=173563" title="Arch Linux Bulletin Board about Vala projects">Arch BBS</a>
        </dd>
      </dl>
    </section>

    <?php  if (in_array("de", $langs)): ?>
    <p>Local websites</p>
    <section>
      <b>.de</b>
      <li> <a href="http://www.pro-linux.de/">Pro Linux</a>
      <li> <a href="http://www.linux-magazin.de/">Linux Magazin</a>
      <li> <a href="http://www.heise.de/open/">Heise Open</a>
      <li> <a href="http://www.linux-community.de/">Linux Community</a>
      <li> <a href="http://wiki.ubuntuusers.de/Software">Ubuntu Users: Software</a>
      <li> <a href="http://ubuntunews.de/">Ubuntu News</a>
    </section>
    <?php endif; ?>
    
 </aside>
 <section id=main style="height: 2200pt; min-width: 700px;">

 <h4>Other FLOSS/Linux software directories</h4>
   <p>
 <?php

  $links = [

      ["http://freecode.com/", "freecode.com.jpeg", "Freecode.com",
       "(AKA Freshmeat) was the original software release tracker, and is still available as archive."
      ],
      ["http://freshcode.club/", "freshcode.club.jpeg", "freshcode.club",
       "and <a href=\"http://freecode.club/\">freecode</a>/<a href=\"http://freshmeat.club/\">freshmeat.club</a>
        are supposed to become substitutes with differing views on shared data sets."
      ],
      ["http://directory.fsf.org/", "directory.fsf.org.jpeg", "Free Software directory",
       "is the FSFs Wiki to summarize FLOSS packages and projects."
      ],
      ["http://www.opensourcesoftwaredirectory.com/", "opensourcesoftwaredirectory.com.jpeg", "Open Source Software Directory",
       "Lists only stable and well-known Linux software, as it's intended for end users."
      ],
      ["http://opensourcelist.org/", "opensourcelist.org.jpeg", "OpenSourceList.org",
       "Collection of best-per-category software; also includes MacOS and Windowsware."
      ],
      ["http://thechangelog.com/", "thechangelog.com.jpeg", "the changelog",
       "Is a blog and weekly podcast on open source development and interesting projects."
      ],
      ["http://www.linuxalt.com/", "linuxalt.com.jpeg", "Linux Alternatives",
       "Curates a list of Linux software alternatives for migrating newcomers."
      ],
      ["http://www.osalt.com/", "osalt.com.jpeg", "OS as Alternative",
       "Lists commercial/prioprietary software and the Free or Linux alternatives in usage categories."
      ],
      ["http://www.linuxgames.com/", "linuxgames.com.jpeg", "Linux Games",
       "Captures progress and newly released gaming software for Linux.",
      ],
      ["http://sourceforge.net/", "sourceforge.net.jpeg", "Sourceforge.net",
       "The original open source development plattform
        is still home to and primary notification hub for many projects."
      ],
      ["http://www.ohloh.net/", "ohloh.net.jpeg", "Ohloh.net",
       "statistically tracks open source project development."
      ],
      ["http://github.com/", "github.com.jpeg", "GitHub",
       "Is a frontend onto a distributed version control system.
        It's less suited for end users, but still allows searching for software."
      ],
      ["http://savannah.nongnu.org/", "savannah.nongnu.org.jpeg", "Savannah",
       "Provides an alternative Free software development plattform."
      ],
      ["https://launchpad.net/", "launchpad.net.jpeg", "Launchpad",
       "Is the development hub for Ubuntu and also lists a few things that
        haven't made it into the package managers yet."
      ],
      ["http://www.libe.net/version/index.php", "libe.net.jpeg", "Libe.net",
       "Is an archive and version tracker for various Linux and open source packages."
      ],
      ["http://www.linuxsoft.cz/en/", "linuxsoft.cz.jpeg", "LinuxSoft.cz",
       "Provides a comprehensive and searchable software list divided into categories.",
      ],
      ["http://en.wikipedia.org/wiki/List_of_free_and_open-source_software_packages", "wikipedia.org.jpeg",
       "Wikipedia: List of free and open source software packages",
       "summarizes a few common names."
      ],
      ["http://distrowatch.com/", "distrowatch.com.jpeg",
       "DistroWatch",
       "Does as it says and tracks new and upcoming BSD / Linux / GNU / Solaris distribution releases."
      ],
      ["http://openfontlibrary.org/", "openfontlibrary.org.jpeg", "Open Font Library",
       "Helps to easily uncover new and nicely categorized true or open type fonts.",
      ],
#      ["", "", "",
#       "",
#      ],
      ["http://www.opensourcescripts.com/", "opensourcescripts.com.jpeg", "Open Source Scripts",
       "Collects web applications and web service scripts.",
      ],
      ["http://opensourcelinux.org/", "opensourcelinux.org.jpeg", "Open Source List",
       "Provides a summary list of common applications.",
      ],
      ["http://osliving.com/", "osliving.com.jpeg", "Open Source Living",
       "is an odd outlier, as they expect open source projects to pay for listings;
        thrives on click thru ads, etc."
      ],
#      ["http://www.hotscripts.com/", "hotscripts.com.jpeg", "hotscripts.com",
#       "is a script directory, mixed open source or non-free and commercial listings,
#        many entries somewhat outdated"
#      ],
#      ["http://www.devscripts.com/", "devscripts.com.jpeg", "devscripts.com",
#       "is a script directory, mixed open source or non-free and commercial listings,
#        many entries somewhat outdated"
#      ],
#      ["http://www.developertutorials.com/scripts/", "developertutorials.com.jpeg", "developer&shy;tutorials.com",
#       "is a script directory, mixed open source or non-free and commercial listings,
#        many entries outdated"
#      ],
#      ["http://www.bigresource.com/scripts/", "bigresource.com.jpeg", "bigresource.com",
#       "is a script directory, mixed open source or non-free and commercial listings,
#        many entries somewhat outdated"
#      ],
#      ["http://www.scripts.com/", "scripts.com.jpeg", "scripts.com",
#       "is a script directory, mixed open source or non-free and commercial listings,
#        many entries somewhat outdated"
#      ],
#      ["http://www.fatscripts.com/", "fatscripts.com.jpeg", "fatscripts.com",
#       "is a script directory, mixed open source or non-free and commercial listings,
#        many entries somewhat outdated"
#      ],
#      ["http://www.scripts20.com/", "scripts20.com.jpeg", "scripts20.com",
#       "is a script directory, mixed open source or non-free and commercial listings,
#        many entries somewhat outdated"
#      ],
#      ["http://www.needscripts.com/", "needscripts.com.jpeg", "needscripts.com",
#       "is a script directory, mixed open source or non-free and commercial listings,
#        many entries somewhat outdated"
#      ],
#      ["http://www.advancescripts.com/", "advancescripts.com.jpeg", "advancescripts.com",
#       "is a script directory, mixed open source or non-free and commercial listings,
#        many entries somewhat outdated"
#      ],
  ];
  
  
  foreach ($links as $row) {
      print <<<HTML
      <div class=links-entry>
         <a href="$row[0]">
            <img src="/img/links/$row[1]" width=200 height=150 align=bottom border=0>
            <b>$row[2]</b>
         </a>
         $row[3]
      </div>
HTML;
  }



include("template/bottom.php");


?>