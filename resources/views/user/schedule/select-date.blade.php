@extends( 'user.layout.default' )
@section( 'content' )
	<link href="/desktop/css/appointment.css?v=1.0.5" rel="stylesheet">
	<div class="container-fluid" id="progress-bar">
		<div class="row">
			<div class="col-xs-2 line-status complete"></div>
			<div class="col-xs-2 line-status complete"></div>
			<div class="col-xs-2 line-status complete"></div>
			<div class="col-xs-2 line-status"></div>
			<div class="col-xs-2 line-status"></div>
			<div class="col-xs-2 line-status"></div>
		</div>
		<!-- row -->
	</div>
	<!-- /progress-bar -->
	<div id="main">
		<!-- ALREADY A USER -->
		@if (!Auth::guard('user')->check())
			<section id="isUser">
				<div class="container">
					<div class="row">
						<div class="col-sm-10 col-sm-offset-1">
							<div class="row">
								<div class="col-md-5 col-md-offset-1 col-xs-6 text-center">
									<h3>Already a user?</h3>
									<a href="javascript:show_login()" class="groomit-btn red-btn rounded-btn">SIGN IN</a>
								</div>
								<div class="col-md-5 col-xs-6 text-center">
									<h3>New User?</h3>
									<a href="javascript:show_register()" class="groomit-btn black-btn rounded-btn">SIGN UP</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
	@endif

	<!-- DATE-TIME -->
		<section id="date-time">
			<div class="container">
				<div class="row">
					<div class="col-xs-12 text-center">
						<h3>SELECT DATE AND TIME</h3>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1 text-center" id="dt-container">
						<form action="/user/schedule/select-date/post" method="post" id="frm_submit">
							@if (!Auth::guard('user')->check())
								@php($disabledTag = 'disabled')
							@else
								@php($disabledTag ='')
							@endif
							{!! csrf_field() !!}
							<input type="hidden" name="groomer_id" id="groomer_id" value="">
							<fieldset>
								<!-- Groomer Type -->
								<div class="row" id="groomer_type">
									<div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 text-center">
										@if( (isset($num_favs) && $num_favs != 0) )
											<div class="form-group" >
												<label class="control-label">GROOMER</label>
												<div class="btn-group btn-group-justified" data-toggle="buttons" id="select_groomer" >
													<label class="btn btn-st-opt"  onclick="javascript:reset_all('N');" id="btn_next"  >
														<input type="radio" name="select_groomer" value="select_available_groomer"  >
														<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
														Next Available
													</label>
													<label class="btn btn-st-opt active" onclick="javascript:reset_all('F');" id="btn_fav" >
														<input type="radio" name="select_groomer" checked value="select_fav_groomer"   >
														<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
														<span class="hidden-xxs">Only </span><span class="hidden-xs">My </span>Favorite | $10
													</label>
												</div>
											</div>
										@else
											<input type="hidden" name="select_groomer"  value="select_available_groomer" >
										@endif
									</div>
								</div>

								<!-- Select Fav Groomer -->
								@if( (isset($num_favs) && $num_favs != 0))
									<div class="row" id="fav_groomers" >
										<div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 text-center">
											<div class="form-group">
												<label class="control-label">FAVORITE GROOMERS</label>
												<div class="dropdown" id="groomers-list" >

													@if ($num_favs == 1)
														<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenuGroomer" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
															@foreach ($favs as $fav)
																<span class="groomer-avatar-xs"
																	  style="background-image: url(data:image/png;base64,{{ $fav->pic }});">
															</span>{{ $fav->name }}
															@endforeach
														</button>
													@else
														<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenuGroomer" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
															Select Groomer
														</button>
														<ul class="dropdown-menu" aria-labelledby="dropdownMenuGroomer">
															@if (count(array($favs)>0))
																@foreach ($favs as $fav)
																	<li><a href="javascript:selectGroomer({{ $fav->groomer_id }});" id="groomer_{{ $fav->groomer_id }}">
																		<span class="groomer-avatar-xs" style="background-image: url(data:image/png;base64,{{ $fav->pic }});">
																		</span>{{ $fav->name }}
																		</a>
																	</li>
																@endforeach
															@endif
														</ul>
													@endif
												</div>
											</div>
										</div>
									</div>
								@endif

								<div class="row">
									<div class="col-md-5 col-md-offset-1 col-sm-6 text-center">
										<div class="form-group">
											<label class="control-label" for="">DATE</label>
											<input class="form-control form-control-select-date date" id="date" name="date"
												   value="Please Select" type="text"
												   placeholder="Please Select" required  autocomplete="off" readonly
													{{$disabledTag}} />
											<em style="font-size: 12px;">Same Day Booking: +$20.00</em>
										</div>
										<!-- /form-group -->
									</div>
									<!-- col-4 -->

									<!-- /col-4 -->
									<!-- Arrival Time -->
									<div class="col-md-5 col-sm-6 text-center">
										<div class="form-group" style="margin-bottom: 0;">
											<label class="control-label" for="">ARRIVAL TIME</label>
											<input type="text" class="form-control" name="time" id="time" value="" autocomplete="off"  readonly />
											<input type="hidden" class="form-control" name="time_id" id="time_id" value="" />
										</div>
										<!-- /form-group -->
									</div>
									<!-- /col-4 -->
								</div>
								<!-- /row -->
								<br>
								<div class="row">
									<div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 text-center">
										<div class="form-group">
											<label class="control-label" for="grooming_place">WHERE CAN YOUR PET BE GROOMED?</label>
											<select class="form-control" id="grooming_place" name="grooming_place" required onchange="showInput()" {{$disabledTag}}>
												<option value="">Please Select</option>
												<option value="C">Countertop/Table</option>
												<option value="B">Bathtub</option>
												<option value="D">Dog wash station in my building</option>
												<option value="O">Others</option>
											</select>
											<input type="text" class="form-control" id="other_grooming_place" name="other_grooming_place" value="" style="display:none;" maxlength="200" placeholder="Please, add more details" />
										</div>
										<!-- /form-group -->
									</div>
									<!-- col-6 -->
								</div>
								<!-- /row -->
								<div class="row">
									<div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
										<!-- /form-group -->
										<div class="form-group text-left cont-checkboxes">

											<div class="checkbox">
												<label for="s_bathtub_and_towel">
													<input type="checkbox" name="s_bathtub_and_towel" id="s_bathtub_and_towel">
													<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
													I have bathtub/sink & towels available.
												</label>
											</div>

											@if (in_array(Schedule::getCurrentPackageId(), [28, 29]))
												<div class="checkbox">
													<label for="non_refundable_booking">
														<input type="checkbox" name="non_refundable_booking" id="non_refundable_booking">
														<span class="cr">
                                                    <i class="cr-icon glyphicon glyphicon-ok"></i>
                                                </span>Non-refundable Booking
														<span class="open-non-r">&nbsp;
                                                    <img src="/desktop/img/ionicons_2-0-1_ios-information-outline_48_0_bd1a2a_none.png"
														 width="18" height="18" alt="More Info"/>
                                                </span>
													</label>

												</div>
											@endif

											<div class="checkbox">
												<label for="accept_terms">
													<input type="checkbox" name="accept_terms" id="accept_terms">
													<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i>
                                                </span>
													I accept
													<a target="_blank"
													   href="/terms-privacy">
														<u>terms & conditions</u>
													</a>.
												</label>

											</div>
											<!-- /checkbox -->
										</div>
										<!-- /form-group -->
									</div>
								</div>
								<div class="row">
									<div class="col-xs-12 text-center">
										<a href="/user/schedule/select-pet"
										   class="groomit-btn black-btn rounded-btn big-btn space-btn" disabled><img
													class="arrow-back" src="/desktop/img/arrow-back.png" width="14" height="18"
													alt="Back" /> BACK &nbsp;</a>
										<a class="groomit-btn red-btn rounded-btn big-btn" {{$disabledTag}}
										onclick="book_now()">BOOK NOW</a>
									</div>
								</div>
								<!-- row -->
							</fieldset>
						</form>
					</div>
					<!-- col-10 -->
				</div>
				<!-- /row -->
			</div>
			<!-- /container -->
		</section>
	</div>

	<!-- GROOMER AVAILABILITY MODAL -->
	<div class="modal fade" id="groomer-availability" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-xs-12">
							<h3 class="ga__title">Date</h3>
							<div class="row">
								<div class="col-sm-8 col-sm-offset-2 text-center form-horizontal">
									<div class="form-group">
										<label class="control-label col-xs-4" for="groomer_calendar">Groomer:</label>
										<div class="col-xs-8">

											<select class="form-control groomer__calendar" id="groomer_calendar" name="groomer_calendar" onchange="updateGroomer($(this).val())">
												<option value="0">Next Available</option>
												@if (count(array($favs)>0))
													@foreach ($favs as $fav)
														<option value="{{ $fav->groomer_id }}">{{ $fav->name }} | $10 </option>
													@endforeach
												@endif
											</select>
										</div>
									</div>
								</div>
							</div>
							<!-- Mobiscroll calendar -->
							<div class="md-listview-rendering-data" id="appt__events">
								<div class="row">
									<div class="col-md-6">
										<div id="demo-listview-rendering"></div>
									</div>
									<div class="col-md-6">
										<!-- Events list -->
										<div class="appt__list">
											<h3 class="appt__list-title text-center">
												<span class="appt__title-date"></span>
											</h3>
											<div class="md-demo-listview-rendering">
												<ul id="demo-listview-rendering-data" class="mbsc-cloak"></ul>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- /appt__events -->
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /MODAL-->



	<script type="text/javascript">

		var selectedGroomer = 0; //By default, Groomer 0 means available for all day.
		$('#groomer_id').val( 0 ) //By default, Groomer 0 is set.



		var onload_func = window.onload;
		window.onload = function() {
			if (onload_func) {
				onload_func();
			}

			@if( isset($num_favs) && $num_favs == 1 ) //Initialize groomer ID if only 1 Fav exist.
			setGroomer( {{ $favs[0]->groomer_id }} );
			@endif

			//load_times();

			//Do not use old calendar only when 'Next Available groomers.
			//$(".dtp").css("display", "none");

			selectedGroomer = $('#groomer_id').val();
			$('#groomer-availability').on('shown.bs.modal', function () { //Shold be here, because the event should not registered multiple times.
				initMobiscroll();
			});

			$(document).on("focus", "#date", function() {
				//$(".dtp").css("display", "none");

				$('#groomer-availability').modal('show');
				// $('#groomer-availability').on('shown.bs.modal', function () { //Should not be here.
				// 	initMobiscroll();

			});

		}


		function showInput() {

			var selectedGroomingPlace =  $("#grooming_place").val();

			if (selectedGroomingPlace == "O") {
				$("#other_grooming_place").css("display", "block");
				$("#other_grooming_place").attr('required', 'required');
			} else {
				$("#other_grooming_place").css("display", "none");
				$("#other_grooming_place").removeAttr('required');
			}
		}

		function setGroomer(groomerID) { //when only one Fav exist.

			selectedGroomer = groomerID;
			$('#groomer_id').val(groomerID);

			$('#groomer_calendar option[value="'+groomerID+'"]').prop("selected", true); //Setup the groomer ID at popup.
		}

		function selectGroomer(groomerID) { //when multi Fav exist, in main page.

			selectedGroomer = groomerID;
			$('#groomer_id').val(groomerID);

			$('#groomer_calendar option[value="'+groomerID+'"]').prop("selected", true); //Setup the groomer ID at popup

			if( groomerID > 0) {
				var clickedGroomer = $('#groomer_' + groomerID).html();
				$('#dropdownMenuGroomer').html(clickedGroomer);                              //Setup the groomer ID at main


			}else {
				//$('#dropdownMenuGroomer').html('Select Groomer');                              //Setup the groomer ID at main

			}

			//reset date & time
			$('#date').val('Please Select');
			$('#time').val('Please Select');
			$('#time_id').val('');

		}

		function updateGroomer(newGroomer) { //When a Fav groomer is changed, at popup, including 'Next Available' option

			var newGroomerNumber = parseInt(newGroomer);
			selectGroomer(newGroomerNumber);

			@if( (isset($num_favs) && $num_favs != 0) )
			if(newGroomerNumber > 0) {
				$('#btn_fav').addClass('active');
				$('#btn_next').removeClass('active');
				$('input[name="select_groomer"][value="select_fav_groomer"]').prop('checked', true); //Toggle box at main
				$('#fav_groomers').show(); //show 'fav groomer lists at main.

			}else {

				$('#btn_next').addClass('active');
				$('#btn_fav').removeClass('active');
				$('input[name="select_groomer"][value="select_available_groomer"]').prop('checked', true); //toggle box at main
				$('#fav_groomers').hide(); //hide 'fav groomer lists at main.
			}
			@else
			$("#select_groomer").val( 'select_fav_groomer' ) ;
			@endif

			initMobiscroll();
			$("#demo-listview-rendering-data").empty();
		}

		function reset_all( selected_val){  //called when N/F buttons at main page, not popup page.

			if (  selected_val == "N" ) {
				$('#fav_groomers').hide(); //hide 'fav groomer lists at main.

				updateGroomer(0); //reset as if it's clicked 'Next available'.
			}else {

				$('#fav_groomers').show(); //show 'fav groomer lists at main.
			}

			$('#date').val('Please Select');
			$('#time').val('Please Select');
			$('#time_id').val('');


		}


		function set_time( new_id, new_time , obj ){
			$('#time').val( new_time );
			$('#time_id').val( new_id );

			var elem = ".at-option";
			var elemClass = "selected";

			if ($(obj).hasClass(elemClass)) {
				$(obj).removeClass(elemClass);
			} else {
				$(elem).removeClass(elemClass);
				$(obj).addClass(elemClass);
			}


		}


		function book_now() {

			@if( (isset($num_favs) && $num_favs != 0) )
			if ( ( $('input[name=select_groomer]:checked').val() == 'select_fav_groomer' ) &&
					( $('#groomer_id').val() == 0 )
			) {
				alert("Please choose your favorite groomer first !")
			}
			@endif

			var grooming_place = $('#grooming_place').val();
			if (grooming_place == 0) {
				alert('Please select WHERE CAN YOUR PET BE GROOMED?');
				return;
			}

			var bathtub_and_towel = $('#s_bathtub_and_towel').is(':checked');
			if (!bathtub_and_towel) {
				alert('Please make sure you have bathtub/sink and towel ready!');
				return;
			}


			@if(in_array(Schedule::getCurrentPackageId(), [28, 29]))
			var non_refundable_booking = $('#non_refundable_booking').is(':checked');
			if (!non_refundable_booking) {
				alert('Please make sure this is Non-refundable booking!');
				return;
			}
			@endif


			if (!$('#accept_terms').is(':checked')) {
				alert('Please accept terms & conditions!');
				return;
			}


			$('#frm_submit').submit();
		}


		function initMobiscroll() {

			if( selectedGroomer === undefined) { //get it from selected groomer_id, especially when 1 fav exist.
				selectedGroomer = $('#groomer_id').val( );
			}

			//Mobiscroll
			mobiscroll.settings = {
				theme: 'material',
				themeVariant: 'light'
			};

			var $ul = $('#demo-listview-rendering-data'),
					cal,
					list,
					events = [],
					formatDate = mobiscroll.util.datetime.formatDate;

			list = $ul.mobiscroll().listview({
				theme: 'groomit',
				swipe: false,
				onItemTap: function (event, inst) {
					if (  event.target.getAttribute('data-availability') == 'AV') {
						$('#time').val( event.target.getAttribute('data-desc') );
						$('#time_id').val( event.target.getAttribute('data-id') );

						$('#groomer-availability').modal('hide');

					}
				},
			}).mobiscroll('getInst');

			var today = new Date();
			var min_date = new Date();
			var max_date = new Date();

{{--			@if( $state == 'FL')--}}
{{--		    	@if (in_array(Schedule::getCurrentPackageId(), [28, 29]))--}}
{{--			        var nov = new Date(2020,9,25 ); //10/25/2020--}}

{{--					if( today < nov) {--}}
{{--						min_date =  new Date( 2020,9,25 );--}}
{{--						max_date =  new Date( 2020,9,25 );--}}

{{--					    min_date.setDate( nov.getDate() + 7 );--}}
{{--				    	max_date.setDate( nov.getDate() + 21 );--}}
{{--				    }else {--}}
{{--					    min_date.setDate( min_date.getDate() + 7 );--}}
{{--					    max_date.setDate( max_date.getDate() + 21 );--}}
{{--				    }--}}
{{--			    @else--}}
{{--			        var nov = new Date( 2020,10,1 ); //11/01/2020--}}

{{--			    	if( today < nov ) {--}}
{{--						min_date =  new Date( 2020,10,1 );--}}
{{--						max_date =  new Date( 2020,10,1 );--}}

{{--					    min_date.setDate(nov.getDate());--}}
{{--					    max_date.setDate(nov.getDate() + 14);--}}
{{--			    	}else {--}}
{{--				    	min_date.setDate(min_date.getDate());--}}
{{--					    max_date.setDate(max_date.getDate() + 14);--}}
{{--				    }--}}
{{--			    @endif--}}
{{--			@else--}}
{{--			    @if (in_array(Schedule::getCurrentPackageId(), [28, 29]))--}}
{{--			        min_date.setDate( min_date.getDate() + 7 );--}}
{{--				    max_date.setDate( max_date.getDate() + 21 );--}}
{{--			    @else--}}
{{--			        min_date.setDate( min_date.getDate() );--}}
{{--			        max_date.setDate( max_date.getDate() + 14 );--}}
{{--			    @endif--}}
{{--            @endif--}}

			var tomorrow = new Date();
			tomorrow.setDate( tomorrow.getDate() + 1 );


			var color_setup = [] ;
			var invalid_setup = [] ;

			$.ajax({
				url: '/user/schedule/select-date/load-groomer-calendar-availability',
				data: {
					_token: '{{ csrf_token() }}',
					groomer_id: selectedGroomer
				},
				cache: false,
				type: 'post',
				dataType: 'json',
				success : function (res) {
					if ($.trim(res.msg) === '') {
						// console.log('min_date:' + res.min_date);
						// console.log('min_date:' + min_date);
						min_date = new Date( res.min_date + " 00:00:00");
						max_date = new Date( res.max_date + " 00:00:00");

						$.each(res.availability, function(i, o) {

							var dt =  o.date ;

							if( o.available == 'AV' ) {
								var new_item = { d: dt , background:  '#68b081' } ;
							}else{
								var new_item = { d: dt , background:  '#dc4d55' } ;
							}

							if( ( o.full_date >= formatDate('yyyy-mm-dd', min_date )) && ( o.full_date <= formatDate('yyyy-mm-dd', max_date)) ) {
								color_setup.push(new_item);

								if( o.available !== 'AV') {
									var invalid_item = { d: dt  } ;
									invalid_setup.push( invalid_item );
    							}
							}
						})

						cal = $('#demo-listview-rendering').mobiscroll().eventcalendar({
							display: 'inline',
							theme: 'groomit',
							defaultValue: null,
							min: min_date,
							max: max_date,
							firstDay: 1,
							labels: [
							    @if (in_array(Schedule::getCurrentPackageId(), [28, 29]))
								{ d: today,  text: '', color: 'transparent' }
								@else
								{ d: today, text: '+$20', color: 'transparent' }
								@endif
							],
							colors: color_setup ,
							//invalid: invalid_setup ,
							view: {
								calendar: {
									type: 'month'
								}
							},
							onPageLoaded: function (event, inst) {
									$('.appt__title-date').text( "Please select a date !");
							},
							onDayChange: function (event, inst) { //not at init, only after change the date.
								date = event.date;   //Set by selected date.
								getEvents();
								$('#date').val(formatDate('yyyy-mm-dd', date));
							}
						}).mobiscroll('getInst');

						cal.navigate( );            //Setup nodate by default.

					}
				}
			});


			function getEvents( ) {

				var i, item, book = "";
				list.settings.animateAddRemove = false;
				$ul.empty(); //it works too.

				var dayName = date.toLocaleDateString('en-US', {weekday: 'short'});

				$('.appt__title-date').html('<span style="color: #858585;">' + dayName + ' </span>' + formatDate('mm/dd/yyyy', date));


				$.ajax({
					url: '/user/schedule/select-date/load-groomer-calendar2/' + selectedGroomer + '/' + formatDate('yyyy-mm-dd', date)  ,
					dataType: 'json',
					success : function (data) {
						var titleFlexible,titleSpecific, flexibleTimes, specificTimes, itemContent;
                        $(".appt__list").addClass("appt__list--time");

    					//Set html layout for the titles of each list
						titleFlexible = '<li data-role="list-divider"><strong>Flexible Time </strong><a data-toggle="tooltip" data-placement="right" title="Highest possibility to get groomer assigned within time window">' +
										'<img src="/desktop/img/ionicons_2-0-1_ios-information-outline_48_0_bd1a2a_none.png" width="22" height="22" alt="More Info"></a></li>';

						titleSpecific = '<li class="appt__title-specific" data-role="list-divider"><strong>Specific Time </strong><a data-toggle="tooltip" data-placement="right" title="We value your time and will try to confirm time & groomer within your time window">' +
										'<img src="/desktop/img/ionicons_2-0-1_ios-information-outline_48_0_bd1a2a_none.png" width="22" height="22" alt="More Info"></a></li>';

						//Filter times according to type
						flexibleTimes = $.grep(data, function(t) {
							return t.title !== "Specific Time";
						});

						specificTimes = $.grep(data, function(t) {
							return t.title === "Specific Time";
						});

						//Add all Flexible times to the list, starting with the title
						list.add('divider-flexible', mobiscroll.$(titleFlexible));

						for (var i = 0; i < flexibleTimes.length; i++) {
							item = flexibleTimes[i];

							item.text = (item.availability == "NA") ? "Not Available" : "Available";
							if (item.availability == "AV") {
								book = '<button mbsc-button class="groomit-btn red-btn rounded-btn">Book</button>';
							} else {
								if (item.area_name != "") {
									book = '<span class="item__booked-icon"></span>Booked in ' + item.area_name;
								} else {
									book = "";
								}
							}

							list.add(item.id, mobiscroll.$(
									'<li data-availability="' + item.availability + '" data-desc="' + item.desc + '" data-id="' + item.id + '">' +
									'<div class="item__row">' +
									'<div class="item__col item__col--time">' + item.desc + '</div>' +
									'<div class="item__col item__col--text">' + item.text + '</div>' +
									'<div class="item__col item__col--location">' + book + '</div>' +
									'</div>' + '</li>'
							));
						}

						if(specificTimes.length > 0) {
							//Add all Specific times to the list, starting with the title
							list.add('divider-specific', mobiscroll.$(titleSpecific));

							// var starting_time = '08:00:00';
							// if( formatDate('yyyy-mm-dd', date) == formatDate('yyyy-mm-dd', today) ) {
							// 		var hr = today.getHours();
							// 		hr = hr + 2 ;
							// 		starting_time = hr + ":00:00" ;
							// }
							for (var i = 0; i < specificTimes.length; i++) {
								item = specificTimes[i];

								item.text = (item.availability == "NA") ? "Not Available" : "Available";
								if (item.availability == "AV") {
									book = '<button mbsc-button class="groomit-btn red-btn rounded-btn">Book</button>';
								} else {
									if (item.area_name != "") {
										book = '<span class="item__booked-icon"></span>Booked in ' + item.area_name;
									} else {
										book = "";
									}
								}

								list.add(item.id, mobiscroll.$(
										'<li data-availability="' + item.availability + '" data-desc="' + item.desc + '" data-id="' + item.id + '">' +
										'<div class="item__row">' +
										'<div class="item__col item__col--time">' + item.desc + '</div>' +
										'<div class="item__col item__col--text">' + item.text + '</div>' +
										'<div class="item__col item__col--location">' + book + '</div>' +
										'</div>' + '</li>'
								));

							}
						}


						//Remove interaction styles from UI ????
						// $("#demo-listview-rendering-data li").each(function() {
						// 	$(this).removeClass("mbsc-lv-item-actionable");
						// });

						$ul.trigger('mbsc-enhance');
						list.settings.animateAddRemove = true;

					}
				});
			}
		}

	</script>
@stop
