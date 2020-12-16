<!-- HEADER -->
<header class="black-bg">
    <div class="container">


    @if(Session::get('user.menu.show') == 'Y')
        <!-- TOP BAR -->
            <div class="row" id="top-bar">
                <div class="col-xs-12">
                    <!--<div class="hidden-sm hidden-xs custom-offset pull-left"></div>-->
                    <nav class="navbar navbar-groomit" role="navigation">
                        <div class="navbar-header">
                            <button type="button" class="navbar-toggle" data-toggle="collapse"
                                    data-target="#groomit-navbar-collapse"><span class="sr-only">Toggle
                                    navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span
                                        class="icon-bar"></span></button>
                            <a class="navbar-brand hidden-lg hidden-md hidden-sm" href="#"><img class="img-responsive"
                                                                                                src="/desktop/img/logo-sm.png"
                                                                                                alt="Groomit"/></a>
                        </div>
                        <div class="collapse navbar-collapse" id="groomit-navbar-collapse">
                            <ul class="nav navbar-nav">
                                <li class="{{ Request::is('user/home') ? 'active' : '' }}"><a class="nav-link"
                                                                                              data-text="Home"
                                                                                              href="/user/home">Home
                                        <!-- <span class="sr-only">(current)</span> -->
                                    </a></li>
                                <li class="{{ Request::is('user/schedule/*') ? 'active' : '' }}"><a class="nav-link"
                                                                                                    data-text="Schedule"
                                                                                                    href="/user/schedule/select-dog">Schedule
                                        <!-- <span class="sr-only">(current)</span> -->
                                    </a></li>
                                <li class="{{ Request::is('user/appointment/*') ? 'active' : '' }}"><a class="nav-link"
                                                                                                    data-text="Appointments"
                                                                                                    href="/user/appointment/list">Appointments
                                        <!-- <span class="sr-only">(current)</span> -->
                                    </a></li>
                                <li class="{{ Request::is('user/pets/dogs') ? 'active' : '' }}" ><a class="nav-link"
                                                                                                    data-text="Dog Profile"
                                                                                                    href="/user/pets/dogs">Dog Profile
                                        <!-- <span class="sr-only">(current)</span> -->
                                    </a></li>
                                <li class="{{ Request::is('user/pets/cats') ? 'active' : '' }}"><a class="nav-link"
                                                                                                    data-text="Cat Profile"
                                                                                                    href="/user/pets/cats">Cat Profile
                                        <!-- <span class="sr-only">(current)</span> -->
                                    </a></li>
                                <li class="{{ Request::is('user/payments') ? 'active' : '' }}"><a class="nav-link"
                                                                                                   data-text="Payments"
                                                                                                   href="/user/payments">Payments
                                        <!-- <span class="sr-only">(current)</span> -->
                                    </a></li>

                                <li class="{{ Request::is('user/mygroomer') ? 'active' : '' }}"><a class="nav-link"
                                                                                                  data-text="MyGroomer"
                                                                                                  href="/user/mygroomer">My Groomers
                                    </a></li>

                                <li class="{{ Request::is('user/help') ? 'active' : '' }}"><a class="nav-link"
                                                                                              data-text="Help" href="/user/help">Help
                                        <!-- <span class="sr-only">(current)</span> -->
                                    </a></li>
                            </ul>
                            <ul class="nav navbar-nav navbar-right">
                                <li class="dropdown pull-right">
                                    @if (Auth::guard('user')->check())

                                        <ul class="nav navbar-nav navbar-right">
                                            <li class="dropdown pull-right"> <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ Auth::guard('user')->user()->first_name }} <strong class="caret"></strong></a>
                                                <ul class="dropdown-menu">
                                                    <li> <a href="/user/myaccount">My Profile</a> </li>
                                                    <li> <a href="/user/logout">Log Out</a> </li>
                                                </ul>
                                            </li>
                                        </ul>
                                    @else
                                    
                                        <button type="button" class="groomit-btn red-btn rounded-btn"
                                                data-toggle="modal"
                                                data-target="#loginModal" id="notLogged" onclick="show_login()">SIGN IN
                                        </button>

                                        <button id="registerLink" type="button"
                                                class="groomit-btn outline-btn white-btn rounded-btn notRegistered"
                                                onclick="location.href='/user/sign-up'">SIGN UP
                                        </button>
                                    @endif

                                </li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
            <!-- /top-bar -->
    @endif

    <!-- LOGO -->
    @if (!Request::is('user/gift-cards'))
        <div class="row" id="logo">
            <div class="col-xs-12">
                <div class="cont-logo hidden-xs">
                    <a href="/"><img class="img-responsive" src="/desktop/img/logo-sm.png" alt="Groomit"/></a>
                </div>
                <!-- cont-logo -->


                <!-- BREADCRUMBS -->
                @if(Session::get('user.menu.show') == 'Y')
                    <div class="" id="breadcrumbs">
                        <h3 class="top-title">{{ Session::get('user.menu.top-title') }}</h3>

						<!-- APPOINTMENT SUMMARY -->
				@if (Request::is('user/schedule/*'))
                    @if (\App\Lib\ScheduleProcessor::getRebook() != 'Y')
                        <div id="appointment-summary" class="display-table">
                            <div class="table-cell" style="width: 200px;">
                                <span class="top-package-title">
                                    @php
                                    $current_pet = Schedule::getCurrentPet();
                                    @endphp
                                    <strong>{{ empty($current_pet) ? 'Current' : $current_pet->name }}</strong>
                                    <span id="current_package_name">{{ Schedule::getCurrentPackageName() }}</span>
                                </span>
                            </div>

                            <div class="table-cell text-left" id="total-summary">
                                <span><strong>TOTAL: </strong></span>
                                <span id="remove-appointment" style="float: right;">
                                    <span id="current_sub_total">${{ number_format(Schedule::getCurrentSubTotal(), 2)
                                    }}</span><a href="/user/schedule/clear{{ empty($current_pet) ? '' : '/' . $current_pet->pet_id
                                    }}" title="Remove Appointment"><i class="far fa-trash-alt"></i></a></span>
                            </div>
                        </div>
                    @endif

                    @php
                    $pets = Schedule::getPets();
                    @endphp
                    @if (!empty($pets))
                    @foreach($pets as $pet)
                        @if (!Schedule::isCurrentPet($pet->pet_id))
                        <div id="appointment-summary" class="display-table">
                            <div class="table-cell" style="width: 200px;">
                                <span class="top-package-title" id="current_package_name">
                                    <strong>{{ $pet->name }}</strong>
                                    {{ $pet->info->package->prod_name }}
                                </span>
                            </div>

                            <div class="table-cell text-left" id="total-summary">
                                 <span><strong>TOTAL: </strong></span>
                                 <span id="remove-appointment" style="float: right;">${{ number_format
                                 ($pet->info->sub_total, 2) }}<a href="/user/schedule/clear/{{ $pet->pet_id }}"
                                                                                title="Remove
                                Appointment"><i class="far fa-trash-alt"></i></a></span>
                            </div>
                        </div>
                         @endif
                    @endforeach
                    @endif
				@endif


                    </div>
                @endif
            <!-- /breadcrumbs -->

                @if (session('user.menu.show') != 'Y')
                    @if (Auth::guard('user')->check())
                    <!-- HEADER LOGIN -->
                        <div id="header-login">
                            <a class="groomit-btn red-btn rounded-btn pull-right" href="/user/logout">LOGOUT</a>
                        </div>
                        <!-- /header-login -->
                    @else
                        <div class="" id="header-login">
                            <span class="visible-xs already-member">Already a user?</span>
                            <button type="button" class="groomit-btn red-btn rounded-btn pull-right"
                                    id="hl-login-btn" onclick="show_login()">
                                SIGN IN
                            </button>
                            <a href="/user/sign-up" role="button" class="groomit-btn outline-btn white-btn rounded-btn pull-right">SIGN UP</a>
                        </div>
                        <!-- /header-login -->
                    @endif
                @endif

            </div>
            <!-- /col-12 -->
        </div>
        <!-- /logo -->
        @endif

    </div>
    <!-- container -->
</header>
<script type="application/javascript">

    @if (in_array(Schedule::getCurrentPackageId(), [28, 29]))
        //ECO
        localStorage.setItem('calendarFromPeriod',7);
        localStorage.setItem('calendarFromUnit','days');
    @else
        //GOLD & SILVER
        localStorage.setItem('calendarFromPeriod',null);
        localStorage.setItem('calendarFromUnit',null);
    @endif
    var link = document.getElementById('registerLink');


</script>


<script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
<script>
    $( document ).ready(function() {
      if(window.location.hash == '#sign-up') {
          var hash = window.location.hash.substring(1);
          setTimeout(function() {
            show_register();
          }, 100);
      } else if(window.location.hash == '#sign-in') {
          var hash = window.location.hash.substring(1);
          setTimeout(function() {
            show_login();
          }, 100);
      } else {

      }
    });
</script>
