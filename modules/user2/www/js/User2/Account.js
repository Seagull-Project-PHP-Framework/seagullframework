if (typeof User2 == 'undefined') {
    User2 = {};
}

User2.Account =
{
    init: function() {
        // show form
        $('#accountContainer .field .viewMode a.edit').click(function() {
            $(this).parents('.field').eq(0).find('.viewMode').hide();
            $(this).parents('.field').eq(0).find('.editMode').show();
            $(this).parents('.field').eq(0)
                .find('.editMode input[type="text"], .editMode textarea')
                .focus();
            return false;
        });
        // hide form
        $('#accountContainer .field .editMode a.cancel').click(function() {
            $(this).parents('.field').eq(0).find('.editMode').hide();
            $(this).parents('.field').eq(0).find('.viewMode').show();
            return false;
        });
        // submit form
        $('#accountContainer .field form.editMode').ajaxForm({
            beforeSubmit: function(elems, f) {
                var ret = false;
                // do not save empty value
                if ($.trim($(elems[1]).val())) {
                    // edit mode
                    SGL2.Util.disableSubmit('input, textarea', f);
                    $('.actions', f).hide();
                    $('.ajaxLoader', f).show();
                    ret = true;
                }
                return ret;
            },
            success: function(r, msg, f) {
                console.log(r);

                // update view value
                $(f).parents('.field').eq(0).find('.viewMode span').html(r.val);
                // hide form
                $(f).parents('.field').eq(0).find('.editMode').hide();
                // show view area
                $(f).parents('.field').eq(0).find('.viewMode').show();

                // update form for next time
                $('input[type="text"], textarea', f).val(r.val);
                $('.ajaxLoader', f).hide();
                $('.actions', f).show();
                SGL2.Util.enableSubmit('input, textarea', f);
            }
        });
        // show upload form
        $('#mediaUpload a.edit').click(function() {
            $(this).hide();
            $('#mediaUpload .editMode').show();
            return false;
        });
        // hide upload form
        $('#mediaUpload a.cancel').click(function() {
            $('#mediaUpload .editMode').hide();
            $('#mediaUpload a.edit').show();
            return false;
        });
        this.uploadMediaTrigger();
        // password/address update
        $('#changePassword-trigger').click(function() {
            $(this).hide();
            $('#changePassword-container').show();
            return false;
        });
        $('#changeAddress-trigger').click(function() {
            $(this).hide();
            $('#changeAddress-container').show();
            return false;
        });
        function hidePasswordForm() {
            $('#changePassword-container').hide();
            $('#changePassword-trigger').show();
        }
        function hideAddressForm() {
            $('#changeAddress-container').hide();
            $('#changeAddress-trigger').show();
        }
        $('#updatePassword .actions a').click(function() {
            hidePasswordForm();
            return false;
        });
        $('#changeAddress .actions a').click(function() {
            hideAddressForm();
            return false;
        });
        $('#updatePassword').ajaxForm({
            beforeSubmit: function(elems, f) {
                var ret = false;
                if ($.trim($(elems[0]).val())
                    && $.trim($(elems[1]).val())
                    && $.trim($(elems[2]).val()))
                {
                    // edit mode
                    SGL2.Util.disableSubmit('input', f);
                    $('.actions', f).hide();
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
                if (r.isUpdated) {
                    hidePasswordForm();
                    // cleanup
                    $('input[type="password"]', f).val('').eq(0).focus();
                }
                // view mode
                $('.ajaxLoader', f).hide();
                $('.actions', f).show();
                SGL2.Util.enableSubmit('input', f);
            }
        });
        $('#changeAddress').ajaxForm({
            beforeSubmit: function(elems, f) {
                var ret = false;
                if ($.trim($(elems[0]).val())
                    //&& $.trim($(elems[1]).val())
                    && $.trim($(elems[2]).val())
                    && $.trim($(elems[4]).val())
                    && $.trim($(elems[5]).val()))
                {
                    // edit mode
                    SGL2.Util.disableSubmit('input', f);
                    $('.actions', f).hide();
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
                hideAddressForm();
                // view mode
                $('.ajaxLoader', f).hide();
                $('.actions', f).show();
                SGL2.Util.enableSubmit('input', f);
            }
        });
    },

    /**
     * @todo Refactor below code together with Product.Edit and FP.Uploader.
     *       Too much copy paste technology envolved.
     */

    uploadMediaTrigger: function() {
        var _self = this;

        $('#mediaUpload input').change(function() {
            console.log('image upload started');

            $('#mediaUpload input').hide();
            $('#mediaUpload .actions').hide();
            $('#mediaUpload .mediaLoader').show();

            $.ajaxFileUpload({
                fileElementId: 'media_upload',
                url: SGL2.Util.makeUrl({
                    module: 'media2',
                    manager: 'mediauploader',
                    typeId: SGL_MEDIATYPE_PROFILE
                }),
                success: _self.showMedia,
                error: function(r, status, e) {
                    SGL2.showMessage('#message', e, SGL_MSG_ERROR, 1);
                    // view mode
                    $('#mediaUpload .mediaLoader').hide();
                    $('#mediaUpload .actions').show();
                    $('#mediaUpload input').show();
                }
            });

            $('#mediaUpload input').val('');
            $('#mediaUpload input').unbind();
            _self.uploadMediaTrigger();
        });
    },

    showMedia: function(r) {
        console.log(r);

        if (typeof r.aMsg == 'undefined' && r.isUploaded) {
            var url = makeUrl({
                module: 'user2',
                action: 'linkProfileMediaAndView',
                mediaId: r.mediaId
            });
            $.ajax({
                url: url,
                success: function(r) {
                    console.log(r);
                    // update image source
                    $('#mediaUpload .image img').attr('src', r.imgPath);

                    // hide edit form
                    $('#mediaUpload .editMode').hide();
                    $('#mediaUpload a.edit').show();

                    // prepare form for next request
                    $('#mediaUpload .mediaLoader').hide();
                    $('#mediaUpload .actions').show();
                    $('#mediaUpload input').show();
                }
            });
        }
    }
}