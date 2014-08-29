<?php
/**
 * api: php
 * title: Session startup
 * description: Avoids session startup until actual login occured
 * license: MITL
 * version: 0.3.1
 *
 * Start $_SESSION only if there's already a session cookie present.
 * (Prevent needless cookies and tracking ids for not logged-in users.)
 *
 * The only handler that initiates any login process is `page_login.php`
 *
 */



// Kill off CloudFlare cookie when Do-Not-Track header present
if ($_SERVER->has("HTTP_DNT") and $_SERVER->boolean["HTTP_DNT"]) {
    header("Set-Cookie: __cfduid= ; path=/; domain=.freshcode.club; HttpOnly");
}





// Check for pre-existant cookie before defaulting to initiate session store
if ($_COOKIE->has("USER")) {
    session_fresh();
}
// just populate placeholders
else {
    $_SESSION["openid"] = "";
    $_SESSION["name"] = "";
    $_SESSION["csrf"] = array();
}


// verify incoming OpenID request
if ($_GET->has("openid_mode") and empty($_SESSION["openid"])) {

    try {
        $openid = new LightOpenID(HTTP_HOST);
        $openid->verify_peer = false;
        if ($openid->mode) {
            if ($openid->validate()) {
                $_COOKIE->no("USER") and session_fresh();
                $_SESSION["openid"] = $openid->identity;
                $_SESSION["name"] = $openid->getAttributes()["namePerson/friendly"];
            }
        }
    }
    catch (ErrorException $e) {
        die("OpenID verify exception (possibly endpoint / SSL error)");
    }

}

#session_write_close();


// Prevent some session tampering
function session_fresh() {

    // Initiate with current session identifier
    if ($_COOKIE->has("USER")) {
        session_id($_COOKIE->id["USER"]);
    }
    session_name("USER");
    session_set_cookie_params(0, "/", HTTP_HOST, false, true);
    session_start();

    // Security by obscurity: lock client against User-Agent
    $useragent = $_SERVER->text->length…30["HTTP_USER_AGENT"];
    // Security by obscurity: IP subnet lock (or just major route for IPv6)
    $subnet = $_SERVER->ip->length…6["REMOTE_ADDR"];
    // Server-side timeout (7 days)
    $expire = time() + 7 * 24 * 3600;

    // New ID for mismatches
    if (empty($_SESSION["state/client"]) or $_SESSION["state/client"] != $useragent
    or  empty($_SESSION["state/subnet"]) or $_SESSION["state/subnet"] != $subnet
    or  empty($_SESSION["state/expire"]) or $_SESSION["state/expire"] < time()
    ) {
        session_destroy();
        session_regenerate_id(true);
        session_start();
    }
    // and Repopulate status fields
    $_SESSION["state/client"] = $useragent;
    $_SESSION["state/subnet"] = $subnet;
    $_SESSION["state/expire"] = $expire;
}


