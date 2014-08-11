<?php
/**
 * api: FTD
 * title: Single forum post
 * description:
 *
 */

$_ = "trim";

print <<<HTML

   <li>
      <article class=entry>

          <h6 class=summary>$entry[summary]
             <span class="funcs trimmed">
                <a class="action forum-edit" data-id=$entry[id] title="Edit">Ed</a>
                <a class="action forum-reply" data-id=$entry[id] title="Reply">Re</a>
             </span>
          </h6>

          <aside class=meta><div>
             <b class=category>$entry[tag]</b>
             <i class=author>
                 <img align=top src="$entry[miniature]" width=16 height=16>
                 $entry[author]
             </i>
             <var class=datetime>{$_(strftime("%Y-%m-%d - %H:%M",$entry["t_published"]))}</var>
          </div></aside>

          <div class="excerpt">$entry[excerpt]</div>
          <div class="content trimmed">$entry[html]</div>

      </article>

HTML;

