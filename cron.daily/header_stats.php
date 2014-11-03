<?php
/**
 * title: statistics
 * description: generate statistics, visitors, projects, autoupdate, etc. for header
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


file_put_contents("template/stats.htm",
<<<HTML
   <ul id=stats>
      <li> <var>$s_num_proj</var> projects
      <li> <var>$s_num_vers</var> releases
      <li> <var>$s_num_auto%</var> auto updating
      <li> <var>$s_visitors</var> visitors/wk
      <li> <var>$s_pageviews</var> recent pageviews
   </ul>
HTML
);
