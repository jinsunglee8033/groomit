var myApp;
myApp = myApp || (function () {
    return {
        showPleaseWait: function (mode) {
            if (mode === 'new') {
                $('.pager').hide();
            } else {
                $('.modal-footer').hide();
            }

            $('.progress.in-form').show();
        },
        hidePleaseWait: function (mode) {
            if (mode === 'new') {
                $('.pager').show();
            } else {
                $('.modal-footer').show();
            }
            $('.progress.in-form').hide();
        },
        showLoading: function() {
            $('#loading-modal').modal();
        },
        hideLoading: function() {
            $('#loading-modal').modal('hide');
        },
        showError: function(msg, func) {

            var open_modal = $('.modal:visible');

            if (open_modal) {
                open_modal.modal('hide');
            }

            $('#error-modal-footer').show();
            $('#error-modal-title').text('Attention !');
            $('#error-modal-title').css('color', 'red');
            $('#error-modal-body').html(msg);
            $('#error-modal').modal('show');

            $('#error-modal').one('hidden.bs.modal', function() {
                if (func) {
                    func();
                }

                if (open_modal) {
                    open_modal.modal('show');
                }
            });

        },
        showSuccess: function(msg, func) {
            $('#error-modal-footer').show();

            $('#error-modal-title').text('Success!');
            $('#error-modal-title').css('color', 'green');
            $('#error-modal-body').html(msg);
            $('#error-modal').modal('show');

            $('#error-modal').one('hidden.bs.modal', function() {
                if (func) {
                    func();
                }
            });
        },
        showConfirm: function(body, ok, cancel) {
            $('#confirm-modal-footer').show();

            var title = 'Please Confirm';
            $('#confirm-modal-title').text(title);
            $('#confirm-modal-body').html(body);
            $('#confirm-modal').modal();

            $('#confirm-modal-cancel').one('click', function() {
                $('#confirm-modal-ok').off();
                $('#confirm-modal').modal('hide');
                if (cancel) {
                    return cancel();
                }
            });
            $('#confirm-modal-ok').one('click', function() {
                $('#confirm-modal').modal('hide');
                if (ok) {
                    return ok();
                }
            });
        },
        showMsg: function(msg, modal_to_close, is_error, reload) {
            if (is_error === '1') {
                myApp.showError(msg);
            } else {
                $('#' + modal_to_close).modal('hide');
                myApp.showSuccess(msg, function() {
                    if(reload === '1') {
                        window.location.reload();
                        console.log('here')
                    }
                })
            }
        }
    };
})();

$(".modal").on('hidden.bs.modal', function () {
    $(this).data('bs.modal', null);
});

$('input').on('keyup', function(e) {
    var max = $(this).prop('maxlength');
    if (max > 0) {
        if ($(this).val().length > max) {
            $(this).val($(this).val().substr(0, max));
        }
    }
})
