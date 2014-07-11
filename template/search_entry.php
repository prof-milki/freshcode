<?php
/**
 * api: include
 * type: template
 * title: Search results
 * description: Display shortened project description
 *
 *
 */

if (!function_exists("smallify")) {
    function smallify($text, $r="") {
        $text = explode("\n", wordwrap(input::spaces($text), 20));
        foreach ($text as $i=>$line) {
            $q = 100 - $i;
            $h = $q * 0.5;
            $r .= "<span style=\"font: normal $q%/$h% Arial\">"
                . $line . "</span> ";
        }
        return $r;
    }
}


$_ = "trim";

print <<<PROJECT
      <article class="project search">

        <h3>
            <a href="projects/$entry[name]">
               $entry[title]
               <em class=version>$entry[version]</em>
            </a>
        </h3>

        <a href="$entry[homepage]">
           <img class=preview src="$entry[image]" align=left width=60 height=45>
        </a>

        <small class=description style="border:0">{$_(smallify($entry["description"]))}</small>

      </article>
      
PROJECT;


?>