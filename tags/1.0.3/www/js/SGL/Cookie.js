
/**
 * Library to deal with cookies.
 *
 * @package SGL
 * @subpackage cookie
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
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
            expires = '; expires=' + date.toString();
        } else {
            expires = '';
        }
        document.cookie = name + '=' + value + expires + path;
    },

    read: function(name) {
        var ret = undefined;
        var aCookiesString = document.cookie.split('; ');
        for (var i = 0, len = aCookiesString.length; i < len; i++) {
            if (aCookiesString[i].split('=')[0] == name) {
                ret = aCookiesString[i].split('=')[1];
                break;
            }
        }
        return ret;
    },

    remove: function(name, path) {
        this.create(name, '', path);
    }
}