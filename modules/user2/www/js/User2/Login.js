if (typeof User2 == 'undefined') {
    User2 = {};
}

User2.Login =
{
    init: function() {
        // login user (AJAX)
        $('#loginUser').ajaxForm({
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
                if (r.isLogged) {
                    document.location.href = r.redir;
                } else {
                    // view mode
                    $('.ajaxLoader', f).hide();
                    $('input[type="submit"]', f).show();
                    SGL2.Util.enableSubmit('input', f);
                    // cleanup
                    $('input[type="text"]', f).val('').focus();
                    $('input[type="password"]', f).val('');
                }
            }
        });
        // register user (AJAX)
        $('#registerUser').ajaxForm({
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
                if (r.isRegistered) {
                    location.href = r.redir;
                } else {
                    // view mode
                    $('.ajaxLoader', f).hide();
                    $('input[type="submit"]', f).show();
                    SGL2.Util.enableSubmit('input', f);
                    // cleanup
//                    $('input[type="text"]', f).val('').focus();
                }
            }
        });
    }
}