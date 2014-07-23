<?php
/**
 * api: freshcode
 * title: Freshcode.club config
 * description: initialization code
 * version: 0.5.0
 * plugin-register: include_once("$FN");
 * 
 *
 * Automatic and manual dependencies.
 * Base configuration.
 *
 */



// autoloader
include("./shared.phar");

// input filter
define("INPUT_QUIET", 1);
define("INPUT_DIRECT", "raw");
include_once("lib/input.php");

// database
include_once("lib/db.php");
db(new PDO("sqlite:freshcode.db"));

// auth+session
define("LOGIN_REQUIRED", 0);
define("HTTP_HOST", $_SERVER->id["HTTP_HOST"]);
include_once("lib/deferred_openid_session.php");

// utility functions
include_once("aux.php");

// List of moderator OpenID handles
$moderator_ids = array(
);


?>