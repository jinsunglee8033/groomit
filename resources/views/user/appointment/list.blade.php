@extends('user.layout.default')
@section('content')
    <link href="/desktop/css/appointment.css" rel="stylesheet" type="text/css">
    <link href="/desktop/css/dashboard.css" rel="stylesheet" type="text/css">

    <!--    <style type="text/css">
            .pet-photo {
                width: 90px !important;
                height: 90px !important;
            }
        </style>-->

    <!-- MY APPOINTMENTS -->
    <section id="my-appointments">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h3><span class="title-icon"><img src="/desktop/img/icon-my-appointments.png" width="61" height="65"
                                                      alt="My Appointments"></span> MY APPOINTMENTS</h3>
                </div>
            </div>
            <!-- /row -->
            <div class="row after-title">
                <div class="col-lg-10 col-lg-offset-1">
                    <div class="row">

                    @if (empty($recent) && empty($upcoming))
                        <!-- NO APPOINTMENTS AT ALL -->
                            <div class="col-lg-10 col-lg-offset-1" id="no-bookings">
                                <div class="row" id="schedule">
                                    <div class="col-xs-6">
                                        <div class="media my-card text-center"><a href="/user/schedule/select-dog">
                                                <div class="nb-wrapper">
                                                    <div class="media-left media-middle"><img
                                                                src="/desktop/img/dog-icon.svg"
                                                                class="media-object" width="120" height="120"
                                                                alt="Schedule Dog"></div>
                                                    <div class="media-body media-middle">
                                                        <h4 class="media-heading text-center">Schedule</h4>
                                                    </div>
                                                </div>
                                            </a></div>
                                        <!-- media -->
                                    </div>
                                    <!-- /col-6 -->
                                    <div class="col-xs-6">
                                        <div class="media my-card text-center"><a href="/user/schedule/select-cat">
                                                <div class="nb-wrapper">
                                                    <div class="media-left media-middle"><img class="media-object"
                                                                                              src="/desktop/img/cat-icon.svg"
                                                                                              width="120" height="120"
                                                                                              alt="Schedule Cat"></div>
                                                    <div class="media-body media-middle">
                                                        <h4 class="media-heading text-center">Schedule</h4>
                                                    </div>

                                                </div>
                                            </a></div>
                                        <!-- media -->
                                    </div>
                                    <!-- /col-6 -->
                                </div>
                                <!-- /row -->
                            </div>
                            <!-- /col-10 -->
                    @endif

                    @if (!empty($recent))
                        <!-- LAST BOOKING -->
                            <div class="col-sm-6">
                                <div class="my-card">
                                    <h4 class="text-center">Last Booking</h4>
                                    <div class="appointment-carousel owl-carousel">
                                        @foreach ($recent as $ap)
                                            <div class="bookings-history">
                                                <div class="pets-container">
                                                    <!-- PET -->
                                                    <div class="row">
                                                        @if (count($ap->pets) > 0)
                                                            @foreach ($ap->pets as $o)
                                                                <div class="col-md-6 text-center">
                                                                    <div class="media history-pet">
                                                                        <div class="media-left media-middle">

                                                                        @if (empty($o->photo))
                                                                            <!-- pet avatar -->
                                                                                <div class="bh-pet-avatar">
                                                                                    <div class="table-cell media-middle"><img
                                                                                                src="/desktop/img/{{ $o->type }}-icon.svg" width="53"
                                                                                                height="53" alt="{{ $o->pet_name }}"></div>
                                                                                </div>
                                                                        @else
                                                                            <!-- pet photo -->
                                                                                <img src="data:image/png;base64,{{ $o->photo }}"
                                                                                     class="img-circle pet-photo" width="221"
                                                                                     height="225" alt="{{ $o->pet_name }}">
                                                                            @endif
                                                                        </div>
                                                                        <div class="media-body media-middle">
                                                                            <p class="media-heading pet-name text-uppercase"><strong>{{ $o->pet_name }}</strong>
                                                                            </p>
                                                                            <p class="package-name">{{ $o->package_name }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <!-- media -->
                                                                </div>
                                                                <!-- /col -->
                                                            @endforeach
                                                        @endif

                                                    </div>
                                                    <!-- /row -->
                                                </div>
                                                <!-- /pets-container -->

                                                <div class="history-content">
                                                    <!-- DATE / TIME / ADDRESS -->
                                                    <div class="row history-dt">
                                                        <div class="col-xs-6 text-center"><span class="date-time-icon"><img
                                                                        src="/desktop/img/calendar-icon.png" width="22"
                                                                        height="22" alt="Date and Time"></span>
                                                            <p><strong>{{ substr(Carbon\Carbon::parse($ap->accepted_date)->format('Y-m-d h:i A'), 0, 10) }}</strong></p>
                                                            <p>{{ substr(Carbon\Carbon::parse($ap->accepted_date)->format('Y-m-d h:i A'), 11) }}</p>
                                                        </div>
                                                        <!-- /col -->
                                                        <div class="col-xs-6 text-center"><span class="address-icon"><img
                                                                        src="/desktop/img/address-icon.png" width="auto"
                                                                        height="22" alt="Date and Time"></span>
                                                            <p>{{ $ap->address }}</p>
                                                        </div>
                                                        <!-- /col -->
                                                    </div>
                                                    <!-- /row -->

                                                    <!-- GROOMER / RATE / TIP -->
                                                    <div class="row history-groomer">
                                                        <div class="col-sm-6 col-xs-5 text-center">
                                                            <div class="cell-groomer">
                                                                @if (isset($ap->groomer->photo))
                                                                    <img src="data:image/png;base64,{{ $ap->groomer->photo }}"
                                                                         class="img-circle" width="90"
                                                                         height="90" alt="Groomer">
                                                                @else
                                                                    <span class="groomer-icon"><img
                                                                                src="/desktop/img/icon-service-dry-brush-out.png"
                                                                                width="30" height="30" alt="Groomer"></span>
                                                                @endif
                                                                <p>Groomer</p>
                                                                <p><strong>{{ empty($ap->groomer) ? '' : ($ap->groomer->first_name . ' ' . $ap->groomer->last_name) }}</strong></p>
                                                                <p><span class="fav-groomer-{{ $ap->groomer_id }} glyphicon glyphicon-heart{{ !empty($ap->groomer) && $ap->groomer->favorite == true ? '' : '-empty' }}" onclick="toggle_favorite_groomer('{{ $ap->appointment_id }}', '{{ $ap->groomer_id }}')"></span></p>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6 col-xs-7">
                                                            <div class="cell-rating">
                                                                <p><strong>Rate</strong></p>

                                                                <!-- data-rating = initial/data-base value -->
                                                                <div class="starrr" id="{{ $ap->appointment_id }}" data-rating="{{ $ap->rating }}"></div>
                                                                <input type="hidden" class="rating" name="rating" value=""/>
                                                            </div>
                                                            <div class="cell-tip">
                                                                <p><strong>Tip</strong></p>

                                                                <input type="hidden" id="sub_total_{{ $ap->appointment_id }}" value="{{ $ap->sub_total }}">

                                                                <!-- Tip options -->
                                                                <div class="btn-group btn-group-justified tip-options {{ is_null($ap->tip) ? '' : 'hidden' }}"
                                                                     data-toggle="buttons">
                                                                    <label class="btn btn-st-opt" onclick="give_tip('{{ $ap->appointment_id }}', 15)">
                                                                        <input type="radio" name="" autocomplete="off" checked>
                                                                        <span class="glyphicon glyphicon-ok"
                                                                              aria-hidden="true"></span>$15</label>
                                                                    <label class="btn btn-st-opt" onclick="give_tip('{{ $ap->appointment_id }}', 20)">
                                                                        <input type="radio" name="" autocomplete="off">
                                                                        <span class="glyphicon glyphicon-ok"
                                                                              aria-hidden="true"></span>$20</label>
                                                                    <label class="btn btn-st-opt text-uppercase red-btn">
                                                                        <input type="radio" name="" autocomplete="off">
                                                                        Other</label>
                                                                </div>

                                                                <!-- Other tip: Edit -->
                                                                <div class="other-tip-edit hidden">
                                                                    <div class="input-group">
                                                                        <label for="" class="table-cell media-middle">Other:</label>
                                                                        <div class="input-group-addon">$</div>
                                                                        <input type="number" id="other_tip_{{ $ap->appointment_id }}"
                                                                               class="form-control pull-left">
                                                                        <span class="input-group-btn">
                                                                <button class="btn red-btn groomit-btn" type="button"
                                                                        onclick="give_tip('{{ $ap->appointment_id }}')">
                                                                    <span class="glyphicon glyphicon-ok"></span>
                                                                </button>
                                                            </span></div>
                                                                    <span class="help-block" id="cancel-tip">Cancel</span>
                                                                </div>

                                                                <!-- Other tip -->
                                                                <p class="other-tip {{ is_null($ap->tip) ? 'hidden' : ''}}"">Tip given: $<span
                                                                        class="other-tip-val">{{ !is_null($ap->tip) ? number_format($ap->tip, 2) : '' }}</span></p>
                                                            </div>
                                                            <!-- /cell-tip -->
                                                        </div>
                                                        <!-- /col -->
                                                        <!--<div class="col-xs-12">
                                                            <textarea class="form-control comments-groomer" name="" placeholder="Comments"></textarea>
                                                        </div>-->
                                                    </div>
                                                    <!-- /row -->
                                                    <a href="#" data-toggle="modal" data-id="{{$ap->appointment_id}}" data-target="#reschedule-confirmation" class="open-reschedule groomit-btn outline-btn red-btn rounded-btn modify-appointment">
                                                        REBOOK
                                                    </a>
                                                </div>
                                                <!-- /history-content -->

                                            </div>
                                            <!-- /LAST BOOKING -->
                                        @endforeach

                                    </div>
                                    <!-- appointment-carousel -->
                                </div>
                                <!-- /my-card -->

                            </div>
                            <!-- /col-6 -->
                    @endif

                    @if (empty($upcoming) && !empty($recent) || !empty($upcoming) && empty($recent))
                        <!-- NO UPCOMING OR NO LAST BOOKING -->
                            <div class="col-sm-6" id="no-bookings">
                                <div class="media my-card text-center"><a href="/user/schedule/select-dog">
                                        <div class="nb-wrapper">
                                            <div class="media-left media-middle"><img src="/desktop/img/dog-icon.svg"
                                                                                      class="media-object" width="120"
                                                                                      height="120" alt="Schedule Dog"></div>
                                            <div class="media-body media-middle">
                                                <h4 class="media-heading text-center">Schedule</h4>
                                            </div>
                                        </div>
                                    </a></div>
                                <!-- media -->

                                <div class="media my-card text-center"><a href="/user/schedule/select-cat">
                                        <div class="nb-wrapper">
                                            <div class="media-left media-middle"><img class="media-object"
                                                                                      src="/desktop/img/cat-icon.svg"
                                                                                      width="120" height="120"
                                                                                      alt="Schedule Cat"></div>
                                            <div class="media-body media-middle">
                                                <h4 class="media-heading text-center">Schedule</h4>
                                            </div>
                                        </div>
                                    </a></div>
                                <!-- media -->

                            </div>
                            <!-- /col-6 -->
                    @endif

                    @if (!empty($upcoming))
                        <!-- UPCOMING -->
                            <div class="col-sm-6">
                                <div class="my-card">
                                    <h4 class="text-center">Upcoming</h4>
                                    <div class="appointment-carousel owl-carousel">
                                        @foreach ($upcoming as $ap)
                                            <div class="bookings-history">
                                                <div class="pets-container">
                                                    <!-- PET -->
                                                    <div class="row">
                                                        @foreach ($ap->pets as $o)
                                                            <div class="col-md-6 text-center">
                                                                <div class="media history-pet">
                                                                    <div class="media-left media-middle">
                                                                    @if (!isset($o->photo))
                                                                        <!-- pet avatar -->
                                                                            <div class="bh-pet-avatar">
                                                                                <div class="table-cell media-middle"><img
                                                                                            src="/desktop/img/{{ $o->type }}-icon.svg" width="53"
                                                                                            height="53" alt="{{ $o->pet_name }}"></div>
                                                                            </div>
                                                                    @else
                                                                        <!-- pet photo -->
                                                                            <img src="data:image/png;base64,{{ $o->photo }}"
                                                                                 class="img-circle pet-photo" alt="{{ $o->pet_name }}">
                                                                        @endif
                                                                    </div>
                                                                    <div class="media-body media-middle">
                                                                        <p class="media-heading pet-name text-uppercase"><strong>{{ $o->pet_name }}</strong>
                                                                        </p>
                                                                        <p class="package-name">{{ $o->package_name }}</p>
                                                                    </div>
                                                                </div>
                                                                <!-- media -->
                                                            </div>
                                                            <!-- /col -->
                                                        @endforeach

                                                    </div>
                                                    <!-- /row -->
                                                </div>
                                                <!-- /pets-container-->
                                                <div class="history-content">
                                                    <!-- DATE / TIME / ADDRESS -->
                                                    <div class="row history-dt">
                                                        <div class="col-xs-6 text-center"><span class="date-time-icon"><img
                                                                        src="/desktop/img/calendar-icon.png" width="22"
                                                                        height="22" alt="Date and Time"></span>
                                                            <p><strong>{{ substr(isset($ap->accepted_date) ? Carbon\Carbon::parse($ap->accepted_date)->format('Y-m-d h:i A') : $ap->reserved_at, 0, 10) }}</strong></p>
                                                            <p>{{ substr(isset($ap->accepted_date) ? Carbon\Carbon::parse($ap->accepted_date)->format('Y-m-d h:i A') : $ap->reserved_at, 11) }}</p>
                                                        </div>
                                                        <!-- /col -->
                                                        <div class="col-xs-6 text-center"><span class="address-icon"><img
                                                                        src="/desktop/img/address-icon.png" width="auto"
                                                                        height="22" alt="Date and Time"></span>
                                                            <p>{{ $ap->address }}</p>
                                                        </div>
                                                        <!-- /col -->
                                                    </div>
                                                    <!-- /row -->
                                                    <div class="row history-dt">
                                                        <div class="col-xs-6 text-center">
                                                            @if (!empty($ap->groomer) && isset($ap->groomer->profile_photo))
                                                                <img src="data:image/png;base64,{{ $ap->groomer->profile_photo }}"
                                                                     class="img-circle" width="90"
                                                                     height="90" alt="Groomer">
                                                            @else
                                                                <span class="groomer-icon"><img
                                                                            src="/desktop/img/icon-service-dry-brush-out.png"
                                                                            width="30" height="30" alt="Groomer"></span>
                                                            @endif
                                                            <p>Groomer</p>
                                                            @if (isset($ap->groomer))
                                                                <p class="set-required"><a onclick="groomer_modal()" class="red-link">{{ $ap->groomer->first_name . ' ' . $ap->groomer_last_name }}</a></p>
                                                            @else
                                                                <p class="set-required">Not assigned</p>
                                                            @endif
                                                        </div>
                                                        <!-- /col -->
                                                        <div class="col-xs-6 text-center">
                                                            <div class="well">
                                                                <p>Please have bathtub/sink and towels available.</p>
                                                            </div>
                                                        </div>
                                                        <!-- /col -->
                                                    </div>
                                                    <!-- /row -->


                                                    @if (isset($ap->pets[0]->package_name) && $ap->pets[0]->package_name != 'ECO')
                                                        <div class="row">
                                                            <div class="col-xs-12">
                                                                <p class="text-center"><em>Full refund if you cancel by <strong>6:00 pm</strong> the day before service time.</em></p>
                                                            </div>
                                                        </div>

                                                        <a href="#" data-toggle="modal" data-target="#modify-alert" data-appointment="{{ $ap->appointment_id }}" class="groomit-btn outline-btn red-btn modify-appointment rounded-btn">
                                                            MODIFY
                                                        </a>
                                                    @else
                                                        <div class="modify-appointment">
                                                        <!-- <div class="modify-appointment"><a href="#"
                                                                               class="groomit-btn outline-btn red-btn btn-block rounded-btn open-non-r" data-id="{{ $ap->appointment_id }}">CANCEL APPOINTMENT</a> -->
                                                            <p class="text-center"><strong>Eco Package can't be cancelled.<br>Please contact Customer Service.</strong></p>
                                                        </div>
                                                        <!-- /modify-appointment -->
                                                    @endif
                                                </div>
                                                <!-- /history-content -->
                                            </div>
                                            <!-- /NEXT BOOKING -->
                                        @endforeach

                                    </div>
                                    <!-- /owl-carousel-appointment -->

                                </div>
                                <!-- /my-card -->

                            </div>
                            <!-- /col-6 -->
                        @endif

                    </div>
                    <!-- /row -->
                </div>
                <!-- /col -->
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->




        <div class="modal fade modal-groomer" id="modal-groomer" tabindex="-1" role="dialog" >
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body text-center">
                        @if (!empty($ap->groomer))
                            <h4 class="modal-title text-center" >{{ $ap->groomer->first_name . ' ' . $ap->groomer->last_name
                         }}</h4>
                            <div class="bh-pet-avatar text-center">
                                <div class="table-cell media-middle">
                                    @if (!empty($ap->groomer->profile_photo))
                                        <img src="data:image/png;base64,{{ $ap->groomer->profile_photo }}" alt="Groomer">
                                    @else
                                        <img id="img_profile_photo" src="/images/upload-img.png"/>
                                    @endif
                                </div>
                            </div>
                            <p>
                                {{ $ap->groomer->bio }}
                            </p>
                            <div class="cell-rating">
                                <div class="starrr" id="appointment_id" data-rating="{{ round($ap->groomer->overall_rating) }}"></div>
                                <input type="hidden" class="rating" name="rating" value=""/>
                            </div>
                            <p><strong>{{ round($ap->groomer->overall_rating,2) }}</strong></p>
{{--                            <p><strong>Completed Groomings: {{ $ap->groomer->completed_qty }}</strong></p>--}}
                        @endif
                    </div>
                </div>
            </div>
        </div>






    </section>
    <!-- /my-appointments -->

    <!-- NON-REFUNDABLE BOOKING MODAL -->
    <div class="modal fade auto-width" id="non-refundable" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog auto-width" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <br><br>
                    <p class="text-center">100% Cancellation fee will occur</p>
                    <br><br>
                    <p class="text-center" id="back-form" style="padding: 0 10px;">
                        <button class="btn rounded-btn black-btn groomit-btn space-btn" data-dismiss="modal" aria-label="Close" type="button">Don't Cancel</button>
                        <button class="btn rounded-btn red-btn groomit-btn" type="button" disabled>Yes, Cancel</button>
                    </p>
                </div>
                <!-- /terms -->
            </div>
            <!-- /modal-content -->

        </div>
    </div>
    <!-- /MODAL-->

    @if (!empty($recent))
        <!-- RESCHEDULE APPOINTMENT CONFIRMATION MODAL -->
        <div class="modal fade auto-width" id="reschedule-confirmation" tabindex="-1" role="dialog" aria-labelledby="reschedule-title">
            <div class="modal-dialog auto-width" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close close-reschedule" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <h3 class="text-center" id="reschedule-title">Reschedule</h3>
                        <input type="hidden" name="reschedule_id" id="reschedule_id"/>
                    @foreach ($recent as $ap)

                        @if (count($ap->pets) > 0)

                            @if (count($ap->pets) > 1)
                                @php ($class = "col-xs-6")
                            @else
                                @php ($class = "col-xs-6 col-xs-offset-3")
                            @endif
                            <!-- PET -->
                                <div class="row-{{$ap->appointment_id}}  row-pet" style="display:none">
                                    @foreach ($ap->pets as $o)
                                        <div class="{{ $class }} text-center">
                                            <div class="history-pet">
                                            @if (empty($o->photo))
                                                <!-- pet avatar -->
                                                    <div class="bh-pet-avatar">
                                                        <img src="/desktop/img/{{ $o->type }}-icon.svg"
                                                             width="53" height="53" alt="{{ $o->pet_name }}">
                                                    </div>
                                                    <!-- pet photo -->
                                                @else
                                                    <img src="data:image/png;base64,{{ $o->photo }}"
                                                         class="img-circle pet-photo" width="90"
                                                         height="90" alt="{{ $o->pet_name }}">
                                                @endif
                                                <p class="text-center pet-name text-uppercase">
                                                    <strong>{{ $o->pet_name }}</strong></p>
                                                <p class="package-name text-center"><strong>{{ $o->package_name }}</strong>
                                                </p>
                                            </div>
                                            <!-- media -->
                                        </div>
                                        <!-- /col -->
                                    @endforeach
                                    <p class="text-center" style="padding: 0 10px;">
                                        <button class="btn rounded-btn black-btn groomit-btn space-btn cancel-reschedule" data-dismiss="modal" aria-label="Cancel" type="button">Cancel</button>
                                        <a href="/user/schedule/select-rebook/{{ $ap->appointment_id }}" class="btn rounded-btn red-btn groomit-btn" type="button" onclick="">Continue</a>
                                    </p>
                                </div>
                                <!-- /row -->
                            @endif

                        @endforeach

                    </div>
                    <!-- /modal-body -->
                </div>
                <!-- /modal-content -->

            </div>
        </div>
        <!-- /MODAL-->
    @endif

    <!-- MODIFY APPOINTMENT ALERT MODAL -->
    <div class="modal fade" id="modify-alert" tabindex="-1" role="dialog" aria-labelledby="modify-alert__title">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h3 class="text-center" id="modify-alert__title">I would like to</h3>
                    <div class="row">
                        <div class="col-xs-10 col-xs-offset-1">
                            <p class="text-center">
                                <a href="#" class="btn rounded-btn red-btn groomit-btn btn-block" type="button" id="modify-alert__reschedule">Reschedule</a>
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-10 col-xs-offset-1">
                            <p class="text-center">
                                <a href="#" class="btn rounded-btn black-btn groomit-btn outline-btn btn-block" type="button" id="modify-alert__cancel">Cancel Appointment</a>
                            </p>
                        </div>
                    </div>
                </div>
                <!-- /modal-body -->
            </div>
            <!-- /modal-content -->

        </div>
    </div>
    <!-- /MODAL-->



    <script type="text/javascript">
        var onload_func = window.onload;
        window.onload = function() {
            if (onload_func) {
                onload_func();
            }

            $(document).on("click", ".open-reschedule", function () {
                var appointment_id = $(this).data('id');
                $(".row-"+appointment_id).show();
            });

            $(document).on("click", ".cancel-reschedule", function () {
                $(".row-pet").hide();
            });

            $(document).on("click", ".close-reschedule", function () {
                $(".row-pet").hide();
            });

            $('.starrr').on('starrr:change', function(e, value){
                if (typeof value === 'undefined') {
                    return;
                }

                var appointment_id = $(e.currentTarget).prop('id');
                var rating = value;

                $.ajax({
                    url: '/user/appointment/rate',
                    data: {
                        _token: '{{ csrf_token() }}',
                        appointment_id: appointment_id,
                        rating: rating
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function(res) {
                        if ($.trim(res.msg) === '') {

                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                });
            });

            modify_appointment_modal();
        }

        function groomer_modal() {
            $('#modal-groomer').modal();
        }

        function give_tip(appointment_id, tip) {
            if (typeof tip === 'undefined') {
                tip = $('.owl-item.active').find('#other_tip_' + appointment_id).val();
            }

            if ($.trim(tip) === '') {
                myApp.showError('Please enter amount');
                return;
            }

            var sub_total = $('#sub_total_' + appointment_id).val();
            sub_total = parseFloat(sub_total).toFixed(2);
            tip = parseFloat(tip).toFixed(2);

            var tip_amt = tip; sub_total * tip / 100;

            myApp.showConfirm('You are about to give tip to the groomer for $' + tip_amt + '.<br/>Are you sure to prooceed?', function() {
                $.ajax({
                    url: '/user/appointment/tip',
                    data: {
                        _token: '{!! csrf_token() !!}',
                        appointment_id: appointment_id,
                        tip: tip
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function(res) {
                        if ($.trim(res.msg) === '') {
                            $('.other-tip-val').text(tip_amt);
                            $('.tip-options').addClass('hidden');
                            $('.other-tip-edit').addClass('hidden');
                            $('.other-tip').removeClass('hidden');
                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                });
            });
        }

        function toggle_favorite_groomer(appointment_id, groomer_id) {
            var target = $('.fav-groomer-' + groomer_id);
            var add_to_favorite = target.hasClass('glyphicon-heart') ? 'N' : 'Y';

            $.ajax({
                url: '/user/appointment/mark-as-favorite',
                data: {
                    _token: '{!! csrf_token() !!}',
                    appointment_id: appointment_id,
                    add_to_favorite: add_to_favorite
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        if (add_to_favorite === 'N') {
                            target.removeClass('glyphicon-heart').addClass('glyphicon-heart-empty');
                        } else {
                            target.removeClass('glyphicon-heart-empty').addClass('glyphicon-heart');
                        }
                    } else {
                        myApp.showError(res.msg);
                    }
                }

            });
        }

        function appointment_delete(appointment_id) {

            console.log(appointment_id);

            $.ajax({
                url: '/user/appointment/delete',
                data: {
                    appointment_id: appointment_id
                },
                cache: false,
                type: 'get',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        myApp.showSuccess('Your scheduled appointment has been cancelled successfully', function() {
                            window.location.href = '{{ URL::previous() }}';
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            })
        }

        function modify_appointment_modal() {
            $('#modify-alert').on('show.bs.modal', function (event) {

                var button = $(event.relatedTarget) // Button that triggered the modal
                var recipient = button.data('appointment')

                var modal = $(this)
                modal.find('#modify-alert__reschedule').attr('href', '/user/appointment/edit/' + recipient)
                modal.find('#modify-alert__cancel').attr('href', '/user/appointment/cancel/' + recipient)

            })
        }
    </script>
@stop
