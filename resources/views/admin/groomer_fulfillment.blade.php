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

            $( "#date" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });

            $('#groomer_schedule').click( function(){
                window.location ='/admin/groomer_schedule';
            });

            $('#appointment_schedule').click( function(){
                window.location ='/admin/appointment_schedule';
            });
        };
    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />APPOINTMENTS SCHEDULE</h3>
    </div>

    <div class="container">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/groomer_fulfillment">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Groomer</label>
                            <div class="col-md-8">
                                <select class="form-control" name="groomer" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                    <option value="" {{ old('groomer', $groomer) == '' ? 'selected' : '' }}>All</option>
                                    @foreach($groomers as $o)
                                        @if (!empty($o->groomer_id ))
                                            <option value="{{ $o->groomer_id }}" {{ old('groomer', $groomer) == $o->groomer_id ? 'selected' : '' }}>{{ $o->first_name }} {{ $o->last_name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Select Date</label>
                            <div class="col-md-8">
                                <input type="text" style="width:100px; float:left;" class="form-control" id="sdate" name="sdate" value="{{ old('sdate', $sdate) }}"/>
                                <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                                <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate" name="edate" value="{{ old('edate', $edate) }}"/>
                            </div>

                        </div>
                    </div>

                    <div class="col-md-4 text-left">
                        <div class="form-group">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary" id="btn_search">Search</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Weekday</label>
                            <div class="col-md-8">
                                <select class="form-control" name="weekday">
                                    <option value="" {{ old('weekday', $weekday) == '' ? 'selected' : '' }}>All</option>
                                    <option value="1" {{ old('weekday', $weekday) == '1' ? 'selected' : '' }}>Monday</option>
                                    <option value="2" {{ old('weekday', $weekday) == '2' ? 'selected' : '' }}>Tuesday</option>
                                    <option value="3" {{ old('weekday', $weekday) == '3' ? 'selected' : '' }}>Wednesday</option>
                                    <option value="4" {{ old('weekday', $weekday) == '4' ? 'selected' : '' }}>Thursday</option>
                                    <option value="5" {{ old('weekday', $weekday) == '5' ? 'selected' : '' }}>Friday</option>
                                    <option value="6" {{ old('weekday', $weekday) == '6' ? 'selected' : '' }}>Saturday</option>
                                    <option value="0" {{ old('weekday', $weekday) == '0' ? 'selected' : '' }}>Sunday</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    <div class="col-md-4 text-left">
                    </div>

                    <div class="col-md-4 text-left">
                        <div class="form-group">
                            <a type="button" class="btn btn-info pull-right" id="groomer_schedule" style="margin-left: 5px; margin-right: 5px;">Groomer Schedule</a>
                            &nbsp;&nbsp;<a type="button" class="btn btn-info pull-right" id="appointment_schedule">Appointment Schedule</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        @if (!empty($groomer) && !empty($summary_data[0]['gromer_name']) && !empty($summary_data[0]['service_area']))
            <a href="/admin/groomer/{{ $groomer }}" target="_blank">{{ $summary_data[0]['gromer_name'] }}</a>, {{ $summary_data[0]['service_area'] }}
        @endif
        <table id="table" class="table table-bordered display text-center" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th class="text-center" rowspan="2">Date</th>
                <th class="warning" colspan="8">AM</th>
                <th class="info" colspan="18">PM</th>
                <th class="text-right success" rowspan="2">Total</th>
            </tr>
            <tr style="font-size: 9px;">
                <th class="text-center warning">8:00</th>
                <th class="text-center warning">8:30</th>
                <th class="text-center warning">9:00</th>
                <th class="text-center warning">9:30</th>
                <th class="text-center warning">10:00</th>
                <th class="text-center warning">10:30</th>
                <th class="text-center warning">11:00</th>
                <th class="text-center warning">11:30</th>
                <th class="text-center info">12:00</th>
                <th class="text-center info">12:30</th>
                <th class="text-center info">1:00</th>
                <th class="text-center info">1:30</th>
                <th class="text-center info">2:00</th>
                <th class="text-center info">2:30</th>
                <th class="text-center info">3:00</th>
                <th class="text-center info">3:30</th>
                <th class="text-center info">4:00</th>
                <th class="text-center info">4:30</th>
                <th class="text-center info">5:00</th>
                <th class="text-center info">5:30</th>
                <th class="text-center info">6:00</th>
                <th class="text-center info">6:30</th>
                <th class="text-center info">7:00</th>
                <th class="text-center info">7:30</th>
                <th class="text-center info">8:00</th>
                <th class="text-center info">8:30</th>
            </tr>
            </thead>
            <tbody>
                @php
                    $total_appoint = 0;
                @endphp
                @foreach ($summary_data as $g)
                <tr style="font-size: 9px;">
                    <td style="font-size: 10px; text-align: left;">{{ $g['week_name'] . ', ' . $g['date'] }}</td>
                    <td {{ $g['appointment_css']['8']['0'] }}>{!! $g['availability']['h8']['0'] !!}</td>
                    <td {{ $g['appointment_css']['8']['30'] }}>{!! $g['availability']['h8']['30'] !!}</td>
                    <td {{ $g['appointment_css']['9']['0'] }}>{!! $g['availability']['h9']['0'] !!}</td>
                    <td {{ $g['appointment_css']['9']['30'] }}>{!! $g['availability']['h9']['30'] !!}</td>
                    <td {{ $g['appointment_css']['10']['0'] }}>{!! $g['availability']['h10']['0'] !!}</td>
                    <td {{ $g['appointment_css']['10']['30'] }}>{!! $g['availability']['h10']['30'] !!}</td>
                    <td {{ $g['appointment_css']['11']['0'] }}>{!! $g['availability']['h11']['0'] !!}</td>
                    <td {{ $g['appointment_css']['11']['30'] }}>{!! $g['availability']['h11']['30'] !!}</td>
                    <td {{ $g['appointment_css']['12']['0'] }}>{!! $g['availability']['h12']['0'] !!}</td>
                    <td {{ $g['appointment_css']['12']['30'] }}>{!! $g['availability']['h12']['30'] !!}</td>
                    <td {{ $g['appointment_css']['13']['0'] }}>{!! $g['availability']['h13']['0'] !!}</td>
                    <td {{ $g['appointment_css']['13']['30'] }}>{!! $g['availability']['h13']['30'] !!}</td>
                    <td {{ $g['appointment_css']['14']['0'] }}>{!! $g['availability']['h14']['0'] !!}</td>
                    <td {{ $g['appointment_css']['14']['30'] }}>{!! $g['availability']['h14']['30'] !!}</td>
                    <td {{ $g['appointment_css']['15']['0'] }}>{!! $g['availability']['h15']['0'] !!}</td>
                    <td {{ $g['appointment_css']['15']['30'] }}>{!! $g['availability']['h15']['30'] !!}</td>
                    <td {{ $g['appointment_css']['16']['0'] }}>{!! $g['availability']['h16']['0'] !!}</td>
                    <td {{ $g['appointment_css']['16']['30'] }}>{!! $g['availability']['h16']['30'] !!}</td>
                    <td {{ $g['appointment_css']['17']['0'] }}>{!! $g['availability']['h17']['0'] !!}</td>
                    <td {{ $g['appointment_css']['17']['30'] }}>{!! $g['availability']['h17']['30'] !!}</td>
                    <td {{ $g['appointment_css']['18']['0'] }}>{!! $g['availability']['h18']['0'] !!}</td>
                    <td {{ $g['appointment_css']['18']['30'] }}>{!! $g['availability']['h18']['30'] !!}</td>
                    <td {{ $g['appointment_css']['19']['0'] }}>{!! $g['availability']['h19']['0'] !!}</td>
                    <td {{ $g['appointment_css']['19']['30'] }}>{!! $g['availability']['h19']['30'] !!}</td>
                    <td {{ $g['appointment_css']['20']['0'] }}>{!! $g['availability']['h20']['0'] !!}</td>
                    <td {{ $g['appointment_css']['20']['30'] }}>{!! $g['availability']['h20']['30'] !!}</td>
                    <td class="text-right success" style="font-size: 11px;">{!! $g['appoint_qty_summary'] !!}</td>
                </tr>
                @php
                    $total_appoint += $g['appoint_qty_summary'];
                @endphp
                @endforeach
                <tr style="font-size: 12px;">
                    <th class="text-right success">Total:</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h8'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h830'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h9'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h930'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h10'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h1030'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h11'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h1130'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h12'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h1230'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h13'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h1330'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h14'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h1430'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h15'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h1530'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h16'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h1630'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h17'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h1730'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h18'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h1830'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h19'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h1930'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h20'] }}</th>
                    <th class="text-right success"> {{ $summary_qty_by_hour['h2030'] }}</th>
                    <th class="text-right success"> {{ $total_appoint }}</th>
                </tr>

            </tbody>
        </table>

        <div class="row">
            <div class="col-xs-1 text-center" style="border: 1px solid #ddd;"> X </div><div class="col-xs-11"> Available Schedule</div>
        </div>
        <div class="row">
            <div class="col-xs-1 bg-danger" style="border: 1px solid #ddd;"> &nbsp; </div><div class="col-xs-11">Taken Schedule & Appointment Time</div>
        </div>
    </div>
@stop