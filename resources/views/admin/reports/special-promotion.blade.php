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

        function search() {
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
            />{{ $spcode }} PROMOTION</h3>
    </div>

    <div class="container">

        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/special-promotion">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel"/>
                <input type="hidden" name="spcode" value="{{ $spcode }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Date</label>
                            <div class="col-md-8">
                                <input type="text" style="width:100px; float:left;" class="form-control" id="sdate" name="sdate" value="{{ old('sdate', $sdate) }}"/>
                                <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                                <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate" name="edate" value="{{ old('edate', $edate) }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-md-offset-4 text-right">
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

        <table class="table table-striped display" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>User.ID</th>
                <th>User.Name</th>
                <th>User.Email</th>
                <th>Appointment.ID</th>
                <th>Amount($)</th>
                <th>Order.Date</th>
                <th>Service.Date</th>
                <th>Credit.Amount($)</th>
                <th>Expire.Date</th>
            </tr>
            </thead>
            <tbody>
            @if (count($data) > 0)
                @foreach ($data as $o)
                    <tr>
                        <td>{{ $o->user_id }}</td>
                        <td>{{ $o->user_name }}</td>
                        <td>{{ $o->email }}</td>
                        <td>{{ $o->appointment_id }}</td>
                        <td>{{ $o->total }}</td>
                        <td>{{ $o->order_date }}</td>
                        <td>{{ $o->service_date }}</td>
                        <td>{{ $o->credit_amt }}</td>
                        <td>{{ $o->expire_date }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="100" class="text-center">No record found.</td>
                </tr>
            @endif
            </tbody>
        </table>


        <div class="text-right">
            {{ $data->appends(Request::except('page'))->links() }}
        </div>
    </div>
@stop
