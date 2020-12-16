@extends('includes.admin_default')
@section('contents')
    <script type="text/javascript">

        window.onload = function () {
            $('#profile_photo').change(function () {
                previewImage(this, 'img_profile_photo');
            });

            $('#prevweek').hide();


            $('#update_groomer_availability').click(function () {
                update_groomer_availability();
            });

            $("#sdate").datetimepicker({
                format: 'YYYY-MM-DD'
            });
            $("#edate").datetimepicker({
                format: 'YYYY-MM-DD'
            });

        };

        function resetAvailability() {
            for (var i = 0; i <= 6; i++) {
                for (var j = 8; j <= 24; j++) {
                    var pad = "00";
                    var hour = pad.substring(0, pad.length - ('' + j).length) + j;
                    var key = 'wd' + i + '_h' + hour;
                    $('#' + key).prop('checked', false);
                }
            }
        }

        function getLastWeekAvailability() {
            var week = $('#week').val();
            var last_week = week * 1 - 1;

            get_groomer_availability(last_week, false);
        }

        function selectAll() {
            for (var i = 0; i <= 6; i++) {
                for (var j = 8; j <= 21; j++) {
                    var pad = "00";
                    var hour = pad.substring(0, pad.length - ('' + j).length) + j;
                    var key = 'wd' + i + '_h' + hour;
                    $('#' + key).prop('checked', true);
                }
            }
        }

        function nextWeek() {
            var we = $('#week_end').text();
            var week = $('#week').val();
            var next_week = week * 1 + 1;

            get_groomer_availability(next_week, true);

            var d = new Date(we);
            var nws = new Date(d.getFullYear(), d.getMonth(), d.getDate() + 1);
            var nwe = new Date(d.getFullYear(), d.getMonth(), d.getDate() + 7);

            $('#week_start').text(nws.toLocaleDateString("en-US"));
            $('#week_end').text(nwe.toLocaleDateString("en-US"));
            $('#week').val(parseInt(week) + 1);

            if (week >= 0) {
                $('#prevweek').show();
            } else {
                $('#prevweek').hide();
            }

        }

        function prevWeek() {
            var ws = $('#week_start').text();
            var week = $('#week').val();
            var last_week = week * 1 - 1;

            get_groomer_availability(last_week, true);

            var d = new Date(ws);
            var pws = new Date(d.getFullYear(), d.getMonth(), d.getDate() - 7);
            var pwe = new Date(d.getFullYear(), d.getMonth(), d.getDate() - 1);

            $('#week_start').text(pws.toLocaleDateString("en-US"));
            $('#week_end').text(pwe.toLocaleDateString("en-US"));
            $('#week').val(parseInt(week) - 1);

            if (week > 1) {
                $('#prevweek').show();
            } else {
                $('#prevweek').hide();
            }
        }



        function get_groomer_availability(week, show_message) {
            myApp.showLoading();
            $.ajax({
                url: '/admin/groomer/get_groomer_schedule',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: '{{ $gr->groomer_id }}',
                    week: week
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {

                        if (res.ga) {
                            var availability = res.ga;
                            for (var i = 0; i <= 6; i++) {
                                for (var j = 8; j <= 24; j++) {
                                    var pad = "00";
                                    var hour = pad.substring(0, pad.length - ('' + j).length) + j;
                                    var key = 'wd' + i + '_h' + hour;
                                    if (availability.indexOf(key) >= 0) {
                                        $('#' + key).prop('checked', true);
                                    } else {
                                        $('#' + key).prop('checked', false);
                                    }
                                }
                            }
                        } else {
                            if (show_message) {
                                //myApp.showSuccess("The Groomer doesn't have selected week's availability.",resetAvailability);
                                resetAvailability();
                            }
                        }

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

        function update_groomer_availability() {
            myApp.showLoading();
            $.ajax({
                url: '/admin/groomer/groomer_schedule_update',
                data: $("#weekly_availability").serialize(),
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    console.log(res);
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        myApp.hideLoading();
                        myApp.showSuccess('Your request has been processed successfully!');
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

        function show_adjustment() {

        }

        function show_recent_appointment() {

            myApp.showLoading();
            $.ajax({
                url: '/admin/groomer/{{$gr->groomer_id}}/history',
                data: {
                    sdate: $('#sdate').val(),
                    edate: $('#edate').val(),
                },
                cache: false,
                type: 'get',
                dataType: 'html',
                success: function (res) {
                    // console.log(res);
                    myApp.hideLoading();
                    $('#history').modal();
                    $('#history_data').html(res);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function update_groomer() {

            $('#frm_update').submit();
        }

        function phone_interview() {
            $('#frm_phone_interview_notes').submit();
        }

        function trial_interview() {
            $('#frm_trial_interview_notes').submit();
        }

        function update_cs_notes() {
            myApp.showLoading();
            $.ajax({
                url: '/admin/groomer/update_cs_notes',
                data: {
                    _token: '{!! csrf_token() !!}',
                    groomer_id: '{{ $gr->groomer_id }}',
                    cs_notes: $('#cs_notes').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        myApp.showSuccess('Your request has been processed successful!', function() {
                            window.location.reload();
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function confirm_pw() {

            if($('#password').val() != $('#confirm_password').val() ){
                alert('Passwords Do not Match');

                $('#password').val('');
                $('#confirm_password').val('');
                $('#password').focus();

                return;
            }
        }

        {{--function remove_groomer_exclusive_area( weekday, alias_id) {--}}
        {{--    window.location.href = '/admin/groomer/remove_exclusive_area/{{ $gr->groomer_id }}/' + weekday + '/' + alias_id;--}}
        {{--}--}}

        {{--function add_groomer_exclusive_area( weekday ) {--}}
        {{--    var exclusive_alias_id = '';--}}
        {{--    if( weekday == 0 ){--}}
        {{--        exclusive_alias_id = $('#exclusive_area_0').val() ;--}}
        {{--    }else if( weekday == 1 ) {--}}
        {{--        exclusive_alias_id = $('#exclusive_area_1').val() ;--}}
        {{--    }else if( weekday == 2 ) {--}}
        {{--        exclusive_alias_id = $('#exclusive_area_2').val() ;--}}
        {{--    }else if( weekday == 3 ) {--}}
        {{--        exclusive_alias_id = $('#exclusive_area_3').val() ;--}}
        {{--    }else if( weekday == 4 ) {--}}
        {{--        exclusive_alias_id = $('#exclusive_area_4').val() ;--}}
        {{--    }else if( weekday == 5 ) {--}}
        {{--        exclusive_alias_id = $('#exclusive_area_5').val() ;--}}
        {{--    }else if( weekday == 6 ) {--}}
        {{--        exclusive_alias_id = $('#exclusive_area_6').val() ;--}}
        {{--    }--}}

        {{--    if ( exclusive_alias_id == '') {--}}
        {{--        alert('Please select exclusive area to add !');--}}
        {{--        return;--}}
        {{--    }--}}
        {{--    window.location.href = '/admin/groomer/add_exclusive_area/{{ $gr->groomer_id }}/' + weekday + '/' + exclusive_alias_id;--}}
        {{--}--}}

    </script>


<div class="container-fluid top-cont">
    <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />Groomer Detail</h3>
</div>

<div class="container-fluid">
    <div class="well filter" style="padding-bottom:5px;">


        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <a href="/admin/groomers" class="btn btn-info">Back to List</a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <a href="/admin/message/{{$gr->groomer_id}}" class="btn-left btn btn-danger"
                       target="_blank">SEND TEXT</a>
                </div>
            </div>
            <div class="col-md-8 text-right">
                <div class="form-group">
                    <div class="btn-right btn btn-danger" data-toggle="modal" data-target="#delete">Delete</div>
                    <div class="btn-right btn btn-success" data-toggle="modal" data-target="#update">Update Information</div>
                    <div class="btn-right btn btn-primary" data-toggle="modal" data-target="#availability">Update Availability</div>
                    <div class="btn-right btn btn-warning" data-toggle="modal" data-target="#change_password">Change Password</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <a href="/admin/groomer-simulator/{{$gr->groomer_id}}" class="btn-left btn btn-info"
                       target="_blank">API SIMULATOR</a>
                </div>
            </div>
            <div class="col-md-8 text-right">
                <div class="form-group">
                    <div class="btn-right btn btn-info" data-toggle="modal" href="/admin/upcoming/{{$gr->groomer_id}}/groomer"
                         data-target="#upcoming">Upcoming Appointment
                    </div>
                    <div class="btn-right btn btn-info" onclick="show_recent_appointment()">Appointment History
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- Upcoming Appointments Modal start -->
<div class="modal fade text-center" id="upcoming">
    <div class="modal-dialog" style="width:90%;">
        <div class="modal-content">
        </div>
    </div>
</div>
<!-- Upcoming Appointments Modal End -->

<!-- Appointments History Modal start -->
<div class="modal fade text-center" id="history">
    <div class="modal-dialog" style="width:90%;">
        <div class="modal-content">
            <script type="text/javascript" class="init">

            </script>
            <div class="padding-10">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
            </div>
            <h3>Appointments History</h3>
            <hr>

            <div class="row">
                <div class="col-sm-2">
                    <div class="form-group">
                    </div>
                </div>

                <div class="col-sm-8">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Service Date</label>
                        <div class="col-md-8">
                            <input type="text" style="width:100px; float:left;" class="form-control" id="sdate"
                                   name="sdate" value="{{ \Carbon\Carbon::today()->subDays(30)->format('Y-m-d') }}"/>
                            <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                            <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                   class="form-control" id="edate" name="edate" value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"/>
                            <a class="btn btn-info form-control" style="margin-left:5px; width:100px; float:left;"
                                onclick="show_recent_appointment()">Search
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div id="history_data">
            </div>
        </div>
    </div>
</div>
<!-- Appointments History Modal End -->

<!-- Update Modal start-->
<div class="modal fade" id="update">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Update Groomer's Information</h4>
            </div>
            <div class="modal-body">
                <form method="post" id="frm_update" name="frm_update" action="/admin/groomer/update" class="form-group"
                      enctype="multipart/form-data">
                    {!! csrf_field() !!}

                    <input type="hidden" name="id" value="{{$gr->groomer_id}}"/>

                    <div class="row no-border">
                        <div class="col-xs-3">Profile Photo</div>
                        <div class="col-xs-8">
                            <div class="text-center">
                                @if ($gr->profile_photo)
                                    <img id="img_profile_photo"
                                         src="data:image/png;base64,{{ $gr->profile_photo }}"/>
                                @else
                                    <img id="img_profile_photo" src="/images/upload-img.png"/>
                                @endif
                                <input type="file" id="profile_photo" name="profile_photo"
                                       value="{{ old('profile_photo') }}" style="visibility:hidden"/>
                            </div>
                        </div>
                        <div class="col-xs-8 col-xs-offset-3">
                            <div class="text-center">
                                <a onclick="$('[name=profile_photo]').click()"
                                   class="btn btn-success upload">Upload Images</a>
                            </div>
                        </div>
                    </div>
                </br>
                    <div class="row no-border">
                        <div class="col-xs-3">Status</div>
                        <div class="col-xs-6">
                            <select name="status" class="form-control">
                            <!--option value="N" {{ old('status', $gr->status) == 'N' ? 'selected' : '' }}>
                                            New
                                        </option-->
                                <option value="A" {{ old('status', $gr->status) == 'A' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="I" {{ old('status', $gr->status) == 'I' ? 'selected' : '' }}>
                                    Inactive
                                </option>
                                <option value="I" {{ old('status', $gr->status) == 'D' ? 'selected' : '' }}>
                                    Remove
                                </option>
                            </select>
                        </div>
                        <div class="col-xs-3"></div>
                    </div>
                    <div class="row no-border">
                        <div class="col-xs-3">Background Check / Trial</div>
                        <div class="col-xs-6">
                            <select name="background_check_status" class="form-control">
                                <option value="G" {{ old('background_check_status', $gr->background_check_status) == 'G' ? 'selected' : '' }}>
                                    Background Checks Progress
                                </option>
                                <option value="R" {{ old('background_check_status', $gr->background_check_status) == 'R' ? 'selected' : '' }}>
                                    Background Checks Rejected
                                </option>
                                <option value="A" {{ old('background_check_status', $gr->background_check_status) == 'A' ? 'selected' : '' }}>
                                    Background Checks Approved
                                </option>
                                <option value="P" {{ old('background_check_status', $gr->background_check_status) == 'P' ? 'selected' : '' }}>
                                    Background Checks Pending
                                </option>
                                <option value="V" {{ old('background_check_status', $gr->background_check_status) == 'V' ? 'selected' : '' }}>
                                    Video Trial Scheduled
                                </option>
                                <option value="I" {{ old('background_check_status', $gr->background_check_status) == 'I' ? 'selected' : '' }}>
                                    InPerson Trial Scheduled
                                </option>
                            </select>
                        </div>
                        <div class="col-xs-3">
                            <input type="text" class="form-control" name="trial_notes" placeholder="Insert Trial date" value="{{ $gr->trial_notes }}"/>
                        </div>
                    </div>
                    <div class="row no-border">
                        <div class="col-xs-3">Level</div>
                        <div class="col-xs-6">
                            <select name="level" class="form-control" {{ ($enable_upd_level == 'Y') ? '' : 'disabled' }}>
                                <option value="" {{ old('level', $gr->level) == '' ? 'selected' : '' }}>
                                    Select Level
                                </option>
                                <option value="1" {{ old('level', $gr->level) == '1' ? 'selected' : '' }}>
                                    Level 1
                                </option>
                                <option value="2" {{ old('level', $gr->level) == '2' ? 'selected' : '' }}>
                                    Level 2
                                </option>
                                <option value="3" {{ old('level', $gr->level) == '3' ? 'selected' : '' }}>
                                    Level 3
                                </option>
                                <option value="4" {{ old('level', $gr->level) == '4' ? 'selected' : '' }}>
                                    Level 4
                                </option>
                                <option value="5" {{ old('level', $gr->level) == '5' ? 'selected' : '' }}>
                                    Level 5
                                </option>
                            </select>
                        </div>
                        <div class="col-xs-3"></div>
                    </div>
                    <div class="row no-border">
                        <div class="col-xs-3">Dog</div>
                        <div class="col-xs-2">
                            <input type="checkbox" name="dog"
                                   value="{{ $gr->dog }}" {{ $gr->dog == 'Y' ? 'checked' : '' }}/>
                        </div>
                    </div>
                    <div class="row no-border">
                        <div class="col-xs-3">Cat</div>
                        <div class="col-xs-2">
                            <input type="checkbox" name="cat"
                                   value="{{ $gr->cat }}" {{ $gr->cat == 'Y' ? 'checked' : '' }}/>
                        </div>
                    </div>
                    <div class="row no-border">
                        <div class="col-xs-3">First Name</div>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="first_name"
                                   value="{{ $gr->first_name }}"/>
                        </div>
                    </div>
                    <div class="row no-border">
                        <div class="col-xs-3">Last Name</div>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="last_name"
                                   value="{{ $gr->last_name }}"/>
                        </div>
                    </div>
{{--                    <div class="row no-border">--}}
{{--                        <div class="col-xs-3">Phone</div>--}}
{{--                        <div class="col-xs-9">--}}
{{--                            <input type="text" class="form-control" name="phone" value="{{ $gr->phone }}"/>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <div class="row no-border">
                        <div class="col-xs-3">Mobile</div>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="mobile_phone"
                                   value="{{ $gr->mobile_phone }}"/>
                        </div>
                    </div>
                    <div class="row no-border">
                        <div class="col-xs-3">Email</div>
                        <div class="col-xs-9">
                            <input type="email" class="form-control" name="email" value="{{ $gr->email }}"/>
                        </div>
                    </div>

                    <div class="row no-border">
                        <div class="col-xs-3">Gender</div>
                        <div class="col-xs-2">
                            <select name="sex" class="form-control">
                                <option value="" {{ old('sex', $gr->sex) == '' ? 'selected' : '' }}>
                                    Select
                                </option>
                                <option value="M" {{ old('sex', $gr->sex) == 'M' ? 'selected' : '' }}>
                                    Male
                                </option>
                                <option value="F" {{ old('sex', $gr->sex) == 'F' ? 'selected' : '' }}>
                                    Female
                                </option>
                            </select>
                        </div>
                        <div class="col-xs-7"></div>
                    </div>
                    <div class="row no-border">
                        <div class="col-xs-3">Transportation</div>
                        <div class="col-xs-2">
                            <select name="transportation" class="form-control">
                                <option value="" {{ old('transportation', $gr->transportation) == '' ? 'selected' : '' }}>
                                    Select
                                </option>
                                <option value="P" {{ old('transportation', $gr->transportation) == 'P' ? 'selected' : '' }}>
                                    Public
                                </option>
                                <option value="C" {{ old('transportation', $gr->transportation) == 'C' ? 'selected' : '' }}>
                                    My Car
                                </option>
                            </select>
                        </div>
                        <div class="col-xs-7"></div>
                    </div>

                    <div class="row no-border">
                        <div class="col-xs-3">Available in my area</div>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="available_in_my_area"
                                   value="{{ $gr->available_in_my_area }}"/>
                        </div>
                        <div class="col-xs-3">Address</div>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="street"
                                   value="{{ $gr->street }}"/>
                        </div>
                        <div class="col-xs-3">City</div>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="city" value="{{ $gr->city }}"/>
                        </div>
                        <div class="col-xs-3">State</div>
                        <div class="col-xs-9">
                            <input type="text" class="form-control small" name="state"
                                   value="{{ $gr->state }}" maxlength="2"/>
                        </div>
                        <div class="col-xs-3">Zip</div>
                        <div class="col-xs-9">
                            <input type="text" class="form-control small" name="zip" value="{{ $gr->zip }}"
                                   maxlength="5"/>
                        </div>
                    </div>
                    <div class="row no-border">
                        <div class="col-xs-3">Bio</div>
                        <div class="col-xs-9">
                            <textarea cols="4" class="form-control" name="bio">{{ $gr->bio }}</textarea>
                        </div>
                    </div>
                    <div class="row no-border">
                        <div class="col-xs-3">Where did you learn to groom?</div>
                        <div class="col-xs-9">
                            <textarea cols="4" class="form-control"
                                      name="groomer_exp_note">{{ $gr->groomer_exp_note }}</textarea>
                        </div>
                    </div>

                    <div class="row no-border">
                        <div class="col-xs-3">Weekly Allowance($)</div>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="weekly_allowance"
                                   value="{{ $gr->weekly_allowance }}"/>
                        </div>
                    </div>

                    <div class="row no-border">
                        <div class="col-xs-3">Bank Name</div>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="bank_name"
                                   value="{{ $gr->bank_name }}"/>
                        </div>
                    </div>

                    <div class="row no-border">
                        <div class="col-xs-3">Account Holder Name</div>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="account_holder"
                                   value="{{ $gr->account_holder }}"/>
                        </div>
                    </div>
                    <div class="row no-border">
                        <div class="col-xs-3">Account #</div>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="account_number"
                                   value="{{ $gr->account_number }}"/>
                        </div>
                    </div>
                    <div class="row no-border">
                        <div class="col-xs-3">Routing #</div>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="routing_number"
                                   value="{{ $gr->routing_number }}"/>
                        </div>
                    </div>
                    <div class="row no-border">
                        <div class="col-xs-3">Service Area</div>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="service_area"
                                   value="{{ $gr->service_area }}"/>
                        </div>
                    </div>
                    <div class="row no-border">
                        <div class="col-xs-3">General Notes</div>
                        <div class="col-xs-9">
                            <textarea cols="4" class="form-control" name="general_notes">{{ $gr->general_notes }}</textarea>
                        </div>
                    </div>

                    <div class="row no-border">
                        <div class="col-xs-3">TEXT APPT</div>
                        <div class="col-xs-6">
                            <select name="text_appt" class="form-control">
                                <option value="Y" {{ old('text_appt', $gr->text_appt) == 'Y' ? 'selected' : '' }}>
                                    YES
                                </option>
                                <option value="N" {{ old('text_appt', $gr->text_appt) == 'N' ? 'selected' : '' }}>
                                    NO
                                </option>
                            </select>
                        </div>
                        <div class="col-xs-3"></div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success" type="button" onclick="update_groomer()">UPDATE</button>
                        <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Update Modal end-->

<!-- Update Phone Interview Notes start-->
<div class="modal fade" id="phone_notes">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Phone Interview Notes</h4>
            </div>
            <div class="modal-body">
                <form method="post" id="frm_phone_interview_notes" name="frm_phone_interview_notes" action="/admin/groomer/phone_interview_notes" class="form-group"
                      enctype="multipart/form-data">
                    {!! csrf_field() !!}

                    <input type="hidden" name="id" value="{{$gr->groomer_id}}"/>

                    <div class="row no-border">
                        <div class="col-xs-12">
                            @if(!empty($gr->phone_interview_notes))
                                <textarea rows="35" class="form-control" name="phone_interview_notes">{{ $gr->phone_interview_notes }}</textarea>
                            @else
                                <textarea rows="35" class="form-control" name="phone_interview_notes">{{ $gr->phone_question }}</textarea>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success" type="button" onclick="phone_interview()">UPDATE</button>
                        <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Update Phone Interview Notes end-->

<!-- Update Trial Interview Notes start-->
<div class="modal fade" id="trial_notes">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Trail Interview Notes</h4>
            </div>
            <div class="modal-body">
                <form method="post" id="frm_trial_interview_notes" name="frm_trial_interview_notes" action="/admin/groomer/trial_interview_notes" class="form-group"
                      enctype="multipart/form-data">
                    {!! csrf_field() !!}

                    <input type="hidden" name="id" value="{{$gr->groomer_id}}"/>

                    <div class="row no-border">
                        <div class="col-xs-12">
                            @if(!empty($gr->trial_interview_notes))
                                <textarea rows="35" class="form-control" name="trial_interview_notes">{{ $gr->trial_interview_notes }}</textarea>
                            @else
                                <textarea rows="35" class="form-control" name="trial_interview_notes">{{ $gr->trial_question }}</textarea>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success" type="button" onclick="trial_interview()">UPDATE</button>
                        <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Update Trial Interview Notes end-->

<!-- Change Password Modal Start -->
<div class="modal" id="change_password" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Change Password</h4>
            </div>
            <div class="modal-body">
                <form method="post" action="/admin/groomer/change_password"
                      class="form-group">
                    {!! csrf_field() !!}

                    <input type="hidden" name="id" value="{{$gr->groomer_id}}"/>

                    <div class="row no-border">
                        <div class="col-xs-3">New Password</div>
                        <div class="col-xs-5">
                            <input type="password" name="password" id="password" value=""/>
                        </div>

                        <div class="col-xs-4">
                            <button class="btn btn-warning" type="submit">CHANGE PASSWORD</button>
                        </div>

                        <div class="col-xs-3">Confirm Password</div>
                        <div class="col-xs-5">
                            <input type="password" name="confirm_password" id="confirm_password" value="" onchange="confirm_pw()"/>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Change Password Modal End -->

<!-- Delete Modal Start -->
<div class="modal" id="delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="/admin/groomer/delete" class="form-group">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Delete Groomer</h4>
                </div>
                <div class="modal-body text-center">
                    Are you sure?
                    <input type="hidden" name="id" value="{{$gr->groomer_id}}"/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button class="btn btn-danger" type="submit">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Delete Modal End -->

<!-- availability Modal start-->
<div class="modal fade" id="availability" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="weekly_availability" method="post" onsubmit="return false;">
                {!! csrf_field() !!}
                <input type="hidden" id="id" name="id" value="{{$gr->groomer_id}}"/>
                <input type="hidden" id="week" name="week" value="{{$dr->week}}"/>

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Update Groomer's Availability</h4>
                </div>

                <div class="modal-body">
                    <div class="text-center">Date Range</div>
                    <div class="text-center row">
                        <div class="col-sm-4 text-left">
                            <a href="#" id="prevweek" onclick="prevWeek()"><< Previous Week</a>
                        </div>
                        <div class="col-sm-2" id="week_start">{{ $dr->week_start }}</div>
                        <div class="col-sm-1">~</div>
                        <div class="col-sm-2" id="week_end">{{ $dr->week_end }} </div>
                        <div class="col-sm-3 text-right">
                            <a href="#" id="nextweek" onclick="nextWeek()"> Next Week >></a>
                        </div>
                    </div>
                    <table class="no-page-break">
                        <tr>
                            <td>
                                <div class="row">

                                    <div class="col availabilityCont">

                                        <div class="row no-border">
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

                                        <div class="row no-border">
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
{{--                                                <div class="availabilityBox availabilityBoxNum text-center">--}}
{{--                                                    10--}}
{{--                                                </div>--}}
{{--                                                <div class="availabilityBox availabilityBoxNum text-center">--}}
{{--                                                    11--}}
{{--                                                </div>--}}
{{--                                                <div class="availabilityBox availabilityBoxNum text-center">--}}
{{--                                                    12--}}
{{--                                                </div>--}}

                                            </div>
                                        </div>

                                        <div class="row no-border">
                                            <div class="col-lg-12">
                                                <div class="availabilityBox availabilityBoxDay">
                                                    <span>M</span>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd0_h08" name="wd0_h08"
                                                           {{ old('wd0_h08') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd0_h08" id="lbl_wd0_h08"
                                                           data-toggle="tooltip"
                                                           title="Please setup your availability"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd0_h09" name="wd0_h09"
                                                           {{ old('wd0_h09') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd0_h09"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd0_h10" name="wd0_h10"
                                                           {{ old('wd0_h10') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd0_h10"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd0_h11" name="wd0_h11"
                                                           {{ old('wd0_h11') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd0_h11"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd0_h12" name="wd0_h12"
                                                           {{ old('wd0_h12') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd0_h12"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd0_h13" name="wd0_h13"
                                                           {{ old('wd0_h13') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd0_h13"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd0_h14" name="wd0_h14"
                                                           {{ old('wd0_h14') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd0_h14"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd0_h15" name="wd0_h15"
                                                           {{ old('wd0_h15') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd0_h15"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd0_h16" name="wd0_h16"
                                                           {{ old('wd0_h16') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd0_h16"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd0_h17" name="wd0_h17"
                                                           {{ old('wd0_h17') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd0_h17"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd0_h18" name="wd0_h18"
                                                           {{ old('wd0_h18') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd0_h18"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd0_h19" name="wd0_h19"
                                                           {{ old('wd0_h19') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd0_h19"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd0_h20" name="wd0_h20"
                                                           {{ old('wd0_h20') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd0_h20"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd0_h21" name="wd0_h21"
                                                           {{ old('wd0_h21') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd0_h21"></label>
                                                </div>
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd0_h22" name="wd0_h22"--}}
{{--                                                           {{ old('wd0_h22') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd0_h22"></label>--}}
{{--                                                </div>--}}
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd0_h23" name="wd0_h23"--}}
{{--                                                           {{ old('wd0_h23') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd0_h23"></label>--}}
{{--                                                </div>--}}
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd0_h24" name="wd0_h24"--}}
{{--                                                           {{ old('wd0_h24') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd0_h24"></label>--}}
{{--                                                </div>--}}
                                            </div>
                                        </div>


                                        <div class="row no-border">
                                            <div class="col-lg-12">
                                                <div class="availabilityBox availabilityBoxDay">
                                                    <span>T</span>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd1_h08" name="wd1_h08"
                                                           {{ old('wd1_h08') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd1_h08"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd1_h09" name="wd1_h09"
                                                           {{ old('wd1_h09') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd1_h09"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd1_h10" name="wd1_h10"
                                                           {{ old('wd1_h10') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd1_h10"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd1_h11" name="wd1_h11"
                                                           {{ old('wd1_h11') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd1_h11"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd1_h12" name="wd1_h12"
                                                           {{ old('wd1_h12') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd1_h12"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd1_h13" name="wd1_h13"
                                                           {{ old('wd1_h13') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd1_h13"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd1_h14" name="wd1_h14"
                                                           {{ old('wd1_h14') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd1_h14"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd1_h15" name="wd1_h15"
                                                           {{ old('wd1_h15') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd1_h15"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd1_h16" name="wd1_h16"
                                                           {{ old('wd1_h16') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd1_h16"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd1_h17" name="wd1_h17"
                                                           {{ old('wd1_h17') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd1_h17"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd1_h18" name="wd1_h18"
                                                           {{ old('wd1_h18') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd1_h18"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd1_h19" name="wd1_h19"
                                                           {{ old('wd1_h19') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd1_h19"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd1_h20" name="wd1_h20"
                                                           {{ old('wd1_h20') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd1_h20"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd1_h21" name="wd1_h21"
                                                           {{ old('wd1_h21') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd1_h21"></label>
                                                </div>
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd1_h22" name="wd1_h22"--}}
{{--                                                           {{ old('wd1_h22') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd1_h22"></label>--}}
{{--                                                </div>--}}
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd1_h23" name="wd1_h23"--}}
{{--                                                           {{ old('wd1_h23') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd1_h23"></label>--}}
{{--                                                </div>--}}
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd1_h24" name="wd1_h24"--}}
{{--                                                           {{ old('wd1_h24') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd1_h24"></label>--}}
{{--                                                </div>--}}
                                            </div>
                                        </div>


                                        <div class="row no-border">
                                            <div class="col-lg-12">
                                                <div class="availabilityBox availabilityBoxDay">
                                                    <span>W</span>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd2_h08" name="wd2_h08"
                                                           {{ old('wd2_h08') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd2_h08"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd2_h09" name="wd2_h09"
                                                           {{ old('wd2_h09') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd2_h09"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd2_h10" name="wd2_h10"
                                                           {{ old('wd2_h10') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd2_h10"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd2_h11" name="wd2_h11"
                                                           {{ old('wd2_h11') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd2_h11"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd2_h12" name="wd2_h12"
                                                           {{ old('wd2_h12') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd2_h12"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd2_h13" name="wd2_h13"
                                                           {{ old('wd2_h13') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd2_h13"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd2_h14" name="wd2_h14"
                                                           {{ old('wd2_h14') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd2_h14"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd2_h15" name="wd2_h15"
                                                           {{ old('wd2_h15') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd2_h15"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd2_h16" name="wd2_h16"
                                                           {{ old('wd2_h16') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd2_h16"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd2_h17" name="wd2_h17"
                                                           {{ old('wd2_h17') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd2_h17"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd2_h18" name="wd2_h18"
                                                           {{ old('wd2_h18') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd2_h18"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd2_h19" name="wd2_h19"
                                                           {{ old('wd2_h19') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd2_h19"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd2_h20" name="wd2_h20"
                                                           {{ old('wd2_h20') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd2_h20"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd2_h21" name="wd2_h21"
                                                           {{ old('wd2_h21') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd2_h21"></label>
                                                </div>
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd2_h22" name="wd2_h22"--}}
{{--                                                           {{ old('wd2_h22') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd2_h22"></label>--}}
{{--                                                </div>--}}
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd2_h23" name="wd2_h23"--}}
{{--                                                           {{ old('wd2_h23') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd2_h23"></label>--}}
{{--                                                </div>--}}
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd2_h24" name="wd2_h24"--}}
{{--                                                           {{ old('wd2_h24') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd2_h24"></label>--}}
{{--                                                </div>--}}
                                            </div>
                                        </div>


                                        <div class="row no-border">
                                            <div class="col-lg-12">
                                                <div class="availabilityBox availabilityBoxDay">
                                                    <span>T</span>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd3_h08" name="wd3_h08"
                                                           {{ old('wd3_h08') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd3_h08"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd3_h09" name="wd3_h09"
                                                           {{ old('wd3_h09') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd3_h09"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd3_h10" name="wd3_h10"
                                                           {{ old('wd3_h10') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd3_h10"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd3_h11" name="wd3_h11"
                                                           {{ old('wd3_h11') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd3_h11"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd3_h12" name="wd3_h12"
                                                           {{ old('wd3_h12') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd3_h12"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd3_h13" name="wd3_h13"
                                                           {{ old('wd3_h13') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd3_h13"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd3_h14" name="wd3_h14"
                                                           {{ old('wd3_h14') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd3_h14"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd3_h15" name="wd3_h15"
                                                           {{ old('wd3_h15') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd3_h15"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd3_h16" name="wd3_h16"
                                                           {{ old('wd3_h16') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd3_h16"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd3_h17" name="wd3_h17"
                                                           {{ old('wd3_h17') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd3_h17"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd3_h18" name="wd3_h18"
                                                           {{ old('wd3_h18') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd3_h18"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd3_h19" name="wd3_h19"
                                                           {{ old('wd3_h19') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd3_h19"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd3_h20" name="wd3_h20"
                                                           {{ old('wd3_h20') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd3_h20"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd3_h21" name="wd3_h21"
                                                           {{ old('wd3_h21') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd3_h21"></label>
                                                </div>
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd3_h22" name="wd3_h22"--}}
{{--                                                           {{ old('wd3_h22') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd3_h22"></label>--}}
{{--                                                </div>--}}
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd3_h23" name="wd3_h23"--}}
{{--                                                           {{ old('wd3_h23') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd3_h23"></label>--}}
{{--                                                </div>--}}
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd3_h24" name="wd3_h24"--}}
{{--                                                           {{ old('wd3_h24') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd3_h24"></label>--}}
{{--                                                </div>--}}
                                            </div>
                                        </div>


                                        <div class="row no-border">
                                            <div class="col-lg-12">
                                                <div class="availabilityBox availabilityBoxDay">
                                                    <span>F</span>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd4_h08" name="wd4_h08"
                                                           {{ old('wd4_h08') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd4_h08"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd4_h09" name="wd4_h09"
                                                           {{ old('wd4_h09') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd4_h09"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd4_h10" name="wd4_h10"
                                                           {{ old('wd4_h10') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd4_h10"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd4_h11" name="wd4_h11"
                                                           {{ old('wd4_h11') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd4_h11"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd4_h12" name="wd4_h12"
                                                           {{ old('wd4_h12') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd4_h12"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd4_h13" name="wd4_h13"
                                                           {{ old('wd4_h13') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd4_h13"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd4_h14" name="wd4_h14"
                                                           {{ old('wd4_h14') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd4_h14"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd4_h15" name="wd4_h15"
                                                           {{ old('wd4_h15') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd4_h15"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd4_h16" name="wd4_h16"
                                                           {{ old('wd4_h16') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd4_h16"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd4_h17" name="wd4_h17"
                                                           {{ old('wd4_h17') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd4_h17"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd4_h18" name="wd4_h18"
                                                           {{ old('wd4_h18') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd4_h18"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd4_h19" name="wd4_h19"
                                                           {{ old('wd4_h19') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd4_h19"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd4_h20" name="wd4_h20"
                                                           {{ old('wd4_h20') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd4_h20"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd4_h21" name="wd4_h21"
                                                           {{ old('wd4_h21') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd4_h21"></label>
                                                </div>
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd4_h22" name="wd4_h22"--}}
{{--                                                           {{ old('wd4_h22') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd4_h22"></label>--}}
{{--                                                </div>--}}
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd4_h23" name="wd4_h23"--}}
{{--                                                           {{ old('wd4_h23') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd4_h23"></label>--}}
{{--                                                </div>--}}
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd4_h24" name="wd4_h24"--}}
{{--                                                           {{ old('wd4_h24') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd4_h24"></label>--}}
{{--                                                </div>--}}
                                            </div>
                                        </div>


                                        <div class="row no-border">
                                            <div class="col-lg-12">
                                                <div class="availabilityBox availabilityBoxDay">
                                                    <span>S</span>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd5_h08" name="wd5_h08"
                                                           {{ old('wd5_h08') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd5_h08"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd5_h09" name="wd5_h09"
                                                           {{ old('wd5_h09') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd5_h09"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd5_h10" name="wd5_h10"
                                                           {{ old('wd5_h10') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd5_h10"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd5_h11" name="wd5_h11"
                                                           {{ old('wd5_h11') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd5_h11"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd5_h12" name="wd5_h12"
                                                           {{ old('wd5_h12') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd5_h12"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd5_h13" name="wd5_h13"
                                                           {{ old('wd5_h13') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd5_h13"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd5_h14" name="wd5_h14"
                                                           {{ old('wd5_h14') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd5_h14"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd5_h15" name="wd5_h15"
                                                           {{ old('wd5_h15') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd5_h15"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd5_h16" name="wd5_h16"
                                                           {{ old('wd5_h16') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd5_h16"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd5_h17" name="wd5_h17"
                                                           {{ old('wd5_h17') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd5_h17"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd5_h18" name="wd5_h18"
                                                           {{ old('wd5_h18') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd5_h18"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd5_h19" name="wd5_h19"
                                                           {{ old('wd5_h19') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd5_h19"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd5_h20" name="wd5_h20"
                                                           {{ old('wd5_h20') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd5_h20"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd5_h21" name="wd5_h21"
                                                           {{ old('wd5_h21') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd5_h21"></label>
                                                </div>
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd5_h22" name="wd5_h22"--}}
{{--                                                           {{ old('wd5_h22') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd5_h22"></label>--}}
{{--                                                </div>--}}
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd5_h23" name="wd5_h23"--}}
{{--                                                           {{ old('wd5_h23') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd5_h23"></label>--}}
{{--                                                </div>--}}
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd5_h24" name="wd5_h24"--}}
{{--                                                           {{ old('wd5_h24') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd5_h24"></label>--}}
{{--                                                </div>--}}
                                            </div>
                                        </div>

                                        <div class="row no-border">
                                            <div class="col-lg-12">
                                                <div class="availabilityBox availabilityBoxDay">
                                                    <span>S</span>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd6_h08" name="wd6_h08"
                                                           {{ old('wd6_h08') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd6_h08"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd6_h09" name="wd6_h09"
                                                           {{ old('wd6_h09') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd6_h09"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd6_h10" name="wd6_h10"
                                                           {{ old('wd6_h10') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd6_h10"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd6_h11" name="wd6_h11"
                                                           {{ old('wd6_h11') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd6_h11"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd6_h12" name="wd6_h12"
                                                           {{ old('wd6_h12') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd6_h12"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd6_h13" name="wd6_h13"
                                                           {{ old('wd6_h13') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd6_h13"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd6_h14" name="wd6_h14"
                                                           {{ old('wd6_h14') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd6_h14"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd6_h15" name="wd6_h15"
                                                           {{ old('wd6_h15') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd6_h15"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd6_h16" name="wd6_h16"
                                                           {{ old('wd6_h16') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd6_h16"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd6_h17" name="wd6_h17"
                                                           {{ old('wd6_h17') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd6_h17"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd6_h18" name="wd6_h18"
                                                           {{ old('wd6_h18') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd6_h18"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd6_h19" name="wd6_h19"
                                                           {{ old('wd6_h19') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd6_h19"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd6_h20" name="wd6_h20"
                                                           {{ old('wd6_h20') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd6_h20"></label>
                                                </div>
                                                <div class="availabilityBox availabilityBoxCheck">
                                                    <input type="checkbox" id="wd6_h21" name="wd6_h21"
                                                           {{ old('wd6_h21') == 'Y' ? 'checked' : '' }} value="Y"/>
                                                    <label for="wd6_h21"></label>
                                                </div>
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd6_h22" name="wd6_h22"--}}
{{--                                                           {{ old('wd6_h22') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd6_h22"></label>--}}
{{--                                                </div>--}}
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd6_h23" name="wd6_h23"--}}
{{--                                                           {{ old('wd6_h23') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd6_h23"></label>--}}
{{--                                                </div>--}}
{{--                                                <div class="availabilityBox availabilityBoxCheck">--}}
{{--                                                    <input type="checkbox" id="wd6_h24" name="wd6_h24"--}}
{{--                                                           {{ old('wd6_h24') == 'Y' ? 'checked' : '' }} value="Y"/>--}}
{{--                                                    <label for="wd6_h24"></label>--}}
{{--                                                </div>--}}
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="modal-footer">
                    <div class="col-xs-8 text-left">
                        <button class="btn btn-info" type="button" onclick="selectAll()">Select All</button>
                        <button class="btn btn-info" type="button" onclick="getLastWeekAvailability()">Same
                            As Last Week
                        </button>
                        <button class="btn btn-warning" type="button" onclick="resetAvailability()">RESET
                        </button>
                    </div>
                    <div class="col-xs-4 text-right">
                        <button class="btn btn-success" id="update_groomer_availability" type="button">
                            UPDATE
                        </button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- availability Modal end-->

<script type="text/javascript">
    function document_upload(type, name) {
        $('#document_type').val(type);
        $('#document_upload_title').text(name);

        $('#document_upload_modal').modal();
    }
</script>
<!-- Document Upload Start -->
<div class="modal" id="document_upload_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="/admin/groomer/{{ $gr->groomer_id }}/document/upload" class="form-group"
                  enctype="multipart/form-data">
                {!! csrf_field() !!}

                <input type="hidden" name="type" id="document_type">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel"><span id="document_upload_title"></span> Upload </h4>
                </div>
                <div class="modal-body text-left">
                    <div class="form-group">
                        <label>Select File</label>
                        <input type="file" name="document_file" value="" class="form-control"/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                    <button class="btn btn-danger" type="submit">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Document Upload End -->

<div class="container-fluid">
    <div id="groomer" class="detail application">
        <div class="row no-border" style="margin:0;">

            @if ($alert = Session::get('alert'))
                @if ($alert == 'Success')
                    <div class="alert alert-success detail">
                        {{ $alert }}
                    </div>
                @else
                    <div class="alert alert-danger detail">
                        {{ $alert }}
                    </div>
                @endif
            @endif

            <div class="row category" style="margin:0;">Document Status</div>
            <div class="row no-border" style="margin:0;">
                <table class="table" style="font-size: 12px;">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>File Name</th>
                            <th>Created By</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $d)
                            <tr>
                                <td>{{ $d->type_name }}</td>
                                <td>
                                    @if (in_array(Auth::guard('admin')->user()->admin_id, [ 11, 12, 15, 18, 21,23, 26, 28, 29, 40, 44, 50, 52 ]))
                                        @if ($d->signed == 'Y' && (!empty($d->e_doc_id)))
                                            <a href="https://api.eversign.com/api/download_final_document?access_key=30581f03071c1cf3d21eda05fbf32c39&business_id=56514&document_hash={{ $d->e_doc_id }}" target="_blank">Show eSignature Document</a>
                                        @else
                                            @if (!empty($d->file_name))
                                                <a href="/admin/groomer/{{ $gr->groomer_id }}/document/{{ $d->id }}/view">
                                                    {{ $d->file_name }}
                                                </a>
                                            @endif
                                        @endif
                                    @else
                                        {{ $d->file_name }}
                                    @endif
                                </td>
                                <td>{{ empty($d->created_by) ? '' : $d->created_by }}</td>
                                <td>{{ empty($d->cdate) ? '' : $d->cdate }}</td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm" onclick="document_upload('{{
                                    $d->type }}', '{{ $d->type_name }}')">Upload</button>
                                    @if (!empty($d->id))
                                        @if ($d->verified == 'Y')
                                            Verified at {{ $d->verified_date }}
                                        @else
                                            <a href="/admin/groomer/{{ $gr->groomer_id }}/document/{{ $d->id
                                            }}/verified" class="btn btn-info btn-sm">Verified</a>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>

                <hr>
            </div>

            @if ($gr->profile_photo)
                <div class="col text-center">
                    <img src="data:image/png;base64,{{ $gr->profile_photo }}"/>
                </div>
            @endif


            <div class="col">
                <div class="row">
                    <div class="col-xs-3">Status</div>
                    <div class="col-xs-9">{{ $gr->status_name() }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Background Check / Trial</div>
                    <div class="col-xs-9">
                        @if($gr->background_check_status == 'G')
                            {{'Background Checks Progress'}}
                        @elseif($gr->background_check_status == 'R')
                            {{'Background Checks Rejected'}}
                        @elseif($gr->background_check_status == 'A')
                            {{'Background Checks Approved'}}
                        @elseif($gr->background_check_status == 'P')
                            {{'Background Checks Pending'}}
                        @elseif($gr->background_check_status == 'V')
                            {{'Video Trial Scheduled'}}
                        @elseif($gr->background_check_status == 'I')
                            {{'InPerson Trial Scheduled'}}
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Trial Date</div>
                    <div class="col-xs-9">{{ $gr->trial_notes }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Level</div>
                    <div class="col-xs-9">{{ $gr->level }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Dog</div>
                    <div class="col-xs-3">{{ $gr->dog }}</div>
                    <div class="col-xs-3">Cat</div>
                    <div class="col-xs-3">{{ $gr->cat }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-3">First Name</div>
                    <div class="col-xs-3">{{ $gr->first_name }}</div>
                    <div class="col-xs-3">Last Name</div>
                    <div class="col-xs-3">{{ $gr->last_name }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Email</div>
                    <div class="col-xs-3">{{ $gr->email }}</div>
                    <div class="col-xs-3">Mobile</div>
                    <div class="col-xs-3">{{ $gr->mobile_phone }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Gender</div>
                    <div class="col-xs-3">
                        @if($gr->sex == 'M')
                            {{'Male'}}
                        @elseif($gr->sex == 'F')
                            {{'Female'}}
                        @endif
                    </div>
                    <div class="col-xs-3">Transportation</div>
                    <div class="col-xs-3">
                        @if($gr->transportation == 'P')
                            {{'Public'}}
                        @elseif($gr->transportation == 'C')
                            {{'My Car'}}
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-3">How did you hear about us?</div>
                    <div class="col-xs-9">{{ $gr->groomer_how_knew_groomit }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Facebook Account</div>
                    <div class="col-xs-3">{{ $gr->f_account }}</div>
                    <div class="col-xs-3">Instagram Account</div>
                    <div class="col-xs-3">{{ $gr->i_account }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Service NY</div>
                    <div class="col-xs-3">{{ empty($gr->service_ny) ? "NO" : "YES" }}</div>
                    <div class="col-xs-3">Service NJ</div>
                    <div class="col-xs-3">{{ empty($gr->service_nj) ? "NO" : "YES" }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Service CT</div>
                    <div class="col-xs-3">{{ empty($gr->service_ct) ? "NO" : "YES" }}</div>
                    <div class="col-xs-3">Service Miami</div>
                    <div class="col-xs-3">{{ empty($gr->service_miami) ? "NO" : "YES" }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Service Philladelphia</div>
                    <div class="col-xs-3">{{ empty($gr->service_philladelphia) ? "NO" : "YES" }}</div>
                    <div class="col-xs-3">Service San diego</div>
                    <div class="col-xs-3">{{ empty($gr->service_sandiego) ? "NO" : "YES" }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Service other area</div>
                    <div class="col-xs-9">{{ empty($gr->service_other_area) ? "" : $gr->service_other_area }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Willing to Relocate</div>
                    <div class="col-xs-9">{{ $gr->relocate }}</div>
                </div>
{{--                <div class="row">--}}
{{--                    <div class="col-xs-3">Available in my area</div>--}}
{{--                    <div class="col-xs-9">{{ $gr->available_in_my_area }}</div>--}}
{{--                </div>--}}
                <div class="row">
                    <div class="col-xs-3">Address</div>
                    <div class="col-xs-9">{{ $gr->street }}, {{ $gr->city }} , {{ $gr->state }} {{ $gr->zip }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Bio</div>
                    <div class="col-xs-9">{{ $gr->bio }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Weekly Allowance($)</div>
                    <div class="col-xs-9">{{ $gr->weekly_allowance }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Bank Name</div>
                    <div class="col-xs-3">{{ $gr->bank_name }}</div>
                    <div class="col-xs-3">Account Holder Name</div>
                    <div class="col-xs-3">{{ $gr->account_holder }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Account #</div>
                    <div class="col-xs-3">{{ $gr->account_number }}</div>
                    <div class="col-xs-3">Routing #</div>
                    <div class="col-xs-3">{{ $gr->routing_number }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Service Area</div>
                    <div class="col-xs-9">{{ $gr->service_area }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Service Area2</div>
                    <div class="col-xs-9">

                        @foreach($gr->area as $ac)
                            @if ($ac->status == 'A')
                            <label>
                                {{ $ac->area_name }}
                                <button type="button" class="btn btn-xs btn-danger"
                                        onclick="remove_groomer_service_area('{{ $ac->area_name }}')">x</button>
                            </label>
                            @endif
                        @endforeach

                        <label>
                            <select id="available_service_area">
                                <option value="">Add Service Area</option>
                                <option value="ALL">ALL</option>
                                    @foreach ($gr->area as $ac)
                                        @if ($ac->status != 'A')
                                            <option value="{{ $ac->area_name }}">{{ $ac->area_name }}</option>
                                        @endif
                                    @endforeach
                            </select>
                            <button type="button" class="btn btn-xs btn-primary" onclick="add_groomer_service_area()">Add</button>
                        </label>
                        <script>
                            function remove_groomer_service_area(id) {
                                window.location.href = '/admin/groomer/remove_service_area/{{ $gr->groomer_id }}/' + id;
                            }

                            function add_groomer_service_area() {
                                if ($('#available_service_area').val() == '') {
                                    alert('Please select service area');
                                    return;
                                }
                                window.location.href = '/admin/groomer/add_service_area/{{ $gr->groomer_id }}/' + $('#available_service_area').val();
                            }
                        </script>
                    </div>
                </div>
{{--                <div class="row">--}}
{{--                    <div class="col-xs-3">Exclusive Area(Mon)</div>--}}
{{--                    <div class="col-xs-9">--}}
{{--                        @foreach($gr->exclusive_area as $ac)--}}
{{--                            @if ($ac->days == 0 )--}}
{{--                                @if ( isset($ac->weekday) && ($ac->weekday == 0) )--}}
{{--                                <label>--}}
{{--                                    {{ $ac->alias_name }}  {{ $ac->state }}--}}
{{--                                    <button type="button" class="btn btn-xs btn-danger" onclick="remove_groomer_exclusive_area( 0, '{{ $ac->alias_id }}')">x</button>--}}
{{--                                </label>--}}
{{--                                @endif--}}
{{--                            @endif--}}
{{--                        @endforeach--}}
{{--                        <label>--}}
{{--                            <select id="exclusive_area_0">--}}
{{--                                <option value="">Add Area</option>--}}
{{--                                @foreach ($gr->exclusive_area as $ac)--}}
{{--                                    @if ($ac->days == 0 )--}}
{{--                                        @if ( isset($ac->weekday) && ($ac->weekday == 0) )--}}
{{--                                        @else--}}
{{--                                        <option value="{{ $ac->alias_id }}">{{ $ac->alias_name }} {{ $ac->state }}</option>--}}
{{--                                        @endif--}}
{{--                                    @endif--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                            <button type="button" class="btn btn-xs btn-primary" onclick="add_groomer_exclusive_area( 0 )">Add</button>--}}
{{--                        </label>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="row">--}}
{{--                    <div class="col-xs-3">Exclusive Area(Tue)</div>--}}
{{--                    <div class="col-xs-9">--}}
{{--                        @foreach($gr->exclusive_area as $ac)--}}
{{--                            @if ($ac->days == 1 )--}}
{{--                                @if ( !empty($ac->weekday) && ($ac->weekday == 1) )--}}
{{--                                    <label>--}}
{{--                                        {{ $ac->alias_name }}  {{ $ac->state }}--}}
{{--                                        <button type="button" class="btn btn-xs btn-danger" onclick="remove_groomer_exclusive_area( 1, '{{ $ac->alias_id }}')">x</button>--}}
{{--                                    </label>--}}
{{--                                @endif--}}
{{--                            @endif--}}
{{--                        @endforeach--}}
{{--                        <label>--}}
{{--                            <select id="exclusive_area_1">--}}
{{--                                <option value="">Add Area</option>--}}
{{--                                @foreach ($gr->exclusive_area as $ac)--}}
{{--                                    @if ($ac->days == 1 )--}}
{{--                                        @if ( empty($ac->weekday) )--}}
{{--                                            <option value="{{ $ac->alias_id }}">{{ $ac->alias_name }} {{ $ac->state }}</option>--}}
{{--                                        @endif--}}
{{--                                    @endif--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                            <button type="button" class="btn btn-xs btn-primary" onclick="add_groomer_exclusive_area( 1 )">Add</button>--}}
{{--                        </label>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="row">--}}
{{--                    <div class="col-xs-3">Exclusive Area(Wed)</div>--}}
{{--                    <div class="col-xs-9">--}}
{{--                        @foreach($gr->exclusive_area as $ac)--}}
{{--                            @if ($ac->days == 2 )--}}
{{--                                @if ( !empty($ac->weekday) && ($ac->weekday == 2) )--}}
{{--                                    <label>--}}
{{--                                        {{ $ac->alias_name }}  {{ $ac->state }}--}}
{{--                                        <button type="button" class="btn btn-xs btn-danger" onclick="remove_groomer_exclusive_area( 2, '{{ $ac->alias_id }}')">x</button>--}}
{{--                                    </label>--}}
{{--                                @endif--}}
{{--                            @endif--}}
{{--                        @endforeach--}}
{{--                        <label>--}}
{{--                            <select id="exclusive_area_2">--}}
{{--                                <option value="">Add Area</option>--}}
{{--                                @foreach ($gr->exclusive_area as $ac)--}}
{{--                                    @if ($ac->days == 2 )--}}
{{--                                        @if ( empty($ac->weekday) )--}}
{{--                                            <option value="{{ $ac->alias_id }}">{{ $ac->alias_name }} {{ $ac->state }}</option>--}}
{{--                                        @endif--}}
{{--                                    @endif--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                            <button type="button" class="btn btn-xs btn-primary" onclick="add_groomer_exclusive_area( 2 )">Add</button>--}}
{{--                        </label>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="row">--}}
{{--                    <div class="col-xs-3">Exclusive Area(Thu)</div>--}}
{{--                    <div class="col-xs-9">--}}
{{--                        @foreach($gr->exclusive_area as $ac)--}}
{{--                            @if ($ac->days == 3 )--}}
{{--                                @if ( !empty($ac->weekday) && ($ac->weekday == 3) )--}}
{{--                                    <label>--}}
{{--                                        {{ $ac->alias_name }}  {{ $ac->state }}--}}
{{--                                        <button type="button" class="btn btn-xs btn-danger" onclick="remove_groomer_exclusive_area( 3, '{{ $ac->alias_id }}')">x</button>--}}
{{--                                    </label>--}}
{{--                                @endif--}}
{{--                            @endif--}}
{{--                        @endforeach--}}
{{--                        <label>--}}
{{--                            <select id="exclusive_area_3">--}}
{{--                                <option value="">Add Area</option>--}}
{{--                                @foreach ($gr->exclusive_area as $ac)--}}
{{--                                    @if ($ac->days == 3 )--}}
{{--                                        @if ( empty($ac->weekday) )--}}
{{--                                            <option value="{{ $ac->alias_id }}">{{ $ac->alias_name }} {{ $ac->state }}</option>--}}
{{--                                        @endif--}}
{{--                                    @endif--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                            <button type="button" class="btn btn-xs btn-primary" onclick="add_groomer_exclusive_area( 3 )">Add</button>--}}
{{--                        </label>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="row">--}}
{{--                    <div class="col-xs-3">Exclusive Area(Fri)</div>--}}
{{--                    <div class="col-xs-9">--}}
{{--                        @foreach($gr->exclusive_area as $ac)--}}
{{--                            @if ($ac->days == 4 )--}}
{{--                                @if ( !empty($ac->weekday) && ($ac->weekday == 4) )--}}
{{--                                    <label>--}}
{{--                                        {{ $ac->alias_name }}  {{ $ac->state }}--}}
{{--                                        <button type="button" class="btn btn-xs btn-danger" onclick="remove_groomer_exclusive_area( 4, '{{ $ac->alias_id }}')">x</button>--}}
{{--                                    </label>--}}
{{--                                @endif--}}
{{--                            @endif--}}
{{--                        @endforeach--}}
{{--                        <label>--}}
{{--                            <select id="exclusive_area_4">--}}
{{--                                <option value="">Add Area</option>--}}
{{--                                @foreach ($gr->exclusive_area as $ac)--}}
{{--                                    @if ($ac->days == 4 )--}}
{{--                                        @if ( empty($ac->weekday) )--}}
{{--                                            <option value="{{ $ac->alias_id }}">{{ $ac->alias_name }} {{ $ac->state }}</option>--}}
{{--                                        @endif--}}
{{--                                    @endif--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                            <button type="button" class="btn btn-xs btn-primary" onclick="add_groomer_exclusive_area( 4 )">Add</button>--}}
{{--                        </label>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="row">--}}
{{--                    <div class="col-xs-3">Exclusive Area(Sat)</div>--}}
{{--                    <div class="col-xs-9">--}}
{{--                        @foreach($gr->exclusive_area as $ac)--}}
{{--                            @if ($ac->days == 5 )--}}
{{--                                @if ( !empty($ac->weekday) && ($ac->weekday == 5) )--}}
{{--                                    <label>--}}
{{--                                        {{ $ac->alias_name }}  {{ $ac->state }}--}}
{{--                                        <button type="button" class="btn btn-xs btn-danger" onclick="remove_groomer_exclusive_area( 5, '{{ $ac->alias_id }}')">x</button>--}}
{{--                                    </label>--}}
{{--                                @endif--}}
{{--                            @endif--}}
{{--                        @endforeach--}}
{{--                        <label>--}}
{{--                            <select id="exclusive_area_5">--}}
{{--                                <option value="">Add Area</option>--}}
{{--                                @foreach ($gr->exclusive_area as $ac)--}}
{{--                                    @if ($ac->days == 5 )--}}
{{--                                        @if ( empty($ac->weekday) )--}}
{{--                                            <option value="{{ $ac->alias_id }}">{{ $ac->alias_name }} {{ $ac->state }}</option>--}}
{{--                                        @endif--}}
{{--                                    @endif--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                            <button type="button" class="btn btn-xs btn-primary" onclick="add_groomer_exclusive_area( 5 )">Add</button>--}}
{{--                        </label>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="row">--}}
{{--                    <div class="col-xs-3">Exclusive Area(Sun)</div>--}}
{{--                    <div class="col-xs-9">--}}
{{--                        @foreach($gr->exclusive_area as $ac)--}}
{{--                            @if ($ac->days == 6 )--}}
{{--                                @if ( !empty($ac->weekday) && ($ac->weekday == 6) )--}}
{{--                                    <label>--}}
{{--                                        {{ $ac->alias_name }}  {{ $ac->state }}--}}
{{--                                        <button type="button" class="btn btn-xs btn-danger" onclick="remove_groomer_exclusive_area( 6, '{{ $ac->alias_id }}')">x</button>--}}
{{--                                    </label>--}}
{{--                                @endif--}}
{{--                            @endif--}}
{{--                        @endforeach--}}
{{--                        <label>--}}
{{--                            <select id="exclusive_area_6">--}}
{{--                                <option value="">Add Area</option>--}}
{{--                                @foreach ($gr->exclusive_area as $ac)--}}
{{--                                    @if ($ac->days == 6 )--}}
{{--                                        @if ( empty($ac->weekday) )--}}
{{--                                            <option value="{{ $ac->alias_id }}">{{ $ac->alias_name }} {{ $ac->state }}</option>--}}
{{--                                        @endif--}}
{{--                                    @endif--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                            <button type="button" class="btn btn-xs btn-primary" onclick="add_groomer_exclusive_area( 6 )">Add</button>--}}
{{--                        </label>--}}
{{--                    </div>--}}
{{--                </div>--}}

                <div class="row">
                    <div class="col-xs-3">Service Packages</div>
                    <div class="col-xs-9">
                        @foreach($gr->packages as $p)
                            @if ($p->status == 'A')
                            <label>
                                {{ $p->pet_type . ', ' . $p->prod_name }}
                                <button type="button"
                                        class="btn btn-xs btn-danger"
                                        onclick="remove_groomer_service_package('{{ $p->prod_id }}')">x</button>
                            </label>
                            @endif
                        @endforeach

                        <label>
                            <select id="available_service_package">
                                <option value="">Add Service Package</option>
                                <option value="ALL">ALL</option>
                                @foreach($gr->packages as $p)
                                    @if ($p->status != 'A')
                                        <option value="{{ $p->prod_id }}">{{ $p->pet_type . ', ' . $p->prod_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-xs btn-primary" onclick="add_groomer_service_package()">Add</button>
                        </label>
                        <script>
                            function remove_groomer_service_package(prod_id) {
                                window.location.href = '/admin/groomer/remove_service_package/{{ $gr->groomer_id }}/' + prod_id;
                            }

                            function add_groomer_service_package() {
                                if ($('#available_service_package').val() == '') {
                                    alert('Please select service package');
                                    return;
                                }
                                window.location.href = '/admin/groomer/add_service_package/{{ $gr->groomer_id }}/' + $
                                ('#available_service_package').val();
                            }
                        </script>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Blocked Breeds</div>
                    <div class="col-xs-9">
                        @foreach($gr->breeds as $p)
                            @if ($p->status == 'A')
                                <label>
                                    {{ $p->breed_name }}
                                    <button type="button"
                                            class="btn btn-xs btn-danger"
                                            onclick="remove_groomer_blocked_breed('{{ $p->breed_id }}')">x</button>
                                </label>
                            @endif
                        @endforeach

                        <label>
                            <select id="available_blocked_breed">
                                <option value="">Add Blocked Breeds</option>
                                @foreach($gr->breeds as $p)
                                    @if ($p->status != 'A')
                                        <option value="{{ $p->breed_id }}">{{ $p->breed_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-xs btn-primary" onclick="add_groomer_blocked_breed()">Add</button>
                        </label>
                        <script>
                            function remove_groomer_blocked_breed(breed_id) {
                                window.location.href = '/admin/groomer/remove_blocked_breed/{{ $gr->groomer_id }}/' + breed_id;
                            }

                            function add_groomer_blocked_breed() {
                                if ($('#available_blocked_breed').val() == '') {
                                    alert('Please select blocked Breeds');
                                    return;
                                }
                                window.location.href = '/admin/groomer/add_blocked_breed/{{ $gr->groomer_id }}/' + $('#available_blocked_breed').val();
                            }
                        </script>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Notification Type</div>
                    <div class="col-xs-9">
                        @foreach($gr->notification as $p)
                            @if ($p->status == 'A')
                                <label>
                                    {{ $p->notification_name }}
                                    <button type="button"
                                            class="btn btn-xs btn-danger"
                                            onclick="remove_groomer_notification_type('{{ $p->notification_id }}')">x</button>
                                </label>
                            @endif
                        @endforeach

                        <label>
                            <select id="available_notification_type">
                                <option value="">Add Notification Types</option>
                                <option value="ALL">ALL</option>
                                @foreach($gr->notification as $p)
                                    @if ($p->status != 'A')
                                        <option value="{{ $p->notification_id }}">{{ $p->notification_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-xs btn-primary" onclick="add_groomer_notification_type()">Add</button>
                        </label>
                        <script>
                            function remove_groomer_notification_type(notification_id) {
                                window.location.href = '/admin/groomer/remove_notification_type/{{ $gr->groomer_id }}/' + notification_id;
                            }

                            function add_groomer_notification_type() {
                                if ($('#available_notification_type').val() == '') {
                                    alert('Please select Notification Type');
                                    return;
                                }
                                window.location.href = '/admin/groomer/add_notification_type/{{ $gr->groomer_id }}/' + $('#available_notification_type').val();
                            }
                        </script>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Log Out?</div>
                    <div class="col-xs-9">
                        @if ($gr->device_token == null)
                            Yes
                        @else
                            No
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Rating</div>
                    <div class="col-xs-9">{{ empty($gr->groomer_stat) ?
                                            'No rating yet' :
                                            ( !empty( $gr->groomer_stat[0]->rating_avg ) ?
                                                    '[AVG Ratings: ' .number_format($gr->groomer_stat[0]->rating_avg, 2) . '] [Rated Appts: ' . $gr->groomer_stat[0]->rating_qty . ']'
                                                    : ''
                                            ) }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Revenues/Earnings </div>
                    <div class="col-xs-9">{{ empty($gr->groomer_stat) ?
                                            '[Total Appts: 0][Total Revenues: $0] ' :
                                            ( !empty( $gr->groomer_stat[0]->book_cnt ) ?
                                                    '[Total Appts: ' . $gr->groomer_stat[0]->book_cnt . '] [Total Revenues: $'  .  number_format($gr->groomer_stat[0]->revenue_total, 2) . '] ' :
                                                   '[Total Appts: 0][Total Revenues: $0] '
                                            ) }}
                        <br/>
                        {{ empty($gr->earnings) ?
                         '[Earnings[$ 0]' :
                         ( !empty( $gr->earnings[0]->earn_appt ) ?
                                 '[Earnings by Appt: $' . number_format($gr->earnings[0]->earn_appt, 2) . ']'  .
                                 '[Earnings by Tip: $' . number_format($gr->earnings[0]->earn_tip, 2) . ']'  .
                                 '[Earnings by Adjust: $' . number_format($gr->earnings[0]->earn_adjust, 2) . ']'  .
                                 '[Earnings by Refer: $' . number_format($gr->earnings[0]->earn_refer, 2) . ']'
                                 :
                                 ''
                         ) }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Groomer Referal Code</div>
                    <div class="col-xs-9">
                        @foreach($promocodes as $p)
                            <label>
                                {{ $p->code }}
                                <button type="button" class="btn btn-xs btn-danger"
                                        onclick="remove_promocode('{{ $p->code }}')">x</button>
                            </label>
                        @endforeach
                        @if(!count($promocodes) > 0)
                            <input type="text" id="n_promocode" style="width: 100px" class="form-control">
                            <button type="button" class="btn btn-xs btn-primary" onclick="add_promocode()">Add</button>
                        @endif
                        <script>
                            function remove_promocode(code) {
                                window.location.href = '/admin/groomer/{{ $gr->groomer_id }}/remove_promocode/' + code;
                            }

                            function add_promocode() {
                                if ($('#n_promocode').val() == '') {
                                    alert('Please select service area');
                                    return;
                                }
                                window.location.href = '/admin/groomer/{{ $gr->groomer_id }}/add_promocode/' + $('#n_promocode').val();
                            }
                        </script>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Number of favorite users</div>
                    <div class="col-xs-9"><a href="/admin/reports/groomer-fav/{{ $gr->groomer_id }}" target="_blank">{{ $gr->fav_user_num }}</a></div>
                </div>


                <div class="row">
                    <div class="col-xs-3">General Notes</div>
                    <div class="col-xs-9">{{ $gr->general_notes }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-3">TEXT APPT</div>
                    <div class="col-xs-9">
                        @if ($gr->text_appt == 'Y')
                            Yes
                        @else
                            No
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Phone Interview Notes</div>
                    <div class="col-xs-1">
                        <div class="btn-left btn btn-success" data-toggle="modal" data-target="#phone_notes">Update Notes</div>
                    </div>
                    <div class="col-xs-8">
                        @if ($gr->phone_interview_notes == null)
                            No notes yet
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Trial Interview Notes</div>
                    <div class="col-xs-1">
                        <div class="btn-left btn btn-success" data-toggle="modal" data-target="#trial_notes">Update Notes</div>
                    </div>
                    <div class="col-xs-8">
                        @if ($gr->trial_interview_notes == null)
                            No notes yet
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">CS Notes</div>
                    <div class="col-xs-6">
                        <textarea id="cs_notes" name="cs_notes" style="width:100%" rows="5">{{ $gr->cs_notes }}</textarea>
                    </div>
                    <div class="col-xs-3 text-center">
                        <button type="button" class="btn btn-danger" onclick="update_cs_notes()">Update</button>
                    </div>
                </div>

            </div>
        </div>

        <div class="row category">EXPERIENCE</div>
        <div class="row">
            <div class="col-xs-12 question">Have you worked as a dog groomer before?</div>
            <div class="col-xs-12 answer">
                @if($gr->groomer_exp == 'Y')
                    Yes
                @else
                    No
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 question">Have you worked as a bather before?</div>
            <div class="col-xs-12 answer">
                @if($gr->bather_exp == 'Y')
                    Yes
                @else
                    No
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 question">Where did you learn to groom?</div>
            <div class="col-xs-12 answer">
                {{ $gr->groomer_exp_note }}
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 question">What do you groom?</div>
            <div class="col-xs-12 answer">
                @if($gr->groomer_target == 'D')
                    Dogs
                @elseif($gr->groomer_target == 'C')
                    Cats
                @elseif($gr->groomer_target == 'B')
                    Both (Dogs and Cats)
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 question">Are you comfortable to groom within customers home/office?</div>
            <div class="col-xs-12 answer">
                @if($gr->comfortable == 'Y')
                    Yes
                @else
                    No
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 question">Do you have a drivers license?</div>
            <div class="col-xs-12 answer">
                @if($gr->driver_license == 'Y')
                    Yes
                @else
                    No
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 question">Safety is our priority, do you agree on a 3rd party background check?</div>
            <div class="col-xs-12 answer">
                @if($gr->agree_to_bg_check == 'Y')
                    Yes
                @else
                    No
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 question">Do you have any certifications?</div>
            <div class="col-xs-12 answer">
                @if($gr->groomer_edu == 'Y')
                    Yes
                @else
                    No
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 question">Certification List</div>
            @if ($gr->groomer_edu_dog_grooming == 'Y')
            <div class="col-xs-12 answer">
                Dog Grooming
            </div>
            @endif
            @if ($gr->groomer_edu_cat_grooming == 'Y')
            <div class="col-xs-12 answer">
                Cat Grooming
            </div>
            @endif
            @if ($gr->groomer_edu_pet_safety_cpr == 'Y')
            <div class="col-xs-12 answer">
                Pet Safety / CPR
            </div>
            @endif
            @if ($gr->groomer_edu_breed_standards == 'Y')
            <div class="col-xs-12 answer">
                Breed Standards
            </div>
            @endif
            @if ($gr->groomer_edu_other == 'Y')
            <div class="col-xs-12 answer">
                Other - {{ $gr->groomer_edu_note }}
            </div>
            @endif
        </div>

        <div class="row">
            <div class="col-xs-12 question">How long experience with Grooming?</div>
            <div class="col-xs-12 answer">
                @if($gr->groomer_exp_years == 1)
                    Less than 1 year
                @elseif($gr->groomer_exp_years == 2)
                    2 years or more
                @elseif($gr->groomer_exp_years == 5)
                    5 years or more
                @elseif($gr->groomer_exp_years == 10)
                    10 years or more
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 question">How many dogs do you groom in average a month?</div>
            <div class="col-xs-12 answer">
                @if($gr->groom_per_month == 10)
                    Less than 10
                @elseif($gr->groom_per_month == 50)
                    More than 50
                @elseif($gr->groom_per_month == 100)
                    More than 100
                @endif
            </div>
        </div>

        <div class="row category">REFERENCE</div>

        <div class="row">
            <div class="col-xs-12 question">Tell us about yourself</div>
            <div class="col-xs-12 answer">
                {{ $gr->groomer_edu_note }}
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 question">recent groomings</div>
            <div class="col-xs-12 answer text-center">
                <img src="data:image/png;base64,{{ $gr->pet_photo['data'] }}"/>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 question">Please provide references</div>
            <div class="col-xs-12 answer">
                {{ $gr->groomer_references }}
            </div>
        </div>

        <div class="row category">GROOMING TOOLS</div>
        <div class="row no-border">
            <div class="col-xs-12 question">Which of these tools do you have?</div>
            <div class="col-xs-12 answer">
                @if ($gr->have_tool == 'Y')
                @if(is_array($gr->tools))
                    @foreach($gr->tools as $tool)
                        {{ $tool->name }} <br>
                    @endforeach
                @endif
                @endif
            </div>
        </div>

    </div>
</div>
@stop