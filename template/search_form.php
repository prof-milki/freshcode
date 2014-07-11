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
$licenses = array_merge(array(""=>"*Any*"), $licenses);

?>

<h3>Search projects</h3>

<form action="/search" method=GET enctype="application/x-www-form-urlencode" accept-encoding=UTF-8>

    <label>
        Description
        <input name=q type=text size=50>
    </label>

    <label>
        Tags
        <input name=tag type=text size=50>
    </label>

    <label>
        License
        <select name=license>
            <?php print form_select_options($licenses, ""); ?>
        </select>
    </label>
    
    <label> 
       <input type=submit title=Search>
    </label>
    
</form>