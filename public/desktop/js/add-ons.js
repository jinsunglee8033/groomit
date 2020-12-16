var shampoo = null;
var addons = [];

$(function() {
	selectShampoo();
	$('.shampoo .btn.btn-st-opt').eq(0).addClass('active');

	$("#not-matted").prop("checked", true);
});

function selectShampoo(id) {
	if (!id) {
		shampoo = $("input[name='shampoo']:first-child").val();
	} else {
		shampoo = $('#shampoo-' + id).val();
	}
}

function saveAddons() {
	var matted = null;
	if ($('input[name=matted]:checked').val() == 'Y') {
		matted = $('input[name=matted_value]').val();
	}

	$("input[name^='addons']:checked").each(function ()
	{
		addons.push($.trim($(this).val()));
	});

	var pet_type = $('#pet_type').val();

	$.ajax({
			url: '/user/appointment/update-addons',
			data: {
				_token: $('#token').val(),
				matted: matted,
				addons: addons,
				shampoo: shampoo
			},
			cache: false,
			type: 'post',
			dataType: 'json',
			success: function (res) {
				//myApp.hideLoading();

				if ($.trim(res.msg) === '') {
					//window.location.href = "/user/appointment/select-pet/" + pet_type;
					window.location.href = "/user/appointment/login-signup";
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