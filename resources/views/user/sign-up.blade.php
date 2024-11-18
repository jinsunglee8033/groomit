@extends('user.layout.default')
<link href="/desktop/css/login.css?v=0.0.2" rel="stylesheet">
<link href="/desktop/css/appointment.css" rel="stylesheet" type="text/css">
<link href="/desktop/css/my-account.css" rel="stylesheet" type="text/css">
@section('content')
    <main class="main sign-up" id="main">
        <div class="container">
            <h1 class="main__title text-center">Sign Up</h1>
            <div class="row">
                <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
                    <form id="signupForm" action="" name="signupForm" class="form-profile-info">
                        <fieldset>
                        <section id="profile">
                            <!-- Avatar, Name & Email -->
                            <div class="row row-flex align-items-end">
                                <div class="col-sm-3 col-sm-offset-1 col-flex col-xs-6 col-xs-offset-3">
                                    <div class="form-group">
                                        <label class="sr-only" aria-label="profile-avatar">Upload profile photo</label>
                                        <div class="profile-avatar profile-avatar--no-photo">
                                            <span class="profile-avatar__update" tabindex="0" role="button" aria-pressed="false"></span>
                                        </div>
                                        <input type="hidden" id="photo" name="photo" value="" />
                                    </div>
                                    <input type="file" id="upload" value="Choose a file" accept="image/*" />
                                </div>
                                <div class="col-sm-7 col-sm-offset-1 col-flex">
                                    <div class="row mb-4">
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label for="">First Name</label>
                                                <input type="text" name="s_first_name" id="s_first_name" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label for="">Last Name</label>
                                                <input type="text" name="s_last_name" id="s_last_name" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-xs-12">
                                            <div class="form-group">
                                                <label for="">Email</label>
                                                <input type="email" name="s_email" id="s_email" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Phone, Referral Code, Pets -->
                            <div class="row">
                                <div class="col-sm-5 mb-4">
                                    <div class="form-group">
                                        <label for="s_phone">Phone Number</label>
                                        <input type="text" name="s_phone" id="s_phone" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-sm-7 mb-4">
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label for="s_referral_code" style="color:#aeaeae;">Referral Code</label>
                                                <input type="text" name="s_referral_code" id="s_referral_code" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label>Pets</label><br>
                                                <div class="sign-up__ci-container">
                                                    <label class="checkbox-inline" for="dog">
                                                        <input type="checkbox" id="dog" value="Y">Dog
                                                    </label>
                                                    <label class="checkbox-inline" for="cat">
                                                        <input type="checkbox" id="cat" value="Y">Cat
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- How did you hear about us -->
                            <div class="row mb-4">  
                                <div class="col-xs-12">
                                    <div class="form-group">
                                        <label for="">How did you hear about us?</label>
                                        <select class="form-control" name="s_hear_from" id="s_hear_from" required>
                                            <option value="">Please Select</option>
                                            <option value="google">Google</option>
                                            <option value="facebook">Facebook</option>
                                            <option value="youtube">YouTube</option>
                                            <option value="instagram">Instagram</option>
                                            <option value="twitter">Twitter</option>
                                            <option value="yelp">Yelp</option>
                                            <option value="friends">Friends</option>
                                            <option value="veterinarian">Veterinarian</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- Password -->
                            <div class="row mb-4">
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="s_password">Password</label>
                                        <input type="password" name="s_password" id="s_password" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="s_password_confirm">Password again</label>
                                        <input type="password" name="s_password_confirm" id="s_password_confirm" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <!-- Address type -->
                            <div class="row  mb-4">
                                <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 text-center">
                                    <div class="form-group" >
                                        <label class="control-label sr-only">Address type</label>
                                        <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons" id="address_type">
                                            <label class="btn btn-st-opt flex-fill" id="home_type" >
                                                <input type="radio" name="home_type" value="apartment">
                                                Apartment
                                            </label>
                                            <label class="btn btn-st-opt active flex-fill" id="home_type" >
                                                <input type="radio" name="home_type" value="house" checked>
                                                House
                                            </label>
                                        </div>
                                    </div>
								</div>
                            </div>
                            <!-- Address -->
                            <div class="row">
                                <div class="col-sm-8 mb-4">
                                    <div class="form-group">
                                        <label class="control-label" for="address1">Address 1</label>
                                        <input name="address1" type="text" class="form-control"
                                               value="{{ empty($address1) ? '' : $address1 }}"
                                               placeholder="Enter your address" id="address1">

                                    </div>
                                </div>
                                <div class="col-sm-4 mb-4">
                                    <div class="form-group">
                                        <label class="control-label" for="address2">Address 2</label>
                                        <input class="form-control" name="address2" id="address2" type="text" maxlength="20" placeholder="Apt, Suite, Unit, Floor, etc." />
                                    </div>
                                </div>
                            </div>
                            <!-- City, State, Zip -->
                            <div class="row">
                                <div class="col-sm-5 mb-4">
                                    <div class="form-group">
                                        <label class="control-label" for="city">City</label>
                                        <input class="form-control" name="city" id="city" type="text"
                                                maxlenght="50" value="{{ empty($city) ? '' : $city }}" required/>
                                    </div>
                                </div>
                                <div class="col-sm-7">
                                    <div class="row mb-4">
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label class="control-label" for="">State</label>
                                                <input class="form-control" name="state" id="state"
                                                       value="{{ empty($state) ? '' : $state }}" type="text" disabled required/>
                                            </div>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label for="">Zip</label>
                                                <input type="text" name="s_zip" id="s_zip" class="form-control"
                                                       value="{{ empty($zip) ? '' : $zip }}" disabled required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Agreement -->
                            <div class="row" id="agreements">
                                <div class="col-xs-12">
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label for="s_terms" class="pl-0">
                                                <input type="checkbox" name="s_terms" id="s_terms" value="">
                                                <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>I accept
                                            </label>
                                            <a class="red-link" href="/terms-privacy" target="_blank" title="Go to Terms & Conditions"><strong> terms & conditions.</strong></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Submit -->
                            <div class="row my-5">
                                <div class="col-xs-12 text-center">
                                    <button type="button" id="sign_up_btn" class="groomit-btn red-btn rounded-btn long-btn type-submit" onclick="register()">SIGN UP</button>
                                </div>
                            </div>
                        </fieldset>
                    </section>
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

        function register() {
            var terms = $('#s_terms').is(':checked');
            if (!terms) {
                alert('Please accept our terms and condition!');
                return;
            }

            var home_type = $("input[name='home_type']:checked").val();{
                if (home_type === 'apartment' && ($('#address2').val().length === 0 ) ){
                    alert('Please Input Address 2');
                    return;
                }
            }

            var dog = ($('#dog').is(":checked")) ? 'Y' : '';
            var cat = ($('#cat').is(":checked")) ? 'Y' : '';

            var photo = $('#photo').val();

            $('#sign_up_btn').attr('disabled', true);

            $.ajax({
                url: '/user/register',
                data: {
                    _token: '{!! csrf_token() !!}',
                    first_name: $('#s_first_name').val(),
                    last_name: $('#s_last_name').val(),
                    phone: $('#s_phone').val(),
                    email: $('#s_email').val(),
                    dog: dog,
                    cat: cat,
                    photo: photo,
                    hear_from: $('#s_hear_from').val(),
                    referral_code: $('#s_referral_code').val(),
                    address1: $('#address1').val(),
                    address2: $('#address2').val(),
                    city: $('#city').val(),
                    state: $('#state').val(),
                    zip: $('#s_zip').val(),
                    password: $('#s_password').val(),
                    password_confirm: $('#s_password_confirm').val(),

                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        @if( Session::get('schedule.prev_url') === 'select-pet' )
                            window.location='{{ Session::get('schedule.full_prev_url') }}';
                        @else
                            window.location='/user';
                        @endif
                    } else {
                        $('#sign_up_btn').attr('disabled', false);
                        alert(res.msg);
                    }
                }
            });
        }

    </script>
@stop