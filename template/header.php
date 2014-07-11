<!DOCTYPE html>
<html>
<head> 
    <title>freshcode.club</title>
    <meta name=version content=0.4.5>
    <meta charset=UTF-8>
    <link rel=stylesheet href="/freshcode.css?0.5.6">
    <link rel="shortcut icon" href="/img/changes.png">
    <base href="//<?= HTTP_HOST ?>/">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="/gimmicks.js"></script>
<?php if (isset($header_add)) { print $header_add . "\n"; } ?>
</head>
<body>

<nav id=topbar>
Open source community software release tracking. <small style="color:#9c7">[0.4.7 alpha]</small>
<span style=float:right>
<a href="//freshmeat.club/">freshmeat.club</a> |
<a href="//freecode.club/">freecode.club</a> |
<b><a href="//freshcode.club/">freshcode.club</a></b>
</span>
</nav>

<footer id=logo>
<a href="/" title="freshcode.club"><img src=logo.png width=200 height=110 alt=freshcode border=0></a>
<div class=empty-box>&nbsp;</div>
</footer>

<nav id=tools>
   <a href="/">Home</a>
   <a href="/submit" class=submit>Submit</a>
   <a href="/tags">Browse Projects by Tag</a>
   <a href="http://fossil.include-once.org/freshcode/wiki/About">About</a>
   <a href="/links">Links</a>
   <a href="http://www.opensourcestore.org/">Forum</a>
</nav>


