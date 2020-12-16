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

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/fulfillment_schedule">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Select Date</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="date" name="date" value="{{ $date }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="col-md-4 control-label">County/State</label>
                            <div class="col-md-8">
                                <select class="form-control" name="county">
                                    <option value="">All</option>
                                    @if (count($counties) > 0)
                                        @foreach ($counties as $o)
                                            <option value="{{ $o->county_name . '/' . $o->state_abbr }}" {{ old('county', $county) == $o->county_name . '/' . $o->state_abbr ? 'selected' : '' }}>{{ $o->county_name . '/' . $o->state_abbr }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 text-left">
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

        <table id="table" class="table table-bordered display text-center" cellspacing="0" width="100%"
               style="font-size: 10px;">
            <thead>
            <tr>
                <th class="text-center" rowspan="2">GROOMER</th>
                <th class="warning" colspan="8">AM</th>
                <th class="info" colspan="22">PM</th>
                <th class="text-center bg-danger" rowspan="2">SERVICE AREA</th>
                <th class="text-center" rowspan="2">App#</th>
                <th class="text-center" rowspan="2">Available<br>Hours</th>
            </tr>
            <tr>
                @foreach ($times as $t)
                <th class="text-center {{ $t->dtime < '12:00:00' ? 'warning' : 'info' }}">{{ $t->label }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody style="font-size: 10px;">
            @php
            $available_groomer = [];
            $groomer_apps_total = 0;
            $groomer_avah_total = 0;
            @endphp
            @foreach ($groomers as $g)
            @if (!empty($fulfillments[$g->groomer_id]) || !empty($availabilities[$g->groomer_id]))
            <tr>
                <td style="text-align: left;"><a href="/admin/groomer/{{ $g->groomer_id }}" target="_blank">{{
                $g->first_name }} {{
                $g->last_name }}</a></td>

                @php
                    $groomer_apps = 0;
                    $groomer_avah = 0;
                @endphp

                @foreach ($times as $t)

                    @if (!empty($fulfillments[$g->groomer_id]) && !empty($fulfillments[$g->groomer_id][$t->id]))
                        <td class="danger">
                            <a href="/admin/appointment/{{ $fulfillments[$g->groomer_id][$t->id]->appointment_id }}">
                        {!! $fulfillments[$g->groomer_id][$t->id]->id_from == $t->id ?
                            $fulfillments[$g->groomer_id][$t->id]->allowed_zip->county_name . '<br>' .
                            $fulfillments[$g->groomer_id][$t->id]->address->zip : '' !!}
                        {!! $fulfillments[$g->groomer_id][$t->id]->id_to == $t->id ? substr
                            ($fulfillments[$g->groomer_id][$t->id]->retime, 0, 5) . '<br>(End)' : '' !!}

                            </a>
                        </td>
                        @if ($fulfillments[$g->groomer_id][$t->id]->id_from == $t->id)
                            @php
                                $groomer_apps++;
                                $groomer_apps_total++;
                            @endphp
                            @endif
                    @else
                        @if (!empty($availabilities[$g->groomer_id]) && !empty($availabilities[$g->groomer_id][$t->id]))
                            <td class="text-center">
                                X
                            </td>
                            @php
                                $groomer_avah++;
                                $groomer_avah_total++;
                                $available_groomer[$t->id] = empty($available_groomer[$t->id]) ? 1 :
                                $available_groomer[$t->id] + 1;
                            @endphp
                        @else
                            @if (empty($available_groomer[$t->id]))
                            @php
                                $available_groomer[$t->id] = 0;
                            @endphp
                            @endif
                            <td class="text-center"></td>
                        @endif
                    @endif
                @endforeach
                <td style="text-align: left;">{{ $g->service_area }}</td>
                <td>{{ $groomer_apps }}</td>
                <td>{{ $groomer_avah }}</td>
            </tr>
            @endif
            @endforeach

            <tr>
                <td style="text-align: left;">Open Appointments</td>
                @foreach ($times as $t)
                    @php
                        $apps = \App\Lib\Helper::get_appointment_by_date_time($date, $t->id, 'N');
                    @endphp
                    <td class="text-center">
                        @if (!empty($apps))
                            @foreach($apps as $ap)
                                <a href="/admin/appointment/{{ $ap->appointment_id }}">{{ $ap->zip }}</a><br>
                            @endforeach
                        @endif
                    </td>
                @endforeach
                <td></td>
                <td>-</td>
                <td>-</td>
            </tr>

            <tr>
                <td style="text-align: left;">Available Groomers</td>
                @foreach ($times as $t)
                    <td class="text-center">
                        @if( !empty($available_groomer[$t->id]))
                            {{ $available_groomer[$t->id] }}
                        @endif
                    </td>
                @endforeach
                <td style="text-align: right;">Total:</td>
                <th class="text-center">{{ $groomer_apps_total }}</th>
                <th class="text-center">{{ $groomer_avah }}</th>
            </tr>
            </tbody>
        </table>
        <div class="row">
            <div class="col-xs-1 text-center" style="border: 1px solid #ddd;"> X </div><div class="col-xs-11"> Available Schedule</div>
        </div>
        <div class="row">
            <div class="col-xs-1 bg-info" style="border: 1px solid #ddd;"> &nbsp; </div><div class="col-xs-11">Taken Schedule & Appointment Time</div>
        </div>
    </div>
@stop