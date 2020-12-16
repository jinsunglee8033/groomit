@extends('includes.default')

@section('content')

    <script type="text/javascript">
        var onload_events = window.onload;
        var validator = null;
        window.onload = function () {
            if (onload_events) {
                onload_events();
            }

            validator = $('#frm_app').validate({
                focusInvalid: true,
                focusCleanup: true,
                ignore: [],
                rules: {
                    phone: {
                        regex: /^\d{10}$/
                    },
                    password_confirm : {
                        equalTo: "#password"
                    },
                    zip: {
                        regex: /^\d{5}$/
                    },
                    mobile_phone: {
                        regex: /^\d{10}$/
                    },
                    groomer_edu_note: {
                        required: {
                            depends: function() {
                                return $('[name=groomer_edu_other]:checked').val() == 'Y'
                            }
                        }
                    },
                    agree_to_bg_check: {
                        same: 'Y'
                    },
                    groomed_photo: {
                        required: true
                    }
                },
                messages: {
                    phone: {
                        regex: 'Please enter valid 10 digit phone number'
                    },
                    mobile_phone: {
                        regex: 'Please enter valid 10 digit phone number'
                    },
                    zip: {
                        regex: 'Please enter valid 5 digit zipcode'
                    },
                    groomed_photo: {
                        required: 'Please upload image'
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
                    _all_: {trigger: 'focus'}
                }
            });

            $("#groomed_photo").change(function(){
                //readURL(this);
                previewImage(this,'img_groomed_photo');
            });

            $('#profile_photo').change(function() {
                previewImage(this,'img_profile_photo');
            });


            @if (session()->has('success') && session('success') == 'Y')
                $('#success').modal();
            @endif

            @if (count($errors) > 0)
                $('#error').modal();
            @endif
        };

        function show_location() {

            if (
                !$('#full_name').valid() ||
                    !$('#email').valid() ||
                    !$('#phone').valid() ||
                    !$('#password').valid() ||
                    !$('#password_confirm').valid()) {

                validator.focusInvalid();
                return;
            }

            $('.disabled').fadeOut('slow');

            $('#collapseOne').collapse('show');
        }

        function show_experience() {
            if (
                !$('#street').valid() ||
                    !$('#zip').valid() ||
                    !$('#city').valid() ||
                    !$('#mobile_phone').valid()) {

                validator.focusInvalid();
                return;
            }

            if($('#state').val() == ''){
                alert("please chose State!");
                return;
            }
            $('#collapseTwo').collapse('show');
        }

        function show_references() {
            if (
                !$('[name=groomer_exp]').valid() ||
                    !$('[name=groomer_edu]').valid() ||
                    !$('#groomer_edu_note').valid() ||
                    !$('[name=groomer_exp_years]').valid()

            ) {
                validator.focusInvalid();
                return;
            }
            $('#collapseThree').collapse('show');
        }

        function show_background_check() {
            if (
                !$('[name=groomer_references]').valid() ||
                    !$('#groomed_photo').valid()
            ) {
                validator.focusInvalid();
                return;
            }


            $('#collapseFour').collapse('show');
        }

        function show_grooming_tools() {
            if (!$('[name=agree_to_bg_check]').valid()) {
                validator.focusInvalid();
                return;
            }

            $('#collapseFive').collapse('show');
        }

        function show_availability() {
            $('#collapseSix').collapse('show');
        }

        function show_profile_photo() {
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

            if (!at_least_one_checked) {
                $('#lbl_wd0_h08').tooltip('show');
                return;
            }


            $('#collapseSeven').collapse('show');
        }

        function submit_form() {

            //$('#frm_app').removeData("validator").removeData("unobtrusiveValidation");//remove the form validation
            //$.validator.unobtrusive.parse($('#frm_app'));//add the form validation




            if (
                !$('#full_name').valid() ||
                !$('#email').valid() ||
                !$('#phone').valid() ||
                !$('#password').valid() ||
                !$('#password_confirm').valid()) {

                location.href = "#apply-now";
                validator.focusInvalid();
                return;
            }

            console.log('### step 1 passwd ###');

            if (
                !$('#street').valid() ||
                !$('#zip').valid() ||
                !$('#city').valid() ||
                !$('#state').valid() ||
                !$('#mobile_phone').valid()) {

                $('#collapseOne').collapse('show');
                validator.focusInvalid();
                return;
            }

            console.log('### step 2 passwd ###');

            if (
                !$('[name=groomer_exp]').valid() ||
                !$('[name=groomer_edu]').valid() ||
                !$('#groomer_edu_note').valid() ||
                !$('[name=groomer_exp_years]').valid()

            ) {

                $('#collapseTwo').collapse('show');
                validator.focusInvalid();
                return;
            }

            if (
                !$('[name=groomer_references]').valid() ||
                !$('#groomed_photo').valid()
            ) {
                $('#collapseThree').collapse('show');
                validator.focusInvalid();
                return;
            }

            if (!$('[name=agree_to_bg_check]').valid()) {

                $('#collapseFour').collapse('show');
                validator.focusInvalid();
                return;
            }

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

            if (!at_least_one_checked) {
                $('#collapseSix').collapse('show');
                $('#lbl_wd0_h08').tooltip('show');
                validator.focusInvalid();
                return;
            }

            if (!$('#profile_photo').valid()) {
                $('#collapseSeven').collapse('show');
                validator.focusInvalid();
                return;
            }

            $('#frm_app').submit();
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
    </script>

    @if (session()->has('success') && session('success') == 'Y')
        <div id="success" class="modal fade " tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false"
             style="display:block;">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Success</h4>
                    </div>
                    <div class="modal-body">
                        <p>
                            Your request is being processed.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
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
                        <h4 class="modal-title">Error</h4>
                    </div>
                    <div class="modal-body">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div id="application-main">
        <section id="banner" class="display-table" style="padding:0 !important;padding-top:0 !important; position:relative;"> <!-- #banner starts -->
            <!-- <div class="container container-banner-info table-cell">
                <div class="col-xs-12" id="centerBannerHome" >
                    <h1 data-aos="zoom-in" data-aos-delay="750">Work with us</h1>
                </div>
            </div> -->
        </section>
        <!-- /end -->
        <section id="benefits" class="log-out"> <!-- #benefits starts -->
            <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center col-sm-12 col-xs-12 ">
                    <div class=""></div>
                    <h3 class="media-heading">Why work with us</h3>
                    <p>Welcome to the nations first In-Home Pet Grooming Service App on Demand. <br>Make money on your own schedule, be your own boss.<br><br></p>
                </div>
                <div class="col-lg-12 text-center">
                    <iframe id="aplicationVideo" width="672" height="378" src="https://www.youtube.com/embed/qlshwqaRISQ?rel=0&amp;showinfo=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                </div>
            </div>
            <div class="row">

                <div class="col-lg-8 col-lg-offset-2 ">
                    <div class=""></div>
                    <div class="work-us">
                        <div class="col-lg-6 col-sm-6 col-xs-12">
                            <ul>
                                <li><p><span><img class="img-responsive" src="images/icon-own-schedule.png" alt="Create your own schedule"/></span> Create your own schedule</p></li>
                                <li><p><span><img class="img-responsive" src="images/icon-insurance-provided.png" alt="Insurance provided"/></span> Insurance provided</p></li>
                            </ul>
                        </div>
                        <div class="col-lg-6 col-sm-6 col-xs-12">
                            <ul>
                                <li><p><span><img class="img-responsive" src="images/icon-more-money.png" alt="Earn more money with less hassle"/></span>Earn more money with less hassle</p></li>
                                <li><p><span><img class="img-responsive" src="images/icon-keep-tips.png" alt="Keep 100% of all your tips"/></span>Keep 100% of all your tips</p></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <form id="frm_app" name="frm_app" method="post" action="/application" enctype="multipart/form-data">
                {!! csrf_field() !!}
                <div class="container">

                    <div class="row">
                        <div class="col-lg-8 text-center col-lg-offset-2">
                        <h2 class="h2title text-center">Application</h2>
                        </div>


                        <div class="col-lg-8 text-center col-lg-offset-2">
                            <div class="formSignUpApp">
                                <div class="field">
                                    <div class="col-lg-12 col-md-12 col-sm-12 normal-first">
                                        <input type="text" id="full_name" name="full_name" placeholder="Full Name" value="{{
                                        old
                                        ('full_name') }}" required/>
                                    </div>
                                </div>


                                <div class="field">
                                    <div class="col-lg-12 col-md-12 col-sm-12 normal-first">
                                        <input type="email" id="email" name="email" placeholder="Email" value="{{ old('email') }}" required/>
                                    </div>
                                </div>

                                <div class="field">
                                    <div class="col-lg-12 col-md-12 col-sm-12 normal-first">
                                        <input type="text" id="phone" name="phone" placeholder="Phone Number" value="{{ old('phone') }}" required/>
                                    </div>
                                </div>

                                <div class="field">
                                    <div class="col-lg-12 col-md-12 col-sm-12 normal-first">
                                        <input type="password" id="password" name="password" placeholder="Password" value="{{ old('password') }}" required/>
                                    </div>
                                </div>

                                <div class="field">
                                    <div class="col-lg-12 col-md-12 col-sm-12 normal-first">
                                        <input type="password" id="password_confirm" name="password_confirm" value="{{ old('password_confirm') }}" placeholder="Password Confirm" required/>
                                    </div>
                                </div>

                                <div class="field">
                                    <div class="col-lg-12  text-center normal-first">
                                        <!--a class="scrollToTop collapsed submitBtn signUpSubmit enableDisabled" data-toggle="collapse" data-parent="#accordion" data-target="#collapseOne"   aria-controls="collapseOne">CONTINUE</a-->
                                        <a class="scrollToTop collapsed submitBtn signUpSubmit enableDisabled"
                                        href="javascript:show_location()">CONTINUE</a>

                                        <p><br><br>Tell us more about you</p>

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="container">

                    <div class="panel-group contDisabled" id="accordion">
                        <div class="disabled"></div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion"
                                    data-target="#collapseOne" aria-expanded="true"
                                    aria-controls="collapseOne">LOCATION </a>
                                </h4>
                            </div>
                            <div id="collapseOne" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="col-lg-10 col-lg-offset-1">
                                        <div>
                                            <div class="field">
                                                <div class="col-lg-12 normal-first">
                                                    <input type="text" id="street" name="street" value="{{ old('street') }}" placeholder="Street" required/>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <div class="col-lg-5 normal col-sm-5 col-xs-12">
                                                    <input type="text" id="city" name="city" value="{{ old('city') }}" placeholder="City" required/>
                                                </div>
                                                <div class="col-lg-4 normal col-sm-4 col-xs-12">
{{--                                                    <input type="text" id="state" name="state" value="{{ old('state') }}" placeholder="State" required/>--}}
                                                    <select id="state" name="state" required>
                                                        <option value="">Choose State:</option>
                                                        <option value="AL">Alabama</option>
                                                        <option value="AK">Alaska</option>
                                                        <option value="AZ">Arizona</option>
                                                        <option value="AR">Arkansas</option>
                                                        <option value="CA">California</option>
                                                        <option value="CO">Colorado</option>
                                                        <option value="CT">Connecticut</option>
                                                        <option value="DE">Delaware</option>
                                                        <option value="FL">Florida</option>
                                                        <option value="GA">Georgia</option>
                                                        <option value="HI">Hawaii</option>
                                                        <option value="ID">Idaho</option>
                                                        <option value="IL">Illinois</option>
                                                        <option value="IN">Indiana</option>
                                                        <option value="IA">Iowa</option>
                                                        <option value="KS">Kansas</option>
                                                        <option value="KY">Kentucky</option>
                                                        <option value="LA">Louisiana</option>
                                                        <option value="ME">Maine</option>
                                                        <option value="MD">Maryland</option>
                                                        <option value="MA">Massachusetts</option>
                                                        <option value="MI">Michigan</option>
                                                        <option value="MN">Minnesota</option>
                                                        <option value="MS">Mississippi</option>
                                                        <option value="MO">Missouri</option>
                                                        <option value="MT">Montana</option>
                                                        <option value="NE">Nebraska</option>
                                                        <option value="NV">Nevada</option>
                                                        <option value="NH">New Hampshire</option>
                                                        <option value="NJ">New Jersey</option>
                                                        <option value="NM">New Mexico</option>
                                                        <option value="NY">New York</option>
                                                        <option value="NC">North Carolina</option>
                                                        <option value="ND">North Dakota</option>
                                                        <option value="OH">Ohio</option>
                                                        <option value="OK">Oklahoma</option>
                                                        <option value="OR">Oregon</option>
                                                        <option value="PA">Pennsylvania</option>
                                                        <option value="RI">Rhode Island</option>
                                                        <option value="SC">South Carolina</option>
                                                        <option value="SD">South Dakota</option>
                                                        <option value="TN">Tennessee</option>
                                                        <option value="TX">Texas</option>
                                                        <option value="UT">Utah</option>
                                                        <option value="VT">Vermont</option>
                                                        <option value="VA">Virginia</option>
                                                        <option value="WA">Washington</option>
                                                        <option value="WV">West Virginia</option>
                                                        <option value="WI">Wisconsin</option>
                                                        <option value="WY">Wyoming</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-3 normal col-sm-3 col-xs-12">
                                                    <input type="text" id="zip" name="zip" value="{{ old('zip') }}" placeholder="ZIP" required/>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <div class="col-lg-12 normal-first">
                                                    <input type="text" id="mobile_phone" name="mobile_phone" value="{{ old('mobile_phone') }}" placeholder="Mobile Phone Number" required/>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <div class="col-lg-12 normal pt-1">
                                                    <label>What area do you service?</label>
                                                    <div class="checkbox nm np">
                                                        <label for="service_ny">
                                                            <span>
                                                                <input type="checkbox" name="service_ny" value="Y">
                                                                New York City
                                                            </span>
                                                        </label>
                                                        <label for="service_nj">
                                                            <span>
                                                                <input type="checkbox" name="service_nj" value="Y">
                                                                New Jersey
                                                            </span>
                                                        </label>
                                                        <label for="service_ct">
                                                            <span>
                                                                <input type="checkbox" name="service_ct" value="Y">
                                                                Connecticut
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <div class="col-lg-12 normal pt-2">
                                                    <label>Are you willing to relocate?</label>
                                                    <div class="clearMobile">
                                                        <span><input name="relocation" type="radio" {{ old('relocation') == 'Y' ? 'checked' : '' }} value="Y"/>Yes</span>
                                                        <span><input name="relocation" type="radio" {{ old('relocation') == 'N' ? 'checked' : '' }} value="N"/>No</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <div class="col-lg-12 normal pt-3">
                                                    <div class="checkbox nm np">
                                                        <label for="notify_availability">
                                                            <span>
                                                                <input type="checkbox" {{ old('notify_availability') == 'Y' ? 'checked' : '' }} value="Y" name="notify_availability">
                                                                Please, let me know when you are available in my area.
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <div class="col-lg-12 text-center normal-first">
                                                    <!--a class="scrollToTop collapsed submitBtn" data-toggle="collapse" data-parent="#accordion" data-target="#collapseTwo"   aria-controls="collapseTwo">CONTINUE</a-->
                                                    <a class="scrollToTop collapsed submitBtn"
                                                    href="javascript:show_experience()">CONTINUE</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion"
                                    data-target="#collapseTwo" aria-controls="collapseTwo">EXPERIENCE</a>
                                </h4>
                            </div>
                            <div id="collapseTwo" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="col-lg-10 col-lg-offset-1">
                                        <div class="normal-space">
                                            <div class="field fieldTitle">
                                                <div class="col-lg-12 normal-first">
                                                    <label>Have you worked as a pet groomer before?</label>
                                                    <div class="clearMobile">
                                                        <span><input name="groomer_exp" type="radio" {{ old('groomer_exp') == 'Y' ? 'checked' : '' }} value="Y"/>Yes</span>
                                                        <span><input name="groomer_exp" type="radio" {{ old('groomer_exp') == 'N' ? 'checked' : '' }} required aria-required="true" value="N"/>No</span>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="field fieldTitle">
                                                <div class="col-lg-12 normal col-sm-12 col-xs-12">
                                                <label>Where did you learn to groom?</label>
                                                <div class="clearMobile">
                                                    <input type="text" id="groomer_exp_note" name="groomer_exp_note" value="{{ old('groomer_exp_note') }}" placeholder="Explain"/>
                                                </div>
                                                </div>
                                            </div>

                                            <div class="field fieldTitle">
                                                <div class="col-lg-12 normal-first">
                                                    <label>What do you groom?</label>
                                                    <div class="clearMobile">
                                                    <span><input name="groomer_target" type="radio" {{ old('') == 'Y' ? 'checked' : '' }} value="D"/>Dogs</span>
                                                    <span><input name="groomer_target" type="radio" {{ old('') == 'Y' ? 'checked' : '' }} value="C"/>Cats</span>
                                                    <span><input name="groomer_target" type="radio" {{ old('') == 'Y' ? 'checked' : '' }} value="B"/>Both</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="field fieldTitle">
                                                <div class="col-lg-12 normal-first">
                                                    <label>Do you have any certifications?</label>
                                                    <div class="clearMobile">
                                                    <span><input name="groomer_edu" type="radio" {{ old('groomer_edu') == 'Y' ? 'checked' : '' }} value="Y"/>Yes</span>
                                                    <span><input name="groomer_edu" type="radio" {{ old('groomer_edu') == 'N' ? 'checked' : '' }} required aria-required="true" value="N"/>No</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <div class="col-lg-12 normal-first">
                                                    <label>If yes, mark all that apply:</label>
                                                    <div class="clearMobile">
                                                    <span><input name="groomer_edu_dog_grooming" type="checkbox" {{ old('') == 'Y' ? 'checked' : '' }} value="Y"/>Dog Grooming</span>
                                                    <span><input name="groomer_edu_cat_grooming" type="checkbox" {{ old('') == 'Y' ? 'checked' : '' }} value="Y"/>Cat Grooming</span>
                                                    <span><input name="groomer_edu_pet_safety_cpr" type="checkbox" {{ old('') == 'Y' ? 'checked' : '' }} value="Y"/>Pet Safety / CPR</span>
                                                    <span><input name="groomer_edu_breed_standards" type="checkbox" {{ old('') == 'Y' ? 'checked' : '' }} value="Y"/>Breed Standards</span>
                                                    <span><input name="groomer_edu_other" type="checkbox" {{ old('') == 'Y' ? 'checked' : '' }} value="Y"/>Other</span>
                                                    <input type="text" name="groomer_edu_note" id="groomer_edu_note"  value="{{ old('groomer_edu_note') }}" placeholder="If other, explain"/>

                                                    </div>
                                                    <div class="clearMobile">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="field fieldTitle ">
                                                <div class="col-lg-12">
                                                    <label>Length of time grooming</label>
                                                </div>
                                                <div class="col-lg-3">

                                                    <div class="checkbox">
                                                        <label>
                                                            <input name="groomer_exp_years" {{ old('groomer_exp_years') == 1 ? 'checked' : '' }} value="1" required aria-required="true"
                                                                type="checkbox"/><span>Less than 1 year</span>
                                                        </label>
                                                    </div>

                                                </div>
                                                <div class="col-lg-3">

                                                    <div class="checkbox">
                                                        <label>
                                                            <input name="groomer_exp_years" {{ old('groomer_exp_years') == 2 ? 'checked' : '' }} value="2"
                                                                type="checkbox"/><span>2 years or more</span>
                                                        </label>
                                                    </div>

                                                </div>
                                                <div class="col-lg-3">

                                                    <div class="checkbox">
                                                        <label>
                                                            <input name="groomer_exp_years" {{ old('groomer_exp_years') == 5 ? 'checked' : '' }} value="5"
                                                                type="checkbox"/><span>5 years or more</span>
                                                        </label>
                                                    </div>

                                                </div>
                                                <div class="col-lg-3">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input name="groomer_exp_years" {{ old('groomer_exp_years') == 10 ? 'checked' : '' }} value="10"
                                                                type="checkbox"/><span>10 years or more</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <div class="col-lg-12  text-center normal-first">
                                                    <a class="scrollToTop collapsed submitBtn"
                                                    href="javascript:show_references()">CONTINUE</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title" id="references-link">
                                    <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion"
                                    data-target="#collapseThree" aria-controls="collapseThree">REFERENCES</a>
                                </h4>
                            </div>
                            <div id="collapseThree" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="col-lg-10 col-lg-offset-1">
                                        <div class="normal-space">
                                            <div class="field fieldTitle">
                                                <div class="col-lg-12 normal-first">
                                                    <label>Please provide references</label>
                                                    <div class="clearMobile">
                                                    <input type="text" id="" name="groomer_references" value="{{ old('groomer_references') }}" required placeholder="Explain"/>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="field fieldTitle">
                                                <div class="col-lg-6 col-sm-6 col-xs-12">
                                                    <label>Please upload photos of your recent groomings</label>
                                                    <img id="img_groomed_photo" style="max-height:80px;"/>
                                                </div>
                                                <div class="col-lg-6 col-sm-6 text-right col-xs-12">
                                                    <input type="file" id="groomed_photo" name="groomed_photo" value="{{ old('groomed_photo') }}" style="visibility:hidden"/>
                                                    <a class="btn-success" onclick="$('#groomed_photo').click()">
                                                        Upload Images
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="field fieldTitle">
                                                <div class="col-lg-12">
                                                    <label>How did you hear about us?</label>
                                                    <input type="text" id="" name="groomer_how_knew_groomit" value="{{ old('groomer_how_knew_groomit') }}" required/>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <div class="col-lg-12  text-center normal-first">
                                                    <a class="scrollToTop collapsed submitBtn"
                                                    href="javascript:show_background_check()">CONTINUE</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion"
                                    data-target="#collapseFour" aria-controls="collapseFour">BACKGROUND CHECK</a>
                                </h4>
                            </div>
                            <div id="collapseFour" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="col-lg-10 col-lg-offset-1">
                                        <div class="normal-space">
                                            <div class="field fieldTitle">
                                                <div class="col-lg-12 text-center normal-first">
                                                    <label class="bottom-space">GroomIt is all about safety and comfort. Do
                                                        you agree to run a Background Check with a 3rd Party Provider? (<a
                                                                href="#" class="red" data-toggle="modal"
                                                                data-target="#myModal">Learn More</a>) </label>
                                                    <div class="clearMobile">
                                                        <span><input name="agree_to_bg_check" type="radio" {{ old('agree_to_bg_check') == 'N' ? 'checked' : '' }} required aria-required="true" value="N"/>No</span>
                                                        <span><input name="agree_to_bg_check" type="radio" {{ old('agree_to_bg_check') == 'Y' ? 'checked' : '' }} value="Y"/>Yes</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <div class="col-lg-12  text-center normal-first">
                                                    <a class="scrollToTop collapsed submitBtn"
                                                    href="javascript:show_grooming_tools()">CONTINUE</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion"
                                    data-target="#collapseFive" aria-controls="collapseFive">GROOMING TOOLS</a>
                                </h4>
                            </div>
                            <div id="collapseFive" class="panel-collapse collapse">
                                <div class="panel-body">
                                <div class="col-lg-10 col-lg-offset-1">

                                        <div class="field fieldTitle text-center">
                                            <div class="col-lg-12 normal-first">
                                                <label>Do you have your own grooming tools?</label>
                                                <div class="clearMobile">
                                                <span><input name="have_tool" type="radio" {{ old('') == 'Y' ? 'checked' : '' }} value="Y"/>Yes</span>
                                                <span><input name="have_tool" type="radio" {{ old('') == 'N' ? 'checked' : '' }} required aria-required="true" value="N"/>No</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="field fieldTitle">
                                            <div class="col-lg-12 text-center">
                                                <label>Which of these tools do you have?</label>
                                            </div>
                                        </div>


                                        <div class="normal-space">
                                            <div class="row">
                                                <div class="col-lg-4 col-md-4 col-lg-offset-2 col-md-offset-2">

                                                    <div class="field tools">
                                                        <div class="col-lg-10 col-md-10 col-lg-offset-1 col-md-offset-1 flush ">


                                                            <div class="checkbox">
                                                                <label>
                                                                    <span><input type="checkbox" id="tool_1" name="tool_1" {{ old('tool_1') == 'Y' ? 'checked' : '' }} value="Y"/>Tool Holders</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <span><input type="checkbox" id="tool_3" name="tool_3" {{ old('tool_3') == 'Y' ? 'checked' : '' }} value="Y"/>Slicker Brushes</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <span><input type="checkbox" id="tool_5" name="tool_5" {{ old('tool_5') == 'Y' ? 'checked' : '' }} value="Y"/>Pin Brushes</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <span><input type="checkbox" id="tool_7" name="tool_7" {{ old('tool_7') == 'Y' ? 'checked' : '' }} value="Y"/>Shedding Tools</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <span><input type="checkbox" id="tool_9" name="tool_9" {{ old('tool_9') == 'Y' ? 'checked' : '' }} value="Y"/>Nail Grinders & Files</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <span><input type="checkbox" id="tool_11" name="tool_11" {{ old('tool_11') == 'Y' ? 'checked' : '' }} value="Y"/>Combs</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <span><input type="checkbox" id="tool_13" name="tool_13" {{ old('tool_13') == 'Y' ? 'checked' : '' }} value="Y"/>Hemostats</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <span><input type="checkbox" id="tool_15" name="tool_15" {{ old('tool_15') == 'Y' ? 'checked' : '' }} value="Y"/>Dryer</span>
                                                                </label>
                                                            </div>

                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="col-lg-4 col-md-4">

                                                    <div class="field tools">
                                                        <div class="col-lg-10 col-md-10 col-lg-offset-1 col-md-offset-1 flush ">

                                                            <div class="checkbox">
                                                                <label>
                                                                    <span><input type="checkbox" id="tool_2" name="tool_2" {{ old('tool_2') == 'Y' ? 'checked' : '' }} value="Y"/>Rubber Curry</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <span><input type="checkbox" id="tool_4" name="tool_4" {{ old('tool_4') == 'Y' ? 'checked' : '' }} value="Y"/>Stripping Knives</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <span><input type="checkbox" id="tool_6" name="tool_6" {{ old('tool_6') == 'Y' ? 'checked' : '' }} value="Y"/>Leash Suction Cup</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <span><input type="checkbox" id="tool_8" name="tool_8" {{ old('tool_8') == 'Y' ? 'checked' : '' }} value="Y"/>Nail Clippers</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <span><input type="checkbox" id="tool_10" name="tool_10" {{ old('tool_10') == 'Y' ? 'checked' : '' }} value="Y"/>Activet Brushes</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <span><input type="checkbox" id="tool_12" name="tool_12" {{ old('tool_12') == 'Y' ? 'checked' : '' }} value="Y"/>Trimmer</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <span><input type="checkbox" id="tool_14" name="tool_14" {{ old('tool_14') == 'Y' ? 'checked' : '' }} value="Y"/>Nail Scissors</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <span><input type="checkbox" id="tool_16" name="tool_16" {{ old('tool_16') == 'Y' ? 'checked' : '' }} value="Y"/>Table</span>
                                                                </label>
                                                            </div>

                                                        </div>
                                                    </div>

                                                </div>

                                            </div>
                                            <div class="field">
                                                <div class="col-lg-12  text-center normal-first">
                                                    <a class="scrollToTop collapsed submitBtn"
                                                    href="javascript:show_availability()">CONTINUE</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="scrollToTop collapsed openAvailability collapsed" data-toggle="collapse"
                                    data-parent="#accordion" data-target="#collapseSix" aria-controls="collapseSix">AVAILABILITY</a>
                                </h4>
                            </div>
                            <div id="collapseSix" class="panel-collapse collapse">
                                <div class="panel-body contDateHidde">

                                    <div class="field">
                                        <div class="col-lg-12 text-center">
                                            <label>What is your weekly availability to serve appointments?</label>
                                        </div>
                                    </div>

                                    <div class="dateHidde text-center">
                                        <img class="img-responsive" src="images/loading.gif" alt=""/>
                                        <p>Loading...</p>
                                    </div>
                                    <div class="col-lg-10 col-md-10 col-lg-offset-1 col-md-offset-1">


                                        <div class="availabilityCont">

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="availabilityBox availabilityBoxAm1 text-align-right">
                                                        AM
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxPm text-align-left">
                                                        PM
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxAm2 text-align-right">
                                                        AM
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="availabilityBox availabilityBoxNum text-center">

                                                    </div>
                                                    <div class="availabilityBox availabilityBoxNum text-center">
                                                        8
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxNum text-center">
                                                        9
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxNum text-center">
                                                        10
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxNum text-center">
                                                        11
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxNum text-center">
                                                        12
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxNum text-center">
                                                        1
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxNum text-center">
                                                        2
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxNum text-center">
                                                        3
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxNum text-center">
                                                        4
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxNum text-center">
                                                        5
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxNum text-center">
                                                        6
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxNum text-center">
                                                        7
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxNum text-center">
                                                        8
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxNum text-center">
                                                        9
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxNum text-center">
                                                        10
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxNum text-center">
                                                        11
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxNum text-center">
                                                        12
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="availabilityBox availabilityBoxDay">
                                                        <span>M</span>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd0_h08" name="wd0_h08" {{ old('wd0_h08') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd0_h08" id="lbl_wd0_h08" data-toggle="tooltip" title="Please setup your availability"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd0_h09" name="wd0_h09" {{ old('wd0_h09') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd0_h09"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd0_h10" name="wd0_h10" {{ old('wd0_h10') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd0_h10"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd0_h11" name="wd0_h11" {{ old('wd0_h11') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd0_h11"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd0_h12" name="wd0_h12" {{ old('wd0_h12') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd0_h12"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd0_h13" name="wd0_h13" {{ old('wd0_h13') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd0_h13"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd0_h14" name="wd0_h14" {{ old('wd0_h14') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd0_h14"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd0_h15" name="wd0_h15" {{ old('wd0_h15') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd0_h15"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd0_h16" name="wd0_h16" {{ old('wd0_h16') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd0_h16"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd0_h17" name="wd0_h17" {{ old('wd0_h17') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd0_h17"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd0_h18" name="wd0_h18" {{ old('wd0_h18') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd0_h18"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd0_h19" name="wd0_h19" {{ old('wd0_h19') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd0_h19"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd0_h20" name="wd0_h20" {{ old('wd0_h20') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd0_h20"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd0_h21" name="wd0_h21" {{ old('wd0_h21') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd0_h21"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd0_h22" name="wd0_h22" {{ old('wd0_h22') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd0_h22"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd0_h23" name="wd0_h23" {{ old('wd0_h23') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd0_h23"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd0_h24" name="wd0_h24" {{ old('wd0_h24') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd0_h24"></label>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="availabilityBox availabilityBoxDay">
                                                        <span>T</span>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd1_h08" name="wd1_h08" {{ old('wd1_h08') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd1_h08"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd1_h09" name="wd1_h09" {{ old('wd1_h09') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd1_h09"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd1_h10" name="wd1_h10" {{ old('wd1_h10') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd1_h10"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd1_h11" name="wd1_h11" {{ old('wd1_h11') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd1_h11"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd1_h12" name="wd1_h12" {{ old('wd1_h12') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd1_h12"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd1_h13" name="wd1_h13" {{ old('wd1_h13') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd1_h13"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd1_h14" name="wd1_h14" {{ old('wd1_h14') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd1_h14"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd1_h15" name="wd1_h15" {{ old('wd1_h15') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd1_h15"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd1_h16" name="wd1_h16" {{ old('wd1_h16') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd1_h16"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd1_h17" name="wd1_h17" {{ old('wd1_h17') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd1_h17"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd1_h18" name="wd1_h18" {{ old('wd1_h18') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd1_h18"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd1_h19" name="wd1_h19" {{ old('wd1_h19') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd1_h19"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd1_h20" name="wd1_h20" {{ old('wd1_h20') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd1_h20"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd1_h21" name="wd1_h21" {{ old('wd1_h21') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd1_h21"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd1_h22" name="wd1_h22" {{ old('wd1_h22') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd1_h22"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd1_h23" name="wd1_h23" {{ old('wd1_h23') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd1_h23"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd1_h24" name="wd1_h24" {{ old('wd1_h24') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd1_h24"></label>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="availabilityBox availabilityBoxDay">
                                                        <span>W</span>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd2_h08" name="wd2_h08" {{ old('wd2_h08') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd2_h08"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd2_h09" name="wd2_h09" {{ old('wd2_h09') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd2_h09"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd2_h10" name="wd2_h10" {{ old('wd2_h10') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd2_h10"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd2_h11" name="wd2_h11" {{ old('wd2_h11') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd2_h11"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd2_h12" name="wd2_h12" {{ old('wd2_h12') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd2_h12"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd2_h13" name="wd2_h13" {{ old('wd2_h13') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd2_h13"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd2_h14" name="wd2_h14" {{ old('wd2_h14') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd2_h14"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd2_h15" name="wd2_h15" {{ old('wd2_h15') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd2_h15"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd2_h16" name="wd2_h16" {{ old('wd2_h16') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd2_h16"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd2_h17" name="wd2_h17" {{ old('wd2_h17') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd2_h17"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd2_h18" name="wd2_h18" {{ old('wd2_h18') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd2_h18"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd2_h19" name="wd2_h19" {{ old('wd2_h19') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd2_h19"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd2_h20" name="wd2_h20" {{ old('wd2_h20') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd2_h20"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd2_h21" name="wd2_h21" {{ old('wd2_h21') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd2_h21"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd2_h22" name="wd2_h22" {{ old('wd2_h22') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd2_h22"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd2_h23" name="wd2_h23" {{ old('wd2_h23') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd2_h23"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd2_h24" name="wd2_h24" {{ old('wd2_h24') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd2_h24"></label>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="availabilityBox availabilityBoxDay">
                                                        <span>T</span>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd3_h08" name="wd3_h08" {{ old('wd3_h08') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd3_h08"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd3_h09" name="wd3_h09" {{ old('wd3_h09') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd3_h09"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd3_h10" name="wd3_h10" {{ old('wd3_h10') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd3_h10"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd3_h11" name="wd3_h11" {{ old('wd3_h11') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd3_h11"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd3_h12" name="wd3_h12" {{ old('wd3_h12') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd3_h12"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd3_h13" name="wd3_h13" {{ old('wd3_h13') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd3_h13"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd3_h14" name="wd3_h14" {{ old('wd3_h14') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd3_h14"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd3_h15" name="wd3_h15" {{ old('wd3_h15') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd3_h15"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd3_h16" name="wd3_h16" {{ old('wd3_h16') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd3_h16"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd3_h17" name="wd3_h17" {{ old('wd3_h17') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd3_h17"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd3_h18" name="wd3_h18" {{ old('wd3_h18') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd3_h18"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd3_h19" name="wd3_h19" {{ old('wd3_h19') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd3_h19"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd3_h20" name="wd3_h20" {{ old('wd3_h20') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd3_h20"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd3_h21" name="wd3_h21" {{ old('wd3_h21') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd3_h21"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd3_h22" name="wd3_h22" {{ old('wd3_h22') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd3_h22"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd3_h23" name="wd3_h23" {{ old('wd3_h23') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd3_h23"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd3_h24" name="wd3_h24" {{ old('wd3_h24') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd3_h24"></label>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="availabilityBox availabilityBoxDay">
                                                        <span>F</span>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd4_h08" name="wd4_h08" {{ old('wd4_h08') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd4_h08"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd4_h09" name="wd4_h09" {{ old('wd4_h09') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd4_h09"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd4_h10" name="wd4_h10" {{ old('wd4_h10') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd4_h10"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd4_h11" name="wd4_h11" {{ old('wd4_h11') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd4_h11"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd4_h12" name="wd4_h12" {{ old('wd4_h12') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd4_h12"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd4_h13" name="wd4_h13" {{ old('wd4_h13') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd4_h13"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd4_h14" name="wd4_h14" {{ old('wd4_h14') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd4_h14"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd4_h15" name="wd4_h15" {{ old('wd4_h15') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd4_h15"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd4_h16" name="wd4_h16" {{ old('wd4_h16') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd4_h16"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd4_h17" name="wd4_h17" {{ old('wd4_h17') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd4_h17"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd4_h18" name="wd4_h18" {{ old('wd4_h18') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd4_h18"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd4_h19" name="wd4_h19" {{ old('wd4_h19') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd4_h19"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd4_h20" name="wd4_h20" {{ old('wd4_h20') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd4_h20"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd4_h21" name="wd4_h21" {{ old('wd4_h21') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd4_h21"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd4_h22" name="wd4_h22" {{ old('wd4_h22') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd4_h22"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd4_h23" name="wd4_h23" {{ old('wd4_h23') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd4_h23"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd4_h24" name="wd4_h24" {{ old('wd4_h24') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd4_h24"></label>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="availabilityBox availabilityBoxDay">
                                                        <span>S</span>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd5_h08" name="wd5_h08" {{ old('wd5_h08') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd5_h08"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd5_h09" name="wd5_h09" {{ old('wd5_h09') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd5_h09"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd5_h10" name="wd5_h10" {{ old('wd5_h10') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd5_h10"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd5_h11" name="wd5_h11" {{ old('wd5_h11') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd5_h11"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd5_h12" name="wd5_h12" {{ old('wd5_h12') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd5_h12"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd5_h13" name="wd5_h13" {{ old('wd5_h13') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd5_h13"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd5_h14" name="wd5_h14" {{ old('wd5_h14') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd5_h14"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd5_h15" name="wd5_h15" {{ old('wd5_h15') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd5_h15"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd5_h16" name="wd5_h16" {{ old('wd5_h16') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd5_h16"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd5_h17" name="wd5_h17" {{ old('wd5_h17') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd5_h17"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd5_h18" name="wd5_h18" {{ old('wd5_h18') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd5_h18"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd5_h19" name="wd5_h19" {{ old('wd5_h19') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd5_h19"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd5_h20" name="wd5_h20" {{ old('wd5_h20') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd5_h20"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd5_h21" name="wd5_h21" {{ old('wd5_h21') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd5_h21"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd5_h22" name="wd5_h22" {{ old('wd5_h22') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd5_h22"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd5_h23" name="wd5_h23" {{ old('wd5_h23') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd5_h23"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd5_h24" name="wd5_h24" {{ old('wd5_h24') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd5_h24"></label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="availabilityBox availabilityBoxDay">
                                                        <span>S</span>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd6_h08" name="wd6_h08" {{ old('wd6_h08') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd6_h08"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd6_h09" name="wd6_h09" {{ old('wd6_h09') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd6_h09"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd6_h10" name="wd6_h10" {{ old('wd6_h10') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd6_h10"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd6_h11" name="wd6_h11" {{ old('wd6_h11') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd6_h11"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd6_h12" name="wd6_h12" {{ old('wd6_h12') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd6_h12"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd6_h13" name="wd6_h13" {{ old('wd6_h13') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd6_h13"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd6_h14" name="wd6_h14" {{ old('wd6_h14') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd6_h14"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd6_h15" name="wd6_h15" {{ old('wd6_h15') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd6_h15"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd6_h16" name="wd6_h16" {{ old('wd6_h16') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd6_h16"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd6_h17" name="wd6_h17" {{ old('wd6_h17') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd6_h17"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd6_h18" name="wd6_h18" {{ old('wd6_h18') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd6_h18"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd6_h19" name="wd6_h19" {{ old('wd6_h19') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd6_h19"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd6_h20" name="wd6_h20" {{ old('wd6_h20') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd6_h20"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd6_h21" name="wd6_h21" {{ old('wd6_h21') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd6_h21"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd6_h22" name="wd6_h22" {{ old('wd6_h22') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd6_h22"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd6_h23" name="wd6_h23" {{ old('wd6_h23') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd6_h23"></label>
                                                    </div>
                                                    <div class="availabilityBox availabilityBoxCheck">
                                                        <input type="checkbox" id="wd6_h24" name="wd6_h24" {{ old('wd6_h24') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                        <label for="wd6_h24"></label>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="field">
                                                <div class="col-lg-12  text-center normal-first">
                                                    <a class="scrollToTop collapsed submitBtn"
                                                    href="javascript:show_profile_photo()">CONTINUE</a>
                                                </div>
                                            </div>


                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion"
                                    data-target="#collapseSeven" aria-controls="collapseSeven">PROFILE PHOTO</a>
                                </h4>
                            </div>
                            <div id="collapseSeven" class="panel-collapse collapse ">
                                <div class="panel-body text-center">
                                    <div class="col-lg-10 col-lg-offset-1">
                                        <div class="normal-space">
                                            <div class="field fieldTitle">
                                                <div class="col-lg-12 text-left">
                                                    <label style="font-size: 14px;">Your Profile Photo is a picture of your face that every customer will see. . It is important that this photo clear and visible so the customer is able to identify that you are the correct groomer.
                                                    <ul class="profile-photo-details">
                                                        <li>- Full face and top of shoulders</li>
                                                        <li>- Good lighting</li>
                                                        <li>- Not blurry</li>
                                                    </ul>
                                                    </label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <img id="img_profile_photo" src="images/upload-img.jpg" alt="Upload profile photo"/>
                                                    <input type="file" id="profile_photo" name="profile_photo" value="{{ old('profile_photo') }}" required aria-required="true" style="visibility:hidden"/>
                                                    <div class="col-lg-12 flush"><a onclick="$('[name=profile_photo]').click()" class="btn-success upload">Upload
                                                            Images</a></div>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <div class="col-lg-12  text-center normal-first">
                                                    <a class="submitBtn" href="javascript:submit_form()">SUBMIT</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </form>


        </section> <!-- #benefits ends -->
    </div>
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
