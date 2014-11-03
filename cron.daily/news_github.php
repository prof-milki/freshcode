<?php
/**
 * title: github releases news feed
 * description: fetch from cache database, build feed and releases page
 * version: 0.3
 *
 * Short summary
 *     → ./template/feed.github.htm
 * Long table
 *     → ./template/github-releases.htm
 *
 */


chdir(dirname(__DIR__));
include("./config.php");
db(new PDO("sqlite:github.db"));


// query
$recent = db("
   SELECT *
     FROM (
       SELECT *
         FROM releases
        WHERE LENGTH(repo_description) > 0
     GROUP BY repo_name
     ORDER BY t_published DESC
        LIMIT 500
          )
  ORDER BY repo_language
");

// prepare output
$lang = "";
$out = [];
$full = "";

// printout
foreach ($recent as $r) {
    $r = array_map("htmlspecialchars", $r);

    
    #-- filter some
    if (preg_match("~/(test|main)$~", $r["repo_name"])
    or  preg_match("~Contribute to .+? by creating an account on GitHub~", $r["repo_description"]))
    {
       continue;
    }


    #-- sidebar feed
    if (count($out) < 25) {
       $name = trim(strstr($r["repo_name"], "/"), "/");
       $out[] = "   <a href='$r[release_url]' title='$r[repo_description]'>$name "
              . "<em class=version title='$r[release_title]'>$r[release_tag]</em></a>";
    }


    #-- complete list
    $verdesc = input::spaces(substr($r["release_body"], 0, 200));
    $name = explode("/", $r["repo_name"]);

    // project blob    
    $full .= <<<HTML
 <tr class="github release">
    <td class=author-avatar><img src="$r[author_avatar]&s=40" alt="$r[author_login]" height=40 width=40></td>
    <td class=repo-name>
       <a href='$r[repo_url]' title='$r[repo_name]'>
          <small class=repo-org>$name[0] /</small>
          <strong class=repo-localname>$name[1]</strong>
       </a>
       <span class=repo-language>$r[repo_language]<span>
    </td>
    <td class=repo-description>
       $r[repo_description]
       <a class=repo-homepage href="$r[repo_homepage]">$r[repo_homepage]</a>
    </td>
    <td class=release>
        <a class=release-tag href='$r[release_url]'><em class=version title='$r[release_title]'>$r[release_tag]</em></a>
        <span class=release-body>$verdesc</span>
    </td>
 </tr>
 
HTML;
}

// write
file_put_contents("./template/feed.github.htm", implode("\n", $out));
file_put_contents("./template/github-releases.htm", $full);


