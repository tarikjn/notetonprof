/* 
* Automatic Expanding Text Area (v0.1)
* by Jonathan Chao
* June 18, 2009
* 
* Inspiration comes from Jason Frame's implementation located at 
*   http://github.com/jaz303/jquery-grab-bag/blob/master/javascripts/jquery.autogrow-textarea.js
*
* NOTE: This script requires jQuery to work. Developed with jQuery v1.3.2
*/

(function($) {
    $.fn.autoExpand = function(o) {
        var defaults = {
            maxHeight: 400,
            lineHeight: 16
        };
        var options = $.extend(defaults, o);
        
        return this.each(function() {
            var $textarea   = $(this),
                interval    = null,
                minHeight   = $textarea.height(),
                lineHeight  = ($.browser.msie) ? options.lineHeight : parseInt($textarea.css("line-height"));

            var div = $('<div></div>').css({
                display:    "none",
                fontSize:   $textarea.css("font-size"),
                fontFamily: $textarea.css("font-family"),
                minHeight:  $textarea.height() - lineHeight,
                lineHeight: $textarea.css("line-height"),
                width:      $textarea.width() - parseInt($textarea.css("padding-left")) - parseInt($textarea.css("padding-right"))
            }).appendTo(document.body);

            if ($.browser.msie) {
                div.css({ wordWrap: "break-word" });
            }
            else {
                div.css({ whiteSpace: "-moz-pre-wrap", whiteSpace: "pre-wrap" });
            }

            $textarea.css({ overflow: "hidden" });
            $textarea.bind('focus', function() { interval = window.setInterval(checkSize, 10); });
            $textarea.bind('blur', function() { window.clearInterval(interval); });

            var checkSize = function() {
                var repeat = function (str, num) { return new Array(num + 1).join(str); }
                div.html($textarea.val().replace(/\n/g, '<br />').replace(/ {2,}/g, function(n) { return repeat("&nbsp;", n.length); }));
                var newHeight = Math.min(Math.max(div.height() + lineHeight, minHeight), options.maxHeight);
                $textarea.css({ overflow: (newHeight == options.maxHeight) ? "auto" : "hidden" });
                $textarea.css({ height: newHeight + "px" });
            };
        });
    };
})(jQuery);
