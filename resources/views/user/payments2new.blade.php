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
                                  <button type="button" class="payment-info-btn groomit-btn black-btn rounded-btn long-btn" onclick="javascript:show_card('{{ $o->billing_id }}')">
                                      View
                                  </button>
                                </div>
                                <div class="col-md-5 col-sm-5 col-md-offsetd col-payment-info-btn">
                                  <button type="button" class="payment-info-btn groomit-btn red-btn rounded-btn long-btn" onclick="javascript:show_card('{{ $o->billing_id }}')">
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
                    <form action="" class="" role="form" method="post" id="paymentForm">
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

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="checkbox">
                                                    <label for="default_card">
                                                        <input type="checkbox" name="default_address" id="default_address">
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
                                        <div class="col-xs-12 text-center mb-4">
                                            <a class="red-link" href="#/"><strong>DELETE CARD</strong></a>
                                        </div>
                                    </div>
                                    <!-- row -->
                                    <div class="row">
                                        <div class="col-xs-6 text-right">
                                            <button type="button" class="groomit-btn black-btn rounded-btn" data-dismiss="modal">
                                                CLOSE
                                            </button>
                                        </div>
                                        <div class="col-xs-6 text-left">
                                            <button type="button" class="groomit-btn red-btn rounded-btn">
                                                EDIT
                                            </button>
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <button type="button" class="groomit-btn black-btn rounded-btn">
                                                CANCEL
                                            </button>
                                        </div>
                                        <div class="col-xs-6 text-left">
                                            <button type="button" class="groomit-btn red-btn rounded-btn" onclick="save_card()">
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

    <script type="text/javascript">
        var current_billing_id = null;

        function show_card(billing_id) {
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

                            $('#modal-card').modal();

                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                })
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
    </script>
@stop
