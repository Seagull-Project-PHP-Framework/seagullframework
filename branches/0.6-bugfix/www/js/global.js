var sgl = {
    isReady: false,
    ready: function(f) {
        // If the DOM is already ready
        if (sgl.isReady) {
            // Execute the function immediately
            if (typeof f == 'string') {
                eval(f);
            } else if (typeof f == 'function') {
                f.apply(document);
            }
        // Otherwise add the function to the wait list
        } else {
            sgl.onReadyDomEvents.push(f);
        }
    },
    onReadyDomEvents: [],
    onReadyDom: function() {
        // make sure that the DOM is not already loaded
        if (!sgl.isReady) {
            // Flag the DOM as ready
            sgl.isReady = true;

            if (sgl.onReadyDomEvents) {
                for (var i = 0, j = sgl.onReadyDomEvents.length; i < j; i++) {
                    if (typeof sgl.onReadyDomEvents[i] == 'string') {
                        eval(sgl.onReadyDomEvents[i]);
                    } else if (typeof sgl.onReadyDomEvents[i] == 'function') {
                        sgl.onReadyDomEvents[i].apply(document);
                    }
                }
                // Reset the list of functions
				sgl.onReadyDomEvents = null;
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
        document.addEventListener("DOMContentLoaded", sgl.onReadyDom, false);
    }

    /* for Internet Explorer */
    /*@cc_on @*/
    /*@if (@_win32)
        document.write("<script id=__ie_onload defer src=javascript:void(0)><\/script>");
        var script = document.getElementById("__ie_onload");
        script.onreadystatechange = function() {
            if (this.readyState == "complete") {
                sgl.onReadyDom(); // call the onload handler
            }
        };
    /*@end @*/

    /* for Safari */
    if (/WebKit/i.test(navigator.userAgent)) { // sniff
        sgl.webkitTimer = setInterval(function() {
            if (/loaded|complete/.test(document.readyState)) {
                // Remove the timer
                clearInterval(sgl.webkitTimer);
                sgl.webkitTimer = null;
                // call the onload handler
                sgl.onReadyDom();
            }
        }, 10);
    }

    /* for other browsers */
    oldWindowOnload = window.onload || null;
    window.onload = function() {
        if (oldWindowOnload) {
            oldWindowOnload();
        }
        sgl.onReadyDom();
    }
}
/** -------------------------------------------------------------*/