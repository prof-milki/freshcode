<?php
/**
 * type: page
 * title: Flagging
 * description: Allows users to flag project listings for moderator attention.
 *
 * A submission here will both increase the `release`.`flag` counter,
 * as well as leave a documentation entry in the `flags` table.
 *
 */


include("template/header.php");
?> <section id=main> <?php

$name = $_REQUEST->proj_name["name"];

// submit
if ($_REQUEST->has("reason", "note", "name")) {

    
    // exists
    if (db("SELECT note FROM flags WHERE name=? and submitter_openid=?", $name, $_SESSION["openid"])->fetch()) {
    
        print "<h2>Flag exists</h2> <p>You have previously flagged this entry. Please give us some time to deal with it.</p>";

    }
    
    // new flag
    else {
        $reason = $_REQUEST->nocontrol->ascii->text["reason"];
        $note = $_REQUEST->nocontrol->ascii->text["note"];
    
        // Into `flags` table
        db("INSERT INTO flags
            (reason, note, name, submitter_openid, submitter_ip)
            VALUES (?,?,?,?,?)",
            $reason, $note, $name, $_SESSION["openid"], $_SERVER->ip["REMOTE_ADDR"]
        );
       
        // Increase `release`.`flag` column (just the last entry)
        db("UPDATE release SET flag=flag+1
            WHERE name=?  ORDER BY t_published DESC, t_changed DESC  LIMIT 1",
            $name
        );

        print "<h2>Thank you</h2> <p>We'll investigate. Thanks for your time and attention!</p>";

    }
}

// notifaction form
else {

    print <<< HTML

      <h2>Flag "$name" for moderator attention</h2>
      <p>
          If you feel there's a project listing here that has issues,
          just tell us.
      </p>
      
      <p>
         <form action="" method=POST enctype="multipart/form-data" accept-encoding="UTF-8">
         
             <input type=hidden name=name value="$name">
         
             <label>
                <input type=radio name=reason value=spam> It's just spam.
             </label>

             <label>
                <input type=radio name=reason value=non-english> Listing is not in English.
             </label>

             <label>
                <input type=radio name=reason value=low-quality> Low quality / formatting issues.
             </label>

             <label>
                <input type=radio name=reason value=urls> URLs are no longer working.
             </label>

             <label>
                <input checked type=radio name=reason value=other> Other (use the note box below in either case).
             </label>

             <label>
                <textarea name=note cols=50 rows=5 placeholder="Moderators, I summon thee, because ..."></textarea>
             </label>

             <label>
                <input type=submit value=Submit>
             </label>

         </form>
      </p>
      
      <p>This is also a reasonable contact mechanism if you want to report another type
      of bug. For reclaiming a lost OpenID logon please preferrably contact us per mail.</p>
      
HTML;
}


include("template/bottom.php");

?>