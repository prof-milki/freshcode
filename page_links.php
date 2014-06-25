<?php
/**
 * title: Links to other directories
 * description: Collection/overview of other software tracking / link lists.
 * version: 0.1
 *
 */


include("layout_header.php");
?> <section id=main> <?php



print preg_replace("~(?<![<\">])(https?://[^\s,]+)~", "<a href=\"$1\">$1</a>", <<<HTML

   <h2>Other FLOSS/Linux software directories</h2>
   <p>
      <a href="http://freecode.com/">Freecode.com (AKA Freshmeat.net)</a> is only
      an archive henceforth.
      But various other software repositories still exist.
   </p>
   <p>
   <ul>

       <li> <a href="http://freshcode.club/">freshcode.club</a>,
            <a href="http://freecode.club/">freecode.club</a> and
            <a href="http://freshmeat.club/">freshmeat.club</a> are supposed to become
            FM/FC substitutes with differing views on shared data sets.

       <li> <a href="http://directory.fsf.org/">http://directory.fsf.org/</a>
              - Free Software directory
              
       <li> http://www.ohloh.net/
              - Statistically tracks open source project development.

       <li> http://sourceforge.net/ - The original open source development plattform
            is still home to and primary notification hub for many projects.

       <li> http://github.com/ - Is a frontend onto a distributed version control
            system. It's less suited for end users, but still allows searching for
            software.

       <li> http://savannah.nongnu.org/ - Provides an alternative Free software
            development plattform.

       <li> https://launchpad.net/ - Is the development hub for Ubuntu and also
            lists a few things that haven't made it into the package managers yet.

       <li> http://www.opensourcesoftwaredirectory.com/
              - Lists only stable and well-known Linux software, as it's intended
                for end users.
              
       <li> http://www.linuxalt.com/
              - Curates a list of Linux software alternatives for migrating newcomers.

       <li> <a href="http://www.libe.net/version/index.php">Libe.net</a>
              - Linux and open source software archive

       <li> And, of course, WP as always provides a few open source listings
            as well: http://en.wikipedia.org/wiki/List_of_free_and_open-source_software_packages
        
   </ul>
   </p>


   <h2>Script directories, mixed open source/commercial listing</h2>
   <p>
       The following listing directories contain web software. For the largest
       part their code is super ancient, and there are lots of non-free listings.
   </p>
   <p>
   <ul>
       <li> http://www.hotscripts.com/
       <li> http://www.devscripts.com/
       <li> http://www.developertutorials.com/scripts/
       <li> http://www.bigresource.com/scripts/
       <li> http://www.scripts.com/
       <li> http://www.fatscripts.com/
       <li> http://www.scripts20.com/
       <li> http://www.needscripts.com/
       <li> http://www.advancescripts.com/

   </ul>
   </p>


   <h2>Paid-for listings</h2>
   <ul>
       <li> http://osliving.com/ - Kinda odd, seems to thrive on click thru
       ads and is primarily about receiving donations for FLOSS listings.
   <p>
   </ul>
   </p>

      <br><br>TODO: Add screenshots.

HTML
    );

include("layout_bottom.php");


?>