<?php
/**
 * api: freshcode
 * title: Freshcode.club config
 * description: initialization code
 * version: 0.4.2
 * plugin-register: include_once("$FN");
 * 
 *
 */

// input filter
define("INPUT_QUIET", 1);
#define("INPUT_DIRECT", "trim");
include_once("lib/input.php");

// database
include_once("lib/db.php");
db(new PDO("sqlite:freshcode.db"));
#db()->tokens["fields"]
#     = "name,title,homepage,description,license,tags,version,state,scope,changes,"
#     . "download,urls,autoupdate_module,autoupdate_url,autoupdate_regex,"
#     . "t_published,t_changed,flag,deleted,submitter_openid,submitter,lock,hidden,image";

// auth+session
define("LOGIN_REQUIRED", 0);
define("HTTP_HOST", $_SERVER->id["HTTP_HOST"]);
include_once("deferred_openid_session.php");

// utility functions
include_once("aux.php");
include_once("release.php");

// List of moderator OpenID handles
$moderator_ids = array(
);


?>