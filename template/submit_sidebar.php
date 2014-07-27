<?php
/**
 * type: template
 * title: Project submit #sidebar
 * description: Generic advises for project submissions
 *
 *
 */

?> 
<aside id=sidebar>

    <section>
        <h5>Submit project<br>and/or release</h5>
        <p>
           You can submit <em title="Free, Libre, and Open Source Software">FLOSS</em>
           or <em title="or Solaris/Darwin/Hurd">BSD/Linux</em> software here.
           It's not required that you're a developer of said project.
        </p>
        <p><small>
           You can always edit the common project information together with
           a current release.  It will show up on the frontpage whenever you
           update a new version number and a changelog summary.
        </small></p>

        <?php
        if ($is_new) {
           print "<p>Or <a class='action submit-import'
           style='color:blue'>import</a> a project..</p>";
        }
        ?>
    </section>

    <?php include("template/submit_import.php"); ?>
    
</aside>
<section id=main>

