@extends('includes.admin_default')
@section('contents')

    <script type="text/javascript">

        window.onload = function () {
            $('#affiliate_photo').change(function () {
                previewImage(this, 'img_affiliate_photo');
            });
        };

        function update_redeem_status(id) {
            myApp.showLoading();
            $.ajax({
                url: '/admin/affiliate/change-redeem-status',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    status: $("#redeem_status" + id).val()
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
    </script>
    <h3 style="margin: 20px 30px;">Affiliate Detail <a href="/admin/affiliates" class="btn btn-default">Back</a>
        <div class="btn-right btn btn-info" data-toggle="modal" data-target="#update">Update Account Info</div>

        <form class="btn-right" action="/affiliate/login-as" method="post">
            <input type="hidden" name="aff_id" value="{{ $affiliate->aff_id }}"/>
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <button class="btn btn-success">Login As</button>
        </form>

    </h3>
    <hr>
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


            <div class="col">
                <div class="row">
                    <div class="col-xs-2">Business Name</div>
                    <div class="col-xs-10">{{ $affiliate->business_name }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-2">Name</div>
                    <div class="col-xs-10">{{ $affiliate->full_name() }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-2">Logo</div>
                @if ($affiliate->affiliate_photo)
                    <div class="col text-center">
                        <img src="data:image/png;base64,{{ $affiliate->affiliate_photo }}"/>
                    </div>
                @else
                    <div class="col-xs-10">
                        No Image
                    </div>
                @endif
                </div>

                <div class="row">
                    <div class="col-xs-2">Email</div>
                    <div class="col-xs-10">{{ $affiliate->email }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-2">Phone</div>
                    <div class="col-xs-10">{{ $affiliate->phone }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-2">Address</div>
                    <div class="col-xs-10">{{ $affiliate->full_address() }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-2">Bank Name</div>
                    <div class="col-xs-10">{{ $affiliate->bank_name }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-2">Account Number</div>
                    <div class="col-xs-10">{{ $affiliate->bank_account_number }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-2">Routing Number</div>
                    <div class="col-xs-10">{{ $affiliate->routing_number }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-2">Earnings</div>
                    <div class="col-xs-10">${{ $earnings }}</div>
                </div>

                <div class="row">
                    <div class="col-xs-2">Redeemed Amount</div>
                    <div class="col-xs-10">${{ $redeemed_amt }}</div>
                </div>


                <div class="row">
                    <div class="col-xs-2">Affiliate Codes</div>
                    <div class="col-xs-10">
                        @foreach($codes as $a)
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="col-xs-2 alert-info">Code</div>
                                    <div class="col-xs-3 alert-info">Promo Amount</div>
                                    <div class="col-xs-3 alert-info">Earned Amount</div>
                                    <div class="col-xs-3 alert-info">Created Date</div>
                                    <div class="col-xs-1 alert-info">Status</div>
                                    <div class="col-xs-2">{{ $a->aff_code }}</div>
                                    <div class="col-xs-3">${{ $a->earning }}</div>
                                    <div class="col-xs-3">${{ $a->earned_amt }}</div>
                                    <div class="col-xs-3">{{ $a->assigned_date }}</div>
                                    <div class="col-xs-1">{!! $a->status_name() !!}</div>
                                </div>
                                {{--<div class="col-xs-3">--}}
                                    {{--<div class="btn-right btn btn-default update_address" data-id="{{ $indexKey }}" data-toggle="modal" data-target="#update_address">Update</div>--}}
                                {{--</div>--}}
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-2">Redeem History</div>
                    <div class="col-xs-10">
                        @foreach($redeems as $b)
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="col-xs-2 alert-warning">Redeemed ID</div>
                                    <div class="col-xs-2 alert-warning">Amount</div>
                                    <div class="col-xs-2 alert-warning">Type</div>
                                    <div class="col-xs-3 alert-warning">Req.Date</div>
                                    <div class="col-xs-3 alert-warning">Status</div>
                                    <div class="col-xs-2">{{ $b->aff_redeemed_id }}</div>
                                    <div class="col-xs-2">${{ $b->amount }}</div>
                                    <div class="col-xs-2">{{ $b->type_name() }}</div>
                                    <div class="col-xs-3">{{ $b->cdate }}</div>
                                    <div class="col-xs-3">
                                        <select id="redeem_status{{ $b->aff_redeemed_id }}">
                                            <option value="N" @if($b->status == 'N') selected @endif>New</option>
                                            <option value="S" @if($b->status == 'S') selected @endif>Processing</option>
                                            <option value="P" @if($b->status == 'P') selected @endif>Paid</option>
                                            <option value="C" @if($b->status == 'C') selected @endif>Canceled</option>
                                        </select>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="update_redeem_status({{ $b->aff_redeemed_id }})">Update</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>


            <!-- Update Account Modal start-->
            <div class="modal fade" id="update" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Update Account Information</h4>
                        </div>
                        <form method="post" name="update" action="/admin/affiliate/update" class="form-group" enctype="multipart/form-data">
                            {!! csrf_field() !!}

                            <input type="hidden" name="aff_id" value="{{$affiliate->aff_id}}" />
                            <div class="modal-body">
                                <div class="row no-border">
                                    <div class="col-xs-4">Business Name</div>
                                    <div class="col-xs-8">
                                        <input type="text" name="business_name" class="form-control" value="{{ $affiliate->business_name }}"/>
                                    </div>
                                </div>
                                <div class="row no-border">
                                    <div class="col-xs-4">Name</div>
                                    <div class="col-xs-4">
                                        <input type="text" name="first_name" class="form-control" value="{{ $affiliate->first_name }}" />
                                    </div>
                                    <div class="col-xs-4">
                                        <input type="text" name="last_name" class="form-control" value="{{ $affiliate->last_name }}" />
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-3">Affiliate Photo</div>
                                    <div class="col-xs-8">
                                        <div class="text-center">
                                            @if ($affiliate->affiliate_photo)
                                                <img id="img_affiliate_photo"
                                                     src="data:image/png;base64,{{ $affiliate->affiliate_photo }}"/>
                                            @else
                                                <img id="img_affiliate_photo" src="/images/upload-img.png"/>
                                            @endif
                                            <input type="file" id="affiliate_photo" name="affiliate_photo"
                                                   value="{{ old('affiliate_photo') }}" style="visibility:hidden"/>
                                        </div>
                                    </div>
                                    <div class="col-xs-8 col-xs-offset-3">
                                        <div class="text-center">
                                            <a onclick="$('[name=affiliate_photo]').click()"
                                               class="btn btn-success upload">Upload Images</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-4">Phone</div>
                                    <div class="col-xs-8">
                                        <input type="text" name="phone" class="form-control" value="{{ $affiliate->phone }}"/>
                                    </div>
                                </div>
                                <div class="row no-border">
                                    <div class="col-xs-4">Address</div>
                                    <div class="col-xs-8">
                                        <input type="text" name="address" class="form-control" value="{{ $affiliate->address }}" />
                                    </div>
                                </div>
                                <div class="row no-border">
                                    <div class="col-xs-4">Suite #</div>
                                    <div class="col-xs-8">
                                        <input type="text" name="address2" class="form-control" value="{{ $affiliate->address2 }}" />
                                    </div>
                                </div>
                                <div class="row no-border">
                                    <div class="col-xs-4">City</div>
                                    <div class="col-xs-8">
                                        <input type="text" name="city" class="form-control" value="{{ $affiliate->city }}" />
                                    </div>
                                </div>
                                <div class="row no-border">
                                    <div class="col-xs-4">State</div>
                                    <div class="col-xs-3">
                                        <input type="text" name="state" class="form-control" value="{{ $affiliate->state }}" maxlength="2" />
                                    </div>
                                    <div class="col-xs-1">Zip</div>
                                    <div class="col-xs-4">
                                        <input type="text" name="zip" class="form-control" value="{{ $affiliate->zip }}" maxlength="5"/>
                                    </div>
                                </div>
                                <div class="row no-border">
                                    <div class="col-xs-4">Bank Name</div>
                                    <div class="col-xs-8">
                                        <input type="text" name="bank_name" class="form-control" value="{{ $affiliate->bank_name }}"/>
                                    </div>
                                </div>
                                <div class="row no-border">
                                    <div class="col-xs-4">Bank Acct. Number</div>
                                    <div class="col-xs-8">
                                        <input type="text" name="bank_account_number" class="form-control" value="{{ $affiliate->bank_account_number }}"/>
                                    </div>
                                </div>
                                <div class="row no-border">
                                    <div class="col-xs-4">Routing Number</div>
                                    <div class="col-xs-8">
                                        <input type="text" name="routing_number" class="form-control" value="{{ $affiliate->routing_number }}"/>
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-4">Password</div>
                                    <div class="col-xs-8">
                                        <input type="password" name="password" class="form-control" value=""/>
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
                                <button class="btn btn-success" type="submit">UPDATE</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Update Profile Modal end-->

        </div>
    </div>
@stop