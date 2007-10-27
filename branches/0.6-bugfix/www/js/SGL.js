var SGL = {
    isReady: false,
    ready: function(f) {
        // If the DOM is already ready
        if (SGL.isReady) {
            // Execute the function immediately
            if (typeof f == 'string') {
                eval(f);
            } else if (typeof f == 'function') {
                f.apply(document);
            }
        // Otherwise add the function to the wait list
        } else {
            SGL.onReadyDomEvents.push(f);
        }
    },
    onReadyDomEvents: [],
    onReadyDom: function() {
        // make sure that the DOM is not already loaded
        if (!SGL.isReady) {
            // Flag the DOM as ready
            SGL.isReady = true;

            if (SGL.onReadyDomEvents) {
                for (var i = 0, j = SGL.onReadyDomEvents.length; i < j; i++) {
                    if (typeof SGL.onReadyDomEvents[i] == 'string') {
                        eval(SGL.onReadyDomEvents[i]);
                    } else if (typeof SGL.onReadyDomEvents[i] == 'function') {
                        SGL.onReadyDomEvents[i].apply(document);
                    }
                }
                // Reset the list of functions
				SGL.onReadyDomEvents = null;
            }
        }
    }
};

/**
 *  Cross-browser onDomReady solution
 *  Dean Edwards/Matthias Miller/John Resig
 */
new function() {
    /* for Mozilla/Opera9 */
    if (document.addEventListener) {
        document.addEventListener("DOMContentLoaded", SGL.onReadyDom, false);
    }

    /* for Internet Explorer */
    /*@cc_on @*/
    /*@if (@_win32)
        document.write("<script id=__ie_onload defer src=javascript:void(0)><\/script>");
        var script = document.getElementById("__ie_onload");
        script.onreadystatechange = function() {
            if (this.readyState == "complete") {
                SGL.onReadyDom(); // call the onload handler
            }
        };
    /*@end @*/

    /* for Safari */
    if (/WebKit/i.test(navigator.userAgent)) { // sniff
        SGL.webkitTimer = setInterval(function() {
            if (/loaded|complete/.test(document.readyState)) {
                // Remove the timer
                clearInterval(SGL.webkitTimer);
                SGL.webkitTimer = null;
                // call the onload handler
                SGL.onReadyDom();
            }
        }, 10);
    }

    /* for other browsers */
    oldWindowOnload = window.onload || null;
    window.onload = function() {
        if (oldWindowOnload) {
            oldWindowOnload();
        }
        SGL.onReadyDom();
    }
}
/** -------------------------------------------------------------*/