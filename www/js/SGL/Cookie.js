
/**
 * Library to deal with cookies.
 *
 * @package    seagull
 * @subpackage cookie
 * @author     Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
SGL.Cookie =
{
    /**
     * @param string name     cookie name
     * @param string value    cookie value
     * @param string path     cookie path
     * @param string expires  expire time in minutes
     */
    create: function(name, value, path, expires) {
        value = (typeof value != 'undefined') ? value : '';
        path  = (typeof path != 'undefined') ? path : '/';
        path  = '; path=' + path;
        if (typeof expires != 'undefined') {
            var date = new Date();
            date.setTime(date.getTime() + 60*1000*expires);
            expires = '; expires=' + date.toGMTString();
        } else {
            expires = '';
        }
        document.cookie = name + '=' + value + expires + path;
    },

    read: function(name) {
        var aCookiesString = document.cookie.split('; ');
        for (var i = 0, len = aCookiesString.length; i < len; i++) {
            var cookieString = aCookiesString[i];
            var aCookie = cookieString.split('=');
            if (aCookie[0] == name) {
                return aCookie[1];
            }
        }
        return null;
    },

    remove: function(name, path) {
        this.create(name, '', path);
    }
}