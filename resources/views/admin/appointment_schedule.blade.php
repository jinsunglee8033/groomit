@extends('includes.admin_default')
@section('contents')

    <script type="text/javascript">
        window.onload = function() {
            $( "#date" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });

            $('#groomer_schedule').click( function(){
                window.location ='/admin/groomer_schedule';
            });

            $('#groomer_fulfillment').click( function(){
                window.location ='/admin/groomer_fulfillment';
            });
        };
    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />APPOINTMENTS SCHEDULE</h3>
    </div>

    <div class="container">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/appointment_schedule">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Select Date</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="date" name="date" value="{{ $date }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-left">
                        <div class="form-group">
                            <div class="col-md-8">
                                <button type="submit" class="btn btn-primary" id="btn_search">Search</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 text-right">
                        <div class="form-group">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-info" id="groomer_schedule">Groomer Schedule</button>
                                <button type="button" class="btn btn-info" id="groomer_fulfillment">Groomer Fulfillment</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <table id="table" class="table table-bordered display text-center" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th class="text-center" rowspan="2">GROOMER</th>
                <th class="warning" colspan="6">AM</th>
                <th class="info" colspan="22">PM</th>
                <th class="text-center" rowspan="2">SERVICE AREA</th>
            </tr>
            <tr>
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
                <th class="text-center info">9:00</th>
                <th class="text-center info">9:30</th>
                <th class="text-center info">10:00</th>
                <th class="text-center info">10:30</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($groomers as $g)
                @if ($g->show)
                <tr>
                    <td><a href="/admin/groomer/{{ $g->groomer_id }}" target="_blank">{{ $g->first_name }} {{ $g->last_name }}</a></td>
                    <td {{ $g->appointment_css['8']['0'] }}>{!! $g->availability['h8']['0'] !!}</td>
                    <td {{ $g->appointment_css['8']['30'] }}>{!! $g->availability['h8']['30'] !!}</td>
                    <td {{ $g->appointment_css['9']['0'] }}>{!! $g->availability['h9']['0'] !!}</td>
                    <td {{ $g->appointment_css['9']['30'] }}>{!! $g->availability['h9']['30'] !!}</td>
                    <td {{ $g->appointment_css['10']['0'] }}>{!! $g->availability['h10']['0'] !!}</td>
                    <td {{ $g->appointment_css['10']['30'] }}>{!! $g->availability['h10']['30'] !!}</td>
                    <td {{ $g->appointment_css['11']['0'] }}>{!! $g->availability['h11']['0'] !!}</td>
                    <td {{ $g->appointment_css['11']['30'] }}>{!! $g->availability['h11']['30'] !!}</td>
                    <td {{ $g->appointment_css['12']['0'] }}>{!! $g->availability['h12']['0'] !!}</td>
                    <td {{ $g->appointment_css['12']['30'] }}>{!! $g->availability['h12']['30'] !!}</td>
                    <td {{ $g->appointment_css['13']['0'] }}>{!! $g->availability['h13']['0'] !!}</td>
                    <td {{ $g->appointment_css['13']['30'] }}>{!! $g->availability['h13']['30'] !!}</td>
                    <td {{ $g->appointment_css['14']['0'] }}>{!! $g->availability['h14']['0'] !!}</td>
                    <td {{ $g->appointment_css['14']['30'] }}>{!! $g->availability['h14']['30'] !!}</td>
                    <td {{ $g->appointment_css['15']['0'] }}>{!! $g->availability['h15']['0'] !!}</td>
                    <td {{ $g->appointment_css['15']['30'] }}>{!! $g->availability['h15']['30'] !!}</td>
                    <td {{ $g->appointment_css['16']['0'] }}>{!! $g->availability['h16']['0'] !!}</td>
                    <td {{ $g->appointment_css['16']['30'] }}>{!! $g->availability['h16']['30'] !!}</td>
                    <td {{ $g->appointment_css['17']['0'] }}>{!! $g->availability['h17']['0'] !!}</td>
                    <td {{ $g->appointment_css['17']['30'] }}>{!! $g->availability['h17']['30'] !!}</td>
                    <td {{ $g->appointment_css['18']['0'] }}>{!! $g->availability['h18']['0'] !!}</td>
                    <td {{ $g->appointment_css['18']['30'] }}>{!! $g->availability['h18']['30'] !!}</td>
                    <td {{ $g->appointment_css['19']['0'] }}>{!! $g->availability['h19']['0'] !!}</td>
                    <td {{ $g->appointment_css['19']['30'] }}>{!! $g->availability['h19']['30'] !!}</td>
                    <td {{ $g->appointment_css['20']['0'] }}>{!! $g->availability['h20']['0'] !!}</td>
                    <td {{ $g->appointment_css['20']['30'] }}>{!! $g->availability['h20']['30'] !!}</td>
                    <td {{ $g->appointment_css['21']['0'] }}>{!! $g->availability['h21']['0'] !!}</td>
                    <td {{ $g->appointment_css['21']['30'] }}>{!! $g->availability['h21']['30'] !!}</td>
                    <td {{ $g->appointment_css['22']['0'] }}>{!! $g->availability['h22']['0'] !!}</td>
                    <td {{ $g->appointment_css['22']['30'] }}>{!! $g->availability['h22']['30'] !!}</td>
                    <td>{{ $g->service_area }}</td>
                </tr>
                @endif
            @endforeach
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