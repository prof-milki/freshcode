<?php
/**
 * api: include
 * type: template
 * title: Project description
 * description: Displays title, version, description, tags, homepage + download button
 * depends: wrap_tags
 *
 * Each projects/ page description contains
 *
 *   → Headline of project title and current version
 *   → Screenshot image
 *   → Description
 *   → Tag list (tags, license, state)
 *   → [Homepage] and [Download] link buttons
 *
 */


$_ = "trim";

$license_long = isset(tags::$licenses[$entry["license"]]) ? tags::$licenses[$entry["license"]] : $entry["license"];

print <<<PROJECT
      <article class=project>

        <h3>
            <a href="/projects/$entry[name]">
               $entry[title]
               <em class=version>$entry[version]</em>
            </a>
        </h3>

        <a href="$entry[homepage]">
           <img class=preview src="$entry[image]" align=right width=120 height=90 border=0>
        </a>

        <p class=description style="border:0">$entry[description]</p>

        <table class=long-tags border=0>
           <tr> <th>Tags</th>     <td>{$_(wrap_tags($entry["tags"]))}</td>           </tr>
           <tr> <th>License</th>  <td><a class=license title="$license_long">$entry[license]</a></td>      </tr>
           <tr> <th>State</th>    <td><a class=license>$entry[state]</a></td>        </tr>
        </table>

        <p class=long-links>
            <a href="$entry[homepage]"><img src="img/home.png" width=20 height=20 border=0 align=middle> Homepage</a>
            <a href="$entry[download]"><img src="img/disk.png" width=20 height=20 border=0 align=middle> Download</a>
        </p>

      </article>
PROJECT;


?>