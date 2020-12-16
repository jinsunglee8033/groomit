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
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />Credit Card Payment Details</h3>
    </div>

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/paymentdetails">
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

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="col-md-5 control-label">Appointment ID</label>
                            <div class="col-md-6">
                                <input type="text" style="width:75px; margin-left: 1px; float:left;" class="form-control" id="appointment_id" name="appointment_id" value="{{ old('appointment_id', $appointment_id) }}"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Eco?</label>
                            <select style="width:100px; margin-left: 5px; float:left;" class="col-md-2 form-control" id="iseco" name="iseco">
                                <option value="" {{ $IsEco != 1 ? 'selected' : '' }}>All</option>
                                <option value="1" {{ $IsEco == 1 ? 'selected' : '' }}>Eco Only</option>
                            </select>
                        </div>
                    </div>


                    <div class="col-md-3 text-right">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                                <button type="button" class="btn btn-info btn-sm" onclick="excel_export()">Export</button>
                                <button type="button" class="btn btn-warning btn-sm" onclick="window.location.href='/admin/reports/paymentsummary'">Summary</button>
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
                <th style="text-align: center;">Date</th>
                <th style="text-align: center;">Type</th>
                <th style="text-align: center;">Category</th>
                <th style="text-align: center;">Amount</th>
                <th style="text-align: center;">Comment</th>
                <th style="text-align: center;">Original Sales ID</th>
                <th style="text-align: center;">Void Date</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($results as $r)
                <tr>

                    <td style="text-align: center;"><a href="/admin/appointment/{{ $r->appointment_id }}">{{ $r->appointment_id }}</a></td>
                    <td style="text-align: center;">{{ $r->cdate }}</td>
                    <td style="text-align: center;">{{ $r->type }}</td>
                    <td style="text-align: center;">{{ $r->category }}</td>
                    <td style="text-align: center;">{{ $r->amt }}</td>
                    <td style="text-align: center;">{{ $r->error_name }}</td>
                    <td style="text-align: center;">{{ $r->orig_sales_id }}</td>
                    <td style="text-align: center;">{{ $r->void_date }}</td>

                </tr>
            @endforeach


            </tbody>

<tfoot>
<tr>
    @if(count($results)>0)
        <td style="text-align: center;">Total amount of {{count($results)}} rows:</td>
        <td style="text-align: center;"></td>
        <td style="text-align: center;"></td>
        <td style="text-align: center;"></td>
        <td style="text-align: center;"></td>
        <td style="text-align: center;">{{ $total->amt_total }}</td>
        <td style="text-align: center;"></td>
        <td style="text-align: center;"></td>
        <td style="text-align: center;"></td>
    @endif
</tr>

</tfoot>

        </table>

    </div>


@stop
