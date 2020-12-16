@extends('user.layout.default')
@section('content')
    <link href="/desktop/css/appointment.css" rel="stylesheet">
    <div id="main">
        <!-- Page Content -->
        <div class="container-fluid" id="progress-bar">
            <div class="row">
                <div class="col-xs-3 line-status complete"></div>
                <div class="col-xs-3 line-status"></div>
                <div class="col-xs-3 line-status"></div>
                <div class="col-xs-3 line-status"></div>
            </div>
            <!-- row -->
        </div>
        <!-- /progress-bar -->

        <!-- SELECT-PET -->
        <section id="select-pet">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h3>SELECT PET</h3>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 text-center">
                        <div class="row" id="pets">
                            @if (Schedule::getFirstPetType() != 'cat')
                                <div class="col-xs-6">
                                    <a href="/user/schedule/select-dog">
                                        <div class="cont-select-pet">
                                            <div class="cont-select-pet-img"><img class="img-responsive"
                                                                                  src="/desktop/img/dog-icon_big.png" alt="Select Dog"/>
                                                <div class="cont-check-green"><img class="img-responsive check-green"
                                                                                   src="/desktop/img/check-green.png" alt="Selected"/>
                                                </div>
                                            </div>
                                            <p>Dog</p>
                                        </div>
                                    </a>
                                    <!-- cont-select-pet -->
                                </div>
                        @endif
                        @if (Schedule::getFirstPetType() != 'dog')
                            <!-- /col-6 -->
                                <div class="col-xs-6">
                                    <a href="/user/schedule/select-cat-new">
                                        <div class="cont-select-pet selected">
                                            <div class="cont-select-pet-img select-cat"><img class="img-responsive"
                                                                                             src="/desktop/img/cat-icon_big.png" alt="Select Cat"/>
                                                <div class="cont-check-green"><img class="img-responsive check-green"
                                                                                   src="/desktop/img/check-green.png" alt="Selected"/>
                                                </div>
                                            </div>
                                            <p>Cat</p>
                                        </div>
                                    </a>
                                    <!-- cont-select-pet -->
                                </div>
                        @endif
                        <!-- /col-6 -->
                        </div>
                        <!-- /row -->
                    </div>
                    <!-- /col-6 -->
                </div>
                <!-- row -->
            </div>
            <!-- /container -->
        </section>
        <!-- /select-pet -->



        <section id="services">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h3>SELECT SERVICE</h3>
                    </div>
                </div>
                <!-- row -->

                <div class="row services-carousel" id="cat-service-new">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-md-12 carousel-container">
                                <div class="owl-carousel" id="cat-carousel">
                                    <div class="item selected select-service {{ Schedule::getCurrentPackageId() == 16
                                    ? 'selected' : '' }}" id="service-gold" onclick="select_package(16)">
                                        <h3 class="service-title gold-bg text-center">GOLD</h3>
                                        <div class="service-body">
                                            <div class="row service-items">
                                                <div class="col-sm-7 col-xs-6">
                                                    <ul>
                                                        <li>
                                                            <div class="media-left"><img
                                                                        class="media-object service-icon"
                                                                        src="/desktop/img/icon-service-bath.png"
                                                                        alt="Bath with Shampoo"/>
                                                            </div>
                                                            <div class="media-body"><span>Bath</span></div>
                                                        </li>
                                                        <li>
                                                            <div class="media-left"><img
                                                                        class="media-object service-icon"
                                                                        src="/desktop/img/icon-service-blow-dry.png"
                                                                        alt="Blow Dry"/></div>
                                                            <div class="media-body"><span>Blow-Dry</span></div>
                                                        </li>
                                                        <li>
                                                            <div class="media-left"><img
                                                                        class="media-object service-icon"
                                                                        src="/desktop/img/icon-service-ear-cleaning.png"
                                                                        alt="Ear Cleaning"/></div>
                                                            <div class="media-body"><span>Ear Cleaning</span></div>
                                                        </li>
														                            <li>
                                                            <div class="media-left"><img
                                                                        class="media-object service-icon"
                                                                        src="/desktop/img/icon-service-sanitary-trim.png"
                                                                        alt="Sanitary Trim"/></div>
                                                            <div class="media-body"><span>Sanitary Trim</span></div>
                                                        </li>
                                                        <li>
                                                            <div class="media-left"><img
                                                                        class="media-object service-icon"
                                                                        src="/desktop/img/icon-service-dry-brush-out.png"
                                                                        alt="Desheading"/></div>
                                                            <div class="media-body"><span>Deshedding</span></div>
                                                        </li>

                                                    </ul>
                                                </div>
                                                <div class="col-sm-5 col-xs-6">
                                                    <ul>
                                                        <li>
                                                            <div class="media-left"><img
                                                                        class="media-object service-icon"
                                                                        src="/desktop/img/icon-service-dry-brush-out.png"
                                                                        alt="Dry Brush Out"/></div>
                                                            <div class="media-body"><span>Dry Brush Out</span></div>
                                                        </li>
                                                        <li>
                                                            <div class="media-left"><img
                                                                        class="media-object service-icon"
                                                                        src="/desktop/img/icon-service-nail-trim.png"
                                                                        alt="Nail Trim"/></div>
                                                            <div class="media-body"><span>Nail Trim</span></div>
                                                        </li>
                                                        <li>
                                                            <div class="media-left"><img
                                                                        class="media-object service-icon"
                                                                        src="/desktop/img/icon-service-dematting.png"
                                                                        alt="Dematting"/></div>
                                                            <div class="media-body"><span>Dematting</span></div>
                                                        </li>
														 <li>
                                                            <div class="media-left"><img
                                                                        class="media-object service-icon"
                                                                        src="/desktop/img/icon-service-pad-tail-trim.png"
                                                                        alt="Pad &amp; Tail Trim"/></div>
                                                            <div class="media-body"><span>Pad &amp; Tail Trim</span></div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <!-- /service-items -->
											<div class="line-divider"></div>
                                            <div class="row">
                                                <div class="col-md-12 text-center">
                                                    <div class="groomit-btn beige-btn btn-block"><span
                                                                class="glyphicon glyphicon-ok"
                                                                aria-hidden="true"></span> HAIRCUT (LION CUT)
                                                    </div>
                                                </div>
                                          </div>

                                          <div class="row">
                        										<div class="col-xs-6">
                        											<ul class="package-specs">
                        												<li>
                        													<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>Book in advance
                        												</li>
                        												<li>
                        													<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>Cancel up to 1h prior
                        												</li>

                        											</ul>
                        										</div>
                        										<!-- /col -->
                        										<div class="col-xs-6">
                        											<ul class="package-specs">
                        												<li>
                        													<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>Favourite Groomer
                        												</li>
                        												<li>
                        													<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>Pay After Service
                        												</li>


                        											</ul>
                        										</div>
                        										<!-- /col -->
                        									</div>
                        									<!-- /row-->


                                          <div class="row">
                                                <div class="col-md-12 text-center">
                                                    <!-- <h4 id="gold_package_price">${{ number_format($gold->denom, 2) }}</h4> -->
                                                    <h4 id="gold_package_price">$150</h4>
                                                </div>
                                            </div>
                                            <!-- row -->
                                        </div>
                                        <!-- /service-items -->

                                        <div class="row">
                                            <div class="col-md-12 text-center">
                                                <button type="button" onclick="select_package(16)"
                                                        class="groomit-btn white-btn check-btn btn-block">
                                                    SELECT
                                                </button>
                                            </div>
                                        </div>
                                        <!-- row -->

                                    </div>
                                    <!-- select-service -->

                                    <div class="item select-service {{ Schedule::getCurrentPackageId() == 27 ?
                                    'selected' : '' }}" id="service-silver" onclick="select_package(27)">
                                        <h3 class="service-title silver-bg text-center">SILVER</h3>
                                        <div class="service-body">
                                            <div class="row service-items">
                                                <div class="col-sm-7 col-xs-6">
                                                    <ul>
                                                      <li>
                                                          <div class="media-left"><img
                                                                      class="media-object service-icon"
                                                                      src="/desktop/img/icon-service-bath.png"
                                                                      alt="Bath with Shampoo"/>
                                                          </div>
                                                          <div class="media-body"><span>Bath</span></div>
                                                      </li>
                                                      <li>
                                                          <div class="media-left"><img
                                                                      class="media-object service-icon"
                                                                      src="/desktop/img/icon-service-blow-dry.png"
                                                                      alt="Blow Dry"/></div>
                                                          <div class="media-body"><span>Blow-Dry</span></div>
                                                      </li>
                                                      <li>
                                                          <div class="media-left"><img
                                                                      class="media-object service-icon"
                                                                      src="/desktop/img/icon-service-ear-cleaning.png"
                                                                      alt="Ear Cleaning"/></div>
                                                          <div class="media-body"><span>Ear Cleaning</span></div>
                                                      </li>
                                                    </ul>
                                                </div>
                                                <div class="col-sm-5 col-xs-6">
                                                    <ul>
                                                        <li>
                                                            <div class="media-left"><img
                                                                        class="media-object service-icon"
                                                                        src="/desktop/img/icon-service-dry-brush-out.png"
                                                                        alt="Dry Brush Out"/></div>
                                                            <div class="media-body"><span>Dry Brush Out</span></div>
                                                        </li>
                                                        <li>
                                                            <div class="media-left"><img
                                                                        class="media-object service-icon"
                                                                        src="/desktop/img/icon-service-nail-trim.png"
                                                                        alt="Nail Trim"/></div>
                                                            <div class="media-body"><span>Nail Trim</span></div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <!-- service-items -->

											<div class="line-divider"></div>
                                            <div class="row">
                                                <div class="col-md-12 text-center">
                                                    <div class="groomit-btn silver-btn check-btn btn-block"><span
                                                                class="glyphicon glyphicon-remove"
                                                                aria-hidden="true"></span> HAIRCUT NOT INCLUDED
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                          										<div class="col-xs-6">
                          											<ul class="package-specs">
                          												<li>
                          													<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>Book in advance
                          												</li>
                          												<li>
                          													<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>Cancel up to 1h prior
                          												</li>

                          											</ul>
                          										</div>
                          										<!-- /col -->
                          										<div class="col-xs-6">
                          											<ul class="package-specs">
                          												<li>
                          													<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>Favourite Groomer
                          												</li>
                          												<li>
                          													<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>Pay After Service
                          												</li>


                          											</ul>
                          										</div>
                          										<!-- /col -->
                          									</div>
                          									<!-- /row-->

                                            <div class="row">
                                                <div class="col-md-12 text-center">
                                                    <!-- <h4 id="silver_package_price">${{ number_format($silver->denom, 2)}}</h4> -->
                                                    <h4 id="silver_package_price">$120</h4>
                                                </div>
                                            </div>
                                            <!-- row -->


                                        </div>
                                        <!-- /service-items -->

                                        <div class="row">
                                            <div class="col-md-12 text-center">
                                                <button type="button" onclick="select_package(27)"
                                                        class="groomit-btn white-btn check-btn btn-block">
                                                    SELECT
                                                </button>
                                            </div>
                                        </div>
                                        <!-- row -->

                                    </div>
                                    <!-- col-lg-6 -->



                                    <div class="item select-service"  id="service-eco" onclick="">
                      							 	<div class="new-label">
                      								 	<img src="/desktop/img/newLabel.png" alt="NEW" />
                      								</div>
                      								<h3 class="service-title eco-bg text-center">ECO</h3>
                      								<div class="service-body">
                      									<div class="row service-items">
                      										<div class="col-xs-6">
                      											<ul>
                                              <li>
                                                  <div class="media-left"><img
                                                              class="media-object service-icon"
                                                              src="/desktop/img/icon-service-bath.png"
                                                              alt="Bath with Shampoo"/>
                                                  </div>
                                                  <div class="media-body"><span>Bath</span></div>
                                              </li>
                                              <li>
                                                  <div class="media-left"><img
                                                              class="media-object service-icon"
                                                              src="/desktop/img/icon-service-blow-dry.png"
                                                              alt="Blow Dry"/></div>
                                                  <div class="media-body"><span>Blow-Dry</span></div>
                                              </li>
                                              <li>
                                                  <div class="media-left"><img
                                                              class="media-object service-icon"
                                                              src="/desktop/img/icon-service-ear-cleaning.png"
                                                              alt="Ear Cleaning"/></div>
                                                  <div class="media-body"><span>Ear Cleaning</span></div>
                                              </li>
                                              <li>
                                                  <div class="media-left"><img
                                                              class="media-object service-icon"
                                                              src="/desktop/img/icon-service-sanitary-trim.png"
                                                              alt="Sanitary Trim"/></div>
                                                  <div class="media-body"><span>Sanitary Trim</span></div>
                                              </li>
                      											</ul>
                      										</div>
                      										<div class="col-xs-6">
                      											<ul>
                                              <li>
                                                  <div class="media-left"><img
                                                              class="media-object service-icon"
                                                              src="/desktop/img/icon-service-dry-brush-out.png"
                                                              alt="Dry Brush Out"/></div>
                                                  <div class="media-body"><span>Dry Brush Out</span></div>
                                              </li>
                                              <li>
                                                  <div class="media-left"><img
                                                              class="media-object service-icon"
                                                              src="/desktop/img/icon-service-nail-trim.png"
                                                              alt="Nail Trim"/></div>
                                                  <div class="media-body"><span>Nail Trim</span></div>
                                              </li>
                                              <li>
                                                  <div class="media-left"><img
                                                              class="media-object service-icon"
                                                              src="/desktop/img/icon-service-dematting.png"
                                                              alt="Dematting"/></div>
                                                  <div class="media-body"><span>Dematting</span></div>
                                              </li>
                                              <li>
                                                  <div class="media-left"><img
                                                              class="media-object service-icon"
                                                              src="/desktop/img/icon-service-pad-tail-trim.png"
                                                              alt="Pad &amp; Tail Trim"/></div>
                                                  <div class="media-body"><span>Pad &amp; Tail Trim</span></div>
                                              </li>
                      											</ul>
                      										</div>
                      									</div>
                      									<!-- /service-items -->
                      									<div class="line-divider"></div>
                      									<div class="row">
                      										<div class="col-md-12 text-center">
                      											<div class="groomit-btn eco-btn btn-block"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> HAIRCUT (LION CUT)
                      											</div>
                      										</div>
                      									</div>
                      									<!-- /row-->
                      									<div class="row">
                      										<div class="col-xs-6">
                      											<ul class="package-specs">
                      												<li>Book 7 days in advance</li>
                      												<li>Non-Refundable Booking</li>
                      											</ul>
                      										</div>
                      										<!-- /col -->
                      										<div class="col-xs-6">
                      											<ul class="package-specs">
                      												<li>No Favourite Groomer</li>
                      												<li>Prepaid</li>


                      											</ul>
                      										</div>
                      										<!-- /col -->
                      									</div>
                      									<!-- /row-->
                      									<div class="row">
                      										<div class="col-md-12 text-center">
                      											<h4 id="eco_package_price">$90</h4>
                      										</div>
                      									</div>
                      									<!-- row -->
                      								</div>
                      								<!-- /service-items -->

                      								<div class="row">
                      									<div class="col-md-12 text-center">
                      										<button type="button" class="groomit-btn white-btn check-btn
                      										btn-block">SELECT</button>
                      									</div>
                      								</div>
                      								<!-- row -->

                      							</div>
                      							<!-- select-service -->



                                </div>
                                <!--/owl -->
                            </div>
                            <!-- /col -->
                        </div>
                        <!-- row -->
                    </div>
                    <!-- col-lg-10 -->
                </div>
                <!-- row -->
            </div>
            <!-- /container -->
        </section>
        <section id="next-btn">
            <div class="container">
                <div class="row">
                    <!--<div class="col-md-12 text-center"> <a class="groomit-btn red-btn big-btn" href="date-time.html">CONTINUE</a> </div>-->
                    <div class="col-md-12 text-center"><a class="groomit-btn rounded-btn red-btn big-btn" id="btn_continue" style="display:{{ Schedule::getCurrentPackageId() ? '' : 'none' }};"
                                                          href="javascript:go_to_next();">CONTINUE</a></div>
                </div>
                <!-- row -->
            </div>
            <!-- container -->
        </section>
    </div>

    <form id="frm_submit" style="display:none;" method="post" action="/user/schedule/select-dog">
        {!! csrf_field() !!}
        <input type="hidden" name="size" id="size" value="{{ old('size', Schedule::getCurrentSize()) }}"/>
        <input type="hidden" name="package" id="package" value="{{ old('package', Schedule::getCurrentPackageId()) }}"/>
    </form>

    <script type="text/javascript">

        function select_size(size) {
            $('#size').val(size);
            //$('#frm_submit').submit();

            $.ajax({
                url: '/user/schedule/select-dog/update-size',
                data: {
                    _token: '{!! csrf_token() !!}',
                    size: size
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {

                        $('#gold_package_price').text('$' + parseFloat(res.gold.denom).toFixed(2));
                        $('#silver_package_price').text('$' + parseFloat(res.silver.denom).toFixed(2));
                        $('#current_sub_total').text('$' + parseFloat(res.current_sub_total).toFixed(2));

                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function select_package(package_id) {
            $('#btn_continue').hide();

            $.ajax({
                url: '/user/schedule/select-dog/update-package',
                data: {
                    _token: '{!! csrf_token() !!}',
                    package_id: package_id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        $('#current_package_name').text(res.current_package.prod_name);
                        $('#current_sub_total').text('$' + parseFloat(res.current_sub_total).toFixed(2));
                        $('#btn_continue').show();
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function go_to_next() {
            var package = $('#package').val();
            if ($.trim(package) === '') {
                myApp.showError('Please select package first!');
                return;
            }

            $('#frm_submit').prop('action', '/user/schedule/select-addon');
            $('#frm_submit').submit();
        }
    </script>
@stop
