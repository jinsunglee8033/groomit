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
            $( "#r_sdate" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });
            $( "#r_edate" ).datetimepicker({
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
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />GROOMER EVALUATION REPORT</h3>
    </div>

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/groomer-evaluation">
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
                            <label class="col-md-4 control-label">Groomer</label>
                            <div class="col-md-8">
                                <select class="form-control" id="groomer_id" name="groomer_id">
                                    <option value="">All</option>
                                    @foreach ($groomers as $o)
                                        <option value="{{ $o->groomer_id }}" {{ old('groomer_id', $groomer_id) == $o->groomer_id ? 'selected' : '' }}>{{ $o->first_name . ' ' . $o->last_name }}[{{$o->status}}]</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">County/State</label>
                            <div class="col-md-8">
                                <select class="form-control" name="county">
                                    <option value="">All</option>
                                    @if (count($counties) > 0)
                                        @foreach ($counties as $o)
                                            <option value="{{ $o->county_name . '/' . $o->state_abbr }}" {{ old('county', $county) == $o->county_name . '/' . $o->state_abbr ? 'selected' : '' }}>{{ $o->county_name . '/' . $o->state_abbr }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Accepted By</label>
                            <div class="col-md-8">
                                <select class="form-control" name="accepted_by">
                                    <option value="">All</option>
                                    <option value="G" {{ old('accepted_by', $accepted_by) == 'G' ? 'selected' : '' }}> Groomer </option>
                                    <option value="C" {{ old('accepted_by', $accepted_by) == 'C' ? 'selected' : '' }}> CS </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-md-offset-8 text-right">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="button" class="btn btn-primary btn-sm" id="btn_search" onclick="search()">Search</button>
                                <button type="button" class="btn btn-primary btn-sm" onclick="rating_search()">Rating
                                    Report</button>
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

        <form id="frm_rating_search" class="form-horizontal" method="post" action="/admin/reports/groomer-rating">
            {{ csrf_field() }}
            <input type="hidden" id="r_groomer_id" name="groomer_id" value="{{ old('groomer_id', $groomer_id) }}"/>
            <input type="hidden" style="width:100px; float:left;" class="form-control" id="r_sdate" name="sdate"
                   value="{{ old('sdate', $sdate) }}"/>
            <input type="hidden" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="r_edate"
                   name="edate" value="{{ old('edate', $edate) }}"/>
        </form>
        <script>
            function rating_search() {
                $('#r_groomer_id').val($('#groomer_id').val());
                $('#r_sdate').val($('#sdate').val());
                $('#r_edate').val($('#edate').val());

                $('#frm_rating_search').submit();
            }

            function onclick_ratint(groomer_id) {
                $('#r_groomer_id').val(groomer_id);
                $('#frm_rating_search').submit();
            }
        </script>

        <table class="table table-bordered display" cellspacing="0" width="100%" style="font-size: 11px;">
            <thead>
            <tr>
                <th>Groomer</th>
                <th style="text-align: center;">Accept.Qty</th>
                <th style="text-align: center;">Complete.Qty</th>
                <th style="text-align: center;">Fav.Qty</th>
                <th style="text-align: center;">Rating<br>Avg/CNT</th>
                <th style="text-align: center;">Delayed.Qty</th>
                <th style="text-align: center;">Total.Delayed.Min</th>
                <th style="text-align: center;">Hours.Available</th>
                <th style="text-align: center;">Sub.Total</th>
                <th style="text-align: center;">Promo.Paid</th>
                <th style="text-align: center;">Tip</th>
                <th style="text-align: center;">Profit.Adjustment</th>
                <th style="text-align: center;">Fee</th>
                <th style="text-align: center;">Payout</th>
                <th style="text-align: center;">Weekly.Alowance</th>
                <th style="text-align: center;">P/L</th>
            </tr>
            </thead>
            <tbody>
            @if (count($data) > 0)
                @foreach($data as $o)
{{--                    @if (!empty($groomer_id) || $o->payout != 0)--}}
                    <tr>
                        <td><a href="/admin/profit-sharing/report-new?groomer_id={{ $o->groomer_id }}&sdate={{ old
                        ('sdate', $sdate->format('Y-m-d')) }}&edate={{ old('edate', $edate->format('Y-m-d')) }}">{{
                        $o->groomer_id }}, {{ $o->groomer_name }}</a></td>
                        <td style="text-align: right;">{{ $o->accept_total }}</td>
                        <td style="text-align: right;">{{ $o->appointment_qty }}</td>
                        <td style="text-align: right;">{{ $o->fa_groomer_qty }}</td>
                        <td style="text-align: right;">
                            @if ($o->rate_qty == 0)
                            {{ $o->rate_qty == 0 ? '-' : number_format($o->rate_score / $o->rate_qty, 2) }} / {{ $o->rate_qty }}
                            @else
                                <a onclick="onclick_ratint({{ $o->groomer_id }})" style="cursor: pointer;">
                                {{ $o->rate_qty == 0 ? '-' : number_format($o->rate_score / $o->rate_qty, 2) }} / {{ $o->rate_qty }}
                                </a>
                            @endif
                        </td>
                        <td style="text-align: right;">{{ $o->delayed_qty }}</td>
                        <td style="text-align: right;">{{ $o->delayed_min }}</td>
                        <td style="text-align: right;">{{ $o->hours_total }}</td>
                        <td style="text-align: right;">${{ number_format($o->sub_total, 2) }}</td>
                        <td style="text-align: right;">${{ number_format($o->promo_amt, 2) }}</td>
                        <td style="text-align: right;">${{ number_format($o->tip, 2) }}</td>
                        <td style="text-align: right;">${{ number_format($o->adjust, 2) }}</td>
                        <td style="text-align: right;">${{ number_format($o->groomer_fee, 2) }}</td>
                        <td style="text-align: right;">${{ number_format($o->payout, 2) }}</td>
                        <td style="text-align: right;">${{ number_format($o->weekly_allowance, 2) }}</td>
                        <td style="text-align: right;">{{ $o->weekly_allowance > 0 || $o->payout > 0 ? '$' . number_format
                        ($o->payout - $o->weekly_allowance, 2) : '-' }}</td>
                    </tr>
{{--                    @endif--}}
                @endforeach
            @else
                <tr>
                    <td colspan="13" class="text-center">No Record Found</td>
                </tr>
            @endif
            </tbody>
            <tfoot>
            @if (count($data_summary) > 0)
                @foreach($data_summary as $o)
                    <tr>
                        <th>Total:</th>
                        <th style="text-align: right;">{{ $o->accept_total_qty }}</th>
                        <th style="text-align: right;">{{ $o->appointment_qty }}</th>
                        <th style="text-align: right;">{{ $o->fa_groomer_qty }}</th>
                        <th style="text-align: right;">
                            {{ $o->rate_qty == 0 ? '-' : number_format($o->rate_score /
                        $o->rate_qty, 2) }} / {{ $o->rate_qty }}</th>
                        <th style="text-align: right;">{{ $o->delayed_qty }}</th>
                        <th style="text-align: right;">{{ $o->delayed_min }}</th>
                        <th style="text-align: right;">{{ $o->hours_total }}</th>
                        <th style="text-align: right;">${{ number_format($o->sub_total, 2) }}</th>
                        <th style="text-align: right;">${{ number_format($o->promo_amt, 2) }}</th>
                        <th style="text-align: right;">${{ number_format($o->tip, 2) }}</th>
                        <th style="text-align: right;">${{ number_format($o->adjust, 2) }}</th>
                        <th style="text-align: right;">${{ number_format($o->groomer_fee, 2) }}</th>
                        <th style="text-align: right;">${{ number_format($o->payout, 2) }}</th>
                        <th style="text-align: right;">${{ number_format($o->weekly_allowance, 2) }}</th>
                        <th style="text-align: right;">{{ $o->weekly_allowance > 0 || $o->payout > 0 ? '$' .
                        number_format
                        ($o->payout -
                        $o->weekly_allowance, 2) : '-' }}</th>
                    </tr>
                @endforeach
            @endif
            </tfoot>
        </table>

        <div class="text-right">
            {{ $data->appends(Request::except('page'))->links() }}
        </div>
    </div>

@stop