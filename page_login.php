<?php
/**
 * api: freshcode
 * type: intercept
 * title: OpenID login
 * description: Login page shows up for authorization-required sections (e.g. /submit)
 * version: 0.5
 *
 * Presents a login box, starts the OpenID auth process.
 * Has some JS default links for a few identity providers.
 * Also provides a /logout button now.
 *
 */


// initiate verification
if ($_POST->has("login_url")) {

    try {
        $openid = new LightOpenID(HTTP_HOST);
        $openid->verify_peer = false;
        $openid->identity = $_POST->uri["login_url"];
        $openid->optional = array("namePerson/friendly");
        exit(header("Location: " . $openid->authUrl()));
    }
    catch (ErrorException $e) {
        $error = $e->getMessage();
        exit(include("page_error.php"));
    }
}


// else
include("template/header.php");
?> <section id=main> <?php


// display login form
if (empty($_SESSION["openid"])) {

    print<<<HTML
    <h3>Login</h3>

    <p>Please provide an <a href="http://en.wikipedia.org/wiki/OpenID">OpenID</a> handle.</p>

    <p>
    <form action="" method=POST class="login box">
      <input type=url id=login_url name=login_url size=50 value="" placeholder="http://name.openid.xy/">
      <br>
      <input type=password style=display:none value=dummy>
      <input type=submit value=Login>
      <span class="service-logins">
         Or use your <a onclick="$('#login_url').val('http://facebook-openid.appspot.com/YourFaceBookLogin').focus().prop({selectionStart:35, selectionEnd:52});">Facebook</a>
               | <a onclick="$('#login_url').val('http://me.yahoo.com/#yourname').focus().prop({selectionStart:21, selectionEnd:29});">Yahoo</a> <br>
               | <a onclick="$('#login_url').val('http://launchpad.net/~yourname').focus().prop({selectionStart:22, selectionEnd:30});">Launchpad</a>
               | <a onclick="$('#login_url').val('https://openid.stackexchange.com/#yourname').focus().prop({selectionStart:34, selectionEnd:42});">StackOverflow</a>
      </span> 
    </form>
    </p>

    <p>There are intentionally no user accounts on freshcode.club,
    but this prerequisite also helps eschew spam submissions.</p>

HTML;
}

// drop relevant session data
elseif ($_REQUEST->id["name"] == "logout") {
    $_SESSION["openid"] = "";
    $_SESSION["user"] = "";
    print "<h3>Signed out</h3>";
}

// a previous login was already successful
else {

    print "<h3>Already logged in</h3>";
    
    print isset($login_hint)
        ? "<p>$login_hint</p>"
        : "<p>You have already associated an OpenID name (<var>$_SESSION[openid]</var>).
           <form action='/login/logout' method=POST><button>Logout</button></form></p>";
    
}

include("template/bottom.php");

?>