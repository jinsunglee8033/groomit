@extends('includes.affiliate_default_new')
@section('contents')
<div class="short-banner-main">
    <section id="banner" class="display-table" style="padding:0 !important;padding-top:0 !important; position:relative;"> <!-- #banner starts -->
        <div class="container container-banner-info table-cell">
            <div class="col-xs-12" id="centerBannerHome" >
                <h1 data-aos="zoom-in" data-aos-delay="750">Affiliate - Apply</h1>
            </div>
        </div>
    </section>
    <!-- #banner ends -->

    <section id="press-tabs">
        <div class="container">
          <div class="row"><!-- Starts row -->
            <div class="col-md-12 text-center"><!-- Starts col-12 -->
              <h2 class="text-center section-title">Affiliates Application</h2>
            </div><!-- Ends col-12 -->
          </div><!-- Ends row -->

          
          <div class="row"><!-- Starts row -->
            <div class="col-md-10 col-md-offset-1 text-center cont-cont-white-form"><!-- Starts col-10 -->
              <div class="cont-white-form">
                <form class="form-in" method="post" action="/affiliate/apply">
    {{--              {!! csrf_field() !!}--}}
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                  @if ($alert = Session::get('alert'))
                    <div class="alert alert-danger">
                      {{ $alert }}
                    </div>
                  @endif
                  <input type="text" name="first_name" placeholder="First Name*" required>
                  <input type="text" name="last_name" placeholder="Last Name*" required>
                  <input type="email" name="email" placeholder="Email*" required>
                  <input type="text" name="business_name" placeholder="Business Name" >
                  <input type="password" name="password" placeholder="Password*" required>
                  <input type="password" name="password_confirmation" placeholder="Repeat Password*" required>
    {{--              <a href="#" onclick="document.forms[0].submit();" class="red-btn btn-in">APPLY NOW</a>--}}
                    <button class="btn btn-default btn-rounded btn-red aos-init aos-animate" type="submit">APPLY NOW</button>
                </form>
              </div>
            </div><!-- Ends col-10 -->
          </div><!-- Ends row -->

        </div>
    </section>

</div>
@stop
