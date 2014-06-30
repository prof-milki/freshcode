<?php
/**
 * title: Links to other directories
 * description: Collection/overview of other software tracking / link lists.
 * version: 0.1
 *
 *
 * ToDo
 *  + http://opensourcelist.org/
 *  + http://www.linuxsoft.cz/en/
 *  + http://www.osalt.com/
 *  + http://www.datamation.com/open-source/open-source-software-the-mega-list-1.html
 *  + http://distrowatch.com/
 *
 *
 */


include("layout_header.php");
?>
 <section id=main style="height:2000pt">

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
      ["http://www.opensourcesoftwaredirectory.com/", "opensourcesoftwaredirectory.com.jpeg", "Open Source Software Directory",
       "Lists only stable and well-known Linux software, as it's intended for end users."
      ],
      ["http://www.linuxalt.com/", "linuxalt.com.jpeg", "Linux Alternatives",
       "Curates a list of Linux software alternatives for migrating newcomers."
      ],
      ["http://www.libe.net/version/index.php", "libe.net.jpeg", "Libe.net",
       "Is an archive and version tracker for various Linux and open source packages."
      ],
      ["http://en.wikipedia.org/wiki/List_of_free_and_open-source_software_packages", "wikipedia.org.jpeg",
       "Wikipedia: List of free and open source software packages",
       "summarizes a few common names."
      ],
      ["http://osliving.com/", "osliving.com.jpeg", "Open Source Living",
       "is an odd outlier, as they expect open source projects to pay for listings;
        thrives on click thru ads, etc."
      ],
      ["http://www.hotscripts.com/", "hotscripts.com.jpeg", "hotscripts.com",
       "is a script directory, mixed open source or non-free and commercial listings,
        many entries somewhat outdated"
      ],
      ["http://www.devscripts.com/", "devscripts.com.jpeg", "devscripts.com",
       "is a script directory, mixed open source or non-free and commercial listings,
        many entries somewhat outdated"
      ],
      ["http://www.developertutorials.com/scripts/", "developertutorials.com.jpeg", "developer&shy;tutorials.com",
       "is a script directory, mixed open source or non-free and commercial listings,
        many entries outdated"
      ],
      ["http://www.bigresource.com/scripts/", "bigresource.com.jpeg", "bigresource.com",
       "is a script directory, mixed open source or non-free and commercial listings,
        many entries somewhat outdated"
      ],
      ["http://www.scripts.com/", "scripts.com.jpeg", "scripts.com",
       "is a script directory, mixed open source or non-free and commercial listings,
        many entries somewhat outdated"
      ],
 #     ["http://www.fatscripts.com/", "fatscripts.com.jpeg", "fatscripts.com",
 #      "is a script directory, mixed open source or non-free and commercial listings,
 #       many entries somewhat outdated"
 #     ],
      ["http://www.scripts20.com/", "scripts20.com.jpeg", "scripts20.com",
       "is a script directory, mixed open source or non-free and commercial listings,
        many entries somewhat outdated"
      ],
      ["http://www.needscripts.com/", "needscripts.com.jpeg", "needscripts.com",
       "is a script directory, mixed open source or non-free and commercial listings,
        many entries somewhat outdated"
      ],
      ["http://www.advancescripts.com/", "advancescripts.com.jpeg", "advancescripts.com",
       "is a script directory, mixed open source or non-free and commercial listings,
        many entries somewhat outdated"
      ],
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



include("layout_bottom.php");


?>