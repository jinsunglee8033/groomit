function saveDateTime() {
	$.ajax({
		url: '/user/appointment/update-date-time',
		data: {
			_token: $('#token').val(),
			date: $('#date').val(),
			time: $('#time').val()
		},
		cache: false,
		type: 'post',
		dataType: 'json',
		success: function (res) {
			//myApp.hideLoading();

			if ($.trim(res.msg) === '') {
				window.location.href = "/user/appointment/select-address";
			} else {
				myApp.showError(res.msg);
			}
		},
		error: function (jqXHR, textStatus, errorThrown) {
			//myApp.hideLoading();
			myApp.showError(errorThrown);
		}
	});
}