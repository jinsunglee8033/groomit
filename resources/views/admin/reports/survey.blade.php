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
        };

        function excel_export() {
            $('#excel').val('Y');
            $('#frm_search').submit();
            $('#excel').val('N');
        }

    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />Survey</h3>
    </div>

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/survey">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel"/>
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
                             <label class="col-md-4 control-label">Groomer</label>
                              <div class="col-md-8">
                                     <select class="form-control" id="groomer_id" name="groomer_id">
                                        <option value="">All</option>
                                         @foreach ($groomers as $r)
                                        <option value="{{ $r->groomer_id }}" {{ old('groomer_id', $groomer_id) == $r->groomer_id ? 'selected' : '' }}>{{ $r->first_name . ' ' . $r->last_name }}[{{$r->status}}]</option>
                                        @endforeach
                                        </select>
                                </div>
                        </div>
                    </div>

                    <div class="col-md-4 text-right">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                                <button type="button" class="btn btn-info btn-sm" onclick="excel_export()">Export</button>
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

        <table class="table table-bordered display" cellspacing="0" width="100%" style="font-size: 11px;">
            <thead>
            <tr>
                <th style="text-align: center;">Appointment ID</th>
                <th style="text-align: center;">Groomer</th>
                <th style="text-align: center;">Overall</th>
                <th style="text-align: center;">Scheduling</th>
                <th style="text-align: center;">Groomer Quality</th>
                <th style="text-align: center;">Cleanliness</th>
                <th style="text-align: center;">Value</th>
                <th style="text-align: center;">Customer Support</th>
                <th style="text-align: center;">Suggestion</th>
                <th style="text-align: center;">Date</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($results as $r)
                <tr>
                    <td style="text-align: center;"><a href="/admin/appointment/{{ $r->appointment_id }}">{{ $r->appointment_id }}</a></td>
                    <td style="text-align: center;"><a href="/admin/groomer/{{ $r->groomer_id }}">{{ $r->groomer_name }}</a></td>
                    <td style="text-align: center;">{{ $r->ov }} </td>
                    <td style="text-align: center;">{{ $r->sc }}</td>
                    <td style="text-align: center;">{{ $r->gq }}</td>
                    <td style="text-align: center;">{{ $r->cl }}</td>
                    <td style="text-align: center;">{{ $r->va }}</td>
                    <td style="text-align: center;">{{ $r->cs }}</td>
                    <td style="text-align: center;">{{ $r->su }}</td>
                    <td style="text-align: center;">{{ $r->cdate }}</td>
{{--                    <td>{{$r->total}}</td>--}}
                </tr>
            @endforeach


            </tbody>
<tfoot>
<tr>
    @if(count($results)>0)
        <td style="text-align: center;">Average of {{count($results)}} survey results:</td>
        <td style="text-align: center;"></td>
        <td style="text-align: center;">{{ round($total->ov_total/count($results), 2) }}</td>
        <td style="text-align: center;">{{ round($total->sc_total/count($results), 2)  }}</td>
        <td style="text-align: center;">{{ round($total->gq_total/count($results), 2)  }}</td>
        <td style="text-align: center;">{{ round($total->cl_total/count($results), 2)  }}</td>
        <td style="text-align: center;">{{ round($total->va_total/count($results), 2) }}</td>
        <td style="text-align: center;">{{ round($total->cs_total/count($results), 2) }}</td>
        <td style="text-align: center;"></td>
        <td style="text-align: center;"></td>

    @endif
</tr>

</tfoot>

        </table>

    </div>


@stop
