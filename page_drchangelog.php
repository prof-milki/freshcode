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
  <p> So be careful with the <em>GitHub</em> module in particular. If you're not using their
      /release tool, it will pull a commit log. Only basic filtering is applied.</p>
  <p> Likewise write <em>Changelogs</em> as <b>summaries</b>. (That's a bit of a misnomer here,
      they're really NEWS or RELEASE-NOTES files.)</p>
  </small>
 </section>
</aside>
<section id=main> <?php


// run test
if ($_REQUEST->has("test")) {

    #-- prepare
    $run = new Autoupdate();
    $project = array(
         "name" => "testproject",
         "version" => "0.0.0.0.0.0.1",
         "homepage" => "",
         "download" => "",
         "urls" => "",
         "autoupdate_module" => $_REQUEST->name->in_array("autoupdate_module", "release.json,changelog,regex,github"),
         "autoupdate_url" => $_REQUEST->url["autoupdate_url"],
         "autoupdate_regex" => $_REQUEST->raw["autoupdate_regex"],
    );
    
    #-- exec
    $method = $run->map[$project["autoupdate_module"]];
    print "<h3>Results for <em>$method</em> extraction</h3>\n";
    $run->debug = 1;
    $result = $run->{$method}($project);
    var_dump($result);

}


// display form
else {

   $data = $_REQUEST->list->html["name,autoupdate_module,autoupdate_url,autoupdate_regex"];
   $data["autoupdate_regex"] or $data["autoupdate_regex"] = "\n\nversion = /Version ([\d.]+)/\n\nchanges = http://example.org/news.html\nchanges = $('article pre#release')\nchanges = ~ ((add|fix|change) \V+) ~mix*";

   $select = "form_select_options";
   print<<<FORM
   
   <h3>Dr. Changelog</h3> 
   <form action=drchangelog method=POST>
        <img src=img/drchangelog.png align=right alt="birdy big eyes" title="Don't ask me, I'm just a pictogram.">
        <p>
           Freshcode.club can be automated to track your software releases. There are
           <a href="http://fossil.include-once.org/freshcode/wiki/Autoupdate">various
           alternatives for</a> uncovering them. You can try them out here.

           <label>
               Retrieval method
               <select name=autoupdate_module>
                   {$select("release.json,changelog,regex,github", $data["autoupdate_module"])}
               </select>
           </label>

           <table border=0 cellpadding=3>
           <colgroup><col width=25% valign=top><col width=25% valign=top><col width=25% valign=top><col width=25% valign=top>
           <tr>
           <td><em>releases.json</em> defines a coherent scheme for publishing version
             and release infos.</td>
           <td>While a <em>Changelog</em> text file is likely the easiest, if you already have
             one, in an accepted formatting.</td>
           <td><em>Github</em> tags fetching uncovers <nobr>/releases</nobr> notes, or as last resort
             extracts your git commit log.</td>
           <td>Using <em>regex/xpath</em> is however the most universal way to extract from
             project websites.</td>
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
           <h4>Regex</h4>
           Screen scraping your own project website is often quite as simple. Define a list for at
           least <code>version = ...</code> and <code>changes = ...</code> - You can assign new URLs
           for each, and in the following rules mix any
           <a href="http://fossil.include-once.org/freshcode/wiki/AutoupdateRegex">→
           RegExp, XPath, or jQuerish</a> selectors.
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