<section id="header">
    <div id="top-banner" style="display:none;">
		<div class="container">
			<div class="row">
				<div class="col-md-8 col-md-offset-2 text-center">
					<!--<a href="/user" class="top-banner-cta">GET <strong>$20 OFF</strong> YOUR FIRST <strong>GOLD</strong> GROOMING PACKAGE | Promo Code <strong>PAW20</strong></a>-->
				</div>
				<div class="col-md-2 text-center">
					<ul class="header-sign-links single">
						<li>
							<a href="/user/sign-up">Sign Up</a>
						</li>
					</ul>
				</div>
				<!-- /col-12 -->
			</div>
			<!-- /row -->
		</div>
		<!-- /container-->
	</div>
	<!-- /top-banner -->
	<div class="container cont-menu-affiliate" id="main-menu">
		<div class="row">
			<nav class="navbar navbar-default">
				<div class="container-fluid">
					<!-- Brand and toggle get grouped for better mobile display -->
					<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>



						<a class="navbar-brand" href="/"><img src="/images/logo-landscape-affiliates.png" alt="Groomit" /></a>
					</div>
					<!-- Collect the nav links, forms, and other content for toggling -->
					<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
						<ul class="nav navbar-nav navbar-right">
                            @if (Auth::guard('affiliate')->check())
                                <li class="become-a-gromer-link">
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
                            @else
                                <li>
                                    <a href="/affiliate/login">LOGIN</a>
                                </li>
                            @endif


							
						</ul>
					</div>
					<!-- /.navbar-collapse -->
				</div>
				<!-- /.container-fluid -->
			</nav>
		</div>
	</div>
</section>





