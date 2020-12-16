@extends('includes.admin_default')
@section('contents')
    <script type="text/javascript">
        var onload_func = window.onload;
        window.onload = function() {
            if (onload_func) {
                onload_func();
            }

            $( "#n_expire_date" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });
        }

        function excel_export() {
            $('#excel').val('Y');
            $('#frm_search').submit();
        }

        function load_code(code) {

            @foreach ($packages as $pa)
            $('#n_package_{{ $pa->prod_id }}').prop('checked', false);
            @endforeach

            myApp.showLoading();
            $.ajax({
                url: '/admin/promo_code/load',
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
                        var o = res.promo_code;
                        $('#n_code').val(o.code);
                        $('#n_codes').val(o.code);
                        $('#n_amt_type').val(o.amt_type);
                        $('#n_amt').val(parseFloat(o.amt).toFixed(2));
                        $('#n_note').val(o.note);
                        $('#n_first_only').attr('checked', o.first_only === 'Y');
                        $('#n_influencer').attr('checked', o.influencer === 'Y');
                        $('#n_no_insurance').attr('checked', o.no_insurance === 'Y');
                        $('#n_include_tax').attr('checked', o.include_tax === 'Y');
                        $('#n_status').val(o.status);
                        $('#n_expire_date').val(o.expire_date);
                        $('#n_states').val(o.states);
                        $('#n_valid_user_ids').val('');

                        if (o.package_ids != null && o.package_ids != '' ) {
                            var pids = o.package_ids.split(',');

                            $.each(pids, function( index, value ) {
                                $('#n_package_' + value).prop('checked', true);
                            });
                        }

                        if (o.valid_user_ids != null) {
                            var vuids = '';
                            $.each(o.valid_user_ids, function( i, v ) {
                                vuids += v.user_id + "\n";
                            });
                            $('#n_valid_user_ids').val(vuids);
                        }

                        show_code(o.code);
                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        var current_code = '';

        function save_code() {
            var package_ids = '';
            @foreach ($packages as $pa)
            {{--alert($('#n_package_{{ $pa->prod_id }}').prop('checked'));--}}
            if ($('#n_package_{{ $pa->prod_id }}').prop('checked')) {
                if (package_ids == '') {
                    package_ids = {{ $pa->prod_id }};
                } else {
                    package_ids +=  ',' + {{ $pa->prod_id }};
                }

            }
            @endforeach

            var mode = $.trim(current_code) === ''? 'new' : 'edit';
            var url = mode === 'new' ? '/admin/promo_code/add' : '/admin/promo_code/update';

            //$('#send').submit();
            myApp.showLoading();

            var data = {
                _token: '{!! csrf_token() !!}',
                amt_type: $('#n_amt_type').val(),
                note: $('#n_note').val(),
                amt: $('#n_amt').val(),
                status: $('#n_status').val(),
                expire_date: $('#n_expire_date').val(),
                states: $('#n_states').val(),
                first_only: $('#n_first_only').is(':checked') ? 'Y' : 'N',
                influencer: $('#n_influencer').is(':checked') ? 'Y' : 'N',
                no_insurance: $('#n_no_insurance').is(':checked') ? 'Y' : 'N',
                include_tax: $('#n_include_tax').is(':checked') ? 'Y' : 'N',
                package_ids: package_ids,
                valid_user_ids: $('#n_valid_user_ids').val()
            };

            @if (in_array(Auth::guard('admin')->user()->email, ['jin@jjonbp.com', 'jun@jjonbp.com']))
                data['codes'] = $('#n_codes').val();
                data['code'] = $('#n_codes').val();
                data['type'] = $('#n_type').val();
                // data['groupon_amt'] = $('#n_groupon_amt').val();
            @else
                data['code'] = $('#n_code').val();
            @endif

            $.ajax({
                url: url,
                data: data,
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


        function show_code(code) {
            var mode = typeof code === 'undefined' ? 'new' : 'edit';
            var title = 'Add New Promo Code';

            if (mode === 'new') {
                @foreach ($packages as $pa)
                $('#n_package_{{ $pa->prod_id }}').prop('checked', false);
                @endforeach

                $('#n_code').val('');
                $('#n_amt_type').val('A');
                $('#n_amt').val('');
                $('#n_note').val('');
                $('#n_first_only').attr('checked', false);
                $('#n_influencer').attr('checked', false);
                $('#n_no_insurance').attr('checked', false);
                $('#n_include_tax').attr('checked', false);
                $('#n_status').val('A');
                $('#n_expire_date').val('');
                $('#n_states').val('');
                $('#n_valid_user_ids').val('');

                $('#n_code').attr('disabled', false);
                current_code = '';
            } else {
                $('#n_code').attr('disabled', true);
                title = 'Promo Code Detail';
                current_code = code;
            }

            $('#promo_code_title').text(title);

            $("#add").modal();
        }

    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />PROMO CODES
            <div class="btn-right btn btn-info btn-red-top" onclick="show_code()">Add New</div>
        </h3>
    </div>

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
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/promo_codes">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel"/>
                <div class="row">
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
                            <label class="col-md-4 control-label">Type</label>
                            <div class="col-md-8">
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

                    <div class="col-md-4 text-right">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                @if (\App\Lib\Helper::get_action_privilege('promocodes_search', 'Promocodes Search'))
                                <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                                @endif
                                @if (\App\Lib\Helper::get_action_privilege('promocodes_export', 'Promocodes Export'))
                                <button type="button" class="btn btn-info btn-sm" onclick="excel_export()">Export</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Redeemed</label>
                            <div class="col-md-8">
                                <select class="form-control" name="redeemed" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                    <option value="" {{ old('redeemed', $redeemed) == '' ? 'selected' : '' }}>All</option>
                                    <option value="Y" {{ old('redeemed', $redeemed) == 'Y' ? 'selected' : '' }}>Yes</option>
                                    <option value="N" {{ old('redeemed', $redeemed) == 'N' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">First.Only?</label>
                            <div class="col-md-8">
                                <select class="form-control" name="first_only" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                    <option value="" {{ old('first_only', $first_only) == '' ? 'selected' : '' }}>All</option>
                                    <option value="Y" {{ old('first_only', $first_only) == 'Y' ? 'selected' : '' }}>Yes</option>
                                    <option value="N" {{ old('first_only', $first_only) == 'N' ? 'selected' : '' }}>No</option>
                                </select>

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
                <th class="text-center">Type</th>
                <th class="text-center">Amount Type</th>
                <th class="text-center">Amount</th>
                <th class="text-center">Redeemed<br>(Appointment ID)</th>
                <th class="text-center">Redeemed Amount</th>
                <th class="text-center">Redeemed Date</th>
                <th class="text-center">Influencer?</th>
                <th class="text-center">First.Only?</th>
                <th class="text-center">Status</th>
                <th class="text-center">Owner(Type)</th>
                <th class="text-center">Note</th>
                <th class="text-center"></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($promo_codes as $n)
                <tr>
                    <td class="text-center" id="message_id">
                        <a href="javascript:load_code('{{ $n->code }}')" style="text-decoration: none; color: blue;">{{ $n->code }}</a>
                    </td>
                    <td class="text-center" id="type_name">{{ $n->type_name() }}</td>
                    <td class="text-center" id="amount_type_name">{{ $n->amt_type_name() }}</td>
                    <td class="text-right" id="amount">{{ $n->amt }}</td>
                    <td class="text-center" id="redeemed">{!! $n->redeemed_appointment('appointment') !!}</td>
                    <td class="text-center" id="redeemed_amt">{!! $n->redeemed_appointment('amount') !!}</td>
                    <td class="text-center" id="redeemed_date">{!! $n->redeemed_appointment('date') !!}</td>
                    <td class="text-center" id="influencer">{{ $n->influencer == 'Y' ? 'Yes' : 'No' }}</td>
                    <td class="text-center" id="first_only">{{ $n->first_only == 'Y' ? 'Yes' : 'No' }}</td>
                    <td class="text-center" id="status">{!! $n->status_name() !!}  </td>
                    <td class="text-center" id="owner">
                        @php
                            $ret = \App\Model\PromoCode::get_owner_name_by_code($n->code);
                        @endphp

                        @if (!empty($ret->type))
                            @if ($ret->type == 'user')
                                <a href="/admin/user/{{ $ret->user_id }}">{{ $ret->first_name }}(E)</a>
                            @elseif ($ret->type == 'groomer')
                                <a href="/admin/groomer/{{ $ret->groomer_id }}">{{ $ret->first_name }}(G)</a>
                            @endif
                        @endif
                    </td>
                    <td class="text-center" id="note">{!! $n->note !!}  </td>
                    <td class="text-center">
                        @if (\App\Lib\Helper::get_action_privilege('promocodes_change_status', 'Promocodes Change Status'))
                        <button type="button" onclick="change_status('{{$n->code}}')" class="btn btn-info btn-sm">Change Status</button>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="text-right">
            {{ $promo_codes->appends(Request::except('page'))->links() }}
        </div>

        <!-- Send Modal Start -->
        <div class="modal" id="add" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="promo_code_title">Promo Code Detail</h4>
                    </div>
                    <div class="form-group">
                        <div class="modal-body">
                            @if (in_array(Auth::guard('admin')->user()->email, ['jin@jjonbp.com', 'jun@jjonbp.com']))
                            <div class="row padding-10">
                                <div class="col-xs-4">Promo Codes</div>
                                <div class="col-xs-8">
                                    <textarea class="form-control" type="text" id="n_codes"></textarea>
                                </div>
                            </div>
                            @else
                            <div class="row padding-10">
                                <div class="col-xs-4">Promo Code</div>
                                <div class="col-xs-8">
                                    <input class="form-control" type="text" id="n_code" maxlength="20" />

                                </div>
                            </div>
                            @endif
                                <div class="row padding-10">
                                    <div class="col-xs-4">Promo Note</div>
                                    <div class="col-xs-8">
                                        <input class="form-control" type="text" id="n_note" />
                                    </div>
                                </div>
                            <div class="row padding-10">
                                <div class="col-xs-4">Amount Type</div>
                                <div class="col-xs-8">
                                    <select class="form-control" id="n_amt_type">
                                        <option value="A">Amount</option>
                                        <option value="R">Ratio</option>
                                        <option value="H">Highest Add-On Price</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row padding-10">
                                <div class="col-xs-4">Amount($)<br>or<br>Ratio(%)</div>
                                <div class="col-xs-8">
                                    <input class="form-control" type="number" id="n_amt" />
                                </div>
                            </div>
{{--                            @if (in_array(Auth::guard('admin')->user()->email, ['jin@jjonbp.com', 'jun@jjonbp.com']))--}}
{{--                            <div class="row padding-10">--}}
{{--                                <div class="col-xs-4">Groupon Amount($)</div>--}}
{{--                                <div class="col-xs-8">--}}
{{--                                    <input class="form-control" type="text" id="n_groupon_amt" />--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            @endif--}}

                            <div class="row padding-10">
                                <div class="col-xs-4">For.influencer?</div>
                                <div class="col-xs-8">
                                    <input type="checkbox" id="n_influencer" value="Y"/> Yes
                                </div>
                            </div>

                            <div class="row padding-10">
                                <div class="col-xs-4">First.Only?</div>
                                <div class="col-xs-8">
                                    <input type="checkbox" id="n_first_only" value="Y"/> Yes
                                </div>
                            </div>
                            <div class="row padding-10">
                                <div class="col-xs-4">Include Insurance ?</div>
                                <div class="col-xs-8">
                                    <input type="checkbox" id="n_no_insurance" value="Y"/> Yes <span style="font-size:
                                     11px;color:#444;">(Will your promo code include Insurance ?)</span>
                                </div>
                            </div>
                            <div class="row padding-10">
                                <div class="col-xs-4">Include Tax ?</div>
                                <div class="col-xs-8">
                                    <input type="checkbox" id="n_include_tax" value="Y"/> Yes <span style="font-size:
                                     11px;color:#444;">(Will your promo code include Tax ?)</span><br>
                                    <span style="font-size: 10px;color:#444;">*. The Appointment Tax amount will be
                                        decided by the
                                        final charge
                                        amount to
                                        customers.</span>
                                </div>
                            </div>
                            <div class="row padding-10">
                                <div class="col-xs-4">Expire.Date</div>
                                <div class="col-xs-8">
                                    <input class="form-control" type="text" id="n_expire_date" />
                                </div>
                            </div>
                                <div class="row padding-10">
                                    <div class="col-xs-4">Available States</div>
                                    <div class="col-xs-8">
                                        <input class="form-control" type="text" id="n_states" />( EX: NY, NJ, CA )
                                    </div>
                                </div>
                            <div class="row padding-10">
                                <div class="col-xs-4">Available Package</div>
                                <div class="col-xs-8">
                                    <div class="row">
                                        @foreach ($packages as $p)
                                        <div class="col-xs-4">
                                            <input type="checkbox" id="n_package_{{ $p->prod_id }}" value="Y"/> {{ $p->pet_type
                                    }}-{{ $p->prod_name }}<br>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="row padding-10">
                                <div class="col-xs-4">User IDs</div>
                                <div class="col-xs-8">
                                    <textarea class="form-control" type="text" id="n_valid_user_ids"></textarea>
                                </div>
                            </div>
                            <div class="row padding-10">
                                <div class="col-xs-4">Status</div>
                                <div class="col-xs-8">
                                    <select class="form-control" id="n_status">
                                        <option value="A">Active</option>
                                        <option value="I">Inactive</option>
                                        <option value="U">Used</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-warning" type="button" onclick="save_code()">Submit</button>
                            <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Send Modal End -->
    </div>
@stop
