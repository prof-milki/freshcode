<?php
/**
 * type: template
 * title: frontpage feeds
 * description: Outputs #sidebar on frontage, containing template/feed.*.htm
 *
 * The feed.*.htm files are regularily updated
 * by cron.daily/newsfeeeds. Thus does not need
 * further processing here.
 *
 */

?>

 <aside id=sidebar>

    <section class="article-links untrimmed">
        <h5>linux.com Software</h5>
        <?php  include("template/feed.linuxcom.htm");  ?>
    </section>

    <section class="article-links trimmed">
        <h5>reddit /r/linux</h5>
        <?php  include("template/feed.reddit.htm");  ?>
    </section>

 </aside>

