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
?> <section id=main> <?php


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
   $select = "form_select_options";
   print<<<FORM
   
   <h3>Dr. Changelog</h3> 
   <form action=drchangelog method=POST>
        <p>
           Freshcode.club can be automated to track your software releases. There are various
           alternatives for uncovering them. You can try them out here.

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
           Add the URL to your changelog, releases.json or github project here. For the regex method
           this will also be the first page to be extracted from.
        </p>

        <p>
           <h4>Regex</h4>
           <label>
               Extraction Rules (URLs, Regex, Xpath)
               <textarea cols=70 rows=10 name=autoupdate_regex placeholder="version = /-(\d+\.\d+\.\d+)\.txz/" maxlength=2500>$data[autoupdate_regex]</textarea>
               <small>
               <a href="http://fossil.include-once.org/freshcode/wiki/AutoupdateRegex">Regex automated updates</a>
               expect a list of <code>field=/regex/</code> names, like version=, changes=, download=, state=.
               Associatively-named "Other URLs" are also used for extraction.</small>
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