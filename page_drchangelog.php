<?php
/**
 * type: page
 * title: Dr. Changelog
 * description: Tool to experiment and try out Autoupdate modules
 * version: 0.1
 * license: AfferoLGPL
 *
 * Reuses fields from /submit form to start a live check run with
 * actual Autoupdate modules.
 *
 */


$header_add = "<meta name=robots content=noindex>";
include("template/header.php");
?>
<aside id=sidebar>
 <section>
  <h5>Know your audience</h5>
  <small>
  <p> Whatever source you choose for release announcements, try to keep them <b>user-friendly</b>. </p>
  <p> End users aren't fond of commit logs. While "merged pull request XY" might be technically
      highly relevant (for e.g. libraries), it's gibberish to most everyone else.</p>
  <p> So be careful with the <em>GitHub</em> module in particular. If you're not using githubs
      /release tool, a commit log may be used still. Only basic filtering is applied.</p>
  <p> Likewise write <em>Changelogs</em> as <b>summaries</b>. (They're better and more correctly called NEWS
      or RELEASE-NOTES files actually.)</p>
  </small>
 </section>
</aside>
<section id=main> <?php


#-- Output formatted results
class TestProject extends ArrayObject {
    function update($result) {
        #-- output formatted
        print "<dl>\n";
        foreach ($result as $key=>$value) {
            print "<dt><b>$key</b></dt>\n<dd>" . input::html($value) . "</dd>\n";
        }
        print "</dl>";
    }
}


// run test
if ($_REQUEST->has("test")) {

    #-- prepare
    $run = new Autoupdate();
    $run->debug = 1;
    $project = new TestProject(array(
         "name" => "testproject",
         "version" => "0.0.0.0.0.0.1",
         "homepage" => "",
         "download" => "",
         "urls" => "",
         "autoupdate_module" => $_REQUEST->id->in_array("autoupdate_module", "none,release.json,changelog,regex,github"),
         "autoupdate_url" => $_REQUEST->url["autoupdate_url"],
         "autoupdate_regex" => $_REQUEST->raw["autoupdate_regex"],
    ));
    
    #-- exec
    print "<h3>Results for <em>$method</em> extraction</h3>\n";
    $method = $run->map[$project["autoupdate_module"]];
    $result = new TestProject($run->{$method}($project));
    $result->update($result);
}


