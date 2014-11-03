<?php
/**
 * api: freshcode
 * title: github-releases
 * description: dump releases feed
 * version: 0.3
 *
 * Shows the summarized GitHub releases from the stored template dump
 * (updated by cron.daily/news_github.php from GHA and cache DB.)
 *
 */

include("template/header.php");
?>

  <style>
    .github-releases {
       width: 100%;
    }
    #githubreleases {
       table-layout: fixed;
       width: 100%;
    }
    .github.release td {
       padding: 4pt 1pt;
       font-size: 95%;
       overflow: hidden;
       text-overflow: ellipsis;
       box-shadow: none;
    }
    .github.release .author-avatar {
    }
    .github.release .repo-name {
    }
    .github.release .repo-name small {
       display: block;
       font-size: 85%;
       color: #555;
    }
    .github.release .repo-name strong {
       font-weight: 400;
       display: block;
    }
    .github.release .repo-description {
       font-size: 90%;
    }
    .github.release .repo-homepage {
       font-size: 70%;
       display: block;
    }
    .github.release .release-tag {
       font-weight: 700;
    }
    .github.release .release-body {
       font: 70%/80% normal;
       max-height: 25pt;
       color: #999;
    }
    .github-releases .repo-language {
       font-size: 60%;
       padding: 0.5pt 1pt;
       border: dotted 1px #eef;
       background: #f1f3ff;
       color: #aae;
    }
  </style>

  <section id=main style="width:70%">
  <h2>GitHub Releases</h2>
  <article class=github-releases>

     <table id=githubreleases>
     <colgroup>
        <col width="5%">
        <col width="25%">
        <col width="35%">
        <col width="35%">
     </colgroup>
<?php include("template/github-releases.htm"); ?>
     </table>

  </article>

  <p style="break: both; clear: all; background: #f3f5f7; padding: 20pt;;">
    Project information courtesy of
    <a href="http://githubarchive.org/">http://githubarchive.org/</a>
    and the GitHub API.
  </p>

<?php
include("template/bottom.php");

