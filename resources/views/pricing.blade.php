@extends('includes.default')

@section('content')
<section id="GroomitBannerClean" class="bannerPricing" title="In-Home pet grooming"> <!-- #banner starts -->
</section> <!-- #banner ends -->

<section id="pricing" class=""> <!-- #benefits starts -->

	<div class="container container-content-clean-pricing">
		<div class="row ">
			<div class="col-lg-12">


            <div id="prices-main">
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
                                                <h4 class="modal-title text-center">Our Services</h4>
                                                <p class="text-center">Choose from our packages below!</p>
                                            </div>
                                        </div>

                                    @php
                                        $dog = \App\Lib\Helper::get_dog_pricing();
                                        $cat = \App\Lib\Helper::get_cat_pricing();
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

                                    @php
                                        $dog_addons = \App\Lib\Helper::get_dog_addon();
                                        $cat_addons = \App\Lib\Helper::get_cat_addon();


                                    @endphp

                                    <section id="shampoo-addon">
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <h4 class="modal-title text-center">Shampoo and optional add-ons</h4>
                                                <p class="text-center">Personalize your pet's grooming experience by selecting a shampoo and
                                                    some of our optional add-ons.</p>
                                                <br><br>
                                            </div>
                                        </div>
                                        <!-- /row -->
                                        <div class="row" id="shampoos">
                                            <div class="col-xs-12">
                                                <div class="display-table dog">

                                                    <div class="table-cell left" id="pura-naturals">
                                                        <img class="img-responsive" src="/images/basics.png" width="118" height="94" alt="Organic dog shampoo" />
                                                    </div>

                                                    <div class="table-cell right shampoo-col" id="shampoo-1">
                                                        <div class="media">
                                                            <div class="media-left"> <img class="media-object" src="/images/lavander.jpg"
                                                                    width="65" height="190" alt="Organic dog shampoo"/> </div>
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
                                                            <div class="media-left">
                                                                <img class="media-object" src="/images/teatree.jpg" width="65" height="190" alt="Organic dog conditioner" />
                                                            </div>
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
                                                            <div class="media-left"> <img class="media-object" src="/images/unscented.jpg"
                                                                    width="65" height="190" alt="Organic dog shampoo and conditioner" /> </div>
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

                                                    <div class="table-cell left"><img class="img-responsive" src="/images/basics.png"
                                                            width="118" height="94" alt="Basics by NXT Generation Pet	&trade;" /></div>
                                                    <div class="table-cell right shampoo-col">
                                                        <div class="media">
                                                            <div class="media-left"> <img class="media-object" src="/images/cat-shampoo.jpg"
                                                                    width="65" height="190" alt="Waterless Foam Organic" /> </div>
                                                            <div class="media-body">
                                                                <h4 class="media-heading">WaterLess Foaming Organic</h4>
                                                                <p>Cat Shampoo</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- /table-cell -->
                {{--                                    <div class="table-cell left shampoo-col addons-list">--}}
                {{--                                        <ul class="addons">--}}
                {{--                                            <li>--}}
                {{--                                                <a role="button" data-toggle="collapse" data-target="#info-checkbox-cat"--}}
                {{--                                                    aria-expanded="false" aria-controls="info-checkbox-cat">--}}
                {{--                                                    <span class="addon-name">Flea/Tick</span><span--}}
                {{--                                                        class="pull-right tooltip-col"> <i class="fas fa-chevron-down"></i>--}}
                {{--                                                    </span>--}}
                {{--                                                </a>--}}
                {{--                                                <div class="collapse" id="info-checkbox-cat">--}}
                {{--                                                    <div class="addon-info-mobile">All natural formula treatment. Kills--}}
                {{--                                                        fleas, ticks, larvae and eggs by contact. No Pyrethrin or--}}
                {{--                                                        Permethrin.</div>--}}
                {{--                                                </div>--}}
                {{--                                            </li>--}}
                {{--                                        </ul>--}}
                {{--                                    </div>--}}
                                                    <!-- /table-cell -->
                                                </div>
                                                <!-- /tablerow -->

                                            </div>
                                            <!-- /col-12 -->
                                        </div>

                                        <div class="col-md-10 col-md-offset-1">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="display-table addons">
                                                        @foreach($cat_addons as $a)
                                                        @endforeach
                                                        @for($i=0; $i<sizeof($a); $i++)
                                                            <div class="col-sm-4 col-xs-12 table-cell left cat hidden">
                                                                <div role="button" data-toggle="collapse" data-target="#info-checkbox-cat-{{$i}}"
                                                                    aria-expanded="false" aria-controls="info-checkbox-cat-{{$i}}">
                                                                    <span class="addon-name">{{ $a[$i]->prod_name }}</span>
                                                                    <span class="pull-right tooltip-col">
                                                                    <i class="fas fa-chevron-down"></i>
                                                                </span>
                                                                </div>
                                                                <div class="collapse" id="info-checkbox-cat-{{$i}}">
                                                                    <div class="addon-info-mobile">
                                                                        {{ $a[$i]->prod_desc }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endfor
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /row -->
                                        </div>

                                        <!-- /row -->
                                    </section>
                                    <section class="dog" id="addons-list">
                                        <div class="row">
                                            <div class="col-md-10 col-md-offset-1">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="display-table addons">
                                                            @foreach($dog_addons as $a)
                                                            @endforeach
                                                            @for($i=0; $i<sizeof($a); $i++)
                                                                    <div class="col-sm-4 col-xs-12  table-cell left" style="margin-top: 10px">
                                                                        <div role="button" data-toggle="collapse" data-target="#info-checkbox-{{$i}}"
                                                                            aria-expanded="false" aria-controls="info-checkbox-{{$i}}">
                                                                            <span class="addon-name">
                                                                                @if($a[$i]->prod_name == 'De-Matting')
                                                                                    {{ $a[$i]->prod_name }} *
                                                                                @else
                                                                                    {{ $a[$i]->prod_name }}
                                                                                @endif
                                                                            </span>
                                                                            <span class="pull-right tooltip-col">
                                                                                <i class="fas fa-chevron-down"></i>
                                                                            </span>
                                                                        </div>
                                                                        <div class="collapse" id="info-checkbox-{{$i}}">
                                                                            <div class="addon-info-mobile">
                                                                                {{ $a[$i]->prod_desc }}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- /row -->
                                                </br>
                                                <p>* Only available in the Gold and Eco Packages.</p>
                                            </div>
                                            <!-- /col -->
                                        </div>
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
</script>

@stop