@extends('includes.default')

@section('content')
<div id="prices-main">
    <div class="container">
        <div class="row" id="shampoo-services">
            <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-offset-0 col-sm-12">
                <section id="service-packs">
                    <!-- Pets -->
                    <div class="row">
                        <div class="col-md-4 col-md-offset-4 col-sm-5 col-xs-10 col-xs-offset-1 text-center">
                        <div class="row" id="pets">
                            <div class="col-xs-6">
                            <a onClick="switchToDog(event);">
                                <div class="cont-select-pet selected">
                                <div class="cont-select-pet-img"><img class="img-responsive" src="/images/dog-icon.svg" alt="Select Dog">
                                </div>
                                <p>Dog</p>
                                </div>
                            </a>
                            <!-- cont-select-pet -->
                            </div>
                            <!-- /col-6 -->
                            <div class="col-xs-6">
                            <a onClick="switchToCat(event);">
                                <div class="cont-select-pet">
                                <div class="cont-select-pet-img select-cat"><img class="img-responsive" src="/images/cat-icon.svg" alt="Select Cat">
                                </div>
                                <p>Cat</p>
                                </div>
                            </a>
                            <!-- cont-select-pet -->
                            </div>
                                        <!-- /col-6 -->
                        </div>
                        <!-- /row -->
                        </div>
                        <!-- /col-6 -->
                            </div>
                    <!-- /row -->
                    
                    <!-- Title -->
                    <div class="row">
                        <div class="col-xs-12">
                        <h4 class="modal-title text-center">Our Services</h4>
                        <p class="text-center">Choose from our packages below!</p>
                        </div>
                    </div>

                    <!-- Packages -->
                    <div class="row services-carousel">
                        <!-- Gold -->
                        <div class="col-sm-4 select-service" id="service-gold">
                        <div class="panel panel-default">
                            <div class="panel-heading text-center">
                            <h3 class="gold">GOLD</h3>
                            <!--<em>Starting at</em><br>
                            <span class="price dog">$109</span>
                            <span class="price cat hidden">$150</span>-->
                            </div>
                            <div class="panel-body">
                            <div class="row service-items">
                                <div class="col-xs-12 text-center">
                                <ul class="text-left dog">
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Bath</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Dry Brush Out</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Conditioner</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Ear Cleaning</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Nail Trim</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Blow Dry</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Cologne</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Sanitary Trim</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Haircut</span> </p>
                                </ul>

                                <ul class="text-left cat hidden">
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Bath</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Blow Dry</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Ear Cleaning</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Dry Brush Out</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Nail Trim</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Dematting</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Paw Pad Trim</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Haircut (Lion Cut)</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Sanitary Trim</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Deshedding</span> </p>
                                    </li>
                                </ul>
                                </div>
                                <div class="col-xs-12 text-center">
                                                        <ul class="package-specs text-center">
                                                            <li>
                                                                Same Day Optional
                                                            </li>
                                                            <li>
                                                                Free cancellation - 2h prior
                                                            </li>
                                                            <li>
                                                                Favorite Groomer
                                                            </li>
                                                            <li>
                                                                Pay After Service
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <!-- /col -->
                            </div>
                            <!-- /service-items -->
                            </div>
                            <!-- /panel-body -->
                        </div>
                        <!-- /panel-default -->

                        </div>
                        <!-- Eco -->
                        <div class="col-sm-4 select-service" id="service-eco">
                        <div class="panel panel-default">
                            <div class="panel-heading text-center">
                            <h3 class="eco">ECO</h3>
                            <!--<em>Starting at</em><br>
                            <span class="price dog">$89</span>
                            <span class="price cat hidden">$125</span>-->

                            </div>
                            <div class="panel-body">
                            <div class="row service-items">
                                <div class="col-xs-12 text-center">
                                <ul class="text-left dog">
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Bath</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Dry Brush Out</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Conditioner</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Ear Cleaning</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Nail Trim</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Blow Dry</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Cologne</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Sanitary Trim</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Haircut</span> </p>
                                    </li>
                                </ul>

                                <ul class="text-left cat hidden">
                                <li>
                                    <p><i class="fas fa-check"></i><span>Bath</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Blow Dry</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Ear Cleaning</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Dry Brush Out</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Nail Trim</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Dematting</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Paw Pad Trim</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Haircut (Lion Cut)</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Sanitary Trim</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-times"></i><span>Deshedding</span> </p>
                                    </li>
                                </ul>
                                </div>
                                <div class="col-xs-12 text-center">
                                                        <ul class="package-specs text-center">
                                                            <li>
                                    Book 7 Days In Advance
                                                            </li>
                                                            <li>
                                    Non-Refundable Booking
                                                            </li>
                                                            <li>
                                                                No Favorite Groomer
                                                            </li>
                                                            <li>
                                                                Prepaid
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <!-- /col -->
                            </div>
                            <!-- /service-items -->
                            </div>
                            <!-- /panel-body -->
                        </div>
                        <!-- /panel-default -->

                        </div>
                        <!-- Silver -->
                        <div class="col-sm-4 select-service" id="service-silver">
                        <div class="panel panel-default">
                            <div class="panel-heading text-center">
                            <h3 class="silver">SILVER</h3>
                            <!--<em>Starting at</em><br>
                            <span class="price dog">$50</span>
                            <span class="price cat hidden">$90</span>-->
                            </div>
                            <div class="panel-body">
                            <div class="row service-items">
                                <div class="col-xs-12 text-center">
                                <ul class="text-left dog">
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Bath</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Dry Brush Out</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Conditioner</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Ear Cleaning</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Nail Trim</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Blow Dry</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-times"></i><span>Cologne</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-times"></i><span>Sanitary Trim</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-times"></i><span>Haircut</span> </p>
                                    </li>
                                </ul>

                                <ul class="text-left cat hidden">
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Bath</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Blow Dry</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Ear Cleaning</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Dry Brush Out</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-check"></i><span>Nail Trim</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-times"></i><span>Dematting</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-times"></i><span>Paw Pad Trim</span></p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-times"></i><span>Haircut (Lion Cut)</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-times"></i><span>Sanitary Trim</span> </p>
                                    </li>
                                    <li>
                                    <p><i class="fas fa-times"></i><span>Deshedding</span> </p>
                                    </li>
                                </ul>
                                </div>
                                <div class="col-xs-12 text-center">
                                                        <ul class="package-specs text-center">
                                                            <li>
                                                                Same Day Optional
                                                            </li>
                                                            <li>
                                                                Free cancellation - 2h prior
                                                            </li>
                                                            <li>
                                                                Favorite Groomer
                                                            </li>
                                                            <li>
                                                                Pay After Service
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <!-- /col -->
                            </div>
                            <!-- /service-items -->
                            </div>
                            <!-- /panel-body -->
                        </div>
                        <!-- /panel-default -->

                        </div>
                        <!-- /col-6 -->
                    </div>
                    <!-- /row -->

                </section>
                <section id="shampoo-addon">
                    <div class="row">
                        <div class="col-xs-12">
                        <h4 class="modal-title text-center">Choose a shampoo and any optional add-ons!</h4>
                        <p class="text-center">Personalize your pet's grooming experience by selecting a shampoo and some of our optional add-ons.</p>
                        </div>
                    </div>
                    <!-- /row -->
                    <div class="row" id="shampoos">
                        <div class="col-xs-12">
                        <div class="display-table dog">

                            <div class="table-cell left" id="pura-naturals"><img class="img-responsive" src="/images/basics.png" width="118" height="94" alt="Basics by NXT Generation Pet	&trade;" /></div>
                            <div class="table-cell right shampoo-col" id="shampoo-1">
                            <div class="media">
                                <div class="media-left"> <img class="media-object" src="/images/lavander.jpg" width="65" height="190" alt="Lavender &amp; Sweet Almond" /> </div>
                                <div class="media-body">
                                <h4 class="media-heading">Lavender &amp;<br />
                                    Sweet Almond</h4>
                                <p>Shampoo & Conditioner</p>
                                </div>
                            </div>
                            </div>
                            <!-- /table-cell -->
                            <div class="table-cell left shampoo-col" id="shampoo-2">
                            <div class="media">
                                <div class="media-left"> <img class="media-object" src="/images/teatree.jpg" width="65" height="190" alt="Tee Tree &amp; Aloe" /> </div>
                                <div class="media-body">
                                <h4 class="media-heading">Tee Tree <br />
                                    &amp; Aloe</h4>
                                <p>Shampoo & Conditioner</p>
                                </div>
                            </div>
                            </div>
                            <!-- /table-cell -->
                            <div class="table-cell right shampoo-col" id="shampoo-3">
                            <div class="media">
                                <div class="media-left"> <img class="media-object" src="/images/unscented.jpg" width="65" height="190" alt="Unscented" /> </div>
                                <div class="media-body">
                                <h4 class="media-heading">Unscented <br />&nbsp;</h4>
                                <p>Shampoo & Conditioner</p>
                                </div>
                            </div>
                            </div>
                            <!-- /table-cell -->
                            <!--<div class="table-cell shampoo-col" id="shampoo-4">
                            <div class="media">
                                <div class="media-left"> <img class="media-object" src="../images/Flea-Tick.png" width="45" height="132" alt="Flea &amp; Tick Natural" /> </div>
                                <div class="media-body">
                                <h4 class="media-heading">Flea & Tick <br />
                                    Natural</h4>
                                <p>Shampoo</p>
                                </div>
                            </div>
                            </div>-->
                            <!-- /table-cell -->
                        </div>
                        <!-- /tablerow -->

                        <div class="display-table cat hidden">

                            <div class="table-cell left"><img class="img-responsive" src="/images/basics.png" width="118" height="94" alt="Basics by NXT Generation Pet	&trade;" /></div>
                            <div class="table-cell right shampoo-col">
                            <div class="media">
                                <div class="media-left"> <img class="media-object" src="/images/cat-shampoo.jpg" width="65" height="190" alt="Waterless Foam Organic" /> </div>
                                <div class="media-body">
                                <h4 class="media-heading">WaterLess Foaming Organic</h4>
                                <p>Cat Shampoo</p>
                                </div>
                            </div>
                            </div>
                            <!-- /table-cell -->
                            <div class="table-cell left shampoo-col addons-list">
                            <ul class="addons">
                                <li>
                                <a role="button" data-toggle="collapse" data-target="#info-checkbox-cat" aria-expanded="false" aria-controls="info-checkbox-cat">
                                    <span class="addon-name">Flea/Tick</span><span class="pull-right tooltip-col"> <i class="fas fa-chevron-down"></i>
                                    </span>
                                </a>
                                <div class="collapse" id="info-checkbox-cat">
                                    <div class="addon-info-mobile">All natural formula treatment. Kills fleas, ticks, larvae and eggs by contact. No Pyrethrin or Permethrin.</div>
                                </div>
                                </li>
                            </ul>
                            </div>
                            <!-- /table-cell -->
                        </div>
                        <!-- /tablerow -->

                        </div>
                        <!-- /col-12 -->
                    </div>
                    <!-- /row -->
                </section>
                <section class="dog" id="addons-list">
                    <div class="row">
                        <div class="col-md-10 col-md-offset-1">
                        <div class="row">
                            <div class="col-sm-4">
                            <ul class="addons">
                                <li> <a role="button" data-toggle="collapse" data-target="#info-checkbox-1" aria-expanded="false" aria-controls="info-checkbox-1"><span class="addon-name">De-Matting *</span><span class="pull-right tooltip-col"> <i class="fas fa-chevron-down"></i>  </span></a>
                                <div class="collapse" id="info-checkbox-1">
                                    <div class="addon-info-mobile">Carefully removing mats and tangles multiple steps.</div>
                                </div>
                                <!-- /collapse --></li>
                                <li><a role="button" data-toggle="collapse" data-target="#info-checkbox-2" aria-expanded="false" aria-controls="info-checkbox-2"> <span class="addon-name">Flea/Tick</span><span class="pull-right tooltip-col"> <i class="fas fa-chevron-down"></i>  </span></a>
                                <div class="collapse" id="info-checkbox-2">
                                    <div class="addon-info-mobile">All natural formula treatment. Kills fleas, ticks, larvae and eggs by contact. No Pyrethrin or Permethrin.</div>
                                </div>
                                </li>
                            <li> <a role="button" data-toggle="collapse" data-target="#info-checkbox-3" aria-expanded="false" aria-controls="info-checkbox-3"> <span class="addon-name">Odor Removal</span><span class="pull-right tooltip-col"><i class="fas fa-chevron-down"></i>  </span></a>
                                <div class="collapse" id="info-checkbox-3">
                                    <div class="addon-info-mobile">Eliminates unpleasant odors incl skunk smell.</div>
                                </div>
                                <!-- /collapse --></li>
                            </ul>
                            </div>
                            <!-- /col-4 -->
                            <div class="col-sm-4">
                            <ul class="addons">
                            <li> <a role="button" data-toggle="collapse" data-target="#info-checkbox-4" aria-expanded="false" aria-controls="info-checkbox-4"><span class="addon-name">Very Berry Face</span><span class="pull-right tooltip-col"> <i class="fas fa-chevron-down"></i> </span></a>
                                <div class="collapse" id="info-checkbox-4">
                                    <div class="addon-info-mobile">Gentle formula contains oatmeal & blueberry to remove stains without irritating.</div>
                                </div>
                                <!-- /collapse --></li>
                                <li><a role="button" data-toggle="collapse" data-target="#info-checkbox-5" aria-expanded="false" aria-controls="info-checkbox-5">  <span class="addon-name">Teeth Brushing</span><span class="pull-right tooltip-col"><i class="fas fa-chevron-down"></i> </span> </a>
                                <div class="collapse" id="info-checkbox-5">
                                    <div class="addon-info-mobile">Mint breath freshening Paste, Tooth brush to keep.</div>
                                </div>
                                <!-- /collapse --></li>
                            <li><a role="button" data-toggle="collapse" data-target="#info-checkbox-6" aria-expanded="false" aria-controls="info-checkbox-6"><span class="addon-name">Anal Gland Expression</span><span class="pull-right tooltip-col"> <i class="fas fa-chevron-down"></i> </span></a>
                                <div class="collapse" id="info-checkbox-6">
                                    <div class="addon-info-mobile">Professional Anal Glands Expression.</div>
                                </div>
                                <!-- /collapse --></li>
                                <li><a role="button" data-toggle="collapse" data-target="#info-checkbox-7" aria-expanded="false" aria-controls="info-checkbox-7"><span class="addon-name">Organic Paw Rescue</span><span class="pull-right tooltip-col"> <i class="fas fa-chevron-down"></i> </span></a>
                                <div class="collapse" id="info-checkbox-7">
                                    <div class="addon-info-mobile">Heals & restores dry & cracked pads. Hydrates, soothes, promotes healing. Moisturizes with Shea Butter & Vitamin E. Safe if licked. Protects from heat, cold & moisture. Helps heal scratches, sores & wounds</div>
                                </div>
                                <!-- /collapse --></li>

                            </ul>
                            </div>
                            <!-- /col-4 -->
                            <div class="col-sm-4">
                            <ul class="addons">
                                <li><a role="button" data-toggle="collapse" data-target="#info-checkbox-8" aria-expanded="false" aria-controls="info-checkbox-8">  <span class="addon-name">Nail Polish</span><span class="pull-right tooltip-col"><i class="fas fa-chevron-down"></i>  </span></a>
                                <div class="collapse" id="info-checkbox-8">
                                    <div class="addon-info-mobile">Fast-drying, one-coat coverage. Trend-setting colors for a fashionable look. Premium-quality for lasting results.</div>
                                </div>
                                <!-- /collapse --></li>
                                    <li><a role="button" data-toggle="collapse" data-target="#info-checkbox-9" aria-expanded="false" aria-controls="info-checkbox-9">  <span class="addon-name">De-Shedding</span><span class="pull-right tooltip-col"><i class="fas fa-chevron-down"></i>  </span></a>
                                <div class="collapse" id="info-checkbox-9">
                                    <div class="addon-info-mobile">Removes undercoat & loose hair.</div>
                                </div>
                                <!-- /collapse --></li>
                                    <li><a role="button" data-toggle="collapse" data-target="#info-checkbox-10" aria-expanded="false" aria-controls="info-checkbox-10"> <span class="addon-name">Nail Grinding</span><span class="pull-right tooltip-col"> <i class="fas fa-chevron-down"></i>  </span></a>
                                <div class="collapse" id="info-checkbox-10">
                                    <div class="addon-info-mobile">Nail Grinding.</div>
                                </div>
                                <!-- /collapse --></li>

                            </ul>
                            </div>
                            <!-- /col-4 -->
                        </div>
                        <!-- /row -->
                        <p>* Only available in the Gold and Eco Packages.</p>
                        </div>
                        <!-- /col -->
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
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
</script>

@stop
