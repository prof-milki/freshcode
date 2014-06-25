<?php
/**
 * OpenID login.
 *
 *
 *
 */


// initiate verification
if ($_POST->has("login_url")) {

    include_once("openid.php");

    $openid = new LightOpenID(HTTP_HOST);
    $openid->identity = $_POST->uri["login_url"];
    $openid->optional = array("namePerson/friendly");
    exit(header("Location: " . $openid->authUrl()));
}


// else display form
include("layout_header.php");
?> <section id=main> <?php
?>

<h3>Login</h3>

<p>Please provide an OpenID handle.</p>

<p>
<form action="" method=POST class=box>
  <input type=text name=login_url size=50 value="" placeholder="http://name.openid.xy/">
  <br>
  <input type=submit value=Login>
</form>
</p>

<p>There are intentionally no user accounts on freshcode.club,
but this prerequisite also helps eschew spam submissions.</p>

<?php
include("layout_bottom.php");
?>
