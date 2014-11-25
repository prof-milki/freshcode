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
        <?php  readfile("template/feed.linuxcom.htm");  ?>
    </section>

    <section class="article-links untrimmed feed-fossies">
        <a href="http://fossies.org/"><h5><span class=green>Fossies</span>.org <small class=blue>/misc</small></h5></a>
        <?php  readfile("template/feed.fossies.htm");  ?>
    </section>

    <section class="article-links trimmed">
        <h5>reddit<em>/r/linux</em></h5>
        <?php  readfile("template/feed.reddit.htm");  ?>
    </section>

    <section class="article-links untrimmed">
        <h5>DistroWatch</h5>
        <?php  readfile("template/feed.distrowatch.htm");  ?>
    </section>

    <section class="article-links trimmed">
        <h5>Games <a href="http://www.linuxgames.com/" style=display:inline>LG</a>, <a href="http://www.gamingonlinux.com/" style=display:inline>GoL</a>, <a href="http://freegamer.blogspot.com/" style=display:inline>FG</a></h5>
        <?php  readfile("template/feed.gamingonlinux.htm");  ?>
        <?php  readfile("template/feed.linuxgames.htm");  ?>
        <?php  readfile("template/feed.linuxgamenews.htm");  ?>
        <?php  readfile("template/feed.freegamer.htm");  ?>
    </section>

    <section class="article-links untrimmed">
        <h5>Sourceforge Files</h5>
        <?php  readfile("template/feed.sourceforge.htm");  ?>
    </section>

    <section class="article-links trimmed">
        <h5>beOpen</h5>
        <?php  readfile("template/feed.beopen.htm");  ?>
    </section>

    <section class="article-links trimmed">
        <a href="/github-releases"><h5>GitHub releases →</h5></a>
        <?php  readfile("template/feed.github.htm");  ?>
    </section>

 </aside>

