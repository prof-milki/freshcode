<?php
/**
 * api: freshcode
 * type: page
 * title: Submit/edit project or release
 * description: Single-page edit form for projects and their releases
 * version: 0.2
 * category: form
 * 
 *
 * Prepares the submission form,
 * handles database preparation
 * and merges in previous release entries.
 *
 *
 */



// Form field names
$fields = array(
    "name", "title", "homepage", "description", "license", "tags", "image",
    "version", "state", "scope", "changes", "download", "urls",
    "autoupdate_module", "autoupdate_url", "autoupdate_regex",
    "submitter", "lock",
);


// Start page output
include("layout_header.php");
?> <section id=main> <?php



// Project name from request
$name = $_REQUEST->nocontrol->trim->name->strtolower->length_3to33["name"];

// Check for existing project infos from database
if ($data = db("SELECT * FROM release WHERE name = ?", $name)->fetch()) {
    $is_new = 0;
}
else {
    // Create new empty $data set
    $data = array_combine($fields, array_fill(0, count($fields), ""));
        #$data = $_REQUEST->list->nocontrol->trim[$fields];
    $is_new = 1;
    $data["name"] = $name;
    $data["submitter"] = $_SESSION["name"];
    $data["t_published"] = time();
}


// Project entry can be locked for editing by specific OpenIDs
if (LOGIN_REQUIRED and !$is_new and $data["lock"]
and !in_array($_SESSION["openid"], array_merge(str_getcsv($data["lock"]), $moderator_ids)))
{
    print "<h3>Locked</h3> <p>This entry cannot be edited with your current login. Its original author registered a different OpenID.</p>";
}


#-- Fetch form input
elseif ($name and $_REQUEST->has("title", "description")) {


    // Check field lengths
    if (!$_REQUEST->multi->min_length_100["title,description,homepage,changes"]) {
        print("<h3>Submission too short</h3> <p>You didn't fill out crucial information. Please note that our user base expects an enticing set of data points to find your project.</p>");
    }
    // Terms and conditions
    elseif (array_sum($_REQUEST->array->int["req"]) < 3) {
        print "<h3>Terms and Conditions</h3> <p>Please go back and assert that your open source project listing is reusable under the CC-BY-SA license.</p>";
    }
    // Passed
    else {

        // Merge input
        $data = array_merge(
             $data,
             $_REQUEST->list->nocontrol->trim[$fields],
             array(
                 "t_changed" => time(),
                 "flag" => 0,
                 "submitter_openid" => $_SERVER["openid"],
                 #"deleted" => 0,
             )
        );

        // Update project
        #db()->test = 1;
        if (db("INSERT INTO release (:?) VALUES (::)", $data, $data)) {

            print "<h2>Submitted</h2> <p>Your project and release informations has been saved.</p>
                  <p>See the result in <a href='http://freshcode.org/projects/$name'>http://freshcode.org/projects/$name</a>.</p>";
        }
        else { 
            print "Unspecified error.";
        }
    }

}





