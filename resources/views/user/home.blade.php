@extends('user.layout.default')
@section('content')
    <link href="/desktop/css/login.css" rel="stylesheet">

    <link href="/desktop/css/dashboard.css" rel="stylesheet">
    <link href="/desktop/css/my-account.css" rel="stylesheet">

    <div id="main">
    <!-- PET-APPOINTMENT -->
    <section id="pet-appointment">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <h3 class=" text-center">Schedule Appointment</h3>
                    </div>
                    <div class="col-sm-3 col-sm-offset-3 col-xs-6 text-center">
                        <div class="row">
                            <div class="col-md-12">
                                <img class="img-responsive d-inline-block" src="/desktop/img/dog-icon.svg" width="120" height="120" alt="Schedule Dog">
                            </div>
                            <div class="col-md-12 mt-2 mb-2">
                                <a href="#/" onclick="click_schedule('/user/schedule/select-dog')" href="#/" class="groomit-btn red-btn pt-2 pb-2 rounded-btn" >
                                    Dog
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-6 text-center">
                        <div class="row">
                            <div class="col-md-12">
                                <img class="img-responsive d-inline-block" src="/desktop/img/cat-icon.svg" width="120" height="120" alt="Schedule Cat">
                            </div>
                            <div class="col-md-12 mt-2 mb-2">
                                <a href="#/" onclick="click_schedule('/user/schedule/select-cat')" href="#/" class="groomit-btn red-btn pt-2 pb-2 rounded-btn" >
                                    Cat
                                </a>
                            </div>
                        </div>
                    </div>
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
            </div>
        </section>
        <!-- /pet-appointment -->

        <section>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="row row-same-height">

                    @if (!empty($recent))
                        <div class="col-sm-6 mb-3">
                                <!-- Account info cont start-->
                                    <div class="groomit-card groomit-card-appt">
                                        
                                        
                                <ul class="flex-container space-between">
                                    <li class="flex-item">
                                        <div class="row row-flex">
                                                <div class="col-sm-12 mt-2 text-center mb-4">
                                                    <div class="inline-block text-center">
                                                        <h2 class="mb-2 text-center">Last Appointment</h2>
                                                    </div>  
                                                </div>
                                        </div>
                                    
                                        @foreach ($recent->pets as $o)
                                        <div class="row-flex align-items-start mb-3">
                                            <div class="col-sm-3 col-xs-4 pl-0 pr-0 text-center">
                                                @if (empty($o->photo))
                                                    <!--  avatar -->
                                                    <div class="circle-avatar">
                                                        <img src="/desktop/img/{{ $o->type }}-icon.svg" alt="{{ $o->pet_name }}">
                                                    </div>
                                                    <!--  /avatar -->
                                                @else
                                                    <!--  avatar -->
                                                    <div class="circle-avatar">
                                                        <img src="data:image/png;base64,{{ $o->photo }}" alt="{{ $o->pet_name }}">
                                                    </div>
                                                    <!--  /avatar -->
                                                @endif
                                            </div>
                                            <div class="col-sm-9 col-xs-8 text-left">
                                                <div class="inline-block text-left">
                                                    <p class="mb-0"><strong>{{ $o->pet_name }}</strong></p>
                                                    <p class="mb-0"><i>{{ $o->package_name }}</i>
                                                    @if( !empty($o->addons) && count($o->addons) > 0 )
                                                            @php
                                                                $inx = 0;
                                                                echo ' | <i> ';
                                                            @endphp
                                                            @foreach ($o->addons as $ao)
                                                                {{ $ao->prod_name }}
                                                                @if ( $inx == (count($o->addons) -1 ))
                                                                    .
                                                                @else
                                                                   ,
                                                                @endif
                                                                @php
                                                                    $inx++;
                                                                @endphp
                                                            @endforeach
                                                            </i>
                                                    @endif


                                                    </p>
                                                </div>  
                                            </div>
                                        </div>
                                        @endforeach




                                        <div class="row row-flex align-items-center mt-4 mb-3" >
                                                <div class="col-xs-6 col-flex pr-0">
                                                    <div class="row row-flex align-items-center">
                                                        <div class="col-xs-12 text-left mb-3 col-flex">
                                                            <span class="icon-groomit d-inline-flex align-middle">
                                                                <img src="/desktop/img/tade-time-icon.png" width="22" height="22" alt="date & time">
                                                            </span>
                                                            <div class="d-inline-flex m-0 align-middle">
                                                                <p class="m-0 align-middle info">
                                                                    <strong>{{ substr(Carbon\Carbon::parse($recent->accepted_date)->format('Y-m-d h:i A'), 0, 10) }}</strong>
                                                                    <label class="mt-0 m-0 align-middle">{{ substr(Carbon\Carbon::parse($recent->accepted_date)->format('Y-m-d h:i A'), 11) }}</label>
                                                                </p>
                                                            </div> 
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-6 col-flex ">
                                                    <div class="row row-flex align-items-center justify-content-end" > 
                                                        <div class="col-xs-12 text-left mb-3 col-flex">
                                                            <div class="justify-content-end d-flex align-items-center">
                                                                <span class="icon-groomit d-inline-flex align-middle">
                                                                    <img src="/desktop/img/id-icon.png" width="22" height="22" alt="date & time">
                                                                </span>
                                                                <p class="d-inline-flex m-0 align-middle info"><strong>ID:
                                                                    {{ $recent->appointment_id . Carbon\Carbon::parse($recent->accepted_date)->format('dm') }}</strong></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                         
                                             
                                             
                                             
                                                       
                                        <!-- pet cont start-->
                                        <div class="groomit-card groomit-card-pet groomit-card-tall-avatar mb-3">
                                                <div class="row align-items-center">
                                                    <div class="col-xs-4  text-center ">
                                                            <div class="cont-avatar">
                                                                <div class="avatar-loading">
                                                                    <p>...</p>
                                                                </div>
                                                                @if (!empty($recent->groomer->profile_photo))
                                                                    <img
                                                                         src="data:image/png;base64,{{ $recent->groomer->profile_photo }}"  alt="Avatar"/>
                                                                @else
                                                                    <img src="/images/upload-img.png"  alt="Avatar" />
                                                                @endif
                                                            </div>
                                                    </div>
                                                    <div class="col-xs-8 text-center ">
                                                        <div class="text-left">
                                                            <h3 class="mt-3">
                                                                <strong>{{ empty($recent->groomer) ? '' : ($recent->groomer->first_name . ' ' . $recent->groomer->last_name) }}</strong>
                                                            </h3>
                                                            <label class="mt-0">
                                                                @if ( $recent->groomer->dog == 'Y' && $recent->groomer->cat == 'Y' )
                                                                    Dog and Cat Groomer
                                                                @elseif ( $recent->groomer->dog == 'Y' )
                                                                    Dog  Groomer
                                                                @elseif ( $recent->groomer->cat == 'Y' )
                                                                    Cat  Groomer
                                                                @endif
                                                            </label>
                                                        </div>  

                                                        <div class="d-flex align-items-center">
                                                            <div class="text-left" >
                                                                <!-- data-rating = initial/data-base value -->
                                                                <div class="starrr" id="{{ $recent->appointment_id }}" data-rating="{{ $recent->rating }}"></div>
                                                                <input type="hidden" class="rating" name="rating" value=""/>
                                                                <label class="mt-0 mb-2">Rate your Groomer</label>
                                                            </div>   
                                                            <div class="text-right">
                                                                <p class="mb-0 pl-5">
                                                                <span class="fav-groomer-{{ $recent->groomer_id }} fav-groomer-i glyphicon glyphicon-heart{{ !empty($recent->groomer) && $recent->groomer->favorite == true ? '' : '-empty' }}"
                                                                      onclick="toggle_favorite_groomer('{{ $recent->appointment_id }}', '{{ $recent->groomer_id }}')">
                                                                </span>
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-sm-11 col-xs-12 col-md-10 col-lg-10">
                                                                <!-- Other tip: Edit -->
                                                                <div class="tip-to mt-3 mb-3">
                                                                    <input type="hidden" id="sub_total_{{ $recent->appointment_id }}" value="{{ $recent->sub_total }}">


                                                                    <div class="input-group d-flex justify-content-between tip-options pb-0 {{ is_null($recent->tip) ? '' : 'hidden' }}" >
                                                                        <h3 class="pr-2"><strong>Tip</strong></h3>
                                                                        <div class="input-tip-to">
                                                                            $
                                                                        </div>
                                                                        <input type="number" autocomplete="off" id="other_tip" name="other_tip" class="form-control input-tip-to-amount">
                                                                        <button type="button" class="btn" onclick="give_tip('{{ $recent->appointment_id }}' )">
                                                                                Submit
                                                                        </button>
                                                                    </div>
                                                                    <h3 class="other-tip text-left {{ is_null($recent->tip) ? 'hidden' : ''}}">
                                                                        <strong>Tip given: $<span  class="other-tip-val">{{ !is_null($recent->tip) ? number_format($recent->tip, 2) : '' }}</span></strong>
                                                                    </h3>

                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                    </div>
                                                    
                                                </div>
                                        </div>
                                        <!-- pet cont ends-->
                            </li>
                            <li class="flex-item">
                                        <div class="row">
                                            <div class="col-md-12 text-center">
                                                <a href="/user/schedule/select-rebook/{{ $recent->appointment_id }}" class=" mt-4 mb-4 groomit-btn outline-btn red-btn rounded-btn">
                                                    REBOOK
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                <!-- Account info cont ends-->   
                            </li>
                        </div>
                        @endif




                        @if (!empty($upcoming))
                        <div class="col-sm-6 mb-3">
                                <!-- Account info cont start-->
                                    <div class="groomit-card groomit-card-appt">
                                    <ul class="flex-container space-between">
                                    <li class="flex-item">
                                        <div class="row row-flex">
                                                <div class="col-sm-12 mt-2 text-center mb-4">
                                                    <div class="inline-block text-center">
                                                        <h2 class="mb-2 text-center">Next Appointment</h2>
                                                    </div>  
                                                </div>
                                        </div>
                                     
                                        @if (count($upcoming->pets) > 0)
                                        @foreach ($upcoming->pets as $o)
                                        <div class="row-flex align-items-start mb-3">
                                            <div class="col-sm-3 col-xs-4 pl-0 pr-0 text-center">
                                            @if (empty($o->photo))
                                                <!--  avatar -->
                                                    <div class="circle-avatar">
                                                        <img src="/desktop/img/{{ $o->type }}-icon.svg" alt="{{ $o->pet_name }}">
                                                    </div>
                                                    <!--  /avatar -->
                                            @else
                                                <!--  avatar -->
                                                    <div class="circle-avatar">
                                                        <img src="data:image/png;base64,{{ $o->photo }}" alt="{{ $o->pet_name }}">
                                                    </div>
                                                    <!--  /avatar -->
                                            @endif
                                            </div>
                                            <div class="col-sm-9 col-xs-8 text-left">
                                                <div class="inline-block text-left">
                                                    <p class="mb-0"><strong>{{ $o->pet_name }}</strong></p>
                                                    <p class="mb-0"><i>{{ $o->package_name }}</i>
                                                        @if( !empty($o->addons) && count($o->addons) > 0 )
                                                            @php
                                                                $inx = 0;
                                                                echo ' | <i> ';
                                                            @endphp
                                                            @foreach ($o->addons as $ao)
                                                                {{ $ao->prod_name }}
                                                                @if ( $inx == (count($o->addons) -1 ))
                                                                    .
                                                                @else
                                                                    ,
                                                                    @endif
                                                                    @php
                                                                        $inx++;
                                                                    @endphp
                                                                    @endforeach
                                                                    </i>
                                                                @endif


                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                        @endif

                                        <div class="row row-flex align-items-center mt-4 mb-3" >
                                                <div class="col-xs-6 col-flex  pr-0">
                                                    <div class="row row-flex align-items-center">
                                                        <div class="col-xs-12 text-left mb-3 col-flex">
                                                            <span class="icon-groomit d-inline-flex align-middle">
                                                                <img src="/desktop/img/tade-time-icon.png" width="22" height="22" alt="date & time">
                                                            </span>
                                                            <div class="d-inline-flex m-0 align-middle">
                                                                <p class="m-0 align-middle info">
                                                                    <strong>{{ substr(isset($upcoming->accepted_date) ? Carbon\Carbon::parse($upcoming->accepted_date)->format('Y-m-d h:i A') : $upcoming->reserved_at, 0, 10) }}</strong>
                                                                    <label class="mt-0 m-0 align-middle">{{ substr(isset($upcoming->accepted_date) ? Carbon\Carbon::parse($upcoming->accepted_date)->format('Y-m-d h:i A') : $upcoming->reserved_at, 11) }}</label>
                                                                </p>
                                                            </div> 
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-6 col-flex ">
                                                    <div class="row row-flex align-items-center justify-content-end" > 
                                                        <div class="col-xs-12 text-left mb-3 col-flex">
                                                            <div class="justify-content-end d-flex align-items-center">
                                                                <span class="icon-groomit d-inline-flex align-middle">
                                                                    <img src="/desktop/img/id-icon.png" width="22" height="22" alt="date & time">
                                                                </span>
                                                                <p class="d-inline-flex m-0 align-middle info"><strong>ID: {{ $upcoming->appointment_id . Carbon\Carbon::parse($upcoming->requested_date)->format('dm') }}</strong></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-flex ">
                                                    <div class="row row-flex align-items-center justify-content-start" > 
                                                        <div class="col-xs-12 text-left mb-3 col-flex">
                                                            <div class="d-flex text-left">
                                                                <span class="icon-groomit d-inline-flex align-middle">
                                                                    <img src="/desktop/img/location-icon.png" width="22" height="22" alt="location">
                                                                </span>
                                                                <p class="d-inline-flex m-0 align-middle info"><strong>
                                                                        {{ $recent->address_info->address1 }} {{ $recent->address_info->address2 }}<br>
                                                                        {{ $recent->address_info->city }} {{ $recent->address_info->state }} {{ $recent->address_info->zip }}
                                                                    </strong></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>

                                        @if (isset($upcoming->groomer))
                                        <div class="groomit-card groomit-card-pet groomit-card-tall-avatar mb-3">
                                                <div class="row align-items-center">
                                                    <div class="col-xs-4 text-center ">
                                                        <div class="cont-avatar">
                                                            {{--                                                                <div class="avatar-loading">--}}
                                                            {{--                                                                    <p>...</p>--}}
                                                            {{--                                                                </div>--}}
                                                            @if (!empty($upcoming->groomer->profile_photo))
                                                                <img
                                                                        src="data:image/png;base64,{{ $upcoming->groomer->profile_photo }}"  alt="Avatar"/>
                                                            @else
                                                                <img src="/images/upload-img.png"  alt="Avatar" />
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="col-xs-8 text-center ">
                                                        <div class="text-left">
                                                            <h3 class="mt-3">
                                                                <strong>{{ $upcoming->groomer->first_name . ' ' . $upcoming->groomer->last_name }}</strong>
                                                            </h3>
                                                            <label class="mt-0 mb-3">
                                                                @if ( $upcoming->groomer->dog == 'Y' && $upcoming->groomer->cat == 'Y' )
                                                                    Dog and Cat Groomer
                                                                @elseif ( $upcoming->groomer->dog == 'Y' )
                                                                    Dog  Groomer
                                                                @elseif ( $upcoming->groomer->cat == 'Y' )
                                                                    Cat  Groomer
                                                                @endif
                                                            </label>
                                                        </div>  
                                                    </div>

                                                </div>
                                        </div>
                                        @else
                                        <div class="groomit-card groomit-card-pet groomit-card-tall-avatar mb-3">
                                                <div class="row align-items-center">
                                                    <div class="col-xs-12 text-center ">
                                                        <div class="text-center">
                                                            <h3 class="mt-4 mb-4">
                                                                <strong>Groomer Not Asigned</strong>
                                                            </h3>
                                                        </div>  
                                                    </div>
                                                    
                                                </div>
                                        </div>
                                        @endif

                                    </li>
                                    <li class="flex-item">


                                        <div class="row">
                                            @if (isset($upcoming->pets[0]->package_name) && $upcoming->pets[0]->package_name != 'ECO')
                                            <div class="row">
                                                    <div class="col-xs-12">
                                                        <p class="text-center"><em>Full refund if you cancel by <strong>6:00 pm</strong> the day before service time.</em></p>
                                                    </div>
                                            </div>

                                            <div class="col-md-12 text-center">
                                                <a href="#/" class=" mt-4 mb-4 groomit-btn outline-btn red-btn rounded-btn" data-toggle="modal" data-target="#modify-alert" data-appointment="{{ $upcoming->appointment_id }}" >
                                                    MODIFY
                                                </a>
                                            </div>
                                            @else
                                                <div class="col-xs-12">
                                                    <p class="text-center"><strong>Eco Package can't be cancelled.<br>Please contact Customer Service.</strong></p>
                                                </div>
                                            @endif
                                        </div>
                
                                        </li>

                                    </div>
                                <!-- Account info cont ends-->   

                        </div>
                        @endif



                        @if (empty($upcoming) && empty($recent))
                        <div class="col-sm-6 col-sm-offset-3 mb-3">
                        @endif

                        @if (empty($upcoming) && !empty($recent))
                        <div class="col-sm-6 mb-3">
                        @endif
                        @if (!empty($upcoming) && empty($recent))
                        <div class="col-sm-6 mb-3">
                        @endif

                        @if (empty($upcoming) || empty($recent))
                                <!-- groomit-card-carousel start-->
                                    <div class="groomit-card groomit-card-carousel">
                                        <div class="row h100p">
                                            <div class="col-sm-12 text-left mt-0 h100p">
                                                <div id="homeUserCarousel" class="carousel slide h100p" data-ride="carousel">
                                                    <!-- Indicators -->
                                                    <ol class="carousel-indicators">
                                                        <li data-target="#homeUserCarousel" data-slide-to="0" class="active"></li>
{{--                                                        <li data-target="#homeUserCarousel" data-slide-to="1"></li>--}}
{{--                                                        <li data-target="#homeUserCarousel" data-slide-to="2"></li>--}}

                                                    </ol>

                                                    <!-- Wrapper for slides -->
                                                    <div class="carousel-inner">

                                                        <div class="item active">
                                                        <img src="https://www.groomit.me/images/wefunder-promo.jpg" alt="wefunder.com" style="width:100%;">
                                                        </div>
                                                        
