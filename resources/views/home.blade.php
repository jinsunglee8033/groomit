@extends('includes.default')

@section('content')

  <style>

    input[name="send"]{
      height: 50px;
      border: none;
      color: white;
      background: #c2303d;
      margin-left: -5px;
      border-top-right-radius: 20px;
      border-bottom-right-radius: 20px;
      border-top-left-radius: 20px;
      border-bottom-left-radius: 20px;
      width: 30%;
      font-size:14px;
    }

    input[name="send"]:hover{
      background:#79111b !important;
    }

    input[name="zip"]:focus, input[name="send"]:focus{
      outline:0;
    }

    input[name="zip"]{
      height: 50px;
      border: solid 1px #Ddd;
      border-top-left-radius: 20px;
      border-bottom-left-radius: 20px;
      width: 50%;
      background: #ebebeb;
      padding-left: 15px;
      font-size: 14px;
    }

    /*#pac-input {
      height: 50px;
      border: solid 1px #Ddd;
      border-top-right-radius: 20px;
      border-bottom-right-radius: 20px;
      border-top-left-radius: 20px;
      border-bottom-left-radius: 20px;
      width: 100%;
      background: #ebebeb;
      padding-left: 15px;
      font-size: 14px;
    }*/

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

<main id="home-main">
  <section class="header-promo hidden-xs">
    <div class="container">
      <div class="row">
        <div class="col-sm-6 col-sm-offset-3 col-xs-10 header-promo__content"> 
          <h1 class="header-promo__title header-promo__title--black header-promo__title--neutra text-center" data-aos="zoom-in" data-aos-delay="750">
            Same-Day In-Home Grooming
          </h1>
          <h2 class="header-promo__subtitle header-promo__title--black header-promo__title--neutra text-center" data-aos="zoom-in" data-aos-delay="850">
            Safe & convenient. Highly skilled & certified pet stylists come to you
          </h2>
          <form method="post" action="/user/check-zip" name="send-zip" id="send-zip">
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
              <div id="map"></div>
          </form>
        </div>
      </div>
    </div>
  </section>
  <!-- /header-banner -->
  <section class="header-promo header-promo--mobile visible-xs">
    <div class="container">
      <div class="row">
        <div class="col-xs-8 header-promo__content"> 
          <h3 class="header-promo__title header-promo__title--black header-promo__title--neutra text-center" data-aos="zoom-in" data-aos-delay="750">
            Same-Day In-Home Grooming
          </h3>
          <h4 class="header-promo__subtitle header-promo__title--black text-center">
            Safe & convenient. Highly skilled & certified pet stylists come to you
          </h4>
          <div class="row header-promo__da-icons">
            <div class="col-xs-6 da-icons"> 
              <a data-aos="zoom-in" target="_blank" href="https://itunes.apple.com/us/app/groomit/id1240314505?mt=8">
                <img src="../images/app-store-btn-rounded.png" alt="Available on the App Store" />
              </a> 
            </div>
            <div class="col-xs-6 da-icons"> 
              <a data-aos="zoom-in" target="_blank" href="https://play.google.com/store/apps/details?id=com.groomit.inc.groomit&hl=en">
                <img src="../images/google-play-btn-rounded.png" alt="Available on Google Play Store" />
              </a> 
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- /header-banner -->

  <!--<section class="promo-banner">
    <a href="/pricing">
      <picture>
        <source media="(max-width:767px)" srcset="../images/home5-base_promo_v6.jpg">
        <img class="img-responsive" src="../images/home5-base_v6--bottom.jpg" alt="Affordable dog grooming">
      </picture>
    </a>
  </section>-->

  <!-- We founder -->
  <section class="we-founder">
    <div class="container">
      <div class="row row-flex align-items-center">
        <div class="col-lg-7 col-lg-offset-1 col-md-8 col-sm-8 col-xs-12 col-flex">
          <h2 class="we-founder__title">We want our customers<br> to be our owners</h2>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12 col-flex we-founder__cta-col">
          <a href="https://wefunder.com/groomit/" class="we-founder__btn" target="_blank">Learn More <span class="sr-only">Opens in a new tab</span></a>
        </div>
      </div>
    </div>
  </section>

  <section id="benefits">
    <div class="container">

        @if($errors->any())
            <div class="container-fluid">
                <div class="col-lg-12 col-sm-12 col-md-12 alert alert-info">{{$errors->first()}}</div>
            </div>
        @endif

    <div class="row" data-aos="fade-up">
        <div class="col-xs-12 text-center">
          <h2>Benefits Of In-Home Grooming</h2>
        </div>
      </div>

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
            <div class="col-xs-12 benefit-item" data-aos="fade-up">
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
            <div class="col-xs-12 benefit-item" data-aos="fade-up">
              <div class="media">
                <div class="media-left"> 
                  <img class="media-object" src="../images/icon__benefits-paws.svg" width="57" height="auto" alt="Pet grooming insurance"> 
                </div>
                <div class="media-body">
                  <h3 class="media-heading">Premium Insurance</h3>
                  <p>Every booking made through our site or app is bonded and covered by premium pet insurance. Safety is our priority.</p>
                </div>
              </div>
              <!-- /media -->
            </div>
            <!-- /col -->

            <div class="col-xs-12 benefit-item" data-aos="fade-up">
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
      
            <div class="row">
        <div class="col-xs-12 text-center"> <a onclick="click_schedule('/user')" target="_blank" class="btn btn-default
        btn-rounded btn-red btn-big" title="Schedule Appointment" data-aos="zoom-in">Schedule Appointment</a> </div>

        <script>
            function click_schedule(url) {
                if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                    window.location.href = 'http://onelink.to/groom';
                } else {
                    window.location.href = url;
                }
            }
        </script>
      </div>


    </div>
  </section>
  <!-- #features ends -->

  <section id="howitworks"> <!-- #mobile-app starts -->
    <div class="container">
      <div class="row">
        <div class="col-md-10 col-md-offset-1">
          <div class="row">
            <div class="col-md-5 col-md-offset-1 col-sm-6 col-sm-offset-1">
              <div id="carousel-left">
                <div class="item" id="cl-1">
                  <h4>Pick it!</h4>
                  <p>Choose one of our three grooming packages for your dog or cat. Personalize their experience by selecting some of our organic shampoos and optional add-ons.</p>
                </div>
                <!-- 1 -->
                <div class="item" id="cl-2">
                  <h4>Book it!</h4>
                  <p>Schedule an in-home pet grooming appointment for when it is most convenient for you. We even offer same day appointments.</p>
                </div>
                <!-- 2 -->
                <div class="item" id="cl-3">
                  <h4>Groom it!</h4>
                  <p>Now you can simply sit back and relax! Your pet groomer is on their way and your pet will soon be looking and feeling fabulous.</p>
                  <a href="/pricing" class="btn btn-default red-outline" target="_self" >Learn More</a>
                </div>
                <!-- 3 -->
              </div>
            </div>
            <div class="col-md-5 col-md-offset-1 col-sm-5" id="phone-container">
              <div class="owl-carousel" id="carousel-right">
                <div class="item" id="cr-1"> <img class="img-responsive" src="../images/home-slide1.jpg" width="215" height="464" alt="Pet grooming app"> </div>
                <!-- 1 -->
                <div class="item" id="cr-2"> <img class="img-responsive" src="../images/home-slide2.jpg" width="215" height="464" alt="Pet grooming app"> </div>
                <!-- 2 -->
                <div class="item" id="cr-3"> <img class="img-responsive" src="../images/home-slide3.jpg" width="215" height="464" alt="Pet grooming app"> </div>
                <!-- 3 -->
              </div>
              <div id="phone"></div>
            </div>
            <!-- /col-6 -->
          </div>
          <!-- /row -->
        </div>
        <!-- /col-8 -->
      </div>
		  <!-- /row -->
    </div>
    <!-- /container -->
    <div id="customNav" class="owl-nav"></div>
    <div id="customDots" class="owl-dots"></div>
  </section>
  <!-- #benefits ends -->

  <section id="downloadapp"> <!-- #downloadapp starts -->
    <div class="container">
      <div class="row" data-aos="fade-up">
        <div class="col-lg-12 text-center">
          <h2>Download App</h2>
        </div>
      </div>
      <div class="row">
        <div class="col-md-3 col-md-offset-3 col-sm-4 col-sm-offset-2 text-right da-icons"> 
          <a data-aos="zoom-in" target="_blank" href="https://itunes.apple.com/us/app/groomit/id1240314505?mt=8">
            <img src="../images/app-store-btn-rounded.png" alt="App store pet app" />
          </a>
        </div>
        <div class="col-md-3 col-sm-4 da-icons">
          <a data-aos="zoom-in" target="_blank" href="https://play.google.com/store/apps/details?id=com.groomit.inc.groomit&hl=en">
            <img src="../images/google-play-btn-rounded.png" alt="Google play pet app" />
          </a> 
        </div>
      </div>
      <div class="row">
        <div class="col-md-12"> <img class="img-responsive hidden-xs" src="../images/download-your-app.png" alt="" /> </div>
      </div>
    </div>
  </section>
  <!--<section id="videoApp">
    <div class="container">
      <div class="row">
        <div class="col-md-12 text-center">
          <iframe class="aos-init aos-animate" data-aos="zoom-in" width="672" height="378" src="https://www.youtube.com/embed/y1l2nq2kL-0?rel=0&amp;showinfo=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
      </div>
    </div>
  </section>-->
  <!-- #videoApp ends -->

  <section id="maps"> <!-- #maps starts -->
    <div class="container">
      <div class="row" data-aos="fade-up">
        <div class="col-xs-12 text-center">
          <h2>Our Service Area</h2>
        </div>
      </div>
      <!-- 5 areas -->
      <!-- <div class="row" data-aos="fade-up">
        <div class="col-lg-5ths col-sm-5ths col-md-5ths text-center">
          <div class="cont-circle-map">
            <div class="click-moadl-map" data-toggle="modal" data-target="#ny-map-modal"></div>
            <img class="img-responsive img-map-home" src="../images/map-new-york.jpg" alt="New York City" /> </div>
          <h4>New York City</h4>
        </div>
        <div class="col-lg-5ths col-sm-5ths col-md-5ths text-center">
          <div class="cont-circle-map">
            <div class="click-moadl-map" data-toggle="modal" data-target="#westchester-map-modal"></div>
            <img class="img-responsive img-map-home" src="../images/map-westchester.jpg" alt="Westchester" /> </div>
          <h4>Westchester</h4>
        </div>
        <div class="col-lg-5ths col-sm-5ths col-md-5ths text-center">
          <div class="cont-circle-map">
            <div class="click-moadl-map" data-toggle="modal" data-target="#north-jersey-map-modal"></div>
            <img class="img-responsive img-map-home" src="../images/map-north-jersey.jpg" alt="North Jersey" /> </div>
          <h4>North Jersey</h4>
        </div>
        <div class="col-lg-5ths col-sm-5ths col-md-5ths text-center">
          <div class="cont-circle-map">
            <div class="click-moadl-map" data-toggle="modal" data-target="#Connecticut-map-modal"></div>
            <img class="img-responsive img-map-home" src="../images/map-conneticut.jpg" alt="best dog groomers" /> </div>
          <h4>Connecticut</h4>
        </div>
        <div class="col-lg-5ths col-sm-5ths col-md-5ths text-center">
          <div class="cont-circle-map">
            <div class="click-moadl-map" data-toggle="modal" data-target="#sandiego-map-modal"></div>
            <img class="img-responsive img-map-home" src="../images/map-san-diego.jpg" alt="San Diego" /> </div>
          <h4>San Diego</h4>
        </div>
      </div> -->
      <div class="row" data-aos="fade-up" id="service-areas">
        <div class="col-xs-12">
          <div class="row" data-aos="fade-up">
            <div class="col-md-2 col-sm-4 col-sm-offset-0 col-xs-10 col-xs-offset-1 text-center">
              <div class="cont-circle-map">
                <div class="click-moadl-map" data-toggle="modal" data-target="#ny-map-modal"></div>
                <img class="img-responsive img-map-home" src="../images/map-new-york.jpg" alt="Dog grooming NYC" /> </div>
              <h4>New York City</h4>
            </div>
            <div class="col-md-2 col-sm-4 col-sm-offset-0 col-xs-10 col-xs-offset-1 text-center">
              <div class="cont-circle-map">
                <div class="click-moadl-map" data-toggle="modal" data-target="#westchester-map-modal"></div>
                <img class="img-responsive img-map-home" src="../images/map-westchester.jpg" alt="Dog grooming Westchester" /> </div>
              <h4>Westchester</h4>
            </div>
            <div class="col-md-2 col-sm-4 col-sm-offset-0 col-xs-10 col-xs-offset-1 text-center">
              <div class="cont-circle-map">
                <div class="click-moadl-map" data-toggle="modal" data-target="#north-jersey-map-modal"></div>
                <img class="img-responsive img-map-home" src="../images/map-north-jersey.jpg" alt="Dog grooming North Jersey" /> </div>
              <h4>North Jersey</h4>
            </div>
            <div class="sa__vertical-divider visible-sm"></div>
            <div class="col-md-2 col-md-offset-0 col-sm-4 col-sm-offset-0 col-xs-10 col-xs-offset-1 text-center">
              <div class="cont-circle-map">
                <div class="click-moadl-map" data-toggle="modal" data-target="#Connecticut-map-modal"></div>
                <img class="img-responsive img-map-home" src="../images/map-conneticut.jpg" alt="Dog grooming Connecticut" /> </div>
              <h4>Connecticut</h4>
            </div>
            <div class="col-md-2 col-sm-4 col-sm-offset-0 col-xs-10 col-xs-offset-1 text-center">
              <div class="cont-circle-map">
                <div class="click-moadl-map" data-toggle="modal" data-target="#Miami-map-modal"></div>
                <img class="img-responsive img-map-home" src="../images/map-miami.jpg" alt="Dog grooming Miami" /> </div>
              <h4>Miami</h4>
            </div>
            <div class="col-md-2 col-sm-4 col-sm-offset-0 col-xs-10 col-xs-offset-1 text-center">
              <div class="cont-circle-map">
                <div class="click-moadl-map" data-toggle="modal" data-target="#Long-Island-map-modal"></div>
                <img class="img-responsive img-map-home" src="../images/map-long-island.jpg" alt="Dog grooming Long Island" /> </div>
              <h4>Long Island</h4>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12 text-center"> <a onclick="click_schedule('/user')" target="_blank" data-aos="zoom-in" class="btn btn-default btn-rounded btn-red" title="Schedule Appointment">Schedule Appointment</a> </div>
      </div>
      <!-- /row -->
    </div>
    <!-- /container -->

    <!-- NY modal -->
    <div class="modal fade modal-map-home" tabindex="-1" role="dialog" id="ny-map-modal" aria-labelledby="Groomit in New York">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <button type="button" class="btn btn-default close-modal-map" data-dismiss="modal"><img src="images/close-modal.png" alt="dog hair cut nyc" /></button>
          <div class="row">
            <div class="container-fluid">
              <div class="col-lg-7 col-md-7 col-sm-7">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387190.2799062274!2d-74.25987281189558!3d40.697670065078334!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNueva+York%2C+EE.+UU.!5e0!3m2!1ses!2sar!4v1503088267792" width="100%" height="250" frameborder="0" style="border:0" ></iframe>
              </div>
              <div class="col-lg-5 col-md-5 col-sm-5">
                <h3>New York</h3>
                <div class="under-modal-title"></div>
                <ul>
                  <li><span>-</span>Manhattan</li>
                  <li><span>-</span>Brooklyn</li>
                  <li><span>-</span>Bronx</li>
                  <li><span>-</span>Queens</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- NY modal -->

    <!-- westchester modal -->
    <div class="modal fade modal-map-home" tabindex="-1" role="dialog" id="westchester-map-modal" aria-labelledby="Groomit in Westchester">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <button type="button" class="btn btn-default close-modal-map" data-dismiss="modal"><img src="images/close-modal.png" alt="dog hair cut nyc" /></button>
          <div class="row">
            <div class="container-fluid">
              <div class="col-lg-7 col-md-7 col-sm-7">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d384729.6973015421!2d-74.01318816103392!3d41.119225938925496!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c2c96a9d6b59af%3A0x370ed86222bddb89!2sCondado+de+Westchester%2C+Nueva+York%2C+EE.+UU.!5e0!3m2!1ses!2sar!4v1503088459327" width="100%" height="250" frameborder="0" style="border:0" ></iframe>
              </div>
              <div class="col-lg-5 col-md-5 col-sm-5">
                <h3>Westchester</h3>
                <div class="under-modal-title"></div>
                <ul>
                  <li><span>-</span>Westchester County</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- westchester modal -->

    <!-- north-jersey modal -->
    <div class="modal fade modal-map-home" tabindex="-1" role="dialog" id="north-jersey-map-modal" aria-labelledby="Groomit in North Jersey">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <button type="button" class="btn btn-default close-modal-map" data-dismiss="modal"><img src="images/close-modal.png" alt="dog hair cut nyc" /></button>
          <div class="row">
            <div class="container-fluid">
              <div class="col-lg-7 col-md-7 col-sm-7">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d96771.87096573914!2d-74.13877133287494!3d40.71535345111854!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c250d225bfafdd%3A0x249f013a2cd25d9!2sJersey+City%2C+Nueva+Jersey%2C+EE.+UU.!5e0!3m2!1ses!2sar!4v1503088946716" width="100%" height="250" frameborder="0" style="border:0" ></iframe>
              </div>
              <div class="col-lg-5 col-md-5 col-sm-5">
                <h3>North Jersey</h3>
                <div class="under-modal-title"></div>
                <ul>
                  <li><span>-</span>Bergen County</li>
                  <li><span>-</span>Hudson County</li>
                  <li><span>-</span>Essex County</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- north-jersey modal -->

    <!-- Connecticut modal -->
    <div class="modal fade modal-map-home" tabindex="-1" role="dialog" id="Connecticut-map-modal" aria-labelledby="Groomit in Connecticut">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <button type="button" class="btn btn-default close-modal-map" data-dismiss="modal"><img src="images/close-modal.png" alt="dog hair cut nyc" /></button>
          <div class="row">
            <div class="container-fluid">
              <div class="col-lg-7 col-md-7 col-sm-7">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d764973.3777504964!2d-73.3179682205728!3d41.50043342440306!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89e65311f21151a5%3A0xae9a6d5b056170e5!2sConnecticut%2C+EE.+UU.!5e0!3m2!1ses!2sar!4v1503089131791" width="100%" height="250" frameborder="0" style="border:0" ></iframe>
              </div>
              <div class="col-lg-5 col-md-5 col-sm-5">
                <h3>Connecticut</h3>
                <div class="under-modal-title"></div>
                <ul>
                  <li><span>-</span>Fairfield County</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Connecticut modal -->

    <!-- sandiego modal -->
    <div class="modal fade modal-map-home" tabindex="-1" role="dialog" id="sandiego-map-modal" aria-labelledby="Groomit in San Diego">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <button type="button" class="btn btn-default close-modal-map" data-dismiss="modal"><img src="images/close-modal.png" alt="dog hair cut nyc" /></button>
          <div class="row">
            <div class="container-fluid">
              <div class="col-lg-7 col-md-7 col-sm-7">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d429157.5467629685!2d-117.38916751208967!3d32.82424042671992!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80d9530fad921e4b%3A0xd3a21fdfd15df79!2sSan+Diego%2C+California%2C+EE.+UU.!5e0!3m2!1ses!2sar!4v1513258988568" width="100%" height="250" frameborder="0" style="border:0"></iframe>
              </div>
              <div class="col-lg-5 col-md-5 col-sm-5">
                <h3>San Diego</h3>
                <div class="under-modal-title"></div>
                <ul>
                  <li><span>-</span>San Diego</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Connecticut modal -->

    <!-- Miami modal -->
    <div class="modal fade modal-map-home" tabindex="-1" role="dialog" id="Miami-map-modal" aria-labelledby="Groomit in Miami">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <button type="button" class="btn btn-default close-modal-map" data-dismiss="modal"><img src="images/close-modal.png" alt="dog hair cut Miami" /></button>
          <div class="row">
            <div class="container-fluid">
              <div class="col-lg-7 col-md-7 col-sm-7">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d229929.1760314769!2d-80.36954411689972!3d25.78234042826896!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x88d9b0a20ec8c111%3A0xff96f271ddad4f65!2sMiami%2C%20FL%2C%20USA!5e0!3m2!1sen!2sar!4v1594403004065!5m2!1sen!2sar" width="100%" height="250" frameborder="0" style="border:0;" aria-hidden="false" tabindex="0"></iframe>                </div>
              <div class="col-lg-5 col-md-5 col-sm-5">
                <h3>Miami</h3>
                <div class="under-modal-title"></div>
                <!--<ul>
                  <li><span>-</span>Manhattan</li>
                  <li><span>-</span>Brooklyn</li>
                  <li><span>-</span>Bronx</li>
                  <li><span>-</span>Queens</li>
                </ul>-->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Miami modal -->

    <!-- Long Island modal -->
    <div class="modal fade modal-map-home" tabindex="-1" role="dialog" id="Long-Island-map-modal" aria-labelledby="Groomit in Long Island">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <button type="button" class="btn btn-default close-modal-map" data-dismiss="modal"><img src="images/close-modal.png" alt="dog hair cut Long Island" /></button>
          <div class="row">
            <div class="container-fluid">
              <div class="col-lg-7 col-md-7 col-sm-7">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d772598.3268846325!2d-73.50950475485169!3d40.85075338557872!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89e84454e1eea5cb%3A0x1df7f96186940d18!2sLong%20Island!5e0!3m2!1sen!2sar!4v1594403337553!5m2!1sen!2sar" width="100%" height="250" frameborder="0" style="border:0;" aria-hidden="false" tabindex="0"></iframe>                
              </div>
              <div class="col-lg-5 col-md-5 col-sm-5">
                <h3>Long Island</h3>
                <div class="under-modal-title"></div>
                <!--<ul>
                  <li><span>-</span>Manhattan</li>
                  <li><span>-</span>Brooklyn</li>
                  <li><span>-</span>Bronx</li>
                  <li><span>-</span>Queens</li>
                </ul>-->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Long Island modal -->

  </section>

  <section class="testimonials">
    <div class="container">
      <div class="row" data-aos="fade-up">
        <div class="col-lg-12 text-center">
          <h2>Testimonials</h2>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12">
          <div class="owl-carousel testimonials__carousel">
            <!-- Testimonial item Yelp -->
            <div class="testimonials__carousel-item">
              <div class="testimonials__info-container">
                <div class="testimonials__source">
                  <a href="https://www.yelp.com/biz/groomit-new-york-4" target="_blank">
                    <img src="../images/logo_yelp.jpg" alt="Read Groomit app reviews on Yelp" />
                  </a>
                </div>
                <div class="testimonials__info">
                  <p class="testimonials__text">
                      <strong>Eric H.</strong>
                  </p>
                  <p class="testimonials__text testimonials__text--gray">
                      New York, NY
                  </p>
                  <div class="testimonials__rating">
                    <img src="../images/icon_star.svg" alt="5 stars rating" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                  </div>
                </div>
              </div>
              <p class="testimonials__text text-center">
                <em>
                  "10/10 One of the best dog grooming experiences ever. Took about 10 minutes from downloading the app to scheduling an appointment the same day! The Groomer even arrived 10 minutes early. The groomer did a really good job grooming and we will definitely be using them again!"
                </em>
              </p>
            </div>
            <!-- Testimonial item Google -->
            <div class="testimonials__carousel-item">
              <div class="testimonials__info-container">
                <div class="testimonials__source">
                  <a href="https://www.google.com/maps/place/Groomit+-+In+Home+Dog+%2F+Cat+Grooming/@40.1258205,-75.9512409,7z/data=!3m1!4b1!4m5!3m4!1s0x89c259618927841f:0x61a3bbaedbea24c6!8m2!3d41.2732914!4d-73.0298614" target="_blank">
                    <img src="../images/logo_google.jpg" alt="Read Groomit app reviews on Google" />
                  </a>
                </div>
                <div class="testimonials__info">
                  <p class="testimonials__text">
                      <strong>Colin M.</strong>
                  </p>
                  <p class="testimonials__text testimonials__text--gray">
                    Manhattan, NYC
                  </p>
                  <div class="testimonials__rating">
                    <img src="../images/icon_star.svg" alt="5 stars rating on Google" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                  </div>
                </div>
              </div>
              <p class="testimonials__text text-center">
                <em>
                  "I would like to first start off by saying that I am truly pleased with my first experience booking an in-house grooming appointment. I had a wonderful groomer and definitely want her to work with my dog moving forward. She started off with allowing Mugsy to sniff her, [...] She spoke to him and gave him positive feedback and just felt genuine. She really cares about pets and you see it with her care for him, her pet stories. Definitely recommend Groomit."
                </em>
              </p>
            </div>
            <!-- Testimonial item Facebook -->
            <div class="testimonials__carousel-item">
              <div class="testimonials__info-container">
                <div class="testimonials__source">
                  <a href="https://www.facebook.com/pg/groomitapp/reviews/?ref=page_internal" target="_blank">
                    <img src="../images/logo_facebook.jpg" alt="Read Groomit app reviews on Facebook" />
                  </a>
                </div>
                <div class="testimonials__info">
                  <p class="testimonials__text">
                      <strong>Christopher T.</strong>
                  </p>
                  <p class="testimonials__text testimonials__text--gray">
                    New York
                  </p>
                  <div class="testimonials__rating">
                    <img src="../images/icon_star.svg" alt="5 stars rating on Facebook" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                  </div>
                </div>
              </div>
              <p class="testimonials__text text-center">
                <em>
                  "Our groomer was excellent. Our dog, Frida, is quite anxious but she was able to bathe and groom her and Frida is so much happier now - and so are we! The whole process took 75 minutes. Isabella was extremely gentle and efficient. Highly recommended!"
                </em>
              </p>
            </div>
            <!-- Testimonial item Yelp -->
            <div class="testimonials__carousel-item">
              <div class="testimonials__info-container">
                <div class="testimonials__source">
                  <a href="https://www.yelp.com/biz/groomit-new-york-4" target="_blank">
                    <img src="../images/logo_yelp.jpg" alt="Read Groomit app reviews on Yelp" />
                  </a>
                </div>
                <div class="testimonials__info">
                  <p class="testimonials__text">
                      <strong>Vanessa T.</strong>
                  </p>
                  <p class="testimonials__text testimonials__text--gray">
                      Queens, NY
                  </p>
                  <div class="testimonials__rating">
                    <img src="../images/icon_star.svg" alt="5 stars rating on Yelp" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                  </div>
                </div>
              </div>
              <p class="testimonials__text text-center">
                <em>
                  "Our groomer was amazing with my 14yo Brussels Griffon. He took every precaution and was super sweet and patient with her. This was my first time using this service and I couldn't be more pleased. It was super convenient and I will definitely  book again for her next groom."
                </em>
              </p>
            </div>
            <!-- Testimonial item Google -->
            <div class="testimonials__carousel-item">
              <div class="testimonials__info-container">
                <div class="testimonials__source">
                  <a href="https://www.google.com/maps/place/Groomit+-+In+Home+Dog+%2F+Cat+Grooming/@40.1258205,-75.9512409,7z/data=!3m1!4b1!4m5!3m4!1s0x89c259618927841f:0x61a3bbaedbea24c6!8m2!3d41.2732914!4d-73.0298614" target="_blank">
                    <img src="../images/logo_google.jpg" alt="Read Groomit app reviews on Google" />
                  </a>
                </div>
                <div class="testimonials__info">
                  <p class="testimonials__text">
                      <strong>Mary M.</strong>
                  </p>
                  <p class="testimonials__text testimonials__text--gray">
                    Brooklyn, NYC
                  </p>
                  <div class="testimonials__rating">
                    <img src="../images/icon_star.svg" alt="5 stars rating on Google" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                  </div>
                </div>
              </div>
              <p class="testimonials__text text-center">
                <em>
                  "I have used Groomit for a few years now, first for my yorkie and most recently for my long haired cat. They are on Time with excellent results. I have zero complaints and my dog actually runs to the groomer. I would recommend to anyone."
                </em>
              </p>
            </div>
            <!-- Testimonial item Facebook -->
            <div class="testimonials__carousel-item">
              <div class="testimonials__info-container">
                <div class="testimonials__source">
                  <a href="https://www.facebook.com/pg/groomitapp/reviews/?ref=page_internal" target="_blank">
                    <img src="../images/logo_facebook.jpg" alt="Read Groomit app reviews on Facebook" />
                  </a>
                </div>
                <div class="testimonials__info">
                  <p class="testimonials__text">
                      <strong>Marie M.</strong>
                  </p>
                  <p class="testimonials__text testimonials__text--gray">
                    New York
                  </p>
                  <div class="testimonials__rating">
                    <img src="../images/icon_star.svg" alt="5 stars rating on Facebook" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                  </div>
                </div>
              </div>
              <p class="testimonials__text text-center">
                <em>
                  "I thought Groomit was excellent from start to finish. The site was very responsive and found a groomer for me quickly. They kept me notified of the time of the visit and the ETA for the groomer's arrival.<br>
                  Our groomer was excellent! She was easy to work with and listened to what I wanted. She was great with my cat, Sylvester, who liked her very much! He is a long haired cat whose fur became very matted. Sylvester was shaved and at the same time made him look very handsome! I highly recommend Groomit!"
                </em>
              </p>
            </div>
            <!-- Testimonial item Yelp -->
            <div class="testimonials__carousel-item">
              <div class="testimonials__info-container">
                <div class="testimonials__source">
                  <a href="https://www.yelp.com/biz/groomit-new-york-4" target="_blank">
                    <img src="../images/logo_yelp.jpg" alt="Read Groomit app reviews on Yelp" />
                  </a>
                </div>
                <div class="testimonials__info">
                  <p class="testimonials__text">
                      <strong>Vanessa T.</strong>
                  </p>
                  <p class="testimonials__text testimonials__text--gray">
                      Queens, NY
                  </p>
                  <div class="testimonials__rating">
                    <img src="../images/icon_star.svg" alt="5 stars rating on Yelp" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                  </div>
                </div>
              </div>
              <p class="testimonials__text text-center">
                <em>
                  "We love Groomit. We've been using their service for over a year and we look forward to seeing them every month. They use the best groomers and products. If you love your pet as much as we do then I encourage you to give them a try. I've had them come to my home in both NYC & NJ."
                </em>
              </p>
            </div>
            <!-- Testimonial item Google -->
            <div class="testimonials__carousel-item">
              <div class="testimonials__info-container">
                <div class="testimonials__source">
                  <a href="https://www.google.com/maps/place/Groomit+-+In+Home+Dog+%2F+Cat+Grooming/@40.1258205,-75.9512409,7z/data=!3m1!4b1!4m5!3m4!1s0x89c259618927841f:0x61a3bbaedbea24c6!8m2!3d41.2732914!4d-73.0298614" target="_blank">
                    <img src="../images/logo_google.jpg" alt="Read Groomit app reviews on Google" />
                  </a>
                </div>
                <div class="testimonials__info">
                  <p class="testimonials__text">
                      <strong>Camesia F.</strong>
                  </p>
                  <p class="testimonials__text testimonials__text--gray">
                    Manhattan, NYC
                  </p>
                  <div class="testimonials__rating">
                    <img src="../images/icon_star.svg" alt="5 stars rating on Google" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                  </div>
                </div>
              </div>
              <p class="testimonials__text text-center">
                <em>
                  "I've always taken my dog to the groomer, but since COVID I wanted to try in home grooming for the safety and convinience. From the first day I downloaded the Groomit app to the day our groomer arrived — the use experience and customer service was excellent. Our groomer was professional and immaculate. My little Bichon Frise was comfortable, calm and throughout the grooming. I'm hooked now. Looking forward to our next appointment and hopefully get Anita to do our grooming again. Thank you Groomit!!for my long haired cat. They are on Time with excellent results. I have zero complaints and my dog actually runs to the groomer. I would recommend to anyone."
                </em>
              </p>
            </div>
            <!-- Testimonial item Facebook -->
            <div class="testimonials__carousel-item">
              <div class="testimonials__info-container">
                <div class="testimonials__source">
                  <a href="https://www.facebook.com/pg/groomitapp/reviews/?ref=page_internal" target="_blank">
                    <img src="../images/logo_facebook.jpg" alt="Read Groomit app reviews on Facebook" />
                  </a>
                </div>
                <div class="testimonials__info">
                  <p class="testimonials__text">
                      <strong>Anthony G.</strong>
                  </p>
                  <p class="testimonials__text testimonials__text--gray">
                    New York
                  </p>
                  <div class="testimonials__rating">
                    <img src="../images/icon_star.svg" alt="5 stars rating on Facebook" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                    <img src="../images/icon_star.svg" alt="" />
                  </div>
                </div>
              </div>
              <p class="testimonials__text text-center">
                <em>
                  "Searched a number of various places to find a business that offered in home services for pets and found Groomit. They are definitely not cheap, but for a 1x a month need are certainly worth it. The groomer they sent was awesome, done in 30 minutes and very attentive and all in all I found a lot of peace in mind in this experience as well as a new business to continue having my pet groomed. Will be a long time customer."
                </em>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

