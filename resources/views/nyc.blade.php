@extends('includes.default')

@section('content')
<section id="GroomitBannerClean" class="bannerPricingNY" title="In-Home pet grooming"> <!-- #banner starts -->
</section> <!-- #banner ends -->

<section id="pricing" class=""> <!-- #benefits starts -->
    

	<div class="container container-content-clean container-content-clean-city container-content-clean-pricing">
        <div class="row row-same-height mb-3">
            <div class="col-lg-6 top col-sm-6 col-sm-push-6 col-sm-push-6 relative-pos same-h-col mb-5">
                <ul class="flex-container space-between mb-5">
                    <li class="flex-item">
                        <h2 class="mb-4">Pet grooming in<br>NEW York</h2>
                        <p class="mb-4">
                            Our services is now in the wonderful city of New york! We’ve already begun providing our exemplary in-home pet grooming service to the wonderful pet owners here and we are enjoying every meeting.
                        </p> 
                        <p class="mb-4">
                            One thing that we have noticed with our time being here, is that many of the breeds we’ve seen so far could benefit immensely from our in-home pet grooming magic!
                        </p>
                    </li>
                    <li class="flex-item">
                        <a onclick="click_schedule('/user')" class="btn btn-default btn-rounded btn-red mt-0 mb-0" title="Schedule Appointment">Schedule Appointment</a>
                    </li>
                </ul>
            </div>
			<div class="col-lg-6 top col-sm-6 col-lg-pull-6 col-sm-pull-6 relative-pos text-center same-h-col mb-5">
                <img class="img-responsive mb-0" src="/desktop/img/ny-pricing.jpg" alt="Dog & Cat Grooming in Miami">            
            </div>
        </div>

        
        

		<div class="row ">
			<div class="col-lg-12">
                <h2 class="text-center pricing-title">PRICING</h2>
            </div>
            <div class="col-lg-12">
            <div id="prices-main mb-0">
                    <section>
                        <div class="">
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
                                                                <div class="cont-select-pet-img"><img class="img-responsive"
                                                                        src="/desktop/img/dog-icon.svg" alt="Dog grooming prices">
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
                                                                <div class="cont-select-pet-img select-cat"><img class="img-responsive"
                                                                        src="/desktop/img/cat-icon.svg" alt="Cat grooming prices">
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
                                                <h4 class="modal-title text-center"><strong>Our Services</strong></h4>
                                                <p class="text-center">Choose from our packages below!</p>
                                            </div>
                                        </div>

                                    @php
                                        $dog = \App\Lib\Helper::get_dog_pricing(1);
                                        $cat = \App\Lib\Helper::get_cat_pricing(1);
                                    @endphp


                                    <!-- Packages -->
                                        <div class="row services-carousel">
                                            <!-- Gold -->
                                            <div class="col-sm-4 select-service" id="service-gold">
                                                <a href="/user" title="Schedule Appointment" target="_self">
                                                    <div class="panel panel-default">
                                                        <div class="panel-heading text-center">
                                                            <h3 class="gold">GOLD</h3>
                                                            <em>Starting at</em><br>
                                                            <span class="price dog">
                                                                ${{ $dog['gold']->denom }}
                                                            </span>
                                                            <span class="price cat hidden">
                                                                ${{ $cat['gold']->denom }}
                                                            </span>
                                                        </div>
                                                        <div class="panel-body">
                                                            <div class="row service-items">
                                                                <div class="col-xs-12 text-center">
                                                                    <ul class="text-left dog">
                                                                        @php
                                                                            $str1 = $dog['gold']->prod_desc;
                                                                            $gold_dog = explode(',', $str1);
                                                                            foreach ($gold_dog as $g){
                                                                        @endphp
                                                                                <li>
                                                                                    <p><i class="fas fa-check"></i><span>{{ $g }}</span> </p>
                                                                                </li>
                                                                        @php
                                                                            }
                                                                        @endphp
                                                                    </ul>

                                                                    <ul class="text-left cat hidden">
                                                                        @php
                                                                            $str2 = $cat['gold']->prod_desc;
                                                                            $gold_cat = explode(',', $str2);
                                                                            foreach ($gold_cat as $gc){
                                                                        @endphp
                                                                        <li>
                                                                            <p><i class="fas fa-check"></i><span>{{ $gc }}</span> </p>
                                                                        </li>
                                                                        @php
                                                                            }
                                                                        @endphp

                                                                    </ul>
                                                                </div>
                                                                <div class="col-xs-12 text-center">
                                                                    <ul class="package-specs text-center">
                                                                        <li>Same day service optional</li>												
                                                                        <li>Request your favorite groomer</li>
                                                                        <li>Full refund if you cancel by 6pm the day before</li>										
                                                                        <li>Payment upon completion</li>
                                                                    </ul>
                                                                </div>
                                                                <!-- /col -->
                                                            </div>
                                                            <!-- /service-items -->
                                                        </div>
                                                        <!-- /panel-body -->
                                                    </div>
                                                    <!-- /panel-default -->
                                                </a>
                                            </div>
                                            <!-- Eco -->
                                            <div class="col-sm-4 select-service" id="service-eco">
                                                <a href="/user" title="Schedule Appointment" target="_self">
                                                    <div class="panel panel-default">
                                                        <div class="panel-heading text-center">
                                                            <h3 class="eco">ECO</h3>
                                                            <em>Starting at</em><br>
                                                            <span class="price dog">
                                                                ${{ $dog['eco']->denom }}
                                                            </span>
                                                            <span class="price cat hidden">
                                                                ${{ $cat['eco']->denom }}
                                                            </span>

                                                        </div>
                                                        <div class="panel-body">
                                                            <div class="row service-items">
                                                                <div class="col-xs-12 text-center">
                                                                    <ul class="text-left dog">
                                                                        @php
                                                                            $str3 = $dog['eco']->prod_desc;
                                                                            $eco_dog = explode(',', $str3);
                                                                            foreach ($gold_dog as $d) {
                                                                        @endphp
                                                                        <li>
                                                                            @if(in_array($d, $eco_dog))
                                                                                <p><i class="fas fa-check"></i><span>{{ $d }}</span> </p>
                                                                            @else
                                                                                <p><i class="fas fa-times"></i><span>{{ $d }}</span> </p>
                                                                            @endif
                                                                        </li>
                                                                        @php
                                                                            }
                                                                        @endphp
                                                                    </ul>

                                                                    <ul class="text-left cat hidden">
                                                                        @php
                                                                            $str = $cat['eco']->prod_desc;
                                                                            $eco_cat = explode(',', $str);
                                                                            foreach ($gold_cat as $c){
                                                                        @endphp
                                                                        <li>
                                                                            @if(in_array($c, $eco_cat))
                                                                                <p><i class="fas fa-check"></i><span>{{ $c }}</span> </p>
                                                                            @else
                                                                                <p><i class="fas fa-times"></i><span>{{ $c }}</span> </p>
                                                                            @endif
                                                                        </li>
                                                                        @php
                                                                            }
                                                                        @endphp
                                                                    </ul>
                                                                </div>
                                                                <div class="col-xs-12 text-center">
                                                                    <ul class="package-specs text-center">
                                                                        <li>Book 7 Days In Advance</li>
                                                                        <li>Request your favorite groomer</li>
                                                                        <li>Non-Refundable Booking</li>
                                                                        <li>Prepaid</li>
                                                                    </ul>
                                                                </div>
                                                                <!-- /col -->
                                                            </div>
                                                            <!-- /service-items -->
                                                        </div>
                                                        <!-- /panel-body -->
                                                    </div>
                                                    <!-- /panel-default -->
                                                </a>
                                            </div>
                                            <!-- Silver -->
                                            <div class="col-sm-4 select-service" id="service-silver">
                                                <a href="/user" title="Schedule Appointment" target="_self">
                                                    <div class="panel panel-default">
                                                        <div class="panel-heading text-center">
                                                            <h3 class="silver">SILVER</h3>
                                                            <em>Starting at</em><br>
                                                            <span class="price dog">
                                                                ${{ $dog['silver']->denom }}
                                                            </span>
                                                            <span class="price cat hidden">
                                                                ${{ $cat['silver']->denom }}
                                                            </span>
                                                        </div>
                                                        <div class="panel-body">
                                                            <div class="row service-items">
                                                                <div class="col-xs-12 text-center">
                                                                    <ul class="text-left dog">
                                                                        @php
                                                                            $str = $dog['silver']->prod_desc;
                                                                            $silver_dog = explode(',', $str);
                                                                            foreach ($gold_dog as $d){
                                                                        @endphp
                                                                        <li>
                                                                            @if(in_array($d, $silver_dog))
                                                                                <p><i class="fas fa-check"></i><span>{{ $d }}</span> </p>
                                                                            @else
                                                                                <p><i class="fas fa-times"></i><span>{{ $d }}</span> </p>
                                                                            @endif
                                                                        </li>
                                                                        @php
                                                                            }
                                                                        @endphp
                                                                    </ul>

                                                                    <ul class="text-left cat hidden">
                                                                        @php
                                                                            $str = $cat['silver']->prod_desc;
                                                                            $silver_cat = explode(',', $str);
                                                                            foreach ($gold_cat as $c){
                                                                        @endphp
                                                                        <li>
                                                                            @if(in_array($c, $silver_cat))
                                                                                <p><i class="fas fa-check"></i><span>{{ $c }}</span> </p>
                                                                            @else
                                                                                <p><i class="fas fa-times"></i><span>{{ $c }}</span> </p>
                                                                            @endif
                                                                        </li>
                                                                        @php
                                                                            }
                                                                        @endphp
                                                                    </ul>
                                                                </div>
                                                                <div class="col-xs-12 text-center">
                                                                    <ul class="package-specs text-center">
                                                                        <li>Same day service optional</li>												
                                                                        <li>Request your favorite groomer</li>
                                                                        <li>Full refund if you cancel by 6pm the day before</li>										
                                                                        <li>Payment upon completion</li>
                                                                    </ul>
                                                                </div>
                                                                <!-- /col -->
                                                            </div>
                                                            <!-- /service-items -->
                                                        </div>
                                                        <!-- /panel-body -->
                                                    </div>
                                                    <!-- /panel-default -->
                                                </a>
                                            </div>
                                            <!-- /col-6 -->
                                        </div>
                                        <!-- /row -->

                                    </section>

                                   
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

            </div>
		</div>
	</div>
</section> <!-- #aboutUs ends -->

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

function click_schedule(url) {
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        window.location.href = 'http://onelink.to/groom';
    } else {
        window.location.href = url;
    }
}
</script>

@stop