{{--                                                        <div class="item">--}}
{{--                                                        <img src="https://www.groomit.me/images/promo_dog_new2.jpg" alt="Dog ECO" style="width:100%;">--}}
{{--                                                        </div>--}}

{{--                                                        <div class="item">--}}
{{--                                                            <img src="https://www.groomit.me/images/promo_cat_new2.jpg" alt="Cat ECO" style="width:100%;">--}}
{{--                                                        </div>--}}
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <!-- groomit-card-carousel ends-->   
                        </div>
                        @endif




                    </div>                
                </div>
            </div>
                
        </div>
        
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



<!-- fav Groomer modal -->
                                        <div class="modal fade auto-width modal-fav-added" id="fav-groomer-info-GROOMERID" tabindex="-1" role="dialog" aria-labelledby="">
                                            <div class="modal-dialog auto-width" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close close-reschedule" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row-pet" >
                                                            <div class="text-center">
                                                                <div class="history-pet mt-0">
                                                                    <div class="row">
                                                                        <div class="col-sm-4 col-sm-offset-4 col-xs-6 col-xs-offset-3 mb-3">
                                                                            <div class="circle-avatar  preload">
                                                                                @if ( !empty($recent->groomer->profile_photo))
                                                                                    <img src="data:image/png;base64,{{ $recent->groomer->profile_photo }}"  alt="Avatar"/>
                                                                                @else
                                                                                    <img src="/desktop/img/dog-icon.svg"  alt="Avatar" />
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <p class="text-center"><strong>{{ empty($recent->groomer) ? '' : ($recent->groomer->first_name . ' ' . $recent->groomer->last_name) }}</strong></p>
                                                                    <p class="text-center">
                                                                        {{ empty($recent->groomer) ? '' : ($recent->groomer->first_name . ' ' . $recent->groomer->last_name) }} was <strong>succesfully added</strong><br>as your favorite groomer.
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12 text-center">
                                                                <a href="#/" data-dismiss="modal" aria-label="Close" class=" mt-4 mb-4 groomit-btn outline-btn red-btn rounded-btn">
                                                                    OK
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>



