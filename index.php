<?php
/**
 * api: php
 * title: Freshcode.club
 * description: FLOSS software release tracking website
 * version: 0.3
 * author: mario
 * license: AGPL
 * 
 * Implements a freshmeat/freecode-like directory for open source
 * release publishing / tracking.
 *
 */


#-- init
include("config.php");


#-- dispatch
switch ($page = $_GET->id["page"]) {

    case "index":
    case "projects":
    case "feed":
    case "links":
    case "tags":
    case "login":
        include("page_$page.php");
        break;

    case "flag":
    case "submit":
        if (LOGIN_REQUIRED and empty($_SESSION["openid"])) {
            exit(include("page_login.php"));
        }
        include("page_$page.php");
        break;

    case "admin":
        if (!in_array($_SESSION["openid"], $moderator_ids)) {
            exit(include("page_login.php"));
        }
        include("page_admin.php");
        break;

    default:
        include("page_error.php");
        
}


?>