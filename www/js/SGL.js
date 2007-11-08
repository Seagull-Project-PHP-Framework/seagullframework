
/**
 * Main Seagull JavaScript library.
 *
 * @package seagull
 * @subpackage SGL
 */
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

// ----------
// --- BC ---
// ----------

/**
 * Used for async load of sourcefourge bloody button,
 */
function async_load()
{
    var node;
    try {
        // variable _asyncDom is set from JavaScript in the iframe
        // node = top._asyncDom.cloneNode(true); // kills Safari 1.2.4
        node = top._asyncDom;
        // try to remove the first script element, the one that
        // executed all document.writes().
        node.removeChild(node.getElementsByTagName('script')[0]);
    } catch (e) {}
    try {
        // insert DOM fragment at a DIV with id "async_demo" on current page
        document.getElementById('async_demo').appendChild(node);
    } catch (e) {
        try {
            // fallback for some non DOM compliant browsers
            document.getElementById('async_demo').innerHTML = node.innerHTML;
        } catch (e2) {};
    }
}

/**
 * Make Seagull SEF URL.
 *
 * @param object params
 *
 * @return string
 */
function makeUrl(params)
{
    var ret = SGL_JS_FRONT_CONTROLLER != ''
        ? SGL_JS_WEBROOT + '/' + SGL_JS_FRONT_CONTROLLER
        : SGL_JS_WEBROOT;
    var moduleName = params.module ? params.module : '';
    var managerName = params.manager ? params.manager : moduleName;

    switch (SGL_JS_URL_STRATEGY) {

    // make classic URL
    case 'SGL_UrlParser_ClassicStrategy':
        if (ret.charAt(ret.length - 1) != '?') {
            ret = ret + '?';
        }
        ret = ret + 'moduleName=' + escape(moduleName) + '&managerName=' + escape(managerName);
        for (x in params) {
            if (x == 'module' || x == 'manager') {
                continue;
            }
            // add param
            ret = '&' + ret + escape(x) + '=' + escape(params[x]);
        }
        break;

    // make default Seagull SEF URL
    default:
        ret = ret + '/' + escape(moduleName) + '/' + escape(managerName) + '/';
        for (x in params) {
            if (x == 'module' || x == 'manager') {
                continue;
            }
            ret = ret + escape(x) + '/' + escape(params[x]) + '/';
        }
        break;
    }
    return ret;
}

SGL.ready(function() {
    var msg = document.getElementById('broadcastMessage');
    if (msg) {
        msg.getElementsByTagName('a')[0].onclick = function() {
            msg.style.display = 'none';
        }
    }
});