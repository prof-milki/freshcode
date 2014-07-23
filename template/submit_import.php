<?php
/**
 * type: template
 * title: Submit form import sidebar
 * description: Import function sidebar section.
 *
 * Local stylesheet addition for making it slightly less prominent
 * until hovered over.
 *
 */

?>

    <style>
       .submit-import.trimmed { opacity: 0.2; margin-top: 30pt; }
       .submit-import.trimmed:hover, .submit-import.trimmed:focus { opacity: 1.0; }
    </style>

    <form action="/submit" method=POST enctype="multipart/form-data" class="submit-import trimmed">
    <!-- <a onclick="$('.submit-import').toggleClass('trimmed'); $(this).hide();">Import project..</a> -->
    <section>
        <a>
        <h5>Import</h5>
        <p>
           Alternatively you can import basic project information.
           <label>
              From
              <select name=import_via style="font-size: 125%"><option title="releases.json, common.js, package.json, bower.json, composer.json">JSON<option title="Description of a Project XML">DOAP<option title="Python Package Info">PKG-INFO<option title="Freecode.com project listing">freecode<option title="Sourceforge.net project homepage">sourceforge</select>
              <small>Which file format or service to use for importing.</small>
           </label>
           <label>
              with Name
              <input type=text name=import_name placeholder=project-id maxlength=33 pattern="^[\w-_.]+$">
              <small>Prior project name on freecode or sourceforge.</small>
           </label>
           <label>
              or File Upload
              <input type=file name=import_file size=5 placeholder="releases.json">
              <small>Upload a project.json or .doap or PKG-INFO summary.</small>
           </label>
           <input type=submit value="Import and Edit">
        </p>
        <p><small>
           But please don't perform mass-imports. It's imperative to reasonably update
           any significant project description to satisfy the CC-BY-SA license for freshcode.club/
        </small></p>
        </a>
    </section>
    </form>