#-- Output input form
else {
    $data = array_map("input::_html", $data);
    $select = "form_select_options";
    print <<<HTML

    <form action="" method=POST enctype="multipart/form-data" accept-encoding=UTF-8>
        <input type=hidden name=is_new value=$is_new>

        <h2>Submit project and/or release</h2>
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

        
        <h3>General Project Info</h3>
        <p>
           <label>
               Project ID
               <input name=name size=20 placeholder=projectname value="$data[name]">
               <small>A short moniker which becomes your http://freshcode.club/projects/<b>name</b>.</small>
           </label>

           <label>
               Title
               <input name=title size=50 placeholder="Awesome Software" value="$data[title]">
           </label>

           <label>
               Homepage
               <input name=homepage size=50 type=url placeholder="http://project.example.org/" value="$data[homepage]">
           </label>

           <label>
               Description
               <textarea cols=50 rows=8 name=description>$data[description]</textarea>
               <small>Please give a concise roundup of what this software does, what specific features
               it provides, the intended target audience, or how it compares to similar apps.</small>
           </label>

           <label>
               License
               <input name=license size=20 placeholder="MITL, BSDL, GNU GPL" value="$data[license]">
               <small>Use abbreviated license names preferrably.</small>
           </label>

           <label>
               Tags
               <input name=tags size=50 placeholder="game, desktop, gtk, python" value="$data[tags]">
               <small>Categorize your project using free-form tags. This can include usage context,
               application type, programming languages, related projects, etc. It's limited to five tags
               however.</small>
           </label>

           <label>
               Image
               <input name=image size=50 placeholder="http://i.imgur.com/xyzbar.png" value="$data[image]">
               <small>Provide a preview image of up to 120x90 px. Supply a path to your website or
               use e.g. <a href="http://imgur.com/">imgur</a> for uploading.</small>
           </label>
        </p>


        <h3>Release Submission</h3>
        <p>
           <label>
               Version
               <input name=version size=20 placeholder=2.0.1 value="$data[version]">
               <small>Prefer <a href="http://semver.org/">semantic versioning</a> for releases.</small>
           </label>

           <label>
               State
               <select name=state>
                   {$select("initial,alpha,beta,development,prerelease,stable,mature,historic", $data["state"])}
               </select>
               <small>You can indicate the stability or target audience of the current release.</small>
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
               <textarea cols=50 rows=7 name=changes>$data[changes]</textarea>
               <small>Summarize the changes in this release. Documentation additions are as
               crucial as new features or fixed issues.</small>
           </label>

           <label>
               Download URL
               <input name=download size=50 type=url placeholder="http://project.example.org/" value="$data[download]">
           </label>

           <label>
               Other URLs
               <textarea cols=50 rows=3 name=urls>$data[urls]</textarea>
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
                   {$select("none,github,sourceforge,regex", $data["autoupdate_module"])}
               </select>
           </label>

           <label>
               Autoupdate URL
               <input name=autoupdate_url type=url size=50 value="$data[autoupdate_url]" placeholder="https://github.com/user/project/tags.atom">
               <small>Add your GitHub tags or Sourceforge project feed URL here.
               <br>For the Regex method this is the primary source to retrieve and read from.</small>
           </label>

           <label>
               Regex
               <textarea cols=50 rows=3 name=autoupdate_regex placeholder="version = /-(\d+\.\d+\.\d+)\.txz/">$data[autoupdate_regex]</textarea>
               <small>
               <a href="http://fossil.include-once.org/freshcode/wiki/AutoupdateRegex">Regex automated updates</a>
               expect a list of field=/regex/ names, like version=, changes=, download=, state=.
               Like-named "Other URLs" are used alternatively as extraction sources.</small>
           </label>

        </p>

        <h3>Publish</h3>
        <p>
           Please proofread again before saving.

           <label>
               Submitter
               <input name=submitter size=50 placeholder="Your name" value="$data[submitter]">
               <small>Give us your name or nick name here.</small>
           </label>

           <label>
               Lock Entry
               <input name=lock size=50 placeholder="$_SESSION[openid]" value="$data[lock]">
               <small>Normally all projects can be edited by everyone (WikiStyle).
               If you commit to yours, you can however lock this submission against an OpenID
               handle. (Or even provide a comma-separated list here for multiple contributors.)</small>
           </label>
        </p>
        <p>
           <b>Terms and Conditions</b>
           <label class=inline><input type=checkbox name="req[os]" value=1> It's open source / libre / Free software or pertains BSD/Linux.</label>
           <label class=inline><input type=checkbox name="req[cc]" value=1> Your entry is shareable under the <a href="http://creativecommons.org/licenses/by-sa/4.0/">CC-BY-SA</a> license.</label>
           <label class=inline><input type=checkbox name="req[sp]" value=1> And it's not spam.</label>
        </p>
        <p>
           <input type=submit value="Submit Project/Release">
        </p>
        <p style=margin-bottom:75pt>
           Thanks for your time and effort!
        </p>

    </form>    
HTML;
}


include("layout_bottom.php");



// Output a list of select <option>s
function form_select_options($names, $value, $r="") {
    foreach (str_getcsv($names) as $id) {
        $r .= "<option" . ($id == $value ? " selected" : "") . " value=\"$id\">$id</option>";
    }
    return $r;
}


?>