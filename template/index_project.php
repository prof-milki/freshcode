<?php
/**
 * api: include
 * type: template
 * title: Frontpage listing
 * description: Outputs list entry for recent project releases
 * depends: wrap_tags
 *
 * Each project release entry on the frontpage contains
 *
 *   → Headline with project title, current version, homepage + download button
 *   → Screenshot image
 *   → Description, .trimmed
 *   → Scope and changes, .trimmed
 *   → Short Tag list: license, tags
 *
 */


// varexpr callback
$_ = "trim";

// greying out flagged entries?
$css_flags = empty($entry["flag"]) ? "" : "style=\"opacity: " . (1.0 - 0.2 * $entry["flag"]) . "\"";

// Write
print <<<HTML
      <article class=project $css_flags>
        <h3>
            <a href="projects/$entry[name]">$entry[title]
            <em class=version>$entry[version]</em></a>
            <span class=links>
                <span class=published_date>$entry[formatted_date]</span>
                <a href="$entry[homepage]"><img src="img/home.png" width=20 height=20 border=0 align=middle alt="⛵"></a>
                <a href="$entry[download]"><img src="img/disk.png" width=20 height=20 border=0 align=middle alt="💾"></a>
            </span>
        </h3>
        <a href="$entry[homepage]"><img class=preview src="$entry[image]" align=right width=120 height=90 border=0></a>
        <p class="description trimmed">$entry[description]</p>
        <p class="release-notes trimmed"><b>$entry[scope]:</b> $entry[changes]</p>
        <p class=tags><img src="img/tag.png" width=30 align=middle height=22 border=0><a class=license>$entry[license]</a>{$_(wrap_tags($entry["tags"]))}</p>
      </article>
HTML;

?>