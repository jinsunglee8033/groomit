@extends('user.layout.default')
@section('content')
    <link href="/desktop/css/appointment.css" rel="stylesheet" type="text/css">
    <link href="/desktop/css/my-account.css" rel="stylesheet" type="text/css">
    <style type="text/css">
        .my-card .media-object {
            width: 95px !important;
            height: 95px !important;
        }

        .selected-already {
            border-color:black !important;
            background-color: #efefef !important;
            opacity: 1 !important;
        }

        .size-mismatch {
            opacity: 0.5;
        }

    </style>
    <!-- PROGRESS -->
    <div class="container-fluid" id="progress-bar">
        <div class="row">
            <div class="col-xs-2 line-status complete"></div>
            <div class="col-xs-2 line-status complete"></div>
            <div class="col-xs-2 line-status"></div>
            <div class="col-xs-2 line-status"></div>
            <div class="col-xs-2 line-status"></div>
            <div class="col-xs-2 line-status"></div>
        </div>
        <!-- row -->
    </div>
    <!-- /progress-bar -->
    <div id="main">
        <!-- ALREADY A USER -->
        @if (!Auth::guard('user')->check())
            <section id="isUser">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-10 col-sm-offset-1">
                            <div class="row">
                                <div class="col-md-5 col-md-offset-1 col-xs-6 text-center">
                                    <h3>Already a user?</h3>
                                    <a href="javascript:show_login()" class="groomit-btn red-btn rounded-btn">SIGN IN</a> </div>
                                <div class="col-md-5 col-xs-6 text-center">
                                    <h3>New User?</h3>
                                    <a href="/user/sign-up" class="groomit-btn black-btn rounded-btn">SIGN UP (page)</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @else



        <!-- MY PETS -->
            <section id="my-pets">
                <div class="container">

                    <div class="row">
                        <div class="col-md-12">

                            <!-- <a href="#pet-condition-modal" role="button" class="btn btn-large btn-primary" data-toggle="modal">Launch Demo Modal</a> -->

                            <!-- PET CONDITION MODAL -->
                            <div id="pet-condition-modal" class="modal fade">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title text-center">PET CONDITION</h4>
                                        </div>
                                        <div class="modal-body">
                                            <p>Please check any of the following pertaining to your pet</p>

                                            <form  role="form" method="post">

                                                <div class="form-group">
                                                    <div class="checkbox col-xs-12 pl-0">
                                                        <label for="condition-01">
                                                            <input type="checkbox" name="pet-condition" id="condition-01" value="condition-01">
                                                            <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                                                            <span class="addon-name">Medical Condition</span> </label>
                                                        <!-- /collapse -->
                                                    </div>
                                                    <!-- /checkbox -->
                                                </div>

                                                <div class="form-group">
                                                    <div class="checkbox col-xs-12 pl-0">
                                                        <label for="condition-02">
                                                            <input type="checkbox" name="pet-condition" id="condition-02" value="condition-02">
                                                            <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                                                            <span class="addon-name">Senior Pet</span> </label>
                                                        <!-- /collapse -->
                                                    </div>
                                                    <!-- /checkbox -->
                                                </div>

                                                <div class="form-group">
                                                    <div class="checkbox col-xs-12 pl-0">
                                                        <label for="condition-03">
                                                            <input type="checkbox" name="pet-condition" id="condition-03" value="condition-03">
                                                            <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                                                            <span class="addon-name">Matted Pet</span> </label>
                                                        <!-- /collapse -->
                                                    </div>
                                                    <!-- /checkbox -->
                                                </div>

                                                <div class="form-group">
                                                    <div class="checkbox col-xs-12 pl-0">
                                                        <label for="condition-04">
                                                            <input type="checkbox" name="pet-condition" id="condition-04" value="condition-04">
                                                            <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                                                            <span class="addon-name">Sassy/Anxious Pet</span> </label>
                                                        <!-- /collapse -->
                                                    </div>
                                                    <!-- /checkbox -->
                                                </div>

                                                <div class="form-group other-form">
                                                    <div class="checkbox col-xs-12 pl-0">
                                                        <label for="condition-05">
                                                            <input type="checkbox" name="pet-condition" id="condition-05" value="condition-05">
                                                            <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                                                            <span class="addon-name">Other</span>
                                                        </label>
                                                        <!-- /collapse -->
                                                    </div>
                                                    <!-- /checkbox -->
                                                </div>

                                                <div class="form-group other-condition-in">
                                                    <div class="checkbox col-xs-12 pl-0">
                                                        <label for="condition-055">
                                                            <input class="other-condition" type="text"></input>
                                                        </label>
                                                        <!-- /collapse -->
                                                    </div>
                                                    <!-- /checkbox -->
                                                </div>

                                                <div class="form-group">
                                                    <div class="checkbox col-xs-12 pl-0">
                                                        <label for="condition-06">
                                                            <input type="checkbox" name="pet-condition" id="condition-06" value="condition-06">
                                                            <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                                                            <span class="addon-name">None of the above</span> </label>
                                                        <!-- /collapse -->
                                                    </div>
                                                    <!-- /checkbox -->
                                                </div>


                                            </form>
                                        </div>
                                        <div class="modal-footer">

                                            <div class="col-md-12 text-center">
                                                <a href="#/" class="groomit-btn rounded-btn black-btn big-btn space-btn">OMIT</a>
                                                <a class="groomit-btn rounded-btn red-btn big-btn" href="#/">SUBMIT</a>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 text-center">
                            <h3><span class="title-icon"><img
                                            src="/desktop/img/{{ Schedule::getCurrentPetType() }}-icon.svg"
                                            width="50"
                                            alt="My Pets"></span> MY {{ strtoupper(Schedule::getCurrentPetType()) }}S</h3>
                        </div>
                    </div>
                    <!-- /row -->
                    <div class="row after-title">

                        <!-- CARDS -->
                        @if (count($pets) > 0)
                            @foreach ($pets as $o)
                                <div class="col-lg-3 col-md-4 col-sm-6 card-wrapper">
                                    <div class="media my-card {{ $o->size != Schedule::getCurrentSize() ? 'size-mismatch' : '' }} {{ Schedule::petSelected($o->pet_id) ? 'selected-already' : '' }} {{ Schedule::getCurrentPetId() == $o->pet_id ? 'selected' : '' }}"
                                         onclick="select_pet('{{ $o->pet_id }}', '{{ $o->size }}', '{{ Schedule::petSelected($o->pet_id) ? 'Y' : 'N' }}', event)" id="div_pet_{{ $o->pet_id }}">
                                        <div class="media-left"
                                             style="widht:105px; height:96.72px;">
                                            @if (!empty($o->photo)) <img
                                                    class="media-object img-circle"
                                                    src="data:image/png;base64,{{ $o->photo }}" width="95"
                                                    height="95" alt="pet" {{ $o->size != Schedule::getCurrentSize() ? 'disabled' : '' }}>
                                            @else
                                            <!-- pet avatar -->
                                                <div class="bh-pet-avatar media-object">
                                                    <div class="table-cell media-middle">
                                                        @if (Schedule::getCurrentPetType() == "dog")
                                                            <img src="/desktop/img/dog-icon.svg" width="60"
                                                                 alt="my dog" {{ $o->size != Schedule::getCurrentSize() ? 'disabled' : '' }}>
                                                        @else <img src="/desktop/img/cat-icon.svg"
                                                                   width="64"
                                                                   alt="my cat" {{ $o->size != Schedule::getCurrentSize() ? 'disabled' : '' }}>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="media-body media-middle">
                                            <div class="display-table">
                                                <div class="table-cell">
                                                    <h4 class="media-heading">{{ strtoupper($o->name) }}</h4>
                                                    <p>{{ $o->age }} years old</p>
                                                </div>
                                                <div class="table-cell media-middle text-right" onclick="show_pet(event, '{{ $o->pet_id }}')"><a
                                                            {{ $o->size != Schedule::getCurrentSize() ? 'disabled' : '' }}> <img src="/desktop/img/ionicons_2-0-1_ios-information-outline_48_0_bd1a2a_none.png" width="22" height="22" alt="More Info" /></a></div>
                                            </div>
                                            <!-- /row -->
                                        </div>
                                        <!-- /media-body -->
                                    </div>
                                    <!-- /my-dog-card -->
                                </div>
                                <!-- /col-3 -->
                            @endforeach
                        @endif
                        <div class="col-lg-3 col-md-4 col-sm-6 card-wrapper" id="new-pet">
                            <div class="media my-card" id="add-new-card" onclick="show_pet(event)">
                                <div class="media-body media-middle">
                                    <div class="display-table">
                                        <div class="table-cell media-middle">
                                            <div class="media-left media-middle"><span class="glyphicon glyphicon-plus-sign"
                                                                                       aria-hidden="true"></span></div>
                                        </div>
                                        <div class="table-cell media-middle">
                                            <h4 class="media-heading text-left">ADD A NEW<br>
                                                {{ strtoupper(Schedule::getCurrentPetType()) }} PROFILE</h4>
                                        </div>
                                    </div>
                                </div>
                                <!-- /media-body -->

                            </div>
                            <!-- /my-dog-card -->
                        </div>
                        <!-- /col-3 -->

                    </div>
                    <!-- /row -->
                    <div class="row">
                        <div class="col-sm-6 col-sm-offset-3 card-wrapper">
                            <div class="media my-card add-another" id="add-pet-appointment"><a href="javascript:add_another_pet(this)">
                                    <div class="media-body media-middle">
                                        <div class="display-table">
                                            <div class="table-cell media-middle">
                                                <div class="media-left media-middle"><span
                                                            class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
                                                </div>
                                            </div>
                                            <div class="table-cell media-middle">
                                                <h4 class="media-heading text-left">Add
                                                    another {{ Schedule::getCurrentPetType() }} to this appointment</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /media-body -->
                                </a></div>
                            <!-- /my-dog-card -->

                        </div>
                        <!-- /col -->
                    </div>
                    <!-- /row -->
                </div>
                <!-- /container -->
            </section>
            <!-- /my-pets -->

            <section id="next-btn">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 text-center"><a href="/user/schedule/select-addon"
                                                              class="groomit-btn rounded-btn black-btn big-btn space-btn"><img class="arrow-back" src="/desktop/img/arrow-back.png" width="14" height="18" alt="Back" /> BACK &nbsp;</a>
                            <button type="button" id="btn_continue" style="display:none" class="groomit-btn rounded-btn red-btn big-btn" onclick="go_to_next()">
                                CONTINUE
                            </button>
                        </div>
                    </div>
                    <!-- row -->
                </div>
                <!-- container -->
            </section>

        @endif
    </div>
    <!-- /main -->

    <!-- MODALS -->

    <!-- EDIT PET -->
    <div class="modal fade" id="modal-pet" tabindex="-1" role="dialog" aria-labelledby="editPetLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h4 class="modal-title text-center"
                        id="editPetLabel">{{ strtoupper(Schedule::getCurrentPetType()) }}
                        PROFILE</h4>

                    <!-- INIT FORM -->
                    <form action="/user/pet/add" role="form" method="post" id="frm_pet" class="form-profile-info"
                          enctype="multipart/form-data" target="ifm_upload">
                        {!! csrf_field() !!}
                        <input type="hidden" name="pet_id" id="pet_id"/>
                        <input type="hidden" name="type" value="{{ Schedule::getCurrentPetType() }}"/>
                        <fieldset>
                            <!-- PROFILE -->
                            <section id="profile">
                                <div class="container" class="st-opt">
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="pet-photo">
                                                <!-- NO PHOTO - AVATAR IS SHOWN -->
                                                <!-- required "demo-photo" class -->
                                                <img id="img_pet_photo" class="demo-photo"
                                                     src="/desktop/img/{{ Schedule::getCurrentPetType() }}-icon.svg"
                                                     width="150">

                                                <!-- USER UPLOADED PHOTO -->
                                                <!-- remove "demo-photo" class -->
                                                <!--<img src="/desktop/img/demo.jpg" width="221" height="225">-->
                                                <input type="file" id="pet_photo" name="pet_photo" style="display:none;"
                                                       onchange="read_file(this)"/>
                                                <div id="add-photo" onclick="$('#pet_photo').click();" disabled="true"
                                                     aria-disabled="true"><span
                                                            class="glyphicon glyphicon-camera center-block"
                                                            aria-hidden="true"></span>Add photo
                                                </div>
                                            </div>
                                        </div>
                                        <!-- /col-3 -->
                                        <div class="col-md-8" id="pet-binfo">
                                            <div class="row mb-4">
                                                <div class="col-xs-6">
                                                    <div class="form-group">
                                                        <label class="control-label"
                                                               for="">{{ ucfirst(Schedule::getCurrentPetType()) }}'s
                                                            Name </label>
                                                        <input class="form-control" name="name" id="name" type="text"
                                                               maxlenght="100"
                                                               required/>
                                                        <span class="form-highlight"></span> <span
                                                                class="form-bar"></span>

                                                    </div>
                                                    <!-- /form-group -->
                                                </div>
                                                <!-- /col-6 -->
                                                <div class="col-xs-6">
                                                    <div class="form-group select-form-group">
                                                        <label class="control-label"
                                                               for="">{{ ucfirst(Schedule::getCurrentPetType()) }}'s
                                                            Age </label>
                                                        <select class="form-control" name="age" id="age" required>
                                                            <option value="">Please Select</option>
                                                            <option value="1">1 year or less</option>
                                                            <option value="2">2 years old</option>
                                                            <option value="3">3 years old</option>
                                                            <option value="4">4 years old</option>
                                                            <option value="5">5 years old</option>
                                                            <option value="6">6 years old</option>
                                                            <option value="7">7 years old</option>
                                                            <option value="8">8 years old</option>
                                                            <option value="9">9 years old</option>
                                                            <option value="10">10 years old</option>
                                                            <option value="11">11 years old</option>
                                                            <option value="12">12 years old</option>
                                                            <option value="13">13 years old</option>
                                                            <option value="14">14 years old</option>
                                                            <option value="15">15 years old</option>
                                                            <option value="+15">+15 years old</option>
                                                        </select>

                                                    </div>
                                                    <!-- /form-group -->
                                                </div>
                                                <!-- /col-6 -->
                                            </div>
                                            <!-- /row -->

                                            <div class="row">
                                                <div class="col-sm-6 mb-4">
                                                    <p class="control-label">Gender </p>
                                                    <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                        <label class="btn btn-st-opt flex-fill">
                                                            <input type="radio" name="gender" autocomplete="off"
                                                                   value="F">
                                                            Female</label>
                                                        <label class="btn btn-st-opt flex-fill">
                                                            <input type="radio" name="gender" autocomplete="off"
                                                                   value="M">
                                                            Male</label>
                                                    </div>
                                                </div>
                                                <!-- /col-6 -->
                                                @if (Schedule::getCurrentPetType() == 'dog')
                                                    <div class="col-sm-6">
                                                        <div class="form-group select-form-group">
                                                            <label class="control-label" for="breed">Dog's breed </label>
                                                            <select class="form-control" name="breed" id="breed"
                                                                    required>
                                                                <option value="">Please Select</option>
                                                                @if (count($breeds) > 0)
                                                                    @foreach ($breeds as $o)
                                                                        <option value="{{ $o->breed_id }}">{{ $o->breed_name }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <!-- /form-group -->
                                                    </div>
                                            @endif
                                            <!-- /col-6 -->
                                            </div>
                                            <!-- /row -->

                                        </div>
                                        <!-- /col-10 -->
                                    </div>
                                    <!-- /row -->
                                    @if (Schedule::getCurrentPetType() == 'dog')
                                        <div class="row mb-4">
                                            <div class="col-xs-12">
                                                <p class="control-label" for="">Size &amp; weight </p>
                                                <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                    <label class="btn btn-st-opt flex-fill">
                                                        <input type="radio" name="size" value="2" autocomplete="off">
                                                        S <20lbs</label>
                                                    <label class="btn btn-st-opt flex-fill">
                                                        <input type="radio" name="size" value="3" autocomplete="off">
                                                        M 21~40lbs</label>
                                                    <label class="btn btn-st-opt flex-fill">
                                                        <input type="radio" name="size" value="4" autocomplete="off">
                                                        L 41~80lbs</label>
                                                    <label class="btn btn-st-opt flex-fill">
                                                        <input type="radio" name="size" value="5" autocomplete="off">
                                                        XL >81lbs</label>
                                                </div>
                                            </div>
                                            <!-- /col -->
                                        </div>
                                    @endif
                                <!-- /row -->
                                    <div class="row mb-4">
                                        <div class="col-xs-12">
                                            <p class="control-label" for="">Temperament</p>
                                            <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="temperment" autocomplete="off"
                                                           value="Friendly">
                                                    Friendly</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="temperment" autocomplete="off"
                                                           value="Anxious">
                                                    Anxious</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="temperment" autocomplete="off"
                                                           value="Fatigue">
                                                    Fatigue</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="temperment" autocomplete="off"
                                                           value="Aggressive">
                                                    Aggressive</label>
                                            </div>
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <!-- /row -->







                                    @if (Schedule::getCurrentPetType() == 'cat')
                                        <div class="row">
                                            <div class="col-sm-6 mb-4">
                                                <p class="control-label">Vaccinated? </p>
                                                <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                    <label class="btn btn-st-opt flex-fill">
                                                        <input type="radio" name="vaccinated" autocomplete="off" checked value="Y">
                                                        Yes</label>
                                                    <label class="btn btn-st-opt flex-fill">
                                                        <input type="radio" name="vaccinated" autocomplete="off" value="N">
                                                        No</label>
                                                </div>
                                            </div>
                                            <!-- /col -->
                                            <div class="col-sm-6 mb-4">
                                                <div class="form-group">
                                                    <label class="control-label" for="">Vaccination Certificate</label><br>
                                                    <input type="file" name="upload_certificate" id="upload_certificate" value="" >

                                                    <a href="#/" class="w-100 vaccination_certificate_upload upload_input btn outline-btn rounded-btn groomit-btn table-cell align-middle" role="button" id="login_pg_register_button">
                                                        Upload
                                                    </a>
                                                </div>
                                                <!-- /form-group -->
                                            </div>
                                            <!-- /col -->
                                        </div>
                                    @endif

                                    @if (Schedule::getCurrentPetType() == 'dog')
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <p class="control-label">Is your dog up-to-date with rabies vaccination?</p>
                                            </div>
                                            <div class="col-sm-6 mb-3">
                                                <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                    <label class="btn btn-st-opt flex-fill">
                                                        <input type="radio" name="vaccinated" autocomplete="off" value="Y">
                                                        Yes</label>
                                                    <label class="btn btn-st-opt flex-fill">
                                                        <input type="radio" name="vaccinated" autocomplete="off" value="N">
                                                        No</label>
                                                </div>
                                            </div>
                                            <!-- /col -->

                                            <div class="col-sm-6 mb-4">
                                                <div class="form-group">
                                                    <input type="file" name="upload_certificate" id="upload_certificate" value="" >
                                                    <a href="#/" class="w-100 vaccination_certificate_upload upload_input btn outline-btn rounded-btn groomit-btn table-cell align-middle" role="button" id="login_pg_register_button">
                                                        Upload Certificate
                                                    </a>
                                                </div>
                                                <!-- /form-group -->
                                            </div>
                                            <!-- /col -->
                                        </div>
                                    @endif


                                    <div class="row mb-4">
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label class="control-label" for="vet">Vet's name (optional)</label>
                                                <input class="form-control" name="vet" id="vet" type="text" maxlenght="100"/>
                                                <span class="form-highlight"></span> <span
                                                        class="form-bar"></span>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col -->
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label class="control-label" for="vet_phone">Vet's phone (optional)</label>
                                                <input class="form-control" name="vet_phone" id="vet_phone" type="text" maxlenght="30"/>
                                                <span class="form-highlight"></span> <span
                                                        class="form-bar"></span>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col -->
                                    </div>
                                    <!-- /row -->



                                    @if (Schedule::getCurrentPetType() == 'dog')
                                        <div class="row mb-4">
                                            <div class="col-xs-12">
                                                <p class="control-label" for="">Last Groom</p>
                                                <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                    <label class="btn btn-st-opt flex-fill">
                                                        <input type="radio" name="last_groom" autocomplete="off"
                                                               value="< 6 weeks">
                                                        <6 weeks</label>
                                                    <label class="btn btn-st-opt flex-fill">
                                                        <input type="radio" name="last_groom" autocomplete="off"
                                                               value="<6 months">
                                                        <6 months</label>
                                                    <label class="btn btn-st-opt flex-fill">
                                                        <input type="radio" name="last_groom" autocomplete="off"
                                                               value=">6 months">
                                                        >6 months</label>
                                                    <label class="btn btn-st-opt flex-fill">
                                                        <input type="radio" name="last_groom" autocomplete="off"
                                                               value="Never">
                                                        Never</label>
                                                </div>
                                            </div>
                                            <!-- /col -->
                                        </div>
                                        <!-- /row -->
                                        <div class="row mb-4">
                                            <div class="col-xs-12">
                                                <p class="control-label" for="">Coat Type</p>
                                                <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                    <label class="btn btn-st-opt flex-fill">
                                                        <input type="radio" name="coat_type" autocomplete="off"
                                                               value="Silky">
                                                        Silky</label>
                                                    <label class="btn btn-st-opt flex-fill">
                                                        <input type="radio" name="coat_type" autocomplete="off"
                                                               value="Wiry">
                                                        Wiry</label>
                                                    <label class="btn btn-st-opt flex-fill">
                                                        <input type="radio" name="coat_type" autocomplete="off"
                                                               value="Double Coat">
                                                        Double Coat</label>
                                                    <label class="btn btn-st-opt flex-fill">
                                                        <input type="radio" name="coat_type" autocomplete="off"
                                                               value="Curly">
                                                        Curly</label>
                                                </div>
                                            </div>
                                            <!-- /col-6 -->
                                        </div>
                                        <!-- /row -->
                                    @endif
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="form-group">
                                                <label class="control-label" for="">Notes to groomer (optional)</label>
                                                <textarea class="form-control" name="special_note" id="special_note" maxlength="250" rows="4"
                                                          placeholder="Allergies, Health Conditions, etc."></textarea>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                    </div>
                                    <!-- /row -->
                                </div>
                                <!-- /container -->
                            </section>
                            <!-- /other-addons -->

                            <section id="next-btn">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-xs-6 text-right" id="btn_cancel" style="display:none">
                                            <button type="button" class="groomit-btn rounded-btn black-btn space-btn" onclick="change_mode('view')">
                                                CANCEL
                                            </button>
                                        </div>
                                        <div class="col-xs-6 text-right" id="btn_close" style="display:none">
                                            <button type="button" class="groomit-btn black-btn rounded-btn space-btn" data-dismiss="modal" aria-label="Close" >CLOSE
                                            </button>
                                        </div>
                                        <div class="col-xs-6 text-left">
                                            <button type="button" class="groomit-btn rounded-btn red-btn " id="btn_edit" style="display:none" onclick="change_mode('edit')">
                                                EDIT
                                            </button>
                                            <button type="submit" class="groomit-btn rounded-btn red-btn " id="btn_submit" style="display:none">
                                                SUBMIT
                                            </button>
                                        </div>
                                    </div>
                                    <!-- row -->
                                </div>
                                <!-- container -->
                            </section>
                        </fieldset>
                    </form>

                    <!-- /FORM -->

                </div>
                <!-- /modal-body -->

            </div>
            <!-- /modal-content -->

        </div>
    </div>
    <!-- /editPetModal -->
    <!-- USER PROFILE -->
    <!-- USER PROFILE -->
    <div class="modal fade" id="editMyProfile1" tabindex="-1" role="dialog" aria-labelledby="editMyProfileLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <style type="text/css">
                    .material-form .form-group {
                        margin-bottom: 0;
                        padding-bottom: 20px;
                        margin-top: 35px;
                        position: relative;
                    }
                    .material-form .form-control[disabled], .material-form .form-control[readonly], .material-form fieldset[disabled] .form-control {
                        background-color: transparent;
                        border-bottom-style: dashed;
                    }
                    .material-form .form-control {
                        display: block;
                        height: 62px;
                        width: 100%;
                        border: none;
                        border-radius: 0 !important;
                        font-size: 14px;
                        font-weight: 300;
                        padding: 0;
                        background-color: transparent;
                        box-shadow: none;
                        border-bottom: 1px solid #cacaca;
                    }
                    .form-highlight {
                        position: absolute;
                        height: 25%;
                        width: 60px;
                        top: 25%;
                        left: 0;
                        pointer-events: none;
                        opacity: 0.4;
                    }
                    .form-bar {
                        position: relative;
                        display: block;
                        width: 100%;
                    }
                    .form-bar:before {
                        left: 50%;
                    }
                    .form-bar:after {
                        right: 50%;
                    }
                    .form-bar:before, .form-bar:after {
                        content: '';
                        height: 1px;
                        width: 0;
                        bottom: 0;
                        position: absolute;
                        transition: 0.3s ease all;
                        -moz-transition: 0.3s ease all;
                        -webkit-transition: 0.3s ease all;
                    }
                    .material-form label:not(.btn-st-opt) {
                        position: absolute;
                        top: -18px;
                        color: #999;
                        font-size: 12px;
                        font-weight: 300;
                        transition: 0.2s ease all;
                        -moz-transition: 0.2s ease all;
                        -webkit-transition: 0.2s ease all;
                    }
                    .material-form label:not(.btn-st-opt) {
                        top: -31px;
                    }

                </style>
                <div class="modal-body" id="view-profile">
                    <h4 class="modal-title text-center" id="editMyProfileLabel">MY PROFILE</h4>

                    <!-- PROFILE -->
                    <section id="profile">
                        <div class="container" class="st-opt">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="my-photo">
                                        <!-- DEFAULT PROFILE PHOTO -->
                                        <!-- required "demo-photo" class -->
                                        <img id="photo" class="demo-photo" src="/desktop/img/my-profile.png" alt="Profile Avatar" width="221" height="221">
                                        <!--<img class="demo-photo" src="img/cat-profile.png" width="237" height="194">-->
                                        <!-- CUSTOM PROFILE PHOTO -->
                                        <!-- remove "demo-photo" class -->
                                        <!--<img src="">-->
                                        <input type="file" id="user_photo" name="pet_photo" style="display:none;" onchange="read_file_for_profile(this)"/>
                                        <div id="add-photo" onclick="$('#editMyProfile1').find('#user_photo').click();"><span class="glyphicon glyphicon-camera center-block" aria-hidden="true"></span>Add photo</div>
                                    </div>
                                    <div class="text-center">
                                        <button type="button" class="groomit-btn rounded-btn outline-btn black-btn switch-content" data-content="change-password">RESET PASSWORD</button>
                                    </div>
                                </div>
                                <!-- /col-3 -->
                                <div class="col-md-8" id="pet-binfo">
                                    <div class="row mb-4">
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <input class="form-control" id="first_name" type="text" maxlength="100" required />
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                <label class="control-label" for="">FIRST NAME <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col-6 -->
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <input class="form-control" id="last_name" type="text" maxlength="100" required />
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                <label class="control-label" for="">LAST NAME <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <!-- /row -->

                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <input class="form-control" id="phone" type="text" maxlength="10" required />
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                <label class="control-label" for="">PHONE NUMBER <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                    </div>
                                    <!-- /row -->

                                </div>
                                <!-- /col-10 -->
                            </div>
                            <!-- /row -->

                        </div>
                        <!-- /container -->
                    </section>
                    <!-- /other-addons -->

                    <section id="next-btn">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <button data-dismiss="modal" aria-label="Close" type="button" class="groomit-btn rounded-btn black-btn long-btn space-btn">CLOSE</button>
                                    <!--<button type="button" class="groomit-btn rounded-btn black-btn long-btn space-btn">CANCEL</button>-->
                                    <!--<button type="submit" class="groomit-btn rounded-btn red-btn long-btn">CONFIRM</button>-->
                                    <button type="button" class="groomit-btn rounded-btn red-btn long-btn" onclick="update_user_profile_temp()">SUBMIT</button>
                                </div>
                            </div>
                            <!-- row -->
                        </div>
                        <!-- container -->
                    </section>
                </div>
                <!-- /modal-body -->

                <!-- CHANGE PASSWORD -->
                <div class="modal-body hidden" id="change-password">
                    <h4 class="modal-title text-center" id="editMyProfileLabel">MY PROFILE</h4>

                    <!-- PROFILE -->
                    <section id="profile">
                        <div class="container" class="st-opt">
                            <div class="row mb-4">

                                <div class="col-md-10 col-md-offset-1" id="pet-binfo">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <input class="form-control" id="current_password" type="password" maxlength="100" required />
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                <label class="control-label" for="current_password">CURRENT PASSWORD <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <!-- /row -->

                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <input class="form-control" id="new_password" type="password" maxlength="100" required />
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                <label class="control-label" for="new_password">NEW PASSWORD <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col-6 -->
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <input class="form-control" id="new_password_confirmation" type="password" maxlength="100" required />
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                <label class="control-label" for="new_password_confirmation">CONFIRM PASSWORD <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <!-- /row -->

                                </div>
                                <!-- /col-10 -->
                            </div>
                            <!-- /row -->

                        </div>
                        <!-- /container -->
                    </section>
                    <!-- /other-addons -->

                    <section id="next-btn">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <button type="button" class="groomit-btn rounded-btn black-btn long-btn space-btn switch-content" data-content="view-profile">GO BACK</button>
                                    <button type="button" class="groomit-btn rounded-btn red-btn long-btn" onclick="reset_user_password()">CONFIRM</button>
                                </div>
                            </div>
                            <!-- row -->
                        </div>
                        <!-- container -->
                    </section>



                </div>
                <!-- /modal-body -->

            </div>
            <!-- /modal-content -->

        </div>
    </div>

    <iframe id="ifm_upload" name="ifm_upload" style="display:none;"></iframe>
    <form id="frm_submit" method="post" action="/user/schedule/select-pet/post">
        {!! csrf_field() !!}
        <input type="hidden" id="selected_pet_id" name="pet_id" value="{{ Schedule::getCurrentPetId() }}"/>
        <input type="hidden" id="add_another_pet" name="add_another_pet" value="N"/>
    </form>


    <script type="text/javascript">
        $("body").on('click', '.vaccination_certificate_upload', function() {
            $('#upload_certificate').trigger('click');
        });

        function add_another_pet() {
            $('#add_another_pet').val('Y');
            go_to_next();
        }

        function select_pet(pet_id, size_id, already_selected) {

            // commenting out below allows changing package of already selected pet
            /*if (already_selected === 'Y') {
                return;
            }*/


            var current_size = '{{ Schedule::getCurrentSize() }}';
            if (current_size !== size_id) {
                myApp.showError('The SIZE of package you selected, does not match with the size of your pet.<br/>' +
                    'Please check the size again, or add/update your pet.');
                return;
            }

            $('#selected_pet_id').val(pet_id);
            $('#btn_continue').show();

            $('.my-card').removeClass('selected');
            $('#div_pet_' + pet_id).addClass('selected');


            $('#add-pet-appointment').show();
            $('#add-new-card').hide();
        }

        function go_to_next() {
            var pet_id = $('#selected_pet_id').val();
            if (pet_id === '') {
                myApp.showError('Please select pet first!');
                $('#add_another_pet').val('N');
                return;
            }

            $('#frm_submit').submit();

        }

        function show_pet(e, pet_id) {
            if (!e) var e = window.event;
            if ('bubbles' in e) {
                if (e.bubbles) {
                    e.stopPropagation();
                }
            } else {
                e.cancelBubble = true;
                if (e.stopPropagation) e.stopPropagation();
            }

            var mode = typeof pet_id === "undefined" ? "add" : "update";
            if (mode === "add") {
                $('#pet_id').val('');
                $('#img_pet_photo').prop('src', '/desktop/img/{{ Schedule::getCurrentPetType() }}-icon.svg');
                $('#name').val('');
                $('#age').val('');

                $('[name="gender"][value!=""]').prop('checked', false);
                $('[name="gender"][value!=""]').parent().removeClass('active');

                $('#breed').val('');

                $('[name="size"][value!=""]').prop('checked', false);
                $('[name="size"][value!=""]').parent().removeClass('active');
                var size = '{{ Schedule::getCurrentSize() }}';
                if ($.trim(size) !== '') {
                    $('[name="size"][value="' + size + '"]').prop('checked', true);
                    $('[name="size"][value="' + size + '"]').parent().addClass('active');
                }

                $('[name="temperment"][value!=""]').prop('checked', false);
                $('[name="temperment"][value!=""]').parent().removeClass('active');

                $('[name="vaccinated"][value!=""]').prop('checked', false);
                $('[name="vaccinated"][value!=""]').parent().removeClass('active');

                $('#vet').val('');
                $('#vet_phone').val('');
                $('#special_note').val('');

                change_mode('edit');

                $('#frm_pet').prop('action', '/user/pet/add');
                $('#modal-pet').modal();
            } else {
                $('#pet_id').val(pet_id);
                $('#frm_pet').prop('action', '/user/pet/update');
                change_mode('view');
                load_pet(pet_id);
            }


        }

        function read_file(input) {

            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#img_pet_photo').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        function close_modal() {
            $('#modal-pet').modal('hide');
            window.location.reload();
        }

        function change_mode(mode) {
            if (mode === 'view') {
                $('#frm_pet input,select,textarea,label,div').prop('disabled', true);
                $('#frm_pet input,select,textarea,label,div').prop('aria-disabled', true);
                $('#btn_edit').show();
                $('#btn_submit').hide();
                $('#btn_cancel').hide();
                $('#btn_close').show();

                $('#frm_pet label').addClass('disabled');
                $('.vaccination_certificate_upload').addClass('disabled');
            } else {
                $('#frm_pet input,select,textarea,label,div').prop('disabled', false);
                $('#frm_pet input,select,textarea,label,div').prop('aria-disabled', false);
                $('#btn_edit').hide();
                $('#btn_submit').show();
                $('#btn_cancel').show();
                $('#btn_close').hide();

                $('#frm_pet label').removeClass('disabled');
                $('.vaccination_certificate_upload').removeClass('disabled');

            }
        }

        function load_pet(pet_id) {

            $.ajax({
                url: '/user/pet/load',
                data: {
                    _token: '{!! csrf_token() !!}',
                    pet_id: pet_id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    if ($.trim(res.msg) === '') {

                        var o = res.data;
                        //console.log( 'photo' + o.photo);
                        //console.log( 'photo_enc' + o.photo_enc);
                        if (o.photo ) {
                            $('#img_pet_photo').prop('src', 'data:image/png;base64,' + o.photo);
                            $('#img_pet_photo').removeClass("demo-photo");
                        } else {
                            $('#img_pet_photo').prop('src', '/desktop/img/{{ Schedule::getCurrentPetType() }}-icon.svg');
                            $('#img_pet_photo').addClass("demo-photo");
                        }
                        $('#name').val(o.name);
                        $('#age').val(o.age);

                        $('[name="gender"][value="' + o.gender + '"]').prop('checked', true);
                        $('[name="gender"][value!="' + o.gender + '"]').parent().removeClass('active');
                        $('[name="gender"][value="' + o.gender + '"]').parent().addClass('active');

                        $('#breed').val(o.breed);

                        $('[name="size"][value="' + o.size + '"]').prop('checked', true);
                        $('[name="size"][value!="' + o.size + '"]').parent().removeClass('active');
                        $('[name="size"][value="' + o.size + '"]').parent().addClass('active');

                        $('[name="temperment"][value="' + o.temperament + '"]').prop('checked', true);
                        $('[name="temperment"][value!="' + o.temperament + '"]').parent().removeClass('active');
                        $('[name="temperment"][value="' + o.temperament + '"]').parent().addClass('active');

                        $('[name="vaccinated"][value="' + o.vaccinated + '"]').prop('checked', true);
                        $('[name="vaccinated"][value!="' + o.vaccinated + '"]').parent().removeClass('active');
                        $('[name="vaccinated"][value="' + o.vaccinated + '"]').parent().addClass('active');

                        $('#vet').val(o.vet);
                        $('#vet_phone').val(o.vet_phone);

                        $('[name="last_groom"][value="' + o.last_groom + '"]').prop('checked', true);
                        $('[name="last_groom"][value!="' + o.last_groom + '"]').parent().removeClass('active');
                        $('[name="last_groom"][value="' + o.last_groom + '"]').parent().addClass('active');

                        $('[name="coat_type"][value="' + o.coat_type + '"]').prop('checked', true);
                        $('[name="coat_type"][value!="' + o.coat_type + '"]').parent().removeClass('active');
                        $('[name="coat_type"][value="' + o.coat_type + '"]').parent().addClass('active');

                        $('#special_note').val(o.special_note);

                        $('#modal-pet').modal();
                    } else {
                        //console.log( 'fail resposne:*******:' + res.msg);
                        myApp.showError(res.msg);
                    }
                }
            });
        }

                @if (Auth::guard('user')->check())
        var onload_func = window.onload;
        window.onload = function() {
            if (onload_func) {
                onload_func();
            }
            // For Users have empty Phone number
            show_user_profile_temp();
        }
        @endif

        // For Users have empty Phone number
        function show_user_profile_temp() {
            myApp.showLoading();
            $.ajax({
                url: '/user/profile/load_temp',
                data: {
                    _token: '{!! csrf_token() !!}'
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        $('#editMyProfile1').find('#first_name').val(res.first_name);
                        $('#editMyProfile1').find('#last_name').val(res.last_name);
                        $('#editMyProfile1').find('#phone').val(res.phone);
                        if (res.photo !== "") {
                            $('#editMyProfile1').find('#photo').prop('src', 'data:image/png;base64,' + res.photo);
                        }
                        $('#editMyProfile1').modal('show');
                    } else {

                    }
                }
            });
        }

        function update_user_profile_temp() {
            var data = new FormData();
            data.append('_token', '{!! csrf_token() !!}');
            data.append('first_name', $('#editMyProfile1').find('#first_name').val());
            data.append('last_name', $('#editMyProfile1').find('#last_name').val());
            data.append('phone', $('#editMyProfile1').find('#phone').val());
            if ($('#editMyProfile1').find('#user_photo')[0].files.length > 0) {
                data.append('photo', $('#editMyProfile1').find('#user_photo')[0].files[0]);
            }
            $('#editMyProfile1').modal('hide');
            myApp.showLoading();
            $.ajax({
                url: '/user/profile/update',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        myApp.showSuccess('Your request has been processed successfully!', function() {

                        });
                    } else {

                    }
                }
            });
        }

    </script>
@stop
