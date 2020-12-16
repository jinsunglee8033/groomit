<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Groomit</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="/desktop/css/animate.min.css" rel="stylesheet">
    <link href="/desktop/js/owlCarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="/desktop/js/owlCarousel/assets/owl.theme.default.min.css" rel="stylesheet">
    <link href="/desktop/js/bootstrap-material-datetimepicker/bootstrap-material-datetimepicker.css" rel="stylesheet">
    <link href="/desktop/css/bootstrap.min.css" rel="stylesheet">
    <link href="/desktop/js/starrr/starrr.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="/desktop/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link href="/desktop/css/header.css" rel="stylesheet">
    <link href="/desktop/css/groomit.css" rel="stylesheet">
    @if (Route::current()->getName() == 'user.home')
    <link href="/desktop/css/dashboard.css" rel="stylesheet">
    @else
    <link href="/desktop/css/appointment.css" rel="stylesheet">
    @endif
    @if (Route::current()->getName() == 'user.appointment.add-payment'
    || Route::current()->getName() == 'user.appointment.add-pet'
    || Route::current()->getName() == 'user.appointment.select-address'
    || Route::current()->getName() == 'user.appointment.select-pet'
    || Route::current()->getName() == 'user.appointment.select-payment')
    <link href="/desktop/css/material-forms.css" rel="stylesheet">
    @endif
	
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-96110082-1"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'UA-96110082-1');
	</script>

</head>
@if (Route::current()->getName() == 'user.appointment.add-ons')
    <body class="add-ons">
@else
    <body>
@endif

@include('includes.user-header')

@section('contents')
@show

@include('includes.user-footer')


<!-- MODALS -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>

            <!-- LOGIN -->
            <div class="modal-body" id="login">
                <h4 class="modal-title text-center" id="myModalLabel">Login</h4>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
                        <form method="post" id="loginForm" action="/user/login" name="loginForm">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="login_channel" value="i">
                            <fieldset>
                                <div class="form-group">
                                    <label class="control-label" for="user-email">EMAIL*</label>
                                    <input type="email" name="email" id="email" class="form-control" required>
                                </div>
                                <!-- /form-group -->
                                <div class="form-group">
                                    <label class="control-label" for="last-name">PASSWORD*</label>
                                    <input type="password" name="passwd" id="password" class="form-control" required>
                                    <span class="help-block text-right" id="password-link"><a class="switch-content" data-content="forgot-password" title="Forgot your password?">Forgot your password?</a></span> </div>
                                <!-- /form-group -->
                                <div class="form-group">
                                    <button class="btn red-btn rounded-btn groomit-btn btn-block" type="submit">LOGIN</button>
                                    <p class="text-center" id="or">OR</p>
                                    <button class="btn blue-btn rounded-btn groomit-btn btn-block" type="button">LOGIN WITH FACEBOOK</button>
                                </div>
                                <!-- /form-group -->

                            </fieldset>
                        </form>
                    </div>
                </div>
                <!-- /row -->
            </div>
            <!-- /login -->

            <!-- FORGOT PASSWORD -->
            <div class="modal-body hidden" id="forgot-password">
                <h4 class="modal-title text-center" id="myModalLabel">FORGOT YOUR PASSWORD?</h4>
                <p class="text-center">Please enter your email and we will get you back on track.</p>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
                        <form id="recorvePassForm" action="">
                            <fieldset>
                                <div class="form-group">
                                    <label class="control-label" for="user-email">EMAIL*</label>
                                    <input type="email" name="user-email" id="user-email" class="form-control" required>
                                </div>
                                <!-- /form-group -->
                                <br>
                                <div class="form-group">
                                    <button class="btn red-btn rounded-btn groomit-btn btn-block" type="submit">SUBMIT</button>
                                </div>
                                <!-- /form-group -->

                            </fieldset>
                        </form>
                    </div>
                </div>
                <!-- /row -->
            </div>
            <!-- /forgot-password -->

            <!-- ENTER TEMPORARY KEY -->
            <div class="modal-body hidden" id="temporary-key">
                <h4 class="modal-title text-center" id="myModalLabel">ENTER TEMPORARY KEY</h4>
                <p class="text-center">Please enter the temporary key we have sent to your email address.</p>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
                        <form id="temporaryKeyForm" action="">
                            <fieldset>
                                <div class="form-group">
                                    <label class="control-label" for="user-key">TEMPORARY KEY</label>
                                    <input type="text" name="user-key" id="user-key" class="form-control">
                                </div>
                                <!-- /form-group -->
                                <br>
                                <div class="form-group text-center">
                                    <button class="btn black-btn rounded-btn groomit-btn switch-content" data-content="forgot-password" type="button">GO BACK</button>
                                    <button class="btn red-btn rounded-btn groomit-btn" type="submit">SUBMIT</button>
                                </div>
                                <!-- /form-group -->

                            </fieldset>
                        </form>
                    </div>
                </div>
                <!-- /row -->
            </div>
            <!-- /temporary-key -->

            <!-- UPDATE PASSWORD -->
            <div class="modal-body hidden" id="update-password">
                <h4 class="modal-title text-center" id="myModalLabel">UPDATE PASSWORD</h4>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
                        <form id="updatePassForm" action="">
                            <fieldset>
                                <div class="form-group">
                                    <label class="control-label" for="new-password">ENTER NEW PASSWORD</label>
                                    <input type="text" name="new-password" id="new-password" class="form-control">
                                </div>
                                <!-- /form-group -->
                                <div class="form-group">
                                    <label class="control-label" for="confirm-password">CONFIRM PASSWORD</label>
                                    <input type="text" name="confirm-password" id="confirm-password" class="form-control">
                                </div>
                                <!-- /form-group -->
                                <br>
                                <div class="form-group text-center">
                                    <button class="btn black-btn rounded-btn groomit-btn switch-content" data-content="temporary-key" type="button">GO BACK</button>
                                    <button class="btn red-btn rounded-btn groomit-btn" type="submit">SUBMIT</button>
                                </div>
                                <!-- /form-group -->

                            </fieldset>
                        </form>
                    </div>
                </div>
                <!-- /row -->
            </div>
            <!-- /update-password -->

        </div>
        <!-- /modal-content -->

    </div>
