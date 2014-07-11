<?php
/**
 * api: include
 * type: template
 * title: Project version entry
 * description: Displays a release, with scope and changes
 * depends: strftime
 *
 * Shows versioning history of projects/
 *   → Date and Version
 *   → Scope and Changes
 *
 * This template, obviously, gets iterated over to output all
 * release entries.
 *
 */


print <<<VERSION_ENTRY
       <div class=release-entry>
          <span class=version>$entry[version]</span><span class=published_date>{$_(strftime("%d %b %Y %H:%M", $entry["t_published"]))}</span>
          <span class=release-notes>
             <b>$entry[scope]:</b>
             $entry[changes]
          </span>
       </div>
VERSION_ENTRY;


?>