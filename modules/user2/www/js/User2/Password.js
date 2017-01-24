if (typeof User2 == 'undefined') {
    User2 = {};
}

User2.Password =
{
    init: function() {
        // recover (AJAX)
        $('#recoverPassword').ajaxForm({
            beforeSubmit: function(elems, f) {
                var ret = false;
                if ($.trim($(elems[0]).val()) && $.trim($(elems[1]).val())) {
                    // edit mode
                    SGL2.Util.disableSubmit('input', f);
                    $('input[type="submit"]', f).hide();
                    $('.ajaxLoader', f).show();
                    ret = true;
                } else {
                    var msg = 'fill in required data'.translate().toString();
                    SGL2.showMessage('#message', msg, SGL_MSG_ERROR, 1);
                }
                return ret;
            },
            success: function(r, msg, f) {
                console.log(r);
                // view mode
                $('.ajaxLoader', f).hide();
                $('input[type="submit"]', f).show();
                SGL2.Util.enableSubmit('input', f);
                // cleanup
                $('input[type="text"]', f).val('').get(0).focus();
            }
        });
        // reset password (AJAX)
        $('#resetPassword').ajaxForm({
            beforeSubmit: function(elems, f) {
                var ret = false;
                if ($.trim($(elems[2]).val()) && $.trim($(elems[3]).val())) {
                    // edit mode
                    SGL2.Util.disableSubmit('input', f);
                    $('input[type="submit"]', f).hide();
                    $('.ajaxLoader', f).show();
                    ret = true;
                } else {
                    var msg = 'fill in required data'.translate().toString();
                    SGL2.showMessage('#message', msg, SGL_MSG_ERROR, 1);
                }
                return ret;
            },
            success: function(r, msg, f) {
                console.log(r);
                if (r.isReset) {
                    $('#resetPasswordContainer').html(r.html);
                } else {
                    // view mode
                    $('.ajaxLoader', f).hide();
                    $('input[type="submit"]', f).show();
                    SGL2.Util.enableSubmit('input', f);
                    // cleanup
                    $('input[type="password"]', f).val('').get(0).focus();
                }
            }
        });
    }
}