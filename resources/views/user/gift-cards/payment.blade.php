@extends('user.layout.default')
@section('content')
<link href="/desktop/css/login.css?V=4.8.1" rel="stylesheet">

<!-- TOP BANNER -->
<div class="aos vouchers--gift-cards" id="vouchers">
  <div class="display-table" id="top-banner">
    <div class="table-cell text-center" id="banner-title">
      <h2>Get your Groomit Gift Card</h2>
    </div>
    <!-- /banner-title --> 
  </div>
  <!-- /top-banner -->
  
  <form action="" class="material-form" role="form" method="post" id="frm_payment">
    <fieldset>
      
      <!-- PAYMENT -->
      <section id="voucher-payment">
        <div class="container">
          <div class="row text-center">
            <div class="col-md-12"> <img class="voucher-card img-responsive" src="{{ $voucher->image }}" width="586"
                                         height="315" alt="Gift Card" />
              <h2>Complete to Checkout</h2>
            </div>
          </div>
          <!-- /row -->
          <div class="row">
            <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
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
                      <label for="is_gift">
                        <input type="checkbox" name="is_gift" id="is_gift" onclick="check_is_gift()">
                        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span> Is this a gift? (check to enter recipient information) </label>
                    </div>
                    <!-- /checkbox --> 
                  </div>
                  <!-- /form-group --> 
                </div>
                <!-- /col-6 --> 
              </div>
              <!-- /row --> 
              
              <!-- RECIPIENT DATA -->
              <div id="recipient-info" style="display:none;">
                <h3>Who is this Gift Card for?</h3>
                <br />
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group select-form-group">
                      <select class="form-control" name="recipient_location" id="recipient_location">
                        <option value="">Please Select</option>
                        <option value="NY">NY</option>
                        <option value="NJ">NJ</option>
                        <option value="CA">CA</option>
                        <option value="CT">CT</option>
                        <option value="PA">PA</option>
                      </select>
                      <label class="control-label" for="">RECIPIENT LOCATION *</label>
                    </div>
                    <!-- /form-group --> 
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group">
                      <input class="form-control" name="sender" id="sender" type="text" maxlenght="100" disabled="disabled" />
                      <span class="form-highlight"></span> <span class="form-bar"></span>
                      <label class="control-label" for="">FROM </label>
                    </div>
                    <!-- /form-group --> 
                  </div>
                  <!-- col-6 -->
                  <div class="col-sm-6">
                    <div class="form-group">
                      <input class="form-control" name="recipient_name" id="recipient_name" type="text" maxlenght="100" disabled="disabled"
                                                       />
                      <span class="form-highlight"></span> <span class="form-bar"></span>
                      <label class="control-label" for="">TO *</label>
                    </div>
                    <!-- /form-group --> 
                  </div>
                </div>
                <div class="row">
                  <!-- col-6 -->
                  <div class="col-sm-6">
                    <div class="form-group">
                      <input class="form-control credit-card" name="recipient_email" id="recipient_email" type="email" disabled="disabled"
                                                       />
                      <span class="form-highlight"></span> <span class="form-bar"></span>
                      <label class="control-label" for="">RECIPIENT EMAIL *</label>
                    </div>
                    <!-- /form-group --> 
                  </div>
                  <!-- /col-6 -->
                  <!-- col-6 -->
                  <div class="col-sm-6">
                    <div class="form-group">
                      <input class="form-control credit-card" name="recipient_email_confirm" id="recipient_email_confirm"
                             type="email" disabled="disabled"
                      />
                      <span class="form-highlight"></span> <span class="form-bar"></span>
                      <label class="control-label" for="">RECIPIENT EMAIL CONFIRM *</label>
                    </div>
                    <!-- /form-group -->
                  </div>
                  <!-- /col-6 -->
                </div>
                <!-- /row -->
                <div class="row">
                  <div class="col-xs-12">
                    <div class="form-group">
                      <textarea class="form-control" name="voucher_message" id="voucher_message" maxlength="250" rows="4" disabled="disabled"
                                                          placeholder="This is a gift"></textarea>
                      <span class="form-highlight"></span> <span class="form-bar"></span>
                      <label class="control-label" for="">MESSAGE</label>
                    </div>
                    <!-- /form-group --> 
                  </div>
                </div>
                <!-- /row --> 
              </div>
              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group">
                    <div class="checkbox">
                      <label for="default_card">
                        <input type="checkbox" name="default_card" id="default_card">
                        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span> Make this default credit card. </label>
                    </div>
                    <!-- /checkbox --> 
                  </div>
                  <!-- /form-group -->
                  <div class="form-group last" style="display: none;">
                    <div class="checkbox">
                      <label for="billing_address">
                        <input type="checkbox" name="billing_address" id="billing_address" onchange="setup_billing_address()">
                        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span> Make billing address same as services address.</label>
                    </div>
                    <!-- /checkbox --> 
                  </div>
                  <!-- /form-group -->
                  <div class="form-group last">
                    <div class="checkbox">
                      <label for="terms">
                        <input type="checkbox" name="terms" id="terms">
                        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span> I accept the <a href="" data-toggle="modal" data-target="#terms-modal"><strong>terms & conditions.</strong></a></label>
                    </div>
                    <!-- /checkbox --> 
                  </div>
                  <!-- /form-group --> 
                </div>
                <!-- /col-6 --> 
              </div>
              <!-- /row --> 
              
            </div>
            <!-- offset-col --> 
          </div>
          <!-- /row --> 
        </div>
        <!-- /container --> 
      </section>
      <section id="confirm-payment">
        <div class="container">
          <div class="row">
            <div class="col-xs-12 text-center"> <button type="button" class="groomit-btn black-btn rounded-btn long-btn space-btn" onclick="change_mode('view')" id="btn_cancel"> CANCEL </button> <button type="button" class="groomit-btn red-btn rounded-btn long-btn" onclick="purchase()" id="btn_submit"> SUBMIT </button> </div>
          </div>
          <!-- row --> 
        </div>
        <!-- /container --> 
      </section>
    </fieldset>
  </form>
  
  <!-- /FORM -->
  
  <section class="text-center" id="voucher-how">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h2>How it works</h2>
        </div>
      </div>
      <!-- /row -->
      <div class="row hidden-xs">
        <div class="col-sm-3" data-aos="flip-left">
          <div class="icon-how"> 
            <img src="/desktop/img/voucher-select.png" width="53" height="80" alt="Select the preferred E-Gift Card amount" />
          </div>
          <p class="how-desc">Select the preferred<br/> E-Gift Card amount</p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3" data-aos="flip-right" data-aos-delay="500">
          <div class="icon-how">
            <img  src="/desktop/img/voucher-email.png" width="69" height="69" alt="E-Gift Card will be emailed instantly to your recipient" />
          </div>
          <p class="how-desc">E-Gift Card will be emailed instantly to your recipient</p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3" data-aos="flip-left" data-aos-delay="1000">
          <div class="icon-how">
            <img src="/desktop/img/voucher-service.png" width="125" height="59" alt="Redeem within Groomit App / Website at checkout" />
          </div>
          <p class="how-desc">Redeem within Groomit App / Website at checkout</p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3" data-aos="flip-right" data-aos-delay="1500" >
          <div class="icon-how">
            <img src="/desktop/img/voucher-credit.png" width="41" height="56" alt="Groomit Credits will be applied to your account." />
          </div>
          <p class="how-desc">Groomit Credits will be applied to your account</p>
        </div>
        <!-- /col-6 -->
      </div>
      <!-- /row -->
      <div class="row visible-xs">
        <div class="col-sm-3" data-aos="flip-left">
          <div class="icon-how">
            <img src="/desktop/img/voucher-select.png" width="53" height="80" alt="Select the preferred E-Gift Card amount" />
          </div>
          <p class="how-desc">Select the preferred<br/> E-Gift Card amount</p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3" data-aos="flip-right">
          <div class="icon-how">
            <img  src="/desktop/img/voucher-email.png" width="69" height="69" alt="E-Gift Card will be emailed instantly to your recipient" />
          </div>
          <p class="how-desc">E-Gift Card will be emailed instantly to your recipient</p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3" data-aos="flip-left">
          <div class="icon-how">
            <img src="/desktop/img/voucher-service.png" width="125" height="59" alt="Redeem within Groomit App / Website at checkout" />
          </div>
          <p class="how-desc">Redeem within Groomit App / Website at checkout</p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3" data-aos="flip-right">
          <div class="icon-how">
            <img src="/desktop/img/voucher-credit.png" width="41" height="56" alt="Groomit Credits will be applied to your account." />
          </div>
          <p class="how-desc">Groomit Credits will be applied to your account</p>
        </div>
        <!-- /col-6 --> 
      </div>
      <!-- /row -->
    </div>
    <!-- /container -->
  </section>
  <!-- /voucher-how -->
  
