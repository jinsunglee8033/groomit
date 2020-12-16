@extends('user.layout.default')
@section('content')
<link href="/desktop/css/login.css?v=4.8.1" rel="stylesheet">

<!-- TOP BANNER -->
<div class="aos vouchers--membership" id="vouchers">
  <div class="display-table" id="top-banner" title="Dog groomers near me">
  </div>
  <!-- /top-banner -->
  <div class="text-center">
      <img class="img-responsive" src="/desktop/img/membership-bar.png" alt="Info on memberships" />
  </div>

  <section class="text-center" id="voucher-how">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h2>How it works</h2>
        </div>
      </div>
      <!-- /row -->
      <div class="row hidden-xs">
        <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
          <div class="row">
              <div class="col-sm-4">
                <div class="icon-how"> <img src="/desktop/img/voucher-select.png" width="53" height="80" alt="Pet grooming NYC" /> </div>
                <p class="how-desc">Select your preferred<br/><strong>membership package</strong></p>
              </div>
              <!-- /col-6 -->
              <div class="col-sm-4">
                <div class="icon-how"> <img  src="/desktop/img/voucher-email.png" width="69" height="69" alt="Cat grooming NYC" />
                </div>
                <p class="how-desc">Check your email for<br/><strong>membership code</strong></p>
              </div>
              <!-- /col-6 -->
              <div class="col-sm-4">
                <div class="icon-how"> <img src="/desktop/img/voucher-service.png" width="125" height="59" alt="Dog grooming NYC" /> </div>
                <p class="how-desc">Schedule a <strong>Groomit<br/>Appointment</strong></p>
              </div>
              <!-- /col-6 -->
          </div>
        </div>
      </div>
      <!-- /row -->
      <div class="row visible-xs">
        <div class="col-sm-3">
          <div class="icon-how"> <img src="/desktop/img/voucher-select.png" width="53" height="80" alt="Pet grooming NYC" /> </div>
          <p class="how-desc">Select your preferred<br/><strong>membership package</strong></p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3">
          <div class="icon-how"> <img  src="/desktop/img/voucher-email.png" width="69" height="69" alt="Cat grooming NYC" /> </div>
          <p class="how-desc">Check your email for<br/><strong>membership code</strong></p>
        </div>
        <!-- /col-6 -->
        <div class="col-sm-3">
          <div class="icon-how"> <img src="/desktop/img/voucher-service.png" width="125" height="59" alt="Dog grooming NYC" /> </div>
          <p class="how-desc">Schedule a <strong>Groomit<br/>Appointment</strong></p>
        </div>
        <!-- /col-6 -->
      </div>
      <!-- /row -->
    </div>
    <!-- /container -->
  </section>
  <!-- /voucher-how -->


  <section class="text-center" id="select-voucher">
    <div class="container">
      <div class="row" id="vouchers-row"> @foreach ($giftcards as $key => $g)
      @if ($key % 2 == 0)
      	@php ($class = "v-left")
      @else
      	@php ($class = "v-right")
      @endif
        <div class="col-sm-6 {{$class}}"> <img class="voucher-card img-responsive" src="{{ $g->image }}" width="586"
                                               height="315" alt="Grooming packages" />
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
                    <form action="/user/memberships/payment" id="form-purchase-info" class="material-form" role="form" method="post">
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
