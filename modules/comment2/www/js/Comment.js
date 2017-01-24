Comment = {
    options : {
        url:      makeUrl({module: 'comment2', action: 'addComment'}),
        dataType: 'json',
        beforeSubmit : function(){
            $('.ajax_loader').show();
        },
        success:  function(response, status){
            //  display msg to user
            Comment.showMessage(response.aMsg.message, response.aMsg.type);
            //  get last inserted comment
            var target = makeUrl({module: 'comment2', action: 'getCommentById', commentId: response.lastCommentInsertId});
            var ok = $.getJSON(target, function(data) {
                $('#comment_wrapper').prepend(data.html);
            });
            $('#my_comments').resetForm();
            $('.ajax_loader').hide();
        }
    },

    setup: function(options) {
        var options = options || {};
        options = $.extend({},this.options, options);
        $('#my_comments').ajaxForm(options);
        $("#comment_body").click( function() { $("#comment_body").html(''); } );

        Comment.loadComments();
    },

    showMessage: function(message, type) {
        if ($('.message').length > 0) {
            $('.message').hide();
            switch (type) {
            case 0:
                $('.message').html('<p class="message-error">' + message +'</p>');
                break;
            case 1:
                $('.message').html('<p class="message-info">' + message +'</p>');
                break;
            case 2:
            default:
                $('.message').html('<p class="message-warning">' + message +'</p>');
            }

            $('.message').fadeIn('slow');
        } else {
            alert(message);
        }
    },

    loadComments: function() {
        var myFk = $("#comment_fk").attr('value');
        var target = makeUrl({module: 'comment2', action: 'getComments', fk: myFk});
        var ok = $.getJSON(target, function(data) {
            $("#existing_comments").html(data.html);
        });
    }
}
