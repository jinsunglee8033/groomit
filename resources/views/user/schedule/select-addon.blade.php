@extends('user.layout.default')
@section('content')
    <link href="/desktop/css/appointment.css?v=1.0.2" rel="stylesheet">
    <!-- PROGRESS -->
    <div class="container-fluid" id="progress-bar">
        <div class="row">
            <div class="col-xs-2 line-status complete"></div>
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
        <!-- ADD ONS -->
        <section id="add-ons">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h3>SELECT ADD-ON</h3>
                    </div>

                    @if (count($shampoos) >= 4)
                        @php ($col = "col-md-12")
                        @php ($class = "shampoo-row")
                    @else
                        @php ($col = "col-md-7")
                        @php ($class = "shampoo-col")
                    @endif

                    <div class="col-lg-10 col-lg-offset-1" id="st-opt">
                        <div class="row {{ $class }}">

                            <!-- / IS MATTED? -->
                            @if (!in_array(Schedule::getCurrentPackageId(), [2,16,27,29]) && isset($dematting))
                                <div class="col-md-5 col-sm-8 col-sm-offset-2 col-md-offset-0 text-center xs-center center-col">
                                    <h4 class="text-center" style="text-transform: capitalize;">Is your {{ Schedule::getCurrentPetType() }} matted?</h4>
                                    <div class="btn-group btn-group-justified" data-toggle="buttons" id="is-matted">
                                        <Do class="btn btn-st-opt  {{ Schedule::demattingSelected() ? 'active' : '' }}" data-toggle="modal" data-target="#demattingTerms" onclick="select_addon('{{ $dematting->prod_id }}', true)">
                                            <input type="radio" name="matted" id="matted" autocomplete="off" {{ Schedule::demattingSelected() ? 'checked' : '' }}>
                                            <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>Yes | ${{ $dematting->denom }} </Do>
                                        <label class="btn btn-st-opt {{ !Schedule::demattingSelected() ? 'checked' : '' }}" onclick="select_addon('{{ $dematting->prod_id }}', false)">
                                            <input type="radio" name="not-matted" id="not-matted" autocomplete="off" {{ !Schedule::demattingSelected() ? 'checked' : '' }}>
                                            <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>No </label>
                                    </div>
                                </div>
                        @endif
                        <!-- /col-5 -->
                        </div>
                        <!-- SHAMPOO -->
                        <div class="row">
                            <div class="{{ $col }} {{ $class }} xs-center center-col last">
                                <h4 class="text-center">Select an Organic Shampoo</h4>
                                <img id="pn-logo" src="/desktop/img/basics.png" width="118" height="94" alt="Basics" />
                                <div class="btn-group btn-group-justified" data-toggle="buttons" id="shampoo">
                                    @foreach ($shampoos as $o)
                                        <label class="btn btn-st-opt {{ $o->prod_id == Schedule::getCurrentShampooId() ? 'active' : '' }}"
                                               onclick="select_shampoo('{{ $o->prod_id }}')">
                                            <input type="radio"
                                                   name="shampoo"
                                                   value="{{ $o->prod_id }}"
                                                   autocomplete="off"
                                                    {{ $o->prod_id == Schedule::getCurrentShampooId() ? 'checked' : '' }}>
                                            <div class="display-table text-left shampoo-data">
                                                <span class="table-cell shampoo-name"><span class="s-name"><strong>{{ $o->prod_name }}{{ $o->denom > 0 ? ' | $' . $o->denom : '' }}</strong></span></span><a class="table-cell shampoo-info" href="" data-toggle="modal" data-target="#shampooInfoModal" data-shampoo="{{ $o->prod_id }}"><img src="/desktop/img/ionicons_2-0-1_ios-information-outline_48_0_bd1a2a_none.png" width="22" height="22" alt="More Info" /></a></div> </label>
                                    @endforeach </div>
                            </div>
                            <!-- /col-7 -->


                        </div>
                        <!-- /row -->
                    </div>
                    <!-- /col-8 -->
                </div>
                <!-- /row -->
            </div>
            <!-- /container -->
        </section>
        <!-- /add-ons -->

        <!-- OTHER ADD ONS -->
        <section id="other-addons">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                        <div class="row">
                            <form id="frm_addon" method="post" action="/user/schedule/select-addon/addon">
                            {!! csrf_field() !!}
                            <!-- ROW 1 -->
                                <!-- set chunks of items to create columns (with or without de-matting) -->
                                @if (isset($dematting))
                                    @php ($demattingItem = 1)
                                @else
                                    @php ($demattingItem = 0)
                                @endif

                                @php ($itemsPerChunk = floor((count($add_ons)-$demattingItem)/2))
                                @php ($count = 1)
                                @foreach($add_ons as $o)
                                    @if (!in_array($o->prod_id, [14, 18]))

                                        @if ($count == 1 || $count == $itemsPerChunk+1)
                                            <div class="col-sm-6"> @endif
                                                <div class="form-horizontal">
                                                    <div class="form-group">
                                                        <div class="checkbox col-xs-12">
                                                            <label for="add_on_{{ $o->prod_id }}">
                                                                <input type="checkbox" name="add_on[]"
                                                                       id="add_on_{{ $o->prod_id }}"
                                                                       onchange="select_addon('{{ $o->prod_id }}')"
                                                                       value="{{ $o->prod_id }}" {{ Schedule::addonChecked($o->
                        prod_id) ? 'checked' : '' }}> <span class="cr"><i
                                                                            class="cr-icon glyphicon glyphicon-ok"></i></span> <span class="addon-name">{{ $o->prod_name }}</span> </label>
                                                            <span class="pull-right tooltip-col"><a
                                                                        role="button" data-toggle="collapse"
                                                                        data-target="#info-checkbox-{{ $o->prod_id }}" aria-expanded="false"
                                                                        aria-controls="info-checkbox-{{ $o->prod_id }}"> <span
                                                                            class="glyphicon glyphicon-chevron-down"
                                                                            aria-hidden="true"></span> </a> </span> <span
                                                                    class="addon-price pull-right">${{ number_format($o->denom, 2) }}</span>
                                                            <div class="collapse" id="info-checkbox-{{ $o->prod_id }}">
                                                                <div class="addon-info-mobile"><em>{{ $o->prod_desc }}</em></div>
                                                            </div>
                                                            <!-- /collapse -->
                                                        </div>
                                                        <!-- /checkbox -->
                                                    </div>
                                                </div>
                                                <!-- /form-horizontal -->

                                                @if ($count == ($itemsPerChunk) || $count == (count($add_ons)-$demattingItem)) </div>
                                        @endif
                                        @php ($count++)
                                    @endif
                                @endforeach
                            </form>
                        </div>
                        <!-- /row -->
                    </div>
                    <!-- /col-8 -->
                </div>
                <!-- /row -->
            </div>
            <!-- /container -->
        </section>
        <!-- /other-addons -->

        <section id="next-btn">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <a class="groomit-btn rounded-btn black-btn big-btn space-btn" href="{{ $back_url }}">
                            <img class="arrow-back" src="/desktop/img/arrow-back.png" width="14" height="18" alt="Back" /> BACK &nbsp;</a>
                        <a class="groomit-btn rounded-btn red-btn big-btn" href="javascript:go_next()">CONTINUE</a>
                    </div>
                </div>
                <!-- row -->
            </div>
            <!-- container -->
        </section>
    </div>
    <form id="frm_shampoo" method="post" style="display:none;" action="/user/schedule/select-addon/shampoo">
        {!! csrf_field() !!}
        <input type="hidden" name="shampoo" id="shampoo"/>
    </form>
    <form id="frm_dematting" method="post" style="display:none;" action="/user/schedule/select-addon/dematting">
        {!! csrf_field() !!}
        <input type="hidden" name="dematting" id="dematting"/>
    </form>

    <!-- SHAMPOO INFO -->
    <div class="modal fade" id="shampooInfoModal" tabindex="-1" role="dialog" aria-labelledby="shampooInfoLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body text-center">
                    <h4 class="modal-title shampooName" id="shampooInfoLabel">Lavender & Sweet Almond</h4>
                    <p class="shampooInfo">Dog Shampoo & Conditioner</p>
                    <br>
                    <div class="text-center"><img class="imageName" src="/desktop/img/shampoo/lavender.jpg" /></div>
                    <p class="shampooDescription"><span class='green'>USDA Organic.</span> Chemical, paraben, preservatives and fragrance free. Antibacterial & antifungal.</p>
                </div>
                <!-- /modal-body -->
                <div class="modal-footer text-center">
                    <button type="button" class="btn btn-default" data-dismiss="modal">OK</button>
                </div>
            </div>
            <!-- /modal-content -->

        </div>
    </div>
    <!-- /modal -->

    <!-- DE-MATTING AGREEMENT MODAL -->
    <div class="modal fade" id="demattingTerms" tabindex="-1" role="dialog" aria-labelledby="dm-terms-label">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <br>
                    <h3 class="text-center">Matted Pets</h3>
                    <br>
                    <p class="text-center" id="dm-terms-label">Severely tangled and/or matted pets, are at a greater risk of discomfort and pain. All precautions will be taken. However, problems occasionally arise, during or after the grooming, such as nicks, discomfort, and clipper irritation.</p>
                    <br>
                    <p class="text-center" id="back-form">
                        <a class="groomit-btn rounded-btn red-btn" data-dismiss="modal" aria-label="I agree" href="#" onclick="i_agree()">I agree</a>
                    </p>
                </div>
                <!-- /terms -->
            </div>
            <!-- /modal-content -->

        </div>
    </div>
    <!-- /MODAL-->

    <script type="text/javascript">

        var demat_clicked = 'no';
        var demat_agreed = 'no';

        function i_agree(){
            demat_agreed = 'yes';
        }

        function click_agree(email){
            $.ajax({
                url: '/user/schedule/select-addon/sendTerm',
                data: {
                    _token: '{!! csrf_token() !!}',
                    email: email
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        refresh_header(res);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            })
        }

        function select_shampoo(shampoo) {

            $.ajax({
                url: '/user/schedule/select-addon/update-shampoo',
                data: {
                    _token: '{!! csrf_token() !!}',
                    shampoo: shampoo
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        refresh_header(res);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            })
        }

        function refresh_header(res) {
            $('#current_shampoo_name').text(res.current_shampoo.prod_name);
            $('#current_addons').empty();
            if (res.current_addons) {
                $.each(res.current_addons, function(i, o) {
                    var html = '<span class="add-on" style="color:#cdcdcd;">' + o.prod_name + ' | ' + '$' + parseFloat(o.denom).toFixed(2) + '</div>'
                    $('#current_addons').append(html);
                });
            }
            $('#current_sub_total').text('$' + parseFloat(res.current_sub_total).toFixed(2));
        }

        function select_addon(prod_id, checked) {

            if (typeof checked === 'undefined') {
                checked = $('#add_on_' + prod_id).is(':checked');
            }

            var action = checked ? 'add' : 'remove';

            if( (action === 'add' && prod_id ==='14') || (action === 'add' && prod_id ==='18') ){
                demat_clicked = 'yes';
            }else if( (action === 'remove' && prod_id ==='14') || (action === 'remove' && prod_id ==='18') ){
                demat_clicked = 'no';
            }
            $.ajax({
                url: '/user/schedule/select-addon/' + action,
                data: {
                    _token: '{{ csrf_token() }}',
                    addon_id: prod_id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        refresh_header(res);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function go_next() {

            if (demat_clicked ==='yes' && demat_agreed === 'no'){
                myApp.showError('Please click I Agree button first!');
                return;
            }

            var shampoo = $('input[name="shampoo"]:checked').val();
            if ($.trim(shampoo) === '') {
                myApp.showError('Please select shampoo first!');
                return;
            }

            window.location.href = '/user/schedule/select-pet';
        }
    </script>

    <style>
        .disabled {
            pointer-events: none;
            cursor: default;
        }
    </style>

@stop
