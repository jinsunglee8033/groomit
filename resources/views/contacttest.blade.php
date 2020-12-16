@extends('includes.default')

@section('content')
<section id="banner" class="bannerFaqs"> <!-- #banner starts -->
    <div class="container table-cell">
        <div class="col-lg-12 top col-md-12 text-center col-sm-12 col-xs-12">
            <img src="images/groomit_text.png" alt="" />
            <p class="bannerInP">Contact Us</p>
        </div>
    </div>
</section> <!-- #banner ends -->
<section id="contactus"> <!-- #contactus starts -->
    <div class="container">
      <div class="row">
        <div class="col-md-8 col-md-offset-2 col-xs-12">
          <form id="frm_contact" name="frm_contact" method="post" action="/contact_us" onsubmit="return checkForm(this);">
            {!! csrf_field() !!}

            @if($errors->any())
            <div class="container-fluid">
              <div class="col-lg-12 col-sm-12 col-md-12 alert alert-info">{{$errors->first()}}</div>
            </div>
            @endif
            <div class="row">
              <div class="col-lg-6 col-sm-6 col-md-6">
                <input name="first_name" type="text" value="{{ old('first_name') }}" placeholder="First Name" required/>
              </div>
              <div class="col-lg-6 col-sm-6 col-md-6">
                <input name="last_name" type="text" value="{{ old('last_name') }}" placeholder="Last Name" required/>
              </div>
              <div class="col-lg-12 col-sm-12 col-md-12">
                <input name="email" type="email" value="{{ old('email') }}" placeholder="Email Address" required/>
              </div>
              <div class="col-lg-12 col-sm-12 col-md-12">
                <input name="subject" type="text" value="{{ old('subject') }}" placeholder="Subject" required/>
              </div>
              <div class="col-lg-12 text-center">
                <textarea name="message" placeholder="Message">{{ old('message') }}</textarea>
              </div>
              <div class="col-lg-6 col-sm-6 col-md-6">
                <canvas id="myCanvas" style="width: 100%; height: 100px;"></canvas>
              </div>
              <div class="col-lg-6 col-sm-6 col-md-6">
                <input name="verification_code" type="text" placeholder="Please Enter Verification Code" required/>
              </div>
              <div class="col-lg-12 col-sm-12 col-md-12 text-center">
                <input type="submit" name="submitBtn" value="SEND" />
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
@stop


