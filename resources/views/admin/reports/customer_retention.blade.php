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
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />CUSTOMER RETENTION REPORT</h3>
    </div>

    <div class="container-fluid">

        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/customer-retention">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel"/>
                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Service Date</label>
                            <div class="col-md-8">
                                <input type="text" style="width:100px; float:left;" class="form-control" id="sdate" name="sdate" value="{{ old('sdate', $sdate) }}"/>
                                <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                                <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate" name="edate" value="{{ old('edate', $edate) }}"/>
                            </div>

                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                        </div>
                    </div>
                    <div class="col-md-4 col-md-offset-4 text-right">
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


        <table id="table" class="table table-striped display" cellspacing="0" width="100%">
            <thead>
            <tr>
            </tr>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Booked For Entire Period</th>
                <th>Booked For Above Period</th>
                <th>Signup.Date</th>
                <th>Last.Order</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($users as $u)
                <tr>
                    <td><a href="/admin/user/{{ $u->user_id }}">{{ $u->user_id }}</a></td>
                    <td><a href="/admin/user/{{ $u->user_id }}">{{ $u->first_name }} {{ $u->last_name }}</a></td>
                    <td>{{ $u->phone }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{!! $u->c1 !!}</td>
                    <td>{!! $u->c2 !!}</td>
                    <td>{!! $u->cdate !!}</td>
                    <td>{!! $u->last_date !!}</td>
                </tr>
            @endforeach
            </tbody>

            <tfoot>
            <tr>
                <th style="text-align: right;" colspan="3">Total:</th>
                <th style="text-align: center;">{{ $total->all_users }}</th>
                <th style="text-align: center;">{{ $total->all_total }}</th>
                <th style="text-align: center;">{{ $total->period_total }}</th>
                <th style="text-align: right;"></th>
                <th style="text-align: right;"></th>

            </tr>
            </tfoot>
        </table>

        <div class="text-right">
            {{ $users->appends(Request::except('page'))->links() }}
        </div>
    </div>

@stop