</div>

<!-- /MODALS -->

<!-- Error modals -->
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

<!-- USER PROFILE -->
<div class="modal fade" id="editMyProfile" tabindex="-1" role="dialog" aria-labelledby="editMyProfileLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      
      <!-- INIT FORM -->
      <form action="" class="material-form" role="form" method="post" id="userProfileForm">
        <fieldset>
          <div class="modal-body" id="view-profile">
            <h4 class="modal-title text-center" id="editMyProfileLabel">MY PROFILE</h4>
            
            <!-- PROFILE -->
            <section id="profile">
              <div class="container" id="st-opt">
                <div class="row">
                  <div class="col-md-4">
                    <div class="my-photo"> 
                      <!-- DEFAULT PROFILE PHOTO --> 
                      <!-- required "demo-photo" class --> 
                      <img class="demo-photo" src="img/my-profile.png" width="221" height="221"> 
                      <!--<img class="demo-photo" src="img/cat-profile.png" width="237" height="194">--> 
                      <!-- CUSTOM PROFILE PHOTO --> 
                      <!-- remove "demo-photo" class --> 
                      <!--<img src="">-->
                      <div id="add-photo"><span class="glyphicon glyphicon-camera center-block" aria-hidden="true"></span>Add photo</div>
                    </div>
                    <div class="text-center">
                      <button type="button" class="groomit-btn rounded-btn outline-btn black-btn switch-content" data-content="change-password">RESET PASSWORD</button>
                    </div>
                  </div>
                  <!-- /col-3 -->
                  <div class="col-md-8" id="pet-binfo">
                    <div class="row">
                      <div class="col-xs-6">
                        <div class="form-group">
                          <input class="form-control" name="" type="text" maxlenght="100" required />
                          <span class="form-highlight"></span> <span class="form-bar"></span>
                          <label class="control-label" for="">FIRST NAME <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
                        </div>
                        <!-- /form-group --> 
                      </div>
                      <!-- /col-6 -->
                      <div class="col-xs-6">
                        <div class="form-group">
                          <input class="form-control" name="" type="text" maxlenght="100" required />
                          <span class="form-highlight"></span> <span class="form-bar"></span>
                          <label class="control-label" for="">LAST NAME <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
                        </div>
                        <!-- /form-group --> 
                      </div>
                      <!-- /col-6 --> 
                    </div>
                    <!-- /row -->
                    
                    <div class="row">
                      <div class="col-xs-6">
                        <div class="form-group">
                          <input class="form-control" name="" type="text" maxlenght="100" required />
                          <span class="form-highlight"></span> <span class="form-bar"></span>
                          <label class="control-label" for="">PHONE NUMBER <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
                        </div>
                        <!-- /form-group --> 
                      </div>
                      <!-- /col-6 -->
                      <div class="col-xs-6">
                        <div class="form-group">
                          <input class="form-control" name="" type="email" maxlenght="100" required />
                          <span class="form-highlight"></span> <span class="form-bar"></span>
                          <label class="control-label" for="">EMAIL <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
                        </div>
                        <!-- /form-group --> 
                      </div>
                      <!-- /col-6 --> 
                    </div>
                    <!-- /row --> 
                    
                  </div>
                  <!-- /col-10 --> 
                </div>
                <!-- /row --> 
                
              </div>
              <!-- /container --> 
            </section>
            <!-- /other-addons -->
            
            <section id="next-btn">
              <div class="container">
                <div class="row">
                  <div class="col-md-12 text-center">
                    <button data-dismiss="modal" aria-label="Close" type="button" class="groomit-btn rounded-btn black-btn long-btn space-btn">CLOSE</button>
                    <!--<button type="button" class="groomit-btn rounded-btn black-btn long-btn space-btn">CANCEL</button>-->
                    <!--<button type="submit" class="groomit-btn rounded-btn red-btn long-btn">CONFIRM</button>--> 
                    <button type="button" class="groomit-btn rounded-btn red-btn long-btn">EDIT</button>
                  </div>
                </div>
                <!-- row --> 
              </div>
              <!-- container --> 
            </section>
          </div>
          <!-- /modal-body --> 
          
          <!-- CHANGE PASSWORD -->
          <div class="modal-body hidden" id="change-password"> 
            <h4 class="modal-title text-center" id="editMyProfileLabel">MY PROFILE</h4>
            
            <!-- PROFILE -->
            <section id="profile">
              <div class="container" id="st-opt">
                <div class="row">
                   
                  <div class="col-md-10 col-md-offset-1" id="pet-binfo">
                    <div class="row">
                      <div class="col-sm-6">
                        <div class="form-group">
                          <input class="form-control" name="" type="text" maxlenght="100" required />
                          <span class="form-highlight"></span> <span class="form-bar"></span>
                          <label class="control-label" for="">CURRENT PASSWORD <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
                        </div>
                        <!-- /form-group --> 
                      </div>
                      <!-- /col-6 -->
                    </div>
                    <!-- /row -->
                    
                    <div class="row">
                     <div class="col-xs-6">
                        <div class="form-group">
                          <input class="form-control" name="" type="text" maxlenght="100" required />
                          <span class="form-highlight"></span> <span class="form-bar"></span>
                          <label class="control-label" for="">NEW PASSWORD <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
                        </div>
                        <!-- /form-group --> 
                      </div>
                      <!-- /col-6 --> 
                      <div class="col-xs-6">
                        <div class="form-group">
                          <input class="form-control" name="" type="text" maxlenght="100" required />
                          <span class="form-highlight"></span> <span class="form-bar"></span>
                          <label class="control-label" for="">CONFIRM PASSWORD <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
                        </div>
                        <!-- /form-group --> 
                      </div>
                      <!-- /col-6 -->
                    </div>
                    <!-- /row --> 
                    
                  </div>
                  <!-- /col-10 --> 
                </div>
                <!-- /row --> 
                
              </div>
              <!-- /container --> 
            </section>
            <!-- /other-addons -->
            
            <section id="next-btn">
              <div class="container">
                <div class="row">
                  <div class="col-md-12 text-center">
                    <button type="button" class="groomit-btn rounded-btn black-btn long-btn space-btn switch-content" data-content="view-profile">GO BACK</button>
                    <button type="button" class="groomit-btn rounded-btn red-btn long-btn">CONFIRM</button>
                  </div>
                </div>
                <!-- row --> 
              </div>
              <!-- container --> 
            </section>

          
          
          </div>
          <!-- /modal-body -->
          
        </fieldset>
      </form>
      <!-- /FORM --> 
      
    </div>
    <!-- /modal-content --> 
    
  </div>
</div>
</body>
</html>
