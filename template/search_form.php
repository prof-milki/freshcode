<?php
/**
 * api: include
 * type: template
 * title: Search from
 * description: Show simple search <form>
 *
 *
 */

$select = "form_select_options";
$licenses = array_slice(tags::$licenses, 1);

?>

<h3>Search projects</h3>

<form action="/search" method=GET enctype="application/x-www-form-urlencode" accept-encoding=UTF-8>

    <label>
        Description
        <input name=q type=text size=50 style=height:24pt>
        <small>Search in project titles and descriptions.</small>
    </label>

    <label>
        Tags
        <input name=tag type=text size=50>
        <small>Comma-separated list of tags you want to include.</small>
    </label>

    <label>
        Licenses<br>
        <select name="license[]" multiple size=3>
            <?php print form_select_options($licenses, NULL); ?>
        </select>
        <small>Constrain results to a specific libre / open source / free software licenses.</small>
    </label>
    
    <label> 
       <input type=submit title=Search>
    </label>
    
</form>