// display form
else {

   $data = $_REQUEST->list->html["name,autoupdate_module,autoupdate_url,autoupdate_regex"];
   $data["autoupdate_regex"] or $data["autoupdate_regex"] = "\n\nversion = /Version ([\d.]+)/\n\nchanges = http://example.org/news.html\nchanges = $('article pre#release')\nchanges = ~ ((add|fix|change) \V+) ~mix*";
   $current_date = strftime("%Y-%m-%d", time());

   $select = "form_select_options";
   print<<<FORM

<style>
/**
 * page-specific layout
 *
 */
.autoupdate-alternatives { border-spacing: 5pt; }
.autoupdate-alternatives td {
    padding: 3pt;
    width: 25%;
    vertical-align: top;
    background: #fcfcfc linear-gradient(to bottom, #f7f0e9, #fff);
    box-shadow: 2px 2px 3px 1px #f9f5f1;
    border-radius: 10pt;
    font-size: 95%;
}
.autoupdate-alternatives td .hidden {
    position: absolute;
    display: none;
}
.autoupdate-alternatives td:hover .hidden {
    display: block;
}
.autoupdate-alternatives td .hidden pre {
    position: relative; top: -30pt; left: -30pt;
    padding: 7pt;
    border: 7px solid #111;
    border-radius: 7pt;
    background: #f7f7f7;
    background-image: radial-gradient(circle at 50% 50%, rgb(255,255,255), rgb(244,244,244));
}
li {
    padding: 1.5pt;
}
</style>
   
   <h3>Dr. Changelog</h3> 
   <form action=drchangelog method=POST>
        <img src=img/drchangelog.png align=right alt="birdy big eyes" title="Don't ask me, I'm just a pictogram.">
        <p>
           Freshcode.club can automatically track your software releases. There are
           <a href="http://fossil.include-once.org/freshcode/wiki/Autoupdate">various
           alternatives for</a> uncovering them. Try them out.

           <label>
               Retrieval method
               <select name=autoupdate_module>
                   {$select("release.json,changelog,regex,github", $data["autoupdate_module"])}
               </select>
           </label>

           <table class=autoupdate-alternatives><tr>
           <td>
             <a href="http://fossil.include-once.org/freshcode/wiki/releases.json"><em>releases.json</em></a>
             defines a concrete scheme for publishing version and release notes.
<span class=hidden><pre>
{
  "version": "1.0.0",
  "changes": "Fixes and adds lots
              of new functions ..",
  "state": "stable",
  "scope": "major feature",
  "download": "http://exmpl.org/"
}
</pre></span>
             </td>
           <td>While a <a href="http://fossil.include-once.org/freshcode/wiki/AutoupdateChangelog"><em>Changelog</em></a>
             text file is likely the easiest, given a coherent format and style.
<span class=hidden><pre>
1.0.0 ($current_date)
------------------
 * Changes foo and bar.
 + Adds baz baz.
 - Some more bugs removed.
 
0.9.9 (2014-02-27)
------------------
 * Now uses Qt5
 - Removed all the bugs

0.9.1 (2014-01-20)
------------------
 * Initial release with
</pre></span>
             </td>
           <td><a href="http://fossil.include-once.org/freshcode/wiki/AutoupdateGithub"><em>Github</em></a>
            extraction prefers <nobr>/releases</nobr> notes. But may as last resort condense a git commit log.
<span class=hidden><pre><a href="https://github.com/blog/1547-release-your-software"><img src="https://camo.githubusercontent.com/9f23f54df9e2f69047fb0f9f80b2e33c8339606f/68747470733a2f2f662e636c6f75642e6769746875622e636f6d2f6173736574732f32312f3733373136362f62643163623637652d653332392d313165322d393064312d3361656365653930373339662e6a7067" width=400 height=200></a></pre></span>
            </td>
           <td>Using <a href="http://fossil.include-once.org/freshcode/wiki/AutoupdateRegex"><em>regex/xpath</em></a>
             is however the most universal way to extract from project websites.
<span class=hidden><pre>
<span style=color:gray># load page</span>
changes = http://exmpl/news

<span style=color:gray># jQuery</span>
changes = $("body .release")
 
<span style=color:gray># RegExp</span>
version = /Version \d+\.\d+/

</pre></span>
             </td>
           </tr></table>

        </p>
        <p>
           <label>
               Autoupdate URL
               <input name=autoupdate_url type=url size=80 value="$data[autoupdate_url]" placeholder="https://github.com/user/repo/tags.atom" maxlength=250>
           </label>
           Add the URL to your Changelog, releases.json, or GitHub project here. For the regex method
           this will also be the first page to be extracted from.
        </p>

        <p>
           <h4>Content Scraping</h4>
           Picking out from your own project website can be surprisingly simple. Define a list for at
           least <code>version = ...</code> and <code>changes = ...</code> - Add source URLs
           and apply
           <a href="http://fossil.include-once.org/freshcode/wiki/AutoupdateRegex">
           RegExp, XPath, or jQuery</a> selectors for extraction.
           <label>
               Extraction Rules <em>(URLs, Regex, Xpath, jQuery)</em>
               <textarea cols=67 rows=10 name=autoupdate_regex placeholder="version = /-(\d+\.\d+\.\d+)\.txz/" maxlength=2500>$data[autoupdate_regex]</textarea>
               <small>
               <li>Assigning new URLs is only necessary when there's different data to extract from.</li>
               <li>RegExps like <code>version = /Changes for ([\d.]+)/</code> often match headlines well.</li>
               <li>A common XPath rule for extracting the first bullet point list is <code>changes = (//ul)[1]/li</code>.</li>
               <li>While <code>changes = $("section#main article .release")</code> narrows it down
                   for HTML pages.</li>
               <li>You often can mix extractors, first an XPath/jQuery expression, then a RegExp.</li>
               <li>Rules for state=, scope= and download= are optional.</li>
               </small>
           </label>
        </p>
        <p>
          <input type=submit name=test value=Test-Run>
        </p>
   </form>
FORM;
}


include("template/bottom.php");

?>