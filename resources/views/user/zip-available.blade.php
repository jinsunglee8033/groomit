@extends('user.layout.default')

@section('content')

    <link href="/desktop/css/login.css" rel="stylesheet">

    <!-- Page Content -->
    <div id="main">

        <!-- 	PROGRESS BAR -->
        <div class="container-fluid hidden" id="progress-bar">
            <div class="row">
                <div class="col-xs-3 line-status complete"> </div>
                <div class="col-xs-3 line-status"> </div>
                <div class="col-xs-3 line-status"> </div>
                <div class="col-xs-3 line-status"> </div>
            </div>
            <!-- row -->
        </div>
        <!-- /progress-bar -->

        <!-- TOP BANNER -->
        <div class="top-banner-init display-table" id="top-banner">
            <div class="table-cell text-center" id="banner-title" >
                <h1><img class="img-responsive" src="/desktop/img/groomit-title.png" width="325" height="56" alt="Groomit"></h1>
                <h2>Schedule an Appointment</h2>
            </div>
            <!-- /banner-title -->
        </div>
        <!-- /top-banner -->

        <!-- ZIP AVAILABLE -->
        <section class="schedule-appointment" id="zip-available">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <div id="zip-box">
                            <div class="row">
                                <div class="col-lg-10 col-lg-offset-1">
                                    <div class="media">
                                        <div class="media-left"> <img class="media-object" src="/desktop/img/yey.png" width="126" height="184" alt="We have groomers available in your area"> </div>
                                        <div class="media-body media-middle">
                                            <h4 class="media-heading text-center"><strong>Great!</strong> We have service in your area.<br />Are you ready?</h4>
                                            <a class="btn red-btn rounded-btn big-btn groomit-btn btn-block text-center" href="/user/schedule/select-dog">GET STARTED</a> </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /zip-box -->
                    </div>
                    <!-- /col-6 -->
                </div>
                <!-- row -->
            </div>
            <!-- /container -->
        </section>
    </div>
    <!-- /main -->

@stop