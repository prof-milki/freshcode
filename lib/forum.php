<?php
/**
 * api: php
 * type: handler
 * title: Follow The Thread
 * description: Straightforward threaded discussion forum
 * version: 0.2
 * category: discussion
 * depends: HTMLPurifier
 * config:
 *    <var name="forum_cfg[categories]" type="list" default="discussion,documentation" help="Comma-separated list of thread classifiers"/>
 *
 *
 * Implements a minimalistic web forum.
 * Primary goals are:
 *   → Threaded discussions in place of bulletin board blabber.
 *   → Contemporary security over restriction gimmicks.
 *   → Usability instead of UI featuritis.
 *   → Open access in lieu of accounteritis.
 *
 * Single table database:
 *    [id] INT PRIMARY KEY NOT NULL UNIQUE,
 *    [pid] INT NOT NULL DEFAULT(0) REFERENCES [forum] ([id]),
 *    [gid] INT NOT NULL REFERENCES [forum] ([id]),
 *    [tag] VARCHAR (0, 32) NOT NULL DEFAULT('discussion'),
 *    [summary] VARCHAR (0, 200) NOT NULL,
 *    [source] TEXT,
 *    [html] TEXT,
 *    [excerpt] TEXT,
 *    [author] VARCHAR (0, 80),
 *    [miniature] TEXT,
 *    [t_published] INT NOT NULL,
 *    [edit_token] VARCHAR (16, 64)
 *
 * Templating
 * Behaviour
 * Configuration
 *
 *
 *
 */


/**
 * Decorative classification/grouping of threads.
 *
 */
global $forum_cfg;
$forum_cfg["categories"] = "discussion,projects,announcement,code,documentation,autoupdate";



/**
 * Callbacks, dispatcher and handler.
 *
 */
class forum {


    /**
     * Can be set externally, depending on application logic.
     *
     */
    var $is_admin = 0;
    var $can_edit = 1;



    /**
     * NOP
     *
     */
    function __construct() {
    }


