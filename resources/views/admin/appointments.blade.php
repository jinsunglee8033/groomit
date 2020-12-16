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

            $( "#sdate2" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });
            $( "#edate2" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });

            $( "#sdate3" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });
            $( "#edate3" ).datetimepicker({
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

        function todays_appointment() {
            $('[name=status]').val('all');
            $('#sdate3').val('');
            $('#edate3').val('');
            $('#sdate2').val('{{ $today }}');
            $('#edate2').val('{{ $today }}');

            $('#sort_by').val('accepted_date');
            $('#sort_asdc').val('asc');

            $('#frm_search').submit();
        }

        function todays_appointment_ordered() {
            $('[name=status]').val('all');
            $('#sdate2').val('');
            $('#edate2').val('');
            $('#sdate3').val('{{ $today }}');
            $('#edate3').val('{{ $today }}');

            $('#sort_by').val('cdate');
            $('#sort_asdc').val('asc');

            $('#frm_search').submit();
        }

        function sorting_by(sort_by, sort_asdc) {
            $('#sort_by').val(sort_by);
            $('#sort_asdc').val(sort_asdc);
            $('#frm_search').submit();
        }

    </script>


<div class="container-fluid top-cont">
    <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />APPOINTMENTS</h3>
</div>

<div class="container-fluid">
    <div class="well filter" style="padding-bottom:5px;">
        <form id="frm_search" class="form-horizontal" method="post" action="/admin/appointments">
            {{ csrf_field() }}
            <input type="hidden" name="excel" id="excel"/>
            <input type="hidden" name="today" id="today"/>
            <input type="hidden" id="sort_by" name="sort_by" value="{{ $sort_by }}">
            <input type="hidden" id="sort_asdc" name="sort_asdc" value="{{ $sort_asdc }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Requested Date</label>
                        <div class="col-md-8">
                            <input type="text" style="width:100px; float:left;" class="form-control" id="sdate" name="sdate" value="{{ old('sdate', $sdate) }}"/>
                            <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                            <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate" name="edate" value="{{ old('edate', $edate) }}"/>
                        </div>

                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Service Date</label>
                        <div class="col-md-8">
                            <input type="text" style="width:100px; float:left;" class="form-control" id="sdate2" name="sdate2" value="{{ old('sdate2', $sdate2) }}"/>
                            <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                            <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate2" name="edate2" value="{{ old('edate2', $edate2) }}"/>
                        </div>

                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Ordered Date</label>
                        <div class="col-md-8">
                            <input type="text" style="width:100px; float:left;" class="form-control" id="sdate3" name="sdate3" value="{{ old('sdate3', $sdate3) }}"/>
                            <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                            <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate3" name="edate3" value="{{ old('edate3', $edate3) }}"/>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Status</label>
                        <div class="col-md-8">
                            <select class="form-control" name="status" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                <option value="all">All</option>
                                @foreach($appointment_status as $k=>$v)
                                    <option value="{{ $k }}" {{ old('status', $status) == $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Groomer</label>
                        <div class="col-md-8">
                            <select class="form-control" name="groomer" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                <option value="" {{ old('groomer', $groomer) == '' ? 'selected' : '' }}>All</option>
                                @foreach($groomers as $o)
                                    <option value="{{ $o->groomer_id }}" {{ old('groomer', $groomer) == $o->groomer_id ? 'selected' : '' }}>{{ $o->first_name }} {{ $o->last_name }}[{{$o->status}}]</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">User Name</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="name" value="{{ old('name', $name) }}"/>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Phone</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="phone" value="{{ old('phone', $phone) }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Email</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="email" value="{{ old('email', $email) }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">ID</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="appointment_id" value="{{ old('appointment_id', $appointment_id) }}"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Pet Type</label>
                        <div class="col-md-8">
                            <select class="form-control" name="pet_type">
                                <option value="" {{ old('pet_type', $pet_type) == '' ? 'selected' : '' }}>All</option>
                                <option value="dog" {{ old('status', $pet_type) == 'dog' ? 'selected' : '' }}>Dog</option>
                                <option value="cat" {{ old('status', $pet_type) == 'cat' ? 'selected' : '' }}>Cat</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Promo Type</label>
                        <div class="col-md-8">
                            <select name="promo_type" class="form-control">
                                <option value="">All</option>
                                <option value="B" {{ old('promo_type', $promo_type) == 'B' ? 'selected' : ''}}>Affiliate</option>
                                <option value="R" {{ old('promo_type', $promo_type) == 'R' ? 'selected' : ''}}>Referal</option>
                                <option value="N" {{ old('promo_type', $promo_type) == 'N' ? 'selected' : ''}}>Normal</option>
                                <option value="G" {{ old('promo_type', $promo_type) == 'G' ? 'selected' : ''}}>Groupon</option>
                                <option value="T" {{ old('promo_type', $promo_type) == 'T' ? 'selected' :
                                ''}}>GILT</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Booked?</label>
                        <div class="col-md-8">
                            <select name="booked" class="form-control">
                                <option value="">All</option>
                                <option value="B" {{ old('booked', $booked) == 'B' ? 'selected' : ''}}>Booked</option>
                                <option value="O" {{ old('booked', $booked) == 'O' ? 'selected' : ''}}>1 Order</option>
                                <option value="M" {{ old('booked', $booked) == 'M' ? 'selected' : ''}}>Multiple Orders</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Package</label>
                        <div class="col-md-8">
                            <select name="package_id" class="form-control">
                                <option value="">All</option>
                                <option value="1" {{ old('package_id', $package_id) == '1' ? 'selected' : ''}}>Dog - Gold</option>
                                <option value="2" {{ old('package_id', $package_id) == '2' ? 'selected' : ''}}>Dog - Silver</option>
                                <option value="28" {{ old('package_id', $package_id) == '28' ? 'selected' : ''}}>Dog -
                                    ECO</option>
                                <option value="16" {{ old('package_id', $package_id) == '16' ? 'selected' : ''}}>Cat
                                    - Gold</option>
                                <option value="27" {{ old('package_id', $package_id) == '27' ? 'selected' : ''}}>Cat
                                    - Silver</option>
                                <option value="29" {{ old('package_id', $package_id) == '29' ? 'selected' : ''}}>Cat
                                    - ECO</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Order.From</label>
                        <div class="col-md-8">
                            <select name="order_from" class="form-control">
                                <option value="">All</option>
                                <option value="D" {{ old('order_from', $order_from) == 'D' ? 'selected' : ''}}>Web</option>
                                <option value="A" {{ old('order_from', $order_from) == 'A' ? 'selected' : ''}}>App</option>
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
                        <label class="col-md-4 control-label">State</label>
                        <div class="col-md-8">
                            <select class="form-control" name="state">
                                <option value="">All</option>
                                @if (count($states) > 0)
                                    @foreach ($states as $o)
                                        <option value="{{ $o->code }}" {{ old('state', $state) == $o->code ? 'selected' : '' }}>{{ $o->name}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                <!--div class="col-md-4">
                    <div class="form-group">
                        {{--<label class="col-md-4 control-label">Reschedules Only</label>--}}
                        {{--<div class="col-md-8">--}}
                            {{--<input type="checkbox" name="rescheduled" value="Y" {{ old('rescheduled', $rescheduled) == 'Y' ? 'checked' : '' }}/>--}}
                        {{--</div>--}}
                    </div>
                </div-->
            </div>

            <div class="row">
                <div class="col-md-12 text-right">
                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-4">
                            @if (\App\Lib\Helper::get_action_privilege('appointments_search', 'Appointments Search'))
                            <button type="button" class="btn btn-primary btn-sm" id="btn_search" onclick="search()">Search</button>
                            @endif
                            @if (\App\Lib\Helper::get_action_privilege('appointments_export', 'Appointments Export'))
                            <button type="button" class="btn btn-info btn-sm" onclick="excel_export()">Export</button>
                            @endif
                            @if (\App\Lib\Helper::get_action_privilege('appointments_todays_appointment',
                            'Appointments Today\'s Appointment'))
                            <button type="button" class="btn btn-success btn-sm" onclick="todays_appointment()">Today
                                Appointments(Service)</button>
                            <button type="button" class="btn btn-success btn-sm" onclick="todays_appointment_ordered()">Today
                                Appointments(Ordered)</button>
                            @endif
                            @if (\App\Lib\Helper::get_action_privilege('appointments_appointment_schedule',
                                'Appointments Appointment Schedule'))
                            <button type="button" class="btn btn-warning btn-sm" onclick="window.open('/admin/appointment_schedule', '_blank')">Appointment Schedule</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <table id="table" class="table table-bordered display" cellspacing="0" width="100%" style="font-size: 11px;">
        <thead>
        <tr>
            <td colspan="22" class="text-right"><strong>TOTAL</strong>: {{ $total }}</td>
        </tr>
        <tr>
            @php
                $sort_asdc_cdate = $sort_by == 'appointment_id' ? ($sort_asdc == 'asc' ? 'desc' : 'asc') : 'desc';
            @endphp
            <th onclick="sorting_by('appointment_id', '{{ $sort_asdc_cdate }}')" style="cursor: pointer;{{ $sort_by
            == 'appointment_id' ? 'color: orange' : '' }}">ID
                <strong
                        style="float: right;">{{ $sort_asdc_cdate == 'asc' ? '&or;' : '&and;' }}</strong></th>
            @php
                $sort_asdc_cdate = $sort_by == 'cdate' ? ($sort_asdc == 'asc' ? 'desc' : 'asc') : 'desc';
            @endphp
            <th onclick="sorting_by('cdate', '{{ $sort_asdc_cdate }}')" style="cursor: pointer;{{ $sort_by
            == 'cdate' ? 'color: orange' : '' }}">Ordered
                Date <strong style="float: right;">{{ $sort_asdc_cdate == 'asc' ? '&or;' : '&and;' }}</strong></th>
            @php
                $sort_asdc_cdate = $sort_by == 'accepted_date' ? ($sort_asdc == 'asc' ? 'desc' : 'asc') : 'desc';
            @endphp
            <th onclick="sorting_by('accepted_date', '{{ $sort_asdc_cdate }}')" style="cursor: pointer;{{ $sort_by
            == 'accepted_date' ? 'color: orange' : '' }}">Service Date
                <strong style="float: right;">{{ $sort_asdc_cdate == 'asc' ? '&or;' : '&and;' }}</strong></th>
            @php
                $sort_asdc_cdate = $sort_by == 'reserved_date' ? ($sort_asdc == 'asc' ? 'desc' : 'asc') : 'desc';
            @endphp
            <th onclick="sorting_by('reserved_date', '{{ $sort_asdc_cdate }}')" style="cursor: pointer;{{ $sort_by
            == 'reserved_date' ? 'color: orange' : '' }}">Requested
                Date <strong style="float: right;">{{ $sort_asdc_cdate == 'asc' ? '&or;' : '&and;' }}</strong></th>
            <th>Assigned Date</th>
            <th>Assigned Min</th>
            <th>Status</th>
            <th>Fav.Groomer Requested</th>
            <th>Pet Type</th>
            <th>Package</th>
            <th>Promo</th>
            <th>Sub Total</th>
            <th>Promo Amount</th>
            <th>Charged</th>
            <th>User</th>
            <th>Phone</th>
            <th>Address</th>
            @php
                $sort_asdc_cdate = $sort_by == 'groomer_name' ? ($sort_asdc == 'asc' ? 'desc' : 'asc') : 'desc';
            @endphp
            <th onclick="sorting_by('groomer_name', '{{ $sort_asdc_cdate }}')" style="cursor: pointer;{{ $sort_by
            == 'groomer_name' ? 'color: orange' : '' }}">Groomer<strong style="float: right;">{{ $sort_asdc_cdate == 'asc' ? '&or;' : '&and;' }}</strong></th>
            <th>Favorite</th>
            <th>Assigned</th>
            <th>Rating</th>
            <th>Booked</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($appointments as $ap)
            <tr id="appointment/{{ $ap->appointment_id }}"
                @if ($ap->status == 'N')
                    class="danger"
                @elseif ($ap->status == 'D' && $ap->accepted_date_only_date == $today)
                    class="success"
                @elseif ($ap->status == 'D')
                    style="background-color:#AED19F;"
                @elseif ($ap->status == 'O')
                    style="background-color:#FFEFC7;"
                @elseif ($ap->status == 'F')
                    style="background-color:#FFFACD;"
                @elseif ($ap->status == 'W')
                    style="background-color:#d9edf7;"
                @elseif (in_array($ap->status, ['C', 'L']))
                style="background-color:#e3e3e3;"
                @elseif (in_array($ap->status, ['R']))
                style="background-color:#f0ad4e;"
                @endif>
                <td>{{ $ap->appointment_id }}</td>
                <td>{{ $ap->cdate }}</td>
                <td>{{ $ap->accepted_date }}</td>
                <td>{{ \Carbon\Carbon::parse($ap->reserved_date)->format('Y-m-d H:i:s D') }}</td>
                <td>{{ $ap->groomer_assign_date }}</td>
                <td style="text-align: right;{{ $ap->assign_diff >= 15 ? 'color:red;' : '' }}">{{
                            number_format
                            ($ap->assign_diff, 0)
                             }}</td>
                <td>
                    @if($ap->status == 'N')
                        <span class="text-red">{{ $ap->status_name }}</span>
                    @else
                        {{ $ap->status_name }}
                    @endif
                </td>
                <td>
                    @if ($ap->fav_type == 'F')
                        <span class="text-red">YES</span>
                    @elseif ($ap->fav_type == 'N')
                        NO
                    @else

                    @endif
                </td>
                <td>{{ $ap->pet_type }}</td>
                <td>{!! str_replace(',', '<br>', $ap->package) !!}</td>
                <td>{{ $ap->promo_type }}<br>{{ $ap->promo_code }}</td>
                <td>${{ $ap->sub_total }}</td>
                <td>${{ $ap->promo_amt }}</td>
                <td>${{ $ap->total }}</td>
                <td><a href="/admin/user/{{ $ap->user_id }}">{{ $ap->user_name }}<br>ID: {{ $ap->user_id }}</a></td>
                <td>{{ $ap->user_phone }}</td>
                <td>{{ isset($ap->address) ? $ap->address : '' }}</td>
                <td><a href="/admin/groomer/{{ $ap->groomer_id }}">{{ $ap->groomer_name }}</a></td>
                <td>
                    @if (!empty($ap->fav_groomers))
                    @foreach($ap->fav_groomers as $g)
                    <a href="/admin/groomer/{{ $g->groomer_id }}">{{ $g->first_name . ' ' .
                    $g->last_name }}</a><br>
                    @endforeach
                    @endif
                </td>
                <td>{{ $ap->assigned_by }}</td>
                <td>{{ $ap->rating }}</td>
                <?php
                    $user_id = $ap->user_id;
                ?>
                <td>Booked( {{ number_format($ap->sum_total,2) }} / {{ $ap->booked_cnt }} =   {{$ap->booked_cnt>0 ?   number_format($ap->sum_total/$ap->booked_cnt,2) : 0 }})</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="text-right">
        {{ $appointments->appends(Request::except('page'))->links() }}
    </div>
</div>
@stop