</div>
<!-- /vouchers --> 

<!-- MODALS -->
<div class="modal fade" id="terms-modal" tabindex="-1" role="dialog" aria-labelledby="termsModalLabel">
  <div class="modal-dialog" role="document"> 
    
    <!-- REGISTER FORM -->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      
      <!-- TERMS -->
      <div class="modal-body" id="terms">
        <h4 class="modal-title text-center" id="termsLabel">Groomit Gift Card Terms and Conditions</h4>
        <div class="row">
          <div class="col-sm-10 col-sm-offset-1">
          <br />
            <p><strong>Revised:</strong> October 1, 2018.</p>
            <p><strong>Purchase.</strong> The <strong>Groomit Gift Card</strong> program allows you to purchase and
              send a virtual Groomit Gift Card ("Voucher") via email, for redemption and use on the <a
                      href="https://www.groomit.me" target="_blank">www.groomit.me</a> platform in the United States.
              Gift Cards can be purchased via the Groomit Web Store, located at <a href="https://www.groomit.me" target="_blank">www.groomit.me</a> and/or other online merchants, but are only redeemable for pet care services on Groomit.me and not for goods in the Groomit Store or anywhere else.  Once purchased, Gift Cards are non-returnable and non-refundable, except as required by law. </p>
            <p><strong>Use. </strong> Gift Cards may be redeemed only toward the purchase of eligible pet care
              services provided by Groomit’s affiliated service providers in the United States through the <a href="https://www.groomit.me" target="_blank">www.groomit.me</a> website and/or its related software applications (the "Services").  All Services are not available in all locations.  Use of Services is subject to Groomit’s Terms of Service located at <a href="https://www.groomit.me/terms-privacy" target="_blank">https://www.groomit.me/terms</a>.  Once a Gift Card has been added to a redeemer’s Groomit account, the funds on it will not expire, and there are no fees associated with its use.  The redeemer may be required to add a secondary payment method to use the Gift Card.  Gift Card balances will be automatically applied when Services are booked.  Any unused Gift Card balance will remain in the redeemer’s Gift Card account in the form of a Gift Card.  If Services purchased exceed a redeemer’s Gift Card balance, then the redeemer must pay the difference, using another payment method in his or her account.</p>
            <p><strong>Limitations. </strong> Gift Cards can be purchased and redeemed only in the United States and
              may be used only through a registered members account which is in good standing on Groomit.me. The Gift
              Card cannot be used to purchase other Gift Cards, and unused Gift Card balances may not be transferred
              from the redeemer’s Gift Cards account to another members Gift Cards account.  Gift Cards cannot be
              reloaded, resold, transferred for value or redeemed for cash, except to the extent required by law. </p>
            <p><strong>Account Corrections.</strong>  Groomit reserves the right to correct any Gift Card balance in a Groomit Users account at any time in the event of a clerical, accounting, or related error. </p>
            <p><strong>Risk of Loss.</strong>  The risk of loss and title for a pass to the purchaser upon Groomit electronic transmission of the Gift Card to the purchaser or designated recipient.  Once issued, Groomit is not responsible for the loss, theft, destruction or unauthorized use of any Gift Card. </p>
            <p><strong>Fraud.</strong>  In the event of suspected fraud, Groomit reserves the right to refuse to issue or honor a Gift Card or to terminate any other obligations under these terms and conditions.</p>
            <p><strong>Limitation of Liability.</strong>  GROOMIT MAKES NO WARRANTIES, EXPRESS OR IMPLIED, WITH
              RESPECT TO GIFT CARS , INCLUDING WITHOUT LIMITATION, ANY EXPRESS OR IMPLIED WARRANTY OF MERCHANTABILITY
              OR FITNESS FOR A PARTICULAR PURPOSE.  IN THE EVENT A GIFT CARD IS NON-FUNCTIONAL, YOUR SOLE REMEDY, AND
              OUR SOLE LIABILITY, WILL BE THE REPLACEMENT OF SUCH GIFT CARD. CERTAIN STATE LAWS DO NOT ALLOW LIMITATIONS
              ON IMPLIED WARRANTIES OR THE EXCLUSION OR LIMITATION OF CERTAIN DAMAGES. IF THESE LAWS APPLY TO YOU, SOME OR ALL OF THE ABOVE DISCLAIMERS OR LIMITATIONS MAY NOT APPLY TO YOU.</p>
            <p><strong>Arbitration.</strong> Any dispute or claim relating in any way to the Groomit Gift Card will be resolved in accordance with the Arbitration Agreement section of Groomit’s Terms of Service located at <a href="https://www.groomit.me/terms-privacy" target="_blank">https://www.groomit.me/terms</a>.</p>
            <p><strong>Other Terms.</strong> When you purchase, receive or redeem a Gift Card, you agree that the laws of the State of Washington, without regard to principles of conflict of laws, will govern these Gift Card Terms and Conditions.  Groomit reserves the right to change these terms and conditions from time to time at its discretion, with any such changes to be effective when posted on the Groomit Service Site.  Gift Card use is also subject to Groomit’s Terms of Service (<a href="https://www.groomit.me/terms-privacy" target="_blank">https://www.groomit.me/terms</a>) and Privacy Policy (<a href="https://www.groomit.me/privacy" target="_blank">https://www.groomit.me/privacy</a>).</p>
            <p>If you have any questions or concerns about the status of a Gift Card, please call customer support at 646-589-0556.</p>
            <br>
            <p class="text-center" id="back-form">
              <button class="btn black-btn rounded-btn groomit-btn" type="button" data-dismiss="modal" aria-label="Close">CLOSE</button>
            </p>
          </div>
        </div>
        <!-- /row --> 
      </div>
      <!-- /modal-body --> 
      
    </div>
    <!-- /modal-content --> 
    
  </div>
</div>
<!-- /terms MODAL -->

<div class="modal fade" id="thankyou-modal" tabindex="-1" role="dialog" aria-labelledby="thankyouModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                onclick="close_thankyou_modal()"><span
                  aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body text-center">
        <h4 class="modal-title" id="thankyouModalLabel">Thank you for your order, you are all set!</h4>
        <p>Your order was processed successfully.</p>
        <p>Your order number is <span id="sales_id"></span>.</p>
        <br />
        <br />
        <p>You will receive a code based on the selected package.<br/>
          Please redeem it during the booking process on our app or website by entering the code in the promo code field.</p>
        <br />
        <br />
        <a href="/user/schedule/select-dog" class="btn red-btn rounded-btn groomit-btn">SCHEDULE APPOINTMENT</a> <br />
        <br />
        <br />
        <p>Haven't received the email? Please <a href="/#contactus">contact us</a>.</p>
      </div>
      <!-- /modal-body --> 
      
    </div>
    <!-- /modal-content --> 
    
  </div>
</div>
<!-- /thank-you --> 

<script type="text/javascript">
    function purchase() {
        if (!$('#terms').is(':checked')) {
            alert('Please accept the terms & conditions !!');
            $('#terms').focus();
            return;
        }

        if ($('#is_gift').is(':checked')) {
            if ($('#recipient_email').val() != $('#recipient_email_confirm').val()) {
                alert('Please check recipient email address !!');
                $('#recipient_email').focus();
                return;
            }
        }

        myApp.showConfirm('You are about to buy Groomit Gift Card. Are you sure? ', function() {
            $.ajax({
                url: '/user/gift-cards/buy/process',
                data: {
                    _token: '{{ csrf_token() }}',
                    voucher_id: {{ $voucher->id }},
                    is_gift: ($('#is_gift').is(':checked') ? 'Y' : 'N'),
                    recipient_location: $('#recipient_location').val(),
                    sender: $('#sender').val(),
                    recipient_name: $('#recipient_name').val(),
                    recipient_email: $('#recipient_email').val(),
                    voucher_message: $('#voucher_message').val(),
                    card_holder: $('#card_holder').val(),
                    card_number: $('#card_number').val(),
                    expire_mm: $('#expire_mm').val(),
                    expire_yy: $('#expire_yy').val(),
                    cvv: $('#cvv').val(),
                    zip: $('#zip').val(),
                    address1: $('#address1').val(),
                    address2: $('#address2').val(),
                    city: $('#city').val(),
                    state: $('#state').val(),
                    default_card: ($('#default_card').is(':checked') ? 'Y' : 'N'),
                    billing_address: ($('#billing_address').is(':checked') ? 'Y' : 'N')
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                      $('#recipient_location').val('');
                      $('#sender').val('');
                      $('#recipient_name').val('');
                      $('#recipient_email').val('');
                      $('#voucher_message').val('');
                      $('#card_holder').val('');
                      $('#card_number').val('');
                      $('#expire_mm').val('');
                      $('#expire_yy').val('');
                      $('#cvv').val('');
                      $('#zip').val('');
                      $('#address1').val('');
                      $('#address2').val('');
                      $('#city').val('');
                      $('#state').val('');

                      $('#sales_id').text(res.sales_id);
                      $('#thankyou-modal').modal();
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        });
    }

    function check_is_gift() {
      if($('#is_gift').is(':checked')) {
        $('#recipient-info').show();
      } else {
        $('#recipient-info').hide();
      }
    }

    function close_thankyou_modal() {
        window.location.href = '/user/home';
    }

</script> 
@stop 