    /**
     * Show forum listing
     *
     */
    function index($page=0) {
    
        // Fetch thread groups (attached to root gid=0)
        $entries = db("
            SELECT *
              FROM forum
             WHERE gid
                IN ( SELECT id
                       FROM forum
                      WHERE pid = 0
                   ORDER BY t_published DESC
                      LIMIT 50
                     OFFSET ?*50 )
          ORDER BY gid DESC,
                   t_published ASC
        ", $page);
        
        // Iterate over groups
        $last = 0;
        $group = array();
        foreach ($entries as $e) {
            if ($e["gid"] != $last) {
                $this->show_thread($group);
                $group = array();
            }
            $group[] = $e;
            $last = $e["gid"];
        }
        $this->show_thread($group);
    }


    /**
     * Iterate over grouped entry list
     * and recursively output posts.
     *
     */
    function show_thread($group, $pid=0) {
    
        #-- find available parent ids
        $parents = array_column($group, "pid");
    
        #-- step throuh
        foreach ($group as $entry) {
        
            #-- show if associated
            if ($entry["pid"] == $pid) {
                $entry["miniature"] or $entry["miniature"] = "/img/user.png";
                include("template/forum_entry.php");
                
                #-- Nest its children
                if (in_array($entry["id"], $parents)) {
                    print "    <ul>\n";
                    $this->show_thread($group, $entry["id"]);
                    print "    </ul>\n";
                }

                print "       </li>\n";
            }
        }
    }

    
    
    /**
     * Load a single entry.
     *
     */
    function entry($id) {
    }



    /**
     * Accept POST input and populate new forum post.
     * Adds an reply if ?pid= is not zero.
     * Doubles as edit function if ?id= is present.
     *
     */
    function submit($INSERT="INSERT") {
    
        #-- Prepare some fields
        $data = array(
            "id" => NULL,
            "pid" => $_POST->int["pid"],
            "gid" => NULL,
            "author" => $_POST->text->length…30->html["author"],
            "miniature" => $_POST->text->length…200->html["image"],
            "tag" => $_POST->text->length…20->html["tag"],
            "summary" => $_POST->text->length…120->html["summary"],
            "source" => $_POST->nocontrol->length…12000["source"],
            "html" => "",
            "excerpt" => "",
            "t_published" => time(),
            "edit_token" => $this->edit_token(),
        );

        #-- Source to HTML
        $data = $this->prepare_output($data);
        
        #-- Reject too minor submisions
        if (strlen("$data[source]$data[summary]") < 100) {
            exit("<p class=warning>Your post was a little too coarse. Please elaborate to keep discussions going.</p>");
        }

        #-- Edit
        if ($id = $_POST->int["id"]) {
            $prev = $this->edit_keep($this->edit_entry($id));
            $data = array_merge($data, $prev);
            $INSERT = "REPLACE";
#            var_dump($INSERT, $data, $prev);
        }
        #-- Reply
        elseif ($data["pid"]) {
            $data["gid"] = $this->group_id($data["pid"]);
        }

        /**
         * Store entry
         *  → Find maximum ID
         *  → Use as new id, and group id if new
         *  → Else keep previous pid and gid/id
         *
         * $data and $ids are split up before, so the :? and ::
         * placeholders don't consume all fields.
         *
         */
        $ids = array_splice($data, 0, 3);  // extract id,pid,gid
        $ok = db("
            $INSERT INTO forum (id, pid, gid, :?)
            VALUES (
                IFNULL(:id, (SELECT IFNULL(MAX(id), 0) + 1 AS id FROM forum)),
                IFNULL(:pid, 0),
                COALESCE(:gid, :id, (SELECT IFNULL(MAX(id), 0) + 1 AS id FROM forum)),
                ::
            );
        ", $data, $data, $ids);
        
        #-- return rendered
        if ($ok) {
            $data["id"] = db()->lastInsertId();
            $data["pid"] = 0;
            $this->show_thread([$data], 0);
        }
    }


    #-- Editing timeout / permission
    function edit_permission($prev) {
        return
            $this->is_admin
        or
            $_COOKIE->name["edit_token"] == $prev["edit_token"]
        and
            $prev["t_published"] + 48*3600 > time();
    }


    #-- Retrieves or sets edit_token
    function edit_token() {
        if (empty($token = $_COOKIE->name["edit_token"])) {
            setcookie("edit_token", $token = sha1(serialize($_SERVER)), time()+7*24*3600);
        }
        return $token;
    }


    /**
     * Retrieve post entry, check edit permission, and/or set defaults
     *
     *  → For forum/edit requests fetches the existing post content.
     *  → Also checks editing permissions (token),
     *    or e.g. existing OpenID logon ($this->can_edit is set from main site).
     *
     */
    function edit_entry($id) {
        if ($prev = db("SELECT * FROM forum WHERE id=?", $id)->fetch()) {

            if (!$this->can_edit) {
                exit("<p class=warning>You aren't logged in on the main site. Please associate an OpenID account.</p>");
            }
            if (!$this->edit_permission($prev)) {
                exit("<p class=warning>Entry not editable. (The edit token does not match, or the article is too old for editing.)</p>");
            }
            
            return $prev;
        }
        exit("<p class=error>Post #$id does not exist.</p>");
    }

    
    #-- Unsets a few fields for replying.
    function edit_keep($prev) {
        $copy = array_flip(str_getcsv("id,pid,gid,miniature,t_published,edit_token"));
        return array_intersect_key($prev, $copy);
    }


    #-- Copy thread/group id from parent post etc.
    function group_id($parent_id) {
        if ($prev = db("SELECT gid FROM forum WHERE id = ?", $parent_id)) {
            return $prev->gid;
        }
        return 0;
    }    


    #-- Convert Markdown/BB source into HTML, create excerpt, prepare user avatar
    function prepare_output($data) {

        #-- Content
        define("HTMLPURIFIER_PREFIX", "phar://lib/htmlpurifier.phar/standalone/");
        $md = new Parsedown();
        $data["html"] = input::purify($md->parse($data["source"]));
        $data["excerpt"] = input::html(substr(strip_tags($data["html"]), 0, 320));
        
        #-- Author
        if (strlen($data["miniature"]) and $type = $_POST->in_array("img_type", "gravatar,identicon,monsterid,wavatar,retro"))
        {
            $data["miniature"] = "http://www.gravatar.com/avatar/"
                               . md5($data["miniature"])
                               . ".jpeg?s=16" . ($type == "gravatar" ? "" : "&d=$type");
        }
        return $data;
    }




    /**
     * Output submit <form>
     *
     *  → Provides a few blank fields.
     *  → Injects cookie defaults if fresh submission / not an edit.
     *  → Escapes previous content for edit_form() calls.
     *
     */
    function submit_form($pid=0, $id=0, $data=array()) {
        global $forum_cfg;

        extract(array_merge(
            array_fill_keys(str_getcsv("author,miniature,tag,summary,source"), ""),
            $data ? array() : $_COOKIE->list->text["author,miniature"],
            array_map("htmlspecialchars", $data)
        ));

        include("template/forum_submit_form.php");
    }


    /**
     * Show editing <form> instead with prefilled previous data.
     *
     *  → Retrieves previous post content, checks permissions at that.
     *  → Outputs edit form (replied for AJAX $.load() request)
     *
     */
    function edit_form($pid=0, $id=0) {
        $data = $this->edit_entry($id);
        $this->submit_form(0, 0, $data);
    }

}




