<?php
/**
 * type: page
 * title: Tags
 * description: Tag cloud
 * status: todo 
 *
 * We'll need a separate `tags` table for that, populated by a cron
 * script (or trigger?) that splits up the `release`.`tags` column
 * per project id.
 * Display may just be handled by page_index, with an extra search
 * param (?tag=...; a ?user= query is needed as well).
 *
 */


include("layout_header.php");
?>
<section id=main>

<h2>Tags</h2>

<p>
   There are still too few project listings to warrant a tag cloud.
</p>

<?php

include("layout_bottom.php");


?>