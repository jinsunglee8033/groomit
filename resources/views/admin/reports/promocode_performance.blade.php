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

@if (count($errors) > 0)
<div id="error" class="modal fade " tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false"
     style="display:block;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Error</h4>
            </div>
            <div class="modal-body">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

<div class="container-fluid top-cont">
    <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png"
        />PROMOCODE PERFORMANCE REPORT</h3>
</div>

<div class="container-fluid">
    <div class="well filter" style="padding-bottom:5px;">
        <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/promocode-performance">
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
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Package</label>
                        <div class="col-md-8">
                            <select class="form-control" name="package">
                                <option value="">All</option>
                                <option value="1,2,28" {{ $package == '1,2,28' ? 'selected' : '' }}>Dog All</option>
                                <option value="1" {{ $package == '1' ? 'selected' : '' }}>Dog-Gold</option>
                                <option value="2" {{ $package == '2' ? 'selected' : '' }}>Dog-Silver</option>
                                <option value="28" {{ $package == '28' ? 'selected' : '' }}>Dog-Eco</option>
                                <option value="16,27,29" {{ $package == '16,27,29' ? 'selected' : '' }}>Cat All</option>
                                <option value="16" {{ $package == '16' ? 'selected' : '' }}>Cat-Gold</option>
                                <option value="27" {{ $package == '27' ? 'selected' : '' }}>Cat-Silver</option>
                                <option value="29" {{ $package == '29' ? 'selected' : '' }}>Cat-Eco</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-4">
                            <button type="button" class="btn btn-primary btn-sm" id="btn_search" onclick="search()">Search</button>
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
            <th style="text-align: center;">Type/Code</th>
            <th style="text-align: center;">1st Customers <br> (#1)</th>
            <th style="text-align: center;">Repeated Customers <br> (#2)</th>
            <th style="text-align: center;">% of #2/#1</th>
            <th style="text-align: center;">Paid In Full <br> (#3)</th>
            <th style="text-align: center;">% of #3/#1</th>
        </tr>
        </thead>
        <tbody>
        @php
        $t_keys = array_keys($summary_by_type);
        @endphp

        @foreach($t_keys as $bkey)
        <tr>
            <td style="text-align: left;">{{ \App\Model\PromoCode::get_type_name($bkey) }}</td>
            <td style="text-align: right;">{{ $summary_by_type[$bkey]['first_customer_qty'] }}</td>
            <td style="text-align: right;">{{ $summary_by_type[$bkey]['again_customer_qty'] }}</td>
            <td style="text-align: right;">
                @if ($summary_by_type[$bkey]['first_customer_qty'] > 0)
                {{ round($summary_by_type[$bkey]['again_customer_qty'] / $summary_by_type[$bkey]['first_customer_qty'] * 100, 2) }} %
                @endif
            </td>
            <td style="text-align: right;">{{ $summary_by_type[$bkey]['fullpaid_qty'] }}</td>
            <td style="text-align: right;">
                @if ($summary_by_type[$bkey]['first_customer_qty'] > 0)
                    {{ round($summary_by_type[$bkey]['fullpaid_qty'] / $summary_by_type[$bkey]['first_customer_qty'] * 100, 2) }} %
                @endif
            </td>
        </tr>
        @endforeach

        @php
            $c_keys = array_keys($summary_by_code);
        @endphp

        @foreach($c_keys as $ckey)
            <tr>
                <td style="text-align: left;">{{ $ckey }}</td>
                <td style="text-align: right;">{{ $summary_by_code[$ckey]['first_customer_qty'] }}</td>
                <td style="text-align: right;">{{ $summary_by_code[$ckey]['again_customer_qty'] }}</td>
                <td style="text-align: right;">
                    @if ($summary_by_code[$ckey]['first_customer_qty'] > 0)
                        {{ round($summary_by_code[$ckey]['again_customer_qty'] / $summary_by_code[$ckey]['first_customer_qty'] * 100, 2) }} %
                    @endif
                </td>
                <td style="text-align: right;">{{ $summary_by_code[$ckey]['fullpaid_qty'] }}</td>
                <td style="text-align: right;">
                    @if ($summary_by_code[$ckey]['first_customer_qty'] > 0)
                        {{ round($summary_by_code[$ckey]['fullpaid_qty'] / $summary_by_code[$ckey]['first_customer_qty'] * 100, 2) }} %
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th style="text-align: right;">Total:</th>
                <th style="text-align: right;">{{ $total->first_customer_qty }}</th>
                <th style="text-align: right;">{{ $total->again_customer_qty }}</th>
                <th style="text-align: right;">
                    @if ($total->first_customer_qty > 0)
                        {{ round($total->again_customer_qty / $total->first_customer_qty * 100, 2) }} %
                    @endif
                </th>
                <th style="text-align: right;">{{ $total->fullpaid_qty }}</th>
                <th style="text-align: right;">
                    @if ($total->first_customer_qty > 0)
                        {{ round($total->fullpaid_qty / $total->first_customer_qty * 100, 2) }} %
                    @endif
                </th>
            </tr>
        </tfoot>
    </table>

</div>

@stop