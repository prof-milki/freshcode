<?php
/**
 * api: freshcode
 * type: template
 * title: HTML page header
 * description: Starts <html> and <head>, outputs top bar / menus etc.
 * version: 0.7.3
 *
 * Optionally injects a `$header_add` list, or allows to override the
 * page $title.
 *
 */
?>
<!DOCTYPE html>
<html>
<head> 
    <title><?= isset($title) ? $title : "freshcode.club" ?></title>
    <meta name=version content=0.7.3>
    <meta charset=UTF-8>
    <link rel=stylesheet href="/freshcode.css?0.7.3">
    <link rel="shortcut icon" href="/img/changes.png">
    <base href="/index">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <!--[if lt IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv.min.js"></script><![endif]-->
    <script src="/gimmicks.js?2"></script>
    <?php if (isset($header_add)) { print $header_add . "\n"; } ?>
</head>
<body>

<nav id=topbar>
Open source software release tracking.
<?= is_int(strpos(HTTP_HOST, ".")) ? '<small style="color:#9c7" class=version>[0.7.3 alpha]</small>' : '<b style="color:#c54">[local dev]</b>'; ?>
<span style=float:right>
<a href="//freshmeat.club/">freshmeat.club</a> |
<a href="//freecode.club/">freecode.club</a> |
<b><a href="//freshcode.club/">freshcode.club</a></b>
</span>
</nav>

<footer id=logo>
<a href="/" title="freshcode.club"><img src="img/logo.png" width=200 height=110 alt=freshcode border=0></a>
<?=file_get_contents("template/stats.htm");?>
</footer>

<nav id=tools>
   <a href="/">Home</a>
   <a href="/submit" class=submit>Submit</a>
   <span class=submenu>
      <a href="/names">Browse</a>
      <a href="/tags" style="left:-5pt;">Projects by Tag</a>
   </span>
   <form id=search_q style="display:inline" action=search><input name=q size=5><a href="/search">Search</a></form>
   <a href="//fossil.include-once.org/freshcode/wiki/About">About</a>
   <a href="/links">Links</a>
   <a href="/meta" class=meta>Meta</a>
</nav>


