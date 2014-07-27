/**
 * api: jquery
 * title: UI behaviour
 * description: Well, just client-side interface features
 * version: 0.3
 * depends: jquery, jquery-ui
 *
 * Collects a few event callbacks to toggle and trigger all the things.
 *  → Compacted entries (.trimmed class)
 *  → Trove tags
 *      → Injection to submit_form input box
 *      → Or appending to links on page_tags cloud
 *  → Action links (green dotted underline)
 *      → Lock entry in submit_form
 *      → Injecting $version placeholder in URLs
 *      → Sidebar box for submit_imports
 *
 */


// DOM ready
$(document).ready(function(){

    // Make frontpage #main .project descriptions expandable, by undoing .trimmed; animatedly
    $(".project .trimmed").one("click", function(){  
        $(this).animate({"max-height": "20em"});
    });
    
    // Likewise for compacted news feeds in #sidebar
    $(".article-links.trimmed").one("click", function(){
        $(this).toggleClass("trimmed");
    });

    // Likewise for compacted news feeds in #sidebar
    $(".forum .entry").one("click", function(){
        $(this).find(".excerpt, .content, .funcs").toggleClass("trimmed");
    });
    
    
    // Trove tags add to input#tags field
    $("#trove_tags.add-tags .option").click(function(){
        var $tags = $("#tags");
        var prev = $tags.val();
        $tags.val(prev + (prev.length ? ", " : "") + $(this).data("tag"));
    });

    // Trove tags highlight in page_tags cloud
    $("#trove_tags.pick-tags .option").click(function(){

        // highlight in trove box
        $(this).toggleClass("selected");
        var tag = $(this).data("tag");

        // and in tag cloud
        $("#tag_cloud a:contains('"+tag+"')").toggleClass("selected");
    });
    
    // Append trove[]= selection to any clicked links in tag cloud
    $("#tag_cloud a").click(function(){

        // array from selected tags
        var tags = $("#trove_tags b.selected, #trove_tags .option.selected").map(function(){
            return $(this).data("tag");
        }).get();
        
        // append to current link
        if (tags.length) {
            this.href += "&trove[]=" + tags.join("&trove[]=");
        }
    });
    
    
    // submit_form: lock entry
    $(".action.lock-entry").click(function(){
        var $lock = $("input[name='lock']");
        if (!$lock.val().length) {
            $lock.val($lock.val("placeholder"));
        }
    });

    // submit_form: apply $version placeholder in URLs
    $(".action.version-placeholder").click(function(){
        var $input = $(this).parent().parent().find("input, textarea").eq(0);
        var version = $("input[name='version']").val();
        if (version.length) {
            $input.val($input.val().replace(version, "$version"));
        }
    });

    // submit_form: apply $version placeholder in URLs
    $(".action.submit-import").click(function(){
        $("#sidebar section").eq(0).hide();
        $("#sidebar .submit-import").fadeToggle("trimmed");
    });

});






