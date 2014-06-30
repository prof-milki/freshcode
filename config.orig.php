<?php
/**
 * api: freshcode
 * title: Freshcode.club config
 * description: initialization code
 * version: 0.2
 * plugin-register: include_once("$FN");
 * 
 *
 */

define("INPUT_QUIET", 1);
#define("INPUT_DIRECT", "trim");
include_once("input.php");

include_once("db.php");
db("connect", "sqlite:freshcode.db");
db()->in_clause = 0;
db()->tokens["fields"]
     = "name,title,homepage,description,license,tags,version,state,scope,changes,"
     . "download,urls,autoupdate_module,autoupdate_url,autoupdate_regex,"
     . "t_published,t_changed,flag,deleted,submitter_openid,submitter,lock,hidden,image";

define("LOGIN_REQUIRED", 0);
define("HTTP_HOST", $_SERVER->id["HTTP_HOST"]);
include_once("deferred_openid_session.php");


// List of moderator OpenID handles
$moderator_ids = array(
);

include_once("layout_aux.php");


?>