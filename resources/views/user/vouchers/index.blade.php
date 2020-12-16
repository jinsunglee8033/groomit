@extends('user.layout.default')
@section('content')
<link href="/desktop/css/login.css?=v4.5" rel="stylesheet">

<!-- TOP BANNER -->
<div class="aos" id="vouchers">
  <div class="display-table" id="top-banner">
    <div class="table-cell text-center" id="banner-title">
    <img class="img-responsive" src="/desktop/img/groomit-giftcards.png" width="378" height="152" alt="Groomit Gift Cards" /> </div>
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
        <div class="col-sm-3">
          <div class="icon-how"> <img src="/desktop/img/voucher-select.png" width="53" height="80" alt="Select your preferred voucher." /> </div>
          <p class="how-desc">Select your preferred <strong>Groomit Voucher</strong></p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3">
          <div class="icon-how"> <img  src="/desktop/img/voucher-email.png" width="69" height="69" alt="We email you voucher code." /> </div>
          <p class="how-desc">We email your <strong>voucher code</strong></p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3">
          <div class="icon-how"> <img src="/desktop/img/voucher-service.png" width="125" height="59" alt="Select the Groomit Service you prefer." /> </div>
          <p class="how-desc">Select the <strong>Groomit Service</strong> you prefer</p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3">
          <div class="icon-how"> <img src="/desktop/img/voucher-credit.png" width="41" height="56" alt="Groomit Credits will be applied to your account." /> </div>
          <p class="how-desc">Groomit <strong>Credits will be applied</strong> to your account</p>
        </div>
        <!-- /col-6 -->
      </div>
      <!-- /row -->
      <div class="row visible-xs">
        <div class="col-sm-3">
          <div class="icon-how"> <img src="/desktop/img/voucher-select.png" width="53" height="80" alt="Select your preferred voucher." /> </div>
          <p class="how-desc">Select your preferred <strong>Groomit Voucher</strong></p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3">
          <div class="icon-how"> <img  src="/desktop/img/voucher-email.png" width="69" height="69" alt="We email you voucher code." /> </div>
          <p class="how-desc">We Email your <strong>voucher code</strong></p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3">
          <div class="icon-how"> <img src="/desktop/img/voucher-service.png" width="125" height="59" alt="Select the Groomit Service you prefer." /> </div>
          <p class="how-desc">Select the <strong>Groomit Service</strong> you prefer</p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3">
          <div class="icon-how"> <img src="/desktop/img/voucher-credit.png" width="41" height="56" alt="Groomit Credits will be applied to your account." /> </div>
          <p class="how-desc">Groomit <strong>Credits will be applied</strong> to your account</p>
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
        <p><strong>Start Your Pet Care Routine Today</strong></p>
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
        <div class="col-sm-6 {{$class}}"> <img class="voucher-card img-responsive" src="{{ $g->image }}" width="586" height="315" alt="Voucher" />
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

  <!--  <section class="text-center" id="voucher-faqs">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h2>FAQs</h2>
        </div>
      </div>
      <div class="row">
      <div class="col-md-10 col-md-offset-1">
      	<div class="panel-group text-left" id="accordion">
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop" data-toggle="collapse" data-parent="#accordion" data-target="#collapse01"  aria-expanded="true" aria-controls="collapse01">What vaccinations do we require?</a> </h4>
              </div>
              <div id="collapse01" class="panel-collapse collapse in">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>We require all dogs to be current on their vaccinations. All necessary vaccinations must be administered at least 24 hours prior to your scheduled appointment.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse02"  aria-expanded="true" aria-controls="collapse02">How much more does GroomIt charge compared to a grooming shop?</a> </h4>
              </div>
              <div id="collapse02" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>We offer competitive pricing and charge between 10-25% more for all in-home grooming services. </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse03"  aria-expanded="true" aria-controls="collapse03">What products do we use to bathe your dog?</a> </h4>
              </div>
              <div id="collapse03" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>We only use all natural and hypoallergenic products.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse04"  aria-expanded="true" aria-controls="collapse04">How long does it take to groom my dog?</a> </h4>
              </div>
              <div id="collapse04" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>On average, we allocate about an hour and a half to groom your dog. However, the size, condition and temperament of your dog will ultimately determine your dog's grooming time.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse05"  aria-expanded="true" aria-controls="collapse05">How quickly will my groomer arrive?</a> </h4>
              </div>
              <div id="collapse05" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>We will always do our best to meet your requested timeframe. We give our client's an hour within which the groomer will arrive. Appointments can be made the same day you call.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse06"  aria-expanded="true" aria-controls="collapse06">Where will the grooming take place?</a> </h4>
              </div>
              <div id="collapse06" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>Your dog will be groomed in the comfort and convenience of your own home. You must have a bathtub or shower accessible for us to bathe all large breeds. For small breeds, we can use your bath or kitchen sink. We only ask that you please provide towels. A secure counter and or table is required for all dogs to safely and properly be groomed.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse07"  aria-expanded="true" aria-controls="collapse07">Do you bathe the dog first?</a> </h4>
              </div>
              <div id="collapse07" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>To get a smooth and even haircut, the hair needs to be clean. Depending on your dog’s breed and style, the groomer may do a “rough cut.” After the rough cut, your dog is washed, fluff dried at which point the haircut is then completed. In other cases, we may wash and dry the dog completely before any length is taken off.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse08"  aria-expanded="true" aria-controls="collapse08">Can you groom my dog to the breed standard?</a> </h4>
              </div>
              <div id="collapse08" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>Yes. Our groomers are familiar with most of the breed standards as well as being highly experienced with the more popular breeds.
                      Many clients in NYC do not groom to breed standard due to the level of maintenance required. 
                      Regardless, if you are looking for a specific style, providing pictures allows both you and your groomer to see what your options are. </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse09"  aria-expanded="true" aria-controls="collapse09">Do I get the same groomer each time?</a> </h4>
              </div>
              <div id="collapse09" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>Some clients prefer to work with the same groomer. We recommend this if your dog tends to be anxious and nervous. Many dogs like the familiarity of having the same groomer as it allows them to not only feel more comfortable and relaxed, but it also allows the groomer to establish a consistent relationship with the dog. 
                      If you would like to have the same groomer just "favorite" the groomer of your choice within the app.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse10"  aria-expanded="true" aria-controls="collapse10">Do you trim the nails?</a> </h4>
              </div>
              <div id="collapse10" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>Yes, trimming the nails is an important part of the grooming process.
                      If your dog’s nails aren’t trimmed on a regular basis, the nail(s) may bleed. Regular nail trimming can minimize the chance of bleeding, but may not eliminate the risk completely.
                      Please note we do not offer "nails only" service.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse11"  aria-expanded="true" aria-controls="collapse11">What are anal glands?</a> </h4>
              </div>
              <div id="collapse11" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>They are the scent sacs directly under your dog’s tail. Some dogs empty them naturally, some do not. 
                      Per your request, please let your groomer know if you would like to have your dog's anal glands emptied (expressed) or if you would rather have your veterinarian do so.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse12"  aria-expanded="true" aria-controls="collapse12">What if I don ‘t like the groom when it is finished?</a> </h4>
              </div>
              <div id="collapse12" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>Please tell us! We want you to be 100% satisfied. 
                      If there are slight changes you would like done, your groomer can usually make those adjustments right then and there.
                      In other instances, if the changes you request require more time, we may ask that your groomer come back another day in which case we can start again. If there are two or more people responsible for the grooming result, it is important that they first agree on a style before giving the groomer further instructions.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse13"  aria-expanded="true" aria-controls="collapse13">How much training does your groomer have?</a> </h4>
              </div>
              <div id="collapse13" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>All our groomers receive safety & policy training. We offer advanced and specialized training to all our current groomers as well as providing training to any beginners who are interested in joining our team at GroomIt.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse14"  aria-expanded="true" aria-controls="collapse14">Do we have to tip the groomers?</a> </h4>
              </div>
              <div id="collapse14" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>It is not required and at your full discretion as to whether you chose to tip your groomer. You have the option to tip within in the app.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse15"  aria-expanded="true" aria-controls="collapse15">How can I earn $25 off my next booking?</a> </h4>
              </div>
              <div id="collapse15" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>Once your first grooming is complete you can refer a friend by using your unique code. We will then credit your account $25 to be applied to your next grooming.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
      </div>
      </div>
    </div>
  </section>-->
  <!-- /faqs -->
    <div class="modal fade" id="modal-purchase-info" tabindex="-1" role="dialog" aria-labelledby="editPetLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h4 class="modal-title text-center" id="editPetLabel">EMAIL ADDRESS</h4>

                    <!-- INIT FORM -->
                    <form action="/user/vouchers/payment" id="form-purchase-info" class="material-form" role="form" method="post">
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
          <p>&copy; 2018 groomit.me - All rights reserved. <span class="hidden-xs">Made in NYC with <i class="fas fa-heart"></i></span></p>
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
