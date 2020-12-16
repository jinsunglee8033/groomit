@extends('user.layout.default')

@section('content')

    <link href="/desktop/css/login.css?v=1.0.3" rel="stylesheet">

    <style>
        /*#pac-input {
            height: 50px;
            border: solid 1px #Ddd;
            border-radius: 20px 20px 20px 20px !important;
            width: 100%;
            background: #ebebeb;
            padding-left: 15px;
            font-size: 14px;
        }*/

        input[name="send"]{
            height: 50px;
            border: none;
            color: white;
            background: #c2303d;
            margin-left: -5px;
            border-radius: 20px 20px 20px 20px !important;
            margin-top: 10px;
            width: 30%;
            font-size:14px;
        }

        input[name="send"]:hover{
            background:#79111b !important;
        }

        input[name="zip"]:focus, input[name="send"]:focus{
            outline:0;
        }

    </style>

    <script type="text/javascript">
        function check_addr() {

            if($('#zip').val().length < 1){
                alert('Please enter full address !');
                return;
            }
            $('#send-zip').submit();

        }
    </script>
    <div class="aos main--no-margin" id="main">

        

        <!-- TOP BANNER -->
        <!--<div class="display-table top-banner-init" id="top-banner">
            <div class="table-cell text-center" id="banner-title">
                <h1><img class="img-responsive home-blade-img" src="/desktop/img/groomit-title.png" width="325" height="56"
                         alt="Groomit"></h1>
                <h2>Pet grooming on demand<br>at your time & place</h2>
            </div>
            <a href="#schedule-appointment" class="visible-xs"><span></span></a>
        </div>-->
        <!-- /top-banner -->

        <section class="header-promo" title="In-Home pet grooming">
          <div class="container">
            <div class="row">
              <div class="col-sm-6 col-sm-offset-3 col-xs-12 header-promo__content"> 
                <h1 class="header-promo__title header-promo__title--white header-promo__title--neutra text-center">
                  Same-Day In-Home Grooming
                </h1>
                <h2 class="header-promo__subtitle header-promo__title--white header-promo__title--neutra text-center">
                  Safe & convenient. Highly skilled & certified pet stylists
                </h2>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-6 col-sm-offset-3 col-xs-10 col-xs-offset-1">
              
                    {{--@if($errors->any())--}}
                    {{--<div class="alert alert-info">{{$errors->first()}}</div>--}}
                    {{--@endif--}}
                    @if (!empty($error))
                    <div class="header-promo__alert">
                      <div class="alert alert-danger">{{ $error }}</div>
                    </div>
                    @endif
                  <form method="post" class="center-block" id="send-zip" action="/user/check-zip" name="send-zip">
                    {!! csrf_field() !!}
                      <input type="hidden" name="address1" id="address1" value="">
                      <input type="hidden" name="city" id="city" value="">
                      <input type="hidden" name="state" id="state" value="">
                      <input type="hidden" name="zip" id="zip" value="">

                      <div class="input-group header-promo__input">
                      <input name="address" type="text" class="form-control" placeholder="Enter your address" id="pac-input">
                      <span class="input-group-btn">
                        <button class="btn" name="send" id="continue" onclick="check_addr()"><i class="fas fa-chevron-right"></i></button>
                      </span>
                    </div>
                    @if (!Auth::guard('user')->check())
                    <div id="already">Existing user? <a href="#" onclick="show_login(); return false">Sign In</a></div>
                    @endif

                  </form>
              </div>
            </div>
          </div>
        </section>
        <!-- /header-banner -->

        <section id="benefits">
          <div class="container">
            <div class="row">
              <div class="col-lg-7 col-md-6 col-md-offset-0 col-sm-10 col-sm-offset-1" id="benefits-video">
                <div class="benefits__video-container">
                  <iframe 
                    width="100%" 
                    height="448" 
                    src="https://www.youtube.com/embed/ZxkvwtHYaJc?rel=0&amp;showinfo=0" 
                    frameborder="0" 
                    allow="autoplay; encrypted-media" 
                    allowfullscreen>
                  </iframe>
                </div>
              </div>
              <div class="col-lg-5 col-md-6 col-md-offset-0 col-sm-10 col-sm-offset-1">
                <div class="row">
                  <div class="col-xs-12 benefit-item">
                    <div class="media">
                      <div class="media-left"> 
                        <img class="media-object" src="../images/icon__benefits-dog.svg" width="48" height="auto" alt="Stress free dog grooming">
                      </div>
                      <div class="media-body">
                        <h3 class="media-heading">Convenient & Stress Free</h3>
                        <p>Getting an in-home pet grooming service is convenient and safe and since your pet is in a familiar environment, it's less stressful. We make sure to clean after every grooming.</p>
                      </div>
                  </div>
                  <!-- /media -->
                  </div>
                  <div class="col-xs-12 benefit-item">
                    <div class="media">
                      <div class="media-left"> 
                        <img class="media-object" src="../images/icon__benefits-paws.svg" width="57" height="auto" alt="Pet grooming insurance"> 
                      </div>
                      <div class="media-body">
                        <h3 class="media-heading">Premium Insurance</h3>
                        <p>Every booking made through our site or app is bonded and covered by premium insurance. Safety is our priority.</p>
                      </div>
                    </div>
                    <!-- /media -->
                  </div>
                  <!-- /col -->

                  <div class="col-xs-12 benefit-item">
                    <div class="media">
                      <div class="media-left"> 
                        <img class="media-object" src="../images/icon__benefits-groomer.svg" width="77" height="auto" alt="Certified pet groomers"> 
                      </div>
                      <div class="media-body">
                        <h3 class="media-heading">Certified Groomers</h3>
                        <p>The Groomit app connects certified pet groomers with pet owners. Groomit runs an extensive background check on all groomers.</p>
                      </div>
                    </div>
                    <!-- /media -->
                  </div>
                  
                  <!-- /col -->
                </div>
                <!-- /row -->
              </div>
              <!-- /col -->
            </div>
            <!-- /row -->
          </div>
        </section>
    </div>

    <script>
        function initMap() {
            var input = document.getElementById('pac-input');
            var options = {
                componentRestrictions: {country: "us"}
            };
            var autocomplete = new google.maps.places.Autocomplete(input, options);
            autocomplete.setFields(['address_components', 'geometry', 'icon', 'name']);
            autocomplete.addListener('place_changed', function() {
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                    window.alert("No details available for input: '" + place.name + "'");
                    return;
                }

                var address1  = '';
                var city      = '';
                var state     = '';
                var zip       = '';

                var street_number = '';
                var route         = '';
                var locality      = '';
                var administrative_area_level_1 = '';
                var postal_code   = '';

                for (var i = 0; i < place.address_components.length; i++) {
                    if (place.address_components[i].types[0] == "street_number") {
                        street_number = place.address_components[i].short_name;
                    }
                    if (place.address_components[i].types[0] == "route") {
                        route = place.address_components[i].short_name;
                    }
                    if (place.address_components[i].types[0] == "locality") {
                        locality = place.address_components[i].short_name;
                    }
                    if (place.address_components[i].types[0] == "administrative_area_level_1") {
                        administrative_area_level_1 = place.address_components[i].short_name;
                    }
                    if (place.address_components[i].types[0] == "postal_code") {
                        postal_code = place.address_components[i].short_name;
                    }
                }

                var address1  = street_number + " " + route;
                var city      = locality;
                var state     = administrative_area_level_1;
                var zip       = postal_code;

                // alert(address1 + city + state + zip);

                $('#address1').val(address1);
                $('#city').val(city);
                $('#state').val(state);
                $('#zip').val(zip);

                $('#continue').prop('disabled', false);

            });
        }
    </script>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBQGtikp7nu9OJqF2Ogds59SsilNlPYLTw&libraries=places&callback=initMap"
            async defer>
    </script>

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


@stop
