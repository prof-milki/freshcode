/**
 * api: jquery
 * title: UI behaviour
 * description: Well, just client-side interface features
 * version: 0.2
 * depends: jquery-ui
 *
 * 
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
    
    
    // Trove tags select box
    $("#trove_tags .option").click(function(){
        var $tags = $("#tags");
        var tags = $tags.val();
        $tags.val(tags + (tags.length ? ", " : "") + $(this).data("tag"));
    });

});

