<?php
/**
 * api: freshcode
 * title: Autoupdate runner
 * description: Cron job for invoking autoupdates on per-project basis
 * version: 0.6.0
 * depends: curl
 * author: mario
 * license: AGPL
 * x-cron: 15 03 * * *
 * 
 *
 * Each project listing can contain:
 *   `autoupdate_module` = none | regex | github | sourceforge | releases.json
 *   `autoupdate_url` = http://...
 *   `autoupdate_regex` = "version=/.../ \n changes=/.../"
 *
 * This module tries to load the mentioned reference URLs, extract version
 * and changelog, scope/state and download link; then updates the database.
 *
 */

// run in cron context
chdir(dirname(__DIR__));
include("config.php");

// go
$_SESSION["submitter"] = "";
$run = new Autoupdate();
$run->debug = 1;
$run->msg_html = 0;
$run->all();
#print_r($run->test("regex", "linux"));
#print_r($run->test("regex", "php"));
