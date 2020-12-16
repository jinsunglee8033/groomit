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

    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png"
            />Notifications REPORT</h3>
    </div>

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/notification">
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
                                        <option value="{{ $o->groomer_id }}" {{ old('groomer_id', $groomer_id) == $o->groomer_id ? 'selected' : '' }}>{{ $o->first_name . ' ' . $o->last_name }}[{{$o->status}}]</option>
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
                            </div>
                        </div>
                        * Open appointments in Groomer app should be based on this data.
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
                <th style="text-align: center;">Appointment.ID</th>
                <th style="text-align: center;">County</th>
                <th style="text-align: center;">Policy</th>
                <th style="text-align: center;">Groomer</th>
                <th style="text-align: center;">TEXT Notified</th>
                <th style="text-align: center;">Invalidated</th>
                <th style="text-align: center;">Notice.Date</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $o)
                <tr>
                    <td>
                        <a href="/admin/appointment/{{ $o->appt_id }}">
                        {{ $o->appt_id }}
                    </td>
                    <td>{{ $o->county }}</td>
                    <td>
                        @if($o->stage == 10)
                            Fav. customers
                        @elseif($o->stage == 13)
                            Exclusive Area(New Marketing Area)
                        @elseif($o->stage == 15)
                            Auto Assign group
                        @elseif($o->stage == 16)
                            Over $200 group
                        @elseif($o->stage == 21)
                            Influencers(Level 1)
                        @elseif($o->stage == 22)
                            1st customers(Level 1)
                        @elseif($o->stage == 23)
                            Repeated customers(Level 1)
                        @elseif($o->stage == 26)
                            Influencers(Level 2)
                        @elseif($o->stage == 27)
                            1st customers(Level 2)
                        @elseif($o->stage == 28)
                            Repeated customers(Level 2)
                        @elseif($o->stage == 30)
                            Level 1
                        @elseif($o->stage == 40)
                            Level 2
                        @elseif($o->stage == 50)
                            Level 3
                        @elseif($o->stage == 700)
                            Delayed appointment
                        @elseif($o->stage == 1000)
                            No groomer found:Review the appointment with groomers.
                        @else
                            ERROR:Contact IT
                        @endif
                    </td>
                    <td>
                        <a href="/admin/groomer/{{ $o->groomer_id }}">
                        {{ $o->first_name }}
                        {{ $o->last_name }}
                        </a>
                    </td>
                    <td>{{ $o->notified }}</td>
                    <td>{{ $o->removed }}</td>
                    <td>{{ $o->cdate }}</td>
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


@stop