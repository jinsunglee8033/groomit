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
            />VOUCHER SALES REPORT</h3>
    </div>

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/vouchers">
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

                    <div class="col-md-4 col-md-offset-8 text-right">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="button" class="btn btn-primary btn-sm" id="btn_search" onclick="search()">Search</button>
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

        <table class="table table-bordered display" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th style="text-align: center;">ID</th>
                <th style="text-align: center;">Date.Time</th>
                <th style="text-align: center;">Type</th>
                <th style="text-align: center;">Amt</th>
                <th style="text-align: center;">Cost</th>
                <th style="text-align: center;">Sendor</th>
                <th style="text-align: center;">Recipient<br>Name</th>
                <th style="text-align: center;">Recipient<br>Email</th>
                <th style="text-align: center;">User</th>
            </tr>
            </thead>
            <tbody>
            @foreach($sales as $o)
                <tr>
                    <td style="text-align: center;">{{ $o->id  }}</td>
                    <td>{{ $o->cdate }}</td>
                    <td style="text-align: right;">{{ $o->status == 'S' ? 'Sales' : 'Void' }}</td>
                    <td style="text-align: right;">${{ $o->status == 'S' ? number_format($o->amt,2) : number_format(-$o->amt,2) }}</td>
                    <td style="text-align: right;">${{ $o->status == 'S' ? number_format($o->cost,2) : number_format(-$o->cost,2) }}</td>
                    <td>{{ $o->sender }}</td>
                    <td>{{ $o->recipient_name }}</td>
                    <td>{{ $o->recipient_email }}</td>
                    <td><a href="/admin/user/{{ $o->user_id }}">{{ $o->first_name . ' ' . $o->last_name }}</a></td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <th colspan="3" style="text-align: right;">Total</th>
                <th style="text-align: right;">${{ number_format($total->amt,2) }}</th>
                <th style="text-align: right;">${{ number_format($total->cost,2) }}</th>
                <th style="text-align: right;">{{ $total->qty }}</th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            </tfoot>
        </table>

        <div class="text-right">
            {{ $sales->appends(Request::except('page'))->links() }}
        </div>
    </div>


@stop