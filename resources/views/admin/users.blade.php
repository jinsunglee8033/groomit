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

            $( "#service_sdate" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });
            $( "#service_edate" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });

            $('body').on('hidden.bs.modal', '.modal', function () {
                $(this).removeData('bs.modal');
            });
        };

        function excel_export() {
            $('#excel').val('Y');
            $('#frm_search').submit();
            $('#excel').val('N');
        }

    </script>

  <div class="container-fluid top-cont">
      <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />USERS</h3>
  </div>

  <div class="container-fluid">

    <div class="well filter" style="padding-bottom:5px;">
        <form id="frm_search" class="form-horizontal" method="post" action="/admin/users">
            {{ csrf_field() }}
            <input type="hidden" name="excel" id="excel"/>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Reg.Date</label>
                        <div class="col-md-8">
                            <input type="text" style="width:100px; float:left;" class="form-control" id="sdate" name="sdate" value="{{ old('sdate', $sdate) }}"/>
                            <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                            <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate" name="edate" value="{{ old('edate', $edate) }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Order.Date</label>
                        <div class="col-md-8">
                            <input type="text" style="width:100px; float:left;" class="form-control" id="service_sdate" name="service_sdate" value="{{ old('service_sdate', $service_sdate) }}"/>
                            <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                            <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="service_edate" name="service_edate" value="{{ old('service_edate', $service_edate) }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Name(First or Last)</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="name" value="{{ old('name', $name) }}"/>
                        </div>

                    </div>
                </div>
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
                        <label class="col-md-4 control-label">Address(Street1, City, or State)</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="location" value="{{ old('location', $location) }}"/>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Signup From</label>
                        <div class="col-md-8">
                            <select name="register_from" class="form-control">
                                <option value="">All</option>
                                <option value="A" {{ old('register_from', $register_from) == 'A' ? 'selected' : ''}}>App</option>
                                <option value="D" {{ old('register_from', $register_from) == 'D' ? 'selected' : ''}}>Web</option>
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
                                <option value="N" {{ old('booked', $booked) == 'N' ? 'selected' : ''}}>No Booked</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Promo Type(Ever existed)</label>
                        <div class="col-md-8">
                            <select class="form-control" name="promo_type" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                <option value="" {{ old('promo_type', $promo_type) == '' ? 'selected' : '' }}>All</option>
                                <option value="X" {{ old('promo_type', $promo_type) == 'X' ? 'selected' : '' }}>Without Promo Code</option>
                                <option value="O" {{ old('promo_type', $promo_type) == 'O' ? 'selected' : '' }}>Any Of Promo Codes</option>
                                <option  value="" >---------------------</option>
                                <option value="A" {{ old('promo_type', $promo_type) == 'A' ? 'selected' : '' }}>Affiliate</option>
                                <option value="R" {{ old('promo_type', $promo_type) == 'R' ? 'selected' : '' }}>Refer a Friend</option>
                                <option value="N" {{ old('promo_type', $promo_type) == 'N' ? 'selected' : '' }}>Normal</option>
                                <option value="G" {{ old('promo_type', $promo_type) == 'G' ? 'selected' : '' }}>Groupon</option>
                                <option value="T" {{ old('promo_type', $promo_type) == 'T' ? 'selected' : '' }}>Gilt</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">User ID</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="user_id" value="{{ old('user_id', $user_id) }}"/>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Referred Source</label>
                        <div class="col-md-8">
                            <select class="form-control" name="referred_source" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                <option value="" {{ old('referred_source', $referred_source) == '' ? 'selected' : '' }}>All</option>
                                <option value="google" {{ old('referred_source', $referred_source) == "google" ? 'selected' : '' }}>Google</option>
                                <option value="facebook" {{ old('referred_source', $referred_source) == "facebook" ? 'selected' : '' }}>Facebook</option>
                                <option value="youtube" {{ old('referred_source', $referred_source) == "youtube" ? 'selected' : '' }}>YouTube</option>
                                <option value="instagram" {{ old('referred_source', $referred_source) == "instagram" ? 'selected' : '' }}>Instagram</option>
                                <option value="twitter" {{ old('referred_source', $referred_source) == "twitter" ? 'selected' : '' }}>Twitter</option>
                                <option value="yelp" {{ old('referred_source', $referred_source) == "yelp" ? 'selected' : '' }}>Yelp</option>
                                <option value="friends" {{ old('referred_source', $referred_source) == "friends" ? 'selected' : '' }}>Friends</option>
                                <option value="veterinarian" {{ old('referred_source', $referred_source) == "veterinarian" ? 'selected' : '' }}>Veterinarian</option>
                                <option value="spaw" {{ old('referred_source', $referred_source) == "spaw" ? 'selected' : '' }}>Spaw</option>
                                <option value="other" {{ old('referred_source', $referred_source) == "other" ? 'selected' : '' }}>Other</option>
                                <option value="none" {{ old('referred_source', $referred_source) == "none" ? 'selected' : '' }}>No Data</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">State</label>
                        <div class="col-md-8">
                            <select class="form-control" name="state">
                                <option value=""  {{ old('state', $state) == '' ? 'selected' : '' }}>All</option>
                                <option value="CA"  {{ old('state', $state) == 'CA' ? 'selected' : '' }}>CA</option>
                                <option value="CT"  {{ old('state', $state) == 'CT' ? 'selected' : '' }}>CT</option>
                                <option value="FL"  {{ old('state', $state) == 'FL' ? 'selected' : '' }}>FL</option>
                                <option value="NJ"  {{ old('state', $state) == 'NJ' ? 'selected' : '' }}>NJ</option>
                                <option value="NY"  {{ old('state', $state) == 'NY' ? 'selected' : '' }}>NY</option>
                                <option value="PA"  {{ old('state', $state) == 'PA' ? 'selected' : '' }}>PA</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Pet Type</label>
                        <div class="col-md-8">
                            <select class="form-control" name="pet_type" data-jcf='{"wrapNative": false,
                            "wrapNativeOnMobile": false}'>
                                <option value="" {{ old('pet_type', $pet_type) == '' ? 'selected' : '' }}>All</option>
                                <option value="dog" {{ old('pet_type', $pet_type) == 'dog' ? 'selected' : ''
                                }}>Dog</option>
                                <option value="cat" {{ old('pet_type', $pet_type) == 'cat' ? 'selected' : ''
                                }}>Cat</option>
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
                        <label class="col-md-4 control-label">Influencer</label>
                        <div class="col-md-8">
                            <select class="form-control" name="influencer">
                                <option value="" {{ old('influencer', $influencer) == '' ? 'selected' : '' }}>All</option>
                                <option value="Y" {{ old('influencer', $influencer) == 'Y' ? 'selected' : ''}}>Y</option>
                                <option value="N" {{ old('influencer', $influencer) == 'N' ? 'selected' : ''}}>N</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Status</label>
                        <div class="col-md-8">
                            <select class="form-control" name="status">
                                <option value="A" {{ old('status', $status) == 'A' ? 'selected' : ''}}>Active</option>
                                <option value="B" {{ old('status', $status) == 'B' ? 'selected' : ''}}>Fraud</option>
                                <option value="C" {{ old('status', $status) == 'C' ? 'selected' : ''}}>De-Activated</option>
                                <option value="ALL" {{ old('status', $status) == 'ALL' ? 'selected' : '' }}>All</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-4">
                            @if (\App\Lib\Helper::get_action_privilege('users_search', 'Users Search'))
                            <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                            @endif
                            @if (\App\Lib\Helper::get_action_privilege('users_export', 'Users Export'))
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
            <td colspan="13" class="text-right"><strong>TOTAL</strong>: {{ $total }}</td>
        </tr>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Zip</th>
            <th>Address</th>
            <th>Registered.At</th>
            <th>Registered From</th>
            <th>Referred.Source</th>
            <th>Booked.Count</th>
            <th>Last.Order</th>
            <th>Days</th>
            <th>Last.Groomer</th>
            <th>Referral.Code</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($users as $u)
            <tr>
                <td><a href="/admin/user/{{ $u->user_id }}">{{ $u->user_id }}</a></td>
                @if ($u->influencer == 'Y')
                    <td><a href="/admin/user/{{ $u->user_id }}" style="color: red">{{ $u->first_name }} {{ $u->last_name }}</a></td>
                @else
                    <td><a href="/admin/user/{{ $u->user_id }}">{{ $u->first_name }} {{ $u->last_name }}</a></td>
                @endif
                <td>{{ $u->phone }}</td>
                <td>{{ $u->email }}</td>
                <td>{{ $u->zip }}</td>
                <td>
                    @if ($u->address1)
                    {{ $u->address1 }}, {{ $u->city }}, {{ $u->county }}, {{ $u->state }}
                    @endif
                </td>
                <td>{{ $u->cdate }}</td>
                <td>{{ $u->register_from =='D' ? 'Web' :'App' }}</td>
                <td>{{ $u->hear_from }}</td>
                <td>{!! $u->book_cnt !!}</td>
                <td>{!! empty($u->last_appt_date) ? '' : date('m-d-Y', strtotime($u->last_appt_date)) !!}</td>
                <td>{!! empty($u->last_appt_date) ? '' :  Carbon\Carbon::parse($u->last_appt_date)->diffInDays( Carbon\Carbon::now() )  !!}</td>
                <td>{!! $u->last_groomer_fname !!} {!! $u->last_groomer_lname !!}</td>
                <td>{!! $u->refer_code !!}</td>
                <td><div class="btn btn-sm btn-success" data-toggle="modal" href="/admin/history/{{$u->user_id}}/user" data-target="#history">History</div></td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="text-right">
        {{ $users->appends(Request::except('page'))->links() }}
    </div>
  </div>

    <!-- Appointments History Modal start -->
    <div class="modal fade text-center" id="history">
        <div class="modal-dialog" style="width:92%; margin-left:4%; padding: 8px;">
            <div class="modal-content">
            </div>
        </div>
    </div>
    <!-- Appointments History Modal End -->
@stop
