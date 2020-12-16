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
        }

        function change_status(code) {
            //$('#send').submit();
            myApp.showLoading();
            $.ajax({
                url: '/admin/promo_code/change_status',
                data: {
                    _token: '{!! csrf_token() !!}',
                    code: code
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        myApp.showSuccess('Your request has been processed successfully!', function () {
                            search();
                        });
                    } else {
                        //alert(res.msg);
                        myApp.showError(res.msg);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function search() {
            $('#excel').val('N');
            $('#frm_search').submit();
        }

    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />REDEEMED GROUPON</h3></div>
    <div class="container">

        @if ($alert = Session::get('alert'))
            @if ($alert == 'Success')
                <div class="alert alert-success detail">
                    {{ $alert }}
                </div>
            @else
                <div class="alert alert-danger detail">
                    {{ $alert }}
                </div>
            @endif
        @endif

        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/redeemed_groupon">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel"/>
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="col-md-5 control-label">Redeemed Date</label>
                            <div class="col-md-7">
                                <input type="text" style="width:100px; float:left;" class="form-control" id="sdate" name="sdate" value="{{ old('sdate', $sdate) }}"/>
                                <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                                <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate" name="edate" value="{{ old('edate', $edate) }}"/>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-5 control-label">Groupon Code</label>
                            <div class="col-md-7">
                                <input type="text" class="form-control" name="promo_code" value="{{ old('promo_code', $promo_code) }}"/>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 text-right">
                        <div class="form-group">
                            <div class="col-md-12">
                                @if (\App\Lib\Helper::get_action_privilege('redeemed_groupon_search', 'Redeemed Groupon Search'))
                                <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                                @endif
                                @if (\App\Lib\Helper::get_action_privilege('redeemed_groupon_export', 'Redeemed Groupon Export'))
                                <button type="button" class="btn btn-info btn-sm" onclick="excel_export()">Export</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <table id="table" class="table table-striped display" cellspacing="0" width="100%">
            <thead>
            <tr>
                <td colspan="7" class="text-right"><strong>TOTAL</strong>: {{ $total }}</td>
            </tr>
            <tr>
                <th class="text-center">Promo Code</th>
                <th class="text-center">Amount</th>
                <th class="text-center">Groupon Payout</th>
                <th class="text-center">Redeemed<br>(Appointment ID)</th>
                <th class="text-center">Redeemed Amount</th>
                <th class="text-center">Redeemed Date</th>
                <th class="text-center">Status</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($promo_codes as $n)
                <tr>
                    <td class="text-center" id="message_id">{{ $n->code }}</td>
                    <td class="text-right" id="amount">${{ $n->amt }}</td>
                    <td class="text-right" id="amount">${{ $n->groupon_amt }}</td>
                    <td class="text-center" id="redeemed">{!! $n->redeemed_appointment('appointment') !!}</td>
                    <td class="text-center" id="redeemed_amt">{!! $n->redeemed_appointment('amount') !!}</td>
                    <td class="text-center" id="redeemed_date">{!! $n->redeemed_appointment('date') !!}</td>
                    <td class="text-center" id="status">{!! $n->status_name() !!}<br>
                        <button type="button" onclick="change_status('{{$n->code}}')" class="btn btn-default btn-sm">Change Status</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="text-right">
            {{ $promo_codes->appends(Request::except('page'))->links() }}
        </div>
    </div>
@stop
