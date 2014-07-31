<?php
/**
 * api: include
 * type: template
 * title: Sidebar links for project
 * description: Shows project URLs, submitter, submit/ and flag/ link, social bookmarks
 * depends: proj_links, social_share_count, social_share_links
 *
 * Creates #sidebar with four <section>s:
 *   → Project links (homepage, download, other URLs)
 *   → Submitter with gravatar/identicon
 *   → Submission edit button, and flag/ link
 *   → Social sharing links and ★ star count.
 *
 */

$_ = "trim";

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
           <a class=submitter href="/search?user=$entry[submitter]">$entry[submitter_img]$entry[submitter]</a><br>
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



?>