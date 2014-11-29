<?php
/**
 * title: statistics
 * description: generate statistics, visitors, projects, autoupdate, etc. for header
 * version: 0.1
 * depends:
 * category: template
 * type: cron
 * x-cron: 11 1 * * *
 *
 * Approximate weekly numbers by multiplication.
 *
 */

chdir(dirname(__DIR__)); 
include("config.php");


// get CloudFlare visitor stats
$json = curl()
   ->url("https://www.cloudflare.com/api_json.html")
   ->postfields([
       "a" => "stats",
       "tkn" => CLOUDFLARE_TKN,
       "email" => CLOUDFLARE_EMAIL,
       "z" => "freshcode.club",
       "interval" => 40,
    ])
   ->exec();
$stats = json_decode($json);
#print_r($stats);
$s_visitors = intval($stats->response->result->objs[0]->trafficBreakdown->uniques->regular * 4.1);
$s_pageviews = intval($stats->response->result->objs[0]->trafficBreakdown->pageviews->regular * 8.9);

// number of projects
$s_num_proj = db("SELECT COUNT(name) AS cnt FROM (SELECT name FROM release GROUP BY name)")->cnt;

// releases
$s_num_vers = db("SELECT COUNT(name) AS cnt FROM (SELECT DISTINCT name, version FROM release WHERE version != ?)", "")->cnt;

// autoupdating entries
$s_num_auto = db("SELECT COUNT(name) AS cnt FROM (SELECT name FROM release WHERE autoupdate_module != ? GROUP BY name)", "none")->cnt;
$s_num_auto = round($s_num_auto / $s_num_proj * 100, 1);

// admin infos
$s_flags = db("SELECT COUNT(reason) AS cnt FROM flags")->cnt;
$s_col = $s_flags ? "style=color:red" : "";
$s_spool = count($a_incoming = array_filter(glob("incoming/??*"), "is_file"));

#-- general freshcode stats
file_put_contents("template/stats.htm",
"
      <li> <var>$s_num_proj</var> projects
      <li> <var>$s_num_vers</var> releases
      <li> <var>$s_num_auto%</var> auto updating
      <li> <var>$s_visitors</var> visitors/wk
      <li> <var>$s_pageviews</var> recent pageviews
");

#-- moderator addendum
$a_incoming = join(", ", array_map("basename", $a_incoming));
file_put_contents("template/stats.admin.htm",
"
      <li> <var $s_col>$s_flags</var> <a href='/admin' style=color:grey>flags</a> · <span title='$a_incoming'><var>$s_spool</var> incoming</span>
"
);
