@extends('includes.admin_default')
@section('contents')

    <script type="text/javascript">
        window.onload = function() {
            $( "#date" ).datetimepicker({
                viewMode: 'months',
                format: 'YYYY-MM'
            });

            $('#appointment_schedule').click( function(){
                window.location ='/admin/appointment_schedule';
            });

            $('#groomer_fulfillment').click( function(){
                window.location ='/admin/groomer_fulfillment';
            });
        };
    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />GROOMER AVAILABILITY SCHEDULE</h3>
    </div>

    <div class="container">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/groomer_schedule">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Select Month</label>
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
                                <button type="button" class="btn btn-info" id="appointment_schedule">Appointment Schedule</button>
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
                <th class="text-center" colspan="31">Date</th>
                <th class="text-center" rowspan="2">SERVICE AREA</th>
            </tr>
            <tr>
                @for($i=1;$i<=31;$i++)
                <th class="text-center info">{{ $i }}</th>
                @endfor
            </tr>
            </thead>
            <tbody>
            @foreach ($groomers as $g)
                <tr>
                    <td><a href="/admin/groomer/{{ $g->groomer_id }}" target="_blank">{{ $g->first_name }} {{ $g->last_name }}</a></td>
                    @for($i=1;$i<=31;$i++)
                    <td>{!! $g->availability['d'.$i] !!}</td>
                    @endfor
                    <td>{{ $g->service_area }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="row">
            <div class="col-xs-1 text-center" style="border: 1px solid #ddd;"> Number </div><div class="col-xs-11"> Available hours of the Day</div>
        </div>
    </div>
@stop