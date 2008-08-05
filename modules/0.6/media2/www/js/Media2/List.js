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
        $('#mediaList .item a.delete').click(function() {
            var url   = $(this).attr('href');
            var _elem = $(this).parents('.item').eq(0);;
            // process mode
            $('.triggers', _elem).hide();
            $('.ajaxLoader', _elem).show();
            $.ajax({
                url: url,
                success: function() {
                    _elem.remove();
                }
            });
            return false;
        });
        // fency zoom
        $.fn.fancyzoom.defaultsOptions.imgDir = SGL_WEBROOT + '/media2/images/fancyzoom/';
        $('#mediaList a.preview').fancyzoom();
//        {Speed: 1000}
//        {overlay: 0.8}
    }
}

$(document).ready(function() {
    Media2.List.init();
});