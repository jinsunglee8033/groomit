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

            @if (count($errors) > 0)
            $('#error').modal();
            @endif
        };

        function search() {
            myApp.showLoading();
            $('#excel').val('N');
            $('#frm_search').submit();
        }

        function excel_export() {
            $('#excel').val('Y');
            $('#frm_search').submit();

        }

        function submit_adjust() {
            if(  $('[name=groomer_id_adjust]').val() == '') {
                alert("Please Select a Groomer !")
                return;
            };
            var reason_code = $('[name=reason_code_adjust]').val() ;
            if(   reason_code == '') {
                alert("Please Select a Reason !")
                return;
            };
            var point = $('#point_adjust').val() ;
            if( point  == '') {
                alert("Please Enter point value to add or remove on the groomer!")
                return;
            };

            if( (reason_code == 10 && point < 0) ||
                (reason_code == 30 && point < 0) ||
                (reason_code == 110 && point < 0)
               ) {
                alert("You cannit use minus point value at this reason code !");
                return;
            }
            if( (reason_code == 300 && point > 0)
            ) {
                alert("You cannit use plus point value at this reason code !");
                return;
            }
            $('#rewards_adjust').submit();

        }
    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />Groomer Rewards</h3>
    </div>

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">
            @if ($alert = Session::get('alert'))
                <div class="alert alert-danger">
                    {{ $alert }}
                </div>
            @endif
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/rewards">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel" value=""/>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Date Range</label>
                            <div class="col-md-8">
                                <input type="text" style="width:100px; float:left;" class="form-control" id="sdate" name="sdate" value="{{ old('sdate', $sdate) }}"/>
                                <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                                <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate" name="edate" value="{{ old('edate', $edate) }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Appointment.ID</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="appointment_id" value="{{ old('appointment_id', $appointment_id) }}"/>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Groomer</label>
                            <div class="col-md-8">
                                <select class="form-control" id="groomer_id" name="groomer_id">
                                    <option value="">All</option>
                                    @foreach ($groomers as $o)
                                        <option value="{{ $o->groomer_id }}" {{ old('groomer_id', $groomer_id) == $o->groomer_id ? 'selected' : '' }}>{{ $o->first_name . ' ' . $o->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-md-offset-8 text-right">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="button" class="btn btn-primary btn-sm" id="btn_search" onclick="search()">Search</button>
                                <button type="button" class="btn btn-info btn-sm" onclick="excel_export()">Export</button>
                                <div class="btn-right btn btn-info" data-toggle="modal" data-target="#adjust_point">ADJUST POINTS</div>
                            </div>
                        </div>

                    </div>


                </div>
            </form>
        </div>

        <style>
            th {
                background-color: #f5f5f5;
            }
        </style>

        <table class="table table-bordered display" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th style="text-align: center;">Groomer ID</th>
                <th style="text-align: center;">Groomer Name</th>
                <th style="text-align: center;">Points</th>
                <th style="text-align: center;">Reason</th>
                <th style="text-align: center;">Appointment.ID</th>
                <th style="text-align: center;">BY</th>
                <th style="text-align: center;">Date</th>
                <th style="text-align: center;">Comments</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $o)
                <tr>
                    <td style="text-align: center;" ><a href="/admin/groomer/{{ $o->groomer_id }}">{{ $o->groomer_id }}</a></td>
                    <td style="text-align: center;" ><a href="/admin/groomer/{{ $o->groomer_id }}">{{ $o->first_name }} {{ $o->first_name }}</a></td>
                    <td style="text-align: center;" >{{ $o->point }}</td>
                    <td style="text-align: center;">{{ $o->descrpt }}</td>
                    <td style="text-align: center;">
                        <a href="/admin/appointment/{{ $o->appt_id }}">
                        {{ $o->appointment_id }}
                    </td>
                    <td style="text-align: center;">{{ $o->modified_by }}</td>
                    <td style="text-align: center;">{{ $o->cdate }}</td>
                    <td style="text-align: center;">{{ $o->comments }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            </tfoot>
        </table>

        <div class="text-right">
            {{ $data->appends(Request::except('page'))->links() }}
        </div>
    </div>

    <div class="modal fade" id="adjust_point" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">ADD Groomer Points</h4>
                </div>
                <form method="post" id="rewards_adjust"  name="rewards_adjust" action="/admin/reports/rewards_adjust" class="form-group">
                    <input type="hidden" name="_token" value="vSDgwSwz6i3f2jjbDQ4nfLtbNUiz2KMsxUe0dZPS">

                    <div class="modal-body">
                        <div class="row no-border">
                            <div class="col-xs-3">Groomer</div>
                            <div class="col-xs-9">
                                <select class="form-control" id="groomer_id_adjust" name="groomer_id_adjust">
                                    <option value="">Please Select</option>
                                    @foreach ($groomers as $o)
                                        <option value="{{ $o->groomer_id }}" {{ old('groomer_id', $groomer_id) == $o->groomer_id ? 'selected' : '' }}>{{ $o->first_name . ' ' . $o->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row no-border">
                            <div class="col-xs-3">Reason</div>
                            <div class="col-xs-9">
                                <select class="form-control" id="reason_code_adjust" name="reason_code_adjust">
                                    <option value="">Please Select</option>
                                    @foreach ($reason_codes as $o)
                                        <option value="{{ $o->reason_code }}">{{ $o->descrpt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row no-border">
                            <div class="col-xs-3">Points</div>
                            <div class="col-xs-9">
                                <input type="text" id="point_adjust" name="point_adjust" class="form-control" value="" placeholder="Example : 5, 20, -5, -20, etc."/>
                            </div>
                        </div>
                        <div class="row no-border">
                            <div class="col-xs-3">Comments</div>
                            <div class="col-xs-9">
                                <textarea class="form-control" id="comments_adjust" name="comments_adjust" maxlength="250" rows="5" ></textarea>
                            </div>
                        </div>
                        <div class="row no-border">
                            <div class="col-xs-3">Appointment ID</div>
                            <div class="col-xs-9">
                                <input type="text" id="appointment_id_adjust" name="appointment_id_adjust" class="form-control" value=""/>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" onclick="submit_adjust()">SUBMIT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@stop