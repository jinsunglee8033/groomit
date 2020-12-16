@extends('user.layout.default')
@section('content')
    <link href="/desktop/css/appointment.css" rel="stylesheet">
    <div id="main">
        <!--CANCEL APPOINTMENT -->
        <section class="modify-date-time date-time--cancel" id="date-time">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <h3>CANCEL APPOINTMENT</h3>
                    </div>
                </div>
                <div class="row cancel-container">
                    <div class="col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 text-center">
                        <!-- Refund Amount -->
                        @if ( $cancelling_fee > 0 )
                        <div class="row">
                            <div class="col-xs-12">
                                <p>
                                    If you cancel after 6:00pm the day before service time,
                                    we <strong> charge you a cancellation fee of ${{ $cancelling_fee }} including tax.</strong>
                                </p>
                                <br>
                            </div>
                        </div>
                        @endif

                        {!! csrf_field() !!}
                        <fieldset>
                            <!-- Reason -->
                            <div class="row">
                                <div class="col-xs-10 col-xs-offset-1">
                                    <div class="form-group">
                                    <select name="cancellation_reason" id="cancellation_reason" placeholder="Please, select a reason" class="select form-control" required>
                                        <option value="">Please, select a reason</option>
                                        <option value="Timing">Timing</option>
                                        <option value="Sick">Sick</option>
                                        <option value="Price">Price</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    </div>
                                    <div class="form-group reason__other">
                                        <input id="cancellation_reason_other" name="cancellation_reason_other" type="text" class="form-control">
                                    </div> 
                                    
                                </div>
                            </div>
                            <!-- Cancellation Agreement -->
                            <div class="row">
                                <div class="col-xs-10 col-xs-offset-1">
                                    <div class="form-group form-group text-left centered-inline">
                                        <div class="checkbox">
                                            <label for="accept_terms_cancel">
                                                <input type="checkbox" name="accept_terms_cancel" id="accept_terms_cancel">
                                                <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span> I agree with
                                                    <a target="_blank" href="/customer-cancellation-policy">
                                                        <strong> cancellation policy</strong>
                                                    </a>
                                            </label>
                                        </div>
                                        <!-- /checkbox -->
                                    </div>
                                    <!-- /form-group -->
                                </div>
                            </div>
                            <!-- Submit -->
                            <div class="row">
                                <div class="col-xs-12 text-center">
                                    <div id="action-buttons">
                                        <a class="groomit-btn black-btn rounded-btn btn-block" style="cursor: pointer;" data-toggle="modal" data-target="#cancel-alert">
                                            CANCEL APPOINTMENT
                                        </a>
                                        <br><br>
                                        <a href="/user/appointment/list" class="link--black"><i class="fas fa-chevron-left"></i><strong> Go Back</strong></a>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <!-- col-10 -->
                </div>
                <!-- /row -->
            </div>
            <!-- /container -->
        </section>
    </div>

    <!-- CANCEL ALERT MODAL -->
    <div class="modal fade" id="cancel-alert" tabindex="-1" role="dialog" aria-labelledby="cancel-alert__title">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h3 class="text-center" id="cancel-alert__title">Cancel Appointment?</h3>
                </div>
                <!-- /modal-body -->
                <div class="modal-footer">
                    <p class="text-center" style="padding: 0 10px;">
                        <button class="btn rounded-btn black-btn outline-btn groomit-btn" data-dismiss="modal" aria-label="Close" type="button">No</button>
                        <a href="#" class="btn rounded-btn red-btn groomit-btn" type="button" id="cancel-alert__submit" onclick="appointment_delete()">Yes</a>
                    </p>
                </div>
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

            show_other_reason();

        }

        function show_other_reason() {

            $("#cancellation_reason").on("change", function() {
                if ($(this).val() == "Other") {
                    $(".reason__other").css("display", "block");
                } else {
                    $(".reason__other").css("display", "none");
                }
            });

        }
       
        function appointment_delete() {
            // if (!confirm('Are you sure you want to Cancel your Groomit Appointment?')) {
            //     return;
            // }
            event.preventDefault();

            if ( !$('#accept_terms_cancel' ).is( ':checked' ) ) {
                alert( 'Please accept cancellation policy !' );
                return;
            }

            var reason  = $("#cancellation_reason").val();
            var reason_other  = $("#cancellation_reason_other").val();
            if (reason =='Other' ) {
                if( reason_other == '') {
                    alert('Please enter tge reason briefly !');
                    return;
                }else {
                    reason = reason_other ;
                }
            }

            $.ajax({
                url: '/user/appointment/delete',
                data: {
                    appointment_id: '{{ $appointment_id }}',
                    note: reason
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
        
    </script>
@stop