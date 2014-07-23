<?php
/**
 * api: freshmeat
 * title: template auxiliary code
 * description: A few utility functions and data for the templates
 * version: 0.5
 * license: AGPL
 *
 * This function asortment prepares some common output.
 * While a few are parsing helpers or DB query shortcuts.
 *
 */



#-- Additional input filters


// Project names may be alphanumeric, and contain dashes
function proj_name($s) {
    return preg_replace("/[^a-z0-9-_]+|^[^a-z0-9]+|[^\w]+$|(?<=[-_])[-_]+/", "", strtolower($s));
}

// Tags is a comma-separated list, yet sometimes delimited with something else; normalize..
function f_tags($s) {
    return
        preg_replace(     # exception for "c++"
            ["/[-_.:]+/", "/(c\+\+(?=[\s,-]))?\+*/", "/[,;|]+/", "/[^a-z0-9,+\s-]+/", "/[,\s]+/", "/^\W+|\W+$/"],
            [  "-",           "$1",                    ",",             " ",            ", "    ,      ""      ],
            strtolower($s)
        );
}


#-- Template helpers

// Wrap tag list into links
function wrap_tags($tags, $r="") {
    foreach (str_getcsv($tags) as $id) {
        $id = trim($id);
        $r .= "<a href=\"/search?tag=$id\">$id </a>";
    }
    return $r;    
}

// Return DAY MONTH and TIME or YEAR for older entries
function date_fmt($time) {
    $lastyear = time() - $time > 250*24*3600;
    return strftime($lastyear ? "%d %b %Y" : "%d %b %H:%M", $time);
}



/**
 * Substitute `$version` placeholders in URLs.
 *
 * Supported syntax variations:
 *    →  $version and $version$
 *    →  %version and %version%
 *
 * And for substituting $version-number dots:
 *    →  $-$version  for which 1.2.3 becomes 1-2-3
 *    →  $_$version  for which 2.3.4 becomes 2_3_4
 *
 */
function versioned_url($url, $version) {
    $rx = "/
        ([ \$ % ])                  # var syntax
        (?: (.?) \\1 )?              # substitution prefix
        (version|Version|VERSION)   # 'version'
        (?= \\1 | \b | _ )          # followed by var syntax, wordbreak, or underscore
    /x";
    // Check for '$version'
    if (preg_match($rx, $url, $m)) {
        // Optionally replace dots in version string
        if ($m[2]) {
            $version = strtr($version, ".", $m[2]);
        }
        $url = preg_replace($rx, $version, $url);
    }
    return $url;
}



// Project listing output preparation;
// HTML context escapaing, versioned urls, formatted date string
function prepare_output(&$entry) {

    // versioned URLs
    $entry["download"] = versioned_url($entry["download"], $entry["version"]);
    
    // project screenshots
    if (TRUE or empty($entry["image"])) {
        if (file_exists($fn = "img/screenshot/$entry[name].jpeg")) {
            $entry["image"] = "/$fn?" . filemtime($fn);
        }
        else {
            $entry["image"] = "/img/nopreview.png";
        }
    }
    
    //
    $entry["formatted_date"] = date_fmt($entry["t_published"]);
    
    // HTML context
    $entry = array_map("input::_html", $entry);

    // user image
    $entry["submitter_img"] = submitter_gravatar($entry["submitter"]);
}


/**
 * Strip email@xyz from submitter name list (else just hash name),
 * return gravatar or identicon.
 *
 */
function submitter_gravatar(&$user, $size=24) {
    $rx = "/[^,;\s]+@[^,;\s]+/";
    $m = array($user);
    
    // capture+strip email
    if (is_int(strpos($user, "@")) and preg_match($rx, $user, $m)) {
        $user = trim(preg_replace($rx, "", $user), ",; ");
    }
    
    // return html <img> snippet
    return "<img src=\"//www.gravatar.com/avatar/" . md5(current($m))
         . "?s=$size&d=identicon&r=pg\" width=$size height=$size class=gravatar>";
}



// Social media share links
function social_share_links($name, $url) {
    $c = array("google"=>0, "facebook"=>0, "twitter"=>0, "reddit"=>0, "linkedin"=>0, "stumbleupon"=>0, "delicious"=>0);
    return <<<HTML
      <span class=social-share-links>
         <a href="https://plus.google.com/share?url=$url" title=google+> g&#65122; </a>
         <a href="https://www.facebook.com/sharer/sharer.php?u=$url" title=facebook> fb </a>
         <a href="https://twitter.com/intent/tweet?url=$url" title=twitter> tw </a>
         <a href="http://reddit.com/submit?url=$url" title=reddit> rd </a>
         <a href="https://www.linkedin.com/shareArticle?mini=true&amp;url=$url" title=linkedin> in </a>
         <a href="https://www.stumbleupon.com/submit?url=$url" title=stumbleupon> su </a>
         <a href="https://del.icio.us/post?url=$url" title=delicious> dl </a>
      </span>
HTML;
}
function social_share_count($num) {
    return empty($num) ? "" : "<var class=social-share-count>$num</var>";
}



/**
 * Write out pseudo pagination links.
 * This is just appended no matter the actually available entries.
 * The db() queries themselves handle the LIMIT/OFFSET, depending on a page param.
 *
 */
function pagination($page_no, $GET_param="n") {
    print "<p class=pagination-links> »";
    foreach (range($page_no-2, $page_no+9) as $n) if ($n > 0) {
        print " <a " . ($n==$page_no ? "class=current " : ""). "href=\"?n=$n\">$n</a> ";
    }
    print "« </p>";
}


// Output a list of select <option>s
function form_select_options($names, $value, $r="") {
    $map = is_string($names) ? array_combine($names = str_getcsv($names), $names) : $names;
    if ($value and !isset($map[$value])) { $map[$value] = $map[$value]; }
    foreach ($map as $id=>$title) {
        $r .= "<option" . ($id == $value ? " selected" : "") . " value=\"$id\" title=\"$title\">$id</option>";
    }
    return $r;
}


// CSRF token, only for logged-in users though
// Here they're mainly to prevent remotely initiated requests against other users, not general form nonces
function csrf($probe=false) {

    // Tokens are stored in session, reusable, but only for an hour
    $store = & $_SESSION["csrf"];
    foreach ($store as $id=>$time) {
        if ($time < time()) { unset($store[$id]); }
    }
    
    // Test presence
    if ($probe) {
        if (empty($_SESSION["openid"])) {
            return TRUE;
        }
        if ($id = $_REQUEST->name["_ct"]) {
            #var_dump($id, $store, isset($store[$id]));
            return isset($store[$id]);
        }
    }
    
    // Create new entry, output form field for token
    else {
        // server ENV already contained Apache unique request id etc.
        $id = sha1(serialize($_SERVER->__vars));
        $store[$id] = time() + 3600;  // timeout
        return "<input type=hidden name=.ct value=$id>";
    }
}





#-- Some string parsing


/**
 *  Plain comma-separated list
 *
 */
function p_csv($str) {
    return preg_split("/\s*,\s*/", trim($str));
}


/**
 *  Extracts key = value list.
 *  Keys may be wrapped in $, % or []
 *  Values may not contain spaces
 *
 */
function p_key_value($str) {
    preg_match_all(
        "@
           [[%$]*  ([-\w]+)  []%$]*
              \s*  [:=>]+  \s*
                   (\S+)
           (?<![,.;])
        @imsx",
        $str, $m
    );
    return array_change_key_case(array_combine($m[1], $m[2]), CASE_LOWER);
}




?>