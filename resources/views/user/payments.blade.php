@extends('user.layout.default')
@section('content')
    <link href="/desktop/css/appointment.css" rel="stylesheet">
    <!-- <link href="/desktop/css/material-forms.css" rel="stylesheet"> -->

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
                                <div class="media my-card {{ session('schedule.payment.id') == $o->billing_id ? 'selected' : '' }}">
                                    <div class="media-left media-middle">

                                    </div>
                                    <div class="media-body media-middle">
                                        <div class="display-table">
                                            <div class="table-cell">
                                                <h4 class="media-heading">{{ $o->card_number }}<br>
                                                    <span class="default-card">{{ $o->default_card == 'Y' ? '(DEFAULT)' : '' }}</span></h4>
                                                <p>VALID THRU {{ $o->expire_mm }}/{{ $o->expire_yy }}</p>
                                            </div>
                                        <!-- <div class="table-cell media-middle text-right"><a href="javascript:show_card('{{ $o->billing_id }}')">
                                                    <img src="/desktop/img/ionicons_2-0-1_ios-information-outline_48_0_bd1a2a_none.png" width="22" height="22" alt="More Info" /></a>
                                            </div> -->
                                        </div>



                                        <!-- /row -->
                                    </div>
                                    <!-- /media-body -->

                                </div>
                                <!-- /my-card -->

                                <div class="col-md-5 col-sm-5 col-md-offset-1 col-sm-offset-1 col-payment-info-btn">
                                    <button type="button" class="payment-info-btn groomit-btn black-btn rounded-btn long-btn" onclick="javascript:show_card('{{ $o->billing_id }}', 'view')">
                                        View
                                    </button>
                                </div>
                                <div class="col-md-5 col-sm-5 col-md-offsetd col-payment-info-btn">
                                    <button type="button" class="payment-info-btn groomit-btn red-btn rounded-btn long-btn" onclick="javascript:show_card('{{ $o->billing_id }}', 'edit')">
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
                    <form action="" class="" role="form" method="post" id="frm_payment">
                        <input type="hidden" id="billing_id" value=""/>
                        <fieldset>

                            <!-- PAYMENT -->
                            <section id="payment">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="control-label" for="">Cardholder Full Name</label>
                                                <input class="form-control" name="card_holder" id="card_holder" type="text" maxlenght="100"
                                                       required/>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- col-6 -->
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="control-label" for="">Card Number</label>
                                                <input class="form-control credit-card" name="card_number" id="card_number" type="text"
                                                       required/>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
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
                                                        <label class="control-label" for="">Expiration Month</label>
                                                        <select class="form-control" name="expire_mm" id="expire_mm" required>
                                                            <option value="">Please Select</option>
                                                            @if (count($months) > 0)
                                                                @foreach ($months as $o)
                                                                    <option value="{{ $o['code'] }}">{{ $o['name'] }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                    <!-- /form-group -->
                                                </div>
                                                <!-- /col -->
                                                <div class="col-lg-7 col-xs-6">
                                                    <div class="form-group select-form-group">
                                                        <label class="control-label" for="">Expiration Year &nbsp; &nbsp;</label>
                                                        <select class="form-control" name="expire_yy" id="expire_yy" required>
                                                            <option value="">Please Select</option>
                                                            @if (count($years) > 0)
                                                                @foreach ($years as $o)
                                                                    <option value="{{ $o['code'] }}">{{ $o['name'] }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
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
                                                <div class="col-lg-6 col-xs-6">
                                                    <div class="form-group">
                                                        <label class="control-label" for="">CVV</label>
                                                        <input class="form-control" name="cvv" id="cvv" type="number" min="000"
                                                               max="9999" maxlength="4" required/>
                                                        <span class="form-highlight"></span> <span
                                                                class="form-bar"></span>
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
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="checkbox">
                                                    <label for="default_card">
                                                        <input type="checkbox" name="default_card" id="default_card">
                                                        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                                                        Make this credit card as default</label>
                                                </div>
                                                <!-- /checkbox -->
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <!-- /row -->

                                    <div class="row" id="div_same_address" style="display: none;">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="checkbox">
                                                    <label for="same_address">
                                                        <input type="checkbox" name="same_address" id="same_address" onclick="get_service_address()">
                                                        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                                                        Make billing address same as service address</label>
                                                </div>
                                                <!-- /checkbox -->
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <!-- /row -->

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="control-label" for="">Address 1</label>
                                                <input class="form-control" name="address1" id="address1" type="text" maxlenght="100"
                                                       required/>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- col-6 -->
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="control-label" for="">Address 2</label>
                                                <input class="form-control" name="address2" id="address2" type="text" maxlenght="100"
                                                       required/>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="control-label" for="">City</label>
                                                <input class="form-control" name="city" id="city" type="text" maxlenght="100"
                                                       required/>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- col-6 -->
                                        <div class="col-sm-3">
                                            <div class="form-group select-form-group">
                                                <label class="control-label" for="">State</label>
                                                <select class="form-control" name="state" id="state" required>
                                                    <option value="">Please Select</option>
                                                    @if (count($states) > 0)
                                                        @foreach ($states as $o)
                                                            <option value="{{ $o->code }}">{{ $o->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col-6 -->

                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label class="control-label" for="">Zip Code</label>
                                                <input class="form-control" name="zip" id="zip" type="text" maxlength="11"
                                                       required/>
                                                <span class="form-highlight"></span> <span
                                                        class="form-bar"></span>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col -->

                                    </div>
                                    <!-- /row -->
                                </div>
                                <!-- /container -->
                            </section>
                            <section id="confirm-payment">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-xs-12 text-center mb-4" id="delete_card" style="display:none;">
                                            <a class="red-link" href="javascript:delete_card()"><strong>DELETE CARD</strong></a>
                                        </div>
                                    </div>
                                    <!-- row -->
                                    <div class="row">
                                        <div class="col-xs-6 text-right">
                                            <button type="button" class="groomit-btn black-btn rounded-btn" data-dismiss="modal" id="btn_close" style="display:none;">
                                                CLOSE
                                            </button>
                                        </div>
                                        <div class="col-xs-6 text-left">
                                            <button type="button" class="groomit-btn red-btn rounded-btn" id="btn_edit" style="display:none;" onclick="change_mode('edit')">
                                                EDIT
                                            </button>
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <button type="button" class="groomit-btn black-btn rounded-btn" data-dismiss="modal" id="btn_cancel" style="display:none;">
                                                CANCEL
                                            </button>
                                        </div>
                                        <div class="col-xs-6 text-left">
                                            <button type="button" class="groomit-btn red-btn rounded-btn" id="btn_submit" style="display:none;" onclick="save_card()" >
                                                SUBMIT
                                            </button>

                                            <!-- Button trigger modal
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-card-alert-1">
                                            Launch alert 1 modal
                                            </button>
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-card-alert-2">
                                            Launch alert 2 modal
                                            </button>
                                            -->

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


    <!-- Alert CARD 1 -->
    <div class="modal fade modal-alert" id="modal-card-alert-1" tabindex="-1" role="dialog" aria-labelledby="">
        <div class="modal-dialog modal-dialog-alert" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h4 class="modal-title text-center" id="">VERIFY YOUR CREDIT CARD</h4>
                    <p class="text-center">We debit your card a small amount. Please enter the debited amount below.</p>
                    <!-- INIT FORM -->
                    <form action="" class="" role="form" method="post" id="">
                        <input type="hidden" id="" value=""/>
                        <fieldset>

                            <!-- PAYMENT -->
                            <section id="" class="mt-3 mb-3">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-sm-4 col-sm-offset-4">
                                            <div class="form-group mb-3 text-center">
                                                <label class="control-label" for=""><strong>Amount</strong></label>
                                                <input class="form-control" name="" id="card_holder" type="text"
                                                       required/>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <div class="col-md-12">
                                            <p class="text-center mt-3">You dont see amount? <a href="#/">Send again</a></p>
                                            <div class="alert text-center mt-4 mb-3">
                                                <strong>Alert section</strong>
                                            </div>
                                            <p class="text-center mt-2">
                                                To avoid this step you can cancel and make your billing addres same as service address.
                                            </p>

                                        </div>
                                        <!-- col-6 -->
                                    </div>
                                    
                                </div>
                                <!-- /container -->
                            </section>
                            <section class="mb-4">
                                <div class="container">
                                    <!-- row -->
                                    <div class="row">
                                        <div class="col-xs-6 text-right">
                                            <button type="button" class="groomit-btn black-btn rounded-btn" data-dismiss="modal" id="">
                                                CANCEL
                                            </button>
                                        </div>
                                        <div class="col-xs-6 text-left">
                                            <button type="button" class="groomit-btn red-btn rounded-btn" id="" >
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











    <!-- Alert CARD 2 -->
    <div class="modal fade modal-alert" id="modal-card-alert-2" tabindex="-1" role="dialog" aria-labelledby="">
        <div class="modal-dialog modal-dialog-alert" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h4 class="modal-title text-center" id="">VERIFY YOUR CREDIT CARD</h4>
                    <p class="text-center mt-0">Successfully verified, you are able to schedule now</p>
                    <!-- INIT FORM -->
                    <form action="" class="mt-3" role="form" method="post" id="">
                        <input type="hidden" id="" value=""/>
                        <fieldset>
                            <section class="mb-4 mt-3">
                                <div class="container">
                                    <!-- row -->
                                    <div class="row">
                                        <div class="col-xs-6 col-xs-offset-3 text-center mt-4">
                                            <button type="button" class="groomit-btn red-btn rounded-btn" id="" >
                                                CONTINUE
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






    <script type="text/javascript">
        var current_billing_id = null;

        function show_card(billing_id, viewmode) {
            var mode = typeof billing_id === 'undefined' ? 'add' : 'update';
            current_billing_id = billing_id;

            $('#same_address').prop('checked', false);

            if (mode === 'add') {

                $('#div_same_address').show();

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

                change_mode('add');

                $('#modal-card').modal();
            } else {

                myApp.showLoading();
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
                        myApp.hideLoading();
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

                            $('#billing_id').val(billing_id);

                            change_mode(viewmode);

                            $('#modal-card').modal();

                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                })
            }
        }

        function get_service_address() {

            if($("#same_address").prop('checked')==false) {
                $('#address1').val('');
                $('#address2').val('');
                $('#city').val('');
                $('#state').val('');
                $('#zip').val('');
            } else {
                $.ajax({
                    url: '/user/payment/get_service_address',
                    data: {
                        _token: '{!! csrf_token() !!}'
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function(res) {
                        myApp.hideLoading();
                        if ($.trim(res.msg) === '') {

                            var o = res.data;

                            $('#address1').val(o.address1);
                            $('#address2').val(o.address2);
                            $('#city').val(o.city);
                            $('#state').val(o.state);
                            $('#zip').val(o.zip);
                        } else {
                            $('#address1').val('');
                            $('#address2').val('');
                            $('#city').val('');
                            $('#state').val('');
                            $('#zip').val('');
                        }
                    }
                });
            }

        }

        function change_mode(viewmode) {
            if (viewmode === 'view') {
                $('#div_same_address').hide();
                $('#frm_payment input,select,textarea,label,div').prop('disabled', true);
                $('#frm_payment input,select,textarea,label,div').prop('aria-disabled', true);
                $('#btn_edit').show();
                $('#btn_close').show();
                $('#btn_submit').hide();
                $('#btn_cancel').hide();
                $('#delete_card').show();
                $('#frm_payment label').addClass('disabled');
            } else {
                $('#div_same_address').show();
                $('#frm_payment input,select,textarea,label,div').prop('disabled', false);
                $('#frm_payment input,select,textarea,label,div').prop('aria-disabled', false);
                $('#btn_edit').hide();
                $('#btn_close').hide();
                $('#btn_submit').show();
                $('#btn_cancel').show();
                $('#delete_card').hide();
                $('#frm_payment label').removeClass('disabled');
            }
        }

        function save_card() {
            var mode = typeof current_billing_id === 'undefined' ? 'add' : 'update';

            myApp.showLoading();
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
                    myApp.hideLoading();
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

        function delete_card() {

            var b_id = $('#billing_id').val();

            myApp.showLoading();
            $.ajax({
                url: '/user/payment/delete_card',
                data: {
                    _token: '{!! csrf_token() !!}',
                    billing_id: b_id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
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

    </script>
@stop
