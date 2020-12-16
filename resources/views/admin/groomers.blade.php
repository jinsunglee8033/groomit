@extends('includes.admin_default')
@section('contents')

    <script type="text/javascript">
        window.onload = function () {
            $("#sdate").datetimepicker({
                format: 'YYYY-MM-DD'
            });
            $("#edate").datetimepicker({
                format: 'YYYY-MM-DD'
            });

            $("#nc_sdate").datetimepicker({
                format: 'YYYY-MM-DD'
            });
            $("#nc_edate").datetimepicker({
                format: 'YYYY-MM-DD'
            });
            $("#ncd_cdate").datetimepicker({
                format: 'YYYY-MM-DD'
            });

            $(function () {
                $('[data-toggle="tooltip"]').tooltip()
            })
        };

        function excel_export() {
            $('#excel').val('Y');
            $('#frm_search').submit();
        }

        var current_groomer_id = null;
        function show_credits(groomer_id) {
            if (typeof groomer_id === 'undefined') {
                groomer_id = current_groomer_id;
            } else {
                current_groomer_id = groomer_id;
                $('#nc_groomer_id').val(groomer_id);
            }

            myApp.showLoading();
            $.ajax({
                url: '/admin/groomer/load-credit',
                data: {
                    _token: '{!! csrf_token() !!}',
                    groomer_id: groomer_id,
                    sdate: $('#nc_sdate').val(),
                    edate: $('#nc_edate').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {

                        var body = $('#tbl_credit').find('tbody');
                        body.empty();

                        var total_amt = 0;

                        $.each(res.data, function(i, o) {
                            var category ='';
                            if(o.category != null){
                                if(o.category == 'R'){
                                    category = 'Reviews by SNS';
                                }else if(o.category == 'O'){
                                    category = 'Others';
                                }else if(o.category == 'B'){
                                    category = 'Bonus';
                                }
                            }
                            var html = '<tr>';

                            html += '<td>' + o.id + '</td>';
                            html += '<td>' + o.groomer_id + '</td>';
                            html += '<td>' + o.groomer_name + '</td>';
                            html += '<td>' + o.type_name + '</td>';
                            html += '<td>' + category + '</td>';
                            html += '<td>$' + parseFloat(o.groomer_profit_amt).toFixed(2) + '</td>';

                            var comments_button = '<a href="#" data-toggle="tooltip" data-html="true" title="' + o.comments + '">';
                            comments_button += 'View Comments';
                            comments_button += '</a>';

                            html += '<td>' + comments_button + '</td>';
                            html += '<td>' + o.cdate + '</td>';
                            html += '<td>' + o.created_by_name + ' (' +  o.created_by + ')</td>';

                            html += '</tr>';

                            body.append(html);

                            total_amt += parseFloat(o.groomer_profit_amt);
                        })

                        $('[data-toggle="tooltip"]').tooltip()

                        if (res.data.length < 1) {
                            var html = '<tr><td colspan="20">No Record Found</td></tr>';
                            body.append(html);
                        }

                        $('#total_amt').text('$' + total_amt.toFixed(2));

                        $('#div_credits').modal();

                    } else {
                        myApp.showError(res.msg)
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function show_credit_detail() {
            var groomer_id = $('#nc_groomer_id').val();
            $('#ncd_groomer_id').val(groomer_id);

            $('#ncd_type').val('');
            $('#ncd_category').val('');
            $('#ncd_amt').val('');
            $('#ncd_cdate').val('');
            $('#ncd_comments').val('');

            $('#div_credits').modal('hide');
            $('#div_credit_detail').modal();
        }

        function hide_credit_detail() {
            $('#div_credit_detail').modal('hide');
            $('#div_credits').modal();
        }

        function save_credit_detail() {
            myApp.showLoading();

            $.ajax({
                url: '/admin/groomer/save-credit',
                data: {
                    _token: '{!! csrf_token() !!}',
                    groomer_id: $('#ncd_groomer_id').val(),
                    type: $('#ncd_type').val(),
                    category: $('#ncd_category').val(),
                    amt: $('#ncd_amt').val(),
                    cdate: $('#ncd_cdate').val(),
                    comments: $('#ncd_comments').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {

                        myApp.showSuccess('Your request has been processed successfully!', function() {
                            $('#div_credit_detail').modal('hide');
                            show_credits($('#ncd_groomer_id').val());
                        });

                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            })
        }

    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png"/>GROOMERS
        </h3>
    </div>

    <div class="container-fluid">

        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/groomers">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel"/>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Date</label>
                            <div class="col-md-8">
                                <input type="text" style="width:100px; float:left;" class="form-control" id="sdate"
                                       name="sdate" value="{{ old('sdate', $sdate) }}"/>
                                <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="edate" name="edate" value="{{ old('edate', $edate) }}"/>
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
                                <input type="text" class="form-control" name="phone"
                                       value="{{ old('phone', $phone) }}"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Email</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="email"
                                       value="{{ old('email', $email) }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Level</label>
                            <div class="col-md-8">
                                <select class="form-control" name="level"
                                        data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                    <option value="" {{ old('level', $level) == '' ? 'selected' : '' }}>All</option>
                                    <option value="1" {{ old('level', $level) == '1' ? 'selected' : '' }}>Level 1
                                    </option>
                                    <option value="2" {{ old('level', $level) == '2' ? 'selected' : '' }}>Level 2
                                    </option>
                                    <option value="3" {{ old('level', $level) == '3' ? 'selected' : '' }}>Level 3
                                    </option>
                                    <option value="4" {{ old('level', $level) == '4' ? 'selected' : '' }}>Level 4
                                    </option>
                                    <option value="5" {{ old('level', $level) == '5' ? 'selected' : '' }}>Level 5
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Address</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="location"
                                       value="{{ old('location', $location) }}"/>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">State</label>
                            <div class="col-md-8">
                                <select class="form-control" name="state"
                                        data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                    <option value="" {{ old('state', $state) == '' ? 'selected' : '' }}>All</option>
                                    @foreach ($states as $s)
                                    <option value="{{ $s->code }}" {{ old('state', $state) == $s->code ? 'selected' : ''
                                    }}>{{ $s->name . ' (' . $s->qty . ')' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Status</label>
                            <div class="col-md-8">
                                <select class="form-control" name="status" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                    <option value="" {{ old('status', $status) == '' ? 'selected' : '' }}>All</option>
                                    <option value="A" {{ old('status', $status) == 'A' ? 'selected' : ''}}>Active</option>
                                    <option value="I" {{ old('status', $status) == 'I' ? 'selected' : ''}}>Inactive</option>
                                    <option value="P" {{ old('status', $status) == 'P' ? 'selected' : ''}}>PreApproval</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Status 2</label>
                            <div class="col-md-8">
                                <select class="form-control" name="background_check_status" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                    <option value="" {{ old('background_check_status', $background_check_status) == '' ? 'selected' : '' }}>All</option>
                                    <option value="G" {{ old('background_check_status', $background_check_status) == 'G' ? 'selected' : ''}}>Background Checks Progress</option>
                                    <option value="R" {{ old('background_check_status', $background_check_status) == 'R' ? 'selected' : ''}}>Background Checks Rejected</option>
                                    <option value="A" {{ old('background_check_status', $background_check_status) == 'A' ? 'selected' : ''}}>Background Checks Approved</option>
                                    <option value="P" {{ old('background_check_status', $background_check_status) == 'P' ? 'selected' : ''}}>Background Checks Pending</option>
                                    <option value="V" {{ old('background_check_status', $background_check_status) == 'V' ? 'selected' : ''}}>Video Trial Scheduled</option>
                                    <option value="I" {{ old('background_check_status', $background_check_status) == 'I' ? 'selected' : ''}}>InPerson Trial Scheduled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 col-md-offset-4 text-right">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                @if (\App\Lib\Helper::get_action_privilege('groomers_search', 'Groomers Search'))
                                <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                                @endif
                                @if (\App\Lib\Helper::get_action_privilege('groomers_export', 'Groomers Export'))
                                <button type="button" class="btn btn-info btn-sm" onclick="excel_export()">Export
                                </button>
                                @endif
                                @if (\App\Lib\Helper::get_action_privilege('groomers_appointments_schedule', 'Groomers
                                Appointment Schedule'))
                                <button type="button" class="btn btn-warning btn-sm" onclick="window.open('/admin/appointment_schedule', '_blank')">Appointment Schedule</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <table class="table table-bordered display" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <td colspan="13" class="text-right"><strong>TOTAL</strong>: {{ $total }}</td>
                </tr>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Dog</th>
                    <th>Cat</th>
                    <th>Level</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Credits</th>
                    <th>Status</th>
                    <th>Status 2</th>
                    <th>Trial Date</th>
                    <th style="text-align: center;">Last Grooming Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groomers as $gr)
                    <tr>
                        <td>
                            <a href="/admin/groomer/{{ $gr->groomer_id }}">
                                {{ $gr->groomer_id }}
                            </a>
                        </td>
                        <td>
                            <a href="/admin/groomer/{{ $gr->groomer_id }}">
                            {{ $gr->first_name }} {{ $gr->last_name }}
                            </a>
                        </td>
                        <td>{{ $gr->dog }}</td>
                        <td>{{ $gr->cat }}</td>
                        <td>{{ $gr->level }}</td>
                        <td>{{ $gr->phone }}</td>
                        <td>{{ $gr->email }}</td>
                        <td>{{ $gr->city }}, {{ $gr->state }} {{ $gr->zip }}</td>
                        <td>
                            @if (\App\Lib\Helper::get_action_privilege('groomers_credits', 'Groomers Credits'))
                            <button type="button" onclick="show_credits('{{ $gr->groomer_id }}')" class="btn btn-primary btn-sm">
                                Credits
                            </button>
                            @endif
                        </td>
                        <td>{{ $gr->status_name() }}</td>
                        <td>{{ $gr->background_status_name() }}</td>
                        <td>{{ $gr->trial_notes }}</td>
                        <td style="text-align: center;">{{ $gr->last_groom_date }}</td>

                    </tr>
                @endforeach
            </tbody>
        </table>


        <div class="text-right">
            {{ $groomers->appends(Request::except('page'))->links() }}
        </div>
    </div>

    <div id="div_credit_detail" class="modal fade " tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false"
         style="display:none;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">New Credit/Debit</h4>
                </div>
                <div class="modal-body">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Groomer</label>
                            <div class="col-md-8">
                                <select id="ncd_groomer_id" name="ncd_groomer_id" class="form-control">
                                    <option value="">Please Select</option>
                                    @foreach ($groomers as $o)
                                        <option value="{{ $o->groomer_id }}">{{ $o->first_name . ' ' . $o->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Type</label>
                            <div class="col-md-8">
                                <select id="ncd_type" name="ncd_type" class="form-control">
                                    <option value="">Please Select</option>
                                    <option value="C">Credit</option>
                                    <option value="D">Debit</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Category</label>
                            <div class="col-md-8">
                                <select id="ncd_category" name="ncd_category" class="form-control">
                                    <option value="">Please Select</option>
                                    <option value="R">Reviews by SNS</option>
                                    <option value="O">Others</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Amount($)</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="ncd_amt"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Date</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="ncd_cdate"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Comments</label>
                            <div class="col-md-8">
                                <textarea id="ncd_comments" rows="5" style="width:100%"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" onclick="hide_credit_detail()">Close</button>
                    <button type="button" class="btn btn-primary" onclick="save_credit_detail()">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <div id="div_credits" class="modal fade " tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false"
         style="display:none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Credit History</h4>
                </div>
                <div class="modal-body">
                    <div class="well filter" style="padding-bottom:5px; min-height:70px;">
                        <form id="frm_credits" class="form-horizontal">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Date</label>
                                    <div class="col-md-9">
                                        <input type="text" style="width:100px; float:left;" class="form-control" id="nc_sdate"
                                               name="nc_sdate" value="{{ $sdate }}"/>
                                        <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                                        <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                               class="form-control" id="nc_edate" name="nc_edate" value="{{ $edate }}"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-md-4 control-label">Groomer</label>
                                    <div class="col-md-8">
                                        <select id="nc_groomer_id" name="nc_groomer_id" class="form-control">
                                            <option value="">All</option>
                                            @foreach ($groomers as $o)
                                                <option value="{{ $o->groomer_id }}">{{ $o->first_name . ' ' . $o->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="show_credits()">Search</button>
                                        <button type="button" class="btn btn-info btn-sm" onclick="show_credit_detail()">Add New Credit</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <table id="tbl_credit" class="table table-striped display" cellspacing="0" width="100%">
                        <thead>
                            <th>ID</th>
                            <th>Groomer.ID</th>
                            <th>Groomer.Name</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Amt($)</th>
                            <th>Comments</th>
                            <th>Date</th>
                            <th>By</th>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <th colspan="5">Total:</th>
                            <th id="total_amt"></th>
                            <th colspan="3"></th>
                        </tfoot>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop
