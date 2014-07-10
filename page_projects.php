<?php
/**
 * type: page
 * title: Project detail view
 * description: List project entry with all URLs and releases
 * license: AGPL
 * version 0.3
 * 
 * Shows:
 *   → General project description
 *   → Sidebar with project links, submitter, management links, social share count
 *   → Release history and changelogs
 * Adds:
 *   → RSS/Atom links to header template
 *
 */

// current project id
$name = $_REQUEST->proj_name["name"];

// inject RSS/Atom links
$header_add = "<link rel=alternate type=application/rss+xml href=/feed/$name.rss>\n"
            . "<link rel=alternate type=application/atom+xml href=/feed/$name.atom>\n"
            . "<link rel=alternate type=json/vnd.freshcode.club href=/feed/$name.json>";
include("template/header.php");


// fetch most current project/release entry
$releases = db("
    SELECT *
      FROM release_versions
     WHERE name = ?
       AND NOT deleted
     LIMIT 1
", $name);

// show
if ($entry = $releases->fetch()) {

    prepare_output($entry);   // HTML preparation and some auto-generated fields
    $_ = "trim";    // callback for varexpression function calls in heredoc
    
    // Output
    print <<<SIDEBAR
      <aside id=sidebar>
         <section>
           <h5>Links</h5>
           <a href="$entry[homepage]"><img src="img/home.png" width=11 height=11> Project Website</a><br>
           <a href="$entry[download]"><img src="img/disk.png" width=11 height=11> Download</a><br>
           {$_(proj_links($entry["urls"], $entry))} 
         </section>

         <section>
           <h5>Submitted by</h5>
           <a class=submitter href="/?user=$entry[submitter]">$entry[submitter_img]$entry[submitter]</a><br>
         </section>

         <section style="font-size:90%">
           <h5>Manage</h5>
         You can also help out here by:<br>
         <a class=long-links href="/submit/$entry[name]" style="display:inline-block; margin: 3pt 1pt;">&larr; Updating infos</a><br>
         or <a href="/flag/$entry[name]">flagging</a> this entry for moderator attention.
         </section>

         <section style="font-size:90%">
           <h5>Share project {$_(social_share_count($entry["social_links"]))}</h5>
           {$_(social_share_links($entry["name"], $entry["homepage"]))}
         </section>
      </aside>
      <section id=main>
SIDEBAR;
      
    // Output
    print <<<PROJECT
      <article class=project>
        <h3>
            <a href="projects/$entry[name]">$entry[title]
            <em class=version>$entry[version]</em></a>
        </h3>
        <a href="$entry[homepage]"><img class=preview src="$entry[image]" align=right width=120 height=90 border=0></a>
        <p class=description style="border:0">$entry[description]</p>
        <p class=long-tags><span>Tags</span> {$_(wrap_tags($entry["tags"]))}</p>
        <p class=long-tags><span>License</span> <a class=license>$entry[license]</a></p>
        <p class=long-links>
            <a href="$entry[homepage]"><img src="img/home.png" width=20 height=20 border=0 align=middle> Homepage</a>
            <a href="$entry[download]"><img src="img/disk.png" width=20 height=20 border=0 align=middle> Download</a>
        </p>
      </article>
PROJECT;

}


// retrieve all released versions
$releases = db("
    SELECT *, MAX(t_changed)
      FROM release
     WHERE name = ?
       AND flag < 5
       AND NOT deleted
  GROUP BY version
  ORDER BY t_published DESC, t_changed DESC
", $name);


// show
?> <article class=release-list>  <h3>Recent Releases</h3> <?php
while ($entry = $releases->fetch()) {
    prepare_output($entry);
    
    // output
    print <<<VERSION_ENTRY
       <div class=release-entry>
          <span class=version>$entry[version]</span><span class=published_date>{$_(strftime("%d %b %Y %H:%M", $entry["t_published"]))}</span>
          <span class=release-notes>
             <b>$entry[scope]:</b>
             $entry[changes]
          </span>
       </div>
VERSION_ENTRY;

}
?> </article> <?php


// html tail
include("template/bottom.php");



/**
 * Convert "url1=, url2=, url3=" list into titled hyperlinks.
 *
 */
function proj_links($urls, $entry, $r="") {
    foreach (p_key_value($urls) as $title=>$url) {
        $title = ucwords($title);
        $url = versioned_url($url, $entry["version"]);
        $r .= "&rarr; <a href=\"$url\">$title</a><br>\n";
    }
    return $r;
}


?>