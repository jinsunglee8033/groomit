@extends('includes.default')
@section('content')
    <link href="https://fonts.googleapis.com/css?family=Pacifico&display=swap" rel="stylesheet">
    <link href="css/scroLi.css?v=1.1.2" rel="stylesheet">
    <link href="css/application.css?v=1.1.4" rel="stylesheet">


    <script type="text/javascript">
        var onload_events = window.onload;
        var validator = null;
        window.onload = function() {
            if (onload_events) {
                onload_events();
            }

            //Init navigation (only on desktop)
            var windowWidth = $(window).innerWidth();

            if (windowWidth > 768) {
                initScrollNavigation();
            }

            //Change images CSS according to aspect (groomer profile pic / grooming photos)
            const images = document.querySelectorAll('img.c-photo__uploaded-photo');
            Array.from(images).forEach(image => {
                image.addEventListener('load', () => fitImage(image));

                if (image.complete && image.naturalWidth !== 0)
                    fitImage(image);
            });


            validator = $('#frm_app').validate({
                focusInvalid: true,
                focusCleanup: true,
                ignore: [],
                rules: {
                    phone: {
                        regex: /^\d{10}$/
                    },
                    password_confirm: {
                        equalTo: "#password"
                    },
                    zip: {
                        regex: /^\d{5}$/
                    },
                    agree_to_bg_check: {
                        same: 'Y'
                    }
                },
                messages: {
                    phone: {
                        regex: 'Please enter valid 10 digit phone number'
                    },
                    zip: {
                        regex: 'Please enter valid 5 digit zipcode'
                    },
                    profile_photo: {
                        required: 'Please upload image'
                    },
                    agree_to_bg_check: {
                        same: 'Please agree to background check'
                    }
                },
                tooltip_options: {
                    //_all_: {container: 'body'},
                    _all_: {
                        trigger: 'focus'
                    }
                }
            });

            $("#groomed_photo").change(function() {
                //readURL(this);

                previewImage(this, 'img_groomed_photo');
                $('#img_groomed_photo').removeClass("sample");
                $('#img_groomed_photo').parent().parent().removeClass("hidden");
            });

            $('#profile_photo').change(function() {
                previewImage(this, 'img_profile_photo');
                $('#img_profile_photo').removeClass("sample");
            });

            @if(session()-> has('success') && session('success') == 'Y')
            $('#success').modal();
            @endif

            @if(count($errors) > 0)
            $('#error').modal();
            @endif
        }

        function submit_form() {

            if (
                !$('#full_name').valid() ||
                !$('#email').valid() ||
                !$('#phone').valid() ||
                !$('#groomer_how_knew_groomit').valid() ||
                !$('#profile_photo').valid() ||

                !$('#street').valid() ||
                !$('#city').valid() ||
                !$('#state').valid() ||
                !$('#zip').valid() ||
                !$('[name=relocation]').valid()
            ) {

                location.href = "#apply-now";
                validator.focusInvalid();

                return;
            }
            console.log('### step 1 passwd ###');

            if (
                !$('#groomer_exp_note').valid() ||
                !$('[name=groomer_target]').valid() ||
                !$('[name=comfortable]').valid() ||
                !$('[name=agree_to_bg_check]').valid() ||
                !$('[name=groomer_exp_years]').valid() ||

                !$('[name=have_tool]').valid()
            ) {

                location.href = "#apply-now";
                validator.focusInvalid();

                return;
            }

            console.log('### step 2 passwd ###');

            var at_least_one_checked = false;

            for (var i = 0; i < 6; i++) {
                for (var j = 8; j <= 24; j++) {
                    var pad = "00"
                    var hour = pad.substring(0, pad.length - ('' + j).length) + j
                    var key = '#wd' + i + '_h' + hour;
                    var checked = $(key).is(':checked');
                    if (checked) {
                        at_least_one_checked = true;
                        break;
                    }
                }

                if (at_least_one_checked) {
                    break;
                }
            }

            console.log('### step 3 passwd ###');

            if (!at_least_one_checked) {
                alert("Check at least one Availability");
                validator.focusInvalid();
                return;
            }

            $('#frm_app').submit();
        }

        function initScrollNavigation() {

            if ($("#frm_app").length) {

                $('#frm_app').WS_ScroLi({
                    validEnd : {
                        status  : true,
                        icon    : 'fas fa-check'
                    },
                    sections : [
                        [ '#step-1', 'fas fa-info' ],
                        [ '#step-2', 'far fa-user' ],
                        [ '#step-3', 'fas fa-cut' ],
                        [ '#step-4', 'far fa-calendar' ]
                    ],
                    position : {
                        x : ['left', 10],
                        y : ['top', 116]
                    },
                    initialPosition : {
                        x : ['left', 10],
                        y : ['top', 50]
                    },
                    icon : {
                        size         : 30,
                        borderWidth  : 1,
                        borderRadius : 100,
                        color        : '#b61b22',
                        colorPast    : '#000',
                        colorOff     : '#b7b7b7'
                    },
                    line : {
                        height      : 30,
                        width       : 3,
                        color       : '#b61b22',
                        colorPast   : '#000',
                        colorOff    : '#b7b7b7'
                    }
                });
            }
        }

        /*function readURL(input) {

            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#img_groomed_photo').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }*/

        function fitImage(image) {
            const aspectRatio = image.naturalWidth / image.naturalHeight;

            // If image is portrait
            if (aspectRatio < 1) {
                image.style.width = '100%';
                image.style.height = 'auto';
                image.style.maxHeight = 'none';
                image.style.maxWidth = '100%';
            }

            // If image is landscape
            else if (aspectRatio > 1) {
                image.style.width = 'auto';
                image.style.height = '100%';
                image.style.maxHeight = '100%';
                image.style.maxWidth = 'none';
            }

            // Otherwise, image is square
            else {
                image.style.width = '100%';
                image.style.height = 'auto';
                image.style.maxHeight = 'none';
                image.style.maxWidth = '100%';
            }
        }

        function all_hours(x){
            if($('#wd'+x).prop('checked') == true){
                $('#wd'+x+'_h08').prop('checked', true);
                $('#wd'+x+'_h09').prop('checked', true);
                $('#wd'+x+'_h10').prop('checked', true);
                $('#wd'+x+'_h11').prop('checked', true);
                $('#wd'+x+'_h12').prop('checked', true);
                $('#wd'+x+'_h13').prop('checked', true);
                $('#wd'+x+'_h14').prop('checked', true);
                $('#wd'+x+'_h15').prop('checked', true);
                $('#wd'+x+'_h16').prop('checked', true);
                $('#wd'+x+'_h17').prop('checked', true);
                $('#wd'+x+'_h18').prop('checked', true);
                $('#wd'+x+'_h19').prop('checked', true);
                $('#wd'+x+'_h20').prop('checked', true);
                $('#wd'+x+'_h21').prop('checked', true);
                $('#wd'+x+'_h22').prop('checked', true);
                $('#wd'+x+'_h23').prop('checked', true);
                $('#wd'+x+'_h24').prop('checked', true);
            }else{
                $('#wd'+x+'_h08').prop('checked', false);
                $('#wd'+x+'_h09').prop('checked', false);
                $('#wd'+x+'_h10').prop('checked', false);
                $('#wd'+x+'_h11').prop('checked', false);
                $('#wd'+x+'_h12').prop('checked', false);
                $('#wd'+x+'_h13').prop('checked', false);
                $('#wd'+x+'_h14').prop('checked', false);
                $('#wd'+x+'_h15').prop('checked', false);
                $('#wd'+x+'_h16').prop('checked', false);
                $('#wd'+x+'_h17').prop('checked', false);
                $('#wd'+x+'_h18').prop('checked', false);
                $('#wd'+x+'_h19').prop('checked', false);
                $('#wd'+x+'_h20').prop('checked', false);
                $('#wd'+x+'_h21').prop('checked', false);
                $('#wd'+x+'_h22').prop('checked', false);
                $('#wd'+x+'_h23').prop('checked', false);
                $('#wd'+x+'_h24').prop('checked', false);
            }
        }
    </script>

    @if (session()->has('success') && session('success') == 'Y')
        <div id="success" class="modal fade " tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false"
             style="display:block;">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <p class="modal-title text-center"><strong>Thank you for your application</strong></p>
                    </div>
                    <div class="modal-body">
                        <p class="text-center">
                            The Groomit Team will be in touch soon. Stay tuned.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <div class="row">
                            <div class="col-sm-4 col-sm-offset-4 col-xs-8 col-xs-offset-2">
                                <button type="button" class="btn btn-red btn-block" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                    <!-- /modal-footer -->
                </div>
            </div>
        </div>
    @endif

    @if (count($errors) > 0)
        <div id="error" class="modal fade " tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false"
             style="display:block;">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <p class="modal-title text-center"><strong>Error</strong></p>
                    </div>
                    <div class="modal-body">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <div class="row">
                            <div class="col-sm-4 col-sm-offset-4 col-xs-8 col-xs-offset-2">
                                <button type="button" class="btn btn-red btn-block" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                    <!-- /modal-footer -->
                </div>
            </div>
        </div>
    @endif

    <div id="application-main">

        <section id="application-banner" class="banner">
            <h1 class="banner__title text-center">Do what you love & join our growing <span class="red">Groomit</span> team</h1>
        </section>
        <!-- /banner -->


        <section id="why-groomit" class="section section--grey-dadada">
            <div class="container">
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1 col-md-5 col-md-offset-0 col-lg-5 col-lg-offset-0 text-center">
                        <iframe width="100%" height="auto" src="https://www.youtube.com/embed/qlshwqaRISQ?rel=0&amp;showinfo=0" frameborder="0"
                                allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    </div>
                    <div class="col-sm-10 col-sm-offset-1 col-md-7 col-md-offset-0 col-lg-6 col-lg-offset-1">
                        <h2 class="section__title section__title--w-subtitle">Why style with <span class="red font-weight-bolder">GROOMIT</span></h2>
                        <p><strong>First In Home Pet Grooming Platform</strong></p>
                        <br>
                        <ul>
                            <li>
                                <p><i class="fas fa-check red"></i>Work on your own schedule, be your own boss.</p>
                            </li>
                            <li>
                                <p><i class="fas fa-check red"></i>Service only your preferred neighborhoods & days.</p>
                            </li>
                            <li>
                                <p><i class="fas fa-check red"></i>No hassle, we manage all appointments & customer support.</p>
                            </li>
                            <li>
                                <p><i class="fas fa-check red"></i>See earnings, your schedule, update availabity within our Groomer App.</p>
                            </li>
                            <li>
                                <p><i class="fas fa-check red"></i>Insurance covers you, pets & property with every appointment.</p>
                            </li>
                            <li>
                                <p><i class="fas fa-check red"></i>Get paid weekly & keep all your tips.</p>
                            </li>
                            <li>
                                <p><i class="fas fa-check red"></i>We provide organic shampoos, conditioners, toothpaste.</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
        <!-- /benefits -->

        <section id="application-form">
            <form id="frm_app" name="frm_app" method="post" action="/application" enctype="multipart/form-data">
                <input type="hidden" id="pre_save" name="pre_save" value="{{ empty($pre_save) ? '' : $pre_save }}">
                {!! csrf_field() !!}
                <div class="section section--white" id="step-1">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0">
                                <h2 class="text-center">Get Started</h2>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="control-label" for="full_name">First Name</label>
                                            <input id="full_name" name="full_name" type="text" value="{{ empty($full_name) ? '' : $full_name }}" required="required" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="last_name" class="control-label">Last Name</label>
                                            <input id="last_name" name="last_name" type="text" value="{{ empty($last_name) ? '' : $last_name }}" class="form-control" required="required">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="email" class="control-label">Email</label>
                                            <input id="email" name="email" type="text" class="form-control" value="{{ empty($email) ? '' : $email }}" required="required">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="phone" class="control-label">Mobile Number</label>
                                            <input id="phone" name="phone" type="text" class="form-control" value="{{ empty($phone) ? '' : $phone }}" required="required">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="groomer_how_knew_groomit" class="control-label">How did you hear about us?</label>
                                            <select id="groomer_how_knew_groomit" name="groomer_how_knew_groomit" class="select form-control" required>
                                                <option value="">Select</option>
                                                <option value="google">Google</option>
                                                <option value="facebook">Facebook</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group text-center">
                                                    <div class="c-photo">
                                                        <div class="c-photo__mask c-photo__mask--profile">
                                                            <img class="sample c-photo__uploaded-photo" id="img_profile_photo" src="images/groomer-sample.jpg?v=1.0.1" alt="Profile photo" />
                                                        </div>
                                                        <p>Sample</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="profile_photo" class="control-label">Profile photo <span class="font-weight-normal">(required)</span></label>
                                                    <br>
                                                    <input type="file" id="profile_photo" name="profile_photo" value="{{ old('profile_photo') }}" required aria-required="true" style="visibility:hidden"/>
                                                    <a onclick="$('[name=profile_photo]').click()" class="btn btn-red">Upload Images</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="f_account" class="control-label">Facebook Account <span class="font-weight-normal">(optional)</span></label>
                                            <input id="f_account" name="f_account" type="text" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="i_account" class="control-label">Instagram Account <span class="font-weight-normal">(optional)</span></label>
                                            <input id="i_account" name="i_account" type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="street" class="control-label">Street</label>
                                            <input id="street" name="street" type="text" class="form-control" required="required">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="city" class="control-label">City</label>
                                            <input id="city" name="city" type="text" class="form-control" required="required">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="state" class="control-label">State</label>
                                            <input id="state" name="state" type="text" class="form-control" required="required">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="zip" class="control-label">ZIP</label>
                                            <input id="zip" name="zip" type="text" class="form-control" required="required">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <label class="control-label">1. What area do you service?</label>
                                            <div class="row">
                                                <div class="col-xs-6 col-sm-4 col-md-2">
                                                    <label class="checkbox-inline" for="service_ny">
                                                        <input type="checkbox" name="service_ny" value="Y">
                                                        New York
                                                    </label>
                                                </div>
                                                <div class="col-xs-6 col-sm-4 col-md-2">
                                                    <label class="checkbox-inline" for="service_nj">
                                                        <input type="checkbox" name="service_nj" value="Y">
                                                        New Jersey
                                                    </label>
                                                </div>
                                                <div class="col-xs-6 col-sm-4 col-md-2">
                                                    <label class="checkbox-inline" for="service_ct">
                                                        <input type="checkbox" name="service_ct" value="Y">
                                                        Connecticut
                                                    </label>
                                                </div>
                                                <div class="col-xs-6 col-sm-4 col-md-2">
                                                    <label class="checkbox-inline" for="service_miami">
                                                        <input type="checkbox" name="service_miami" value="Y">
                                                        Miami
                                                    </label>
                                                </div>
                                                <div class="col-xs-6 col-sm-4 col-md-2">
                                                    <label class="checkbox-inline" for="service_philladelphia">
                                                        <input type="checkbox" name="service_philladelphia" value="Y">
                                                        Philadelphia
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <div class="display-table">
                                                <div class="table-cell">
                                                    <label for="service_other_area" class="control-label">Other</label>
                                                </div>
                                                <div class="table-cell">
                                                    <input id="service_other_area" name="service_other_area" placeholder="Tell us" type="text" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <div class="display-table">
                                                <div class="table-cell">
                                                    <label for="relocation" class="control-label">2. Are you willing to relocate?</label>
                                                </div>
                                                <div class="table-cell text-right">
                                                    <span><input type="radio" name="relocation" value="Y" required/>&nbsp Yes</span>&nbsp &nbsp
                                                    <span><input type="radio" name="relocation" value="N"/>&nbsp No</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /col -->
                        </div>
                        <!-- /row -->
                    </div>
                    <!-- /container -->
                </div>
                <!-- /step-1 -->
                <div class="section section--grey-f4f4f4" id="step-2">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0">
                                <h2 class="text-center">Tell us about yourself</h2>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group inline-label">
                                            <label class="control-label" for="bather_exp">3. Have you worked as </label>
                                            <label class="checkbox-inline">
                                                <input type="checkbox" name="bather_exp" value="Y">
                                                <em>Bather</em>
                                            </label>
                                            <label> or </label>
                                            <label class="checkbox-inline">
                                                <input type="checkbox" name="groomer_exp" value="Y">
                                                <em>Groomer</em>
                                            </label>
                                            <label class="control-label"> before?</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <label for="groomer_exp_note" class="control-label">4. Where did you learn your skills?</label>
                                            <input id="groomer_exp_note" name="groomer_exp_note" type="text" class="form-control" placeholder="Tell us more...">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group inline-label">
                                            <label for="groomer_target" class="control-label">5. Do you service </label>

                                            <span>&nbsp &nbsp<input type="radio" name="groomer_target" value="D" required/>&nbsp Dogs
                                            <img src="images/dog-icon_xs.png" alt="Dogs"/>
                                        </span>&nbsp &nbsp
                                            <span>&nbsp &nbsp<input type="radio" name="groomer_target" value="C"/>&nbsp Cats
                                            <img src="images/cat-icon_xs.png" alt="Cats"/>
                                        </span>
                                            <span>&nbsp &nbsp<input type="radio" name="groomer_target" value="B"/>&nbsp Both</span>

                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <div class="display-table">
                                                <div class="table-cell">
                                                    <label for="radio" class="control-label">6. Groomit is an in-home, on demand grooming service. <br>Are you comfortable grooming in our clients home/office?</label>
                                                </div>
                                                <div class="table-cell text-right">
                                                    <span><input type="radio" name="comfortable" value="Y" required/>&nbsp Yes</span>&nbsp &nbsp
                                                    <span><input type="radio" name="comfortable" value="N"/>&nbsp No</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <div class="display-table">
                                                <div class="table-cell">
                                                    <label for="" class="control-label">7. Do you have a drivers license <span class="font-weight-normal">(optional)?</span></label>
                                                </div>
                                                <div class="table-cell text-right">
                                                    <span><input type="radio" name="driver_license" value="Y"/>&nbsp Yes</span>&nbsp &nbsp
                                                    <span><input type="radio" name="driver_license" value="N"/>&nbsp No</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <div class="display-table">
                                                <div class="table-cell">
                                                    <label for="agree_to_bg_check" class="control-label">
                                                        8. Safety is our priority, do you agree on a 3rd party background check? (
                                                        <a href="#" data-toggle="modal" data-target="#myModal" title="Learn More">
                                                            <span class="font-weight-normal">Learn More</span>
                                                        </a>)
                                                    </label>
                                                </div>
                                                <div class="table-cell text-right">
                                                    <span><input type="radio" name="agree_to_bg_check" value="Y" required>&nbsp Yes</span>&nbsp &nbsp
                                                    <span><input type="radio" name="agree_to_bg_check" value="N">&nbsp No</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <div class="display-table">
                                                <div class="table-cell">
                                                    <label for="groomer_edu" class="control-label">9. Do you have any certifications?</label>
                                                </div>
                                                <div class="table-cell text-right">
                                                    <span><input type="radio" name="groomer_edu" value="Y" required>&nbsp Yes</span>&nbsp &nbsp
                                                    <span><input type="radio" name="groomer_edu" value="N">&nbsp No</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <label class="control-label">10. Certification in</label>
                                            <div class="row">

                                                <div class="col-xs-6 col-sm-3">
                                                    <span><input name="groomer_edu_dog_grooming" type="checkbox" {{ old('') == 'Y' ? 'checked' : '' }} value="Y"/>&nbsp Dog Grooming</span>
                                                </div>
                                                <div class="col-xs-6 col-sm-3">
                                                    <span><input name="groomer_edu_cat_grooming" type="checkbox" {{ old('') == 'Y' ? 'checked' : '' }} value="Y"/>&nbsp Cat Grooming</span>
                                                </div>
                                                <div class="col-xs-6 col-sm-3">
                                                    <span><input name="groomer_edu_pet_safety_cpr" type="checkbox" {{ old('') == 'Y' ? 'checked' : '' }} value="Y"/>&nbsp Pet Safety / CPR</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <label for="groomer_exp_years" class="control-label">11. How many years of professional experience as a bather/groomer do you have?</label>
                                            <div class="row">

                                                <div class="col-xs-6 col-sm-3">
                                                    <span><input type="radio" name="groomer_exp_years" value="1" required>&nbsp Less than a year</span>
                                                </div>
                                                <div class="col-xs-6 col-sm-3">
                                                    <span><input type="radio" name="groomer_exp_years" value="2">&nbsp 2 years+</span>
                                                </div>
                                                <div class="col-xs-6 col-sm-3">
                                                    <span><input type="radio" name="groomer_exp_years" value="5">&nbsp 5 years+</span>
                                                </div>
                                                <div class="col-xs-6 col-sm-3">
                                                    <span><input type="radio" name="groomer_exp_years" value="10">&nbsp 10 years+</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{--                                <div class="row">--}}
                                {{--                                    <div class="col-xs-12">--}}
                                {{--                                        <div class="form-group">--}}
                                {{--                                            <label for="groomer_references" class="control-label">12. References</label>--}}
                                {{--                                            <input id="groomer_references" name="groomer_references" type="text" class="form-control" placeholder="Explain..." required>--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                </div>--}}
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <label for="groomer_edu_note" class="control-label">12. Tell us about yourself</label>
                                            <input id="groomer_edu_note" name="groomer_edu_note" type="text" class="form-control" placeholder="Tell us more...">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="groomed_photo" class="control-label">13. Photos of your recent groomings</label>
                                            <input type="file" id="groomed_photo" name="groomed_photo" value="{{ old('groomed_photo') }}" style="visibility:hidden"/>
                                            <a onclick="$('[name=groomed_photo]').click()" class="btn btn-red">Upload Images</a>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 text-center">
                                        <div class="c-photo hidden">
                                            <div class="c-photo__mask">
                                                <img class="sample c-photo__uploaded-photo" id="img_groomed_photo" src="images/grooming-photos.png" alt="Recent Groomings" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- /step-2 -->
                <div class="section section--white" id="step-3">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0">
                                <h2 class="text-center">Tools</h2>
                                <div class="form-group">
                                    <div class="table-cell">
                                        <label class="control-label">
                                            15. Do you have your own grooming tools? <!--(
                                            <a href="#" data-toggle="modal" data-target="#myModal" title="Learn More">
                                                <span class="font-weight-normal">Learn More</span>
                                            </a>)?-->
                                        </label> &nbsp &nbsp &nbsp &nbsp
                                    </div>
                                    <div class="table-cell">
                                        <b>
                                            <span><input type="radio" name="have_tool" value="Y" required>&nbsp Yes</span>&nbsp &nbsp
                                            <span><input type="radio" name="have_tool" value="N">&nbsp No</span>
                                        </b>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Tools Col 1 -->
                                    <div class="col-sm-3 col-xs-6">
                                        <div class="form-group">
                                            <label class="checkbox-inline" for="tool_1">
                                                <input type="checkbox" id="tool_1" name="tool_1" {{ old('tool_1') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                Tool Holders
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="checkbox-inline" for="tool_5">
                                                <input type="checkbox" id="tool_5" name="tool_5" {{ old('tool_5') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                Pin Brushes
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="checkbox-inline" for="tool_9">
                                                <input type="checkbox" id="tool_9" name="tool_9" {{ old('tool_9') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                Nail Grinders & Files
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="checkbox-inline" for="tool_13">
                                                <input type="checkbox" id="tool_13" name="tool_13" {{ old('tool_13') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                Hemostats
                                            </label>
                                        </div>
                                    </div>
                                    <!-- Tools Col 2 -->
                                    <div class="col-sm-3 col-xs-6">
                                        <div class="form-group">
                                            <label class="checkbox-inline" for="tool_2">
                                                <span><input type="checkbox" id="tool_2" name="tool_2" {{ old('tool_2') == 'Y' ? 'checked' : '' }} value="Y"/>Rubber Curry</span>
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="checkbox-inline" for="tool_6">
                                                <span><input type="checkbox" id="tool_6" name="tool_6" {{ old('tool_6') == 'Y' ? 'checked' : '' }} value="Y"/>Leash Suction Cup</span>
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="checkbox-inline" for="tool_10">
                                                <span><input type="checkbox" id="tool_10" name="tool_10" {{ old('tool_10') == 'Y' ? 'checked' : '' }} value="Y"/>Activet Brushes</span>
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="checkbox-inline" for="tool_14">
                                                <span><input type="checkbox" id="tool_14" name="tool_14" {{ old('tool_14') == 'Y' ? 'checked' : '' }} value="Y"/>Nail Scissors</span>
                                            </label>
                                        </div>
                                    </div>
                                    <!-- Tools Col 3 -->
                                    <div class="col-sm-3 col-xs-6">
                                        <div class="form-group">
                                            <label class="checkbox-inline" for="tool_3">
                                                <input type="checkbox" id="tool_3" name="tool_3" {{ old('tool_3') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                Slicker Brushes
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="checkbox-inline" for="tool_7">
                                                <input type="checkbox" id="tool_7" name="tool_7" {{ old('tool_7') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                Shedding Tools
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="checkbox-inline" for="tool_11">
                                                <input type="checkbox" id="tool_11" name="tool_11" {{ old('tool_11') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                Combs
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="checkbox-inline" for="tool_15">
                                                <input type="checkbox" id="tool_15" name="tool_15" {{ old('tool_15') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                Dryer
                                            </label>
                                        </div>
                                    </div>
                                    <!-- Tools Col 4 -->
                                    <div class="col-sm-3 col-xs-6">
                                        <div class="form-group">
                                            <label class="checkbox-inline" for="tool_4">
                                                <span><input type="checkbox" id="tool_4" name="tool_4" {{ old('tool_4') == 'Y' ? 'checked' : '' }} value="Y"/>Stripping Knives</span>
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="checkbox-inline" for="tool_8">
                                                <span><input type="checkbox" id="tool_8" name="tool_8" {{ old('tool_8') == 'Y' ? 'checked' : '' }} value="Y"/>Nail Clippers</span>
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="checkbox-inline" for="tool_12">
                                                <span><input type="checkbox" id="tool_12" name="tool_12" {{ old('tool_12') == 'Y' ? 'checked' : '' }} value="Y"/>Trimmer</span>
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="checkbox-inline" for="tool_16">
                                                <span><input type="checkbox" id="tool_16" name="tool_16" {{ old('tool_16') == 'Y' ? 'checked' : '' }} value="Y"/>Table</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /step-3 -->
                <div class="section section--grey-f4f4f4" id="step-4">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0">
                                <h2 class="text-center">Availability</h2>
                                <div class="form-group">
                                    <label class="control-label">16. Please, enter your potential availability.</label>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="availability">
                                            <!-- Days -->
                                            <div class="availability__row availability__days">
                                                <div class="availability__col availability__am-pm"></div>
                                                <div class="availability__col availability__hour"></div>
                                                <div class="availability__col">Mon</div>
                                                <div class="availability__col">Tue</div>
                                                <div class="availability__col">Wed</div>
                                                <div class="availability__col">Thurs</div>
                                                <div class="availability__col">Fri</div>
                                                <div class="availability__col">Sat</div>
                                                <div class="availability__col">Sun</div>
                                            </div>

                                            <!-- All hours -->
                                            <div class="availability__row availability__time">
                                                <div class="availability__col availability__hour availability__hour--all">All hours</div>
                                                @for ($x = 0; $x <= 6; $x++)
                                                    <div class="availability__col">
                                                        <input type="checkbox" id="{{'wd'.$x}}" name="{{'wd'.$x}}" value="Y" onclick="all_hours({{$x}})"/>
                                                        <label for="{{'wd'.$x}}"></label>
                                                    </div>
                                                @endfor

                                            </div>
                                            <!-- Hours -->
                                            @for ($i = 8; $i <= 24; $i++)
                                                <div class="availability__row availability__time">
                                                    @if ($i == 8 || $i == 24)
                                                        <div class="availability__col availability__am-pm">AM</div>
                                                    @elseif ($i == 12)
                                                        <div class="availability__col availability__am-pm">PM</div>
                                                    @else
                                                        <div class="availability__col availability__am-pm"></div>
                                                    @endif
                                                    @if ($i > 12)
                                                        <div class="availability__col availability__hour">{{$i - 12}}</div>
                                                    @else
                                                        <div class="availability__col availability__hour">{{$i}}</div>
                                                    @endif
                                                    @for ($x = 0; $x <= 6; $x++)
                                                        <div class="availability__col">
                                                            <input type="checkbox" id="{{'wd'.$x.'_h'.sprintf("%02d", $i)}}" name="{{'wd'.$x.'_h'.sprintf("%02d", $i)}}" value="Y"/>
                                                            <label for="{{'wd'.$x.'_h'.sprintf("%02d", $i)}}"></label>
                                                        </div>
                                                    @endfor
                                                </div>
                                        @endfor
                                        <!-- Days Bottom (mobile only) -->
                                            <div class="availability__row availability__days hidden-lg hidden-md hidden-sm">
                                                <div class="availability__col availability__am-pm"></div>
                                                <div class="availability__col availability__hour"></div>
                                                <div class="availability__col">Mon</div>
                                                <div class="availability__col">Tue</div>
                                                <div class="availability__col">Wed</div>
                                                <div class="availability__col">Thurs</div>
                                                <div class="availability__col">Fri</div>
                                                <div class="availability__col">Sat</div>
                                                <div class="availability__col">Sun</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Submit Btn -->
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group text-center">
                                    <a class="btn btn-red" href="javascript:submit_form()">SUBMIT</a>
                                    {{--                                <button name="submit" type="submit" class="btn btn-red">Submit</button>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /step-4 -->
            </form>
        </section>
        <!-- /application-form -->

    </div>
    <!-- /application-main -->


    <!-- Button trigger modal -->
    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h3>identity check</h3>
                    <h4>identity verification</h4>
                    <p>Social Security Number (SSN) verification is the most efficient way to verify your applicant's
                        identity. if an identity cannot be verified, the Checkr
                        system alerts the applicant to request additional documentation.</p>
                    <h4>Address hsitory</h4>
                    <p>Our identity Check includes a trace of all known addresses over the past seven years. Based on
                        this information,Checkr searches relevnat court jurisdictions
                        for the same time period</p>
                    <h3>criminal recors check</h3>
                    <h4>sex offender registry check</h4>
                    <p>A thorough background check should include a Sex Offender Registry Check. Checkr searches
                        registries for every state. The data returned includes
                        data of registration and current status.</p>
                    <h4>County criminal records check</h4>
                    <p>Checkr perfoms direct searches of county court records for different industies and copanies of
                        all sizes. This search is part of the baseline for
                        establishing due diligence. Results include felony and misdemeanor criminal cases as well as,
                        charges, dispostion, dates and sentencing information.</p>
                    <h4>State criminal records check</h4>
                    <p>Every state maintains a repository of its criminal records. Because of varying state laws, some
                        repoitories do not offer compete data for each of
                        their counties or access. Checkr recommends running this check concurrent with the county
                        criminal records check for the most thorough results.</p>
                    <h4>national criminal records check</h4>
                    <p>This check scours over 30 millon records. It is an excellent 'lead source' for records to be
                        seached at the county level because it revelas summary
                        case information. It must be run concurrent with the county criminal records search.</p>
                    <h4>federal criminal records check</h4>
                    <p>This search is part of hte baseline for establishing due diligence and is run concurrent with the
                        county criminal recods check. Results include
                        violations of federal law.</p>
                    <h4>Global watchlist check</h4>
                    <p>This check searches known domestic and international terrorist watchlists as well as the records
                        of the Office of inspector General (OIG), Excluded
                        Parties List (EPL) and additional domestic and international agency lists.</p>
                    <h4>county and federal civil records check</h4>
                    <p>This check provides access to Superior (upper) and Municipal (lower) courts for civil records, as
                        well as those presided over by the federal district
                        court sysem.</p>
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>


@stop