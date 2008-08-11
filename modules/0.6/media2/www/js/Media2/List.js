if (typeof Media2 == 'undefined') { Media2 = {}; }

/**
 * Media list.
 *
 * @package media2
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
Media2.List =
{
    init: function() {
        var _self = this;

        $('#mediaList .item a.delete').click(function() {
            _self.initTrigger(this);
            return false;

        });
        // fency zoom
        $.fn.fancyzoom.defaultsOptions.imgDir = SGL_WEBROOT + '/media2/images/fancyzoom/';
        $('#mediaList a.preview').fancyzoom();
//        {Speed: 1000}
//        {overlay: 0.8}
    },

    initTrigger: function(elem) {
        var url   = $(elem).attr('href');
        var _elem = $(elem).parents('.item').eq(0);

        // process mode
        $('.triggers', _elem).hide();
        $('.ajaxLoader', _elem).show();
        $.ajax({
            url: url,
            success: function(r) {
                _elem.remove();
            }
        });
        return false;
    }
}