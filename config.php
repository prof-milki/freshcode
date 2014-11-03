<?php
/**
 * api: freshcode
 * title: Freshcode.club config
 * description: initialization code
 * version: 0.7.0
 * plugin-register: include_once("$FN");
 * 
 *
 * Automatic and manual dependencies.
 * Base configuration.
 *
 */


// base
chdir(__DIR__);

// autoloader
include("./shared.phar");

// input filter
define("INPUT_QUIET", 1);
define("INPUT_DIRECT", "raw");
include_once("lib/input.php");

// database
include_once("lib/db.php");
db(new PDO("sqlite:freshcode.db"));
db()->in_clause = 0;

// auth+session
define("LOGIN_REQUIRED", 0);
define("CAPTCHA_REQUIRED", 0);
define("HTTP_HOST", $_SERVER->id["HTTP_HOST"]);
include_once("lib/deferred_openid_session.php");

// utility functions
include_once("aux.php");
define("FRESHCODE_USER_AGENT", "freshcode/0.7 (Linux x86-64; PHP/5.5.x) projects-autoupdate/0.7 (img,news,regex,xpath) github-poll/0.4 +http://freshcode.club/");
curl::$defaults["useragent"] = FRESHCODE_USER_AGENT;
ini_set("user_agent", FRESHCODE_USER_AGENT);

// List of administrative OpenID handles
$moderator_ids = array();
include("config.local.php");   


?>