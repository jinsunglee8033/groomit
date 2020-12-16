@extends('user.layout.default')
@section('content')
    <link href="/desktop/css/appointment.css?v=1.0.2" rel="stylesheet" type="text/css">

    <style>
        .bh-pet-avatar-new {
            border: solid 1px #cacaca;
            background-color: #f2f1f1;
            border-radius: 50%;
            display: table;
            padding: 0px;
            width: 95px;
            height: 95px !important;
        }

    </style>

    <div id="main">
        <!-- MY GROOMERS -->
        <section id="my-groomers">
            <div class="container">

                <!-- No Groomers -->
                <div class="row" id="groomers-no-groomers">
                    <div class="col-md-12 text-center">
                        <h3><span class="title-icon">
                                <img src="/desktop/img/groomers-menu-icon.svg" width="38" alt="My Groomers">
                            </span> MY GROOMERS
                        </h3>
                    </div>
                    @if(count($my_groomers) < 1)
                    <div class="col-md-12 text-center">
                        <p><em>Add a favorite Groomer after your first appointment</em></p>
                        <div class="col-lg-12">
                            <button type="button" class="groomit-btn rounded-btn red-btn long-btn"
                                    onclick="window.location.href = '/user/appointment/list';">
                                Schedule Appointment
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- No Favs -->
                @if(count($my_groomers)>0)
                <div class="row after-title row-flex justify-content-center" id="groomers-no-favs">
                    
                    @foreach($my_groomers as $m)
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 card-wrapper--flex col-flex">
                            <div class="media my-card">
                                <div class="media-left">
                                    <div class="media-object">
                                        <div class="table-cell media-middle">
                                            @if (!empty($m->profile_photo))
                                                <img class="media-object img-circle" src="data:image/png;base64,{{ $m->profile_photo }}"
                                                        width="95" height="95" alt="Groomer">
                                            @else
                                                <img class="img-circle" src="/desktop/img/dog-icon.svg" alt="Groomer">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="media-body media-middle">
                                    <div class="display-table">
                                        <div class="table-cell">
                                            <h4 class="media-heading">{{ $m->first_name }} {{ $m->last_name }}
                                            <a href="#" data-toggle="modal" data-id="" data-target="#my-groomer-info-{{ $m->groomer_id }}">
                                                    <img class="infoiconimg" src="/desktop/img/ionicons_2-0-1_ios-information-outline_48_0_bd1a2a_none.png" width="18" height="18" alt="More Info">
                                            </a>
                                            </h4>
                                            <p class="fav-action">
                                                @if ($m->fav_groomer_id == null)
                                                    <span class="my-groomer-info-{{$m->groomer_id}} glyphicon glyphicon-heart-empty make-fav-icon make-fav-icon-gr" onclick="make_favorite({{$m->groomer_id}})"></span>
                                                    <em>Make favorite</em>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
                @endif

                <!-- Fav Groomers -->
                @if(count($fav_groomers)>0)
                <h3 class="text-center after-title">
                    <span class="title-icon">
                        <span class="glyphicon glyphicon-heart make-fav-icon make-fav-icon-gr"></span>
                    </span>
                    MY FAVORITE
                </h3>
                <div class="row after-title row-flex justify-content-center" id="groomers-favs">
                    
                    @foreach($fav_groomers as $f)
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 card-wrapper--flex col-flex">
                            <div class="media my-card">
                                <div class="media-left">
                                    <div class="media-object">
                                        <div class="table-cell media-middle">
                                            @if (!empty($f->profile_photo))
                                                <img class="media-object img-circle" src="data:image/png;base64,{{ $f->profile_photo }}"
                                                        width="95" height="95" alt="Groomer">
                                            @else
                                                <img class="img-circle" src="/desktop/img/dog-icon.svg" alt="Groomer">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="media-body media-middle">
                                    <div class="display-table">
                                        <div class="table-cell">
                                            <p class="media-heading">
                                                <strong>
                                                    {{ $f->first_name }} {{ $f->last_name }}
                                                </strong>
                                                <a href="#" data-toggle="modal" data-id="" data-target="#fav-groomer-info-{{ $f->groomer_id }}">
                                                    <img class="infoiconimg" src="/desktop/img/ionicons_2-0-1_ios-information-outline_48_0_bd1a2a_none.png" width="18" height="18" alt="More Info">
                                                </a>
                                            </p>
                                            <p class="fav-action">
                                                <span class="fav-groomer-info-{{$f->groomer_id}} glyphicon glyphicon-heart make-fav-icon make-fav-icon-gr" onclick="remove_favorite({{ $f->groomer_id }})"></span>
                                                <em>Favorite</em>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
                @endif

            </div>
        </section>

    </div>
    <!-- /main -->

    <!-- MODALS -->

    
    <!-- RESCHEDULE APPOINTMENT CONFIRMATION MODAL -->

    @if(count($fav_groomers)>0)
        @foreach($fav_groomers as $f)
            <div class="modal fade auto-width" id="fav-groomer-info-{{ $f->groomer_id }}" tabindex="-1" role="dialog" aria-labelledby="reschedule-title">
                <div class="modal-dialog auto-width" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close close-reschedule" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="row-pet" >
                                <div class="text-center">
                                    <div class="history-pet">
                                        <div class="bh-pet-avatar-new groomer-ph center text-center center-text">
                                            @if(!empty($f->profile_photo))
                                                <img class="media-object img-circle"
                                                     src="data:image/png;base64,{{ $f->profile_photo }}"
                                                     width="95" height="95">
                                            @else
                                                <img src="/desktop/img/dog-icon.svg" width="53" height="53" alt="Image">
                                            @endif
                                        </div>
                                        <p class="text-center"><strong>{{ $f->first_name }} {{ $f->last_name }}</strong></p>
                                        <p class="text-center">
                                            {{ $f->bio }}
                                        </p>
                                        @if ($f->total_appts >= 50)
                                        <div class="cell-rating">
                                            <div class="starrr" data-rating="{{ \App\Lib\Helper::get_avg_rating($f->groomer_id) }}" style="pointer-events: none;"></div>
                                            <input type="hidden" class="rating" name="rating" value=""/>
                                        </div>
                                        @endif
                                        <p><span class="glyphicon glyphicon-heart make-fav-icon make-fav-icon-gr" onclick="remove_favorite({{$f->groomer_id}})"></span> <em>Favorite</em></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    @if(count($my_groomers)>0)
        @foreach($my_groomers as $f)
            <div class="modal fade auto-width" id="my-groomer-info-{{ $f->groomer_id }}" tabindex="-1" role="dialog" aria-labelledby="reschedule-title">
                <div class="modal-dialog auto-width" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close close-reschedule" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="row-pet">
                                <div class="text-center">
                                    <div class="history-pet">
                                        <div class="bh-pet-avatar-new groomer-ph center text-center center-text">
                                            @if(!empty($f->profile_photo))
                                                <img class="media-object img-circle"
                                                     src="data:image/png;base64,{{ $f->profile_photo }}"
                                                     width="95" height="95">
                                            @else
                                                <img src="/desktop/img/dog-icon.svg" width="53" height="53" alt="Image">
                                            @endif
                                        </div>
                                        <p class="text-center"><strong>{{ $f->first_name }} {{ $f->last_name }}</strong></p>
                                        <p class="text-center">
                                            {{ $f->bio }}
                                        </p>
                                        @if ($f->total_appts >= 50)
                                        <div class="cell-rating">
                                            <div class="starrr" data-rating="{{\App\Lib\Helper::get_avg_rating($f->groomer_id)}}" style="pointer-events: none;"></div>
                                            <input type="hidden" class="rating" name="rating" value=""/>
                                        </div>
                                        @endif
                                        @if ($f->fav_groomer_id == null)
                                        <p>
                                            <span class="my-groomer-info-{{$f->groomer_id}} glyphicon glyphicon-heart-empty make-fav-icon make-fav-icon-gr" onclick="make_favorite({{$f->groomer_id}})"></span>
                                            <em>Make as Favorite</em>
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <script type="text/javascript">

        function make_favorite(groomer_id) {
            var target = $('.my-groomer-info-' + groomer_id);
            var add_to_favorite = target.hasClass('glyphicon-heart') ? 'N' : 'Y';

            if (add_to_favorite === 'N') {
                target.removeClass('glyphicon-heart').addClass('glyphicon-heart-empty');
            }else {
                target.removeClass('glyphicon-heart-empty').addClass('glyphicon-heart');
            }

            $.ajax({
                url: '/user/mygroomer/make-favorite',
                data: {
                    _token: '{!! csrf_token() !!}',
                    groomer_id: groomer_id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {

                        window.location.href = "/user/mygroomer";

                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function remove_favorite(groomer_id) {
            var target = $('.fav-groomer-info-' + groomer_id);
            var add_to_favorite = target.hasClass('glyphicon-heart') ? 'N' : 'Y';

            if (add_to_favorite === 'N') {
                target.removeClass('glyphicon-heart').addClass('glyphicon-heart-empty');
            }else {
                target.removeClass('glyphicon-heart-empty').addClass('glyphicon-heart');
            }

            $.ajax({
                url: '/user/mygroomer/remove-favorite',
                data: {
                    _token: '{!! csrf_token() !!}',
                    groomer_id: groomer_id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {

                        window.location.href = "/user/mygroomer";

                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

    </script>
@stop