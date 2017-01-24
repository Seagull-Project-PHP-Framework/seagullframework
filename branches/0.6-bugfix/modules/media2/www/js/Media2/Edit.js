if (typeof Media2 == 'undefined') { Media2 = {}; }

/**
 * Media edit.
 *
 * @package media2
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
Media2.Edit =
{
    init: function() {
        $('#mediaEdit').ajaxForm({
            beforeSubmit: function(elems, f) {
                if ($.trim($(elems[1]).val())) {
                    // edit mode
                    SGL2.Util.disableSubmit('input, textarea', f);
                    $('.triggers', f).hide();
                    $('.ajaxLoader', f).show();
                } else {
                    var msg = 'fill in required data'.translate().toString();
                    SGL2.showMessage('#message', msg, SGL_MSG_ERROR, 1);
                    return false;
                }
            },
            success: function(r, msg, f) {
                console.log(r);
                if (r.isUpdated) {
                    location.href = SGL2.Util.makeUrl({
                        module: 'media2',
                        manager: 'media2'
                    });
                } else {
                    // view mode
                    $('.ajaxLoader', f).hide();
                    $('.triggers', f).show();
                    SGL2.Util.enableSubmit('input, textarea', f);
                }
            }
        });
    }
}

$(document).ready(function() {
    Media2.Edit.init();
});