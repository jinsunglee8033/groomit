@extends('includes.admin_default')
@section('contents')

    <script type="text/javascript">
        window.onload = function() {
            $( "#sdate" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });
            $( "#edate" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });

            $('#table tbody').children('tr').css('cursor','pointer');

            $('#table tbody').on('click', 'td', function () {
                if (this.id) {
                    window.location.href = '/admin/' + this.id;
                }
            });
        };

        function remove(application_id) {

            var r = confirm ('Are you sure?');

            if (r == true) {
                myApp.showLoading();
                $.ajax({
                    url: '/admin/application/remove',
                    data: {
                        _token: '{!! csrf_token() !!}',
                        id: application_id
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function(res) {
                        myApp.hideLoading();

                        if ($.trim(res.msg) === '') {
                            myApp.showSuccess('Your request has been processed successful!', function() {
                                //window.location.reload();
                                $('#' + application_id).hide();
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

            } else {
                return;
            }
        }

        function excel_export() {
            $('#excel').val('Y');
            $('#frm_search').submit();
        }

    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />APPLICATIONS</h3>
    </div>

    <div class="container">

    <div class="well filter" style="padding-bottom:5px;">
        <form id="frm_search" class="form-horizontal" method="post" action="/admin/applications">
            {{ csrf_field() }}
            <input type="hidden" name="excel" id="excel"/>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Date</label>
                        <div class="col-md-8">
                            <input type="text" style="width:100px; float:left;" class="form-control" id="sdate" name="sdate" value="{{ old('sdate', $sdate) }}"/>
                            <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                            <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate" name="edate" value="{{ old('edate', $edate) }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Name</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="name" value="{{ old('name', $name) }}"/>
                        </div>

                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Phone</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="phone" value="{{ old('phone', $phone) }}"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Email</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="email" value="{{ old('email', $email) }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Status</label>
                        <div class="col-md-8">
                            <select class="form-control" name="status" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                <option value="" {{ old('status', $status) == '' ? 'selected' : '' }}>All</option>
                                <option value="N" {{ old('status', $status) == 'N' ? 'selected' : '' }}>New</option>
                                <option value="A" {{ old('status', $status) == 'A' ? 'selected' : '' }}>Approved</option>
                                <option value="M" {{ old('status', $status) == 'M' ? 'selected' : '' }}>Maybe</option>
                                <option value="R" {{ old('status', $status) == 'R' ? 'selected' : '' }}>Rejected</option>
                                <option value="T" {{ old('status', $status) == 'T' ? 'selected' : '' }}>On Trial</option>
                                <option value="C" {{ old('status', $status) == 'C' ? 'selected' : '' }}>Contacted</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Address</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="location" value="{{ old('location', $location) }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">States</label>
                        <div class="col-md-8">
                            <select class="form-control" name="state" id="state" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                <option value="">Please Select</option>
                                @if (count($states) > 0)
                                    @foreach ($states as $o)
                                        <option value="{{ $o->code }}" {{ old('state', $state) == $o->code ? 'selected' : '' }}>{{ $o->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">

                    </div>
                </div>
                <div class="col-md-4 col-md-offset-4 text-right">
                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-4">
                            @if (\App\Lib\Helper::get_action_privilege('applications_search', 'Applications Search'))
                            <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                            @endif
                            @if (\App\Lib\Helper::get_action_privilege('applications_export', 'Applications Export'))
                            <button type="button" class="btn btn-info btn-sm" onclick="excel_export()">Export</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>


    <table id="table" class="table table-striped display" cellspacing="0" width="100%">
        <thead>
        <tr>
            <td colspan="8" class="text-right"><strong>TOTAL</strong>: {{ $total }}</td>
        </tr>
        <tr>
            <th>ID</th>
            <th>Status</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Location</th>
            <th>Applied Date</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($applications as $a)
            <tr id="{{ $a->id }}">
                <td id="application/{{ $a->id }}">{{ $a->id }}</td>
                <td id="application/{{ $a->id }}">
                    @if($a->status == 'A')
                        <span class="approved">Approved</span>
                    @else
                        {{ \App\Model\Application::status_name($a->status) }}
                    @endif
                </td>
                <td id="application/{{ $a->id }}">{{ $a->first_name }} {{ $a->last_name }}</td>
                <td id="application/{{ $a->id }}">{{ $a->email }}</td>
                <td id="application/{{ $a->id }}">{{ $a->phone }}</td>
                <td id="application/{{ $a->id }}">{{ $a->city }}, {{ $a->state }}</td>
                <td id="application/{{ $a->id }}">{{ $a->cdate }}</td>
                <td><button class="btn btn-danger btn-xs" onclick="remove({{$a->id}})">Remove</button></td>
            </tr>
        @endforeach
        </tbody>
    </table>


    <div class="text-right">
        {{ $applications->appends(Request::except('page'))->links() }}
    </div>

    </div>

@stop
