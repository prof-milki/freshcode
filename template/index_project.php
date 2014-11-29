<?php
/**
 * api: include
 * type: template
 * title: Frontpage listing
 * description: Outputs list entry for recent project releases
 * depends: wrap_tags
 * version: 0.5
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
$css_flags = ($entry["flag"] < 2) ? "" : "style=\"opacity: " . (1.0 - 0.2 * $entry["flag"]) . "\"";
$css_class = preg_match("/^\d+\.0(\.0)+$/", $entry["version"]) ? " sponsored" : ""; 

// desc
$license_long = isset(tags::$licenses[$entry["license"]]) ? tags::$licenses[$entry["license"]] : $entry["license"];

// Write
print <<<HTML
      <article class="project$css_class" $css_flags itemscope itemtype="http://schema.org/SoftwareApplication">
        <h3>
            <a href="/projects/$entry[name]"><span itemprop=name>$entry[title]</span>
            <em class=version itemprop=softwareVersion>$entry[version]</em></a>
            <span class=links>
                <span class=published_date itemprop=datePublished>$entry[formatted_date]</span>
                <a href="$entry[homepage]" itemprop=url><img src="img/home.png" width=20 height=20 alt="⛵"></a>
                <a href="$entry[download]" itemprop=downloadUrl><img src="img/disk.png" width=20 height=20 alt="💾"></a>
            </span>
        </h3>
        <a href="$entry[homepage]"><img class=preview itemprop=image src="$entry[image]" align=right width=120 height=90 border=0></a>
        <p class="description trimmed" itemprop=featureList>$entry[description]</p>
        <p class="release-notes trimmed" itemprop=releaseNotes><b>$entry[scope]:</b> $entry[changes]</p>
        <p class=tags itemprop=keywords><img src="img/tag.png" width=30 align=middle height=22 border=0><a class=license title="$license_long">$entry[license] </a>{$_(wrap_tags($entry["tags"]))}</p>
      </article>
HTML;

?>