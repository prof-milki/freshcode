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

<p>Please provide an <a href="http://en.wikipedia.org/wiki/OpenID">OpenID</a> handle.</p>

<p>
<form action="" method=POST class="login box">
  <input type=text id=login_url name=login_url size=50 value="" placeholder="http://name.openid.xy/">
  <br>
  <input type=password style=display:none value=dummy>
  <input type=submit value=Login>
  <span class="service-logins">
     <!--Or with <a onclick="$('#login_url').val('https://www.google.com/accounts/o8/id');">Google</a>-->
  </span> 
</form>
</p>

<p>There are intentionally no user accounts on freshcode.club,
but this prerequisite also helps eschew spam submissions.</p>

<?php
include("layout_bottom.php");
?>
