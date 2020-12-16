<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" type="text/css" href="/css/admin.css"/>
    <link rel="stylesheet" type="text/css" href="/css/style.css"/>
{{--<link rel="stylesheet" type="text/css" href="/js/datepicker/css/datepicker.css"/>--}}

<!-- Bootstrap core CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    @if (Route::current()->getName() == 'admin.application')
        <title>Groomer Application - {{ $a->first_name }} {{ $a->last_name }}</title>
    @else
        <title>Groomer</title>
    @endif

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=almAEvpzoq">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=almAEvpzoq">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=almAEvpzoq">
    <link rel="manifest" href="/site.webmanifest?v=almAEvpzoq">
    <link rel="mask-icon" href="/safari-pinned-tab.svg?v=almAEvpzoq" color="#bf372b">
    <link rel="shortcut icon" href="/favicon.ico?v=almAEvpzoq">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
</head>
<body>

<!-- Static navbar -->
<nav class="navbar navbar-inverse navbar-top">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand " href="/groomer">Groomer</a>
    </div>
    <div id="navbar" class="navbar-collapse collapse">
        <ul class="nav navbar-nav">
            {{--<li class="nav-item {{ (Request::is("admin/profit-sharing") ? 'active': '') }}">--}}
            {{--<a class="nav-link" href="/admin/profit-sharing">Profit Sharing Setup</a>--}}
            {{--</li>--}}
            {{--<li class="nav-item {{ (Request::is("admin/profit-sharing/report") ? 'active': '') }}">--}}
            {{--<a class="nav-link" href="/admin/profit-sharing/report">Profit Sharing Report</a>--}}
            {{--</li>--}}
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li class="nav-item">
                <a class="nav-link" href="/groomer/logout">Logout</a>
            </li>
        </ul>
    </div><!--/.nav-collapse -->
</nav>

<!-- /navbar -->

@section('contents');
@show

@include('includes.admin_footer')

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.2.0/js/tether.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/js/moment/min/moment.min.js"></script>
<script type="text/javascript" src="/js/datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script src="/js/loading.js"></script>
<link rel="stylesheet" href="/js/datetimepicker/css/bootstrap-datetimepicker.min.css" />
<script src="/js/aos/aos.js"></script>
<script src="/js/main.js"></script>
<script src="/js/jquery-validation/dist/jquery.validate.js"></script>

{{--@if (Route::current()->getName() == 'admin.push_notifications')--}}
{{--<script type="text/javascript" src="/js/typeahead/typeahead.bundle.js"></script>--}}
{{--<script type="text/javascript" src="/js/typeahead/typeaheadAjax.js"></script>--}}
{{--@endif--}}

@if (Route::current()->getName() == 'admin.application' || Route::current()->getName() == 'admin.groomer')
    <script type="text/javascript">
        $(function() {

                    @if (Route::current()->getName() == 'admin.application')
            var availability = {!! json_encode($a->availability) !!};
                    @else
            var availability = {!! json_encode($gr->availability) !!};
                    @endif

            for (var i = 0; i <= 6; i++) {
                for (var j = 8; j <= 24; j++) {
                    var pad = "00";
                    var hour = pad.substring(0, pad.length - ('' + j).length) + j;
                    var key = 'wd' + i + '_h' + hour;
                    if (availability.indexOf(key) >= 0) {
                        $('#' + key).prop('checked', true);
                    }
                }
            }

            /* make checkbox unclickable */
            //$("input:checkbox").click(function() { return false; });
        });
    </script>
@endif


@if (!Input::get('id')
        && Route::current()->getName() != 'admin.messages'
        && Route::current()->getName() != 'admin.promo_codes'
        && Route::current()->getName() != 'admin.redeemed_groupon'
        && Route::current()->getName() != 'admin.appointment_schedule'
        && Route::current()->getName() != 'admin.fulfillment_schedule'
        && Route::current()->getName() != 'admin.applications'
        && Route::current()->getName() != 'admin.affiliate_withdraw_requests'
        && Route::current()->getName() != 'admin.users')
    <script type="text/javascript" class="init">
        $('#table tbody').children('tr').css('cursor','pointer');

        $('#table tbody').on('click', 'tr', function () {
            window.location.href = '/admin/' + this.id;
        });
    </script>

@endif


<div class="modal" tabindex="-1" role="dialog" id="loading-modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Please Wait...</h4>
            </div>
            <div class="modal-body">
                <div class="progress" style="margin-top:20px;">
                    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                        <span class="sr-only">Please wait.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" tabindex="-1" role="dialog" id="error-modal">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="error-modal-title">Modal title</h4>
            </div>
            <div class="modal-body" id="error-modal-body">
            </div>
            <div class="modal-footer" id="error-modal-footer">
                <button type="button" id="error-modal-ok" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" tabindex="-1" role="dialog" id="confirm-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="confirm-modal-title">Modal title</h4>
            </div>
            <div class="modal-body" id="confirm-modal-body">

            </div>
            <div class="modal-footer" id="confirm-modal-footer">
                <button type="button" id="confirm-modal-cancel" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" id="confirm-modal-ok" class="btn btn-primary" data-dismiss="modal">Ok</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

</body>
</html>
