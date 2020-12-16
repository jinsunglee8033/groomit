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
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />Number of Groomers by County Summary</h3>
    </div>

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/groomers-by-countysummary">
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
                        <div class="form-group text-right">
                            <div class="col-md-8 col-md-offset-8">
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
                <th style="text-align: center;">County</th>
                <th style="text-align: center;">Number of Groomers</th>
                <th style="text-align: center;">Number of Users</th>
                <th style="text-align: center;">Number of Appointments</th>
                <th style="text-align: center;">Revenue</th>
                <th style="text-align: center;">Groomers</th>

            </tr>
            </thead>
            <tbody>
            @foreach ($results as $r)
                <tr>
                    <td style="text-align: center;">{{ $r->county }}</td>
                    <td style="text-align: center;">{{ $r->groomer_cnt }}</td>
                    <td style="text-align: center;">{{ $r->user_cnt }}</td>
                    <td style="text-align: center;">{{ $r->appt_cnt }}</td>
                    <td style="text-align: center;">${{ number_format($r->appt_total, 2) }}</td>
                    <td style="text-align: center;">
                        @foreach ($details as $d)
                            @if( $r->county  == $d->county )
                                <a href="/admin/groomer/{{ $d->groomer_id }}">{{ $d->first_name }} {{ $d->last_name }}, </a>
                            @endif
                        @endforeach
                    </td>
                </tr>
            @endforeach

            </tbody>

            <tfoot>
            <tr>
                @if(count($results)>0)
                    <td style="text-align: center;">Total</td>
                    <td style="text-align: center;">{{$total -> num_total}}</td>
                    <td style="text-align: center;">{{$total -> user_total}}</td>
                    <td style="text-align: center;">{{$total -> appt_total}}</td>
                    <td style="text-align: center;">${{number_format($total -> rev_total, 2)}}</td>

                @endif
            </tr>

            </tfoot>

{{--<tfoot>--}}
{{--<tr>--}}
{{--    @if(count($results)>0)--}}
{{--        <td style="text-align: center;">Average of {{count($results)}} survey results:</td>--}}
{{--        <td style="text-align: center;"></td>--}}
{{--        <td style="text-align: center;">{{ round($total->ov_total/count($results), 2) }}</td>--}}
{{--        <td style="text-align: center;">{{ round($total->sc_total/count($results), 2)  }}</td>--}}
{{--        <td style="text-align: center;">{{ round($total->gq_total/count($results), 2)  }}</td>--}}
{{--        <td style="text-align: center;">{{ round($total->cl_total/count($results), 2)  }}</td>--}}
{{--        <td style="text-align: center;">{{ round($total->va_total/count($results), 2) }}</td>--}}
{{--        <td style="text-align: center;">{{ round($total->cs_total/count($results), 2) }}</td>--}}
{{--        <td style="text-align: center;"></td>--}}
{{--        <td style="text-align: center;"></td>--}}

{{--    @endif--}}
{{--</tr>--}}

{{--</tfoot>--}}

        </table>

    </div>


@stop
