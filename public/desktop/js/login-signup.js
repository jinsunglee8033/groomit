$(function() {
	$('#terms').change(function(){

		if ($('input[name="terms"]:checked').length > 0) {
			$('#signupBtn').removeAttr('disabled');
		} else {
			$('#signupBtn').attr('disabled', 'disabled');
		}
	});
});
