<!-- HEADER -->
<header class="black-bg">
  <div class="container"> 
    
    <!-- TOP BAR -->
    <div class="row" id="top-bar">
      <div class="col-xs-12">
        <div class="hidden-sm hidden-xs custom-offset pull-left"></div>
        <nav class="navbar navbar-groomit" role="navigation">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#groomit-navbar-collapse"> <span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span> </button>
            <a class="navbar-brand hidden-lg hidden-md hidden-sm" href="#"><img class="img-responsive" src="/desktop/img/logo.png" alt="Groomit"/></a> </div>
          <div class="collapse navbar-collapse" id="groomit-navbar-collapse">
            <ul class="nav navbar-nav">
              <li> <a class="nav-link" data-text="Dashboard" href="index.html">Dashboard 
                <!-- <span class="sr-only">(current)</span> --> 
                </a> </li>
              <li class="active"> <a class="nav-link" data-text="Appointments" href="#">Appointments 
                <!-- <span class="sr-only">(current)</span> --> 
                </a> </li>
              <li> <a class="nav-link"  data-text="Dog Profile" href="#">Dog Profile 
                <!-- <span class="sr-only">(current)</span> --> 
                </a> </li>
              <li> <a class="nav-link"  data-text="Payments" href="#">Payments 
                <!-- <span class="sr-only">(current)</span> --> 
                </a> </li>
              <li> <a class="nav-link"  data-text="Messages" href="#">Messages 
                <!-- <span class="sr-only">(current)</span> --> 
                </a> </li>
              <li> <a class="nav-link"  data-text="Help" href="#">Help 
                <!-- <span class="sr-only">(current)</span> --> 
                </a> </li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
              <li class="dropdown pull-right">
                <a href="#" class="dropdown-toggle hidden" data-toggle="dropdown">
                  @if (Auth::guard('user')->check())
                    {{ Auth::guard('user')->user()->first_name }}
                  @endif
                    <strong class="caret"></strong>
                </a>
                @if (Auth::guard('user')->check())
                  <button type="button" class="groomit-btn red-btn rounded-btn" onclick="window.location.href='/user/logout'">LOGOUT</button>
                @else
                  <button type="button" class="groomit-btn red-btn rounded-btn" data-toggle="modal" data-target="#loginModal" id="notLogged">LOGIN</button>

                @endif
                <ul class="dropdown-menu hidden">
                  <li> <a href="#">My Profile</a> </li>
                  <li> <a href="#">Payments</a> </li>
                  <li> <a href="#">Log Out</a> </li>
                </ul>
              </li>
            </ul>
          </div>
        </nav>
      </div>
    </div>
    <!-- /top-bar --> 
    
    <!-- LOGO -->
    <div class="row" id="logo">
      <div class="col-xs-12">
        <div class="cont-logo hidden-xs"> <img class="img-responsive" src="/desktop/img/logo.png" alt="Groomit"/> </div>
        <!-- cont-logo --> 
        
        <!-- BREADCRUMBS -->
        <div class="" id="breadcrumbs">
          <h2 class="top-title">Book</h2>
        </div>
        <!-- /breadcrumbs --> 
        
        <!-- HEADER LOGIN -->
        <div class="hidden" id="header-login">
          @if (Auth::guard('user')->check())
            <a class="groomit-btn red-btn rounded-btn pull-right" href="/user/logout">LOGOUT</a>
          @else
            <span class="visible-xs already-member">Already a user?</span>
            <a class="groomit-btn red-btn rounded-btn pull-right" data-toggle="modal" data-target="#loginModal">LOGIN</a>
            <span class="pull-right hidden-xs already-member">Already a user?</span>
          @endif

        </div>
        <!-- /header-login --> 
        
        
      </div>
      <!-- /col-12 --> 
    </div>
    <!-- /logo -->

    
    <!-- APPOINTMENT SUMMARY -->
    <div id="appointment-summary" class="">
      <div class="row">
        <div class="col-md-12">
          <h4 class="top-package-title">@if (Session::get('appointment:service')) {{ strtoupper(Session::get('appointment:service')->prod_name) }} @endif</h4>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-9">
          @if (!empty(Session::get('appointment:shampoo')))
            <p>{{ Session::get('appointment:shampoo')->prod_name }}
            @if (!empty($a = Session::get('appointment:addons')))
                -
                @foreach ($a as $o)
                {{ $o->prod_name }} | ${{ $o->prod_price }} &nbsp;
                @endforeach
            @endif
            </p>
          @endif
        </div>
        <div class="col-sm-3 text-right" id="total-summary">
          <p>@if (!empty(Session::get('appointment')->total)) ${{ Session::get('appointment')->total }} @endif </p>
        </div>
      </div>
    </div>
    <!-- /appointment-summary --> 
    
  </div>
  <!-- container --> 
</header>