<!-- /fav groomer modal --> 

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
                            //$('.other-tip-edit').addClass('hidden');
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
                            loadFavModal();
                        }
                    } else {
                        myApp.showError(res.msg);
                    }
                }

            });
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


        

        function loadFavModal(){
            $('#fav-groomer-info-GROOMERID').modal('show');

            $(function(){
                $('#fav-groomer-info-GROOMERID').on('shown.bs.modal', function () {
                    circleAvatarFitImage();
                        $(".circle-avatar").removeClass("preload");
                });
            });
        }
        

        function groomitCardFitImage() {
        var realHeight = 0;
        var realWidth = 0;
         $(".groomit-card-tall-avatar").each(function(){
            cardH = $(this).innerHeight();
            maskW = ($(this).find(".cont-avatar").innerWidth())-15;
            maskPhotoCover = ($(this).find(".avatar-loading"));
            var originalImg = $(this).find( ".cont-avatar img" );
            groomitPhotoH = originalImg.height();
            groomitPhotoW = originalImg.width();
            pNewImgH = (cardH*100)/groomitPhotoH;
            newImgW = (groomitPhotoW*pNewImgH)/100;
            newImgH = (groomitPhotoH*pNewImgH)/100;
            toCenterMargin = 0;
            if( (newImgW>=maskW)&&(newImgH>=cardH) ){
                originalImg.height(newImgH);
                toCenterMargin = (maskW-newImgW)/2;
                maskPhotoCover.fadeOut( "fast" );
                originalImg.css("margin-left", toCenterMargin+"px");
            }
            else {
                if( (newImgW<=maskW)&&(newImgH>=cardH) ){
                    maskPhotoCover.fadeOut( "fast" );
                    originalImg.width(maskW);
                }else if( (newImgH>=cardH)&&(newImgW>=maskW) ){
                    originalImg.height(newImgH);
                    maskPhotoCover.fadeOut( "fast" );

                }
            }
         });
     }

     

     function circleAvatarFitImage() {
        $(".circle-avatar").each(function(){
            var thisCircleAvatar = $(this);
            var contCircleAvatarW = thisCircleAvatar.innerWidth();
            var img = new Image();
            img.onload = function() {
                groomitPhotoHCircle = this.height;
                groomitPhotoWCircle = this.width;
                originalImgCircle = thisCircleAvatar.find( "img" );
                var pNewImgWCircle = ((contCircleAvatarW*100)/groomitPhotoWCircle);
                var pNewImgHCircle = ((contCircleAvatarW*100)/groomitPhotoHCircle);
                var newImgWCircle = (groomitPhotoWCircle*pNewImgWCircle)/100;
                var newImgHCircle = (groomitPhotoHCircle*pNewImgWCircle)/100;
                thisCircleAvatar.height(contCircleAvatarW);
                minHW= Math.min(newImgWCircle, newImgHCircle);
                if( (newImgHCircle>=contCircleAvatarW)&&(newImgWCircle==minHW) ){
                    originalImgCircle.width(contCircleAvatarW);
                    toCenterMarginCircle = -((newImgHCircle-contCircleAvatarW)/2);
                    originalImgCircle.css("margin-top", (toCenterMarginCircle)+"px");
                }else if( (newImgWCircle>=contCircleAvatarW)&&(newImgHCircle==minHW) ){
                    originalImgCircle.height(contCircleAvatarW);
                    toCenterMarginCircle = (((pNewImgHCircle*groomitPhotoWCircle)/100)-newImgWCircle)/2;
                    originalImgCircle.css("margin-left", -(toCenterMarginCircle)+"px");
                } else{
                    console.log(minHW);
                }
            }
            img.src = $(this).find( "img" ).attr('src');
        });
    }

    $(window).load(function() {
        groomitCardFitImage();
        circleAvatarFitImage();
    });
    $(window).resize(function() {
        groomitCardFitImage();
        circleAvatarFitImage();
    });
    

    </script>
@stop