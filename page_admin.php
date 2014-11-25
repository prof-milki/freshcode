<?php
/**
 * api: freshcode
 * type: page
 * title: admin interface
 * description: Showcase user flags and allow to delete or hide project entries/revisions.
 * version: 0.1
 * depends: db
 *
 * User flags are collected in a separate `flags` table. Yet each project
 * entry contains a `flags` counter column as well (this is used by front-end
 * code to automatically hide too frequently flagged submissions)..
 *
 * CREATE TABLE flags
 *     (name TEXT, reason TEXT, note TEXT, submitter_openid TEXT, submitter_ip TEXT);
 *
 * The admin page lists from the flags table. Then allows to "delete" certain
 * revisions, or just mark them as hidden.
 *
 */



// Moderator authorization already handled by dispatcher (index.php)
include("template/header.php");
?>
  <section id=main>
<?php
$name = $_REQUEST->proj_name["name"];


// Just list flags+projects
if (empty($name)) {
    print "<h3>Flagged entries</h3> <dl>";

    // query flags table, but associate data from last release
    $flags = db("SELECT * FROM flags
                 LEFT JOIN release_versions ON flags.name=release_versions.name");
   
    // just output admin/PROJID links
    while ($row = $flags->fetch()) {
        $row = array_map("input::html", $row);
        print <<<HTML
           <dt><a href="admin/$row[name]">$row[name]</a> (<em>$row[flag]</em> flags on #$row[t_published])</dt>
           <dd><b>$row[reason]</b>
               $row[note]
           </dd>
HTML;
    }
    
}

// Show entry + respond to actions
else {


    /**
     * Apply actions
     *
     *   → Actions in `action[field][]=name and action[value][]=value`
     *   → Revisions are  lists of `select[t_published][] = t_changed`
     *
     */
    if ($_POST->has("action")) {
    
        // Merge action keys and values
        $action = $_POST->raw["action"];
        $action = array_combine(
            array_intersect_key($action["field"], $action["value"]),
            array_intersect_key($action["value"], $action["field"])
        );
        var_dump($action);

        // Run trough actions
        foreach ($action as $field=>$value) if (strlen($field)) {
            // Update DB for each revision in select[][]
            foreach ($_POST->raw["select"] as $t_published => $t_changed) {
                db("UPDATE release
                    SET :? = ?
                    WHERE name=? AND t_published=? AND t_changed IN (??)",
                    array($field), $value,
                    $name, $t_published, $t_changed
                );
            }
        }
        
        // Manually empty `flags` table
        if ($action["flag"] === "0") {
            db("DELETE FROM flags WHERE name=?", $name);
        }
    }



    /**
     * Get all revisions and flags for project name
     *
     *
     */
    $entries = db("SELECT * FROM release WHERE name=? ORDER BY t_published ASC, t_changed ASC", $name)->fetchAll();
    $flags = db("SELECT * FROM flags WHERE name=?", $name)->fetchAll();


    // Start <form>
    print "<h3>Edit '$name' revisions</h3>  Oldest to newest.
           <form action='admin/$name' method=POST>         
          ";

    // Show all flagging notes
    foreach ($flags as $row) {
        $row = array_map("input::html", $row);
        print "<li>Flag: <b>$row[reason]</b><br>Note: <em>$row[note]</em><br>By: <u>$row[submitter_openid]</u></li><br>";
    }

    
    // Print each revision;
    foreach ($entries as $rev=>$row) {
    
        // current, last, and next row
        $row = array_map("input::html", $row);
        $last = isset($entries[$rev-1]) ? array_map("input::html", $entries[$rev-1]) : $row;
        $next = isset($entries[$rev+1]) ? array_map("input::html", $entries[$rev+1]) : $row;

        // Version header
        $date = strftime("%Y-%m-%d %T", $row["t_changed"]);
        print "
        <br>
        <table class='rc admin'>
        <tr>
          <th>
             <input type=checkbox name='select[$row[t_published]][]' value='$row[t_changed]'>
             rev=$rev
          </th>
          <th>
             pub=$row[t_published]
             chg=$row[t_changed] <small>($date)</small>
          </th>
        </tr>";
        
        // Fields
        foreach ($row as $f=>$v) {
            $v = pdiff::tridiff($last[$f], $v, $next[$f]);
            if (in_array($f, ["t_published", "t_changed"])) {
                $v .= " <small>(" . strftime("%Y-%m-%d %T", intval($row[$f])) . ")</small>";
            }
            if (in_array($f, ["hidden","flag","deleted","name","t_changed","version"])) {
                $f = "<em>$f</em>";
            }
            print "<tr><td>$f</td><td>$v</td>";
        }
        print "</table>";
    }

    
    /**
     * Print `action` form fields
     *
     *  → Assocciatively as `action[field][] = dbfield`
     *                  and `action[value][] = vakue`
     *  → Applying depends an the field being non-empty
     *    (for unchecked checkboxes they are).
     *
     */
    print <<<HTML
    <h4>Actions</h4>
    <table class=rc>
    <tr>
      <td><label>
        <input type=checkbox name="action[field][0]" value="hidden">
        <input type=hidden name="action[value][0]" value="1">
        <b>Set hidden</b>
      </label></td>
      <td>so the revision will no longer show up on the frontpage. </td>
    </tr>
    <tr>
      <td><label>
        <input type=checkbox name="action[field][1]" value="deleted">
        <input type=hidden name="action[value][1]" value="1">
        <b>Mark deleted</b>
      </label></td>
      <td> to terminate a project revision line </td>
    </tr>
    <tr>
      <td><label>
        <input type=checkbox name="action[field][2]" value="flag" checked>
        <input type=hidden name="action[value][2]" value="0">
        <b>Unset flags</b>
      </label></td>
      <td> to clear flagging history when finished. </td>
    </tr>
    <tr><th>Update field</th><th>with value</th></tr>
    <tr>
      <td> <input type=text name="action[field][3]" value=""> </td>
      <td> <input type=text name="action[value][3]" value="" size=40> </td>
    </tr>
    <tr>
      <td> <input type=text name="action[field][4]" value=""> </td>
      <td> <input type=text name="action[value][4]" value="" size=40> </td>
    </tr>
    <tr>
      <td> <input type=text name="action[field][5]" value=""> </td>
      <td> <input type=text name="action[value][5]" value="" size=40> </td>
    </tr>
    </table>
    <br>
    <input type=submit value=Apply>
    <br>
    <br>
HTML;
}



?>