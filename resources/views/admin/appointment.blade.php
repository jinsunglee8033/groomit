@extends('includes.admin_default')
@section('contents')

    <script type="text/javascript">
        window.onload = function() {
            var reserved_date = '{!! $ap->reserved_date !!}';
            // var d = new Date(reserved_date);
            var d = moment(reserved_date).toDate();
            //var h = d.getHours();

            $( "#accepted_date" ).datetimepicker({
                sideBySide: true,
                defaultDate: d
                //enabledDates: [moment(d)],
                //enabledHours: [h, h+1, h+2]
            }).on('dp.hide', function() {
                update_groomers();
            });

            $( "#required_date" ).datetimepicker({
                // sideBySide: true,
                defaultDate: d,
                format: 'MM/DD/YYYY'
            });

            $('*').dblclick(function(e) {
                e.preventDefault();
            });

            $( "#pdate" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });

            $("#pb_cdate").datetimepicker({
                format: 'YYYY-MM-DD'
            });

            $("#photo_before").on("click", function() {
                $('#imagepreview_before').attr('src', $('#imageresource_before').attr('src'));
                $('#imagemodal_before').modal('show');
            });

            $("#photo_after").on("click", function() {
                $('#imagepreview_after').attr('src', $('#imageresource_after').attr('src'));
                $('#imagemodal_after').modal('show');
            });

        };

        function send_reminder() {
            $('.status-button').hide();
            myApp.showConfirm("Are you sure?", function() {

                myApp.showLoading();
                $.ajax({
                    url: '/admin/appointment/reminder',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: '{{ $ap->appointment_id }}'
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function(res) {
                        myApp.hideLoading();
                        if ($.trim(res.msg) === '') {
                            $('#groomer_on_the_way').modal('hide');
                            myApp.showSuccess('Your request has been processed successfull!', function() {
                                window.location.reload();
                            });
                        } else {
                            $('.status-button').show();
                            myApp.showError(res.msg);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        myApp.hideLoading();
                        $('.status-button').show();
                        myApp.showError(errorThrown);
                    }
                });
            }, function() {
                // do nothing
            });
        }

        function send_renotification() {
            $('.status-button').hide();
            myApp.showConfirm("Are you sure to send New Notifications to Level 1 ?", function() {

                myApp.showLoading();
                $.ajax({
                    url: '/admin/appointment/new_notification',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: '{{ $ap->appointment_id }}'
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function(res) {
                        myApp.hideLoading();
                        if ($.trim(res.msg) === '') {
                            $('#groomer_on_the_way').modal('hide');
                            myApp.showSuccess('Your request has been processed successfull!', function() {
                                window.location.reload();
                            });
                        } else {
                            $('.status-button').show();
                            myApp.showError(res.msg);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        myApp.hideLoading();
                        $('.status-button').show();
                        myApp.showError(errorThrown);
                    }
                });
            }, function() {
                // do nothing
            });
        }

        function groomer_on_the_way(id) {
            $('.status-button').hide();
            myApp.showConfirm("Are you sure?", function() {
                myApp.showLoading();
                $.ajax({
                    url: '/admin/appointment/groomer_on_the_way',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function(res) {
                        myApp.hideLoading();
                        if ($.trim(res.msg) === '') {
                            $('#groomer_on_the_way').modal('hide');
                            myApp.showSuccess('Your request has been processed successfull!', function() {
                                window.location.reload();
                            });
                        } else {
                            $('.status-button').show();
                            myApp.showError(res.msg);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        myApp.hideLoading();
                        $('.status-button').show();
                        myApp.showError(errorThrown);
                    }
                });
            }, function() {
                // do nothing
            });
        }

        function confirm_available_groomer() {
            myApp.showLoading();
            $.ajax({
                url: '/admin/appointment/confirm_available_groomer',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: '{{ $ap->appointment_id }}',
                    groomer_id: $('#groomer_id').val(),
                    accepted_date: $('#accepted_date').val(),
                    status: $('#status').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        assign_groomer();
                    } else {
                        if ($.trim(res.msg) === 'unchecked') {
                            $('#change_status').modal('hide');
                            myApp.showConfirm('The groomer does not have available calendar schedules at the selected date/time. Do you want to continue?', assign_groomer, window.location.reload);
                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function update_requested_time() {
            // alert("hi");
            myApp.showLoading();
            $.ajax({
                url: '/admin/appointment/update_requested_time',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: '{{ $ap->appointment_id }}',
                    required_date: $('#required_date').val(),
                    required_time: $('#required_time').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        //$('#change_status').modal('hide');
                        myApp.showSuccess('Your request has been processed successful!', function() {
                            window.location.reload();
                        });
                    } else {
                        $('.status-button').show();
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function pay_bonus_submit() {

            var pb_id = '{{ $ap->appointment_id }}';
            var pb_amt  = $('#pb_amt').val();
            var pb_cdate    = $('#pb_cdate').val();
            var pb_comments = $('#pb_comments').val();

            myApp.showLoading();
            $.ajax({
                url: '/admin/appointment/pay_bonus',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: pb_id,
                    amt: pb_amt,
                    cdate: pb_cdate,
                    comments: pb_comments
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        //$('#change_status').modal('hide');
                        myApp.showSuccess('Your request has been processed successful!', function() {
                            window.location.reload();
                        });
                    } else {
                        $('.status-button').show();
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function assign_groomer() {

            $('.status-button').hide();
            myApp.showLoading();
            $.ajax({
                url: '/admin/appointment/assign_groomer',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: '{{ $ap->appointment_id }}',
                    groomer_id: $('#groomer_id').val(),
                    accepted_date: $('#accepted_date').val(),
                    status: $('#status').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        $('#change_status').modal('hide');

                        var address_mismatch_msg = '';
                        if (res.address_match === 'N') {
                            address_mismatch_msg = ' <span style="color:red; font-weight:bold;">However credit card address does not match with service address. Please verify the customer and make notes for later use.</span>'
                        }
                        myApp.showSuccess('Your request has been processed successful!' + address_mismatch_msg, function() {
                            window.location.reload();
                        });
                    } else {
                        $('.status-button').show();
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    $('.status-button').show();
                    myApp.showError(errorThrown);
                }
            });
        }

        function unassign_groomer() {
            $('#frm_unassign_groomer').submit();
        }

        function cancel_with_fee() {
            $('.status-button').hide();

            $('#cancel_with_fee_modal').modal();
        }

        function cancel_with_fee_submit() {

            var res = confirm('Do you cancel with cancellation fee?');
            if (!res) {
                return;
            }

            $('.status-button').hide();

            myApp.showLoading();
            $.ajax({
                url: '/admin/appointment/change_status',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: '{{ $ap->appointment_id }}',
                    status: 'C',
                    collect_fee: 'Y',
                    charge_amt: $('#cwf_charge_amt').val(),
                    groomer_commission_amt: $('#cwf_groomer_commission_amt').val(),
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        //$('#change_status').modal('hide');
                        myApp.showSuccess('Your request has been processed successful!', function() {
                            window.location.reload();
                        });
                    } else {
                        $('.status-button').show();
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    $('.status-button').show();
                    myApp.showError(errorThrown);
                }
            });
        }

        function change_status(id, status) {
            var res = '';

            if (status == 'C' && $('#cwf_charge_amt').val() > 0  ) {
                 res = confirm('Are you sure to proceeed without charges, even though the customer needs to pay cancellation fee ?');
            }else {
                res = confirm('Are you sure to proceeed?');
            }



            if (!res) {
                return;
            }

            $('.status-button').hide();

            myApp.showLoading();
            $.ajax({
                url: '/admin/appointment/change_status',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    status: status
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        //$('#change_status').modal('hide');

                        @if (in_array($ap->package, [28, 29]))
                        if (status == 'C') {
                            alert('Please be sure to contact IT, if Refund is needed.');
                        }
                        @endif

                        myApp.showSuccess('Your request has been processed successful!', function() {
                            window.location.reload();
                        });
                    } else {
                        $('.status-button').show();
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    $('.status-button').show();
                    myApp.showError(errorThrown);
                }
            });
        }

        function update_groomers() {
            myApp.showLoading();
            $.ajax({
                url: '/admin/appointment/get-available-groomers',
                data: {
                    _token: '{!! csrf_token() !!}',
                    id: '{{ $ap->appointment_id }}',
                    accepted_date: $('#accepted_date').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();

                    if ($.trim(res.msg) === '') {

                        $('#groomer_id').empty();
                        $('#groomer_id').append('<option value="">Please Select</option>');

                        if (res.groomers) {
                            $.each(res.groomers, function(i, o) {
                                $('#groomer_id').append('<option value="' + o.groomer_id + '">' + o.first_name + ' ' + o.last_name + ' ( Lvl.' + o.level + ', ' + o.groom_pet + ')</option>');
                            });
                        }

                        if (res.unavailable_groomers) {
                            $.each(res.unavailable_groomers, function (i, o) {
                                $('#groomer_id').append('<option class="unavailable" disabled value="' + o.groomer_id + '">' + o.first_name + ' ' + o.last_name + ' ( Lvl.' + o.level + ', ' + o.groom_pet + ') has an appointment at ' + o.accepted_date + '</option>');
                            });
                        }

                        if ($('#include_groomers').is(':checked')){
                            $('.unavailable').prop('disabled', false);
                        }

                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            })
        }

        function update_op_note() {
            myApp.showLoading();

            $.ajax({
                url: '/admin/appointment/update-op-note',
                data: {
                    _token: '{!! csrf_token() !!}',
                    appointment_id: '{{ $ap->appointment_id }}',
                    op_note: $('#op_note').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        myApp.showSuccess('Your request has been processed successful!', function() {
                            window.location.reload();
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function update_payment() {

            myApp.showLoading();
            $.ajax({
                url: '/admin/appointment/change_status',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: '{{ $ap->appointment_id }}',
                    status: '{{ $ap->status }}',
                    payment_id: $('#payment_id').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        $('#change_status').modal('hide');
                        myApp.showSuccess('Your request has been processed successfully!', function() {
                            window.location.reload();
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function update_service(pet_id) {

            var checked = [];
            $("input[name^='addon" + pet_id + "']:checked").each(function ()
            {
                checked.push(parseInt($(this).val()));
            });

            var shampoo_id = $("input[name='shampoo_id" + pet_id + "']:checked").val();

            myApp.showLoading();
            $.ajax({
                url: '/admin/appointment/update_service',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: '{{ $ap->appointment_id }}',
                    pet_id: pet_id,
                    size_id: $('#size_id' + pet_id).val(),
                    package_id: $('#package_id' + pet_id).val(),
                    shampoo_id: shampoo_id,
                    addon: checked
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        $('#change_status').modal('hide');
                        myApp.showSuccess('Your request has been processed successfully!', function() {
                            window.location.reload();
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function update_groomer_note(pet_id) {
            myApp.showLoading();
            $.ajax({
                url: '/admin/appointment/update_groomer_note',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: '{{ $ap->appointment_id }}',
                    pet_id: pet_id,
                    groomer_note: $('#groomer_note').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        $('#change_status').modal('hide');
                        myApp.showSuccess('Your request has been processed successfully!', function() {
                            window.location.reload();
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function edit_promocode() {
            $('#promo_form').css("display", "block");
            $('#promo_code_edit').css("display", "none");
        }

        function cancel_promocode() {
            $('#promo_form').css("display", "none");
            $('#promo_code_edit').css("display", "block");
        }

        function cancel_fav_groomer() {

            myApp.showLoading();
            $.ajax({
                url: '/admin/appointment/cancel_fav_groomer',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: '{{ $ap->appointment_id }}'
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        $('#change_status').modal('hide');
                        update_service('{{ $ap->pets[0]->pet_id }}') ;

                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function update_fav_groomer() {

            var assign_favorit_groomer = $('#assign_favorite_groomer').val();
            if(assign_favorit_groomer == ''){
                alert("Please select groomer");
                return
            }
            myApp.showLoading();
            $.ajax({
                url: '/admin/appointment/update_fav_groomer',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: '{{ $ap->appointment_id }}',
                    my_favorite_groomer: assign_favorit_groomer
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        $('#change_status').modal('hide');
                        update_service('{{ $ap->pets[0]->pet_id }}') ;

                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function update_promocode() {

            myApp.showLoading();
            $.ajax({
                url: '/admin/appointment/update_promo_code',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: '{{ $ap->appointment_id }}',
                    promo_code: $('#promo_code').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        $('#change_status').modal('hide');
                        myApp.showSuccess('Your request has been processed successfully!', function() {
                            window.location.reload();
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function send_service_completion_email() {

            myApp.showLoading();
            $.ajax({
                url: '/admin/appointment/send_service_completion_email',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: '{{ $ap->appointment_id }}'
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        $('#change_status').modal('hide');
                        myApp.showSuccess('Your request has been processed successfully!', function() {
                            window.location.reload();
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function toggle_delayed() {
            var delayed = $('#cb_delayed').is(':checked') ? 'Y' : 'N';
            $("#delayed").val(delayed);
            $('#frm_delayed').submit();
        }
    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />Appointment Detail</h3>
    </div>

    <div class="well filter" style="padding-bottom:5px;margin: 30px;">


        <div class="row">
            <div class="col-md-2">
                <div class="form-group">

                    <a href="/admin/applications" class="btn btn-info">Back to List</a>
                    <a type="button" class="btn btn-info" href="/admin/appointment/{{ $ap->appointment_id }}/invoice" target="_blank">Invoice</a>

                </div>
            </div>
            <div class="col-md-10 text-right">
                <div class="form-group">
                    @if ( in_array( $ap->status,  ['P','C','L']) )
                        @if (\App\Lib\Helper::get_action_privilege('appointment_adjustment', 'Appointment Adjustment'))
                            <button type="button" class="btn btn-warning" onclick="$('#pay_bonus').modal()"> Pay Bonus
                            </button>
                            <button type="button" class="btn btn-info" onclick="$('#adjust_modal').modal()"> Adjustments
                            </button>
                        @endif
                    @endif

{{--                    @if ($ap->status == 'C' || $ap->status == 'L' || $ap->status == 'P')--}}
{{--                        @if (\App\Lib\Helper::get_action_privilege('appointment_manually_charge', 'Appointment Manually Charge'))--}}
{{--                            <button type="button" class="btn btn-info" onclick="$('#charge_modal')"> Manually Charge </button>--}}
{{--                        @endif--}}
{{--                        <script>--}}
{{--                            function manually_charge() {--}}
{{--                                if (confirm('Are you sure to refund the cancelled appointment ? ')) {--}}
{{--                                    window.location.href = '/admin/appointment/{{ $ap->appointment_id }}/manually_charge';--}}
{{--                                }--}}
{{--                            }--}}
{{--                        </script>--}}
{{--                        @if (\App\Lib\Helper::get_action_privilege('appointment_manually_refund', 'Appointment Manually Refund'))--}}
{{--                            <button type="button" class="btn btn-info" onclick="manually_refund()"> Manually Refund </button>--}}
{{--                        @endif--}}
{{--                        <script>--}}
{{--                            function manually_refund() {--}}
{{--                                if (confirm('Are you sure to refund the cancelled appointment ? ')) {--}}
{{--                                    window.location.href = '/admin/appointment/{{ $ap->appointment_id }}/manually_refund';--}}
{{--                                }--}}
{{--                            }--}}
{{--                        </script>--}}
{{--                    @endif--}}

                        {!! Helper::app_buttons($ap, $allowed_admin) !!}
                    @if ($ap->status == 'C' && \App\Lib\Helper::appointment_paid($ap->appointment_id))
                        @if (\App\Lib\Helper::get_action_privilege('appointment_refund', 'Appointment Refund'))
                        <button type="button" class="btn btn-info" onclick="refund_payment()"> Refund </button>
                            @endif
                        <script>
                            function refund_payment() {
                                if (confirm('Are you sure to refund the cancelled appointment ? ')) {
                                    window.location.href = '/admin/appointment/{{ $ap->appointment_id }}/refund';
                                }
                            }
                        </script>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Groomer Modal Start -->
    <div class="modal" id="assign_groomer" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Assign Groomer</h4>
                </div>
                <div class="modal-body">

                    <div id="result"></div>

                    <div class="row">
                        <div class="col-xs-4 text-right">
                            <input type="checkbox" id="include_groomers" name="include_groomers" onchange="update_groomers()" />
                        </div>
                        <div class="col-xs-7">
                            <label>Includes groomers regardless of appointments</label>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-xs-4 text-right">Service Time</div>
                        <div class="col-xs-7">
                            <input type="text" class="form-control" id="accepted_date" name="accepted_date" value="{{ old('accepted_date', $ap->accepted_date) }}" onchange="update_groomers()"/>
                            @if($ap->accepted_date)
                                <br>Accepted - <strong>{{ $ap->accepted_date }}</strong>
                            @endif
                        </div>
                    </div>
                    <br><br>
                    <div class="row">
                        <div class="col-xs-4 text-right">Groomer ID</div>
                        <div class="col-xs-7">
                            <select class="form-control" id="groomer_id">
                                <option value="">Please Select</option>
                                @foreach ($groomers as $o)
                                    <option value="{{ $o->groomer_id }}" {{ $ap->groomer['groomer_id'] == $o->groomer_id ? 'selected' : '' }}>{{ $o->first_name . ' ' . $o->last_name . ' ( Lvl.' . $o->level . ', ' . $o->groom_pet . ' )' }}</option>
                                @endforeach

                                @foreach ($unavailable_groomers as $o)
                                    <option class="unavailable" disabled value="{{ $o->groomer_id }}" {{ $ap->groomer['groomer_id'] == $o->groomer_id ? 'selected' : '' }}>{{ $o->first_name . ' ' . $o->last_name . ' ( Lvl.' . $o->level . ', ' . $o->groom_pet . ' ) ' . 'has an appointment at ' .  $o->accepted_date }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="btn btn-warning" onclick="unassign_groomer()">Unassign</div>
                            </div>
                        </div>
                        <div class="col-md-8 text-right">
                            <div class="form-group">
                                <div class="btn btn-primary" onclick="confirm_available_groomer()">Submit</div>

                                <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                    <br><br>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Groomer Modal Start -->
    <div class="modal" id="pay_bonus" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Pay Bonus</h4>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Amount($)</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" id="pb_amt"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Date</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" id="pb_cdate"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Comments</label>
                    <div class="col-md-8">
                        <textarea id="pb_comments" rows="5" style="width:100%"></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 text-right">
                        <div class="form-group">
                            <div class="btn btn-primary" onclick="pay_bonus_submit()">Submit</div>
                            <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Assign Groomer Modal Start -->
    <div class="modal" id="change_requested_date" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Change Requested Date</h4>
                </div>
                <div class="modal-body">

                    <div id="result"></div>

                    <div class="row">
                        <div class="col-xs-4 text-right">Required Date </div>

                        <div class="col-xs-7">
                            <input type="text" class="form-control" id="required_date" name="required_date" value="{{ old('required_date', $ap->reserved_date) }}"/>
                        </div>
                    </div>
                    <br><br>
                    <div class="row">
                        <div class="col-xs-4 text-right">Required Time</div>
                        <div class="col-xs-7">
                            <select class="form-control form-control-select-time" name="required_time" id="required_time">
                                @if (is_array($time_windows))
                                    @foreach ($time_windows as $o)
                                        <option value="{{ $o->id }}" >{{ $o->title }}[{{ $o->time }}]</option>
                                    @endforeach
                                @endif

                            </select>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-md-8 text-right">
                            <div class="form-group">
                                <div class="btn btn-primary" onclick="update_requested_time()">Submit</div>

                                <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{--    @if ( in_array(Auth::guard('admin')->user()->admin_id, [11,21, 6, 15]))--}}
    <!-- Assign Groomer Modal Start -->
    <div class="modal" id="adjust_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="frm_search" class="form-horizontal" method="post" action="/admin/appointment/{{ $ap->appointment_id }}/adjust">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Adjust</h4>
                    </div>
                    <div class="modal-body">

                        <div id="result"></div>
                        <div class="row">
                            <div class="col-xs-1 text-right"></div>
                            <div class="col-xs-10 text-right ">
                                *. This will affect Profit Share Report only for booking purpose.
                                <br/>
                                <br/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4 text-right">Date</div>
                            <div class="col-xs-7">
                                <input type="text" class="form-control" name="pdate" id="pdate"/>
                            </div>
                        </div>
                        <br><br>
                        <div class="row">
                            <div class="col-xs-4 text-right">Type</div>
                            <div class="col-xs-7">
                                <select class="form-control" name="type">
                                    <option value="">Please Select</option>
                                    <option value="C">ChargeBack</option>
                                    <option value="D">Charge</option>
                                </select>
                            </div>
                        </div>
                        <br><br>
                        <div class="row">
                            <div class="col-xs-4 text-right">Amount</div>
                            <div class="col-xs-7">
                                <input type="text" class="form-control" name="amt" />
                            </div>
                        </div>
                        <br><br>
                        <div class="row">
                            <div class="col-xs-4 text-right">Comment</div>
                            <div class="col-xs-7">
                                <textarea class="form-control" name="comments"></textarea>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                </div>
                            </div>
                            <div class="col-md-7 text-right">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                    <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
{{--    @endif--}}

    <!-- Assign Groomer Modal Start -->
    <div class="modal" id="cancel_with_fee_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Cancel With Fee</h4>
                </div>
                <div class="modal-body">

                    <div id="result"></div>

                    <div class="row">
                        <div class="col-xs-4 text-right">Charge Amount</div>
                        <div class="col-xs-7">
                            <input type="text" class="form-control" id="cwf_charge_amt" value="{{$cwf_charge_amt}}"/>
                        </div>
                    </div>
                    <br><br>
                    <div class="row">
                        <div class="col-xs-4 text-right">Groomer Commission Amt</div>
                        <div class="col-xs-7">
                            <input type="text" class="form-control" id="cwf_groomer_commission_amt" value="{{$cwf_groomer_commission_amt}}"/>
                        </div>
                    </div>
                    <br><br>
                    <!--
                    <div class="row">
                        <div class="col-xs-11">
                        New Cancellation Fee Policy from 2020.<br/>
                        *. Within 12 hr : 100% cancellation fee<br/>
                        12 ~ 24 hr   : 50% cancellation fee<br/>
                        Before 24 hr : Free cancellation fee<br/>
                        Non Groomerable after groomers arrive : 50% cancellation fee<br/>
                        *. 65% to groomers commission out of above charged amount
                        </div>
                    </div>
                    -->
                </div>
                <div class="modal-footer">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                            </div>
                        </div>
                        <div class="col-md-7 text-right">
                            <div class="form-group">
                                <button type="button" class="btn btn-primary" onclick="cancel_with_fee_submit()"
                                >Submit</button>
                                <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <form id="frm_unassign_groomer" class="form-horizontal" method="post" action="/admin/appointment/assign_groomer">
        {{ csrf_field() }}
        <input type="hidden" name="id" value="{{ $ap->appointment_id }}"/>
        <input type="hidden" name="for_unassign" value="unassign"/>

    </form>
    <!-- Assign Groomer Modal End -->


    <!-- Change Status Modal Start -->
    <div class="modal" id="change_status" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Change Status</h4>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <div class="col-xs-3">Status</div>
                        <div class="col-xs-6">
                            <select id="status" class="form-control">
                                @foreach($status as $k=>$v)
                                    <option value="{{ $k }}" @if($ap->status == $k) selected @endif>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xs-3">
                            <button type="button" class="btn btn-info" id="change_status_btn" onclick="change_status()"> Submit </button>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Change Status Modal End -->

    <div class="detail">
        @if (session()->has('success'))
            <div class="alert alert-info alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                Your request has been processed successfully!
            </div>
        @endif

        @if ($errors->has('exception'))
            <div class="alert alert-warning alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                {{ $errors->first('exception') }}
            </div>
        @endif
        <div class="row">
            <div class="col-xs-3">Appointment ID</div>
            <div class="col-xs-3">{{ $ap->appointment_id }}</div>
            <div class="col-xs-3">Order.From</div>
            <div class="col-xs-3">{{ $ap->order_from == 'D' ? 'Web' : 'App' }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">User ID/Name</div>
            <div class="col-xs-3"><a href="/admin/user/{{ $ap->user->user_id }}">{{ $ap->user_id }} / {{ $ap->user->first_name }} {{ $ap->user->last_name }}</a></div>
            <div class="col-xs-3">Phone: {{ $ap->user->phone }}</div>
            <div class="col-xs-3">Email: {{ $ap->user->email }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Status</div>
            <div class="col-xs-3">
                <mark> {{ $ap->status_name }} </mark>
                @if ($ap->status == 'C' && $ap->note) <span style="color: orangered;">&nbsp; ( Reason : {{ $ap->note }} )</span> @endif
            </div>
            <div class="row">
                <div class="col-xs-3">Total Appts($Total/QTY)</div>
                <div class="col-xs-3">{{ number_format($ap->sum_total,2)}} / {{ $ap->booked_cnt}} = {{ $ap->booked_cnt>0 ? number_format($ap->sum_total/$ap->booked_cnt,2): 0 }}</div>
                <div class="col-xs-3"></div>
                <div class="col-xs-3"></div>
            </div>
{{--            <div class="col-xs-3">Delayed?</div>--}}
{{--            <div class="col-xs-3">--}}
{{--                <form id="frm_delayed" method="post" action="/admin/appointment/toggle-delayed">--}}
{{--                    {!! csrf_field() !!}--}}

{{--                    <input type="hidden" name="appointment_id" value="{{ $ap->appointment_id }}"/>--}}
{{--                    <input type="hidden" id="delayed" name="delayed" value="{{ $ap->delayed }}"/>--}}
{{--                    <input type="checkbox" id="cb_delayed" value="Y" {{ $ap->delayed == 'Y' ? 'checked' : '' }} onclick="toggle_delayed()"/>--}}


{{--                </form>--}}

{{--            </div>--}}
        </div>
        <div class="row">
            <div class="col-xs-3">Schedule date</div>
            <div class="col-xs-3">{{ $ap->cdate }}</div>
            <div class="col-xs-3">Place</div>
            <div class="col-xs-3">
                <mark> {{ $ap->place_name }} </mark>
                @if ($ap->place_id == 'O') <span style="color: orangered;">&nbsp; {{ $ap->other_place_name }} </span> @endif
            </div>
        </div>
        <div class="row">
            <div class="col-xs-3">Requested Time</div>
            <div class="col-xs-9">{{ $ap->reserved_at }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Service Time</div>
            @if($ap->accepted_date)
                <div class="col-xs-3"><strong>{{ $ap->accepted_date }}</strong></div>
            @else
                <div class="col-xs-3">Date and Time was not confirmed yet.</div>
            @endif
            <div class="col-xs-6"></div>
        </div>

       <!-- Favorit Groomer (Waitting)-->
        <div class="row">
                <div class="col-xs-3">Is Fav.Groomer Requested?</div>
                <div class="col-xs-3">
                    @if ($ap->fav_type == 'F')
                        <span style="color: orangered;">YES
                        @if ( !empty($ap->first_name))
                                [{{ $ap->first_name }} {{ $ap->last_name }}][{{ $ap->my_favorite_groomer }}]
                                <button type="button" id="cancel_fav_groomer" class="btn btn-danger btn-sm"
                                        onclick="cancel_fav_groomer()">Change into next available Groomer</button>
                        @endif
                        </span>
                    @elseif ($ap->fav_type == 'N')
                        NO
                        <select name="assign_favorite_groomer" id="assign_favorite_groomer">
                            <option value="">Select</option>
                            @foreach($ap->fav_groomers as $fav)
                                <option value="{{ $fav->groomer_id }}" {{ old('package_id', $fav->groomer_id) == $fav->my_favorite_groomer ? 'selected' : '' }}>
                                    {{ $fav->first_name . ' ' . $fav->last_name . '(' . $fav->groomer_id . ')' }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" id="update_fav_groomer" class="btn btn-danger btn-sm"
                                onclick="update_fav_groomer()">Change into Fav.Groomer</button>
                    @else

                    @endif
                </div>
        </div>

        <div class="row">
            <div class="col-xs-3">Groomer</div>
            <div class="col-xs-3">
                @if($ap->groomer)
                <a href="/admin/groomer/{{ $ap->groomer_id }}"><strong>{{
            $ap->groomer["first_name"] }} {{ $ap->groomer["last_name"] }}</strong></a><br/>
                    @if(!empty($ap->rating) && $ap->rating > 0)
                        Rating : {{  number_format($ap->rating,2) }}<br/>
                    @endif
                  Total Rating : {{ is_null($ap->groomer["average_rating"]) ? 'No rating yet' : number_format($ap->groomer["average_rating"], 2) . '[' . $ap->groomer["rating_qty"] . ' Rated Appts][' . $ap->groomer["total_appts"] . ' Total Appts]' }}
                @else
                    Not Assigned
                @endif
            </div>
            <div class="col-xs-6">
                Favorite Groomers
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Is Service Package ?</th>
                            <th>Is Service Area ?</th>
                        </tr>
                    </thead>
                    @if (!empty($ap->fav_groomers) && count($ap->fav_groomers) > 0)
                    <tbody>
                        @foreach ($ap->fav_groomers as $fav)
                            <tr>
                                <td>{{ $fav->first_name . ' ' . $fav->last_name . '(' . $fav->groomer_id . ')'}}</td>
                                <td>{{ empty($fav->service_package) ? 'No' : 'Yes' }}</td>
                                <td>{{ empty($fav->service_area) ? 'No' : 'Yes' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    @else
                    <tbody>
                    <tr><td colspan="3">No favorite groomers</td></tr>
                    </tbody>
                    @endif
                </table>


                @if (!empty($ap->blocked_groomers) && count($ap->blocked_groomers) > 0)
                Blocked Groomers
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Name</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($ap->blocked_groomers as $blocked)
                        <tr>
                            <td>{{ $blocked->first_name . ' ' . $blocked->last_name . '(' . $blocked->groomer_id . ')'}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @else
                    <tbody>
                    <tr><td colspan="3">No Blocked groomers</td></tr>
                    </tbody>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-xs-3">Service Address</div>
            <div class="col-xs-9">
                {{ $ap->address }}
                @if (!$ap->address_match)
                    <span style="color:red; font-weight: bold; margin-left: 20px;">Billing Address Mismatch!</span>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-xs-3">Check-In</div>
            <div class="col-xs-3">{{ $ap->check_in }}</div>
            <div class="col-xs-3">Estimated Earning</div>
            <div class="col-xs-3">{{ $ap->estimated_earning }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Check-Out</div>
            <div class="col-xs-3">{{ $ap->check_out }}</div>
            <div class="col-xs-3">Estimated Bonus</div>
            <div class="col-xs-3">{{ $ap->estimated_bonus }}</div>
        </div>

        @if($update_history && count($update_history) > 0)
        <div class="row no-border">
            <div class="col-xs-3">Update History</div>
            <div class="col-xs-9"></div>
        </div>

        <div class="row">
            <div class="alert alert-warning col-xs-12">
                <div class="row" style="font-weight: 600;">
                    <div class="col-xs-2">Status</div>
                    <div class="col-xs-3">Requested Date</div>
                    <div class="col-xs-2">Service Date</div>
                    <div class="col-xs-1">Groomer</div>
                    <div class="col-xs-2">Modified By</div>
                    <div class="col-xs-2">Modified Date</div>
                </div>
                @foreach($update_history as $o)
                    <div class="row">
                        <div class="col-xs-2">{{ $o->status_name }}</div>
                        <div class="col-xs-3">{{ $o->reserved_at }}</div>
                        <div class="col-xs-2">
                            @if ($o->accepted_date)
                                {{ $o->accepted_date }}
                            @endif
                        </div>
                        <div class="col-xs-1">{{ $o->groomer_name }}</div>
                        <div class="col-xs-2">{{ $o->modified_by }}</div>
                        <div class="col-xs-2">{{ $o->mdate }}</div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

            @if(!empty($ap->cc_trans) && count($ap->cc_trans) > 0)
                <div class="row no-border">
                    <div class="col-xs-3">Holding/Charges History</div>
                    <div class="col-xs-9"></div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th  style="text-align: center;" >Type</th>
                                    <th  style="text-align: center;" >Category</th>
                                    <th  style="text-align: center;" >Amount</th>
                                    <th  style="text-align: center;" >Result</th>
                                    <th  style="text-align: center;" >Request Date</th>
                                    <th  style="text-align: center;" >Response</th>
                                    <th  style="text-align: center;" >Void Date</th>
                                    <th  style="text-align: center;" >ETC</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($ap->cc_trans as $cc_tran)
                                <tr>
                                    <td align="center">{{ \App\Model\CCTrans::get_type_name($cc_tran->type) }}</td>
                                    <td align="center">{{ \App\Model\CCTrans::get_category_name($cc_tran->category) }}</td>
                                    <td align="center" >{{ $cc_tran->amt }}</td>
                                    <td align="center">{{ $cc_tran->result == '0' ? 'Success' : 'Failed' }}</td>
                                    <td align="center">{{ $cc_tran->cdate }}</td>
                                    <td align="center">{{ $cc_tran->result_msg }}</td>
                                    <td align="center">{{ $cc_tran->void_date }}</td>
                                    <td align="center">{{ $cc_tran->error_name }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        <div class="row">
            <div class="col-xs-12">Service Pet<br><br></div>

            <div class="col-xs-12 alert alert-info">
                <div class="row">
                    <div class="col-xs-3">Name (Pet ID), Age, Breed</div>
                    <div class="col-xs-2">Size</div>
                    <div class="col-xs-2">Package</div>
                    <div class="col-xs-2">Shampoo</div>
                    <div class="col-xs-2">Add-ons</div>
                    <div class="col-xs-1">Service Price</div>
                </div>
                @foreach($ap->pets as $pet)
                    <div class="row no-border">
                        <div class="col-xs-1">
                            <a href="/admin/pet/{{ $pet->pet_id }}">
                            @if(!empty($pet->photo))
                                <img width="60" src="data:image/gif;base64,{{ $pet->photo }}" />
                            @else
                                @if($pet->type == 'cat')
                                    <img width="60" src="/images/cat-profile-avatar.jpg" />
                                @else
                                    <img width="60" src="/images/dog-profile-avatar.jpg" />
                                @endif
                            @endif
                            </a>
                        </div>

                        @if ($pet->type != 'cat')

                            <div class="col-xs-2 align-left">
                                <a href="/admin/pet/{{ $pet->pet_id }}">
                                <strong>{{ $pet->pet_name }}</strong> ({{ $pet->pet_id }}) <br> {{ $pet->age }} <br> {{ $pet->breed->breed_name }}
                                </a>
                            </div>

                            <div class="col-xs-2">
                                <select name="size_id{{ $pet->pet_id }}" id="size_id{{ $pet->pet_id }}">
                                    @foreach($sizes as $p)
                                        <option value="{{ $p->size_id }}" {{ old('size_id', $pet->size_id) == $p->size_id ? 'selected' : '' }}>{{ $p->size_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-xs-2">
                                <select name="package_id{{ $pet->pet_id }}" id="package_id{{ $pet->pet_id }}">
                                    @foreach($packages as $p)
                                        <option value="{{ $p->prod_id }}" {{ old('package_id', $pet->package_id) == $p->prod_id ? 'selected' : '' }}>{{ $p->prod_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-xs-2">
                                @foreach($shampoos as $p)
                                <label class="radio">
                                    <input type="radio" name="shampoo_id{{ $pet->pet_id }}" id="shampoo_id{{ $pet->pet_id }}" value="{{ $p->prod_id }}" {{ old('shampoo_id', $pet->shampoo[0]->prod_id) == $p->prod_id ? 'checked' : '' }} />
                                    {{ $p->prod_name }}
                                    {{ $p->denom > 0 ? '$ ' . $p->denom : '' }}
{{--                                    [{{ $p->status == 'A' ? 'Active' : 'Closed' }}]--}}
                                </label>
                                @endforeach
                            </div>


                            <div class="col-xs-2">

                                @foreach($addons as $p)
                                    <label class="checkbox">
                                        <input type="checkbox" name="addon{{ $pet->pet_id }}_{{ $p->prod_id }}" value="{{ $p->prod_id }}" {{ in_array($p->prod_id, $pet->addon_array) ? 'checked' : '' }} /> {{ $p->prod_name }} [ ${{ $p->denom }} ]
                                        @if (in_array($p->prod_id, $pet->addon_array))
                                            @foreach($pet->addons as $a)
                                                @if ($a->prod_id == $p->prod_id)
                                            <br><small>{{ $a->created_by }}, {{ $a->cdate }}</small>
                                                @endif
                                            @endforeach
                                        @endif
                                    </label>
                                @endforeach

                            </div>

                        @else
                            <div class="col-xs-2 align-left">
                                <a href="/admin/pet/{{ $pet->pet_id }}">
                                <strong>{{ $pet->pet_name }}</strong> ({{ $pet->pet_id }}) <br> {{ $pet->age }} <br> Cat
                                </a>
                            </div>

                            <div class="col-xs-4 col-xs-offset-2">
                                <select name="package_id{{ $pet->pet_id }}" id="package_id{{ $pet->pet_id }}">
                                    @foreach($packages as $p)
                                        <option value="{{ $p->prod_id }}" {{ old('package_id', $pet->package_id) == $p->prod_id ? 'selected' : '' }}>{{ $p->prod_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-xs-2">

                                @foreach($addons as $p)
                                    <label class="checkbox">
                                        <input type="checkbox" name="addon{{ $pet->pet_id }}_{{ $p->prod_id }}" value="{{ $p->prod_id }}" {{ in_array($p->prod_id, $pet->addon_array) ? 'checked' : '' }} /> {{ $p->prod_name }} [ ${{ $p->denom }} ]
                                    </label>
                                @endforeach

                            </div>
                        @endif


                        <div class="col-xs-1 right align-right">${{ $pet->sub_total }}</div>
                    </div>
                    <div class="row">
                        @if ($ap->status == 'P')
                        <div class="col-xs-3">
                            Groomer Note:<br/><textarea style="width:100%" rows="5" id="groomer_note">{{
                            $pet->groomer_note
                            }}</textarea>
                            <br>
                            <button type="button" class="btn btn-sm
                            btn-danger" onclick="update_groomer_note({{ $pet->pet_id }})
                                    ">Update Groomer Note</button>
                        </div>
                        @endif
                        @if (!empty($pet->special_note))
                            <div class="col-xs-3">
                                User Note:<br/>{{ $pet->special_note }}
                            </div>
                        @else
                            <div class="col-xs-3"></div>
                        @endif
                        @if (!empty($pet->before_image))
                            <a id="photo_before">
                                <div class="col-xs-2">
                                    Before: <br/><img id="imageresource_before" src="data:image/png;base64,{{ $pet->before_image }}" height="150"/>
                                </div>
                            </a>
                        @else
                            <div class="col-xs-2"></div>
                        @endif
                        @if (!empty($pet->after_image))
                            <a id="photo_after">
                                <div class="col-xs-2">
                                    After: <br/><img id="imageresource_after" src="data:image/png;base64,{{ $pet->after_image }}" height="150"/>
                                </div>
                            </a>
                        @else
                            <div class="col-xs-2"></div>
                        @endif
                        <div class="col-xs-2 text-right">
                            <button type="button" style="margin-top:50%;" class="btn btn-sm
                            btn-danger" onclick="update_service('{{ $pet->pet_id }}')">Update Package/Shampoo/Add-ons</button>
                        </div>
                    </div>

                    @endforeach
            </div>
        </div>

        <!-- Creates the bootstrap modal where the image will appear -->
        <div class="modal fade" id="imagemodal_before" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="myModalLabel_before">Before</h4>
                    </div>
                    <div class="modal-body">
                        <img src="" id="imagepreview_before" style="width: 100%; height: 100%;" >
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="imagemodal_after" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="myModalLabel_after">After</h4>
                    </div>
                    <div class="modal-body">
                        <img src="" id="imagepreview_after" style="width: 100%; height: 100%;" >
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-3">Sub Total</div>
            <div class="col-xs-9">${{ $ap->sub_total }}</div>
        </div>
        @if($ap->promo_code)
            <div class="row">
                <div class="col-xs-3">Promo Amount / Code</div>
                <div class="col-xs-9">${{ $ap->promo_amt }} / {{ $ap->promo_code }}
                    <button type="button" id="promo_code_edit" class="btn btn-danger btn-sm" onclick="edit_promocode()">Edit</button>
                    <div class="form-group top-margin" id="promo_form" style="display:none;">
                        <input type="text" class="form-control form-inline" id="promo_code" name="promo_code" value="{{ $ap->promo_code }}" style="width:150px;display:inline;"/>
                        &nbsp;
                        <button type="button" id="promo_code_cancel" class="btn btn-info btn-sm form-inline" onclick="cancel_promocode()">Cancel</button>
                        &nbsp;
                        <button type="button" id="promo_code_update" class="btn btn-danger btn-sm form-inline" onclick="update_promocode()">Update</button>
                    </div>
                </div>

            </div>
        @else
            <div class="row">
                <div class="col-xs-3">Promo Amount / Code</div>
                <div class="col-xs-9">
                    <button type="button" id="promo_code_edit" class="btn btn-danger btn-sm" onclick="edit_promocode()">Apply New Promo Code</button>
                    <div class="form-group" id="promo_form" style="display:none;">
                        <input type="text" class="form-control form-inline" id="promo_code" name="promo_code" value="{{ $ap->promo_code }}" style="width:150px;display:inline;"/>
                        &nbsp;
                        <button type="button" id="promo_code_cancel" class="btn btn-info btn-sm form-inline" onclick="cancel_promocode()">Cancel</button>
                        &nbsp;
                        <button type="button" id="promo_code_update" class="btn btn-danger btn-sm form-inline" onclick="update_promocode()">Apply</button>
                    </div>
                </div>

            </div>
        @endif

        <div class="row">
            <div class="col-xs-3">Safety & Insurance</div>
            <div class="col-xs-9">${{ $ap->safety_insurance }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Sameday Booking</div>
            <div class="col-xs-9">${{ $ap->sameday_booking }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Fav. Groomer Fee</div>
            <div class="col-xs-9">${{ $ap->fav_groomer_fee }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Tax</div>
            <div class="col-xs-9">${{ $ap->tax }}</div>
        </div>

        @if($ap->credit_amt > 0)
            <div class="row">
                <div class="col-xs-3">Credit Amount</div>
                <div class="col-xs-9">
                    <a href="/admin/user/{{ $ap->user->user_id }}#credit_history">${{ $ap->credit_amt }}</a></div>
            </div>
        @endif
        <div class="row">
            <div class="col-xs-3"><strong>Total</strong></div>
            <div class="col-xs-3"><strong>${{ $ap->total }}</strong></div>
        </div>

            @php
                $cctrans_amt = \App\Model\CCTrans::where('appointment_id', $ap->appointment_id)
                    ->whereIn('type', ['A','S','V'] )
                    ->where('category', 'S')
                    ->where('result', 0)
                    //->whereNull('void_date')
                    ->where('amt', '!=', 0.01)
                    ->where( DB::raw('IfNull(error_name,"")'), '!=', 'cccomplete' )
                    ->sum( DB::raw("case type when 'S' then amt when 'A' then amt else -amt end") );
                if($cctrans_amt == '') {
                    $cctrans_amt = 0;
                }
            @endphp

            @if ( ($cctrans_amt > 0 ) && !in_array($ap->status, ['C', 'L']) && $cctrans_amt != $ap->total  )
                <div class="row">
                    <div class="col-xs-3"><strong>Holding/Charged Amount</strong></div>
                    <div class="col-xs-1"><strong>$ {{ $cctrans_amt }}</strong></div>

                    @if (in_array($ap->status, ['P']))
                    <button type="button" class="btn btn-danger" onclick="re_chargerefund()"> Charge/Refund the Difference !</button>
                        <br/>
                        *.This will charge or refund the amount of difference right now.<br/>
                        If you want it at appointment completion, not right now, please do not click it.
                            <script>
                                function re_chargerefund() {
                                    if (confirm('Are you sure to charge or refund with the difference only ? ')) {
                                        window.location.href = '/admin/appointment/{{ $ap->appointment_id }}/chargerefund';
                                    }
                                }
                            </script>
                    @else
                        <div class="col-xs-8" align="left"> You can leave the amount difference. The difference will be charged or refunded at appointment completed.
                    @endif
                </div>
                <div>
            @endif

{{--        @php--}}
{{--        $cctrans_amt = \App\Model\CCTrans::where('appointment_id', $ap->appointment_id)--}}
{{--            ->where('type', 'S')--}}
{{--            ->where('category', 'S')--}}
{{--            ->where('result', 0)--}}
{{--            ->whereNull('void_date')--}}
{{--            ->where('amt', '!=', 0.01)--}}
{{--            ->sum('amt');--}}

{{--            if($cctrans_amt == '') {--}}
{{--                $cctrans_amt = 0;--}}
{{--            }--}}
{{--        @endphp--}}
{{--        @if ( $cctrans_amt > 0 )--}}
{{--        <div class="row">--}}
{{--            <div class="col-xs-3"><strong>Collected Amount</strong></div>--}}
{{--            <div class="col-xs-3"><strong>$ {{ $cctrans_amt }}</strong></div>--}}
{{--            <div class="col-xs-6">--}}
{{--                @if (!in_array($ap->status, ['C', 'L']) && $cctrans_amt != $ap->total )--}}
{{--                <button type="button" class="btn btn-danger" onclick="re_payment()"> Re-Payments</button>--}}
{{--                <script>--}}
{{--                    function re_payment() {--}}
{{--                        if (confirm('Are you sure to charge with new amounts ? ')) {--}}
{{--                            window.location.href = '/admin/appointment/{{ $ap->appointment_id }}/repayment';--}}
{{--                        }--}}
{{--                    }--}}
{{--                </script>--}}
{{--                @endif--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        @endif--}}

        @if($ap->new_credit > 0)
            <div class="row"></div>
            <div class="row">
                <div class="col-xs-3">New Credit Amount</div>
                <div class="col-xs-9" style="color:green;">${{ $ap->new_credit }}</div>
            </div>
        @endif

        @if ($ap->status != 'C' && $ap->status != 'P')
        <div class="row bg-danger">
            <div class="col-xs-3">Payment Information</div>
            <div class="col-xs-6">
                <select name="payment_id" id="payment_id" class="form-control">
                    <option value="">Select Credit Card</option>
                    @foreach ($payments as $p)
                    <option value="{{ $p->billing_id }}" {{ old('billing_id', $ap->payment_id) == $p->billing_id ? 'selected' : '' }}>
                        @if ($p->status !='A')[{!! $p->status_name() !!}] / @endif
                        {{ $p->card_holder }} /
                        {{ $p->card_number }} (ID: {{ $p->billing_id }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-xs-3 text-center"><button class="btn btn-danger" onclick="update_payment()">Change Credit Card</button></div>
        </div>
        @endif
        <div class="row">
            <div class="col-xs-3">Operation.Note</div>
            <div class="col-xs-6">
                <textarea id="op_note" name="op_note" style="width:100%" rows="5">{{ $ap->op_note }}</textarea>
            </div>
            <div class="col-xs-3 text-center">
                <button type="button" class="btn btn-danger" onclick="update_op_note()">Update</button>
            </div>
        </div>

        @if (count($op_notes) > 0)
            <div class="row no-border">
                <div class="col-xs-3">Operation.Note.History</div>
                <div class="col-xs-9"></div>
            </div>

            <div class="row">
                <div class="alert alert-warning col-xs-12">
                    <div class="row" style="font-weight: 600;">
                        <div class="col-xs-2">Appointment.ID</div>
                        <div class="col-xs-10">Note</div>
                    </div>
                    @foreach($op_notes as $o)
                        <div class="row">
                            <div class="col-xs-2">{{ $o->appointment_id }}</div>
                            <div class="col-xs-10">{{ $o->op_note }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <br><br><br>
    </div>

@stop