if (typeof SGL.Util == 'undefined') {
    SGL.Util = {};
}

/**
 * String utilities.
 *
 * @package SGL
 * @subpackage Util
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
SGL.Util.String =
{
    uneval: function(obj, depth) {
        if (typeof depth == 'undefined') {
            depth = 0;
        }
        var ret = '{';
        for (var i in obj) {
            ret += '"' + i + '"' + ":";
            if (typeof obj[i] == 'object') {
                ret += SGL.Util.String.uneval(obj[i], depth + 1);
            } else {
                ret += obj[i];
            }
            ret += ',';
        }
        if (ret == '{' && ret.length == 1) {
            ret = '';
        }
        if (ret) {
            ret = ret.substr(0, ret.length-1) + '}';
        }
        return (!depth && ret) ? '(' + ret + ')' : ret;
    }
}