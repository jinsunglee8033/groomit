@extends('user.layout.default')
@section('content')
    <link href="/desktop/css/appointment.css?v=1.0.2" rel="stylesheet" type="text/css">
    <link href="/desktop/css/my-account.css" rel="stylesheet" type="text/css">

    <style type="text/css">
        .my-card .media-object {
            width: 95px !important;
            height: 95px !important;
        }
    </style>
    <div id="main">
        <!-- MY PETS -->
        <section id="my-pets">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h3><span class="title-icon"><img src="/desktop/img/dog-icon.svg" width="48"
                                                          alt="My Dogs"></span> MY DOGS</h3>
                    </div>
                </div>
                <!-- /row -->
                <div class="row after-title">

                @if (count($dogs) > 0)
                    @foreach ($dogs as $o)
                        <!-- CARDS -->
                            <div class="col-lg-3 col-md-4 col-sm-6 card-wrapper">
                                <div class="media my-card">
                                    <div class="media-left">
                                    <!--@if (!empty($o->photo))
                                        <img class="media-object img-circle"
                                             src="data:image/png;base64,{{ $o->photo }}" width="95px"
                                                 height="96.72px" alt="{{ $o->name }}">
                                        @else
                                        <img class="media-object img-circle"
                                             src="/desktop/img/demo.jpg" width="95px"
                                             height="96.72px" alt="{{ $o->name }}">
                                        @endif-->
                                            @if (!empty($o->photo))
                                                <img class="media-object img-circle"
                                                     src="data:image/png;base64,{{ $o->photo }}" width="95"
                                                     height="95" alt="{{ $o->name }}">


                                            @else
                                            <!-- pet avatar -->
                                                <div class="bh-pet-avatar media-object">
                                                    <div class="table-cell media-middle">
                                                        <img src="/desktop/img/dog-icon.svg" width="60" alt="{{ $o->name }}">
                                                    </div>

                                                </div>
                                            @endif
                                    </div>
                                    <div class="media-body media-middle">
                                        <div class="display-table">
                                            <div class="table-cell">
                                                <h4 class="media-heading">{{ $o->name }}</h4>
                                                <p>{{ $o->age }} old</p>
                                            </div>
                                            <div class="table-cell media-middle text-right"><a
                                                        href="javascript:show_pet('{{ $o->pet_id }}')"><img src="/desktop/img/ionicons_2-0-1_ios-information-outline_48_0_bd1a2a_none.png" width="22" height="22" alt="More Info" /></a></div>
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


                    <div class="col-lg-3 col-md-4 col-sm-6 card-wrapper">
                        <div class="media my-card" id="add-new-card" onclick="show_pet()">
                            <div class="media-body media-middle">
                                <div class="display-table">
                                    <div class="table-cell media-middle">
                                        <div class="media-left media-middle"><span class="glyphicon glyphicon-plus-sign"
                                                                                   aria-hidden="true"></span></div>
                                    </div>
                                    <div class="table-cell media-middle">
                                        <h4 class="media-heading text-left">ADD A NEW<br>
                                            DOG PROFILE</h4>
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
            </div>
            <!-- /container -->
        </section>
        <!-- /my-pets -->
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
                    <h4 class="modal-title text-center" id="editPetLabel">DOG PROFILE</h4>

                    <!-- INIT FORM -->
                    <form action="/user/pet/add" role="form" enctype="multipart/form-data"
                          method="post" id="frm_pet" target="ifm_upload" onsubmit="myApp.showLoading();" class="form-profile-info">

                        {!! csrf_field() !!}
                        <input type="hidden" id="pet_id" name="pet_id"/>
                        <input type="hidden" id="type" name="type" value="dog"/>

                        <fieldset>

                            <!-- PROFILE -->
                            <section id="profile">
                                <div class="container" class="st-opt">
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="pet-photo">
                                                <!-- DEFAULT PROFILE PHOTO -->
                                                <!-- required "demo-photo" class -->
                                                <img id="img_pet_photo" class="demo-photo" src="/desktop/img/dog-icon.svg" width="150">
                                                <!-- CUSTOM PROFILE PHOTO -->
                                                <!-- remove "demo-photo" class -->
                                                <!--<img src="/desktop/img/demo.jpg" width="221" height="225">-->
                                                <input type="file" id="pet_photo" name="pet_photo" style="display:none;" onchange="read_file(this)"/>
                                                <div id="add-photo" onclick="$('#pet_photo').click();"><span
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
                                                        <label class="control-label" for="">Dog's name </label>
                                                        <input class="form-control" name="name" id="name" type="text" maxlenght="100"
                                                               required/>
                                                        <span class="form-highlight"></span> <span
                                                                class="form-bar"></span>

                                                    </div>
                                                    <!-- /form-group -->
                                                </div>
                                                <!-- /col-6 -->
                                                <div class="col-xs-6">
                                                    <div class="form-group select-form-group">
                                                        <label class="control-label" for="age">Dog's age </label>
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

                                                    <p class="control-label">Gender</p>
                                                    <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                        <label class="btn btn-st-opt flex-fill">
                                                            <input type="radio" name="gender" value="F" autocomplete="off" checked>
                                                            Female</label>
                                                        <label class="btn btn-st-opt flex-fill">
                                                            <input type="radio" name="gender" value="M" autocomplete="off">
                                                            Male</label>
                                                    </div>
                                                </div>
                                                <!-- /col-6 -->
                                                <div class="col-sm-6">
                                                    <div class="form-group select-form-group">
                                                        <label class="control-label" for="breed">Dog's breed </label>
                                                        <select class="form-control" name="breed" id="breed" required>
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
                                                <!-- /col-6 -->
                                            </div>
                                            <!-- /row -->

                                        </div>
                                        <!-- /col-10 -->
                                    </div>
                                    <!-- /row -->

                                    <div class="row mb-4">
                                        <div class="col-xs-12">
                                            <p class="control-label" for="">Size &amp; weight </p>
                                            <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="size" value="2" autocomplete="off" checked>
                                                    S (<20lbs)</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="size" value="3" autocomplete="off">
                                                    M (21~40lbs)</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="size" value="4" autocomplete="off">
                                                    L (41~80lbs)</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="size" value="5" autocomplete="off">
                                                    XL (>81lbs)</label>
                                            </div>
                                        </div>
                                        <!-- /col -->
                                    </div>
                                    <!-- /row -->
                                    <div class="row mb-4">
                                        <div class="col-xs-12">
                                            <p class="control-label" for="">Temperament </p>
                                            <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="temperment" value="Friendly" autocomplete="off" checked>
                                                    Friendly</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="temperment" value="Anxious" autocomplete="off">
                                                    Anxious</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="temperment" value="Fatigue" autocomplete="off">
                                                    Fatigue</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="temperment" value="Aggressive" autocomplete="off">
                                                    Aggressive</label>
                                            </div>
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <!-- /row -->

                                    <div class="row">
                                        <div class="col-sm-12">
                                            <p class="control-label">Is your dog up-to-date with rabies vaccination?</p>
                                        </div>
                                        <div class="col-sm-6 mb-3">
                                            <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="vaccinated" value="Y" autocomplete="off" checked>
                                                    Yes</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="vaccinated" value="N" autocomplete="off">
                                                    No</label>
                                            </div>
                                        </div>
                                        <!-- /col -->

                                        <div class="col-sm-6 mb-4">
                                            <div class="form-group">
                                                <input type="file" id="upload_certificate" name="upload_certificate" value="" class="form-control">
                                                <a href="#/" class="w-100 vaccination_certificate_upload upload_input btn outline-btn rounded-btn groomit-btn table-cell align-middle" role="button" id="login_pg_register_button">
                                                    Upload Certificate
                                                </a>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col -->
                                    </div>
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


                                    <div class="row mb-4">
                                        <div class="col-xs-12">
                                            <p class="control-label" for="last_groom">Last groom </p>
                                            <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="last_groom" autocomplete="off" checked value="< 6 weeks">
                                                    <6 weeks</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="last_groom" value="<6 months" autocomplete="off">
                                                    <6 months</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="last_groom" value=">6 months" autocomplete="off">
                                                    >6 months</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="last_groom" value="Never" autocomplete="off">
                                                    Never</label>
                                            </div>
                                        </div>
                                        <!-- /col -->
                                    </div>
                                    <!-- /row -->
                                    <div class="row mb-4">
                                        <div class="col-xs-12">
                                            <p class="control-label" for="coat_type">Coat type </p>
                                            <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="coat_type" value="Silky" autocomplete="off" checked>
                                                    Silky</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="coat_type" value="Wiry" autocomplete="off">
                                                    Wiry</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="coat_type" value="Double Coat" autocomplete="off">
                                                    Double Coat</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="coat_type" value="Curly" autocomplete="off">
                                                    Curly</label>
                                            </div>
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <!-- /row -->

                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="form-group">
                                                <label class="control-label" for="">Notes to groomer (optional)</label>
                                                <textarea class="form-control" name="special_note" id="special_note" maxlength="250" rows="4"
                                                          placeholder="Allergies, Health Conditions, etc."></textarea>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                            </div>
                                            <!-- /form-group --> </div>

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

    <iframe id="ifm_upload" name="ifm_upload" style="display:none;"></iframe>

    <script type="text/javascript">
        $("body").on('click', '.vaccination_certificate_upload', function() {
            $('#upload_certificate').trigger('click');
        });
        function select_pet(pet_id) {
            $('#selected_pet_id').val(pet_id);
        }

        function go_to_next() {
            var pet_id = $('#selected_pet_id').val();
            if (pet_id === '') {
                myApp.showError('Please select pet first!');
                return;
            }

            $('#frm_submit').submit();

        }

        function show_pet(pet_id) {
            var mode = typeof pet_id === "undefined" ? "add" : "update";
            if (mode === "add") {
                $('#pet_id').val('');
                $('#img_pet_photo').prop('src', '/desktop/img/dog-icon.svg');
                $('#name').val('');
                $('#age').val('');

                $('[name="gender"][value!=""]').prop('checked', false);
                $('[name="gender"][value!=""]').parent().removeClass('active');

                $('#breed').val('');

                $('[name="size"][value!=""]').prop('checked', false);
                $('[name="size"][value!=""]').parent().removeClass('active');
                var size = '{{ session('schedule.size') }}';
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

        function load_pet(pet_id) {
            myApp.showLoading();

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
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {

                        var o = res.data;
                        if (o.photo) {
                            $('#img_pet_photo').prop('src', 'data:image/png;base64,' + o.photo);
                            $('#img_pet_photo').removeClass("demo-photo");
                        } else {
                            $('#img_pet_photo').prop('src', '/desktop/img/dog-icon.svg');
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
                        $('#pet_id').val(o.pet_id);

                        change_mode('view');

                        $('#modal-pet').modal();
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function change_mode(mode) {
            if (mode === 'view') {
                $('#frm_pet input,select,textarea,label,div').prop('disabled', true);
                $('#frm_pet input,select,textarea,label,div').prop('aria-disabled', true);
                $('#btn_edit').show();
                $('#btn_close').show();
                $('#btn_submit').hide();
                $('#btn_cancel').hide();
                $('#add-photo').hide();

                $('#frm_pet label').addClass('disabled');
                $('.vaccination_certificate_upload').addClass('disabled');
            } else {
                $('#frm_pet input,select,textarea,label,div').prop('disabled', false);
                $('#frm_pet input,select,textarea,label,div').prop('aria-disabled', false);
                $('#btn_edit').hide();
                $('#btn_close').hide();
                $('#btn_submit').show();
                $('#btn_cancel').show();
                $('#add-photo').show();

                $('#frm_pet label').removeClass('disabled');
                $('.vaccination_certificate_upload').removeClass('disabled');
            }
        }

    </script>
@stop