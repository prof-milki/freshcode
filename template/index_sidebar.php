<?php
/**
 * type: template
 * title: frontpage feeds
 * description: Outputs #sidebar on frontage, containing template/feed.*.htm
 * version: 0.4
 *
 * The feed.*.htm files are regularily updated
 * by cron.daily/newsfeeeds. Thus does not need
 * further processing here.
 *
 */

?>

 <aside id=sidebar>

    <section class="article-links untrimmed">
        <h5>Linux.com Software</h5>
        <?php  include("template/feed.linuxcom.htm");  ?>
    </section>

    <section class="article-links trimmed">
        <h5>reddit<em>/r/linux</em></h5>
        <?php  include("template/feed.reddit.htm");  ?>
    </section>

    <section class="article-links trimmed">
        <h5>LinuxGames</h5>
        <?php  include("template/feed.linuxgames.htm");  ?>
    </section>

    <section class="article-links untrimmed">
        <h5>Sourceforge Files</h5>
        <?php  include("template/feed.sourceforge.htm");  ?>
    </section>

    <section class="article-links untrimmed">
        <h5>DistroWatch</h5>
        <?php  include("template/feed.distrowatch.htm");  ?>
    </section>

 </aside>

