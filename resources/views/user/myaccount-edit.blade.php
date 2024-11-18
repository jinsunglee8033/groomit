@extends('user.layout.default')
<link href="/desktop/css/login.css?v=0.0.2" rel="stylesheet">
@section('content')
<link href="/desktop/css/my-account.css" rel="stylesheet">

    <main class="main sign-up" id="main">
        <div class="container">
            <h1 class="main__title text-center">Edit My Profile</h1>
            <div class="row">
                <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
                    <form name="signupForm" id="signupForm" action="">
                        {!! csrf_field() !!}
                        <fieldset>
                            <!-- Avatar, Name & Email -->
                            <div class="row row-flex align-items-top">
                                <div class="col-sm-3 col-sm-offset-1 col-flex col-xs-6 col-xs-offset-3">
                                    <div class="form-group">
                                        <label class="sr-only" aria-label="profile-avatar">Upload profile photo</label>

                                        @if (!empty($user->photo))
                                            <div class="profile-avatar profile-avatar--w-photo" style="background-image: url('data:image/png;base64,{{ $user->photo }}')">
                                                <span class="profile-avatar__update" tabindex="0" role="button" aria-pressed="false"></span>
                                            </div>
                                        @else
                                        <div class="profile-avatar profile-avatar--no-photo">
                                            <span class="profile-avatar__update" tabindex="0" role="button" aria-pressed="false"></span>
                                        </div>
                                        @endif

                                        <input type="hidden" id="photo" name="photo" value="" />
                                    </div>
                                    <input type="file" id="upload" value="Choose a file" accept="image/*" />
                                </div>
                                <div class="col-sm-7 col-sm-offset-1 col-flex">
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label for="">First Name</label>
                                                <input type="text" name="s_first_name" id="s_first_name" class="form-control" value="{{ $user->first_name }}" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label for="">Last Name</label>
                                                <input type="text" name="s_last_name" id="s_last_name" class="form-control" value="{{ $user->last_name }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label for="s_phone">Phone Number</label>
                                                <input type="text" name="s_phone" id="s_phone" class="form-control" value="{{ $user->phone }}" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label>Pets</label><br>
                                                <div class="sign-up__ci-container">
                                                    <label class="checkbox-inline" for="dog">
                                                        <input type="checkbox" id="dog" value="Y" {{ ($user->dog == 'Y') ? 'checked' : '' }}>Dog
                                                    </label>
                                                    <label class="checkbox-inline" for="cat">
                                                        <input type="checkbox" id="cat" value="Y" {{ ($user->cat == 'Y') ? 'checked' : '' }}>Cat
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Password -->
                                            <div class="col-xs-12 text-left">
                                                <button class="mt-5 groomit-btn grey-btn rounded-btn  type-submit" type="button" data-toggle="collapse" data-target="#contResetPass" aria-expanded="false" aria-controls="contResetPass" onclick="change_pw()">
                                                    Change Password
                                                </button>
                                            </div>  

                                            <div class="collapse change-password" id="contResetPass">

                                                <div class="col-xs-12 text-left">
                                                    <p class="mt-4"><strong>Reset Password</strong></p>
                                                </div> 
                                                <div class="col-xs-12">
                                                    <div class="form-group">
                                                        <label for="c_password">Current Password</label>
                                                        <input type="password" name="c_password" id="c_password" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-xs-12">
                                                    <div class="form-group">
                                                        <label for="s_password">Password</label>
                                                        <input type="password" name="s_password" id="s_password" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-xs-12">
                                                    <div class="form-group">
                                                        <label for="s_password_confirm">Password again</label>
                                                        <input type="password" name="s_password_confirm" id="s_password_confirm" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="row my-5">
                                <div class="col-xs-12 text-center">
                                    <button type="button" id="update_profile_btn" class="groomit-btn red-btn rounded-btn long-btn type-submit" onclick="update_profile()">SUBMIT</button>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
