if (typeof Media2 == 'undefined') { Media2 = {}; }

/**
 * Media association.
 *
 * @package media2
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
Media2.Assoc = $.extend({}, Media2.List,
{
    init: function() {
        Media2.List.init();

        var _self = this;
        $('#mediaAssoc').ajaxForm({
            beforeSubmit: function(elems, f) {
                SGL2.Util.disableSubmit('input', f);
                $('.triggers', f).hide();
                $('.ajaxLoader', f).show();
            },
            success: function(r, msg, f) {
                console.log(r);
                if (r.isAssociated) {
                    location.href = r.redir;
                } else {
                    // view mode
                    $('.ajaxLoader', f).hide();
                    $('.triggers', f).show();
                    SGL2.Util.enableSubmit('input, textarea', f);
                }
            }

        });
    }
});