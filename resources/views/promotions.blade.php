@extends('includes.default')

@section('content')
<div class="promotions">
  <section id="banner"> <!-- #banner starts -->
    <div class="container">
      <div class="col-lg-12 top col-md-12 text-center col-sm-12 col-xs-12" id="centerBannerHome">
        <p class="bannerInP">Promotions</p>
      </div>
    </div>
  </section>
  <!-- /banner-->

  <section class="text-center" id="refer-friend">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h1>Refer a Friend</h1>
          <h4><strong>Refer a friend and get $15 both of you.</strong></h4>
          <h4 class="h4-reg">Find your unique code on your Dashboard once you Sign up or Login to Groomit App.</h4>
        </div>
      </div>
      <!-- /row -->
    </div>
    <!-- /container -->
  </section>
  <!-- /refer-friend -->

  <!--<section id="download-app" class="text-center">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h1>Download App</h1>
          <h4>Select your device platform and get dog shining!</h4>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 col-md-offset-3 col-sm-10 col-sm-offset-1">
          <div class="row" id="app-icons">
            <div class="col-sm-6"> <a target="_blank" href="https://itunes.apple.com/us/app/groomit/id1240314505?mt=8"><img src="images/image_appstore.png" class="img-responsive" alt="App Store" /></a> </div>
            <div class="col-sm-6"> <a target="_blank" href="https://play.google.com/store/apps/details?id=com.groomit.inc.groomit&hl=en"><img src="images/image_googleplay.png" class="img-responsive" alt="Google Play Store" /></a> </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <h4>Or Schedule An Appointment</h4>
              <a href="#" target="_self" class="schedule-btn" title="Schedule Appointment">Schedule Appointment</a> </div>
          </div>
        </div>
      </div>
    </div>
  </section>-->
  <!-- #downloadapp ends -->

  <section id="affiliates-banner" class="text-center">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h1>Affiliates</h1>
          <h4><strong>Make $15 per referral by joining the free Groomit Affiliate Program</strong></h4>
          <br />
          <h4>When you sign up for the Groomit Affiliate Program we'll give you a promo code that you can share through an ad, Facebook post, on your blog, a tweet – however you choose! You'll earn income for every customer that makes an appointment with groomit through your code.</h4>
        </div>
        <!-- Ends col-12 -->
      </div>
      <!-- Ends row -->
      <div class="row">
        <div class="col-md-12"> <a href="/affiliate/apply" target="_self" title="APPLY NOW" class="schedule-btn">APPLY NOW</a> </div>
        <!-- Ends col-12 -->
      </div>
      <!-- Ends row -->
      <!--<div class="row" id="social">
        <div class="col-md-6 col-md-offset-3 col-sm-10 col-sm-offset-1">
          <div class="row">
            <div class="col-sm-3 col-xs-6"> <a href="https://www.facebook.com/groomitapp/" target="_blank" title="Facebook"><img src="images/facebook-btn.jpg" width="95" height="12" alt="Facebook" /></a> </div>
            <div class="col-sm-3 col-xs-6"> <a href="https://twitter.com/groomitapp" target="_blank" title="Twitter"><img src="images/twitter-btn.jpg" width="83" height="10" alt="Twitter" /></a> </div>
            <div class="col-sm-3 col-xs-6"> <a href="https://www.instagram.com/groomitapp/" target="_blank" title="Instagram"><img src="images/instagram-btn.jpg" width="106" height="12" alt="Instagram" /></a> </div>
            <div class="col-sm-3 col-xs-6"> <a href="https://www.linkedin.com/company/11050921/" target="_blank" title="LinkedIn"><img src="images/linkedin-btn.jpg" width="88" height="11" alt="LinkedIn" /></a> </div>
          </div>
        </div>
      </div>-->
      <!-- /row -->
    </div>
    <!-- Ends Container -->
  </section>
  <!-- /affiliates-banner -->

<!--  <section id="work-with-us">
    <div class="container">
      <div class="row">
        <div class="col-md-12 text-center">
          <h1>Are you a Groomer? Work with us</h1>
        </div>
      </div>
      <div class="row" id="form-work">
        <div class="col-md-12 text-center">
          <form action="application">
            <div class="row">
                  <div class="equal10 col-sm-4">
                    <input type="text" value="" placeholder="First Name" />
                  </div>
                  <div class="equal10 col-sm-4">
                    <input type="text" value="" placeholder="Last Name" />
                  </div>
                  <div class="equal10 col-sm-4">
                    <input type="email" value="" placeholder="Email" />
                  </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>-->
  <!-- /work with us -->
</div>
<!-- /promotions -->
@stop
