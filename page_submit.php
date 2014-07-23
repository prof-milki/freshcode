<?php
/**
 * api: freshcode
 * type: page
 * title: Submit/edit project or release
 * description: Single-page edit form for projects and their releases
 * version: 0.7.0
 * category: form
 * license: AGPLv3
 * 
 * Prepares the submission form. On POST checks a few constraints,
 * but UPDATE itself is handled by release::update() and ::store().
 *
 * Tags: http://aehlke.github.io/tag-it/
 *
 */



// Form field names
$form_fields = array(
    "name", "title", "homepage", "description", "license", "tags", "image",
    "version", "state", "scope", "changes", "download", "urls",
    "autoupdate_module", "autoupdate_url", "autoupdate_regex",
    "submitter", "lock",
);


// Get project ID from request
$name = $_REQUEST->proj_name->length…3…33["name"];

// Retrieve existing project data in DB.
$data = release::latest($name);
$is_new = empty($data);


// Else create empty form value defaults in $data
if ($is_new) {
    $data = array_fill_keys($form_fields, "");
    $data["name"] = $name;
    $data["submitter"] = $_SESSION["name"];
    // Optional: import initial $data from elsewhere
    if ($_POST->has("import_via")) {
        $data = array_merge($data, project_import::fetch());
    }
}


// Project entry can be locked for editing by specific OpenIDs.
if (!release::permission($data, $_SESSION["openid"])) {
    $error = "This entry cannot be edited with your current <a href='/login'>login</a>. Its original author registered a different one. If your OpenID provider login fails to work, please flag for for moderator attention.";
    exit(include("page_error.php"));
}



// Start page output
include("template/header.php");
include("template/submit_sidebar.php");


/**
 * Fetch form input on submit.
 * Check some constraints.
 * Then insert into database.
 *
 */
if ($name and $_REQUEST->has("title", "description")) {

    // Check field lengths
    if (!$_REQUEST->multi->serialize->length…150…150->strlen["title,description,homepage,changes"]) {
        print("<h3>Submission too short</h3> <p>You didn't fill out crucial information. Please note that our user base expects an enticing set of data points to find your project.</p>");
    }
    // Terms and conditions
    elseif (array_sum($_REQUEST->array->int->range…0…1["req"]) < 2) {
        print "<h3>Terms and Conditions</h3> <p>Please go back and assert that your open source project listing is reusable under the CC-BY-SA license.</p>";
    }
    elseif (!csrf(TRUE)) {
        print "<h3>CSRF token invalid</h3> <p>This is likely a session timeout (1 hour), etc. Please retry or login again.</p>";
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
            print "<h2>Submitted</h2> <p>Your project and release informations have been saved.</p>
                  <p>See the result in <a href=\"http://freshcode.club/projects/$name\">http://freshcode.club/projects/$name</a>.</p>";
        }
        else { 
            print "Unspecified database error. Please retry later.";
        }
    }

}


#-- Output input form with current $data
else {
    $data = array_map("input::html", $data);
    include("template/submit_form.php");
}


include("template/bottom.php");




?>