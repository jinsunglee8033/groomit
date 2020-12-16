@extends( 'user.layout.default' )
@section( 'content' )
	<link href="/desktop/css/appointment.css?v=1.0.2" rel="stylesheet">
	<!-- PROGRESS -->
	<div class="container-fluid" id="progress-bar">
		<div class="row">
			<div class="col-xs-2 line-status"></div>
			<div class="col-xs-2 line-status"></div>
			<div class="col-xs-2 line-status"></div>
			<div class="col-xs-2 line-status"></div>
			<div class="col-xs-2 line-status"></div>
			<div class="col-xs-2 line-status"></div>
		</div>
		<!-- row -->
	</div>
	<!-- /progress-bar -->
	<div id="main">
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
									<div class="cont-select-pet selected">
										<div class="cont-select-pet-img"><img class="img-responsive" src="/desktop/img/dog-icon.svg" alt="Select Dog" width="100" />
											<!--<div class="cont-check-green"><img class="img-responsive check-green" src="/desktop/img/check-green.png" alt="Selected"/>
											</div>-->
										</div>
										<p>Dog</p>
									</div>
								</a>
								<!-- cont-select-pet -->
							</div>
							@endif
							<!-- /col-6 -->
							@if (Schedule::getFirstPetType() != 'dog')
							<div class="col-xs-6">
								<a href="/user/schedule/select-cat">
									<div class="cont-select-pet">
										<div class="cont-select-pet-img select-cat"><img class="img-responsive" src="/desktop/img/cat-icon.svg" alt="Select Cat" width="100" />
											<!--<div class="cont-check-green"><img class="img-responsive check-green" src="/desktop/img/check-green.png" alt="Selected"/>
											</div>-->
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
		</section>
		<!-- /select-pet -->


		<!-- PET SIZE -->
		<section id="pet-size">
			<div class="container">
				<div class="row">
					<div class="col-md-12 text-center">
						<h3>SELECT DOG'S SIZE</h3>
					</div>
				</div>
				<!-- row -->

				<div class="row">
					<div class="col-md-8 col-sm-10 col-md-offset-2 col-sm-offset-1">
						<div class="row">
							<div class="col-md-12" id="dog-sizes-container">
								<div class="cont-dog-size {{ old('size', Schedule::getCurrentSize()) == 2 ? 'active' : '' }} text-center" id="size-s" onclick="select_size(2)"><img class="img-responsive dog-size-i" src="/desktop/img/dog-size-i.png" alt="Dog Size Small"/> <img class="img-responsive dog-size-a" src="/desktop/img/dog-size-a.png" alt="Dog Size Small"/>
									<p class="text-center hidden-xs"><strong>Small</strong> Breed <br>
										<20lbs</p>
								</div>
								<div class="cont-dog-size {{ old('size', Schedule::getCurrentSize()) == 3 ? 'active' : '' }} text-center" id="size-m" onclick="select_size(3)"><img class="img-responsive dog-size-i" src="/desktop/img/dog-size-i.png" alt="Dog Size Medium"/> <img class="img-responsive dog-size-a" src="/desktop/img/dog-size-a.png" alt="Dog Size Medium"/>
									<p class="text-center hidden-xs"><strong>Medium</strong> Breed<br> 21-40lbs
									</p>
								</div>
								<div class="cont-dog-size {{ old('size', Schedule::getCurrentSize()) == 4 ? 'active' : '' }} text-center" id="size-l" onclick="select_size(4)"><img class="img-responsive dog-size-i" src="/desktop/img/dog-size-i.png" alt="Dog Size Large"/> <img class="img-responsive dog-size-a" src="/desktop/img/dog-size-a.png" alt="Dog Size Large"/>
									<p class="text-center hidden-xs"><strong>Large</strong> Breed <br> 41-90lbs
									</p>
								</div>
								<div class="cont-dog-size {{ old('size', Schedule::getCurrentSize()) == 5 ? 'active' : '' }} text-center" id="size-xl" onclick="select_size(5)"><img class="img-responsive dog-size-i" src="/desktop/img/dog-size-i.png" alt="Dog Size Extra-large"/> <img class="img-responsive dog-size-a" src="/desktop/img/dog-size-a.png" alt="Dog Size Extra-large"/>
									<p class="text-center hidden-xs"><strong>Extra-large</strong> Breed <br> >91lbs
									</p>
								</div>
							</div>
							<p class="text-center visible-xs" id="xs-selected-size"><strong>Small</strong> Breed
								<20lbs</p>
						</div>
						<!-- row -->
					</div>
				</div>
				<!-- row -->
			</div>
			<!-- /container -->
		</section>
		<section id="services" style="display:{{ Schedule::getCurrentSize() ? '' : 'none' }}">
			<div class="container">
				<div class="row">
					<div class="col-md-12 text-center">
						<h3>SELECT SERVICE</h3>
					</div>
				</div>
				<!-- row -->

				<div class="row services-carousel" id="dog-service">
					<div class="col-xs-12 carousel-container ios-fix">
						<div class="owl-carousel" id="dog-carousel">
							<div class="item select-service {{ Schedule::getCurrentPackageId() == 1 ? 'selected' : '' }}" id="service-gold" onclick="select_package(1)">
								<h3 class="service-title gold-bg"><span class="pull-left">GOLD</span><span class="pull-right" id="gold_package_price">${{ number_format($gold->denom, 2) }}</span><span class="clearfix"></span></h3>
								<div class="service-body">
									<div class="row service-items">
										<div class="col-xs-12">
											<div class="center-service-items">
												<ul class="text-left">
													<li>
														<p><i class="fas fa-check"></i><span>Dry Brush Out</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Bath</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Coat Conditioning</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Ear Cleaning</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Nail Trim</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Blow Dry</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Cologne</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Sanitary Trim</span> </p>
													</li>
												</ul>
											</div>
											<!-- /center-service-items -->
										</div>
									</div>
									<!-- /service-items -->
									<div class="row haircut-info">
										<div class="col-md-12 text-center">
											<div class="groomit-btn beige-btn-outline btn-block"><span class="glyphicon glyphicon-ok text-success" aria-hidden="true"></span> HAIRCUT
											</div>
											<div class="line-divider"></div>
										</div>

									</div>
									<!-- /row-->

									<div class="row">
										<div class="col-xs-6">
											<ul class="package-specs pull-left">
												<li>Same day service<br> optional</li>
												<li>Request your<br> favorite groomer</li>
											</ul>
										</div>
										<!-- /col -->
										<div class="col-xs-6">
											<ul class="package-specs pull-right">
												<li>Full refund if you cancel by 6pm the day before</li>
												<li>Payment upon<br> completion</li>
											</ul>
										</div>
										<!-- /col -->
									</div>
									<!-- /row-->
								</div>
								<!-- /service-items -->

								<div class="row">
									<div class="col-md-12 text-center">
										<button type="button" class="groomit-btn white-btn check-btn btn-block">SELECT</button>
									</div>
								</div>
								<!-- row -->

							</div>
							<!-- select-service -->

							<div class="item select-service {{ Schedule::getCurrentPackageId() == 28 ? 'selected' : '' }}" id="service-eco" onclick="select_package(28)">
							 	<!--<div class="new-label">
								 	<img src="/desktop/img/newLabel.png" alt="NEW" />
								</div>-->
								<h3 class="service-title eco-bg"><span class="pull-left">ECO</span><span class="pull-right" id="eco_package_price">${{ number_format($eco->denom, 2) }}</span><span class="clearfix"></span></h3>
								<div class="service-body">
									<div class="row service-items">
										<div class="col-xs-12">
											<div class="center-service-items">
												<ul class="text-left">
													<li>
														<p><i class="fas fa-check"></i><span>Dry Brush Out</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Bath</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Coat Conditioning</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Ear Cleaning</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Nail Trim</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Blow Dry</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Cologne</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Sanitary Trim</span> </p>
													</li>
												</ul>
											</div>
											<!-- /center-service-items -->
										</div>
									</div>
									<!-- /service-items -->

									<div class="row haircut-info">
										<div class="col-md-12 text-center">
											<div class="groomit-btn eco-btn-outline btn-block"><span class="glyphicon glyphicon-ok text-success" aria-hidden="true"></span> HAIRCUT
											</div>
											<div class="line-divider"></div>
										</div>
									</div>
										<!-- /row-->

									<div class="row">
										<div class="col-xs-6">
											<ul class="package-specs pull-left">
												<li>Book 7 days<br> in advance</li>
												<li>Request your<br> favorite groomer</li>
											</ul>
										</div>
										<!-- /col -->
										<div class="col-xs-6">
											<ul class="package-specs pull-right">
												<li>Non-Refundable<br> Booking</li>
												<li>Prepaid</li>
											</ul>
										</div>
										<!-- /col -->
									</div>
									<!-- /row-->
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

							<div class="item select-service {{ Schedule::getCurrentPackageId() == 2 ? 'selected' : '' }}" id="service-silver" onclick="select_package(2)">
								<h3 class="service-title silver-bg"><span class="pull-left">SILVER</span><span class="pull-right" id="silver_package_price">${{ number_format($silver->denom, 2) }}</span><span class="clearfix"></span></h3>

								<div class="service-body">
									<div class="row service-items">
										<div class="col-xs-12">
											<div class="center-service-items">
												<ul class="text-left">
													<li>
														<p><i class="fas fa-check"></i><span>Dry Brush Out</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Bath</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Coat Conditioning</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Ear Cleaning</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Nail Trim</span> </p>
													</li>
													<li>
														<p><i class="fas fa-check"></i><span>Blow Dry</span> </p>
													</li>
												</ul>
											</div>
											<!-- /center-service-items -->
										</div>
									</div>
									<!-- service-items -->

									<div class="row haircut-info">
										<div class="col-md-12 text-center">
											<div class="groomit-btn silver-btn-outline check-btn btn-block"><span class="glyphicon glyphicon-remove text-danger" aria-hidden="true"></span> NO HAIRCUT
											</div>
											<div class="line-divider"></div>
										</div>
									</div>
									<!-- /row-->
									<div class="row">
										<div class="col-xs-6">
											<ul class="package-specs pull-left">
												<li>Same day service<br> optional</li>
												<li>Request your<br> favorite groomer</li>
											</ul>
										</div>
										<!-- /col -->
										<div class="col-xs-6">
											<ul class="package-specs pull-right">
												<li>Full refund if you cancel by 6pm the day before</li>
												<li>Payment upon<br> completion</li>
											</ul>
										</div>
										<!-- /col -->
									</div>
									<!-- /row-->
								</div>
								<!-- /service-items -->

								<div class="row">
									<div class="col-md-12 text-center">
										<button type="button" class="groomit-btn white-btn check-btn btn-block">SELECT</button>
									</div>
								</div>
								<!-- row -->
							</div>
							<!-- /select-service -->


						</div>
						<!--/owl -->
					</div>
					<!-- /col -->
				</div>
				<!-- row -->
			</div>
			<!-- /container -->
		</section>
		<section id="next-btn">
			<div class="container">
				<div class="row">
					<!--<div class="col-md-12 text-center"> <a class="groomit-btn red-btn big-btn" href="date-time.html">CONTINUE</a> </div>-->
					<div class="col-md-12 text-center"><a class="groomit-btn rounded-btn red-btn big-btn" id="btn_continue" style="display:{{ Schedule::getCurrentPackageId() ? '' : 'none' }};" href="javascript:go_to_next();">CONTINUE</a>
					</div>
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

	<!-- OUT OF SERVICE MODAL -->
	<!--<div class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" id="block-appointments">
		<div class="modal-dialog modal-sm" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<h3 class="text-center red-link">Attention!</h3>
					<br><br>
					<p class="text-center">
						Accepting bookings now in NY, NJ & PA.
					</p>
					<br><br>
					<p class="text-center" style="padding: 0 10px;">
						<button class="btn rounded-btn black-btn groomit-btn" type="button" data-dismiss="modal" aria-label="Close">
							Close
						</button>
					</p>
				</div>
			</div>
		</div>
	</div>-->
	<!-- /MODAL-->

	<script type="text/javascript">

		function select_size( size ) {
			$( '#size' ).val( size );
			//$('#frm_submit').submit();

			$.ajax( {
				url: '/user/schedule/select-dog/update-size',
				data: {
					_token: '{!! csrf_token() !!}',
					size: size
				},
				cache: false,
				type: 'post',
				dataType: 'json',
				success: function ( res ) {
					if ( $.trim( res.msg ) === '' ) {

						$( '#gold_package_price' ).text( '$' + parseFloat( res.gold.denom ).toFixed( 2 ) );
						$( '#silver_package_price' ).text( '$' + parseFloat( res.silver.denom ).toFixed( 2 ) );
                        $( '#eco_package_price' ).text( '$' + parseFloat( res.eco.denom ).toFixed( 2 ) );
						$( '#current_sub_total' ).text( '$' + parseFloat( res.current_sub_total ).toFixed( 2 ) );

					} else {
						myApp.showError( res.msg );
					}
				}
			} );
		}

		function select_package( package_id ) {

			$( '#btn_continue' ).hide();

			$.ajax( {
				url: '/user/schedule/select-dog/update-package',
				data: {
					_token: '{!! csrf_token() !!}',
					package_id: package_id
				},
				cache: false,
				type: 'post',
				dataType: 'json',
				success: function ( res ) {
					if ( $.trim( res.msg ) === '' ) {
                        $( '#package' ).val(package_id);
						$( '#current_package_name' ).text( res.current_package.prod_name );
						$( '#current_sub_total' ).text( '$' + parseFloat( res.current_sub_total ).toFixed( 2 ) );
						$( '#btn_continue' ).show();
					} else {
						alert( res.msg );
						window.location.href = '/user/schedule/select-dog';
					}
				}
			} );
		}

		function go_to_next() {
			var size = $( '#size' ).val();
			if ( $.trim( size ) === '' ) {
				myApp.showError( 'Please select size first!' );
				return;
			}

			var package = $( '#package' ).val();
			if ( $.trim( package ) === '' ) {
				myApp.showError( 'Please select package first!' );
				return;
			}

			$( '#frm_submit' ).prop( 'action', '/user/schedule/select-addon' );
			$( '#frm_submit' ).submit();
		}

		//Trigger Mixpanel Analytics event
		//mixpanel.track("Start Booking");

		//Trigger Segment event (Analytics)
		analytics.track("Start Booking");

	</script>
	@stop
