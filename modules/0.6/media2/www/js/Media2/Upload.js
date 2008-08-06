if (typeof Media2 == 'undefined') { Media2 = {}; }

/**
 * Media upload.
 *
 * @package media2
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
Media2.Upload =
{
    disableSubmit: function(f) {
        SGL2.Util.disableSubmit('input[type!="file"]', f);
        $('.triggers', f).hide();
        $('.ajaxLoader', f).show();
    },

    enableSubmit: function(f) {
        $('.ajaxLoader', f).hide();
        $('.triggers', f).show();
        SGL2.Util.enableSubmit('input[type!="file"]', f);
    },

    uploadCallback: function(r) {
        console.log(r);

        var _self = Media2.Upload;

        if (typeof r.aMsg == 'undefined') {
            if (!r.isUploaded) {
                _self.enableSubmit(_self._f);
            } else {
                $.ajax({
                    url: makeUrl({module: 'media2', action: 'getMediaEditScreen', mediaId: r.mediaId}),
                    success: function(r) {
                        $('#mediaUpload').remove();
                        $('#mediaContainer').html(r.html);
                        Media2.Edit.init();
                    }
                });
            }
        } else {
            // view mode
            _self.enableSubmit(this._f);
        }
    },

    init: function() {
        var _self = this;

        $('#mediaUpload input[type="button"]').click(function() {
            function showError(msg) {
                SGL2.showMessage('#message', msg, SGL_MSG_ERROR, 1);
            }
            if (!$('#media_upload').val()) {
                showError('fill in required data'.translate().toString());
                return false;
            } else {
                _self._f = $(this).parents('form').eq(0);

                // edit mode
                _self.disableSubmit(_self._f);
                // upload file
                $.ajaxFileUpload({
                    fileElementId: 'media_upload',
                    url: SGL2.Util.makeUrl({module: 'media2', manager: 'mediauploader'}),
                    secureuri: false,
                    dataType: 'json',
                    success: _self.uploadCallback,
                    error: function(r, status, e) {
                        showError(e);
                        // view mode
                        _self.enableSubmit(_self._f);
                    }
                });
            }
            return false; // prevent submit
        });
    }
}