</main>
<!-- /home-main -->


<!-- MODAL COVID -->
<!--<div class="modal fade co-alert" tabindex="-1" role="dialog" id="covid-alert" aria-labelledby="#co-alert__title">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <h1 class="co-alert__title text-center" id="co-alert__title">Latest Update</h1>-->
        <!--<p class="co-alert__text">In keeping the broad health and safety recommendations in light of COVID-19, Groomit has suspended all in-home grooming until further notice.</p>-->
        <!--<p class="co-alert__text text-center"><strong>Accepting bookings now in NY, NJ & PA.</strong></p>
        <div class="text-center">
          <a href="/blog/2020/04/30/from-home-the-latest-updates-on-groomits-ongoing-response-to-coronavirus-covid-19/" class="btn btn-default btn-rounded btn-black btn-big co-alert--btn" target="_blank">Learn More</a>
        </div>
        <br><br>
      </div>
    </div>
  </div>
</div>-->


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

  <script>
    function checkForm(form) // Submit button clicked
    {
        form.submitBtn.disabled = true;
        form.submitBtn.value = "Please wait...";
        form.submitBtn.style = "background-color:#464646";

        return true;
    }

    var onload_func = window.onload;
    window.onload = function() {
        $(function() {
            $('a[href*=#]:not([href=#])').click(function() {
                if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
                    var target = $(this.hash);
                    target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
                    if (target.length) {
                        $('html,body').animate({
                            scrollTop: target.offset().top - $('#header').height()
                        }, 500);
                        return false;
                    }
                }
            });
        });

        //initCovidAlert();
        initTestimonials();
    }

    function switchToDog(event) {
      event.preventDefault;
      $("#pets .col-xs-6:last-child .cont-select-pet").removeClass("selected");
      $("#pets .col-xs-6:first-child .cont-select-pet").addClass("selected");
      $(".cat").addClass("hidden");
      $(".dog").removeClass("hidden");
    }
    function switchToCat(e) {
      e.preventDefault;
      $("#pets .col-xs-6:first-child .cont-select-pet").removeClass("selected");
      $("#pets .col-xs-6:last-child .cont-select-pet").addClass("selected");
      $(".dog").addClass("hidden");
      $(".cat").removeClass("hidden");
    }

    /*function initCovidAlert() {
      $("#covid-alert").modal('show');
    }*/

    function initTestimonials() {
      $('.testimonials__carousel').owlCarousel({
          loop: true,
          nav: false,
          dots: true,
          autoplay: true,
          autoplayHoverPause: true,
          responsive:{
              0:{
                  items:1,
                  margin: 30
              },
              767:{
                  items:2,
                  margin: 60
              },
              991:{
                  items:3,
                  margin: 30
              },
              1200:{
                  items:3,
                  margin: 60
              }
          }
      })
    }

  </script>
@stop
