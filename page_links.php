<?php
/**
 * title: Links to other directories
 * description: Collection/overview of other software tracking / link lists.
 * version: 0.8
 * type: template
 * category: links
 *
 * ToDo
 *  - http://www.datamation.com/open-source/open-source-software-the-mega-list-1.html
 *  - http://www.datamation.com/osrc/article.php/3925806/Open-Source-Software-Top-59-Sites.htm
 *  - http://sourceforge.net/new/
 *  - http://flossmetrics.org/
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
      <li> <a href="http://www.reddit.com/r/opensource/">/r/opensource</a>
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
      They are more software forges and hosters though:
    </p>
    <section>
    <ul>
      <li> <a href="https://gitorious.org/">Gitorious</a>
      <li> <a href="https://bitbucket.org/">Bitbucket</a>
      <li> <a href="http://www.tigris.org/">Tigris</a>
      <li> <a href="https://alioth.debian.org/">Alioth</a>
      <li> <a href="http://gna.org/">GNA!</a>
      <li> <a href="https://freepository.com/home/">FreePository</a>
      <li> <a href="http://ourproject.org/">OurProject</a>
      <li> <a href="https://www.assembla.com/search/home">Assembla</a>
      <li> <a href="https://code.google.com/">Google Code</a>
      <li> <a href="http://www.codeplex.com/">CodePlex</a>
      <li> <a href="http://www.cloudforge.com/">CloudForge</a>
      <li> <a href="https://www.bountysource.com/projects">(BountySource)</a>
      <li> <a href="http://pgfoundry.org/">PgFoundry</a>
      <li> <a href="https://www.nuget.org/packages">nuGet (.NET)</a>
      <li> <a href="http://www.antepedia.com/">Antepedia (search)</a>
      <li> <a href="http://www.krugle.org/projects/">Krugle (meta list)</a>
    </ul>   
    <small>See also <a href="http://alternativeto.net/software/sourceforge/">SF alternatives</a>.</small>
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
        <dt>Perl</dt>
        <dd>
          <li> <a href="http://www.cpan.org/" title="Comprehensive Perl Archive Network">CPAN</a>
          <li> <a href="http://perltricks.com/">Perl Tricks</a>
        </dd>
        <dt>PHP</dt>
        <dd>
          <li> <a href="https://packagist.org/explore/" title="Composers Package Repo">Packagegist</a>
          <li> <a href="http://phptrends.com/">PHP Trends</a>
        </dd>
        <dt>Ruby</dt>
        <dd>
          <li> <a href="https://rubygems.org/gems">Gems</a>
        </dd>
        <dt>Vala</dt>
        <dd>
          <li> <a href="https://bbs.archlinux.org/viewtopic.php?id=173563" title="Arch Linux Bulletin Board about Vala projects">Arch BBS</a>
        </dd>
        <dt>Javascript</dt>
        <dd>
          <li> <a href="http://plugins.jquery.com/">jQuery Plugins</a>
        </dd>
      </dl>
    </section>
    <section>
      <b>Windows software</b>
      <li> <a href="https://help.ubuntu.com/community/ListOfOpenSourcePrograms">LOOP</a>
      <li> <a href="http://eos.osbf.eu/start/">EOS directory</a>
      <li> <a href="http://opensourcewindows.org/">OpenSource Windows</a>
      <li> <a href="http://osswin.sourceforge.net/">OSSWin</a>
    </section>

    <section>
      <b>More games</b>
      <li> <a href="http://osgameclones.com/">OS Game Clones</a>
      <li> <a href="http://freegamer.blogspot.de/">FreeGamer</a>
    </section>

    <section>
      <b>Code snippets</b>
      <li> <a href="http://snipplr.com/">snipplr</a>
    </section>

    <?php  if (in_array("de", $langs)): ?>
    <p>Local websites</p>
    <section>
      <b>.de</b>
      <li> <a href="http://www.pro-linux.de/">Pro Linux</a> + <a href="http://www.pro-linux.de/cgi-bin/DBApp/check.cgi">DBApp</a>
      <li> <a href="http://www.linux-magazin.de/">Linux Magazin</a>
      <li> <a href="http://www.heise.de/open/">Heise Open</a> + <a href="http://www.heise.de/download/top-downloads-50000505000/?f=5s">SW-Cat</a>
      <li> <a href="http://www.linux-community.de/">Linux Community</a>
      <li> <a href="http://wiki.ubuntuusers.de/Software">Ubuntu Users: Software</a>
      <li> <a href="http://ubuntunews.de/">Ubuntu News</a>
    </section>
    <?php endif; ?>
    
 </aside>
 <section id=main style="height: 3000pt; min-width: 700px;">

 <h4>Other FLOSS/Linux software directories</h4>
   <p>
 <?php

  $links = [

#      ["http://freshcode.club/", "freshcode.club.jpeg", "freshcode.club",
#       "and <a href=\"http://freecode.club/\">freecode</a>/<a href=\"http://freshmeat.club/\">freshmeat.club</a>
#        are supposed to become substitutes with differing views on shared data sets."
#      ],


   //1
      ["http://freecode.com/", "freecode.com.jpeg", "Freecode.com",
       "(AKA Freshmeat) was the original software release tracker, and is still available as archive."
      ],
      ["http://sourceforge.net/", "sourceforge.net.jpeg", "Sourceforge.net",
       "Is the classic open source development service
        and still home to and primary hub for many projects."
      ],
      ["http://fossies.org/linux/misc/", "fossies.org.jpeg", "Fossies.org",
       "tracks popular open source packages; and is quite feature-rich underneath its classic interface.",
      ],
   //2
      ["http://directory.fsf.org/", "directory.fsf.org.jpeg", "Free Software directory",
       "is the FSFs Wiki to summarize FLOSS packages and projects."
      ],
      ["http://github.com/", "github.com.jpeg", "GitHub",
       "is a prettier frontend onto git source control.
        Less suited for end users, but still allows searching for software."
      ],
      ["http://www.icewalkers.com/", "icewalkers.com.jpeg", "Ice Walkers",
       "is also a software release tracker and news blog, with its own software directory.",
      ],
   //3
      ["http://www.opensourcesoftwaredirectory.com/", "opensourcesoftwaredirectory.com.jpeg", "Open Source Software Directory",
       "Lists only stable and well-known Linux software, as it's intended for end users."
      ],
      ["https://launchpad.net/", "launchpad.net.jpeg", "Launchpad",
       "is the development hub for Ubuntu and also lists a few things that
        haven't made it into the package managers yet."
      ],
      ["http://www.linuxgames.com/", "linuxgames.com.jpeg", "Linux Games",
       "Captures progress and newly released gaming software for Linux.",
      ],
   //4
      ["http://www.osalt.com/", "osalt.com.jpeg", "OS as Alternative",
       "Lists commercial/prioprietary software and the Free or Linux alternatives in usage categories."
      ],
      ["http://www.ohloh.net/", "ohloh.net.jpeg", "OpenHUB (Ohloh)",
       "statistically tracks open source project development."
      ],
      ["http://www.zwodnik.com/", "zwodnik.com.jpeg", "Zwodnik",
       "provides a pretty overview, categorization, description and reviews for open source packages.",
      ],
   //5
      ["http://www.linuxalt.com/", "linuxalt.com.jpeg", "Linux Alternatives",
       "Curates a list of Linux software alternatives for migrating newcomers."
      ],
      ["http://savannah.nongnu.org/", "savannah.nongnu.org.jpeg", "Savannah",
       "Provides an alternative Free software development plattform."
      ],
      ["http://www.reddit.com/r/coolgithubprojects", "coolgithubprojects.jpeg", "CoolGitHubProjects",
       "is a subreddit discussing interesting finds from (otherwise opaque) GitHub repos.",
      ],
   //6
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
   //7
      ["http://linuxappfinder.com/all", "linuxappfinder.com.jpeg", "Linux AppFinder",
       "Provides vast categories and application options, alternative lists, web feeds, news, and a community forum.",
      ],
      ["http://www.opensourcescripts.com/", "opensourcescripts.com.jpeg", "Open Source Scripts",
       "Collects web applications and web service scripts.",
      ],
      ["http://www.libe.net/version/index.php", "libe.net.jpeg", "Libe.net",
       "is an archive and version tracker for various Linux and open source packages."
      ],
   //8
      ["http://opensourcelist.org/", "opensourcelist.org.jpeg", "OpenSourceList.org",
       "Collection of best-per-category software; also includes MacOS and Windowsware."
      ],
      ["http://opensourcelinux.org/", "opensourcelinux.org.jpeg", "Open Source List",
       "Provides a summary list of common applications.",
      ],
      ["http://opensourcearcade.com/", "opensourcearcade.com.jpeg", "Open Source Arcade",
       "is an assemblage of games categorized per programming language or genre.",
      ],
   //9
      ["http://libreprojects.net/", "libreprojects.net.jpeg", "Libre Projects",
       "is itself a meta directory to various open source and open content directories and community hubs.",
      ],
      ["http://openfontlibrary.org/", "openfontlibrary.org.jpeg", "Open Font Library",
       "Helps to easily uncover new and nicely categorized true or open type fonts.",
      ],
      ["http://www.findbestopensource.com/home/", "findbestopensource.com.jpeg", "Find Best OpenSource",
       "is a well curated news and application category blog.",
      ],
   //10
      ["http://freeopensourcesoftware.org/", "freeopensourcesoftware.org.jpeg", "Free Open Source Software",
       "A Wiki which doesn't host a software directory itself, but provides various resources to uncover them.",
      ],
      ["https://openhatch.org/", "openhatch.org.jpeg", "OpenHatch",
       "enables matchmaking for projects and their developers and interested users and contributions.",
      ],
      ["http://thechangelog.com/", "thechangelog.com.jpeg", "the changelog",
       "Is a blog and weekly podcast on open source development and interesting projects."
      ],
   //11
      ["http://alternativeto.net/software/sourceforge/", "alternativeto.net.jpeg", "AlternativeTo",
       "is an extensive meta cross-reference list of web sites and applications."
      ],
      ["http://beopen.bplaced.net/category/projects/", "beopen.bplaced.net.jpeg", "BeOpen",
       "directorizes open source projects, software forges, news, events, links and organisations.",
      ],
      ["http://www.lgdb.org/", "lgdb.org.jpeg", "Linux Games DB",
       "is cataloging and searching for new Linux-specific and compatible games.",
      ],
   //12
      ["http://gtk-apps.org/", "gtk-apps.org.jpeg", "Gtk-Apps",
       "lists existing and updated Gtk+/Gnome applications",
      ],
      ["http://opendesktop.info/portal/", "opendesktop.info.jpeg", "openDesktop",
       "aggregates Qt/Gtk/Gnome and Java/Wine application releasess",
      ],
      ["http://www.portablelinuxgames.org/", "portablelinuxgames.org.jpeg", "Portable Linux Games",
       "provides pre-build and distribution independent .run packages.",
      ],
   //13
      ["http://www.tuxarena.com/", "tuxarena.com.jpeg", "TuxArena",
       "has news, application lists, and tutorials for Ubuntu, Debian, Mint.",
      ],
      ["http://linuxsoftnews.wordpress.com/", "linuxsoftnews.wordpress.com.jpeg", "LinuxSoftNews.WP",
       "is a blog with monthly software release summaries and detailed reviews",
      ],
      ["http://www.fosshub.com/", "fosshub.com.jpeg", "FOSSHUB",
       "is a project download hoster and release announcer for some well-known apps.",
      ],
   //14
#      ["", "", "",
#       "",
#      ],
#      ["", "", "",
#       "",
#      ],
#      ["", "", "",
#       "",
#      ],
  ];

  
  // Write out our gallery  
  foreach ($links as $entry) {
      print <<<HTML
      <div class=links-entry>
         <a href="$entry[0]">
            <img src="/img/links/$entry[1]" width=200 height=150 align=bottom border=0>
            <b>$entry[2]</b>
         </a>
         $entry[3]
      </div>\n
HTML;
  }
  



include("template/bottom.php");


?>