$('#pet-size').show();
$('#dog-service').show();
$('#cat-service').hide();

var pet = null;
var size = null;
var service = null;

$(function() {
	// select dog as a default
	selectPet('dog');
	$(".dog.cont-select-pet").addClass('selected');
});

function selectPet(pet) {

	$('#pet_type').val(pet);

	if (pet == 'cat') {
		$('#pet-size').hide();
		$('#dog-service').hide();
		$('#cat-service').show();

		$.ajax({
			url: '/user/appointment/get-package',
			data: {
				_token: $('#token').val(),
				prod_type: 'C' // Cat service
			},
			cache: false,
			type: 'post',
			dataType: 'json',
			success: function (res) {
				//myApp.hideLoading();

				if ($.trim(res.msg) === '') {

					var packages = res.packages;

					$.each(packages, function(i, o) {
						$('#cat-price').html('$' + o.denom);
						$('#prod_id').val(o.prod_id);
						$('#prod_name').val(o.prod_name);
						$('#prod_price').val(o.denom);
					});

				} else {
					myApp.showError(res.msg);
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				//myApp.hideLoading();
				myApp.showError(errorThrown);
			}
		});

	} else {
		$('#pet-size').show();
		$('#dog-service').show();
		$('#cat-service').hide();
	}
}


function selectSize(size_id) {
	$('#pet_type').val('dog');
	$('#size_id').val(size_id);
	//myApp.showLoading();

	$.ajax({
		url: '/user/appointment/get-package',
		data: {
			size_id: size_id,
			_token: $('#token').val(),
			prod_type: 'P' // only dog has size option
		},
		cache: false,
		type: 'post',
		dataType: 'json',
		success: function (res) {
			//myApp.hideLoading();

			if ($.trim(res.msg) === '') {

				var packages = res.packages;


				$.each(packages, function(i, o) {
					if (o.prod_id == '1') {
						$('#gold-price').html('$' + o.denom);
						$('#gold_prod_id').val(o.prod_id);
						$('#gold_prod_name').val(o.prod_name);
						$('#gold_prod_price').val(o.denom);
					} else {
						$('#silver-price').html('$' + o.denom);
						$('#silver_prod_id').val(o.prod_id);
						$('#silver_prod_name').val(o.prod_name);
						$('#silver_prod_price').val(o.denom);
					}

				});

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

function selectService(service) {

	if ($('#pet_type').val() == 'dog') {
		if (!$('#size_id').val()) {
			myApp.showError('Please select dog size first!');
		} else {
			$('#prod_id').val($('#' + service + '_prod_id').val());
			$('#prod_name').val($('#' + service + '_prod_name').val());
			$('#prod_price').val($('#' + service + '_prod_price').val());
		}
	}
}

function saveService() {

	var pet_type = $('#pet_type').val();
	var size_id = $('#size_id').val();
	var size_name = $('#size_name').val();
	var prod_id = $('#prod_id').val();
	var prod_name = $('#prod_name').val();
	var prod_price = $('#prod_price').val();

	if (!pet_type) {
		myApp.showError("Please select pet type!");
		return;
	}

	if (pet_type == 'dog'){

		if (!size_id) {
			myApp.showError("Please select dog size!");
			return;
		}

		if (!prod_id || !prod_price) {
			myApp.showError("Please select service!");
			return;
		}
	}

	$.ajax({
		url: '/user/appointment/update-service',
		data: {
			pet_type: pet_type,
			size_id: size_id,
			size_name: size_name,
			prod_id: prod_id,
			prod_name: prod_name,
			prod_price: prod_price,
			_token: $('#token').val()
		},
		cache: false,
		type: 'post',
		dataType: 'json',
		success: function (res) {
			//myApp.hideLoading();

			if ($.trim(res.msg) === '') {
				window.location.href = "/user/appointment/add-ons";
			} else {
				myApp.showError(res.msg);
			}
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(jqXHR);
			//myApp.hideLoading();
			myApp.showError(errorThrown + '--' + textStatus);
		}
	});
}