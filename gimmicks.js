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
$(function(){

    // Make frontpage #main .project descriptions expandable, by undoing .trimmed; animatedly
    $(".project .trimmed").one("click", function(){  
        $(this).animate({"max-height": "20em"});
    });
    
    // Likewise for compacted news feeds in #sidebar
    $(".article-links.trimmed").one("click", function(){
        $(this).toggleClass("trimmed");
    });

});

