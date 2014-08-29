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
        $text = explode("\n", wordwrap(input::spaces($text), 15));
        foreach ($text as $i=>$line) {
            $q = 100 - $i;
            $o = (100 - 1.9*$i) / 100;
            $r .= "<span style=\"font-size: $q%; opacity: $o;\">"
                . $line . "</span> ";
        }
        return $r;
    }
}


$_ = "trim";

print <<<PROJECT
      <article class="project search">

        <h3>
            <a href="/projects/$entry[name]">
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