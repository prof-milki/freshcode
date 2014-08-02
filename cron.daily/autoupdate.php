<?php
/**
 * api: freshcode
 * title: Autoupdate runner
 * description: Cron job for invoking autoupdates on per-project basis
 * version: 0.5.0
 * depends: curl
 * author: mario
 * license: AGPL
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
$run = new Autoupdate();
$run->all();
#print_r($run->test("github", "youtube-dl"));

