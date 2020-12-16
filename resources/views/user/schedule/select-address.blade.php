@extends('user.layout.default')
@section('content')
    <link href="/desktop/css/appointment.css" rel="stylesheet" type="text/css">
    <link href="/desktop/css/material-forms.css" rel="stylesheet" type="text/css">

    <!-- PROGRESS -->
    <div class="container-fluid" id="progress-bar">
        <div class="row">
            <div class="col-xs-2 line-status complete"></div>
			<div class="col-xs-2 line-status complete"></div>
			<div class="col-xs-2 line-status complete"></div>
			<div class="col-xs-2 line-status complete"></div>
			<div class="col-xs-2 line-status"></div>
			<div class="col-xs-2 line-status"></div>
        </div>
        <!-- row -->
    </div>
    <!-- /progress-bar -->
    <div id="main">
        <!-- MY ADDRESS -->
        <section id="my-payments">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h3><span class="title-icon"><img src="/desktop/img/address-icon.png" width="20" height="29"
                                                          alt="Addresses"></span>ADDRESS</h3>
                    </div>
                </div>
                <!-- /row -->
                <div class="row after-title">

                    <div>
                        <!-- CARDS -->

                    @if (count($addresses) > 0)
                        @foreach ($addresses as $o)
                            <!-- CARD DEFAULT -->
                                <div class="col-lg-3 col-md-4 col-sm-6 card-wrapper">
                                    <div class="media my-card {{ Schedule::getAddressId() == $o->address_id ? 'selected' : '' }}" onclick="select_address('{{ $o->address_id }}')">
                                        <div class="media-left media-middle">
                                            <input type="radio" name="address_id"
                                                   value="{{ $o->address_id }}" {{ Schedule::getAddressId() == $o->address_id ? 'checked' : '' }}>
                                        </div>
                                        <div class="media-body media-middle">
                                            <div class="display-table">
                                                <div class="table-cell media-middle">
                                                    <p><strong>{{ $o->address1 }} {{ $o->address2 }},<br /> {{ $o->city }}
                                                            {{ $o->state }}, {{ $o->zip }}</strong></p>
                                                </div>
                                                <!-- <div class="table-cell media-middle text-right" style="z-index:9999;"><a
                                                            style="cursor:pointer;" onclick="show_address(event, '{{ $o->address_id }}')">
                                                       <img src="/desktop/img/ionicons_2-0-1_ios-information-outline_48_0_bd1a2a_none.png" width="22" height="22" alt="More Info" /></a>
                                                </div> -->
                                            </div>



                                            <!-- /row -->
                                        </div>
                                        <!-- /media-body -->

                                    </div>
                                    <!-- /my-card -->

                                      <div class="col-md-5 col-sm-5 col-md-offset-1 col-sm-offset-1">
                                        <button type="button" class="payment-info-btn groomit-btn black-btn
                                        rounded-btn long-btn" onclick="show_address(event, '{{ $o->address_id }}',
                                                'view')">
                                            View
                                        </button>
                                      </div>
                                      <div class="col-md-5 col-sm-5 col-md-offsetd">
                                        <button type="button" class="payment-info-btn groomit-btn red-btn rounded-btn
                                         long-btn" onclick="show_address(event, '{{ $o->address_id }}', 'edit')">
                                            Edit
                                        </button>
                                      </div>

                                </div>
                                <!-- /col-3 -->
                        @endforeach
                    @else
                        <!-- ADD NEW -->
                            <div class="col-lg-3 col-md-4 col-sm-6 card-wrapper">
                                <div class="media my-card" id="add-new-card" onclick="show_address()">
                                    <div class="media-body media-middle">
                                        <div class="display-table">
                                            <div class="table-cell media-middle">
                                                <div class="media-left media-middle"><span
                                                            class="glyphicon glyphicon-plus-sign"
                                                            aria-hidden="true"></span>
                                                </div>
                                            </div>
                                            <div class="table-cell media-middle">
                                                <h4 class="media-heading text-left">ADD A NEW<br>
                                                    ADDRESS</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /media-body -->

                                </div>
                                <!-- /my-dog-card -->
                            </div>
                            <!-- /col-3 -->
                    @endif
                    </div>
                </div>
                <!-- /row -->
            </div>
            <!-- /container -->
        </section>
        <!-- /my-address -->

        <section id="next-btn">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <a href="/user/schedule/select-date" class="groomit-btn rounded-btn black-btn big-btn space-btn"><img class="arrow-back" src="/desktop/img/arrow-back.png" width="14" height="18" alt="Back" /> BACK &nbsp;</a>
                        <a href="/user/schedule/select-payment" id="btn_continue" class="groomit-btn rounded-btn red-btn big-btn" style="display:{{ Schedule::getAddressId() ? '' : 'none' }};">CONTINUE</a>
                    </div>
                </div>
                <!-- row -->
            </div>
            <!-- container -->
        </section>
    </div>
    <!-- /main -->

    <!-- MODALS -->

    <!-- EDIT ADDRESS -->
    <div class="modal fade" id="modal-address" tabindex="-1" role="dialog" aria-labelledby="editAddressLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h4 class="modal-title text-center" id="editAddressLabel">ADDRESS</h4>

                    <!-- INIT FORM -->
                    <form action="" role="form" method="post" id="frm_address">
                        <input type="hidden" id="address_id" name="address_id"/>
                        <fieldset>

                            <!-- ADDRESS -->
                            <section id="payment">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                            	<label class="control-label" for="">ADDRESS</label>
                                                <input class="form-control" name="address1"
                                                       id="address1" type="text" maxlenght="200"
                                                       value="{{ empty($address1) ? '' : $address1 }}"
                                                       required/>
                                                <span class="form-highlight"></span>
                                                <span class="form-bar"></span>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- col-6 -->
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                            <label class="control-label" for="address2">APT # &nbsp;
                                                        <input type="checkbox" id="house" name="house" value="house"/>
                                                        House
                                                </label>
                                                <input class="form-control" name="address2" id="address2" type="text"
                                                       maxlenght="20"/>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                

                                            </div>
                                            <!-- /form-group -->
                                        </div>

                                        <!-- /col-6 -->
                                    </div>
                                    <!-- /row -->
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="control-label" for="city">CITY</label>
                                                <input class="form-control" name="city" id="city" type="text"
                                                       maxlenght="50" value="{{ empty($city) ? '' : $city }}" required/>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                
                                            </div>
                                            <!-- /form-group -->

                                        </div>
                                        <!-- col-6 -->
                                        <div class="col-sm-6">
                                            <div class="row">
                                                <div class="col-xs-6">
                                                    <div class="form-group">
                                                        <label class="control-label" for="">STATE</label>
                                                        <select class="form-control" name="state" id="state" required>
                                                            <option value="">Please Select</option>
                                                            @if (count($states) > 0)
                                                                @foreach ($states as $o)
                                                                    <option value="{{ $o->code }}" {{ old('state', $state) == $o->code ? 'selected' : '' }}>{{ $o->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        <span class="form-highlight"></span> <span
                                                                class="form-bar"></span>
                                                        
                                                    </div>
                                                    <!-- /form-group -->
                                                </div>
                                                <!-- /col -->
                                                <div class="col-xs-6">
                                                    <div class="form-group">
                                                       <label class="control-label" for="zip">ZIP</label>
                                                        <input class="form-control" name="zip" id="zip"
                                                               type="text" maxlength="5" value="{{ empty($zip) ? '' : $zip }}"/>
                                                        <span class="form-highlight"></span>
                                                        <span class="form-bar"></span>
                                                        
                                                    </div>
                                                    <!-- /form-group -->
                                                </div>
                                                <!-- /col -->
                                            </div>
                                            <!-- /row -->
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <!-- /row -->
                                </div>
                                <!-- /container -->
                            </section>
                            <section id="confirm-payment">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-xs-12 text-center">
                                            <button type="button" class="groomit-btn black-btn rounded-btn long-btn space-btn" id="btn_cancel" style="display:none"
                                                    onclick="change_mode('view')">CANCEL
                                            </button>
                                             <button type="button" class="groomit-btn black-btn rounded-btn long-btn space-btn" id="btn_close" data-dismiss="modal" aria-label="Close" style="display:none">CLOSE
                                            </button>
                                            <button type="button" class="groomit-btn red-btn rounded-btn long-btn" id="btn_edit" style="display:none"
                                                    onclick="change_mode('edit')">EDIT
                                            </button>
                                            <button type="button" class="groomit-btn red-btn rounded-btn long-btn" id="btn_submit" style="display:none"
                                                    onclick="save_address()">SUBMIT
                                            </button>
                                        </div>
                                    </div>
                                    <!-- row -->
                                </div>
                                <!-- /container -->
                            </section>
                        </fieldset>
                    </form>

                    <!-- /FORM -->

                </div>
                <!-- /modal-body -->

            </div>
            <!-- /modal-content -->

        </div>
    </div>
    <!-- /editAddressModal -->

    <form id="frm_submit" method="post" action="/user/schedule/select-address/post">
        {!! csrf_field() !!}
    </form>

    <script type="text/javascript">
        var onload_func = window.onload;
        window.onload = function() {
            if (onload_func) {
                onload_func();
            }

            var force_edit_zip_only_id = '{{ $force_edit_zip_only_id }}';
            if ($.trim(force_edit_zip_only_id) !== '') {
                show_address(null, force_edit_zip_only_id, 'edit');
            }
        }

        function select_address(address_id) {
            $('#btn_continue').hide();

            $.ajax({
                url: '/user/schedule/select-address/post',
                data: {
                    _token: '{!! csrf_token() !!}',
                    selected_address_id: address_id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        $('#btn_continue').show();
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function go_to_next() {

            window.location.href = '/user/schedule/select-payment';
        }

        function change_mode(mode) {
            if (mode === 'view') {
                $('#frm_address input,select,textarea,label,div').prop('disabled', true);
                $('#frm_address input,select,textarea,label,div').prop('aria-disabled', true);
                $('#btn_edit').show();
				$('#btn_close').show();
                $('#btn_submit').hide();
                $('#btn_cancel').hide();

                $('#frm_address label').addClass('disabled');
            } else {
                $('#frm_address input,select,textarea,label,div').prop('disabled', false);
                $('#frm_address input,select,textarea,label,div').prop('aria-disabled', false);
                $('#state').prop('disabled', true);
                $('#zip').prop('disabled', true);
                $('#btn_edit').hide();
				$('#btn_close').show();
                $('#btn_submit').show();
                $('#btn_cancel').hide();

                $('#frm_address label').removeClass('disabled');
            }
        }

        function show_address(e, address_id, view_mode) {
            if (!e) var e = window.event;
            e.cancelBubble = true;
            if (e.stopPropagation) e.stopPropagation();

            var mode = typeof address_id === 'undefined' ? 'add' : 'update';
            if (mode === 'add') {
                $('#address_id').val('');
                $('#address1').val('');
                $('#address2').val('');
                $('#city').val('');
                $('#state').val('');
                $('#zip').val();

                change_mode('edit');

                $('#modal-address').modal();
            } else {
                myApp.showLoading();
                $.ajax({
                    url: '/user/address/load',
                    data: {
                        _token: '{!! csrf_token() !!}',
                        address_id: address_id
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function (res) {
                        myApp.hideLoading();
                        if ($.trim(res.msg) === '') {

                            var o = res.data;
                            if(o.address1.length > 0) {
                                $('#address1').val(o.address1);
                            }
                            $('#address2').val(o.address2);
                            if(o.city.length > 0) {
                                $('#city').val(o.city);
                            }
                            if(o.state.length > 0) {
                                $('#state').val(o.state);
                            }
                            $('#zip').val(o.zip);
                            $('#address_id').val(o.address_id);

                            if (typeof view_mode === 'undefined') {
                                view_mode = 'view';
                            }

                            change_mode(view_mode);
                            $('#modal-address').modal();
                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                });
            }
        }

        function save_address() {
            var address_id = $('#address_id').val();
            var mode = $.trim(address_id) === '' ? 'add' : 'update';
            var address2 = $('#address2').val();

            if (!$("#house").prop('checked')) {
                if ($.trim(address2) == '') {
                    alert('Please enter your APT #, if your home is not House.');
                    $('#address2').focus();
                    return;
                }
            }

            myApp.showLoading();
            $.ajax({
                url: '/user/address/' + mode,
                data: {
                    _token: '{!! csrf_token() !!}',
                    address_id: address_id,
                    address1: $('#address1').val(),
                    address2: $('#address2').val(),
                    city: $('#city').val(),
                    state: $('#state').val(),
                    zip: $('#zip').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        myApp.showSuccess('Thank you, your request has been processed successfully!', function () {
                            window.location.reload();
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

    </script>

    <script>

        function initMap() {
            var input = document.getElementById('address1');
            var options = {
                componentRestrictions: {country: "us"}
            };
            var autocomplete = new google.maps.places.Autocomplete(input, options);
            autocomplete.setFields(['address_components', 'geometry', 'icon', 'name']);
            autocomplete.addListener('place_changed', function() {
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                    window.alert("No details available for input: '" + place.name + "'");
                    return;
                }

                var address1  = '';
                var city      = '';
                var state     = '';
                var zip       = '';

                var street_number = '';
                var route         = '';
                var locality      = '';
                var administrative_area_level_1 = '';
                var postal_code   = '';

                for (var i = 0; i < place.address_components.length; i++) {
                    if (place.address_components[i].types[0] == "street_number") {
                        street_number = place.address_components[i].short_name;
                    }
                    if (place.address_components[i].types[0] == "route") {
                        route = place.address_components[i].short_name;
                    }
                    if (place.address_components[i].types[0] == "locality") {
                        locality = place.address_components[i].short_name;
                    }
                    if (place.address_components[i].types[0] == "administrative_area_level_1") {
                        administrative_area_level_1 = place.address_components[i].short_name;
                    }
                    if (place.address_components[i].types[0] == "postal_code") {
                        postal_code = place.address_components[i].short_name;
                    }
                }

                var address1  = street_number + " " + route;
                var city      = locality;
                var state     = administrative_area_level_1;
                var zip       = postal_code;

                // alert(address1 + city + state + zip);

                $('#address1').val(address1);
                $('#city').val(city);
                $('#state').val(state);
                $('#zip').val(zip);

                $('#continue').prop('disabled', false);

            });
        }
    </script>
    <style>
        .pac-container {
            z-index: 10000 !important;
        }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBQGtikp7nu9OJqF2Ogds59SsilNlPYLTw&libraries=places&callback=initMap"
            async defer>
    </script>

@stop
