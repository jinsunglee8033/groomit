@extends('user.layout.default')
@section('content')
    <link href="/desktop/css/dashboard.css" rel="stylesheet">
    <div id="main">
        <!-- PET-APPOINTMENT -->
        <section id="pet-appointment">
            <div class="container">
                <div class="row">
                    <div class="col-lg-10 col-lg-offset-1">
                        <div class="row" id="schedule">
                            <div class="col-xs-6">
                                <div class="media my-card text-center">
                                    <a onclick="click_schedule('/user/schedule/select-dog')">
                                        <div class="media-left media-middle"><img src="/desktop/img/dog-icon.svg"
                                                                                  class="media-object" width="120"
                                                                                  height="120" alt="Schedule Dog"></div>
                                        <div class="media-body media-middle">
                                            <h4 class="media-heading text-center">Schedule</h4>
                                        </div>
                                    </a></div>
                                <!-- media -->
                            </div>
                            <!-- /col-6 -->
                            <div class="col-xs-6">
                                <div class="media my-card text-center">
                                    <a onclick="click_schedule('/user/schedule/select-cat')">
                                        <div class="media-left media-middle"><img class="media-object"
                                                                                  src="/desktop/img/cat-icon.svg"
                                                                                  width="120" height="120"
                                                                                  alt="Schedule Cat"></div>
                                        <div class="media-body media-middle">
                                            <h4 class="media-heading text-center">Schedule</h4>
                                        </div>
                                    </a></div>
                                <!-- media -->
                            </div>
                            <!-- /col-6 -->
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
                        <!-- /row -->
                    </div>
                    <!-- /col-6 -->
                </div>
                <!-- row -->
            </div>
            <!-- /container -->
        </section>
        <!-- /pet-appointment -->

        <!-- BOTTOM-PANEL -->
        <section id="bottom-panel">
            <div class="container">
                <div class="row">
                    <div class="col-lg-10 col-lg-offset-1">
                        <div id="bottom-panel-wrapper">
                            <div class="row">

                            @if (empty($upcoming) && empty($recent))
                                <!-- NO APPOINTMENTS -->
                                    <div class="col-sm-6 col-sm-offset-3 bookings-history groomit-credits">
                                        <h4 class="text-center">Earn Groomit Credits</h4>
                                        <div class="history-content">
                                            <div class="row">
                                                <div class="col-xs-12 text-center"><img
                                                            src="/desktop/img/groomit-credits.png" width="209"
                                                            height="137"
                                                            alt="Groomit Credits"></div>
                                                <!-- /col -->
                                            </div>
                                            <!-- /row -->
                                            <div class="row">
                                                <div class="col-xs-12 text-center">
                                                    <div class="rc-panel">
                                                        <p><strong>Your Referral Code is <span
                                                                        class="referral-code">{{ $user->referral_code }}
                                                                    .</span></strong>
                                                        </p>
                                                        <p>Refer a friend and both receive ${{ $user->referral_amount }} discount.</p>
                                                    </div>
                                                    <!-- /rc-panel -->
                                                </div>
                                                <!-- /col -->
                                            </div>
                                            <!-- /row -->
                                        </div>
                                        <!-- /history-content -->
                                    </div>
                                    <!-- /NO APPOINTMENTS -->
                            @endif

                            @if (!empty($recent))
                                <!-- LAST BOOKING  -->
                                    <!-- Add "hidden" class if no last bookings -->
                                    <div class="col-sm-6 bookings-history" id="last-booking">
                                        <h4 class="text-center">Last Booking</h4>
                                        <div class="pets-container">

                                        @if (count($recent->pets) > 0)


                                            <!-- PET -->
                                                <div class="row">
                                                    <div class="col-xs-12 text-center">

                                                        @foreach ($recent->pets as $o)

                                                            <div class="media history-pet">
                                                                <div class="media-left media-middle">
                                                                @if (empty($o->photo))
                                                                    <!-- pet avatar -->
                                                                        <div class="bh-pet-avatar">
                                                                            <div class="table-cell media-middle"><img
                                                                                        src="/desktop/img/{{ $o->type }}-icon.svg" width="53"
                                                                                        height="53" alt="{{ $o->pet_name }}"></div>
                                                                        </div>
                                                                        <!-- pet photo -->
                                                                    @else
                                                                        <img src="data:image/png;base64,{{ $o->photo }}"
                                                                             class="img-circle pet-photo" width="90"
                                                                             height="90" alt="{{ $o->pet_name }}">
                                                                    @endif
                                                                </div>
                                                                <div class="media-body media-middle">
                                                                    <p class="media-heading pet-name text-uppercase">
                                                                        <strong>{{ $o->pet_name }}</strong></p>
                                                                    <p class="package-name"><strong>{{ $o->package_name }}</strong>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <!-- media -->

                                                        @endforeach

                                                    </div>
                                                    <!-- /col -->
                                                </div>
                                                <!-- /row -->

                                            @endif
                                        </div>
                                        <!-- /pets-container -->
                                        <div class="history-content">

                                            <!-- DATE / TIME / ADDRESS -->
                                            <div class="row history-dt">
                                                <div class="col-md-5 col-md-offset-1 col-xs-6 text-center"><span
                                                            class="date-time-icon"><img
                                                                src="/desktop/img/calendar-icon.png"
                                                                width="22" height="22"
                                                                alt="Date and Time"></span>
                                                    <p>{{ substr(Carbon\Carbon::parse($recent->accepted_date)->format('Y-m-d h:i A'), 0, 10) }}</p>
                                                    <p>{{ substr(Carbon\Carbon::parse($recent->accepted_date)->format('Y-m-d h:i A'), 11) }}</p>
                                                </div>
                                                <!-- /col -->
                                                <div class="col-md-5 col-xs-6 text-center"><span
                                                            class="address-icon"><img
                                                                src="/desktop/img/address-icon.png" width="auto"
                                                                height="22"
                                                                alt="Date and Time"></span>
                                                    <p>{{ $recent->address }}</p>
                                                </div>
                                                <!-- /col -->
                                            </div>
                                            <!-- /row -->

                                            <!-- GROOMER / RATE / TIP -->
                                            <div class="row history-groomer">
                                                <div class="col-xs-12 text-center">
                                                    <div class="display-table">
                                                        <div class="table-cell cell-groomer">
                                                            <p><strong>Groomer</strong></p>
                                                            <p>{{ empty($recent->groomer) ? '' : ($recent->groomer->first_name . ' ' . $recent->groomer->last_name) }}</p>
                                                        </div>
                                                        <div class="table-cell cell-rating">
                                                            <p><strong>Rate</strong></p>

                                                            <!-- data-rating = initial/data-base value -->
                                                            <div class="starrr" id="{{ $recent->appointment_id }}" data-rating="{{ $recent->rating }}"></div>
                                                            <input type="hidden" class="rating" name="rating" value=""/>
                                                        </div>
                                                        <div class="table-cell cell-tip">
                                                            <p><strong>Tip</strong></p>

                                                            <input type="hidden" id="sub_total_{{ $recent->appointment_id }}" value="{{ $recent->sub_total }}">

                                                            <!-- Tip options -->
                                                            <div class="btn-group btn-group-justified tip-options {{ is_null($recent->tip) ? '' : 'hidden' }}"
                                                                 data-toggle="buttons">
                                                                <label class="btn btn-st-opt" onclick="give_tip('{{ $recent->appointment_id }}', 15)">
                                                                    <input type="radio" name="" autocomplete="off"
                                                                           checked>
                                                                    <span class="glyphicon glyphicon-ok"
                                                                          aria-hidden="true"></span>$15</label>
                                                                <label class="btn btn-st-opt" onclick="give_tip('{{ $recent->appointment_id }}', 20)">
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
                                                                    <label for=""
                                                                           class="table-cell media-middle">Other:</label>
                                                                    <div class="input-group-addon">$</div>
                                                                    <input type="number" name="other_tip" id="other_tip"
                                                                           class="form-control pull-left">
                                                                    <span class="input-group-btn">
                                                                    <button class="btn red-btn groomit-btn"
                                                                            onclick="give_tip('{{ $recent->appointment_id }}')"
                                                                            type="button">
                                                                        <span class="glyphicon glyphicon-ok"></span>
                                                                    </button>
                                                                </span></div>
                                                                <span class="help-block" id="cancel-tip">Cancel</span>
                                                            </div>

                                                            <!-- Other tip -->
                                                            <p class="other-tip {{ is_null($recent->tip) ? 'hidden' : ''}}">Tip given: $<span
                                                                        class="other-tip-val">{{ !is_null($recent->tip) ? number_format($recent->tip, 2) : '' }}</span></p>
                                                        </div>
                                                        <!-- /table-cell -->
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /row -->

                                            <a href="/user/schedule/select-rebook/{{ $recent->appointment_id }}" class="groomit-btn outline-btn red-btn modify-appointment rounded-btn">
                                                REBOOK
                                            </a>

                                        </div>
                                        <!-- /history-content -->

                                        <div class="view-all text-center"><a href="/user/appointment/list" title="View All"
                                                                             class="text-uppercase" target="_self">View
                                                All</a></div>
                                    </div>
                                    <!-- /LAST BOOKING -->
                            @endif

                            @if (empty($upcoming) && !empty($recent) || empty($recent) && !empty($upcoming) )
                                <!-- EARN GROOMIT CREDITS -->
                                    <!-- Add "hidden" class if there are past and next appointments -->
                                    <div class="col-sm-6 bookings-history groomit-credits">
                                        <h4 class="text-center">Earn Groomit Credits</h4>
                                        <div class="history-content">
                                            <div class="row">
                                                <div class="col-xs-12 text-center"><img
                                                            src="/desktop/img/groomit-credits.png" width="209"
                                                            height="137"
                                                            alt="Groomit Credits"></div>
                                                <!-- /col -->
                                            </div>
                                            <!-- /row -->
                                            <div class="row">
                                                <div class="col-xs-12 text-center">
                                                    <div class="rc-panel">
                                                        <p><strong>Your Referral Code is <span
                                                                        class="referral-code">{{ $user->referral_code }}
                                                                    .</span></strong>
                                                        </p>
                                                        <p>Refer a friend and both receive ${{ $user->referral_amount }} discount.</p>
                                                    </div>
                                                    <!-- /rc-panel -->
                                                </div>
                                                <!-- /col -->
                                            </div>
                                            <!-- /row -->
                                        </div>
                                        <!-- /history-content -->
                                    </div>
                                    <!-- /EARN GROOMIT CREDITS -->
                            @endif

                            @if (!empty($upcoming))
                                <!-- NEXT BOOKING  -->
                                    <!-- Add "hidden" class if no next appointment -->
                                    <div class="col-sm-6 bookings-history" id="next-booking">
                                        <h4 class="text-center">Next Booking</h4>
                                        <div class="pets-container">

                                        @if (count($upcoming->pets) > 0)

                                            <!-- PET -->
                                                <div class="row">
                                                    <div class="col-xs-12 text-center">

                                                        @foreach ($upcoming->pets as $o)

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
                                                                             class="img-circle pet-photo" width="90"
                                                                             height="90" alt="{{ $o->pet_name }}">
                                                                    @endif
                                                                </div>
                                                                <div class="media-body media-middle">
                                                                    <p class="media-heading pet-name text-uppercase">
                                                                        <strong>{{ $o->pet_name }}</strong></p>
                                                                    <p class="package-name"><strong>{{ $o->package_name }}</strong>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <!-- media -->

                                                        @endforeach

                                                    </div>
                                                    <!-- /col -->
                                                </div>
                                                <!-- /row -->

                                            @endif

                                        </div>
                                        <!-- /NEXT BOOKING -->
                                        <div class="history-content">

                                            <!-- DATE / TIME / ADDRESS -->
                                            <div class="row history-dt">
                                                <div class="col-md-4 col-xs-4 text-center"><span
                                                            class="date-time-icon"><img
                                                                src="/desktop/img/calendar-icon.png" width="22"
                                                                height="22"
                                                                alt="Date and Time"></span>
                                                    <p>{{ substr(isset($upcoming->accepted_date) ? Carbon\Carbon::parse($upcoming->accepted_date)->format('Y-m-d h:i A') : $upcoming->reserved_at, 0, 10) }}</p>
                                                    <p>{{ substr(isset($upcoming->accepted_date) ? Carbon\Carbon::parse($upcoming->accepted_date)->format('Y-m-d h:i A') : $upcoming->reserved_at, 11) }}</p>
                                                </div>
                                                <!-- /col -->
                                                <div class="col-md-4 col-xs-4 text-center"><span
                                                            class="address-icon"><img
                                                                src="/desktop/img/address-icon.png" width="auto"
                                                                height="22"
                                                                alt="Date and Time"></span>
                                                    <p>{{ $upcoming->address }}</p>
                                                </div>
                                                <!-- /col -->
                                                <div class="col-md-4 col-xs-4 text-center"><span
                                                            class="groomer-icon"><img
                                                                src="/desktop/img/icon-service-dry-brush-out.png"
                                                                width="30"
                                                                height="30" alt="Groomer"></span>
                                                    <p>Groomer</p>
                                                    @if (isset($upcoming->groomer))
                                                        <p class="set-required">{{ $upcoming->groomer->first_name . ' ' . $upcoming->groomer->last_name }}</p>
                                                    @else
                                                        <p class="set-required">Not assigned</p>
                                                    @endif
                                                </div>
                                                <!-- /col -->
                                            </div>
                                            <!-- /row -->
                                            <div class="row">
                                                <div class="col-lg-8 col-lg-offset-2 col-sm-10 col-sm-offset-1 col-xs-12">
                                                    <p class="text-center"><em>Full refund if you cancel by <strong>6:00 pm</strong> the day before service time.</em></p>
                                                </div>
                                            </div>
                                            @if (isset($upcoming->pets[0]->package_name) && $upcoming->pets[0]->package_name != 'ECO')

                                                <a href="#" data-toggle="modal" data-target="#modify-alert" data-appointment="{{ $upcoming->appointment_id }}" class="groomit-btn outline-btn red-btn modify-appointment rounded-btn">
                                                    MODIFY
                                                </a>

                                            @else
                                                <div class="row modify-appointment">
                                                    <div class="col-md-8 col-md-offset-2">
                                                        <div class="display-table">
                                                            <div class="table-cell media-bottom">
                                                                <!-- <a href="#" class="groomit-btn outline-btn red-btn btn-block rounded-btn open-non-r">
                                                                    CANCEL APPOINTMENT
                                                                </a> -->
                                                                <p class="text-center"><strong>Eco Package can't be cancelled.<br>Please contact Customer Service.</strong></p>
                                                            </div>
                                                            <!-- /table-cell -->
                                                        </div>
                                                        <!-- /display-table -->
                                                    </div>
                                                    <!-- /col -->
                                                </div>
                                                <!-- /row -->
                                            @endif
                                        </div>
                                        <!-- /history-content -->

                                        <div class="view-all text-center"><a href="/user/appointment/list" title="View All"
                                                                             class="text-uppercase" target="_self">View
                                                All</a></div>
                                    </div>
                                @endif
                            </div>
                            <!-- /bottom-panel-wrapper -->
                        </div>
                        <!-- /col -->
                    </div>
                    <!-- row -->
                </div>
                <!-- /container -->
        </section>
        <!-- /bottom-panel -->
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
    <!-- /main -->

    @if (!empty($upcoming))
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
                            <button class="btn rounded-btn red-btn groomit-btn" type="button"
                                    onclick="appointment_delete({{ $upcoming->appointment_id }})">Yes, Cancel</button>
                        </p>
                    </div>
                    <!-- /terms -->
                </div>
                <!-- /modal-content -->

            </div>
        </div>
        <!-- /MODAL-->
    @endif

    @if (!empty($recent))
        <!-- RESCHEDULE APPOINTMENT CONFIRMATION MODAL -->
        <div class="modal fade auto-width" id="reschedule-confirmation" tabindex="-1" role="dialog" aria-labelledby="reschedule-title">
            <div class="modal-dialog auto-width" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <h3 class="text-center" id="reschedule-title">Reschedule</h3>
                    @if (count($recent->pets) > 0)

                        @if (count($recent->pets) > 1)
                            @php ($class = "col-xs-6")
                        @else
                            @php ($class = "col-xs-6 col-xs-offset-3")
                        @endif

                        <!-- PET -->
                            <div class="row">

                                @foreach ($recent->pets as $o)
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

                            </div>
                            <!-- /row -->

                        @endif
                        <p class="text-center" style="padding: 0 10px;">
                            <button class="btn rounded-btn black-btn groomit-btn space-btn" data-dismiss="modal" aria-label="Cancel" type="button">Cancel</button>

                            <a href="/user/appointment/rebook/{{ $recent->appointment_id }}" class="groomit-btn outline-btn red-btn rounded-btn modify-appointment">
                                REBOOK
                            </a>

                        </p>
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
                    <br><br>
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

        function give_tip(appointment_id, tip) {
            if (typeof tip === 'undefined') {
                tip = $('#other_tip').val();
            }

            if ($.trim(tip) === '') {
                myApp.showError('Please enter amount');
                return;
            }

            var sub_total = $('#sub_total_' + appointment_id).val();
            sub_total = parseFloat(sub_total).toFixed(2);
            tip = parseFloat(tip).toFixed(2);

            var tip_amt = tip; // sub_total * tip / 100;

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

        @if (!empty($upcoming))
        function appointment_delete(appointment_id) {

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
        @endif

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