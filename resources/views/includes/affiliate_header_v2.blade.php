@if (Auth::guard('affiliate')->check())
    <header class="main-nav main-nav--affiliates">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-4 col-sm-3">
                    <a href="/">
                        <img class="main-nav__logo" src="../../images/logo-landscape.png" alt="Groomit Affiliates - Earn great commissions on referrals" />
                    </a>
                </div>
                <!--<div class="col-8 col-sm-9">
                    <div class="main-nav__menu main-nav--loggedin">
                        <nav class="navbar navbar-expand-sm navbar-light">
                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-nav__menu-affiliates" aria-controls="main-nav__menu-affiliates" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            <div class="collapse navbar-collapse" id="main-nav__menu-affiliates">
                                <ul class="navbar-nav ml-auto">
                                        <li class="nav-item active main-nav__item">
                                            <a class="nav-link main-nav__link" href="/affiliate/logout">LOGOUT</a>
                                        </li>
                                    @if(Route::current()->getName() == 'affiliate.my-account')
                                        <li class="nav-item main-nav__item">
                                            <a class="nav-link main-nav__link" href="/affiliate/earnings">EARNINGS</a>
                                        </li>
                                    @else
                                        <li class="nav-item main-nav__item">
                                            <a class="nav-link main-nav__link" href="/affiliate/my-account">MY ACCOUNT</a>
                                        </li>
                                    @endif

                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>-->
            </div>
        </div>
    </header>
    <div class="top-banner top-banner--position-top top-banner--affiliates"></div>
@else
    <header class="main-nav main-nav--affiliates">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-5 col-sm-4">
                    <a href="/">
                        <img class="img-fluid main-nav__logo" src="../../images/logo-landscape.png" alt="Groomit Affiliates - Earn great commissions on referrals" />
                    </a>
                </div>
                <!--<div class="col-7 col-sm-8 text-right">
                    <div class="main-nav__menu main-nav--guest">
                        <a class="btn btn-primary--groomit px-sm-4" href="/affiliate/login">LOGIN</a>
                    </div>
                </div>-->
            </div>
        </div>
    </header>
    <div class="top-banner top-banner--position-top top-banner--affiliates"></div>
@endif