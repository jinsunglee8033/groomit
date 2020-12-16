@extends('includes.admin_default')
@section('contents')

    <script type="text/javascript">
        window.onload = function() {
            $( "#pdate" ).datetimepicker({
                format: 'YYYY-MM-DD hh:mm'
            });
        };

        function update_groomer() {
            $('#frm_update').submit();
        }
    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />Application Detail</h3>
    </div>

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">


            <div class="row">
                <div class="col-md-2">

                </div>
                <div class="col-md-10 text-right">
                    <div class="form-group">

                        <div class="btn-right btn btn-success" data-toggle="modal" data-target="#update">Update Information</div>

                        <a class="btn-right btn btn-info" onclick="window.print()">PRINT</a>

                        <script>
                            function application_update_status(status) {
                                $('#u_status').val(status);
                                $('#form_update_status').submit();
                            }
                        </script>
                        <form method="post" id="form_update_status" action="/admin/application/status"
                        class="form-group">
                            {!! csrf_field() !!}
                            <input type="hidden" name="id" value="{{$a->id}}" />
                            <input type="hidden" id="u_status" name="status"/>
                        </form>

                        @if ($a->status == 'N')
                        <a class="btn-right btn btn-danger" onclick="application_update_status('T')">ON TRIAL</a>
                        <a class="btn-right btn btn-danger" onclick="application_update_status('C')">CONTACTED</a>
                        <a class="btn-right btn btn-danger" onclick="application_maybe()">MAYBE</a>
                        <a class="btn-right btn btn-danger" onclick="application_reject()">REJECT</a>
                        <div class="approve">
                            <form method="post" name="approve" action="/admin/application/approve" class="form-group">
                                    {!! csrf_field() !!}
                                <button class="btn-right btn btn-success">APPROVE</button>
                                <select name="level" class="form-control">
                                    <option value="">Select Level</option>
                                    <option value="1">Level 1 </option>
                                    <option value="2">Level 2 </option>
                                    <option value="3">Level 3 </option>
                                    <option value="4">Level 4 </option>
                                    <option value="5">Level 5 </option>
                                </select>
                                <input type="hidden" name="id" value="{{$a->id}}" />
                            </form>
                        </div>
                        @else
                            @if (\App\Lib\Helper::get_action_privilege('application_status', 'Application Status'))
                                @if ($a->status !== 'A')
                                <a class="btn-right btn btn-danger" onclick="application_update_status('N')">New</a>
                                @if ($a->status !== 'T')
                                <a class="btn-right btn btn-danger" onclick="application_update_status('T')">ON TRIAL</a>
                                @endif
                                @if ($a->status !== 'C')
                                <a class="btn-right btn btn-danger" onclick="application_update_status('C')">CONTACTED</a>
                                @endif
                                <a class="btn-right btn btn-danger" onclick="application_reject()">REJECT</a>
                                @if ($a->status !== 'M')
                                <a class="btn-right btn btn-danger" onclick="application_maybe()">MAYBE</a>
                                @endif
                                @endif
                            <div class="approve">
                                <form method="post" name="approve" action="/admin/application/approve" class="form-group">
                                        {!! csrf_field() !!}
                                    <button class="btn-right btn btn-success">APPROVE</button>
                                    <select name="level" class="form-control">
                                        <option value="">Select Level</option>
                                        <option value="1">Level 1 </option>
                                        <option value="2">Level 2 </option>
                                        <option value="3">Level 3 </option>
                                        <option value="4">Level 4 </option>
                                        <option value="5">Level 5 </option>
                                    </select>
                                    <input type="hidden" name="id" value="{{$a->id}}" />
                                </form>
                            </div>
                            @endif
                        @endif

                        @if ($a->status == 'A')
                            <div class="btn-right btn btn-info">APPROVED</div>
                        @else
                            @php
                                $preapproved = \App\Model\Groomer::where('application_id', $a->id)->first();
                            @endphp
                            @if (empty($preapproved))
                                @if (\App\Lib\Helper::get_action_privilege('application_preapprove', 'Application
                                Preapprove'))
                                <a href="/admin/application/preapprove/{{ $a->id }}" class="btn btn-info">Pre Approve</a>
                                @endif
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($a->status == 'R')
            <div class="alert alert-danger detail" style="text-align: left;">
                {{ '[REJECTED] ' . $a->reject_reason . '. ' . (empty($a->reject_notes) ? '' : $a->reject_notes) }}
            </div>

        @else 
            @if ($a->status == 'M')
                <div class="alert alert-danger detail" style="text-align: left;">
                    {{ '[MAYBE] ' . $a->reject_reason . '. ' . (empty($a->reject_notes) ? '' : $a->reject_notes) }}
                </div>
            @else
                <div class="alert alert-danger detail" style="text-align: left;">
                    {{ 'Current Status: ' . \App\Model\Application::status_name($a->status) }}
                </div>
            @endif
        @endif

        @if ($alert = Session::get('alert'))
            @if ($alert == 'Success')
                <div class="alert alert-success detail" style="text-align: left;">
                    {{ $alert }}
                </div>
            @else
                <div class="alert alert-danger detail" style="text-align: left;">
                    {{ $alert }}
                </div>
            @endif
        @endif
    </div>

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
                    <form method="post" id="frm_update" name="frm_update" action="/admin/application/update" class="form-group"
                          enctype="multipart/form-data">
                        {!! csrf_field() !!}

                        <input type="hidden" name="id" value="{{$a->id}}"/>

                        <div class="row no-border">
                            <div class="col-xs-3">Profile Photo</div>
                            <div class="col-xs-8">
                                <div class="text-center">
                                    @if ($a->profile_photo)
                                        <img id="img_profile_photo"
                                             src="data:image/png;base64,{{ $a->profile_photo }}"/>
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
                            <div class="col-xs-3">Dog</div>
                            <div class="col-xs-2">
                                <input type="checkbox" name="dog"
                                       value="{{ $a->dog }}" {{ $a->dog == 'Y' ? 'checked' : '' }}/>
                            </div>
                        </div>
                        <div class="row no-border">
                            <div class="col-xs-3">Cat</div>
                            <div class="col-xs-2">
                                <input type="checkbox" name="cat"
                                       value="{{ $a->cat }}" {{ $a->cat == 'Y' ? 'checked' : '' }}/>
                            </div>
                        </div>
                        <div class="row no-border">
                            <div class="col-xs-3">First Name</div>
                            <div class="col-xs-9">
                                <input type="text" class="form-control" name="first_name"
                                       value="{{ $a->first_name }}"/>
                            </div>
                        </div>
                        <div class="row no-border">
                            <div class="col-xs-3">Last Name</div>
                            <div class="col-xs-9">
                                <input type="text" class="form-control" name="last_name"
                                       value="{{ $a->last_name }}"/>
                            </div>
                        </div>
                        <div class="row no-border">
                            <div class="col-xs-3">Mobile</div>
                            <div class="col-xs-9">
                                <input type="text" class="form-control" name="mobile_phone"
                                       value="{{ $a->mobile_phone }}"/>
                            </div>
                        </div>
                        <div class="row no-border">
                            <div class="col-xs-3">Email</div>
                            <div class="col-xs-9">
                                <input type="email" class="form-control" name="email" value="{{ $a->email }}"/>
                            </div>
                        </div>
                        <div class="row no-border">

                            <div class="col-xs-3">Available in my area</div>
                            <div class="col-xs-9">
                                <input type="text" class="form-control" name="available_in_my_area"
                                       value="{{ $a->groomer_exp_note }}"/>
                            </div>

                            <div class="col-xs-3">Address</div>
                            <div class="col-xs-9">
                                <input type="text" class="form-control" name="street"
                                       value="{{ $a->street }}"/>
                            </div>
                            <div class="col-xs-3">City</div>
                            <div class="col-xs-9">
                                <input type="text" class="form-control" name="city" value="{{ $a->city }}"/>
                            </div>
                            <div class="col-xs-3">State</div>
                            <div class="col-xs-9">
                                <input type="text" class="form-control small" name="state"
                                       value="{{ $a->state }}" maxlength="2"/>
                            </div>
                            <div class="col-xs-3">Zip</div>
                            <div class="col-xs-9">
                                <input type="text" class="form-control small" name="zip" value="{{ $a->zip }}"
                                       maxlength="5"/>
                            </div>
                        </div>
                        <div class="row no-border">
                            <div class="col-xs-3">Bio</div>
                            <div class="col-xs-9">
                                <textarea cols="4" class="form-control" name="bio">{{ $a->bio }}</textarea>
                            </div>
                        </div>

                        <div class="row no-border">
                            <div class="col-xs-3">Have you worked as a dog groomer before?</div>
                            <div class="col-xs-2">
                                <input type="checkbox" name="groomer_exp" value="{{ $a->groomer_exp }}"
                                        {{ $a->groomer_exp == 'Y' ? 'checked' : '' }}/>
                            </div>
                        </div>

                        <div class="row no-border">
                            <div class="col-xs-3">Have you worked as a bather before?</div>
                            <div class="col-xs-2">
                                <input type="checkbox" name="bather_exp" value="{{ $a->bather_exp }}"
                                        {{ $a->bather_exp == 'Y' ? 'checked' : '' }}/>
                            </div>
                        </div>

                        <div class="row no-border">
                            <div class="col-xs-3">Where did you learn your skills?</div>
                            <div class="col-xs-9">
                            <textarea cols="4" class="form-control"
                                      name="groomer_exp_note">{{ $a->groomer_exp_note }}</textarea>
                            </div>
                        </div>

                        <div class="row no-border">
                            <div class="col-xs-3">Service Area</div>
                            <div class="col-xs-9">
                                <input type="text" class="form-control" name="service_area"
                                       value="{{ $a->service_area }}"/>
                            </div>
                        </div>
                        <div class="row no-border">
                            <div class="col-xs-3">General Notes</div>
                            <div class="col-xs-9">
                                <textarea cols="4" class="form-control" name="general_notes">{{ $a->general_notes }}</textarea>
                            </div>
                        </div>

                        <div class="row no-border">
                            <div class="col-xs-3">TEXT APPT</div>
                            <div class="col-xs-6">
                                <select name="text_appt" class="form-control">
                                    <option value="Y" {{ old('text_appt', $a->text_appt) == 'Y' ? 'selected' : '' }}>
                                        YES
                                    </option>
                                    <option value="N" {{ old('text_appt', $a->text_appt) == 'N' ? 'selected' : '' }}>
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


<div class="container-fluid">
    <div id="section-to-print" class="detail application">
        <div class="row category" style="margin:0;">Application History</div>
        <div class="row no-border" style="margin:0;">
            <table class="table" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Notes</th>
                        <th>Date & Time</th>
                        <th>Groomer</th>
                        <th>Amount</th>
                        <th>Created By</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @php
                    $trial_completed = false;
                @endphp
                @if (!empty($histories) && count($histories) > 0)
                @foreach ($histories as $his)
                    <tr>
                        <td>
                            @php
                                switch($his->status) {
                                    case 3:
                                    echo 'Email Sent';
                                    break;
                                    case 4:
                                    echo 'Email Communication';
                                    break;
                                    case 6:
                                    echo 'Phone Call';
                                    break;
                                    case 7:
                                    echo 'Text Message';
                                    break;
                                    case 1:
                                    echo 'Trial Scheduled';
                                    break;
                                    case 5:
                                    $trial_completed = true;
                                    echo 'Trial In Progress';
                                    break;
                                    case 2:
                                    $trial_completed = true;
                                    echo 'Trial Completed';
                                    break;
                                    case 9:
                                    echo 'Other';
                                    break;
                                }
                            @endphp
                        </td>
                        <td>{{ $his->notes }}</td>
                        <td>{{ $his->pdate }}</td>
                        <td>{{ $his->status == '2' ? $his->groomer_id . ', ' . $his->groomer_name : '' }}</td>
                        <td style="text-align: right;">$ {{ $his->status == '2' ? $his->amt : '' }}</td>
                        <td>{{ $his->created_by }}</td>
                        <td>{{ $his->cdate }}</td>
                        <td></td>
                    </tr>
                @endforeach
                @endif
                </tbody>
                <tfoot>
                <form method="post" action="/admin/application/update-status" class="form-group">
                    {!! csrf_field() !!}
                    <input type="hidden" name="id" value="{{ $a->id }}">
                    <tr>
                        <th>
                            <select id="application_status" name="status" class="form-control"
                                    onchange="change_application_status()">
                                <option value="">Select Status</option>
                                <option value="3">Email Sent </option>
                                <option value="4">Email Communication </option>
                                <option value="6">Phone Call </option>
                                <option value="7">Text Message </option>
                                <option value="1">Trial Scheduled </option>
                                <option value="5">Trial In Progress </option>
                                <option value="2">Trial Completed </option>
                                <option value="9">Other </option>
                            </select>
                        </th>
                        <th>
                            <textarea name="notes" class="form-control" style="width: 300px;height: 100px;"></textarea>
                        </th>
                        <th>
                            <input id="pdate" name="pdate" type="text" class="form-control" style="max-width: 240px;">
                        </th>
                        <th>
                            <select id="application_groomer" name="groomer_id" class="form-control" style="display:
                            none;">
                                @foreach ($groomers as $g)
                                    <option value="{{ $g->groomer_id }}" {{ $g->groomer_id == 39 ? 'selected' : '' }}>
                                        {{ $g->first_name . ' ' . $g->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </th>
                        <th>
                            <input id="application_amt" name="amt" type="text" class="form-control" style="max-width:
                            240px; display: none;" placeholder="Amount">
                        </th>
                        <th></th>
                        <th></th>
                        <th style="text-align: right;">
                            @if (\App\Lib\Helper::get_action_privilege('application_update', 'Application Update'))
                            <button type="submit" class="btn-right btn btn-success">Update</button>
                                @endif
                        </th>
                    </tr>
                </form>
                </tfoot>
            </table>
            <hr>
        </div>

        <script type="text/javascript">
            function change_application_status() {
                var status = $('#application_status').val();
                if (status == '2') {
                    $('#application_groomer').show();
                    $('#application_amt').show();
                } else {
                    $('#application_groomer').hide();
                    $('#application_amt').hide();
                }
            }
        </script>

        @if ($trial_completed)
        <div class="row category" style="margin:0;">Trial Document Status</div>
        <div class="row no-border" style="margin:0;">
            <table class="table" style="font-size: 12px;">
                <thead>
                <tr>
                    <th>Type</th>
                    <th>File</th>
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
                            @if (!empty($d->data))
                            <img src="data:image/png;base64,{{ $d->data }}"/>
                            @endif
                        </td>
                        <td>{{ empty($d->created_by) ? '' : $d->created_by }}</td>
                        <td>{{ empty($d->cdate) ? '' : $d->cdate }}</td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-info btn-sm" onclick="document_upload('{{
                                    $d->type }}', '{{ $d->type_name }}')">Upload</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                </tfoot>
            </table>

            <hr>
        </div>
        @endif

        <div class="row no-border">
            @if ($a->profile_photo)
                <div class="col text-center">
                    <img src="data:image/png;base64,{{ $a->profile_photo }}"/>
                </div>
            @endif
            <div class="col">
                <div class="row">
                    <div class="col-xs-3">Name</div>
                    <div class="col-xs-9">{{ $a->first_name }} {{ $a->last_name }}</div>
                </div>
{{--                <div class="row">--}}
{{--                    <div class="col-xs-3">Phone</div>--}}
{{--                    <div class="col-xs-9">{{ $a->phone }}</div>--}}
{{--                </div>--}}
                <div class="row">
                    <div class="col-xs-3">Mobile</div>
                    <div class="col-xs-9">{{ $a->mobile_phone }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Email</div>
                    <div class="col-xs-9">{{ $a->email }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">How did you hear about us?</div>
                    <div class="col-xs-9">{{ $a->groomer_how_knew_groomit }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Facebook Account</div>
                    <div class="col-xs-9">{{ $a->f_account }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Instagram Account</div>
                    <div class="col-xs-9">{{ $a->i_account }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Service NY</div>
                    <div class="col-xs-9">{{ empty($a->service_ny) ? "NO" : "YES" }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Service NJ</div>
                    <div class="col-xs-9">{{ empty($a->service_nj) ? "NO" : "YES" }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Service CT</div>
                    <div class="col-xs-9">{{ empty($a->service_ct) ? "NO" : "YES" }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Service Miami</div>
                    <div class="col-xs-9">{{ empty($a->service_miami) ? "NO" : "YES" }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Service Philladelphia</div>
                    <div class="col-xs-9">{{ empty($a->service_philladelphia) ? "NO" : "YES" }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Service San diego</div>
                    <div class="col-xs-9">{{ empty($a->service_sandiego) ? "NO" : "YES" }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Service Other</div>
                    <div class="col-xs-9">{{ $a->service_other_area }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Willing to Relocate</div>
                    <div class="col-xs-9">{{ $a->relocate }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Available in my area</div>
                    <div class="col-xs-9">{{ $a->available_in_my_area }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Address</div>
                    <div class="col-xs-9">{{ $a->street }}, {{ $a->city }} , {{ $a->state }} {{ $a->zip }}</div>
                </div>
{{--                <div class="row">--}}
{{--                    <div class="col-xs-3">Bio</div>--}}
{{--                    <div class="col-xs-9">{{ $a->bio }}</div>--}}
{{--                </div>--}}
            </div>
        </div>

        <div class="row category">EXPERIENCE</div>
        <div class="row">
            <div class="col-xs-12 question">Have you worked as a dog groomer before?</div>
            <div class="col-xs-12 answer">
                @if($a->groomer_exp == 'Y')
                    Yes
                @else
                    No
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 question">Have you worked as a bather before?</div>
            <div class="col-xs-12 answer">
                @if($a->bather_exp == 'Y')
                    Yes
                @else
                    No
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 question">Where did you learn your skills?</div>
            <div class="col-xs-12 answer">
                {{ $a->groomer_exp_note }}
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 question">What do you groom?</div>
            <div class="col-xs-12 answer">
                @if($a->groomer_target == 'D')
                    Dogs
                @elseif($a->groomer_target == 'C')
                    Cats
                @elseif($a->groomer_target == 'B')
                    Both (Dogs and Cats)
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 question">Are you comfortable to groom within customers home/office?</div>
            <div class="col-xs-12 answer">
                {{ ($a->comfortable == 'Y') ? "YES" : "NO" }}
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 question">Do you have a drivers license?</div>
            <div class="col-xs-12 answer">
                {{ ($a->driver_license == 'Y') ? "YES" : "NO" }}
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 question">Safety is our priority, do you agree on a 3rd party background check?</div>
            <div class="col-xs-12 answer">
                @if($a->agree_to_bg_check == 'Y')
                    Yes
                @else
                    No
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 question">Do you have any certifications?</div>
            <div class="col-xs-12 answer">
                @if($a->groomer_edu == 'Y')
                    Yes
                @else
                    No
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 question">Certification List</div>
            @if ($a->groomer_edu_dog_grooming == 'Y')
            <div class="col-xs-12 answer">
                Dog Grooming
            </div>
            @endif
            @if ($a->groomer_edu_cat_grooming == 'Y')
            <div class="col-xs-12 answer">
                Cat Grooming
            </div>
            @endif
            @if ($a->groomer_edu_pet_safety_cpr == 'Y')
            <div class="col-xs-12 answer">
                Pet Safety / CPR
            </div>
            @endif
            @if ($a->groomer_edu_breed_standards == 'Y')
            <div class="col-xs-12 answer">
                Breed Standards
            </div>
            @endif
            @if ($a->groomer_edu_other == 'Y')
            <div class="col-xs-12 answer">
                Other - {{ $a->groomer_edu_note }}
            </div>
            @endif
        </div>

        <div class="row">
            <div class="col-xs-12 question">How long experience with Grooming</div>
            <div class="col-xs-12 answer">
                @if($a->groomer_exp_years == 1)
                    Less than 1 year
                @elseif($a->groomer_exp_years == 2)
                    2 years or more
                @elseif($a->groomer_exp_years == 5)
                    5 years or more
                @elseif($a->groomer_exp_years == 10)
                    10 years or more
                @endif
            </div>
        </div>

        <div class="row category">REFERENCE</div>

        <div class="row">
            <div class="col-xs-12 question">Tell us about yourself</div>
            <div class="col-xs-12 answer">
                {{ $a->groomer_edu_note }}
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 question">recent groomings</div>
            <div class="col-xs-12 answer text-center">
                <img src="data:image/png;base64,{{ $a->pet_photo['data'] }}"/>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 question">Please provide references</div>
            <div class="col-xs-12 answer">
                {{ $a->groomer_references }}groomer_references
            </div>
        </div>

        <div class="row category">GROOMING TOOLS</div>
        <div class="row no-border">
            <div class="col-xs-12 question">Which of these tools do you have?</div>
            <div class="col-xs-12 answer">
                @if ($a->have_tool == 'Y')
                @if(is_array($a->tools))
                    @foreach($a->tools as $tool)
                        {{ $tool->name }} <br>
                    @endforeach
                @endif
                @endif
            </div>
        </div>



        <table class="no-page-break">
            <tr>
                <td>
                    <div class="row category ">AVAILABILITY</div>
                    <div class="row">

            <div class="col-xs-12 question">What days and what times are you available to work with us?</div>

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

                <div class="row no-border">
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


                <div class="row no-border">
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


                <div class="row no-border">
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


                <div class="row no-border">
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


                <div class="row no-border">
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


                <div class="row no-border">
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

                <div class="row no-border">
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

            </div>
        </div>
                </td>
            </tr>
        </table>
    </div>
</div>


        <!-- Send Modal Start -->
        <div class="modal" id="modal_reject_reason" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">REJECT REASON</h4>
                    </div>
                    <div class="form-group">
                        <form method="post" action="/admin/application/reject" class="form-group">
                            {!! csrf_field() !!}
                            <input type="hidden" name="id" value="{{$a->id}}" />
                            <div class="modal-body">
                                <div class="row padding-10">
                                    <div class="col-xs-3">Reject Reason</div>
                                    <div class="col-xs-8">
                                        <select class="form-control" name="reject_reason">
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row padding-10">
                                    <div class="col-xs-3">Reject Notes</div>
                                    <div class="col-xs-8">
                                        <textarea name="reject_notes" rows="5" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-danger" type="submit">Submit</button>
                                <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Send Modal End -->

        <script type="text/javascript">
            function application_reject() {
                $('#modal_reject_reason').modal();
            }
        </script>

        <!-- Send Modal Start -->
        <div class="modal" id="modal_maybe_reason" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">REJECT REASON</h4>
                    </div>
                    <div class="form-group">
                        <form method="post" action="/admin/application/maybe" class="form-group">
                            {!! csrf_field() !!}
                            <input type="hidden" name="id" value="{{$a->id}}" />
                            <div class="modal-body">
                                <div class="row padding-10">
                                    <div class="col-xs-3">Reject Reason</div>
                                    <div class="col-xs-8">
                                        <select class="form-control" name="reject_reason">
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row padding-10">
                                    <div class="col-xs-3">Reject Notes</div>
                                    <div class="col-xs-8">
                                        <textarea name="reject_notes" rows="5" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-danger" type="submit">Submit</button>
                                <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Send Modal End -->

        <script type="text/javascript">
            function application_maybe() {
                $('#modal_maybe_reason').modal();
            }
        </script>


    <!-- Send Modal End -->
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
                <form method="post" action="/admin/application/{{ $a->id }}/document/upload" class="form-group"
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
@stop