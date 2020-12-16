@extends('user.layout.default')
@section('content')
<link href="/desktop/css/login.css?v=4.8.2" rel="stylesheet">

<!-- TOP BANNER -->
<div class="aos vouchers--gift-cards" id="vouchers">
  <div class="display-table" id="top-banner" title="Dog gift ideas">
    <!--<div class="table-cell text-center" id="banner-title">
      <img class="img-responsive" src="/desktop/img/groomit-giftcards.png" width="378" height="152" alt="Groomit Gift Cards" /> 
    </div>-->
    <!-- /banner-title -->
  </div>
  <!-- /top-banner -->

	  <!--<section class="text-center" id="voucher-benefits">
		<div class="container">
		  <div class="row">
		  <div class="col-lg-12">
		  <div class="row">
			<div class="col-sm-4">
			 <div class="display-table">
			 <span class="table-cell b-icon"><img src="/desktop/img/extra-savings.png" width="44" height="44" alt="Extra Savings" /></span><span class="table-cell b-text">Extra Savings</span>
			 </div>
				<div class="display-table">
			 <span class="table-cell b-icon"><img src="/desktop/img/free-addons.png" width="37" height="37" alt="Extra Savings" /></span><span class="table-cell b-text">Free Add-ons</span>
			 </div>
				<div class="display-table">
			 <span class="table-cell b-icon"><img src="/desktop/img/VIP-Reward-Points.png" width="37" height="37" alt="VIP Reward Points" /></span><span class="table-cell b-text">VIP Reward Points</span>
			 </div>
			</div>
			<div class="col-sm-4">
			  <div class="display-table">
			 <span class="table-cell b-icon"><img src="/desktop/img/exclusive-events.png" width="34" height="34" alt="Extra Savings" /></span><span class="table-cell b-text">Exclusive Events</span>
			 </div>

			   <div class="display-table">
			 <span class="table-cell b-icon"><img src="/desktop/img/special-giveaways.png" width="35" height="32" alt="Extra Savings" /></span><span class="table-cell b-text">Special Giveaways</span>
			 </div>

			   <div class="display-table">
			 <span class="table-cell b-icon"><img src="/desktop/img/Credits-Never-Expires.png" width="37" height="37" alt="Credits Never Expires" /></span><span class="table-cell b-text">Credits Never Expires</span>
			 </div>
			</div>
			<div class="col-sm-4">
			   <div class="display-table">
			 <span class="table-cell b-icon"><img src="/desktop/img/social-media-tags.png" width="32" height="32" alt="Social Media Tags" /></span><span class="table-cell b-text">Social Media Tags</span>
			 </div>
			   <div class="display-table">
			 <span class="table-cell b-icon"><img src="/desktop/img/vip-customer-support.png" width="18" height="33" alt="VIP Customer Support" /></span><span class="table-cell b-text">VIP Customer Support</span>
			 </div>
			   <div class="display-table">
			 <span class="table-cell b-icon"><img src="/desktop/img/Highly-Reviewed.png" width="37" height="37" alt="Highly Reviewed Groomers Only" /></span><span class="table-cell b-text">Highly Reviewed Groomers Only</span>
			 </div>
			</div>
			</div>

			</div>
		  </div>
		</div>
	  </section>-->
  <!-- /select-voucher -->

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
            <img src="/desktop/img/voucher-select.png" width="53" height="80" alt="Dog gift" />
          </div>
          <p class="how-desc">Select the preferred<br/> E-Gift Card amount</p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3" data-aos="flip-right" data-aos-delay="500">
          <div class="icon-how">
            <img  src="/desktop/img/voucher-email.png" width="69" height="69" alt="Dog gift" />
          </div>
          <p class="how-desc">E-Gift Card will be emailed instantly to your recipient</p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3" data-aos="flip-left" data-aos-delay="1000">
          <div class="icon-how">
            <img src="/desktop/img/voucher-service.png" width="125" height="59" alt="Dog gift" />
          </div>
          <p class="how-desc">Redeem within Groomit App / Website at checkout</p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3" data-aos="flip-right" data-aos-delay="1500" >
          <div class="icon-how">
            <img src="/desktop/img/voucher-credit.png" width="41" height="56" alt="Dog gift" />
          </div>
          <p class="how-desc">Groomit Credits will be applied to your account</p>
        </div>
        <!-- /col-6 -->
      </div>
      <!-- /row -->
      <div class="row visible-xs">
        <div class="col-sm-3">
          <div class="icon-how">
            <img src="/desktop/img/voucher-select.png" width="53" height="80" alt="Select the preferred E-Gift Card amount" />
          </div>
          <p class="how-desc">Select the preferred<br/> E-Gift Card amount</p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3">
          <div class="icon-how">
            <img  src="/desktop/img/voucher-email.png" width="69" height="69" alt="E-Gift Card will be emailed instantly to your recipient" />
          </div>
          <p class="how-desc">E-Gift Card will be emailed instantly to your recipient</p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3">
          <div class="icon-how">
            <img src="/desktop/img/voucher-service.png" width="125" height="59" alt="Redeem within Groomit App / Website at checkout" />
          </div>
          <p class="how-desc">Redeem within Groomit App / Website at checkout</p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3">
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
  
   <section class="text-center" id="get-it">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
        <p><strong>Groomit Gift Card is the perfect present for any dog or cat owner</strong></p>
        <p>Get It For Your Pet Or Send It As Gift</p>
        </div>
        </div>
        </div>
        </section>

  <section class="text-center" id="select-voucher">
    <div class="container">
      <div class="row" id="vouchers-row"> @foreach ($giftcards as $key => $g)
      @if ($key % 2 == 0)
      	@php ($class = "v-left")
      @else
      	@php ($class = "v-right")
      @endif
        <div class="col-sm-6 {{$class}}"> <img class="voucher-card img-responsive" src="{{ $g->image }}" width="586"
                                               height="315" alt="Cat gift" />
            <?php /*?>@if ($g->status == 'A')<?php */?>
            <button onclick="purchase({{ $g->id }})" target="_self" class="btn btn-default buy-now" title="Buy Now">BUY NOW</button>
           <?php /*?>@endif<?php */?>
        </div>
        @endforeach </div>
      <!-- /row -->
    </div>
    <!-- /container -->
  </section>
  <!-- /select-voucher -->

  
    <div class="modal fade" id="modal-purchase-info" tabindex="-1" role="dialog" aria-labelledby="editPetLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h4 class="modal-title text-center" id="editPetLabel">EMAIL ADDRESS</h4>

                    <!-- INIT FORM -->
                    <form action="/user/gift-cards/payment" id="form-purchase-info" class="material-form" role="form" method="post">
                      {!! csrf_field() !!}
                      <input type="hidden" id="p_voucher_id" name="voucher_id">
                      <div class="form-group">
                        <input type="text" id="receiver_email" name="email" class="form-control" placeholder="Receiver Email">
                      </div>

                      <div class="form-group text-center">
                           <button type="button" class="groomit-btn black-btn rounded-btn long-btn space-btn" id="btn_close" data-dismiss="modal" aria-label="Close">CLOSE
                          </button>
                          <button type="submit" class="groomit-btn rounded-btn red-btn long-btn" id="btn_submit">SUBMIT</button>
                      </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- /vouchers -->
