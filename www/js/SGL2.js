
/**
 * SGL2.
 *
 * @package SGL2
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */

SGL2       = {};
SGL2.Key   = {};
SGL2.Util  = {};
SGL2.State = {};

/**
 * Keys.
 */
SGL2.Key.ENTER     = 13;
SGL2.Key.BACKSPACE = 8;

/**
 * States.
 */
SGL2.State.msgIsVisible = false;

/**
 * Utils.
 */
SGL2.Util.disableSubmit = function(className, context) {
    if (typeof className == undefined) {
        className = '.save';
    }
    jQuery.browser.msie
        ? jQuery(className, context).attr('disabled', 'disabled')
        : jQuery(className, context).attr('disabled', 'disabled').css('opacity', .5);
}
SGL2.Util.enableSubmit = function(className, context) {
    if (typeof className == undefined) {
        className = '.save';
    }
    jQuery.browser.msie
        ? jQuery(className, context).attr('disabled', null)
        : jQuery(className, context).attr('disabled', null).css('opacity', 1);
}
SGL2.Util.makeUrl = function(params) {
    var ret         = SGL_FC != '' ? SGL_WEBROOT + '/' + SGL_FC : SGL_WEBROOT;
    var moduleName  = params.module ? params.module : '';
    var managerName = params.manager ? params.manager : moduleName;

    ret = ret + '/' + escape(moduleName) + '/' + escape(managerName) + '/';
    for (var v in params) {
        if (v == 'module' || v == 'manager') {
            continue;
        }
        ret = ret + escape(v) + '/' + escape(params[v]) + '/';
    }
    return ret;
}

/**
 * Show message.
 */
SGL2.showMessage = function(elem, message, type, speed) {
    var typeClass;
    speed = speed || 3;
    switch (type.toString()) {
        case SGL_MSG_ERROR:
            typeClass = 'error';
            break;
        case SGL_MSG_INFO:
        default:
            typeClass = 'success';
            break;
    }
    if (typeof SGL2.State.msgIsVisible == 'undefined') {
        SGL2.State.msgIsVisible = false;
    }
    if (SGL2.State.msgIsVisible == false) {
        jQuery('p', elem).addClass(typeClass).text(message)
            .parent('div').show()
            .children('p').effect('highlight', {}, speed * 500);
        setTimeout(function() {
            jQuery(elem).fadeOut(speed * 500);
            setTimeout(function() {
                jQuery('p', elem).removeClass(typeClass);
                SGL2.State.msgIsVisible = false;
            }, speed * 500);
        }, speed * 1000);
    }
    SGL2.State.msgIsVisible = true;
//        $('p', elem).addClass(typeClass).text(message).parent('div')
//            .show('slide', {direction: 'right'}, 1500);
//        setTimeout(function() {
//            $(elem).hide('slide', {direction: 'right'}, 1500);
//            setTimeout(function() { $('p', elem).removeClass(typeClass); }, 1500);
//        }, 3000);
}

// BC
if (typeof makeUrl == 'undefined') {
    function makeUrl() {
        return SGL2.Util.makeUrl.apply(SGL2.Util, arguments);
    }
}

// Firebug's console for other browsers.
if (typeof console == 'undefined') {
    console = { log: function() {} };
}
jQuery.extend(String.prototype, {
    translate: function() {
        var ret = this;
        if (SGL2.Localisation && SGL2.Localisation[this]) {
            ret = SGL2.Localisation[ret];
        }
        return ret;
    }
});	

jQuery(document).ready(function() {

    // global error handling
    jQuery('#message').ajaxError(function(msg, r, opts) {
        if (opts.dataType == 'json') {
            var msg = eval('(' + r.responseText + ')'), ret = '';
            ret += msg.errorType + ': ';
            ret += msg.message;
            if (ret.debugInfo) {
                ret += '(DEBUG: ' + ret.debugInfo + ')';
            }
            SGL2.showMessage(this, ret, SGL_MSG_ERROR);
        }

    // global message handling
    }).ajaxSuccess(function(msg, r, opts) {
        if (opts.dataType == 'json') {
            var response = eval('(' + r.responseText + ')'), ret = '';
            if (typeof response.aMsg != 'undefined' && !response.aMsg.persist) {
                SGL2.showMessage(this, response.aMsg.message, response.aMsg.type);
            }
        }
    });

    jQuery("#cLang").change(function() {
        var selectedLang = jQuery(this).val();
        if (!selectedLang) {
            return false;
        } else {
            var url = SGL_JS_CURRURL;
            // remove version ref
            url = url.replace(/version\/\d+\/?/, '');
            // substitute language ref
            if (url.match(/cLang\/[a-z]{2}(\-[a-z0-9\-]+)?\/*/)) {
                url = url.replace(/cLang\/[a-z]{2}(\-[a-z0-9\-]+)?\/*/i, 'cLang/' +selectedLang +'/');
            } else {
                url = url.replace(/\/$/, '');
                url = url + '/cLang/' +selectedLang +'/';
            }
            var webRoot = SGL_JS_WEBROOT.match(/(http[s]?\:\/\/[\w\.]+([\:0-9]+)?)/i)[0];
            var target = webRoot + url;
            window.location.href = target;
        }
    });
});