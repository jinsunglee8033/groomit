@extends('user.layout.default')
@section('content')
<!-- PROGRESS -->
<div class="container-fluid" id="progress-bar">
    <div class="row">
        <div class="col-xs-2 line-status complete"></div>
        <div class="col-xs-2 line-status complete"></div>
        <div class="col-xs-2 line-status complete"></div>
        <div class="col-xs-2 line-status complete"></div>
        <div class="col-xs-2 line-status complete"></div>
        <div class="col-xs-2 line-status complete"></div>
    </div>
    <!-- row -->
</div>
<!-- /progress-bar -->
<div id="fetching-groomers-body">
    <section>
        <h4 class="text-center modal-title">FETCHING GROOMERS</h4>
    </section>
    <section>
        <div class="container">
            <div class="row">
                <div class="col-xs-12 text-center">
                    <p><img src="/desktop/img/yey.png" width="126" height="184" alt="Fetching Groomers"></p>
                    <br>
                    <p>We're locating available groomers.<br>Shortly we confirm your start time and groomer by email/app.</p>
                    <br>
                </div>
                <!-- /col -->
            </div>
            <!-- /row -->
            <div class="row">
                <div class="col-xs-12 text-center">
                    <div class="form-group">
                        <a href="/user/appointment/list" class="btn red-btn rounded-btn groomit-btn long-btn"
                                type="button">GOT IT
                        </a>
                    </div>
                    <!-- /form-group -->
                </div>
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </section>
</div>

@stop
