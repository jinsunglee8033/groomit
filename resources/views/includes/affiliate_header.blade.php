<!-- Starts header with banner background -->
@if (Auth::guard('affiliate')->check())
    <div><!-- Starts cont-banner -->
        <div class="container-fluid" id="cont-all-nav"><!-- Starts Container-fluid -->
            <div class="container"><!-- Starts Container -->
                <div class="row"><!-- Starts row -->
                    <div class="col-md-12"><!-- Starts col-12 -->
                        <nav class="navbar navbar-default" role="navigation">
                            <div class="navbar-header"><!-- Starts navbar-header -->
                                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#nav-bar-affiliates">
                                    <span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
                                </button> <a class="navbar-brand" href="/">
                                    @if (!empty($data->affiliate_photo))
                                        <img class="img-responsive" src="data:image/png;base64,{{ $data->affiliate_photo }}" style="height: 80px;">
                                    @else
                                        <img class="img-responsive" src="/images/logo-landscape.png" alt="Groomit" />
                                    @endif
                                </a>
                            </div><!-- Ends navbar-header -->

                            <div class="collapse navbar-collapse" id="nav-bar-affiliates"><!-- Starts nav-bar-affiliates -->
                                <ul class="nav navbar-nav pull-right"><!-- Starts navbar-nav -->
                                        <li class="red-menu-link">
                                            <a href="/affiliate/logout">LOGOUT</a>
                                        </li>
                                    @if(Route::current()->getName() == 'affiliate.my-account')
                                        <li class="become-a-gromer-link">
                                            <a href="/affiliate/earnings">EARNINGS</a>
                                        </li>
                                    @else
                                        <li class="become-a-gromer-link">
                                            <a href="/affiliate/my-account">MY ACCOUNT</a>
                                        </li>
                                    @endif

                                </ul><!-- Ends navbar-nav -->
                            </div><!-- Ends nav-bar-affiliates -->

                        </nav>
                    </div><!-- Ends col-12 -->
                </div><!-- Ends row -->
            </div><!-- Ends Container -->
        </div><!-- Ends Container-fluid -->
    </div><!-- Ends cont-banner -->
@else
    <div class="cont-banner" id="cont-banner-home"><!-- Starts cont-banner -->
        <div class="cont-banner-title text-center">
            <img class="img-responsive" src="/img/logo-banner.png" alt="Groomit Affiliates - Earn great commissions on referrals" />

            @php
                switch (Route::current()->getName()) {
                    case 'affiliate.login':
                        echo "<h2>LOGIN</h2>";
                        break;
                    case 'affiliate.apply':
                        echo "<h2>APPLY NOW</h2>";
                        break;
                    case 'affiliate.forgot-password.verify-email':
                    case 'affiliate.forgot-password.verify-key':
                    case 'affiliate.forgot-password.update-password':
                        echo "<h2>FORGOT PASSWORD</h2>";
                        break;
                    default:
                        echo "<h2>Earn great commissions on referrals</h2>";
                        break;
                }
            @endphp
        </div>
        <div class="container-fluid" id="cont-all-nav"><!-- Starts Container-fluid -->
            <div class="container"><!-- Starts Container -->
                <div class="row"><!-- Starts row -->
                    <div class="col-md-12"><!-- Starts col-12 -->
                        <nav class="navbar navbar-default" role="navigation">
                            <div class="navbar-header"><!-- Starts navbar-header -->
                                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#nav-bar-affiliates">
                                    <span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
                                </button> <a class="navbar-brand" href="/"><img class="img-responsive" src="/img/logo.png" alt="Groomit" /></a>
                            </div><!-- Ends navbar-header -->
                            <div class="collapse navbar-collapse" id="nav-bar-affiliates"><!-- Starts nav-bar-affiliates -->
                                <ul class="nav navbar-nav pull-right"><!-- Starts navbar-nav -->
                                    <li class="active">
                                        <a class="btn-affiliate" href="/affiliate/login">LOGIN</a>
                                    </li>
                                    <!--<li class="become-a-gromer-link">
                                        <a href="/affiliate/apply">become affiliate</a>
                                    </li>-->
                                </ul><!-- Ends navbar-nav -->
                            </div><!-- Ends nav-bar-affiliates -->
                        </nav>
                    </div><!-- Ends col-12 -->
                </div><!-- Ends row -->
            </div><!-- Ends Container -->
        </div><!-- Ends Container-fluid -->
    </div><!-- Ends cont-banner -->
@endif
<!-- Ends header with banner background -->
