<?php
/**
 * api: freshcode
 * type: main
 * title: meta/forum
 * description: Simple threaded discussion / documentation forum.
 * version: 0.2
 *
 * Distinct layout from main site and harbours its own dispatcher.
 * Editing/post features. CSS is melted in, as there's no subpaging.
 *
 */


#-- custom config
include_once("./shared.phar");  // autoloader
define("INPUT_QUIET", 1) and
include_once("lib/input.php");  // input filter
define("HTTP_HOST", $_SERVER->id["HTTP_HOST"]);
include_once("lib/deferred_openid_session.php");  // auth+session
include_once("aux.php");        // utility functions
include_once("config.local.php");
include_once("lib/db.php");     // database API
db(new PDO("sqlite:forum.db")); // separate storage


#-- set up forum handling
$f = new forum();
$f->is_admin = in_array($_SESSION["openid"], $moderator_ids);


#-- dispatch functions
switch ($name = $_GET->id["name"]) {

    case "submit":
        exit( $f->submit() );

    case "post":
        exit( $f->submit_form($_REQUEST->int["pid"], 0) );

    case "edit":
        exit( $f->edit_form(0, $_REQUEST->int["id"]) );

    case "index":
    case "":
    default:
        // handled below per default
}
   
?>
<!DOCTYPE html>
<html>
<head>
    <title>freshcode.club forum</title>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="gimmicks.js?019"></script>
    <meta charset=UTF-8>
    <?= "<style>\n"
      . file_get_contents("forum.css")
      . "</style>";
    ?>
</head>
<body>
<div id=title>
   <h1><a href="/"><b>fresh</b>(code)<b class=red>.</b><span class=grey>club</span></a></h1>
</div>
<br>
<ul class=forum>

   <li>
      <div class=entry>
         <a class="action forum-new" data-id=0>New Thread</a>
      </div>
   </li>
   <?php
      $f->index();
    ?>
</ul>
</body>
</html>
