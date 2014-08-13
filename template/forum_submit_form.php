<?php
/**
 * api: ftt
 * type: template
 * title: Post submit/edit form
 * description: Outputs input form for new / reply / editing forum posts.
 *
 *
 */


?>

<!-- faux -->
<form action=spex method=POST style="displaY: None">
   <input type="hidden" name="back" value="index" />
   <input type="hidden" name="mode" value="posting" />
   <input type="hidden" name="id" value="0" />
   <input type="hidden" name="posting_mode" value="0" />
   <input type="text" size="40" name="name" value="" maxlength="40">
   <input type="text" size="40" name="email" value="">
   <input type="text" size="40" name="homepage" value="">
   <input id="subject" type="text" size="50" name="subject" value="">
   <textarea cols="80" rows="21" name="comment"></textarea>
   <input type="submit" name="save_entry" value="OK - Submit" title="Save entry">
</form>

<!-- actual -->
<form class=forum-submit action=none style="display: run-in">

   <label>
       <b>Author</b>
       <input name=author placeholder=your-name size=50 value="<?=$author?>">
   </label>

   <label>
       <b><select name=img_type><option>gravatar<option>identicon<option>monsterid<option>wavatar<option>retro</select></b>
       <input name=image type=email placeholder="you@example.com" size=50 value="<?=$image?>">
   </label>

   <label>
       <b>Category</b>
       <select name=tag><?=form_select_options($forum_cfg["categories"], $tag)?></select>
   </label>

   <label>
       <b>Summary</b>
       <input name=summary placeholder="..." size=60 value="<?=$summary?>">
   </label>

   <label>
       <span style="position: absolute">
          <div class="markup-buttons">
           <a class="action markup" style="font-style: italic" data-before="*" data-after="*">italic</a>
           <a class="action markup" style="font-weight: bold" data-before="**" data-after="**">bold</a>
           <a class="action markup" style="text-decoration: underline" data-before="[" data-after="](http://example.org/)">link</a>
           <a class="action markup" style="" data-before="`" data-after="`">{code}</a>
           <a class="action markup" style="" data-before="\n  *  " data-after="">• list</a>
          </div>
       </span>
       <b>Message</b>
       <textarea name=source cols=55 rows=12><?=$source?></textarea>
   </label>

   <input type=hidden name=id value="<?=$id?>">
   <input type=hidden name=pid value="<?=$pid?>">

   <button class="action forum-submit">Follow The Thread</button>
   <br>

</form>

