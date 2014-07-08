<?php
/**
 * api: freshcode
 * type: page
 * title: Submit/edit project or release
 * description: Single-page edit form for projects and their releases
 * version: 0.6.0
 * category: form
 * license: AGPLv3
 * 
 *
 * Prepares the submission form,
 * handles database preparation
 * and merges in previous release entries.
 *
 *
 */



// Form field names
$form_fields = array(
    "name", "title", "homepage", "description", "license", "tags", "image",
    "version", "state", "scope", "changes", "download", "urls",
    "autoupdate_module", "autoupdate_url", "autoupdate_regex",
    "submitter", "lock",
);


// Start page output
include("template/header.php");
?> 
<aside id=sidebar>
    <section>
        <h5>Submit project<br>and/or release</h5>
        <p>
           You can submit <em title="Free, Libre, and Open Source Software">FLOSS</em>
           or <em title="or Solaris/Darwin/Hurd">BSD/Linux</em> software here.
           It's not required that you're a developer of said project.
        </p>
        <p>
           You can always edit the common project information together with
           a current release.  It will show up on the frontpage whenever you
           update a new version number and a changelog summary.
        </p>
    </section>
</aside>
<section id=main>
<?php




// Get project ID from request
$name = $_REQUEST->proj_name->length…3…33["name"];

// Retrieve existing project data in DB.
$data = release::latest($name);
$is_new = empty($data);

// Else create empty form value defaults in $data
if ($is_new) {
    $data = array_combine($form_fields, array_fill(0, count($form_fields), ""));
    $data["name"] = $name;
    $data["submitter"] = $_SESSION["name"];
}


// Project entry can be locked for editing by specific OpenIDs.
if (!release::permission($data, $_SESSION["openid"])) {
    print "<h3>Locked</h3> <p>This entry cannot be edited with your current <a href='/login'>login</a>. Its original author registered a different OpenID.</p>";
}


/**
 * Fetch form input on submit.
 * Check some constraints.
 * Then insert into database.
 *
 */
elseif ($name and $_REQUEST->has("title", "description")) {

    // Check field lengths
    if (!$_REQUEST->multi->serialize->length…150…150->strlen["title,description,homepage,changes"]) {
        print("<h3>Submission too short</h3> <p>You didn't fill out crucial information. Please note that our user base expects an enticing set of data points to find your project.</p>");
    }
    // Terms and conditions
    elseif (array_sum($_REQUEST->array->int->range…0…1["req"]) < 3) {
        print "<h3>Terms and Conditions</h3> <p>Please go back and assert that your open source project listing is reusable under the CC-BY-SA license.</p>";
    }
    elseif (!csrf(TRUE)) {
        print "<h3>CSRF token invalid</h3> <p>Session timeout, etc.</p>";
    }
    // Passed
    else {
    
        // Merge new data
        $release = new release($data);
        $release->update(
            $_REQUEST,
            array(
                "flag" => 0,   // User flags presumably become obsolete when project gets manually edited
                "submitter_openid" => $_SESSION["openid"],
            )
        );
        
        // Update project
        if ($release->store()) {
            print "<h2>Submitted</h2> <p>Your project and release informations has been saved.</p>
                  <p>See the result in <a href=\"http://freshcode.club/projects/$name\">http://freshcode.club/projects/$name</a>.</p>";
        }
        else { 
            print "Unspecified error.";
        }
    }

}





