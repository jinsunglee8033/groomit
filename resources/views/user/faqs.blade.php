@extends('user.layout.default')

@section('content')
<link href="/desktop/css/login.css" rel="stylesheet">
<div class="main--no-margin" id="main">
  <!-- TOP BANNER -->
  <div class="display-table" id="top-banner">
    <div class="table-cell text-center" id="banner-title">
      <h1><img class="img-responsive" src="/desktop/img/groomit-title.png" width="325" height="56"
                         alt="Groomit"></h1>
      <h2>Frequently Asked Questions</h2>
    </div>
    <!-- /banner-title -->
    <a href="#schedule-appointment" class="visible-xs"><span></span></a></div>
  <!-- /top-banner -->

  <section id="faqs" class="log-out faqsAccordion"> <!-- #benefits starts -->

    <div class="container">
      <div class="row">
        <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
          <div class="panel-group" id="accordion">
            <!--<h1 class="h1title text-center">Frequently Asked Questions</h1>-->
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop" data-toggle="collapse" data-parent="#accordion" data-target="#collapse00"  aria-expanded="true" aria-controls="collapse00">How to schedule within Groomit App</a> </h4>
              </div>
              <div id="collapse00" class="panel-collapse collapse in">
                <div class="panel-body">
                  <div class="col-lg-12 text-center">
                    <iframe width="100%" height="315" src="https://www.youtube.com/embed/oD7kiYrmWr0?rel=0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    <h3 class="text-left mt-4 mb-1">Pick it!</h3>
                    <p class="text-left">Choose one of our three grooming packages for your dog or cat. Personalize their experience by selecting some of our organic shampoos and optional add-ons.</p>
                    <h3 class="text-left mb-1">Book it!</h3>
                    <p class="text-left">Schedule an in-home pet grooming appointment for when it is most convenient for you. We even offer same day appointments.</p>
                    <h3 class="text-left mb-1">Groom it!</h3>
                    <p class="text-left">Now you can simply sit back and relax! Your pet groomer is on their way and your pet will soon be looking and feeling fabulous.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop" data-toggle="collapse" data-parent="#accordion" data-target="#collapse01" aria-controls="collapse01">What vaccinations do we require?</a> </h4>
              </div>
              <div id="collapse01" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>We require all pets to be current on their vaccinations especially Rabies and Distemper vaccines. All necessary vaccinations must be administered at least 24 hours prior to your scheduled appointment.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse03"  aria-expanded="true" aria-controls="collapse03">What products do we use to bathe your pet?</a> </h4>
              </div>
              <div id="collapse03" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>We only use Organic, Natural and Hypoallergenic Shampoo and conditioner.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse04"  aria-expanded="true" aria-controls="collapse04">How long does it take to groom my pet?</a> </h4>
              </div>
              <div id="collapse04" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>It should be time based on their condition, size and temperament. The typical Time is 1.5-2 hours.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse05"  aria-expanded="true" aria-controls="collapse05">How quickly will my pet groomer arrives?</a> </h4>
              </div>
              <div id="collapse05" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>We make it as convenient as possible for your pet to get groomed asap. We will give you a time window of one hour within which the groomer will arrive. Appointments are available same day within hours. Pet grooming near me.</p>
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
                    <p>Your pet will be groomed in the comfort and convenience of your own home. You must have a bathtub or shower accessible for us to bathe all large breeds. For small breeds, we can use your bath or kitchen sink. We only ask that you please provide towels. A secure counter and or table is required for all pets to safely and properly be groomed.</p>
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
                    <p>To get a smooth and even haircut, the hair needs to be clean. Depending on your dog’s breed and style, the groomer may do a “rough cut.” After the rough cut, your dog is washed, fluff dried at which point the haircut is then completed. In other cases, we may wash and dry the dog completely before any length is taken off. Dog grooming near me.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse08b"  aria-expanded="true" aria-controls="collapse08b">How do you bathe cats?</a> </h4>
              </div>
              <div id="collapse08b" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>By standard we use organic dry shampoo, if you need a wet shampoo please discuss with groomer during appointment.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse08"  aria-expanded="true" aria-controls="collapse08">Can you groom my pet to the breed standard?</a> </h4>
              </div>
              <div id="collapse08" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>Yes. Our groomers are familiar with most of breed standards as well as being highly experienced with the more popular breeds. Many clients in NYC do not groom to breed standard due to the level of maintenance required.  Regardless, if you are looking for a specific style, providing pictures allows both you and your groomer to see what your options are.</p>
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
                    <p>Some clients prefer to work with the same groomer. We recommend this if your pet tends to be anxious and nervous. Many dogs like the familiarity of having the same groomer as it allows them to not only feel more comfortable and relaxed, but it also allows the groomer to establish a consistent relationship with the dog or cat.  If you would like to have the same groomer just "favorite" the groomer of your choice within the Groomit app or web.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse10"  aria-expanded="true" aria-controls="collapse10">Do you cut the nails?</a> </h4>
              </div>
              <div id="collapse10" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>yes, we are able to cut the nails on most pets. Dog grooming near me.</p>
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
                    <p>They are the scent sacs directly under your dog’s tail. Some dogs empty them naturally, some do not.  Per your request, please let your groomer know if you would like to have your dog's anal glands emptied (expressed) or if you would rather have your veterinarian do so.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse13"  aria-expanded="true" aria-controls="collapse13">How much training does your groomer receive?</a> </h4>
              </div>
              <div id="collapse13" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>All pet groomers receive safety training. we train groomers that are interested in becoming a Groomit partner.</p>
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
                    <p>It is at your discretion to tip the groomers. You can tip within our app, website or cash.</p>
                  </div>
                </div>
              </div>
            </div>
            <!-- /14 -->
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse15"  aria-expanded="true" aria-controls="collapse15">How can I earn $15 off my next booking?</a> </h4>
              </div>
              <div id="collapse15" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>Once your first grooming is complete you can refer a friend by sharing your unique code. Go to Refer A Friend in our app or web to find your unique code. We will then credit your account $15 to be applied to your next grooming.</p>
                  </div>
                </div>
              </div>
            </div>
            <!-- /15 -->
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse16"  aria-expanded="true" aria-controls="collapse16">Should dog's ear be plucked?</a> </h4>
              </div>
              <div id="collapse16" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>Our general recommendations are to pluck the ear hair on a regular basis to avoid buildup of dirt & ear wax and it's much easier on us and your pet to pluck a little ear hair then to wait and have a ton to try to remove at once. Removing a lot of ear hair can create irritation and so our policy is that we are happy to do it but if you choose not to, then it needs to be done at your veterinarian to avoid complications.</p>
                  </div>
                </div>
              </div>
            </div>
            <!-- /16 -->
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"> <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse12"  aria-expanded="true" aria-controls="collapse12">What if I don ‘t like the groom when it is finished?</a> </h4>
              </div>
              <div id="collapse12" class="panel-collapse collapse">
                <div class="panel-body">
                  <div class="col-lg-12">
                    <p>Please tell us! We want you to be 100% satisfied.  If there are slight changes you would like done, your groomer can usually make those adjustments right then and there. In other instances, if the changes you request require more time, we may ask that your groomer come back another day in which case we can start again. If there are two or more people responsible for the grooming result, it is important that they first agree on a style before giving the groomer further instructions.</p>
                  </div>
                </div>
              </div>
            </div>
            <!-- /12 -->
          </div>
          <!-- /panel-group -->
        </div>
        <!-- /col -->
      </div>
      <!-- /row -->
    </div>
  </section>
  <footer>
    <div class="container">
      <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
          <p class="text-uppercase">&copy; 2020 Groomit - Made with love in NYC.</p>
        </div>
        <!-- /col -->
      </div>
      <!-- /row -->
    </div>
    <!-- /container -->
  </footer>
</div>
@stop
