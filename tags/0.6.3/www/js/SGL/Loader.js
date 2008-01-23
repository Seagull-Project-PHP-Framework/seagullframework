
/**
 * NB! Untested, incompatible with scriptaculous loader.
 *
 * Seagull javascript libraries loader.
 *
 * $output->addCssFile('js/SGL/Loader.js?Cookie,Util');
 *   - load js/SGL/Cookie.js
 *   - load js/SGL/Util.js
 * $output->addCssFile('js/SGL/Loader.js?Cookie,Util.String');
 *   - load js/SGL/Cookie.js
 *   - load js/SGL/Util.js
 *   - load js/SGL/Util/String.js
 *
 * @package seagull
 * @subpackage loader
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
SGL.Loader =
{
    path: SGL_JS_WEBROOT + '/js/SGL/',

    require: function(fileName) {
        document.write('<script type="text/javascript" '
            + 'src="' + this.path + fileName + '"></script>');
    },

    load: function(library) {
        var fileName = library.replace(/\./, '/') + '.js';
        this.require(fileName);
    },

    init: function() {
        var aScripts = document.getElementsByTagName('script');
        for (var i = 0; i < aScripts.length; i++) {
            if (aScripts[i].src) {
                var matches;
                if (matches = aScripts[i].src.match(/SGL\/Loader\.js\?(.+)$/)) {
                    var aLibs = matches[1].split(',');
                    for (var i = 0, len = aLibs.length; i < len; i++) {
                        SGL.Loader.load(aLibs[i]);
                    }
                }
            }
        }
    }
}
SGL.Loader.init();