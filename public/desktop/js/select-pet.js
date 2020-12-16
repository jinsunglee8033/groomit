
$(function() {
	$("#photo").change(function(){
		previewImage(this,'img_photo');
	});

});

function selectPet(pet_id) {
	var selected_pet = $('#selected_pet').val();

	if (selected_pet.indexOf(pet_id) > -1) {
		selected_pet = selected_pet.replace(pet_id,'');
		selected_pet = selected_pet.replace('||','|');
	} else {
		selected_pet += '|' + pet_id;
	}

	$('#selected_pet').val(selected_pet);
}

function updatePet() {
	var selected_pet = $('#selected_pet').val();
	selected_pet = selected_pet.replace('||','');
	$('#selected_pet').val(selected_pet);

	if (selected_pet = '') {
		myApp.showError('Please select service pet!');
	} else {

		$.ajax({
			url: '/user/appointment/update-pet',
			data: {
				_token: $('#token').val(),
				selected_pet: selected_pet
			},
			cache: false,
			type: 'post',
			dataType: 'json',
			success: function (res) {
				myApp.hideLoading();

				if ($.trim(res.msg) === '') {
					window.location.href = "/user/appointment/select-date-time";

				} else {
					myApp.showError(res.msg);
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				myApp.hideLoading();
				myApp.showError(errorThrown);
			}
		});	}


}

function getPetInfo(pet_id) {
	myApp.showLoading();

	$.ajax({
		url: '/user/pet/get-by-id',
		data: {
			_token: $('#token').val(),
			pet_id: pet_id
		},
		cache: false,
		type: 'post',
		dataType: 'json',
		success: function (res) {
			myApp.hideLoading();

			if ($.trim(res.msg) === '') {
				var pet = res.pet;
				$('input[name=pet_id]').val(pet.pet_id);
				$('input[name=name]').val(pet.name);
				$('select[name=age]').val(pet.age);
				$('input[name=gender]').filter('[value='+ pet.gender+']').prop('checked', true);
				$('input[name=gender]').filter('[value='+ pet.gender+']').parent().addClass('active');

				$('select[name=breed]').val(pet.breed);

				$('input[name=size]').filter('[value='+ pet.size+']').prop('checked', true);
				$('input[name=size]').filter('[value='+ pet.size+']').parent().addClass('active');

				$('input[name=temperament]').filter('[value="'+ pet.temperament+'"]').prop('checked', true);
				$('input[name=temperament]').filter('[value="'+ pet.temperament+'"]').parent().addClass('active');

				$('input[name=vaccinated]').filter('[value='+ pet.vaccinated+']').prop('checked', true);
				$('input[name=vaccinated]').filter('[value='+ pet.vaccinated+']').parent().addClass('active');

				$('input[name=vet]').val(pet.vet);
				$('input[name=vet_phone]').val(pet.vet_phone);

				$('input[name=last_groom]').filter('[value="'+ pet.last_groom+'"]').prop('checked', true);
				$('input[name=last_groom]').filter('[value="'+ pet.last_groom+'"]').parent().addClass('active');

				$('input[name=coat_type]').filter('[value="'+ pet.coat_type+'"]').prop('checked', true);
				$('input[name=coat_type]').filter('[value="'+ pet.coat_type+'"]').parent().addClass('active');

				$('input[name=special_note]').val(pet.special_note);
				// photo:
				$('input[name=photo]').val(pet.photo);
			} else {
				myApp.showError(res.msg);
			}
		},
		error: function (jqXHR, textStatus, errorThrown) {
			myApp.hideLoading();
			myApp.showError(errorThrown);
		}
	});
}

function updatePet() {
	myApp.showLoading();

	var pet_id = $('input[name=pet_id]').val();

	$.ajax({
		url: '/user/pet/update',
		data: {
			_token: $('#token').val(),
			pet_id: pet_id,
			name: $('input[name=name]').val(),
			age: $('select[name=age]').val(),
			gender: $('input[name=gender]').val(),
			breed: $('select[name=breed]').val(),
			size: $('input[name=size]').val(),
			temperament: $('input[name=temperament]').val(),
			vaccinated: $('input[name=vaccinated]').val(),
			vet: $('input[name=vet]').val(),
			vet_phone: $('input[name=vet_phone]').val(),
			last_groom: $('input[name=last_groom]').val(),
			coat_type: $('input[name=coat_type]').val(),
			special_note: $('input[name=special_note]').val(),
			photo: $('input[name=photo]').val(),
			type: $('input[name=type]').val()
		},
		cache: false,
		type: 'post',
		dataType: 'json',
		success: function (res) {
			myApp.hideLoading();

			if ($.trim(res.msg) === '') {
				window.location.href = "/user/appointment/select-pet/" + $('input[name=type]').val();
			} else {
				myApp.showError(res.msg);
			}
		},
		error: function (jqXHR, textStatus, errorThrown) {
			myApp.hideLoading();
			myApp.showError(errorThrown);
		}
	});
}