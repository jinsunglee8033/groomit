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

        function show_detail(appointment_id) {
            myApp.showLoading();

            $.ajax({
                url: '/admin/profit-sharing/report/load-detail',
                data: {
                    _token: "{!! csrf_token() !!}",
                    appointment_id: appointment_id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();

                    if ($.trim(res.msg) === '') {

                        $('#tbody_detail').empty();

                        $.each(res.data, function(i, o) {

                            var html = '<tr>';

                            html += '<td>' + o.package + '</td>';
                            html += '<td style="text-align:right;">$' + parseFloat(o.sub_total).toFixed(2) + '</td>';
                            html += '<td style="text-align:right;">' + o.groomer_profit_ratio + '%</td>';
                            html += '<td style="text-align:right;">$' + o.groomer_fee + '</td>';
                            html += '<td style="text-align:right;">$' + parseFloat(o.groomer_profit_amt).toFixed(2) +
                                '</td>';
                            html += '<td>' + (o.exception_groomer_id ? o.exception_groomer_id : '-') + '</td>';
                            html += '<td>' + (o.exception_user_id ? o.exception_user_id : '-') + '</td>';
                            html += '<td style="text-align:right;">' + o.orig_profit_ratio + '%</td>';

                            html += '</tr>';

                            $('#tbody_detail').append(html);

                        });

                        $('#div_detail').modal();

                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
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
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />PROFIT SHARING REPORT</h3>
    </div>

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/profit-sharing/report-new">
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
                            <label class="col-md-4 control-label">Type</label>
                            <div class="col-md-8">
                                <select class="form-control" name="type">
                                    <option value="">All</option>
                                    <option value="A" {{ old('type', $type) == 'A' ? 'selected' : '' }}>Appointment</option>
                                    <option value="T" {{ old('type', $type) == 'T' ? 'selected' : '' }}>Tip</option>
                                    <option value="C" {{ old('type', $type) == 'C' ? 'selected' : '' }}>Credit</option>
                                    <option value="D" {{ old('type', $type) == 'D' ? 'selected' : '' }}>Debit</option>
                                    <option value="R" {{ old('type', $type) == 'R' ? 'selected' : '' }}>Groomer Referal</option>
                                    <option value="L" {{ old('type', $type) == 'L' ? 'selected' : '' }}>Void of Groomer Referal</option>
                                </select>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Groomer</label>
                            <div class="col-md-8">
                                <select class="form-control" name="groomer_id">
                                    <option value="">All</option>
                                    @foreach ($groomers as $o)
                                        <option value="{{ $o->groomer_id }}" {{ old('groomer_id', $groomer_id) == $o->groomer_id ? 'selected' : '' }}>{{ $o->first_name . ' ' . $o->last_name }}[{{$o->groomer_id }}]{{ $o->status == 'A' ? '':'[Inactive]' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Customer Name</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="user" value="{{ old('user', $user) }}"/>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Promo Code</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="promo_code" value="{{ old('promo_code', $promo_code) }}"/>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Appointment.ID</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="appointment_id" value="{{ old('appointment_id', $appointment_id) }}"/>
                            </div>

                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Appointment.Type</label>
                            <div class="col-md-8">
                                <select class="form-control" name="appointment_type">
                                    <option value="">All</option>
                                    <option value="dog" {{ old('appointment_type', $appointment_type) == 'dog' ?
                                    'selected' : '' }}>Dog: All</option>
                                    <option value="1" {{ old('appointment_type', $appointment_type) == '1' ?
                                    'selected' : '' }}>Dog: Gold</option>
                                    <option value="2" {{ old('appointment_type', $appointment_type) == '2' ?
                                    'selected' : '' }}>Dog: Silver</option>
                                    <option value="28" {{ old('appointment_type', $appointment_type) == '28' ?
                                    'selected' : '' }}>Dog: ECO</option>
                                    <option value="cat" {{ old('appointment_type', $appointment_type) == 'cat' ?
                                    'selected' : '' }}>Cat: All</option>
                                    <option value="16" {{ old('appointment_type', $appointment_type) == '16' ?
                                    'selected' : '' }}>Cat: Gold</option>
                                    <option value="27" {{ old('appointment_type', $appointment_type) == '27' ?
                                    'selected' : '' }}>Cat: Silver</option>
                                    <option value="29" {{ old('appointment_type', $appointment_type) == '29' ?
                                    'selected' : '' }}>Cat: ECO</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Repeating.User</label>
                            <div class="col-md-8">
                                <select class="form-control" name="repeating">
                                    <option value="">All</option>
                                    <option value="N" {{ old('repeating', $repeating) == 'N' ? 'selected' : '' }}>1st time User</option>
                                    <option value="Y" {{ old('repeating', $repeating) == 'Y' ? 'selected' : '' }}>Repeating User</option>
                                </select>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Promo.Type</label>
                            <div class="col-md-8">
                                <select class="form-control" name="promo_type">
                                    <option value="" {{ old('promo_type', $promo_type) == '' ? 'selected' : '' }}>All</option>
                                    <option value="A" {{ old('promo_type', $promo_type) == 'A' ? 'selected' : '' }}>Affiliate</option>
                                    <option value="R" {{ old('promo_type', $promo_type) == 'R' ? 'selected' : '' }}>Refer a Friend</option>
                                    <option value="N" {{ old('promo_type', $promo_type) == 'N' ? 'selected' : '' }}>Normal</option>
                                    <option value="G" {{ old('promo_type', $promo_type) == 'G' ? 'selected' : '' }}>Groupon</option>
                                    <option value="K" {{ old('promo_type', $promo_type) == 'K' ? 'selected' : '' }}>Voucher Code</option>
                                    <option value="T" {{ old('promo_type', $promo_type) == 'T' ? 'selected' : '' }}>GILT</option>
                                </select>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">County</label>
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
                            <label class="col-md-4 control-label">State</label>
                            <div class="col-md-8">
                                <select class="form-control" name="state">
                                    <option value="">All</option>
                                    @if (count($states) > 0)
                                        @foreach ($states as $s)
                                            <option value="{{ $s->state_abbr }}" {{ old
                                            ('state', $state) == $s->state_abbr ? 'selected' :
                                            '' }}>{{ $s->state_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-md-offset-8 text-right">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                @if (\App\Lib\Helper::get_action_privilege('profitshare_search', 'Profit Share Search'))
                                    <button type="button" class="btn btn-primary btn-sm" id="btn_search" onclick="search()">Search</button>
                                @endif
                                @if (\App\Lib\Helper::get_action_privilege('profitshare_export', 'Profit Share Export'))
                                    <button type="button" class="btn btn-info btn-sm" onclick="excel_export()">Export</button>
                                @endif
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
            <tr style="font-size: 11px;">
                <th rowspan="2">APP.ID</th>
                <th rowspan="2">Customer.Name</th>
                <th rowspan="2">Type</th>
                <th rowspan="2">Pet.#</th>
                <th rowspan="2">APP.Type</th>
                <th rowspan="2">AddOn Amt ($)</th>
                <th rowspan="2">Sub Total ($)</th>
                <th rowspan="2">Tip ($)*</th>
                <th rowspan="2">Promo Amt ($)*</th>
                <th rowspan="2">Marketing<br>Cost ($)*</th>
                <th rowspan="2">Promo Code</th>
                <th rowspan="2">Credit Amt ($)*</th>
                <th rowspan="2">Safety Ins. ($)</th>
                <th rowspan="2">Samday Booking($)</th>
                <th rowspan="2">Fav. Groomer Fee($)</th>
                <th rowspan="2">Tax ($)</th>
                <th rowspan="2">Total ($)</th>
                <th colspan="6">Groomer</th>
                <th rowspan="2">Groupon Payout</th>
                <th rowspan="2">Profit</th>
                <th rowspan="2">Date</th>
                <th rowspan="2">Memo</th>
            </tr>
            <tr style="font-size: 11px;">
                <th>Name</th>
                <th>Commission (%)</th>
                <th>Fee ($)</th>
                <th>Sameday Earning($)</th>
                <th>Fav. Groomer Earning($)</th>
                <th>Commission.Amt ($)</th>
            </tr>
            </thead>
            <tbody style="font-size: 11px;">
            @if (count($data) > 0)
                @foreach($data as $o)
                    <tr>
                        <td><a href="/admin/appointment/{{$o->appointment_id}}" target="_blank">{{ $o->appointment_id }}</a></td>
                        <td><a href="/admin/user/{{$o->user_id}}" target="_blank">{{ $o->customer_name }}</a></td>
                        <td>{{ $o->type_name }}</td>
                        @if ($o->type == 'T')
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td style="text-align: right">{{ $o->tip }}</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td style="text-align: right">{{ $o->sub_total }}</td>
                        @elseif (in_array($o->type, ['C', 'D', 'J', 'R', 'L' ]))
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        @else
                            <td style="text-align: right">{{ $o->pet_qty }}</td>
                            <td>{{ $o->app_package_type }}</td>
                            <td style="text-align: right">{{ $o->add_on_amt }}</td>
                            <td style="text-align: right">{{ $o->sub_total }}</td>
                            <td>-</td>
                            <td style="text-align: right">{{ $o->app_cnt > 1 ? $o->promo_amt : 0 }}</td>
                            <td style="text-align: right">{{ $o->app_cnt == 1 ? $o->promo_amt - $o->groupon_amt : 0 }}</td>
                            <td style="text-align: right">@if($o->promo_amt > 0){!! $o->promo_type . ':' . $o->promo_code . (!empty($o->groupon_seq) ? ' ' . $o->groupon_seq : '') !!} @else - @endif</td>
                            <td style="text-align: right">{{ $o->credit_amt }}</td>
                            <td style="text-align: right">{{ $o->safety_insurance }}</td>
                            <td style="text-align: right">{{ $o->sameday_booking }}</td>
                            <td style="text-align: right">{{ $o->fav_groomer_fee }}</td>
                            <td style="text-align: right">{{ $o->tax }}</td>
                            <td style="text-align: right">{{ $o->total_amt }}</td>
                        @endif
                        <td><a href="/admin/groomer/{{ $o->groomer_id }}" target="_blank">{{ $o->groomer_name . ' (' . $o->groomer_id
                        . ')
                        '}}</a></td>
                        <td style="text-align: right">{{ $o->groomer_profit_ratio }}%</td>
                        <td style="text-align: right">{{ $o->groomer_fee }}</td>
                        <td style="text-align: right">{{ $o->groomer_sameday_earning }}</td>
                        <td style="text-align: right">{{ $o->groomer_fav_earning }}</td>
                        <td style="text-align: right"><a href="javascript:show_detail('{{ $o->appointment_id }}')">{{ $o->groomer_profit_amt }}</a></td>
                        @if ($o->type == 'T' || $o->groupon_amt == 0)
                            <td style="text-align: right">0</td>
                        @else
                            <td style="text-align: right">{{ number_format($o->groupon_amt, 2, '.', '') }}</td>
                        @endif
                        <td style="text-align: right">
                            {{ number_format($o->profit_amt, 2) }}
                        </td>
                        <td>
                            <a href="/admin/reports/groomer-rating/{{ $o->groomer_id }}/{{ $o->user_id }}"
                               class="btn-left btn" target="_blank">
                            {{ Carbon\Carbon::parse($o->cdate)->format('m/d/Y h:i A') }}
                            </a>
                        </td>
                        <td>{{ $o->comments }}</td>
                    </tr>
                @endforeach
                <tr class="info">
                    <td colspan="2">TOTAL APPOINTMENTS : <strong>{{ $total->cnt }}</strong></td>
                    <td class="text-right"><strong>TOTAL</strong></td>
                    <td style="text-align: right">{{ number_format($total->sum_pet_qty, 0, ',', '') }}</td>
                    <td></td>
                    <td style="text-align: right">{{ number_format($total->sum_add_on_amt, 2, '.', '') }}</td>
                    <td style="text-align: right">{{ number_format($total->sum_sub_total_amt, 2, '.', '') }}</td>
                    <td style="text-align: right">{{ number_format($total->sum_tip_amt, 2, '.', '') }}</td>
                    <td style="text-align: right">${{ number_format($total->sum_promo_amt, 2, '.', '') }}</td>
                    <td style="text-align: right">${{ number_format($total->sum_market_amt, 2, '.', '') }}</td>
                    <td></td>
                    <td style="text-align: right">{{ number_format($total->sum_credit_amt, 2, '.', '') }}</td>
                    <td style="text-align: right">{{ number_format($total->sum_safety_insurance_amt, 2, '.', '') }}</td>
                    <td style="text-align: right">{{ number_format($total->sum_sameday_booking_amt, 2, '.', '') }}</td>
                    <td style="text-align: right">{{ number_format($total->sum_fav_groomer_fee_amt, 2, '.', '') }}</td>
                    <td style="text-align: right">{{ number_format($total->sum_tax_amt, 2, '.', '') }}</td>
                    <td style="text-align: right">{{ number_format($total->sum_total_amt, 2, '.', '') }}</td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right">{{ number_format($total->sum_groomer_fee_amt, 2, '.', '') }}</td>
                    <td style="text-align: right">{{ number_format($total->sum_groomer_sameday_earning_amt, 2, '.', '') }}</td>
                    <td style="text-align: right">{{ number_format($total->sum_groomer_fav_earning_amt, 2, '.', '') }}</td>
                    <td style="text-align: right">{{ number_format($total->sum_groomer_profit_amt - $total->sum_groomer_credit_amt, 2, '.','') }}
                                  <br>Credit: {{ number_format($total->sum_groomer_credit_amt, 2, '.', '') }}
                                  <br>Total: {{ number_format($total->sum_groomer_profit_amt, 2, '.', '') }}
                    </td>
                    <td style="text-align: right">{{ number_format($total->sum_groupon_amt, 2, '.', '') }}</td>
                    <td style="text-align: right">{{ number_format($total->sum_profit, 2, '.', '') }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @else
                <tr>
                    <td colspan="100" class="text-center">No Records Found</td>
                </tr>
            @endif
            </tbody>
        </table>

        <div class="text-right">
            {{ $data->appends(Request::except('page'))->links() }}
        </div>
    </div>

    <div class="modal" id="div_detail" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">x</span></button>
                    <h4 class="modal-title" id="title">Commisison Detail</h4>
                </div>
                <div class="modal-body">

                    <table class="table table-responsive table-condensed table-bordered">
                        <thead class="thead-default">
                        <tr>
                            <th rowspan="2" style="text-align:center;">Package</th>
                            <th rowspan="2" style="text-align:center;">Sub.Total</th>
                            <th rowspan="2" style="text-align:center;">Profit.Ratio(%)</th>
                            <th rowspan="2" style="text-align:center;">Fee($)</th>
                            <th rowspan="2" style="text-align:center;">Profit.Amt($)</th>
                            <th colspan="2" style="text-align:center;">Exception</th>
                            <th rowspan="2" style="text-align:center;">Orig.Ratio(%)</th>
                        </tr>
                        <tr>
                            <th style="text-align:center;">Groomer</th>
                            <th style="text-align:center;">User</th>
                        </tr>
                        </thead>
                        <tbody id="tbody_detail">
                        <tr>
                            <td colspan="15">No Record Found</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer" style="margin-right:15px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@stop