<?php
/**
 * type: page
 * title: Project detail view
 * description: List project entry with all URLs and releases
 * license: AGPL
 * version 0.2
 * 
 *
 */


include("layout_header.php");

// query projects
$releases = db("
    SELECT *
      FROM release_versions
     WHERE name = ?
", $_REQUEST->name["name"]);

// show
if ($entry = $releases->fetch()) {

    // HTML preparation and some auto-generated fields
    prepare_output($entry);
    
    // callback for varexpression function calls in heredoc
    $_ = "trim";
    
    // output
    print <<<HTML

      <aside id=sidebar>
         <section>
           <h5>Links</h5>
           <a href="$entry[homepage]"><img src="img/home.png" width=11 height=11> Project Website</a><br>
           <a href="$entry[download]"><img src="img/disk.png" width=11 height=11> Download</a><br>
           {$_(proj_links($entry["urls"], $entry))} 
         </section>

         <section>
           <h5>Submitted by</h5>
           <a href="/?user=$entry[submitter]">$entry[submitter]</a><br>
         </section>

         <section style="font-size:90%">
           <h5>Manage</h5>
         You can also help out here by:<br>
         <a class=long-links href="/submit/$entry[name]" style="display:inline-block; margin: 3pt 1pt;">&larr; Updating infos</a><br>
         or <a href="/flag/$entry[name]">flagging</a> this entry for moderator attention.
         </section>

         <section style="font-size:90%">
           <h5>Share</h5>
           {$_(social_share_links($entry["name"], $entry["homepage"]))}
         </section>
      </aside>
      <section id=main>
      
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
      
HTML;
}


// query projects
$releases = db("
    SELECT *
      FROM release
     WHERE name = ? AND flag < 5 AND NOT deleted
  GROUP BY version
  ORDER BY t_published DESC, t_changed DESC
", $_REQUEST->name["name"]);

// show
print " <article class=release-list>  <h3>Recent Releases</h3> ";
while ($entry = $releases->fetch()) {
    prepare_output($entry);
    
    // output
    print <<<HTML

       <div class=release-entry>
          <span class=version>$entry[version]</span><span class=published_date>{$_(strftime("%d %b %Y %H:%M", $entry["t_published"]))}</span>
          <span class=release-notes>
             <b>$entry[scope]:</b>
             $entry[changes]
          </span>
       </div>

HTML;
}
print "</article>";


include("layout_bottom.php");



function proj_links($urls, $entry, $r="") {
    foreach (p_key_value($urls) as $title=>$url) {
        $title = ucwords($title);
        $url = versioned_url($url, $entry["version"]);
        $r .= "&rarr; <a href=\"$url\">$title</a><br>\n";
    }
    return $r;
}


?>