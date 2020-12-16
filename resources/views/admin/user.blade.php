@extends('includes.admin_default')
@section('contents')

    <script type="text/javascript">
        window.onload = function() {
            $('.update_address').click(function () {
                var indexKey = $(this).data('id');

                $("input[name='address_id']").val($("#address_id" + indexKey).val());
                $("input[name='name']").val($("#name" + indexKey).text());
                $("input[name='address1']").val($("#address1" + indexKey).text());
                $("input[name='address2']").val($("#address2" + indexKey).text());
                $("input[name='city']").val($("#city" + indexKey).text());
                $("input[name='state']").val($("#state" + indexKey).text());
                $("input[name='zip']").val($("#zip" + indexKey).text());
            });

            $('.update_billing').click(function () {
                var indexKey = $(this).data('id');
                var card_type = $("#card_type" + indexKey).text();

                $("input[name='billing_id']").val($("#billing_id" + indexKey).text());
                $("input[name='card_holder']").val($("#card_holder" + indexKey).text());
                $("select[name='card_type'] option[value='" + card_type + "']").prop('selected', true);
                $("input[name='card_number']").val($("#card_number" + indexKey).text());
                $("input[name='expire_yy']").val($("#expire_yy" + indexKey).text());
                $("input[name='expire_mm']").val($("#expire_mm" + indexKey).text());
                $("input[name='b_address1']").val($("#b_address1" + indexKey).text());
                $("input[name='b_address2']").val($("#b_address2" + indexKey).text());
                $("input[name='b_city']").val($("#b_city" + indexKey).text());
                $("input[name='b_state']").val($("#b_state" + indexKey).text());
                $("input[name='b_zip']").val($("#b_zip" + indexKey).text());
            });

            $( "#expire_date" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });
        };

        function change_billing_status(indexKey) {
            myApp.showLoading();
            $.ajax({
                url: '/admin/user/change_billing_status',
                data: {
                    _token: '{{ csrf_token() }}',
                    user_id: '{{$user->user_id}}',
                    billing_id: $("#billing_id" + indexKey).text(),
                    status: $("#status" + indexKey).text(),
                    card_number: $("#card_number" + indexKey).text()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        $('#change_status').modal('hide');
                        myApp.showSuccess('Your request has been processed successful!', function() {
                            window.location.reload();
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function update_op_note() {
            myApp.showLoading();

            $.ajax({
                url: '/admin/user/update-op-note',
                data: {
                    _token: '{!! csrf_token() !!}',
                    user_id: '{{ $user->user_id }}',
                    op_note: $('#op_note').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        myApp.showSuccess('Your request has been processed successful!', function() {
                            window.location.reload();
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function update_yelp_review() {
            myApp.showLoading();

            $.ajax({
                url: '/admin/user/update-yelp-review',
                data: {
                    _token: '{!! csrf_token() !!}',
                    user_id: '{{ $user->user_id }}',
                    yelp_review: $('#yelp_review').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        myApp.showSuccess('Your request has been processed successful!', function() {
                            window.location.reload();
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function load_appointments() {
            myApp.showLoading();

            $('#recent').load('/admin/recent/{{ $user->user_id }}/user', function() {
                $('#upcoming').load('/admin/upcoming/{{ $user->user_id }}/user', function() {
                    $('#cancel').load('/admin/appointment_cancel/{{ $user->user_id }}/user/pop', function() {
                        $('#vouchers').load('/admin/voucher/sales/{{ $user->user_id }}/pop', function () {
                            myApp.hideLoading();
                            $('#appointments').modal();
                            $('#upcoming').find('div.padding-10').find('button').parent().hide();
                            $('#upcoming').find('div.padding-10').find('button').find('span').parent().parent().show();
                            $('#recent').find('div.padding-10').find('button').find('span').parent().hide();
                            $('#recent').find('div.padding-10').find('button').parent().hide();
                            $('#cancel').find('div.padding-10').find('button').find('span').parent().hide();
                            $('#cancel').find('div.padding-10').find('button').parent().hide();
                            $('#vouchers').find('div.padding-10').find('button').find('span').parent().hide();
                        })
                    })
                })
            })
        }

        function remove_favorite_groomer(groomer_id) {
            myApp.showConfirm('Are you sure to proceed?', function() {
                myApp.showLoading();
                $.ajax({
                    url: '/admin/user/favorite-groomer/remove',
                    data: {
                        _token: '{!! csrf_token() !!}',
                        user_id: '{{ $user->user_id }}',
                        groomer_id: groomer_id
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function(res) {
                        myApp.hideLoading();
                        if ($.trim(res.msg) === '') {
                            myApp.showSuccess('Your request has been processed successfully!', function() {
                                //$('#btn_remove_favorite_groomer_' + groomer_id).parent('label').remove();
                                window.location.reload();
                            })
                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                });
            });
        }

        function remove_blocked_groomer(groomer_id) {
            myApp.showConfirm('Are you sure to proceed?', function() {
                myApp.showLoading();
                $.ajax({
                    url: '/admin/user/blocked-groomer/remove',
                    data: {
                        _token: '{!! csrf_token() !!}',
                        user_id: '{{ $user->user_id }}',
                        groomer_id: groomer_id
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function(res) {
                        myApp.hideLoading();
                        if ($.trim(res.msg) === '') {
                            myApp.showSuccess('Your request has been processed successfully!', function() {
                                //$('#btn_remove_favorite_groomer_' + groomer_id).parent('label').remove();
                                window.location.reload();
                            })
                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                });
            });
        }

        function add_favorite_groomer() {
            var groomer_id = $('#favorite_groomer_id').val();
            if ($.trim(groomer_id) === '') {
                myApp.showError('Please select groomer first');
                return;
            }

            myApp.showConfirm('Are you sure to proceed?', function() {
                myApp.showLoading();
                $.ajax({
                    url: '/admin/user/favorite-groomer/add',
                    data: {
                        _token: '{!! csrf_token() !!}',
                        user_id: '{{ $user->user_id }}',
                        groomer_id: groomer_id
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function(res) {
                        myApp.hideLoading();
                        if ($.trim(res.msg) === '') {
                            myApp.showSuccess('Your request has been processed successfully!', function() {
                                window.location.reload();
                            });
                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                })
            })
        }

        function add_blocked_groomer() {
            var groomer_id = $('#blocked_groomer_id').val();
            if ($.trim(groomer_id) === '') {
                myApp.showError('Please select groomer first');
                return;
            }

            myApp.showConfirm('Are you sure to proceed?', function() {
                myApp.showLoading();
                $.ajax({
                    url: '/admin/user/blocked-groomer/add',
                    data: {
                        _token: '{!! csrf_token() !!}',
                        user_id: '{{ $user->user_id }}',
                        groomer_id: groomer_id
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function(res) {
                        myApp.hideLoading();
                        if ($.trim(res.msg) === '') {
                            myApp.showSuccess('Your request has been processed successfully!', function() {
                                window.location.reload();
                            });
                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                })
            })
        }

        function update_status() {
            myApp.showLoading();

            $.ajax({
                url: '/admin/user/update-status',
                data: {
                    _token: '{!! csrf_token() !!}',
                    user_id: '{{ $user->user_id }}',
                    status: $('#user_status').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        myApp.showSuccess('Your request has been processed successful!', function() {
                            $('#user_status_msg').text('updated');
                            $('#user_status_old').val($('#user_status').val());
                        });
                    } else {
                        $('#user_status_msg').text(res.msg);
                        $('#user_status').val($('#user_status_old').val());
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }
    </script>


<div class="container-fluid top-cont">
    <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />User Detail</h3>
</div>

<div class="container-fluid">
    <div class="well filter" style="padding-bottom:5px;">
        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <a href="/admin/users" class="btn btn-info">Back to List</a>
                </div>
            </div>

            <div class="col-md-10">
                <div class="col-md-2">
                <div class="form-group">
                    <a href="/admin/message_user/{{$user->user_id}}" class="btn-left btn btn-danger"
                       target="_blank">SEND TEXT</a>
                </div>
                </div>
                @if (\App\Lib\Helper::get_action_privilege('user_detail_update_profile', 'User Detail Update Profile'))
                <div class="btn-right btn btn-info" data-toggle="modal" data-target="#update">Update Profile</div>
                @endif
                <form class="btn-right" action="/admin/user/login-as" method="post">
                    <input type="hidden" name="user_id" value="{{ $user->user_id }}"/>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button class="btn btn-success">Login As</button>
                </form>
                <div class="btn-right btn btn-success" data-toggle="modal" data-target="#reset_password">Reset Password</div>
                <form class="btn-right" action="/admin/messages" method="post" target="_blank">
                    <input type="hidden" name="user_id" value="{{$user->user_id}}"/>
                    <input type="hidden" name="no_date" value="Y"/>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button class="btn btn-warning">Messages</button>
                </form>

                <div class="btn-right btn btn-info" data-toggle="modal" onclick="load_appointments()">Transactions</div>

                <div style="display:none;" class="btn-right btn btn-info" data-toggle="modal" href="/admin/recent/{{$user->user_id}}/user" data-target="#recent">Appointments</div>
                <div style="display:none;" class="btn-right btn btn-info" data-toggle="modal" href="/admin/upcoming/{{$user->user_id}}/user" data-target="#upcoming">Upcoming Appointments</div>



            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div id="user" class="detail application">
        <div class="row no-border">

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


            @if ($user->profile_photo)
                <div class="col text-center">
                    <img src="data:image/png;base64,{{ $user->profile_photo }}"/>
                </div>
            @endif


            <div class="col">
                <div class="row">
                    <div class="col-xs-3">User ID</div>
                    <div class="col-xs-9">{{ $user->user_id }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Status</div>
                    <div class="col-xs-9">
                        <select id="user_status" onchange="update_status()">
                            <option value="A" {{ $user->status == 'A' ? 'selected': '' }}>Active</option>
                            <option value="B" {{ $user->status == 'B' ? 'selected': '' }}>Fraud</option>
                            <option value="C" {{ $user->status == 'C' ? 'selected': '' }}>Deactivated</option>
                        </select>
                        <small><span id="user_status_msg"></span></small>

                        {{ isset($user->frauds) ? $user->frauds[0]->code_name : '' }}
                        <input type="hidden" id="user_status_old" value="{{ $user->status }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Influencer</div>
                    <div class="col-xs-9">{{ $user->influencer }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Register From</div>
                    <div class="col-xs-9">{{ $user->register_from_name }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Hear From</div>
                    <div class="col-xs-9">{{ $user->hear_from }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Referral Url</div>
                    <div class="col-xs-9">{{ strpos($user->referral_url, 'http') !== false ? $user->referral_url : '' }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Register At</div>
                    <div class="col-xs-9">{{ $user->cdate }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Name</div>
                    <div class="col-xs-9">{{ $user->first_name }} {{ $user->last_name }}</div>
                </div>
                <div class="row">
                    <div class="col-xs-3">Phone</div>
                    <div class="col-xs-9">{{ $user->phone }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Email</div>
                    <div class="col-xs-9">{{ $user->email }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Referral Code</div>
                    <div class="col-xs-9">{{ $user->referral_code }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Available Credit ($)</div>
                    <div class="col-xs-9">{{ $user->available_credit }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Pets</div>
                    <div class="col-xs-9">
                        @foreach($user->pets as $indexKey => $pet)
                            <div class="col-xs-9">
                                <a href="/admin/pet/{{ $pet->pet_id }}">{{$pet->name}} {{ ($pet->status =='A') ? '[Active]' : '[Deactivated]'  }}</a>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Pet Type Signup (Dog)</div>
                    <div class="col-xs-9">{{ $user->dog }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Pet Type Signup (Cat)</div>
                    <div class="col-xs-9">{{ $user->cat }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Address</div>
                    <div class="col-xs-9">
                        @foreach($user->address as $indexKey => $a)
                            <div class="row">
                                <div class="col-xs-9">
                                    <div class="hidden" id="name{{ $indexKey }}">{{ $a->name }}</div>
                                    {{--<div class="col-xs-4 alert-info">ID</div>--}}
                                    {{--<div class="col-xs-6" id="address_id{{ $indexKey }}">{{ $a->address_id }}</div><br>--}}
                                    <input type="hidden" id="address_id{{ $indexKey }}" value="{{ $a->address_id }}"/>
                                    <span class="col-xs-4 alert-info">Status</span>
                                    <span class="col-xs-6">{!! $a->status_name() !!}</span><br>
                                    <div class="col-xs-4 alert-info">Address</div>
                                    <div class="col-xs-6">
                                        <span id="address1{{ $indexKey }}">{{ $a->address1 }}</span>,
                                        <span id="address2{{ $indexKey }}">{{ $a->address2 }}</span>,
                                        <span id="city{{ $indexKey }}">{{ $a->city }}</span>,
                                        <span id="state{{ $indexKey }}">{{ $a->state }}</span>
                                        <span id="zip{{ $indexKey }}">{{ $a->zip }}</span>
                                    </div>
                                </div>
                                <div class="col-xs-3">
                                    <div class="btn-right btn btn-default update_address" data-id="{{ $indexKey }}" data-toggle="modal" data-target="#update_address">Update</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Billing</div>
                    <div class="col-xs-9">
                        @foreach($user->billing as $indexKey => $b)
                            <div class="row">
                                <div class="col-xs-8">
                                    <span class="col-xs-4 alert-warning">ID</span>
                                    <span class="col-xs-8" id="billing_id{{ $indexKey }}">{{ $b->billing_id }}</span><br>

                                    <span class="col-xs-4 alert-warning">Status</span>
                                    <span class="col-xs-8">{!! $b->status_name() !!}</span><br>
                                    <span class="col-xs-4 alert-warning">Default Card</span>
                                    <span class="col-xs-8">{!! $b->default_card !!}</span><br>

                                    <span class="col-xs-4 alert-warning">Card Holder</span>
                                    <span class="col-xs-8" id="card_holder{{ $indexKey }}">{{ $b->card_holder }}</span><br>
                                    <span id="card_type{{ $indexKey }}" class="hidden">{{ $b->card_type }}</span>
                                    <span id="status{{ $indexKey }}" class="hidden">{{ $b->status }}</span>
                                    <span class="col-xs-4 alert-warning">Card Number</span>
                                    <span class="col-xs-8" id="card_number{{ $indexKey }}">{{ $b->card_number }}</span><br>
                                    <span class="col-xs-4 alert-warning">Expire</span>
                                    <span class="col-xs-8">
                                        <span id="expire_mm{{ $indexKey }}">{{ $b->expire_mm }}</span> /
                                        <span id="expire_yy{{ $indexKey }}">{{ $b->expire_yy }}</span>
                                    </span><br>

                                    <span class="col-xs-4 alert-warning">Address</span>
                                    <div class="col-xs-8">
                                        <span id="b_address1{{ $indexKey }}">{{ $b->address1 }}</span>,
                                        <span id="b_address2{{ $indexKey }}">{{ $b->address2 }}</span>,
                                        <span id="b_city{{ $indexKey }}">{{ $b->city }}</span>,
                                        <span id="b_state{{ $indexKey }}">{{ $b->state }}</span>
                                        <span id="b_zip{{ $indexKey }}">{{ $b->zip }}</span>
                                    </div>

                                </div>
                                <div class="col-xs-4">
                                    <div class="btn-right btn btn-default update_billing" data-id="{{ $indexKey }}" data-toggle="modal" data-target="#update_billing">Update</div>
                                    @if ($b->status == 'A')
                                    <div class="btn-right btn btn-danger" onclick="change_billing_status({{ $indexKey }})">Deactivate</div>
                                    @else
                                        <div class="btn-right btn btn-success" onclick="change_billing_status({{ $indexKey }})">Activate</div>
                                    @endif

                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Favorite Groomers</div>
                    <div class="col-xs-9">
                        @foreach($user->favorite_groomer as $indexKey => $a)
                            <label>
                                <a href="/admin/groomer/{{ $a->groomer_id }}">{{ $a->first_name }} {{ $a->last_name }}</a>
                                <button type="button" class="btn btn-xs btn-danger" id="btn_remove_favorite_groomer_{{ $a->groomer_id }}" onclick="remove_favorite_groomer('{{ $a->groomer_id }}')">x</button>
                            </label>
                        @endforeach
                        |
                        <label>
                            <select id="favorite_groomer_id">
                                <option value="">Add Favorite Groomer</option>
                                @if (count($groomers) > 0)
                                    @foreach ($groomers as $o)
                                        <option value="{{ $o->groomer_id }}">{{ $o->first_name }} {{ $o->last_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <button type="button" class="btn btn-xs btn-primary" onclick="add_favorite_groomer()">Add</button>
                        </label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Blocked Groomers</div>
                    <div class="col-xs-9">
                        @foreach($user->blocked_groomer as $indexKey => $a)
                            <label>
                                <a href="/admin/groomer/{{ $a->groomer_id }}">{{ $a->first_name }} {{ $a->last_name }}</a>
                                <button type="button" class="btn btn-xs btn-danger" id="btn_remove_blocked_groomer_{{ $a->groomer_id }}" onclick="remove_blocked_groomer('{{ $a->groomer_id }}')">x</button>
                            </label>
                        @endforeach
                        |
                        <label>
                            <select id="blocked_groomer_id">
                                <option value="">Add Blocked Groomer</option>
                                @if (count($groomers) > 0)
                                    @foreach ($groomers as $o)
                                        <option value="{{ $o->groomer_id }}">{{ $o->first_name }} {{ $o->last_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <button type="button" class="btn btn-xs btn-primary" onclick="add_blocked_groomer()">Add</button>
                        </label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Groomer Preference</div>
                    <div class="col-xs-9">
                        @if($user->groomer_prefer == 'M')
                            {{'Male groomers only'}}
                        @elseif($user->groomer_prefer == 'F')
                            {{'Female groomers only'}}
                        @else
                            No
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Operation.Note</div>
                    <div class="col-xs-6">
                        <textarea id="op_note" name="op_note" style="width:100%" rows="5">{!! $user->op_note !!}</textarea>
                    </div>
                    <div class="col-xs-3 text-center">
                        <button type="button" class="btn btn-danger" onclick="update_op_note()">Update</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-3">Yelp.Review</div>
                    <div class="col-xs-6">
                        <textarea id="yelp_review" name="yelp_review" style="width:100%" rows="5">{{ $user->yelp_review
                        }}</textarea>
                    </div>
                    <div class="col-xs-3 text-center">
                        <button type="button" class="btn btn-danger" onclick="update_yelp_review()">Update</button>
                    </div>
                </div>


                <div class="row"><a name="credit_history"></a>
                    <div class="col-xs-3">Credit/Debit History</div>
                    <div class="col-xs-9 text-right">
                        <button type="button" class="btn btn-info" onclick="$('#credit_modal').modal()"> New
                            Credit/Debit</button>
                        <table class="table table-striped display text-left" cellspacing="0" width="100%"
                               style="font-size:
                        12px;margin-top: 16px;">
                            <thead>
                            <tr>
                                <th>Created.Date</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Amount($)</th>
                                <th>Expire.Date</th>
                                <th>Comment</th>
                                <th>Referral.Code</th>
                                <th>Owner</th>
                                <th>Appointment.ID</th>
                                <th>Status</th>
                                <th>Order.Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if (count($credit_data) > 0)
                                @foreach ($credit_data as $o)
                                    <tr>
                                        <td>{{ $o->cdate }}</td>
                                        <td>{{ $o->type == 'C' ? 'Credit' : 'Debit' }}</td>
                                        <td>{{ \App\Model\Credit::get_category_name($o->category) }}</td>
                                        <td>{{ $o->amt }}</td>
                                        <td>{{ $o->expire_date }}</td>
                                        <td>{{ $o->notes }}</td>
                                        <td><a href="/admin/promo_codes/{{ $o->referral_code }}">{{ $o->referral_code }}</a></td>
                                        <td>
                                            @php
                                                $ret = \App\Model\PromoCode::get_owner_name_by_code($o->referral_code);
                                            @endphp

                                            @if (!empty($ret->type))
                                                @if ($ret->type == 'user')
                                                    <a href="/admin/user/{{ $ret->user_id }}">{{ $ret->first_name }} (E)</a>
                                                @elseif ($ret->type == 'groomer')
                                                    <a href="/admin/groomer/{{ $ret->groomer_id }}">{{ $ret->first_name }} (G)</a>
                                                @endif
                                            @endif
                                        </td>
                                        <td><a href="/admin/appointment/{{ $o->appointment_id }}">{{ $o->appointment_id }}</a></td>
                                        <td>{{ $o->status }}</td>
                                        <td>{{ $o->order_date }}</td>
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
                            {{ $credit_data->appends(Request::except('page'))->links() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assign Groomer Modal Start -->
            <div class="modal" id="credit_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form class="form-horizontal" method="post" action="/admin/user/{{ $user->user_id }}/add_credit">
                            {{ csrf_field() }}
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">New Credit/Debit</h4>
                            </div>
                            <div class="modal-body">

                                <div id="result"></div>

                                <div class="row">
                                    <div class="col-xs-4 text-right">Type</div>
                                    <div class="col-xs-8">
                                        <select class="form-control" name="type">
                                            <option value="">Please Select</option>
                                            <option value="C">Credit</option>
                                            <option value="D">Debit</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-xs-4 text-right">Category</div>
                                    <div class="col-xs-8">
                                        <select class="form-control" name="category">
                                            <option value="">Please Select</option>
                                            <option value="N">Normal</option>
                                            <option value="T">Store Credit</option>
                                            <option value="R">Referral Credit</option>
                                            <option value="S">Signup Credit</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-4 text-right">Amount</div>
                                    <div class="col-xs-8">
                                        <input type="text" class="form-control" name="amt" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-xs-4 text-right">Expire Date</div>
                                    <div class="col-xs-7">
                                        <input type="text" class="form-control" id="expire_date" name="expire_date"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-4 text-right">Comment</div>
                                    <div class="col-xs-8">
                                        <textarea class="form-control" name="comments"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                        </div>
                                    </div>
                                    <div class="col-md-8 text-right">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                            <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Upcoming Appointments Modal start -->
            <div class="modal fade text-center" id="appointments">
                <div class="modal-dialog" style="width:90%;">
                    <div class="modal-content">
                        <div id="upcoming"></div>
                        <div id="recent" style="margin-top:20px;"></div>
                        <div id="cancel" style="margin-top:20px;"></div>
                        <div id="vouchers" style="margin-top:-10px;"></div>
                    </div>
                </div>
            </div>
            <!-- Upcoming Appointments Modal End -->


            <!-- Update Profile Modal start-->
            <div class="modal fade" id="update" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Update User's Information</h4>
                        </div>
                        <form method="post" name="update" action="/admin/user/update" class="form-group">
                            {!! csrf_field() !!}

                            <input type="hidden" name="id" value="{{$user->user_id}}" />
                            <div class="modal-body">
                                <div class="row no-border">
                                    <div class="col-xs-3">Name</div>
                                    <div class="col-xs-4">
                                        <input type="text" name="first_name" class="form-control" value="{{ $user->first_name }}" />
                                    </div>
                                    <div class="col-xs-4">
                                        <input type="text" name="last_name" class="form-control" value="{{ $user->last_name }}" />
                                    </div>
                                </div>
                                <div class="row no-border">
                                    <div class="col-xs-3">Phone</div>
                                    <div class="col-xs-9">
                                        <input type="text" name="phone" class="form-control" value="{{ $user->phone }}" />
                                    </div>
                                </div>
                                <div class="row no-border">
                                    <div class="col-xs-3">Email</div>
                                    <div class="col-xs-9">
                                        <input type="email" name="email" class="form-control" value="{{ $user->email }}"/>
                                    </div>
                                </div>
                                <div class="row no-border">
                                    <div class="col-xs-3">Influencer</div>
                                    <div class="col-xs-9">
                                        <select class="form-control" name="influencer" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                            <option value="" {{ old('influencer', $user->influencer) == '' ? 'selected' : '' }}>All</option>
                                            <option value="Y" {{ old('influencer', $user->influencer) == 'Y' ? 'selected' : ''}}>Y</option>
                                            <option value="N" {{ old('influencer', $user->influencer) == 'N' ? 'selected' : ''}}>N</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row no-border">
                                    <div class="col-xs-3">Groomer Preference</div>
                                    <div class="col-xs-9">
                                        <select class="form-control" name="groomer_prefer" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                            <option value="" {{ old('groomer_prefer', $user->groomer_prefer) == '' ? 'selected' : '' }}>All</option>
                                            <option value="M" {{ old('groomer_prefer', $user->groomer_prefer) == 'M' ? 'selected' : ''}}>Male groomers only</option>
                                            <option value="F" {{ old('groomer_prefer', $user->groomer_prefer) == 'F' ? 'selected' : ''}}>Female groomers only</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-success" type="submit">UPDATE</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Update Profile Modal end-->

            <!-- Reset Password Modal start-->
            <div class="modal fade" id="reset_password" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Reset Password</h4>
                        </div>
                        <form method="post" name="update" action="/admin/user/reset_password" class="form-group">
                            {!! csrf_field() !!}

                            <input type="hidden" name="id" value="{{$user->user_id}}" />
                            <div class="modal-body">
                                <div class="row no-border">
                                    <div class="col-xs-4">Password</div>
                                    <div class="col-xs-8">
                                        <input type="password" name="password" class="form-control" value="" />
                                    </div>
                                </div>
                                <div class="row no-border">
                                    <div class="col-xs-4">Confirm Password</div>
                                    <div class="col-xs-8">
                                        <input type="password" name="confirm_password" class="form-control" value=""/>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-success" type="submit">Reset</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Reset Password Modal end-->


            <!-- Update Address Modal Start -->
            <div class="modal" id="update_address" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Update Address</h4>
                        </div>
                        <form method="post" action="/admin/user/update_address" class="form-group">
                            {!! csrf_field() !!}
                            <div class="modal-body">
                                <input type="hidden" name="id" value="{{$user->user_id}}" />
                                <input type="hidden" name="address_id" value="" />

                                <div class="row no-border">
                                    <div class="col-xs-12">
                                        <div class="row">
                                            <div class="col-xs-4">Name</div>
                                            <div class="col-xs-8"><input type="text" name="name" class="form-control" value="" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">Street</div>
                                            <div class="col-xs-8"><input type="text" name="address1" class="form-control" value="" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">Apt. #</div>
                                            <div class="col-xs-8"><input type="text" name="address2" class="form-control" value="" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">City</div>
                                            <div class="col-xs-8"><input type="text" name="city" class="form-control" value="" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">State</div>
                                            <div class="col-xs-8"><input type="text" name="state" class="form-control" value="" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">Zip</div>
                                            <div class="col-xs-8"><input type="text" name="zip" class="form-control" value="" /></div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-warning" type="submit">Update</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Update Address Modal End -->


            <!-- Update Billing Modal Start -->
            <div class="modal" id="update_billing" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Update Billing</h4>
                        </div>
                        <form method="post" action="/admin/user/update_billing" class="form-group">
                            {!! csrf_field() !!}
                            <div class="modal-body">
                                <input type="hidden" name="id" value="{{$user->user_id}}" />
                                <input type="hidden" name="billing_id" value="" />

                                <div class="row no-border">
                                    <div class="col-xs-12">
                                        <div class="row">
                                            <div class="col-xs-4">Card Holder</div>
                                            <div class="col-xs-8"><input type="text" name="card_holder" class="form-control" value="" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">Card Type</div>
                                            <div class="col-xs-8">
                                                <select name="card_type" class="form-control">
                                                    <option value="V">Visa</option>
                                                    <option value="M">Master</option>
                                                    <option value="A">Amex</option>
                                                    <option value="D">Discover</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">Card Number</div>
                                            <div class="col-xs-8"><input type="text" name="card_number" class="form-control" value="" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">Expiration</div>
                                            <div class="col-xs-4"><input type="text" name="expire_yy" class="form-control" value="" /></div>
                                            <div class="col-xs-4"><input type="text" name="expire_mm" class="form-control" value="" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">CVV</div>
                                            <div class="col-xs-8"><input type="text" name="cvv" class="form-control" value="" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">Street</div>
                                            <div class="col-xs-8"><input type="text" name="b_address1" class="form-control" value="" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">Apt. #</div>
                                            <div class="col-xs-8"><input type="text" name="b_address2" class="form-control" value="" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">City</div>
                                            <div class="col-xs-8"><input type="text" name="b_city" class="form-control" value="" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">State</div>
                                            <div class="col-xs-8"><input type="text" name="b_state" class="form-control" value="" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-4">Zip</div>
                                            <div class="col-xs-8"><input type="text" name="b_zip" class="form-control" value="" /></div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-warning" type="submit">Update</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Update Billing Modal End -->


        </div>
    </div>
</div>
@stop