<?php
/**
 * type: page
 * title: Error info
 * description: Generic error page
 * version: 0.1
 * license: -
 *
 * Drop-in page for generic error message
 * or anything pushed in via `$error`.
 *
 */


include("template/header.php");
?> <section id=main> <?php

print "<h2>Error</h2>\n";

print isset($error) ? "<p>$error</p>" : "<p>Sorry. Some problem occured (entry not accessible etc.)</p>";


include("template/bottom.php");

?>