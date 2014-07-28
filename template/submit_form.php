<?php
/**
 * api: freshcode
 * type: template
 * title: Project submit/edit form
 * description: Input fields for project description/release editing.
 * version: 0.5
 * x-func-req: form_select_options
 * x-var-req: tags::$licenses
 * 
 *
 * Expects previous or empty field set in $data.
 *  →
 *
 * Also prints out a trivial diversion form for crawlbots.
 *
 */

$select = "form_select_options";
$_ = "trim";
print <<<HTML
    
    <span class="PageRank" style="DisplaY: nOne; VisiBility: HiddEN;">
      Please bots, submit your recommended link here: <br />
      <form action="/submit/pagerank" method="POST">
         Name:    <input name="name" value="" />    <br/>
         Email:   <input name="email" value="" />   <br/>
         Website: <input name="link" value="http://" />  <br/>
         Comment: <textarea name="comment"></textarea>  <br/>
         <input type="submit" name="submit" value="Send" />
      </form><hr/> (Real form follows...)
    </span> 

    <form action="" method=POST enctype="multipart/form-data" accept-encoding=UTF-8 rel=nofollow>
        <input type=hidden name=is_new value=$is_new>
        
        <h3>General Project Info</h3>
        <p>
           <label>
               Project ID
               <input name=name size=20 placeholder=projectname value="$data[name]"
                      maxlength=33 required pattern="^\w[-_\w]+\w$">
               <small>A short moniker which becomes your http://freshcode.club/projects/<var>name</var>.<br>
               <small>May contain letters, numbers, hyphen or underscore.</small></small>
           </label>

           <label>
               Title
               <input name=title size=50 placeholder="Awesome Software" value="$data[title]"
                      maxlength=100 required>
           </label>

           <label>
               Homepage
               <input name=homepage size=50 type=url placeholder="http://project.example.org/" value="$data[homepage]"
                      maxlength=250>
           </label>

           <label>
               Description
               <textarea cols=55 rows=9 name=description
                         maxlength=1500 required>$data[description]</textarea>
               <small>Please give a concise roundup of what this software does, what specific features
               it provides, the intended target audience, or how it compares to similar apps.</small>
           </label>

           <label>
               License
               <select name=license>
                  {$select(tags::$licenses, $data["license"])}
               </select>
               <small>Again note that FLOSS is preferred.</small>
           </label>

           <label>
               Tags<br>
                  <input id=tags name=tags size=50 placeholder="game, desktop, gtk, python" value="$data[tags]"
                         maxlength=150 pattern="^\s*((c\+\+|\w+([-.]\w+)*(\[,;\s]+)?){0,10}\s*$"
                         style="display:inline-block">
                  <span style="inline-block; height: 0px; overflow: visible; position: absolute;">
                      <img src=img/addtrove.png with=100 height=150 style="position:relative;top:-150px;">
                      <span id=trove_tags class=add-tags>{$_(tags::trove_select(tags::$tree))}</span>
                  </span>
               <small style="width:60%">Categorize your project. Tags can be made up of letters, numbers and dashes. 
               This can include usage context, application type, programming languages, related projects,
               etc.</small>
           </label>

           <label>
               Image
               <input type=url name=image size=50 placeholder="http://i.imgur.com/xyzbar.png" value="$data[image]" maxlength=250>
               <small>Previews will be 120x90 px large. Alternatively a homepage screenshot
               will appear later.</small>
           </label>
        </p>


        <h3>Release Submission</h3>
        <p>
           <label>
               Version
               <input name=version size=20 placeholder=2.0.1 value="$data[version]" maxlength=32>
               <small>Prefer <a href="http://semver.org/">semantic versioning</a> for releases.</small>
           </label>

           <label>
               State
               <select name=state>
                   {$select("initial,alpha,beta,development,prerelease,stable,mature,historic", $data["state"])}
               </select>
               <small>Tells about the stability or target audience of the current release.</small>
           </label>

           <label>
               Scope
               <br>
               <select name=scope>
                  {$select("minor feature,minor bugfix,major feature,major bugfix,security,documentation,cleanup,hidden", $data["scope"])}
               </select>
               <small>Indicate the significance and primary scope of this release.</small>
           </label>

           <label>
               Changes
               <textarea cols=60 rows=8 name=changes maxlength=2000>$data[changes]</textarea>
               <small>Summarize the changes in this release. Documentation additions are as
               crucial as new features or fixed issues.</small>
           </label>

           <label>
               Download URL
               <input name=download size=50 type=url placeholder="http://project.example.org/" value="$data[download]" maxlength=250>
               <small>In particular for the download link one could apply the
               <a class="action version-placeholder"><b><kbd>\$version</kbd></b> placeholder</a>.</small>
           </label>

           <label>
               Other URLs
               <textarea cols=60 rows=5 name=urls maxlength=2000>$data[urls]</textarea>
               <small>A list of comma or newline-separated project URLs
               like <code>src = http://foo, deb = http://bar</code>.
               Common link types include src / rpm / deb / txz / dvcs / release-notes / forum, etc.
               Either may contain a <a class="action version-placeholder">\$version placeholder</a>
               again.</small>
           </label>
        </p>


        <h3>Automatic Release Tracking</h3>
        <p>
           <em>You can skip this section.</em>
           But after registering your first version manually, you can later automate the process.
           Use a normalized Changelog or <var>releases.json</var> in your version control system,
           or a regex for your project homepage.
           See the <a href="http://fossil.include-once.org/freshcode/wiki/Autoupdate">Autoupdate Howto</a>.
        </p>
        <p>
           <label>
               Via
               <select name=autoupdate_module>
                   {$select("none,release.json,changelog,regex,github,sourceforge", $data["autoupdate_module"])}
               </select>
           </label>

           <label>
               Autoupdate URL
               <input name=autoupdate_url type=url size=50 value="$data[autoupdate_url]" placeholder="https://github.com/user/repo/tags.atom" maxlength=250>
               <small>This is the primary source for <b>releases.json</b> and the <b>regex</b> method.
               GitHub and Sourceforge URLs are autodiscovered if they're e.g. your project homepage.</small>
           </label>

           <label>
               Regex
               <textarea cols=50 rows=3 name=autoupdate_regex placeholder="version = /-(\d+\.\d+\.\d+)\.txz/" maxlength=2500>$data[autoupdate_regex]</textarea>
               <small>
               <a href="http://fossil.include-once.org/freshcode/wiki/AutoupdateRegex">Regex automated updates</a>
               expect a list of <code>field=/regex/</code> names, like version=, changes=, download=, state=.
               Associatively-named "Other URLs" are also used for extraction.</small>
           </label>

        </p>

        <h3>Publish</h3>
        <p>
           Please proofread again before saving.

           <label>
               Submitter
               <input name=submitter size=50 placeholder="Your Name,  optional@example.com" value="$data[submitter]" maxlength=100>
               <small>List your name or nick name here. Optionally add a gravatar email.</small>
           </label>

           <label>
               Lock Entry
               <input name=lock size=50 placeholder="$_SESSION[openid]" value="$data[lock]" maxlength=250>
               <small>Normally all projects can be edited by everyone (WikiStyle).
               If you commit to yours, you can however <a class="action lock-entry"><b>lock</b> this project</a>
               against one or multiple OpenID handles (comma-separated, take care to use exact URLs).
               Or add a password hash for using the submit API.
           </label>
        </p>
        <p>
           <b>Terms and Conditions</b>
           <label class=inline><input type=checkbox name="req[os]" value=1 required> It's open source / libre / Free software or pertains BSD/Linux.</label>
           <label class=inline><input type=checkbox name="req[cc]" value=1 required> Your entry is shareable under the <a href="http://creativecommons.org/licenses/by-sa/4.0/">CC-BY-SA</a> license.</label>
        </p>
        <p>
           <input type=submit value="Submit Project/Release">
           {$_(csrf())}
        </p>
        <p style=margin-bottom:75pt>
           Thanks for your time and effort!
        </p>

    </form>    
HTML;


?>