<footer id="copyrights">
    <div class="container">
      <div class="row">
        <div class="col-xs-12 text-center">
          <p>&copy; 2020 groomit.me - All rights reserved. <span class="hidden-xs">Made in NYC with <i class="fas fa-heart"></i></span></p>
          <p class="visible-xs">Made in NYC with <i class="fas fa-heart"></i></p>
        </div>
      </div>
    </div>
  </footer>
<input type="hidden" id="is_login" value="{{ Auth::guard('user')->check() ? 'Y' : 'N' }}">
<script type="text/javascript">
    function purchase(giftcard_id) {
      var is_login = $('#is_login').val();

      if (is_login != 'Y') {
        show_login();
        return;
      }

      $('#p_voucher_id').val(giftcard_id);
      $('#form-purchase-info').submit();

        // myApp.showConfirm('You are about to buy gift card ', function() {
            // $.ajax({
            //     url: '/user/vouchers/buy',
            //     data: {
            //         _token: '{{ csrf_token() }}',
            //         giftcard_id: giftcard_id
            //     },
            //     cache: false,
            //     type: 'post',
            //     dataType: 'json',
            //     success: function(res) {
            //         if ($.trim(res.msg) === '') {

            //         } else {
            //             myApp.showError(res.msg);
            //         }
            //     }
            // });
        // });
    }

</script>
@stop
