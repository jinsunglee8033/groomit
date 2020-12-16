@extends('user.layout.default')
@section('content')
    <link href="/desktop/css/appointment.css" rel="stylesheet" type="text/css">
    <link href="/desktop/css/material-forms.css" rel="stylesheet" type="text/css">

    <!-- PROGRESS -->
    <div class="container-fluid" id="progress-bar">
        <div class="row">
            <div class="col-xs-2 line-status complete"></div>
			<div class="col-xs-2 line-status complete"></div>
			<div class="col-xs-2 line-status complete"></div>
			<div class="col-xs-2 line-status complete"></div>
			<div class="col-xs-2 line-status complete"></div>
			<div class="col-xs-2 line-status"></div>
        </div>
        <!-- row -->
    </div>
    <!-- /progress-bar -->
    <div id="main">
        <!-- MY PAYMENTS -->
        <section id="my-payments">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h3><span class="title-icon"><img src="/desktop/img/payments-icon.png" width="43" height="31"
                                                          alt="Payments"></span>PAYMENTS</h3>
                    </div>
                </div>
                <!-- /row -->
                <div class="row after-title">

                    <!-- CARDS -->

                    @if (count($payments) > 0)
                        @foreach ($payments as $o)
                        <!-- CARD DEFAULT -->
                        <div class="col-lg-3 col-md-4 col-sm-6 card-wrapper">
                            <div class="media my-card {{ Schedule::getPaymentId() == $o->billing_id ? 'selected' : '' }}" onclick="select_card('{{ $o->billing_id }}')">
                                <div class="media-left media-middle">
                                    <input type="radio" name="payment_id" value="{{ $o->billing_id }}" {{ Schedule::getPaymentId() == $o->billing_id ? 'checked' : '' }}>
                                </div>
                                <div class="media-body media-middle">
                                    <div class="display-table">
                                        <div class="table-cell">
                                            <h4 class="media-heading">{{ $o->card_number }}<br>
                                                <span class="default-card">{{ $o->default_card == 'Y' ? '(DEFAULT)' : '' }}</span></h4>
                                            <p>VALID THRU {{ $o->expire_mm }}/{{ $o->expire_yy }}</p>
                                        </div>
                                        <!-- <div class="table-cell media-middle text-right"><a onclick="show_card(event, '{{ $o->billing_id }}')">
                                                <img src="/desktop/img/ionicons_2-0-1_ios-information-outline_48_0_bd1a2a_none.png" width="22" height="22" alt="More Info" /></a>
                                        </div> -->

                                    </div>
                                    <!-- /row -->

                                </div>
                                <!-- /media-body -->

                            </div>
                            <!-- /my-card -->

                            <div class="col-md-5 col-sm-5 col-md-offset-1 col-sm-offset-1 col-payment-info-btn">
                              <button type="button" class="payment-info-btn groomit-btn black-btn rounded-btn
                              long-btn" onclick="show_card(event, '{{ $o->billing_id }}', 'view')">
                                  View
                              </button>
                            </div>
                            <div class="col-md-5 col-sm-5 col-md-offsetd col-payment-info-btn">
                              <button type="button" class="payment-info-btn groomit-btn red-btn rounded-btn long-btn"
                                      onclick="show_card(event, '{{ $o->billing_id }}', 'edit')">
                                  Edit
                              </button>
                            </div>
                        </div>
                        <!-- /col-3 -->



                        @endforeach
                        <!-- ADD NEW -->
                        <div class="col-lg-3 col-md-4 col-sm-6 card-wrapper">
                            <div class="media my-card" id="add-new-card" onclick="show_card()">
                                <div class="media-body media-middle new-card-sad-middle">
                                    <div class="display-table">
                                        <div class="table-cell media-middle">
                                            <div class="media-left media-middle"><span class="glyphicon glyphicon-plus-sign"
                                                                                       aria-hidden="true"></span></div>
                                        </div>
                                        <div class="table-cell media-middle">
                                            <h4 class="media-heading text-left">ADD A NEW<br>
                                                CREDIT CARD</h4>
                                        </div>
                                    </div>
                                </div>
                                <!-- /media-body -->

                            </div>
                            <!-- /my-dog-card -->
                        </div>
                        <!-- /col-3 -->
                    @else
                        <!-- ADD NEW -->
                        <div class="col-lg-3 col-md-4 col-sm-6 card-wrapper">
                            <div class="media my-card" id="add-new-card" onclick="show_card()">
                                <div class="media-body media-middle">
                                    <div class="display-table">
                                        <div class="table-cell media-middle">
                                            <div class="media-left media-middle"><span class="glyphicon glyphicon-plus-sign"
                                                                                       aria-hidden="true"></span></div>
                                        </div>
                                        <div class="table-cell media-middle">
                                            <h4 class="media-heading text-left">ADD A NEW<br>
                                                CREDIT CARD</h4>
                                        </div>
                                    </div>
                                </div>
                                <!-- /media-body -->

                            </div>
                            <!-- /my-dog-card -->
                        </div>
                        <!-- /col-3 -->
                    @endif



                </div>
                <!-- /row -->
            </div>
            <!-- /container -->
        </section>
        <!-- /my-pets -->

        <section id="next-btn">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <a href="/user/schedule/select-address" class="groomit-btn rounded-btn black-btn big-btn space-btn"><img class="arrow-back" src="/desktop/img/arrow-back.png" width="14" height="18" alt="Back" /> BACK &nbsp;</a>
                        <button type="button" id="btn_continue" class="groomit-btn {{ Schedule::getPaymentId() ? '' : 'hidden' }} rounded-btn red-btn big-btn" onclick="show_confirm()">CONTINUE</button>
                    </div>
                </div>
                <!-- row -->
            </div>
            <!-- container -->
        </section>
    </div>
    <!-- /main -->

    <!-- MODALS -->

    <!-- EDIT CARD -->
    <div class="modal fade" id="modal-card" tabindex="-1" role="dialog" aria-labelledby="editCardLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h4 class="modal-title text-center" id="editCardLabel">PAYMENT</h4>

                    <!-- INIT FORM -->
                    <form action="" class="material-form" role="form" method="post" id="frm_payment">
                        <fieldset>

                            <!-- PAYMENT -->
                            <section id="payment">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <input class="form-control" name="card_holder" id="card_holder" type="text" maxlenght="100"
                                                       required/>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                <label class="control-label" for="">CARD HOLDER FULL NAME *</label>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- col-6 -->
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <input class="form-control credit-card" name="card_number" id="card_number" type="text"
                                                       required/>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                <label class="control-label" for="">CARD NUMBER *</label>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col-6 -->
                                    </div>

                                    <!-- /row -->
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="row">
                                                <div class="col-lg-5 col-xs-6">
                                                    <div class="form-group select-form-group">
                                                        <select class="form-control" name="expire_mm" id="expire_mm" required>
                                                            <option value="">Please Select</option>
                                                            @if (count($months) > 0)
                                                                @foreach ($months as $o)
                                                                    <option value="{{ $o['code'] }}">{{ $o['name'] }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        <label class="control-label" for="">EXPIRED MONTH *</label>
                                                    </div>
                                                    <!-- /form-group -->
                                                </div>
                                                <!-- /col -->
                                                <div class="col-lg-7 col-xs-6">
                                                    <div class="form-group select-form-group">
                                                        <select class="form-control" name="expire_yy" id="expire_yy" required>
                                                            <option value="">Please Select</option>
                                                            @if (count($years) > 0)
                                                                @foreach ($years as $o)
                                                                    <option value="{{ $o['code'] }}">{{ $o['name'] }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        <label class="control-label" for="">EXPIRED YEAR *</label>
                                                    </div>
                                                    <!-- /form-group -->
                                                </div>
                                                <!-- /col -->
                                            </div>
                                            <!-- /row -->
                                        </div>
                                        <!-- col-6 -->
                                        <div class="col-sm-6">
                                            <div class="row">
                                                <div class="col-lg-5 col-xs-6">
                                                    <div class="form-group">
                                                        <input class="form-control" name="cvv" id="cvv" type="number" min="000"
                                                               max="9999" maxlength="4" required/>
                                                        <span class="form-highlight"></span> <span
                                                                class="form-bar"></span>
                                                        <label class="control-label" for="">CVV *</label>
                                                    </div>
                                                    <!-- /form-group -->
                                                </div>
                                                <!-- /col -->
                                                <div class="col-lg-5 col-xs-6">
                                                    <div class="form-group">
                                                        <input class="form-control" name="zip" id="zip" type="text" maxlength="11"
                                                               required/>
                                                        <span class="form-highlight"></span> <span
                                                                class="form-bar"></span>
                                                        <label class="control-label" for="">ZIP CODE *</label>
                                                    </div>
                                                    <!-- /form-group -->
                                                </div>
                                                <!-- /col -->
                                            </div>
                                            <!-- /row -->
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="form-group">
                                            <div class="checkbox">
                                                    <label for="billing_address">
                                                        <input type="checkbox" name="billing_address" id="billing_address" onchange="setup_billing_address()">
                                                        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                                                        Make billing address same as services address.</label>
                                                </div>
                                                <!-- /checkbox -->

                                            </div>
                                            </div>
                                            </div>

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <input class="form-control" name="address1" id="address1" type="text" maxlenght="100"
                                                       required/>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                <label class="control-label" for="">ADDRESS *</label>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- col-6 -->
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <input class="form-control" name="address2" id="address2" type="text" required/>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                <label class="control-label" for="">APT/SUITE</label>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <input class="form-control" name="city" id="city" type="text" maxlenght="100"
                                                       required/>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                <label class="control-label" for="">CITY *</label>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- col-6 -->
                                        <div class="col-sm-6">
                                            <div class="form-group select-form-group">
                                                <select class="form-control" name="state" id="state" required>
                                                    <option value="">Please Select</option>
                                                    @if (count($states) > 0)
                                                        @foreach ($states as $o)
                                                            <option value="{{ $o->code }}">{{ $o->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <label class="control-label" for="">STATE *</label>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <!-- /row -->
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="checkbox">
                                                    <label for="default_card">
                                                        <input type="checkbox" name="default_card" id="default_card">
                                                        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                                                        Make this default credit card. </label>
                                                </div>
                                                <!-- /checkbox -->
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <!-- /row -->
                                </div>
                                <!-- /container -->
                            </section>
                            <section id="confirm-payment">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-xs-12 text-center">
                                            <button type="button" class="groomit-btn black-btn rounded-btn long-btn space-btn" onclick="change_mode('view')" id="btn_cancel" style="display:none;">
                                                Cancel
                                            </button>
                                             <button type="button" class="groomit-btn black-btn rounded-btn long-btn space-btn" id="btn_close" data-dismiss="modal" aria-label="Close" style="display:none">CLOSE
                                            </button>
                                            <button type="button" class="groomit-btn red-btn rounded-btn long-btn" onclick="change_mode('edit')" id="btn_edit" style="display:none;">
                                                EDIT
                                            </button>
                                            <button type="button" class="groomit-btn red-btn rounded-btn long-btn" onclick="save_card()" id="btn_submit" style="display:none;">
                                                SUBMIT
                                            </button>
                                        </div>
                                    </div>
                                    <!-- row -->
                                </div>
                                <!-- /container -->
                            </section>
                        </fieldset>
                    </form>

                    <!-- /FORM -->

                </div>
                <!-- /modal-body -->

            </div>
            <!-- /modal-content -->

        </div>
    </div>
    <!-- /editCardModal -->

    <!-- CONFIRM ORDER -->
    <div class="modal fade" id="modal-confirm" tabindex="-1" role="dialog" aria-labelledby="confirmOrderLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body" id="confirm-order-body">
                    <h4 class="modal-title text-center" id="confirmOrderLabel">CONFIRM ORDER</h4>
                    <div class="row">
                        <div class="col-md-10 col-md-offset-1">
                            <!-- INIT TABLE -->
                            <table class="table" id="orderTable">
                                <tbody>
                                    @if (count($pets) > 0)
                                        @foreach ($pets as $o)
                                        <tr>
                                            <td class="name">{{ $o->info->package->prod_name }}</td>
                                            <td class="value1"></td>
                                            <td class="value2">${{ number_format($o->info->sub_total, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    @endif
                                    <tr id="tr_promo_code">
                                        <td class="name">PROMO CODE</td>
                                        <td class="value1"></td>
                                        <td class="value2">
                                            <div class="input-group">
                                                <input type="text" name="promo_code" id="promo_code" maxlength="20"
                                                       class="form-control" value="{{ $promo_code }}">
                                                <span class="input-group-btn">
                                                    <button class="btn red-btn groomit-btn" type="button" onclick="apply_code()"><span
                                                                class="visible-xs glyphicon glyphicon-ok"></span><span
                                                                class="hidden-xs">Apply</span></button>
                                                </span></div>

                                            <!-- /input-group --></td>
                                    </tr>
                                    <tr style="display:{{ $promo_amt > 0 ? '' : 'none' }}" id="tr_promo_amt">
                                        <td class="name">Promo Values</td>
                                        <td class="value1"></td>
                                        <td class="value2">$<span id="promo_amt">{{ number_format($promo_amt, 2) }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="name">SAMEDAY BOOKING</td>
                                        <td class="value1"></td>
                                        <td class="value2">$<span id="sameday_booking">{{ number_format($sameday_booking, 2) }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="name">FAVORITE GROOMER FEE</td>
                                        <td class="value1"></td>
                                        <td class="value2">$<span id="sameday_booking">{{ number_format($fav_fee, 2) }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="name" >SAFETY INSURANCE</td>
                                        <td class="value1"></td>
                                        <td class="value2">$<span id="safety_insurance">{{ number_format($safety_insurance, 2) }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="name">SALES TAX</td>
                                        <td class="value1"></td>
                                        <td class="value2">$<span id="tax">{{ number_format($tax, 2) }}</span></td>
                                    </tr>
                                    <tr style="display:{{ $promo_amt > 0 ? '' : 'none' }}" id="tr_promo_amt">
                                        <td class="name">DISCOUNT APPLIED</td>
                                        <td class="value1"></td>
                                        <td class="value2" style="color: red;">$<span id="discount_promo_amt">{{
                                        number_format($discount_applied, 2) }}</span></td>
                                    </tr>
                                    <tr style="display:{{ $available_credit > 0 ? '' : 'none' }}" id="tr_available_credit">
                                        <td class="name">
                                            <div class="checkbox">
                                                <label for="use_credit">
                                                    <input type="checkbox" name="use_credit" id="use_credit" onchange="toggle_credit()" {{ $use_credit == 'Y' ? 'checked' : '' }}>
                                                    <span class="cr"><i
                                                                class="cr-icon glyphicon glyphicon-ok"></i></span>
                                                    AVAILABLE CREDITS </label>
                                            </div>

                                            <!-- /checkbox --></td>
                                        <td class="value1">$<span id="available_credit">{{ number_format($available_credit, 2) }}</span></td>
                                        <td class="value2">$<span id="credit_amt">{{ number_format($credit_amt, 2) }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="name">TOTAL</td>
                                        <td class="value1"></td>
                                        <td class="value2">$<span id="total">{{ number_format($total, 2) }}</span></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="height:10px; border-right: 0px; border-left: 0px;"></td>
                                    </tr>
                                    <tr id="tr_new_credit" style="display:{{ $new_credit > 0 ? '' : 'none' }}">
                                        <td class="name">NEW CREDIT</td>
                                        <td class="value1"></td>
                                        <td class="value2" style="color: green;">$<span id="new_credit">{{ number_format($new_credit, 2) }}</span></td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- /TABLE -->

                        </div>
                        <!-- /col -->
                    </div>
                    <!-- /row -->
                    <div class="row">
                        <div class="col-xs-12 text-center">
                            <div class="form-group">
                                <button data-content="fetching-groomers" id="btn_confirm" onclick="confirm_order()"
                                        class="btn red-btn rounded-btn groomit-btn long-btn"
                                        type="button">CONFIRM
                                </button>
                            </div>
                            <!-- /form-group -->
                        </div>
                    </div>
                    <!-- /row -->
                </div>
                <!-- /modal-body -->

                <div class="modal-body hidden" id="fetching-groomers-body">
                    <h4 class="modal-title text-center" id="confirmOrderLabel">FETCHING GROOMERS</h4>
                    <div class="row">
                        <div class="col-xs-12 text-center">
                            <p><img src="/desktop/img/yey.png" width="126" height="184" alt="Fetching Groomers"></p>
                            <br>
                            <p>We're locating available groomers.<br>Shortly we confirm your start time and groomer by email/app.</p>
                        </div>
                        <!-- /col -->
                    </div>
                    <!-- /row -->
                    <div class="row">
                        <div class="col-xs-12 text-center">
                            <div class="form-group">
                                <a href="/user/home" class="btn red-btn rounded-btn groomit-btn long-btn"
                                        type="button">GOT IT
                                </a>
                            </div>
                            <!-- /form-group -->
                        </div>
                    </div>
                    <!-- /row -->
                </div>
                <!-- /modal-body -->

            </div>
            <!-- /modal-content -->

        </div>
    </div>
    <!-- /confirmOrderModal -->

    <script type="text/javascript">
        var current_billing_id = null;

        function show_card(e, billing_id, viewmode) {
            if (!e) var e = window.event;
            e.cancelBubble = true;
            if (e.stopPropagation) e.stopPropagation();

            var mode = typeof billing_id === 'undefined' ? 'add' : 'update';
            current_billing_id = billing_id;

            if (mode === 'add') {

                $('#card_holder').val('');
                $('#card_number').val('');
                $('#expire_mm').val('');
                $('#expire_yy').val('');
                $('#cvv').val('');
                $('#address1').val('');
                $('#address2').val('');
                $('#city').val('');
                $('#state').val('');
                $('#zip').val('');
                $('#default_card').prop('checked', false);

                change_mode('edit');

                $('#modal-card').modal();
            } else {
                $.ajax({
                    url: '/user/payment/load',
                    data: {
                        _token: '{!! csrf_token() !!}',
                        billing_id: billing_id
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function(res) {
                        if ($.trim(res.msg) === '') {

                            var o = res.data;

                            $('#card_holder').val(o.card_holder);
                            $('#card_number').val(o.card_number);
                            $('#expire_mm').val(o.expire_mm);
                            $('#expire_yy').val(o.expire_yy);
                            $('#cvv').val(o.cvv);
                            $('#address1').val(o.address1);
                            $('#address2').val(o.address2);
                            $('#city').val(o.city);
                            $('#state').val(o.state);
                            $('#zip').val(o.zip);
                            $('#default_card').prop('checked', o.default_card === 'Y');

                            if (typeof viewmode === 'undefined') {
                                viewmode = 'view';
                            }

                            change_mode(viewmode);

                            $('#modal-card').modal();

                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                })
            }
        }

        function change_mode(mode) {
            if (mode === 'view') {
                $('#frm_payment input,select,textarea,label,div').prop('disabled', true);
                $('#frm_payment input,select,textarea,label,div').prop('aria-disabled', true);
                $('#btn_edit').show();
                $('#btn_close').show();
                $('#btn_submit').hide();
                $('#btn_cancel').hide();

                $('#frm_payment label').addClass('disabled');
            } else {
                $('#frm_payment input,select,textarea,label,div').prop('disabled', false);
                $('#frm_payment input,select,textarea,label,div').prop('aria-disabled', false);
                $('#btn_edit').hide();
                $('#btn_close').show();
                $('#btn_submit').show();
                $('#btn_cancel').hide();

                $('#frm_payment label').removeClass('disabled');
            }
        }

        function save_card() {
            var mode = typeof current_billing_id === 'undefined' ? 'add' : 'update';
            $.ajax({
                url: '/user/payment/' + mode,
                data: {
                    _token: '{!! csrf_token() !!}',
                    billing_id: current_billing_id,
                    card_holder: $('#card_holder').val(),
                    card_number: $('#card_number').inputmask('unmaskedvalue'),
                    expire_mm: $('#expire_mm').val(),
                    expire_yy: $('#expire_yy').val(),
                    cvv: $('#cvv').val(),
                    address1: $('#address1').val(),
                    address2: $('#address2').val(),
                    city: $('#city').val(),
                    state: $('#state').val(),
                    zip: $('#zip').val(),
                    default_card: $('#default_card').is(':checked') ? 'Y' : 'N'
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        myApp.showSuccess('Thank you, your request has been processed successfully!', function() {
                            window.location.reload();
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function select_card(billing_id) {
            $.ajax({
                url: '/user/schedule/select-payment/post',
                data: {
                    _token: '{!! csrf_token() !!}',
                    billing_id: billing_id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        //window.location.reload();
                        $('#btn_continue').removeClass('hidden');
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function show_confirm() {

            $('#modal-confirm').modal();
        }

        function toggle_credit() {
            $.ajax({
                url: '/user/schedule/select-payment/use-credit',
                data: {
                    _token: '{!! csrf_token() !!}',
                    use_credit: $('#use_credit').is(':checked') ? 'Y' : 'N'
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        update_total(res);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function update_total(res) {
            $('#promo_amt').text(parseFloat(res.promo_amt).toFixed(2));
            $('#safety_insurance').text(parseFloat(res.safety_insurance).toFixed(2));
            $('#sameday_booking').text(parseFloat(res.sameday_booking).toFixed(2));
            $('#fav_fee').text(parseFloat(res.fav_fee).toFixed(2));
            $('#available_credit').text(parseFloat(res.available_credit).toFixed(2));
            $('#credit_amt').text(parseFloat(res.credit_amt).toFixed(2));
            $('#discount_promo_amt').text(parseFloat(res.discount_applied).toFixed(2));
            $('#tax').text(parseFloat(res.tax).toFixed(2));
            $('#total').text(parseFloat(res.total).toFixed(2));


            if (res.promo_amt > 0) {
                $('#tr_promo_amt').show();
            } else {
                $('#tr_promo_amt').hide();
            }

            if (res.new_credit > 0) {
                $('#new_credit').text(parseFloat(res.new_credit).toFixed(2));
                $('#tr_new_credit').show();
            } else {
                $('#tr_new_credit').hide();
            }

            if (res.total !== 0 || res.promo_amt !== 0) {
                $('#tr_promo_code').show();
            } else {
                $('#tr_promo_code').hide();
            }
        }

        function apply_code() {
            $.ajax({
                url: '/user/schedule/select-payment/apply-code',
                data: {
                    _token: '{!! csrf_token() !!}',
                    promo_code: $('#promo_code').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        update_total(res);
                    } else {
                        $('#promo_code').val('');
                        myApp.showError(res.msg);
                    }
                }
            })
        }

        function confirm_order() {
            $('#btn_confirm').hide();
            myApp.showLoading();
            $.ajax({
                url: '/user/schedule/process',
                data: {
                    _token: '{!! csrf_token() !!}'
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {

                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {

                        // $('#confirm-order-body').animateCss('flipOutY', function () {
                        //
                        //     // $('#fetching-groomers-body').removeClass('hidden');
                        //     // $('#fetching-groomers-body').animateCss('flipInY');
                        //
                        //     setTimeout(function () {
                        //         $('.modal').css('overfloy-y', 'auto');
                        //     });
                        // });

                        $('#confirm-order-body').addClass('hidden');
                        window.location.href = '/user/schedule/thank-you';

                    } else {
                        $('#btn_confirm').show();
                        myApp.showError(res.msg);
                    }

                    //Trigger TGM Conversion event
                    dataLayer.push({
                        event: "bookingConversion"
                    })

                    //Trigger Mixpanel event (Analytics)
                    //mixpanel.track("Booking");

                    //Trigger Segment event (Analytics)
                    analytics.track("Booking");
                    
                }
            });
        }

        var old_address1 = '';
        var old_address2 = '';
        var old_city = '';
        var old_state = '';
        var old_zip = '';
        function setup_billing_address() {
            var checked = $('#billing_address').is(':checked');
            if (checked) {
                old_address1 = $('#address1').val();
                old_address2 = $('#address2').val();
                old_city = $('#city').val();
                old_state = $('#state').val();
                old_zip = $('#zip').val();

                $('#address1').val('{{ $address->address1 }}');
                $('#address2').val('{{ $address->address2 }}');
                $('#city').val('{{ $address->city }}');
                $('#state').val('{{ $address->state }}');
                $('#zip').val('{{ $address->zip }}');
            } else {
                $('#address1').val(old_address1);
                $('#address2').val(old_address2);
                $('#city').val(old_city);
                $('#state').val(old_state);
                $('#zip').val(old_zip);
            }

        }

    </script>

@stop