#-- Output input form
else {
    $data = array_map("input::html", $data);
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
               <small>A short moniker which becomes your http://freshcode.club/projects/<b>name</b>.<br>
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
               <textarea cols=50 rows=8 name=description
                         maxlength=1500 required>$data[description]</textarea>
               <small>Please give a concise roundup of what this software does, what specific features
               it provides, the intended target audience, or how it compares to similar apps.</small>
           </label>

           <label>
               License
               <select name=license>
                  {$select($licenses, $data["license"])}
               </select>
               <small>Again note that FLOSS is preferred.</small>
           </label>

           <label>
               Tags
               <input name=tags size=50 placeholder="game, desktop, gtk, python" value="$data[tags]"
                      maxlength=150 pattern="^\s*(\w+(-\w+)*(\s*,\s*|\s+)?){0,10}\s*$">
               <small>Categorize your project. Tags can be made up of letters, numbers and dashes. 
               This can include usage context, application type, programming languages, related projects,
               etc.</small>
           </label>

           <label>
               Image
               <input type=url name=image size=50 placeholder="http://i.imgur.com/xyzbar.png" value="$data[image]" maxlength=250>
               <small>Provide a preview image of up to 120x90 px.
               It will be fetched and displayed later. (Else a homepage screenshot will appear.)</small>
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
               <textarea cols=50 rows=7 name=changes maxlength=2000>$data[changes]</textarea>
               <small>Summarize the changes in this release. Documentation additions are as
               crucial as new features or fixed issues.</small>
           </label>

           <label>
               Download URL
               <input name=download size=50 type=url placeholder="http://project.example.org/" value="$data[download]" maxlength=250>
               <small>In particular for the download link one could utilize the <b>\$version</b> placeholder.</small>
           </label>

           <label>
               Other URLs
               <textarea cols=50 rows=3 name=urls maxlength=2000>$data[urls]</textarea>
               <small>You can add more project URLs using a comma/newline-separated list
               like <tt>src=http://, deb=http://</tt>.
               Common link types include src=, rpm=, deb=, txz=, dvcs=, forum=, changelog=, etc.</small>
           </label>
        </p>


        <h3>Automatic Release Tracking</h3>
        <p>
           <em>You can skip this section.</em>
           But instead of registering each version manually, you can later automate the process
           with some version control systems or e.g. your project homepage and changelog.
           See the <a href="http://fossil.include-once.org/freshcode/wiki/Autoupdate">Autoupdate Howto</a>.
        </p>
        <p>
           <label>
               Via
               <select name=autoupdate_module>
                   {$select("none,release.json,github,sourceforge,regex", $data["autoupdate_module"])}
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
               expect a list of field=/regex/ names, like version=, changes=, download=, state=.
               Associatively-named "Other URLs" are also used for extraction.</small>
           </label>

        </p>

        <h3>Publish</h3>
        <p>
           Please proofread again before saving.

           <label>
               Submitter
               <input name=submitter size=50 placeholder="Your name" value="$data[submitter]" maxlength=50>
               <small>Give us your name or nick name here.</small>
           </label>

           <label>
               Lock Entry
               <input name=lock size=50 placeholder="$_SESSION[openid]" value="$data[lock]" maxlength=250>
               <small>Normally all projects can be edited by everyone (WikiStyle).
               If you commit to yours, you can however lock this submission against an OpenID
               handle. (Or even provide a comma-separated list here for multiple contributors.)</small>
           </label>
        </p>
        <p>
           <b>Terms and Conditions</b>
           <label class=inline><input type=checkbox name="req[os]" value=1 required> It's open source / libre / Free software or pertains BSD/Linux.</label>
           <label class=inline><input type=checkbox name="req[cc]" value=1 required> Your entry is shareable under the <a href="http://creativecommons.org/licenses/by-sa/4.0/">CC-BY-SA</a> license.</label>
           <label class=inline><input type=checkbox name="req[sp]" value=1> And it's not spam.</label>
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
}


include("template/bottom.php");



// Output a list of select <option>s
function form_select_options($names, $value, $r="") {
    $map = is_string($names) ? array_combine($names = str_getcsv($names), $names) : $names;
    if ($value and !isset($map[$value])) { $map[$value] = $map[$value]; }
    foreach ($map as $id=>$title) {
        $r .= "<option" . ($id == $value ? " selected" : "") . " value=\"$id\" title=\"$title\">$id</option>";
    }
    return $r;
}


?>