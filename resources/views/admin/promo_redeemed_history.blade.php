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

        function search() {
            $('#excel').val('N');
            $('#frm_search').submit();
        }

    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />PROMO REDEEMED HISTORY</h3></div>
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
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/promo_redeemed_history">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel"/>
                <div class="row">

                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="col-md-5 control-label">Accepted Date</label>
                            <div class="col-md-7">
                                <input type="text" style="width:100px; float:left;" class="form-control" id="sdate" name="sdate" value="{{ old('sdate', $sdate) }}"/>
                                <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                                <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate" name="edate" value="{{ old('edate', $edate) }}"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-5 control-label">Code Type</label>
                            <div class="col-md-7">
                                <select class="form-control" name="type" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                    <option value="" {{ old('type', $type) == '' ? 'selected' : '' }}>All</option>
                                    <option value="B" {{ old('type', $type) == 'B' ? 'selected' : '' }}>Affiliate</option>
                                    <option value="R" {{ old('type', $type) == 'R' ? 'selected' : '' }}>Refer a Friend</option>
                                    <option value="N" {{ old('type', $type) == 'N' ? 'selected' : '' }}>Normal</option>
                                    <option value="G" {{ old('type', $type) == 'G' ? 'selected' : '' }}>Groupon</option>
                                    <option value="T" {{ old('type', $type) == 'T' ? 'selected' : '' }}>GILT</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-5 control-label">Promo Code</label>
                            <div class="col-md-7">
                                <input type="text" class="form-control" name="promo_code" value="{{ old('promo_code', $promo_code) }}"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-5 control-label">Owner Name</label>
                            <div class="col-md-7">
                                <input type="text" class="form-control" name="owner_name" value="{{ old('owner_name', $owner_name) }}"/>
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
                <th class="text-center">Promo Code</th>
                <th class="text-center">Code Type</th>
                <th class="text-center">Amount Type</th>
                <th class="text-center">Amount or Ratio</th>
                <th class="text-center">Created by Who</th>
                <th class="text-center">Owner Name</th>
                <th class="text-center">Code Created Date</th>
                <th class="text-center">Appointment ID</th>
                <th class="text-center">Redeemed Amount</th>
                <th class="text-center">Code Redeemed Date</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($promo_codes as $n)
                <tr>
                    <td class="text-center" id="code">{{ $n->code }}</td>
                    <td class="text-center" id="code_type">
                        @if ($n->type == 'R')
                            Refer a Friend
                        @elseif ($n->type == 'N')
                            Normal
                        @elseif ($n->type == 'A')
                            Affiliate
                        @elseif ($n->type == 'G')
                            Groupon
                        @elseif ($n->type == 'B')
                            Affiliate Code generated by affiliate user
                        @elseif ($n->type == 'K')
                            Voucher
                        @elseif ($n->type == 'T')
                            Gilt
                        @endif
                    </td>
                    <td class="text-right" id="amount_type">
                        @if ($n->amt_type == 'A')
                            Amount
                    <td class="text-right" id="amount_ratio">${{ $n->amt }}</td>
                        @elseif ($n->amt_type == 'R')
                            Ratio
                    <td class="text-right" id="amount_ratio">{{ $n->amt }}%</td>
                        @endif
                    </td>
                    <td class="text-center" id="created_by">{{ $n->name }}</td>
                    <td class="text-center" id="owner_name">{{ $n->owner_name }}</td>
                    <td class="text-center" id="code_created_date">{{ $n->cdate }}</td>
                    <td class="text-center" id="appointment_id">
                        <a href="/admin/appointment/{{ $n->appointment_id }}">{{ $n->appointment_id }}</a>
                    </td>
                    <td class="text-center" id="redeemed_amount">${{ $n->promo_amt }}</td>
                    <td class="text-center" id="accepted_date">{{ $n->accepted_date }}</td>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="text-right">
{{--            {{ $promo_codes->appends(Request::except('page'))->links() }}--}}
        </div>
    </div>
@stop