</main>
<!-- /main -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-lg-10 col-lg-offset-1">
                <p>&copy; 2020 Groomit - Made with love in NYC.</p>
            </div>
            <!-- /col -->
        </div>
        <!-- /row -->
    </div>
    <!-- /container -->
</footer>

<!-- Crop Tool -->
<div class="modal fade" id="crop-modal" tabindex="-1" role="dialog" aria-labelledby="crop-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body text-center upload-demo">
                <h3 class="main__title" id="crop-modal-label">Crop</h3>

                <div class="upload-demo-wrap mb-5">
                    <div id="upload-demo"></div>
                </div>
                <div class="pt-5 mb-5">
                    <button type="button" role="button" class="groomit-btn black-btn outline-btn long-btn rounded-btn type-submit mr-3 mb-3 mb-sm-0" id="cancel">Cancel</button>
                    <button type="button" role="button" class="groomit-btn red-btn rounded-btn long-btn type-submit" id="upload-result">Set as profile photo</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /MODAL-->

    <script>
        function initMap() {
            var input = document.getElementById('address1');
            var options = {
                componentRestrictions: {country: "us"}
            };
            var autocomplete = new google.maps.places.Autocomplete(input, options);
            autocomplete.setFields(['address_components', 'geometry', 'icon', 'name']);
            autocomplete.addListener('place_changed', function() {
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                    window.alert("No details available for input: '" + place.name + "'");
                    return;
                }

                var address1  = '';
                var city      = '';
                var state     = '';
                var zip       = '';

                var street_number = '';
                var route         = '';
                var locality      = '';
                var administrative_area_level_1 = '';
                var postal_code   = '';

                for (var i = 0; i < place.address_components.length; i++) {
                    if (place.address_components[i].types[0] == "street_number") {
                        street_number = place.address_components[i].short_name;
                    }
                    if (place.address_components[i].types[0] == "route") {
                        route = place.address_components[i].short_name;
                    }
                    if (place.address_components[i].types[0] == "locality") {
                        locality = place.address_components[i].short_name;
                    }
                    if (place.address_components[i].types[0] == "administrative_area_level_1") {
                        administrative_area_level_1 = place.address_components[i].short_name;
                    }
                    if (place.address_components[i].types[0] == "postal_code") {
                        postal_code = place.address_components[i].short_name;
                    }
                }

                var address1  = street_number + " " + route;
                var city      = locality;
                var state     = administrative_area_level_1;
                var zip       = postal_code;

                // alert(address1 + city + state + zip);

                $('#address1').val(address1);
                $('#city').val(city);
                $('#state').val(state);
                $('#s_zip').val(zip);

                $('#continue').prop('disabled', false);

            });
        }
    </script>

    <script src="https://maps.googleapis.com/maps/api/js?key=&libraries=places&callback=initMap"
            async defer>
    </script>


    <script type="text/javascript">
        var onload_func = window.onload;
        window.onload = function() {
            if (onload_func) {
                onload_func();
            }

            initPhotoCrop();
        }

        function update_profile() {

            if ($('.change-password').attr('aria-expanded') === "true"){
                if($('#c_password').val().length < 1){
                    alert("Please Insert Current Password");
                    return;
                }
                if($('#s_password').val().length < 1){
                    alert("Please Insert New Password");
                    return;
                }
                if($('#s_password_confirm').val().length < 1){
                    alert("Please Insert New Password again");
                    return;
                }
                if( $('#s_password').val() != $('#s_password_confirm').val() ){
                    alert("Not Matched Password / Password again");
                    return;
                }
            }

            var dog = ($('#dog').is(":checked")) ? 'Y' : '';
            var cat = ($('#cat').is(":checked")) ? 'Y' : '';

            var photo = $('#photo').val();

            $('#update_profile_btn').attr('disabled', true);

            $.ajax({
                url: '/user/myaccount/user_update',
                data: {
                    _token: '{!! csrf_token() !!}',
                    first_name: $('#s_first_name').val(),
                    last_name: $('#s_last_name').val(),
                    phone: $('#s_phone').val(),
                    dog: dog,
                    cat: cat,
                    photo: photo,
                    c_password: $('#c_password').val(),
                    password: $('#s_password').val(),
                    password_confirm: $('#s_password_confirm').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        window.location='/user/myaccount';
                    } else {
                        $('#update_profile_btn').attr('disabled', false);
                        alert(res.msg);
                    }
                }
            });
        }

        function change_pw() {

            if ($('.change-password').attr('aria-expanded') === "true") {

                $("#c_password").removeAttr("required");
                $("#s_password").removeAttr("required");
                $("#s_password_confirm").removeAttr("required");


            } else {

                $("#c_password").attr("required", "");
                $("#s_password").attr("required", "");
                $("#s_password_confirm").attr("required", "");

            }
        }

        //Make address2 required when selecting Apartment
        function requireAddress2(required) {

            if (!required) {

                $("#address2").removeAttr("required");

            } else {

                $("#address2").attr("required", "");

            }
        }

        //Upload photo and crop
        function initPhotoCrop() {
            var $uploadCrop;
            var removeBtn = '<span class="profile-avatar__remove" tabindex="0" role="button" aria-pressed="false"></span>';
            var updateBtn = '<span class="profile-avatar__update" tabindex="0" role="button" aria-pressed="false"></span>';

            //Read uploaded file and bind to the Croppie plugin
            function readFile(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    
                    reader.onload = function (e) {
                        $('.upload-demo').addClass('ready');
                        $('#crop-modal').modal('show');
                        $uploadCrop.croppie('bind', {
                            url: e.target.result
                        }).then(function(){
                            console.log('jQuery bind complete');
                            
                        });
                        
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
                else {
                    alert("Sorry - you're browser doesn't support the FileReader API");
                }
            }

            //Init croppie plugin
            $uploadCrop = $('#upload-demo').croppie({
                viewport: {
                    width: 200,
                    height: 200,
                    type: 'square'
                },
                enableExif: true,
                enableOrientation: true
            });

            //Upload file
            $('#upload').on('change', function () { readFile(this); });

            //Apply cropped photo to form
            $('#upload-result').on('click', function (ev) {
                $uploadCrop.croppie('result', {
                    type: 'base64',
                    size: {width: 400, height: 400}
                }).then(function (resp) {

                    $(".profile-avatar").css("background-image", "url('"+ resp +"')");
                    $(".profile-avatar").removeClass("profile-avatar--no-photo");
                    $(".profile-avatar").addClass("profile-avatar--w-photo");
                    $(".profile-avatar__update").remove();
                    $(".profile-avatar").append(removeBtn);
                    $("#photo").val(resp);
                    $('#crop-modal').modal('hide');

                });
            });

            //Cancel button on the crop modal
            $('#cancel').on('click', function () {
                $('#crop-modal').modal('hide');
                reset($('#upload'));
                $("#photo").val("");
            });

            //Click on the Upload file input through the plus button and the avatar image
            /*$(".profile-avatar").on('click', function() {
                $('#upload').trigger('click');
            });*/

            $("body").on('click', '.profile-avatar__update', function() {
                $('#upload').trigger('click');
            });

            //Clear photo and restore initial state of UI
            $('body').on('click', '.profile-avatar__remove', function() {

                $(".profile-avatar").removeClass("profile-avatar--w-photo");
                $(".profile-avatar").addClass("profile-avatar--no-photo");
                $(".profile-avatar").css("background-image", "");
                $(".profile-avatar__remove").remove();
                $(".profile-avatar").append(updateBtn);
                reset($('#upload'));
                $("#photo").val("");
            });

        }


        //Clear hidden photo input
        window.reset = function(e) {
            e.wrap('<form>').closest('form').get(0).reset();
            e.unwrap();
        };



    </script>
@stop