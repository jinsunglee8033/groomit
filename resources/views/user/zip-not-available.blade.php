@extends('user.layout.default')

@section('content')
    <link href="/desktop/css/login.css" rel="stylesheet">

    <!-- Page Content -->
    <div class="main--no-margin" id="main">
        <!-- TOP BANNER -->
        <div class="top-banner-init display-table" id="top-banner">
            <div class="table-cell text-center" id="banner-title">
                <h1><img class="img-responsive" src="/desktop/img/groomit-title.png" width="325" height="56"
                         alt="Groomit"></h1>
                <h2>Schedule an Appointment</h2>
            </div>
            <!-- /banner-title -->
        </div>
        <!-- /top-banner -->

        <!-- ZIP NOT AVAILABLE -->
        <section class="schedule-appointment" id="zip-not-available">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <div id="zip-box">
                            <div class="row">
                                <div class="col-lg-10 col-lg-offset-1">
                                    <div class="media">
                                        <div class="media-left"><img class="media-object"
                                                                     src="/desktop/img/not-available.png" width="130"
                                                                     height="102" alt="Groomers not available"></div>
                                        <div class="media-body media-top">
                                            <p class="media-heading">Oh no! Looks like we aren’t in your area yet.<br>
                                            Please give us a little more info and we’ll let you know the moment we’re available.</p>
                                            @if (Session::has('success'))
                                                <div class="alert alert-success detail"> {{ Session::get('success') }} </div>
                                            @endif

                                            @if (count($errors) > 0)
                                                <div class="alert alert-danger">
                                                    <ul>
                                                        @foreach ($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                            <form id="subscribe" class="{{ Session::has('success') ? 'hidden' : '' }}" method="post"
                                                  action="/user/subscribe">
                                                {!! csrf_field() !!}
                                                <input type="hidden" name="zip"
                                                       value="{{ old('zip', Session::get('zip')) }}"/>
                                                <input type="hidden" name="inserted_id" id="inserted_id"
                                                       value="{{ old('inserted_id', Session::get('inserted_id')) }}"/>
                                                <fieldset>

                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label class="control-label" for="first_name">FIRST
                                                                    NAME *</label>
                                                                <input type="text" name="first_name" id="first_name"
                                                                       class="form-control"
                                                                       value="{{ old('first_name') }}" required>
                                                            </div>
                                                            <!-- /input-group -->
                                                        </div>
                                                        <!-- /col -->
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label class="control-label" for="last_name">LAST
                                                                    NAME *</label>
                                                                <input type="text" name="last_name" id="last_name"
                                                                       class="form-control"
                                                                       value="{{ old('last_name') }}" required>
                                                            </div>
                                                        </div>
                                                        <!-- /col -->
                                                    </div>
                                                    <!-- /row -->
                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label class="control-label" for="subscriber_email">EMAIL
                                                                    *</label>
                                                                <input type="email" name="subscriber_email"
                                                                       id="subscriber_email" class="form-control"
                                                                       value="{{ old('subscriber_email') }}"
                                                                       required>
                                                            </div>
                                                            <!-- /input-group -->
                                                        </div>
                                                        <!-- /col -->
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label class="control-label" for="phone_number">PHONE
                                                                    NUMBER</label>
                                                                <input type="text" name="phone_number" id="phone_number"
                                                                       class="form-control"
                                                                       value="{{ old('phone_number') }}">
                                                            </div>
                                                            <!-- /input-group -->
                                                            <button class="btn red-btn pull-right rounded-btn groomit-btn"
                                                                    type="submit">SEND
                                                            </button>
                                                        </div>
                                                        <!-- /col -->
                                                    </div>
                                                    <!-- /row -->
                                                </fieldset>
                                            </form>
                                        </div>
                                        <!-- /media-body -->
                                    </div>
                                    <!-- /media -->
                                </div>
                                <!-- /col -->
                            </div>
                            <!-- /row -->
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
