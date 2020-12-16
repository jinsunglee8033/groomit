<!DOCTYPE html>
<html lang="en">
    <head>

        <!-- Segment Analytics Service -->
        <script>
        !function(){var analytics=window.analytics=window.analytics||[];if(!analytics.initialize)if(analytics.invoked)window.console&&console.error&&console.error("Segment snippet included twice.");else{analytics.invoked=!0;analytics.methods=["trackSubmit","trackClick","trackLink","trackForm","pageview","identify","reset","group","track","ready","alias","debug","page","once","off","on"];analytics.factory=function(t){return function(){var e=Array.prototype.slice.call(arguments);e.unshift(t);analytics.push(e);return analytics}};for(var t=0;t<analytics.methods.length;t++){var e=analytics.methods[t];analytics[e]=analytics.factory(e)}analytics.load=function(t,e){var n=document.createElement("script");n.type="text/javascript";n.async=!0;n.src="https://cdn.segment.com/analytics.js/v1/"+t+"/analytics.min.js";var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(n,a);analytics._loadOptions=e};analytics.SNIPPET_VERSION="4.1.0";
        analytics.load("vVEIBQcDH1zdJDWvAqBlMcEWcGO9rz7z");
        analytics.page();
        }}();
        </script>


        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-TKX9ZHP');</script>
        <!-- End Google Tag Manager -->


        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ Helper::get_meta_title() }}</title>
		<link rel="canonical" href="{{ Helper::get_meta_canonical() }}">
    	<meta name="description" content="{{ Helper::get_meta_description() }}">

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<script defer src="/font-awesome/js/fontawesome-all.js"></script>
        <link href="/desktop/css/animate.min.css" rel="stylesheet">
        <link href="/desktop/js/owlCarousel/assets/owl.carousel.min.css" rel="stylesheet">
        <link href="/desktop/js/owlCarousel/assets/owl.theme.default.min.css" rel="stylesheet">
        <link href="/desktop/js/bootstrap-material-datetimepicker/bootstrap-material-datetimepicker.css" rel="stylesheet">
        <link href="/desktop/css/bootstrap.min.css" rel="stylesheet">
        <link href="/desktop/js/starrr/starrr.css" rel="stylesheet">
        <link href="/js/aos/aos.css" rel="stylesheet">
        <link href="/desktop/js/mobiscroll/css/mobiscroll.jquery.min.css" rel="stylesheet">
        <link href="/desktop/js/range-slider/css/bootstrap-slider.min.css" rel="stylesheet">
        <link href="/desktop/js/croppie/croppie.css" rel="stylesheet">

        <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
        <link href="/desktop/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <link href="/desktop/css/spacing-utilities.css" rel="stylesheet">
        <link href="/desktop/css/header.css?v=0.0.1" rel="stylesheet">
        <link href="/desktop/css/groomit.css?v=0.0.5" rel="stylesheet">

        <!-- Mixpanel Analytics Services -->
        <!-- start Mixpanel -->
        <!--<script type="text/javascript">(function(c,a){if(!a.__SV){var b=window;try{var d,m,j,k=b.location,f=k.hash;d=function(a,b){return(m=a.match(RegExp(b+"=([^&]*)")))?m[1]:null};f&&d(f,"state")&&(j=JSON.parse(decodeURIComponent(d(f,"state"))),"mpeditor"===j.action&&(b.sessionStorage.setItem("_mpcehash",f),history.replaceState(j.desiredHash||"",c.title,k.pathname+k.search)))}catch(n){}var l,h;window.mixpanel=a;a._i=[];a.init=function(b,d,g){function c(b,i){var a=i.split(".");2==a.length&&(b=b[a[0]],i=a[1]);b[i]=function(){b.push([i].concat(Array.prototype.slice.call(arguments,
        0)))}}var e=a;"undefined"!==typeof g?e=a[g]=[]:g="mixpanel";e.people=e.people||[];e.toString=function(b){var a="mixpanel";"mixpanel"!==g&&(a+="."+g);b||(a+=" (stub)");return a};e.people.toString=function(){return e.toString(1)+".people (stub)"};l="disable time_event track track_pageview track_links track_forms track_with_groups add_group set_group remove_group register register_once alias unregister identify name_tag set_config reset opt_in_tracking opt_out_tracking has_opted_in_tracking has_opted_out_tracking clear_opt_in_out_tracking people.set people.set_once people.unset people.increment people.append people.union people.track_charge people.clear_charges people.delete_user people.remove".split(" ");
        for(h=0;h<l.length;h++)c(e,l[h]);var f="set set_once union unset remove delete".split(" ");e.get_group=function(){function a(c){b[c]=function(){call2_args=arguments;call2=[c].concat(Array.prototype.slice.call(call2_args,0));e.push([d,call2])}}for(var b={},d=["get_group"].concat(Array.prototype.slice.call(arguments,0)),c=0;c<f.length;c++)a(f[c]);return b};a._i.push([b,d,g])};a.__SV=1.2;b=c.createElement("script");b.type="text/javascript";b.async=!0;b.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?
        MIXPANEL_CUSTOM_LIB_URL:"file:"===c.location.protocol&&"//cdn4.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn4.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn4.mxpnl.com/libs/mixpanel-2-latest.min.js";d=c.getElementsByTagName("script")[0];d.parentNode.insertBefore(b,d)}})(document,window.mixpanel||[]);
        mixpanel.init("d97a25112750a0187d6378c98aee38a6");</script>-->
        <!-- end Mixpanel -->

    </head>
    <body>

		<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TKX9ZHP"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

    @include('user.layout.header')

    <!-- Page Content -->
        @yield('content')


        @if (count($errors->all()) > 0)
            <div class="container global-error" style="margin-top: -30px;">
                <div class="row">
                    <div class="col-md-10 col-md-offset-1">
                        <div class="alert alert-danger alert-dismissable">
                            <ul>
                                @foreach ($errors->all() as $o)
                                    <li>{{ $o }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
    @endif

    <!-- /main -->

        <!-- MODALS -->
        <div class="modal fade" id="login-modal-parent" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                    </div>

                    <!-- LOGIN -->
                    <div class="modal-body login-active" id="modal-login">
                        <h4 class="modal-title text-center" id="myModalLabel">Sign In</h4>
                        <div class="row">
                            <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
                                <div>
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
                                            <input type="password" name="password" id="password" class="form-control"
                                                   required>
                                            <span class="help-block text-right" id="password-link"><a
                                                        onclick="show_forgot_password()" class="switch-content"
                                                        data-content="forgot-password" title="Forgot your password?">Forgot
                                                    your password?</a></span></div>
                                        <!-- /form-group -->
                                        <div class="form-group">
                                            <button class="btn red-btn rounded-btn groomit-btn btn-block type-submit" type="button"
                                                    onclick="login()">SIGN IN
                                            </button>
                                            <p class="text-center" id="or">OR</p>
                                            <button class="btn blue-btn rounded-btn groomit-btn btn-block"
                                                    type="button" onclick="facebook_login()">SIGN IN WITH FACEBOOK
                                            </button>
                                            <div class="display-table" id="register-block">
                                            <span class="table-cell align-middle">NOT A GROOMIT USER YET?</span>
                                            <a href="/user/sign-up" class="btn outline-btn rounded-btn groomit-btn table-cell align-middle" role="button" id="login_pg_register_button">
                                                SIGN UP
                                            </a>
                                            </div>
                                        </div>
                                        <!-- /form-group -->

                                    </fieldset>
                                </div>
                            </div>
                        </div>
                        <!-- /row -->
                    </div>
                    <!-- /login -->

                    <!-- FORGOT PASSWORD -->
                    <div class="modal-body hidden" id="modal-forgot-password">
                        <h4 class="modal-title text-center" id="myModalLabel">FORGOT YOUR PASSWORD?</h4>
                        <p class="text-center">Please enter your email and we will get you back on track.</p>
                        <div class="row">
                            <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
                                <div>
                                    <fieldset>
                                        <div class="form-group">
                                            <label class="control-label" for="user-email">EMAIL*</label>
                                            <input type="email" name="forgot_password_email" id="forgot_password_email"
                                                   class="form-control"
                                                   required>
                                        </div>
                                        <!-- /form-group -->
                                        <br>
                                        <div class="form-group">
                                            <button class="btn red-btn rounded-btn groomit-btn btn-block" type="button"
                                                    onclick="verify_forgot_password_email()">
                                                SUBMIT
                                            </button>
                                            <button class="btn black-btn rounded-btn groomit-btn btn-block"
                                                    type="button" onclick="show_login()">BACK TO LOGIN
                                            </button>

                                        </div>
                                        <!-- /form-group -->

                                    </fieldset>
                                </div>
                            </div>
                        </div>
                        <!-- /row -->
                    </div>
                    <!-- /forgot-password -->

                    <!-- ENTER TEMPORARY KEY -->
                    <div class="modal-body hidden" id="modal-temporary-key">
                        <h4 class="modal-title text-center" id="myModalLabel">ENTER TEMPORARY KEY</h4>
                        <p class="text-center">Please enter the temporary key we have sent to your email address.</p>
                        <div class="row">
                            <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
                                <div>
                                    <fieldset>
                                        <div class="form-group">
                                            <label class="control-label" for="user-key">TEMPORARY KEY</label>
                                            <input type="text" name="forgot_password_key" id="forgot_password_key"
                                                   class="form-control">
                                        </div>
                                        <!-- /form-group -->
                                        <br>
                                        <div class="form-group text-center">
                                            <button class="btn black-btn rounded-btn groomit-btn switch-content"
                                                    data-content="forgot-password" type="button"
                                                    onclick="show_forgot_password()">GO BACK
                                            </button>
                                            <button class="btn red-btn rounded-btn groomit-btn" type="button"
                                                    onclick="verify_forgot_password_key()">SUBMIT
                                            </button>
                                        </div>
                                        <!-- /form-group -->

                                    </fieldset>
                                </div>
                            </div>
                        </div>
                        <!-- /row -->
                    </div>
                    <!-- /temporary-key -->

                    <!-- UPDATE PASSWORD -->
                    <div class="modal-body hidden" id="modal-update-password">
                        <h4 class="modal-title text-center" id="myModalLabel">UPDATE PASSWORD</h4>
                        <div class="row">
                            <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
                                <div>
                                    <fieldset>
                                        <div class="form-group">
                                            <label class="control-label" for="forgot_password_new">ENTER NEW
                                                PASSWORD</label>
                                            <input type="password" name="forgot_password_new" id="forgot_password_new"
                                                   class="form-control">
                                        </div>
                                        <!-- /form-group -->
                                        <div class="form-group">
                                            <label class="control-label" for="forgot_password_confirm">CONFIRM
                                                PASSWORD</label>
                                            <input type="password" name="forgot_password_confirm"
                                                   id="forgot_password_confirm"
                                                   class="form-control">
                                        </div>
                                        <!-- /form-group -->
                                        <br>
                                        <div class="form-group text-center">
                                            <button class="btn black-btn rounded-btn groomit-btn switch-content"
                                                    data-content="temporary-key" type="button"
                                                    onclick="show_temporary_key()">GO BACK
                                            </button>
                                            <button class="btn red-btn rounded-btn groomit-btn" type="button"
                                                    onclick="update_password()">SUBMIT
                                            </button>
                                        </div>
                                        <!-- /form-group -->

                                    </fieldset>
                                </div>
                            </div>
                        </div>
                        <!-- /row -->
                    </div>
                    <!-- /update-password -->
                    <!-- REGISTER FORM -->
                <div class="modal-body hidden" id="register-form">
                    <h4 class="modal-title text-center" id="registerModalLabel">SIGN UP FOR GROOMIT</h4>
                    <div class="row">
                        <div class="col-md-10 col-md-offset-1">
                            <form id="signupForm" action="" name="signupForm">
                                <fieldset>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="">FIRST NAME</label>
                                                <input type="text" name="s_first_name" id="s_first_name" class="form-control" required>
                                            </div>
                                        </div>
                                        <!-- /col-6 -->
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="">LAST NAME</label>
                                                <input type="text" name="s_last_name" id="s_last_name" class="form-control" required>
                                            </div>
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <!-- /row -->
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="">MOBILE PHONE NUMBER</label>
                                                <input type="text" name="s_phone" id="s_phone" class="form-control" required>
                                            </div>
                                        </div>
                                        <!-- col-lg-5 -->

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="">EMAIL</label>
                                                <input type="email" name="s_email" id="s_email" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /row -->
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="">HOW DID YOU HEAR ABOUT US?</label>
                                                <select class="form-control" name="s_hear_from" id="s_hear_from" required>
                                                    <option value="">Please Select</option>
                                                    <option value="google">Google</option>
                                                    <option value="facebook">Facebook</option>
                                                    <option value="youtube">YouTube</option>
                                                    <option value="instagram">Instagram</option>
                                                    <option value="twitter">Twitter</option>
                                                    <option value="yelp">Yelp</option>
                                                    <option value="friends">Friends</option>
                                                    <option value="veterinarian">Veterinarian</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- col-lg-5 -->
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label for="" style="color:#aeaeae;">REFERRAL CODE</label>
                                                <input type="text" name="s_referral_code" id="s_referral_code" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label for="">ZIP</label>
                                                <input type="text" name="s_zip" id="s_zip" class="form-control" value="{{ empty($zip) ? '' : $zip }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /row -->
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="">PASSWORD</label>
                                                <input type="password" name="s_password" id="s_password" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="">REPEAT PASSWORD</label>
                                                <input type="password" name="s_password_confirm" id="s_password_confirm" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /row -->
                                    <div class="row" id="agreements">
                                        <div class="col-xs-12">
                                            <div class="form-group">
                                                <div class="checkbox">
                                                    <label for="s_terms">
                                                        <input type="checkbox" name="s_terms" id="s_terms" value="">
                                                        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>I accept</label><a class="red-link switch-content" data-content="terms" data-animationout="fadeOut" data-animationin="fadeIn" onClick="switch_content(this)"><strong> terms & conditions.</strong></a>
                                                </div>
                                                <!-- /checkbox -->
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                    </div>
                                    <!-- /agreements -->
                                    <div class="row">
                                        <div class="col-xs-12 text-center">
                                          <button class="btn black-btn rounded-btn groomit-btn" onClick="switch_content(this)" data-content="modal-login" type="button">GO BACK</button>
                                            <button type="button" class="groomit-btn red-btn rounded-btn long-btn type-submit" onclick="register()">SIGN UP</button>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                    <!-- /row -->
                </div>
                <!-- /register -->

                <!-- TERMS -->
                <div class="modal-body hidden" id="terms">
                    <h4 class="modal-title text-center" id="termsLabel">Terms and Conditions</h4>
                    <div class="row">
                        <div class="col-sm-10 col-sm-offset-1">
                            <ol>
                                <li><strong>Binding Agreement.</strong> These terms of use (&ldquo;TOU&rdquo;) constitute a legally binding agreement between you, an individual (hereinafter &ldquo;You&rdquo;) and Groomit LLC (&ldquo;Groomit,&rdquo; &ldquo;we&rdquo; or &ldquo;us&rdquo;), the company that owns and operates the website and/or application for use on a mobile telephone for the services outlined herein. <strong>By using the Groomit Website and/or  (the &ldquo;Website&rdquo;) and its related services (collectively, the &ldquo;Groomit Service&rdquo;), whether You are using it as a pet owner (a &ldquo;Pet Owner&rdquo;), a groomer looking to provide services  (a &ldquo;Groomer&rdquo;) , or just a browser (a &ldquo;Browser&rdquo;)(all of which are collectively referred to herein as  &ldquo;User&rdquo;), You agree to be bound by these Terms of Use (&rdquo;TOU&rdquo;).</strong> <strong>We may modify these &quot;TOU&quot; from time to time, without notice to You, and any such changes and/or modifications shall be binding upon You effective as of the date of such change and/or , so You are advised to check this page periodically. </strong>You acknowledge that information You provide is accurate and complete and that We may terminate this Agreement in the event it is revealed the information You provided is inaccurate or incomplete. </li>
                                <li><strong>Eligibility.</strong> You must be eighteen (18) or over to register as a User or use the Web site or mobile application. Registration is void where prohibited. By registering and/or using the Web site, You represent and warrant that you are over the age of Eighteen (18), have the right, authority, and capacity to enter into and abide by these TOU and agree to be bound by the TOU.  You are not eligible if you have been previously terminated as a User.</li>
                                <li><strong>Username.</strong> You will be, as part of the registration process, asked to select a unique user name and password to establish Your account. You are solely responsible for maintaining the security of the account. You are responsible for keeping your password confidential and for notifying use if your password has been compromised. You will notify us in the event that you become aware of unauthorized use of your account or any breach of our security.  You are responsible for all of services requested under Your account.  Groomit is not responsible or liable for any loss you may incur as a result of someone else using your password or account, with or without your knowledge.  We may hold You responsible for any losses incurred as a result of your failure to maintain the security of Your account. </li>
                                <li><strong>Payment. </strong>You agree that your use may result in charges to you for the Groomit Service arranged through the Web site or mobile application.  We will facilitate payment for the Groomit Service by serving as an agent for You, but You understand and agree that We are not proving the service. Charges against You are final and non-refundable unless otherwise agreed between the Pet Owner and/or Groomer.   In the event you have a dispute with the Groomer we will seek to facilitate a resolution of any such dispute, but We shall not be responsible to You for a reimbursement of any money paid. You will be responsible for any charge associated with a cancellation due to the groomer&rsquo;s inability to provide the service due to reasons caused by you or your pet. </li>
                                <li><strong>Term and Termination.</strong> These TOU will remain in full force and effect as long as You are a User. They apply to anything You do through/on the Groomit Web site, this includes, but is not limited to, posting photos of you and your pet, requesting services, providing services, providing products, buying products, sharing your story, and any blogging or posting that You do through our Website respectively. We have the right to investigate any allegations that You have violated these TOU, and to take whatever action we think is necessary or appropriate to remedy such violations, including, without limitation, terminating your User status, pursuing legal action against You and reporting your conduct to the appropriate legal authorities.  Subject to your compliance with these TOU, you have a non-exclusive, revocable and non-transferable right to access and use this website and/or mobile phone application consistent with these TOU.  Your use of the content, information and related materials that are made available to you by Groomit pursuant to these TOU is subject to your compliance with these TOU.  Your right to use the service is expressly subject to Your compliance with these TOU, which is revocable and non-transferrable, and may be terminated by us for any reason outlined in these TOU or any other reason that we determine in our sole reasonable discretion. </li>
                                <li><strong>Services Provided.</strong> The services provided by Groomit is the platform which enables Pet Owners and Groomers to interact and to enable Pet Owners to arrange for Groomers to come to the Pet Owner&rsquo;s home or other agreed upon location for the provision of certain grooming services to the Pet Owner&rsquo;s pet. (hereinafter &ldquo;Services&rdquo;) Unless agreed upon in writing, the Services are provided to the Pet Owner for noncommercial use and that the Pet Owner is not permitted to sell the services of the Groomer to any other parties.  IT IS AGREED AND UNDERSTOOD GROOMIT DOES NOT PROVDE GROOMING SERVCIES BUT ACTS AS A PLATFORM FOR PET OWNERS AND GROOMERS TO INTERACT.</li>
                                <li><strong>Your Responsibilities</strong>.  You may have the right to use our platform only in strict compliance with the following terms and conditions and your failure to comply may result in your termination as a User.
                                    <ol>
                                        <li>You are not permitted to use the platform in any manner that could result in damage to our system, disable or overburden our network or interfere/interrupt any other user&rsquo;s ability to use the platform.  </li>
                                        <li>You may not attempt to gain access to our platform using any username other than the one obtained by You.  You may not, and shall not assist others in accessing our platform for any purpose other than for the strict purposes intended.</li>
                                        <li>You may not seek to use our platform to promote any business, venture or other activity without first obtaining our prior written permission, which may be withheld for any reason and no reason. </li>
                                    </ol>
                                </li>
                                <li><strong>If You Are a Pet Owner. </strong>If you are a Pet Owner, You agree that:
                                    <ol>
                                        <li>
                                            You and your pet will be at the designated location at the designated time ready for the Groomer to provide his/her services and your pet will be calm and ready for grooming.</li>
                                        <li>
                                            You will have prepared the designated location for the groomer, by ensuring that there are the following items ready for the use of the groomer:
                                            <ol>
                                                <li>Clean Towel(s);</li>
                                                <li>Sink with hot and cold water; </li>
                                                <li>Space for the groomer to place a table, upon which he/she will groom the pet, free of debris and/or clutter;</li>
                                                <li>An electrical outlet and electricity, with sufficient capacity for a hair dryer;</li>
                                                <li>A waste receptacle for the groomer to dispose of the pet clippings when the work is completed; and</li>
                                                <li>Any other item reasonable and necessary for the groomer to perform the services requested by the Pet Owner.</li>
                                            </ol>
                                        </li>
                                        <li>You will not solicit the groomer to provide you with services directly but only arrange for services through our platform.</li>
                                        <li>You will be responsible to pay for all of the services you have arranged unless you cancel such service more than twenty-four (24) hours prior to the time the service is scheduled to be provided. If you cancel on less than twenty-four (24) hours notice you will be charged the full fee for the service.</li>
                                        <li>While we will always do what is safest for your pet, the removal of mats is a time consuming and sometimes dangerous process. If your pet is severely matted, we may be unable to save their coat without causing harm or discomfort. Therefore, customers who use our dematting service must give their consent to Groomit For Pets LLC to remove (shave down or spot shave) their pet's coat if necessary.<br>By agreeing, you release Groomit for Pets LLC from any and all liability for any skin conditions that might be revealed by the removal of the matted coat. You must understand that such pre-existing conditions are not the responsibility of Groomit for Pets LLC. Revealed medical conditions may require veterinary care and you will assume financial responsibility of any such expense.</li>
                                        <li>You agree to cancel the services in the event that your pet is not well and/or is not in a condition where your pet can be safely groomed.  If you fail to cancel and your pet is sick and/or unable to be safely groomed, you will be charged a fee of 50% of the grooming charge.  The determination that your pet is unable to be groomed shall be made by the groomer in his/her sole discretion.</li>
                                    </ol>
                                </li>
                                <li><strong>If You Are a Groomer:</strong> If You are a Groomer, you agree that:
                                    <ol>
                                        <li>You will be at the designated location at the designated time ready to provide the grooming services. </li>
                                        <li>You bring with you all equipment reasonable and necessary to perform the services to the Pet Owner, which such items include, but are not limited to:
                                            <ol>
                                                <li>Hair Clipper; </li>
                                                <li>Scissors; </li>
                                                <li>Comb and brush; </li>
                                                <li>Shampoo</li>
                                                <li>Nail clipper; </li>
                                                <li>Sprayer attachment for sink; </li>
                                                <li>Hair Dryer and extension cord; </li>
                                                <li>Hair bows and/or bandana; and </li>
                                                <li>Any other item reasonable and necessary for the groomer to perform the services requested by the Pet Owner. </li>
                                            </ol>
                                        </li>
                                        <li>You will not solicit the Pet Owner to services directly but only arrange for services through our platform.</li>
                                    </ol>
                                </li>
                                <li><strong>Ownership of Content.</strong> We own all proprietary rights in the Service and the Website, including, without limitation, all trademark rights in Groomit name and logo, all patent rights in the Service, and all copyright and other rights in the content that we display or post on the Website <strong>(&ldquo;Content&rdquo;)</strong>. You may not copy, publish, transmit, distribute copies of, perform, display, or modify any Content except as is necessary to use the Service.</li>
                                <li><strong>Limitations on Use of the IDNID Service. </strong>We operate the Service solely as a platform that permits groomers and pet owners to interact. <strong>We do not provide any grooming services.</strong> We will investigate any illegal and/or unauthorized uses of the Service and we will take all legal action that we consider necessary or appropriate to ensure that such activity is stopped.  </li>
                                <li><strong>Interaction and Disputes with Other Users; Release.</strong> Whether You are a Groomer or a Pet Owner, other than as specifically provided in these TOU, You are solely responsible for your communication and interaction with other Users. If there is a dispute between any Groomer and any Pet Owner, between any other Users, or between Groomer or Pet Owner and any third party,  We are under <strong>no obligation</strong> to become involved, and You hereby <strong>release</strong> Groomit, its officers, employees, agents and successors in rights from claims, demands and damages (actual and consequential) of every kind or nature, including death, known or unknown, suspected and unsuspected, disclosed and undisclosed, arising out of or in any way related to such disputes and/or the Service. In the event that we are forced to be involved in any such dispute, You agree that You shall be responsible for any and all legal fees and costs we incur as a result of our involvement. If You are a California resident, You waive California Civil Code Section 1542, which says: &quot;A general release does not extend to claims which the creditor does not know or suspect to exist in his favor at the time of executing the release, which, if known by him must have materially affected his settlement with the debtor.&quot;</li>
                                <li><strong>Text Messaging</strong>. You agree that we may send you information by text message (SMS) and You shall be responsible for any and all fees, expenses and carrier charges that may be imposed upon you by your cellular telephone service provider.  </li>
                         
                                <li><strong>Indemnity. </strong>You will indemnify Groomit and its subsidiaries, affiliates, officers, agents, and other partners and employees <strong>(the &ldquo;Groomit Parties&rdquo;)</strong> and hold them harmless from any loss, liability, claim, demand, fees and expenses, including reasonable attorney's fees, made by any third party due to or arising out of your use of the IDNID Service, including, without limitation, any claim arising from your breach of any of these TOU and/or any breach of your representations and warranties set forth above.     </li>
                                <li><strong>DISCLAIMER OF WARRANTIES.</strong>
                                    <ol>
                                        <li><strong>GROOMIT EXPRESSLY DISCLAIMS ANY AND ALL WARRANTIES, EXPRESS OR IMPLIED, RELATING TO THE OPERATION OR YOUR USE OF THE GROOMIT  WEBSITE OR APP, INCLUDING, WITHOUT LIMITATION:</strong>                                         </li>
                                        <li>THE SERVICE IS PROVIDED ON AN &quot;AS IS&quot; OR &quot;AS AVAILABLE&quot; BASIS, WITHOUT ANY WARRANTIES OF ANY KIND. ALL EXPRESS AND IMPLIED WARRANTIES ARE EXPRESSLY DISCLAIMED TO THE FULLEST EXTENT PERMITTED BY LAW. GROOMIT EXPRESSLY DISCLAIMS ANY WARRANTY THAT THE GROOMIT SERVICE WILL ALWAYS BE AVAILABLE OR THAT IT WILL OPERATE ERROR-FREE, AND EXPRESSLY DISCLAIMS ANY LIABILITY FOR (i)) ANY INTERRUPTIONS TO THE AVAILABILITY OF THE SERVICE, WHETHER SUCH INTERRUPTIONS ARE DUE TO INTERNET EVENTS BEYOND GROOMIT&rsquo;S CONTROL, TO PROBLEMS WITH THE GROOMIT SERVERS, OR TO PROBLEMS WITH YOUR COMPUTER OR YOUR INTERNET CONNECTION, AND (ii) ANY VIRUSES OR OTHER HARMFUL COMPONENTS THAT MAY HARM YOUR COMPUTER AS A RESULT OF YOUR INTERACTION WITH THE GROOMIT SERVICE. Some jurisdictions do not allow the disclaimer of implied warranties, some of the foregoing disclaimers may not apply to You insofar as they relate to implied warranties.     </li>
                                        <li>GROOMIT IS NOT RESPONSIBLE IF THE SERVICE IS UNABLE TO BE PROVIDED DUE TO THE ACTIONS OF YOU OR YOUR PET.        </li>
                                    </ol>
                                </li>
                                <li><strong>LIMITATIONS OF LIABILITY</strong> UNDER NO CIRCUMSTANCES SHALL GROOMIT  BE LIABLE FOR DIRECT, INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL OR EXEMPLARY DAMAGES (EVEN IF IDNID HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES), RESULTING FROM ANY ASPECT OF YOUR USE OF THE GROOMIT SERVICE, WHETHER THE DAMAGES ARISE FROM USE OR MISUSE OF THE GROOMIT SERVICE OR THE WEBSITE; ARISE FROM THE SERVICES PROVIDED BY A GROOMER OR ACTIONS BY A PET OWNER,  INABILITY TO USE THE GROOMIT SERVICE OR THE WEBSITE; THE INTERRUPTION, SUSPENSION, MODIFICATION, ALTERATION, OR TERMINATION OF THE GROOMIT SERVICE OR THE WEBSITE; ACTIONS TAKEN OR STATEMENTS MADE, BY A GROOMER OR PET OWNER OR BY ANY THIRD PARTY WHOM YOU MAY MEET OR WITH WHOM YOU MAY COMMUNICATE AS A RESULT OF USING THE GROOMIT SERVICE; OR BY REASON OF ANY INFORMATION OR ADVICE RECEIVED THROUGH OR ADVERTISED IN CONNECTION WITH THE GROOMIT SERVICE OR THE WEBSITE OR ANY LINKS ON THE WEBSITE. THESE LIMITATIONS SHALL APPLY TO THE FULLEST EXTENT PERMITTED BY LAW. In some jurisdictions, limitations of liability are not permitted, so some of the foregoing limitations may not apply to You. </li>
                                <li><strong>Privacy.</strong> For our privacy policy, <a href="https://www.groomit.me/terms-privacy" target="_blank">https://www.groomit.me/terms-privacy</a></li>
                                <li><strong>Governing Law, Jurisdiction, Time Limit on Claims.</strong> If you have any dispute with us, the dispute will be governed by the laws of the State of New York without regard to its conflict of law provisions. You hereby submit to personal jurisdiction by and venue in the state and federal courts of the State of New York, City of Yonkers. Regardless of any statute or law to the contrary, any claim or cause of action arising out of or related to use of the Groomit Service or the TOU must be filed by You within one (1) year after such claim or cause of action arose or be forever barred.</li>
                                <li><strong>Arbitration</strong>. You agree that any dispute or controversy arising out of or relating to these Terms or the breach, termination, enforcement, interpretation or validity thereof or the use of the Services (collectively &ldquo;Disputes&rdquo;)will be settled by binding arbitration between You and Groomit, except that each party retains the right to bring an action in small claims court and the righto seek injunction to prevent actual or threatened infringement, misappropriation or violation of a party&rsquo;s intellectual property rights.  You acknowledge and agree you are waiving your right to trial by jury.  Any arbitration shall be in Yonkers, NY using the American Arbitration Association commercial arbitration rules using a single arbitrator to be selected by Groomit.  The parties shall equally share the expenses associated with such Arbitration. </li>
                                <li><strong>Additional Terms.</strong> Our failure to exercise or enforce any right or provision of the TOU shall not constitute a waiver of such right or provision. If a court of competent jurisdiction holds any provision of the TOU invalid, the court should try to give effect to the parties' intentions as reflected in the provision, and the other provisions of the TOU will remain in full force and effect.   </li>
                            </ol> 
                            <br>
                            <p class="text-center" id="back-form">

                                <button class="btn black-btn rounded-btn groomit-btn" onClick="switch_content(this)" data-content="register-form" data-animationout="fadeOut" data-animationin="fadeIn" type="button">GO BACK</button>
                            </p>
                        </div>
                    </div>
                    <!-- /row -->
                </div>
                <!-- /terms -->


                </div>
                <!-- /modal-content -->

            </div>
        </div>


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
            <div class="modal-dialog modal-sm" role="document">
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
					<style type="text/css">
						.material-form .form-group {
							margin-bottom: 0;
							padding-bottom: 20px;
							margin-top: 35px;
							position: relative;
						}
						.material-form .form-control[disabled], .material-form .form-control[readonly], .material-form fieldset[disabled] .form-control {
							background-color: transparent;
							border-bottom-style: dashed;
						}
						.material-form .form-control {
							display: block;
							height: 62px;
							width: 100%;
							border: none;
							border-radius: 0 !important;
							font-size: 14px;
							font-weight: 300;
							padding: 0;
							background-color: transparent;
							box-shadow: none;
							border-bottom: 1px solid #cacaca;
						}
						.form-highlight {
							position: absolute;
							height: 25%;
							width: 60px;
							top: 25%;
							left: 0;
							pointer-events: none;
							opacity: 0.4;
						}
						.form-bar {
							position: relative;
							display: block;
							width: 100%;
						}
						.form-bar:before {
							left: 50%;
						}
						.form-bar:after {
							right: 50%;
						}
						.form-bar:before, .form-bar:after {
							content: '';
							height: 1px;
							width: 0;
							bottom: 0;
							position: absolute;
							transition: 0.3s ease all;
							-moz-transition: 0.3s ease all;
							-webkit-transition: 0.3s ease all;
						}
						.material-form label:not(.btn-st-opt) {
							position: absolute;
							top: -18px;
							color: #999;
							font-size: 12px;
							font-weight: 300;
							transition: 0.2s ease all;
							-moz-transition: 0.2s ease all;
							-webkit-transition: 0.2s ease all;
						}
						.material-form label:not(.btn-st-opt) {
							top: -31px;
						}

					</style>
                    <div class="modal-body" id="view-profile">
                        <h4 class="modal-title text-center" id="editMyProfileLabel">MY PROFILE</h4>

                        <!-- PROFILE -->
                        <section class="material-form" id="profile">
                            <div class="container" id="st-opt">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="my-photo">
                                            <!-- DEFAULT PROFILE PHOTO -->
                                            <!-- required "demo-photo" class -->
                                            <img id="photo" class="demo-photo" src="/desktop/img/my-profile.png" alt="Profile Avatar" width="221" height="221">
                                            <!--<img class="demo-photo" src="img/cat-profile.png" width="237" height="194">-->
                                            <!-- CUSTOM PROFILE PHOTO -->
                                            <!-- remove "demo-photo" class -->
                                            <!--<img src="">-->
                                            <input type="file" id="user_photo" name="pet_photo" style="display:none;" onchange="read_file_for_profile(this)"/>
                                            <div id="add-photo" onclick="$('#editMyProfile').find('#user_photo').click();"><span class="glyphicon glyphicon-camera center-block" aria-hidden="true"></span>Add photo</div>
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
                                                    <input class="form-control" id="first_name" type="text" maxlength="100" required />
                                                    <span class="form-highlight"></span> <span class="form-bar"></span>
                                                    <label class="control-label" for="">FIRST NAME <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
                                                </div>
                                                <!-- /form-group -->
                                            </div>
                                            <!-- /col-6 -->
                                            <div class="col-xs-6">
                                                <div class="form-group">
                                                    <input class="form-control" id="last_name" type="text" maxlength="100" required />
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
                                                    <input class="form-control" id="phone" type="text" maxlength="10" required />
                                                    <span class="form-highlight"></span> <span class="form-bar"></span>
                                                    <label class="control-label" for="">PHONE NUMBER <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
                                                </div>
                                                <!-- /form-group -->
                                            </div>
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
                                        <button type="button" class="groomit-btn rounded-btn red-btn long-btn" onclick="update_user_profile()">SUBMIT</button>
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
                        <section class="material-form" id="profile">
                            <div class="container" id="st-opt">
                                <div class="row">

                                    <div class="col-md-10 col-md-offset-1" id="pet-binfo">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <input class="form-control" id="current_password" type="password" maxlength="100" required />
                                                    <span class="form-highlight"></span> <span class="form-bar"></span>
                                                    <label class="control-label" for="current_password">CURRENT PASSWORD <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
                                                </div>
                                                <!-- /form-group -->
                                            </div>
                                            <!-- /col-6 -->
                                        </div>
                                        <!-- /row -->

                                        <div class="row">
                                            <div class="col-xs-6">
                                                <div class="form-group">
                                                    <input class="form-control" id="new_password" type="password" maxlength="100" required />
                                                    <span class="form-highlight"></span> <span class="form-bar"></span>
                                                    <label class="control-label" for="new_password">NEW PASSWORD <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
                                                </div>
                                                <!-- /form-group -->
                                            </div>
                                            <!-- /col-6 -->
                                            <div class="col-xs-6">
                                                <div class="form-group">
                                                    <input class="form-control" id="new_password_confirmation" type="password" maxlength="100" required />
                                                    <span class="form-highlight"></span> <span class="form-bar"></span>
                                                    <label class="control-label" for="new_password_confirmation">CONFIRM PASSWORD <span class="set-required"><span class="hidden-sm hidden-xs">REQUIRED</span><span class="visible-sm visible-xs"> *</span></span></label>
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
                                        <button type="button" class="groomit-btn rounded-btn red-btn long-btn" onclick="reset_user_password()">CONFIRM</button>
                                    </div>
                                </div>
                                <!-- row -->
                            </div>
                            <!-- container -->
                        </section>



                    </div>
                    <!-- /modal-body -->

                </div>
                <!-- /modal-content -->

            </div>
        </div>

        <script src="/desktop/js/jquery.min.js"></script>
        <script src="/desktop/js/bootstrap.min.js"></script>
        <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
        <script src="/desktop/js/ie10-viewport-bug-workaround.js"></script>
        <script src="/desktop/js/range-slider/bootstrap-slider.min.js"></script>
        <script src="/desktop/js/owlCarousel/owl.carousel.min.js"></script>
        <script src="/desktop/js/moment.min.js"></script>
        <script src="/desktop/js/bootstrap-material-datetimepicker/bootstrap-material-datetimepicker.js"></script>
        <script src="/desktop/js/jquery.inputmask.bundle.js"></script>
        <script src="/desktop/js/starrr/starrr.js"></script>
        <script src="/desktop/js/mobiscroll/mobiscroll.jquery.min.js"></script>
        <script src="/js/aos/aos.js"></script>
        <script src="/desktop/js/croppie/croppie.min.js"></script>
        <script src="/desktop/js/croppie/exif.js"></script>
        <script src="/desktop/js/scripts.js?ver=042720207"></script>
        <script src="/js/loading.js?t=201808081140"></script>
        <script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>
        <script>
            window.fbAsyncInit = function() {
                FB.init({
                    appId      : '1266973380177705',
                    cookie     : true,
                    xfbml      : true,
                    version    : 'v3.0'
                });

                FB.AppEvents.logPageView();

            };

            (function(d, s, id){
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) {return;}
                js = d.createElement(s); js.id = id;
                js.src = "https://connect.facebook.net/en_US/sdk.js";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));

            // window.fbAsyncInit = function () {
            //     FB.init({
            //         appId: '1248083228570516',
            //         cookie: true,
            //         xfbml: true,
            //         version: 'v3.0'
            //     });
            //
            //     FB.AppEvents.logPageView();
            //
            // };
            //
            // (function (d, s, id) {
            //     var js, fjs = d.getElementsByTagName(s)[0];
            //     if (d.getElementById(id)) {
            //         return;
            //     }
            //     js = d.createElement(s);
            //     js.id = id;
            //     js.src = "https://connect.facebook.net/en_US/sdk.js";
            //     fjs.parentNode.insertBefore(js, fjs);
            // }(document, 'script', 'facebook-jssdk'));
        </script>

        <script type="text/javascript">

            $(document).ready(function () {

                /*var path = '{{ URL::current() }}';
                var key = path + ".scroll";

                if (Cookies.get(key) !== null) {
                    $('body,html').animate({scrollTop: Cookies.get(key)}, 'slow');
                }

                $(document).on("scroll", function () {
                    Cookies.set(key, $(document).scrollTop());
                });*/


				$('#login-modal-parent').on('hidden.bs.modal', function (e) {
					$('#login-modal-parent .modal-body').addClass('hidden');
					$('#login-modal-parent .modal-body#modal-login').removeClass('hidden');
				})

                $(document ).ajaxError(function( event, jqxhr, settings, thrownError ) {
                    myApp.hideLoading();
                    myApp.showError(jqxhr.status + ' ' + jqxhr.statusText);
                });
            });



            function facebook_login() {

                FB.login(function (response) {

                    if (response.authResponse) {
                        //console.log('Welcome!  Fetching your information.... ');
                        FB.api('/me?fields=email,name,id,picture', function (response) {

                            var photo_url = '';
                            if (response.picture && response.picture.data) {
                                photo_url = response.picture.data.url;
                            }

                            $.ajax({
                                url: '/user/login/facebook',
                                data: {
                                    _token: '{!! csrf_token() !!}',
                                    email: response.email,
                                    name: response.name,
                                    user_id: response.id,
                                    photo_url: photo_url
                                },
                                cache: false,
                                type: 'post',
                                dataType: 'json',
                                success: function (res) {
                                    if ($.trim(res.msg) === '') {
                                        var redirect_url = '{{ session()->has('schedule.url') ? "/" . session('schedule.url') : '/user/home' }}';
                                        window.location.href = redirect_url;
                                    } else {
                                        myApp.showError(res.msg);
                                    }
                                }
                            });

                        });
                    } else {
                        console.log('User cancelled login or did not fully authorize.');
                    }
                }, {scope: 'email'});
            }

            function login() {

                $.ajax({
                    url: '/user/login',
                    data: {
                        _token: '{!! csrf_token() !!}',
                        email: $('#email').val(),
                        password: $('#password').val()
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function (res) {
                        if ($.trim(res.msg) === '') {
                            var redirect_url = '{{ session()->has('schedule.url') ? "/" . session('schedule.url') : '/user/home' }}';
                            window.location.href = redirect_url;
                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                });
            }

            function verify_forgot_password_email() {
                $.ajax({
                    url: '/user/forgot-password/verify-email',
                    data: {
                        _token: '{!! csrf_token() !!}',
                        email: $('#forgot_password_email').val()
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function (res) {
                        if ($.trim(res.msg) === '') {
                            show_temporary_key();
                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                })
            }

            function verify_forgot_password_key() {
                $.ajax({
                    url: '/user/forgot-password/verify-key',
                    data: {
                        _token: '{!! csrf_token() !!}',
                        email: $('#forgot_password_email').val(),
                        key: $('#forgot_password_key').val()
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function (res) {
                        if ($.trim(res.msg) === '') {
                            show_update_password();
                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                })
            }

            function update_password() {
                $.ajax({
                    url: '/user/forgot-password/update-password',
                    data: {
                        _token: '{!! csrf_token() !!}',
                        email: $('#forgot_password_email').val(),
                        passwd: $('#forgot_password_new').val(),
                        passwd_confirm: $('#forgot_password_confirm').val()
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function (res) {
                        if ($.trim(res.msg) === '') {
                            myApp.showSuccess('Your password has been updated successfully!', function() {
                                window.location.reload();
                            });
                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                });
            }

            function show_login() {
                $('#login-modal-parent').modal();

                $(".modal").css("overflow-y", "hidden");

                var id = $('.login-active').prop('id');
                if (id === 'modal-login') {
                    setTimeout(function () {
                        $('.modal').css('overflow-y', 'auto');
                    });
                } else {
                    $('.login-active').animateCss('flipOutY', function () {
                        $('.login-active').addClass('hidden');
                        $('.login-active').removeClass('login-active');
                        $('#modal-login').removeClass('hidden');
                        $("#modal-login").animateCss('flipInY');
                        $('#modal-login').addClass('login-active');

                        setTimeout(function () {
                            $('.modal').css('overflow-y', 'auto');
                        });
                    });
                }
            }

            function show_login_register() {
                $('#login-modal-parent').modal();

                $(".modal").css("overflow-y", "hidden");

                switch_content($('#login_pg_register_button'));
            }

			// function show_register() {
            //
			// 	$("#login-modal-parent .modal-body").addClass("hidden");
			//     $("#register-form").removeClass("hidden");
			// 	$("#register-form .black-btn").addClass("hidden");;
            //     $('#login-modal-parent').modal();
            //
			// 	//Do not show GO BACK button when clicked on Register button directly
			// 	$('#login-modal-parent').on('hidden.bs.modal', function () {
			// 		$("#register-form .black-btn").removeClass("hidden");
            //
			// 	})
            //
            // }

            function show_forgot_password() {
                $(".modal").css("overflow-y", "hidden");

                $('.login-active').animateCss('flipOutY', function () {
                    $('.login-active').addClass('hidden');
                    $('.login-active').removeClass('login-active');
                    $('#modal-forgot-password').removeClass('hidden');
                    $('#modal-forgot-password').addClass('login-active');
                    $("#modal-forgot-password").animateCss('flipInY');

                    setTimeout(function () {
                        $('.modal').css('overflow-y', 'auto');
                    });
                });
            }

            function show_temporary_key() {
                $(".modal").css("overflow-y", "hidden");

                $('.login-active').animateCss('flipOutY', function () {
                    $('.login-active').addClass('hidden');
                    $('.login-active').removeClass('login-active');
                    $('#modal-temporary-key').removeClass('hidden');
                    $('#modal-temporary-key').addClass('login-active');
                    $("#modal-temporary-key").animateCss('flipInY');

                    setTimeout(function () {
                        $('.modal').css('overflow-y', 'auto');
                    });
                });
            }

            function show_update_password() {
                $(".modal").css("overflow-y", "hidden");

                $('.login-active').animateCss('flipOutY', function () {
                    $('.login-active').addClass('hidden');
                    $('.login-active').removeClass('login-active');
                    $('#modal-update-password').removeClass('hidden');
                    $('#modal-update-password').addClass('login-active');
                    $("#modal-update-password").animateCss('flipInY');

                    setTimeout(function () {
                        $('.modal').css('overflow-y', 'auto');
                    });
                });
            }

			function show_forgot_password() {
                $(".modal").css("overflow-y", "hidden");

                $('.login-active').animateCss('flipOutY', function () {
                    $('.login-active').addClass('hidden');
                    $('.login-active').removeClass('login-active');
                    $('#modal-forgot-password').removeClass('hidden');
                    $('#modal-forgot-password').addClass('login-active');
                    $("#modal-forgot-password").animateCss('flipInY');

                    setTimeout(function () {
                        $('.modal').css('overflow-y', 'auto');
                    });
                });
            }

			function switch_content(elem) {

				var targetContent, activeContent, animationIn, animationOut;

					$(".modal").css("overflow-y","hidden");

					targetContent = $(elem).data("content");
					animationIn = $(elem).data("animationin");
					animationOut = $(elem).data("animationout");
					activeContent = $(elem).closest(".modal-body");

					if (!animationOut) {
						animationOut = 'flipOutY';
					}

					if (!animationIn) {
						animationIn = 'flipInY';
					}

					$(activeContent).animateCss(animationOut, function() {
						$(activeContent).addClass("hidden");
						$("#"+targetContent).removeClass("hidden");
						$("#"+targetContent).animateCss(animationIn);
						setTimeout(function(){$(".modal").css("overflow-y","auto");},1000);

					});

			}

			{{--function register() {--}}
			{{--	var terms = $('#s_terms').is(':checked');--}}
			{{--	if (!terms) {--}}
			{{--		alert('Please accept our terms and condition!');--}}
			{{--		return;--}}
			{{--	}--}}

            {{--    var home_type = $("input[name='home_type']:checked").val();{--}}
            {{--        if (home_type === 'apartment' && ($('#address2').val().length === 0 ) ){--}}
            {{--            alert('Please Input Address 2');--}}
            {{--            return;--}}
            {{--        }--}}
            {{--    }--}}

            {{--    var dog = ($('#dog').is(":checked")) ? 'Y' : '';--}}
            {{--    var cat = ($('#cat').is(":checked")) ? 'Y' : '';--}}

			{{--	$.ajax({--}}
			{{--		url: '/user/register',--}}
			{{--		data: {--}}
			{{--			_token: '{!! csrf_token() !!}',--}}
			{{--			first_name: $('#s_first_name').val(),--}}
			{{--			last_name: $('#s_last_name').val(),--}}
			{{--			phone: $('#s_phone').val(),--}}
			{{--			email: $('#s_email').val(),--}}
            {{--            dog: dog,--}}
            {{--            cat: cat,--}}
			{{--			hear_from: $('#s_hear_from').val(),--}}
			{{--			referral_code: $('#s_referral_code').val(),--}}
            {{--            address1: $('#address1').val(),--}}
            {{--            address2: $('#address2').val(),--}}
            {{--            city: $('#city').val(),--}}
            {{--            state: $('#state').val(),--}}
			{{--			zip: $('#s_zip').val(),--}}
			{{--			password: $('#s_password').val(),--}}
			{{--			password_confirm: $('#s_password_confirm').val(),--}}

			{{--		},--}}
			{{--		cache: false,--}}
			{{--		type: 'post',--}}
			{{--		dataType: 'json',--}}
			{{--		success: function(res) {--}}
			{{--			if ($.trim(res.msg) === '') {--}}
			{{--				// window.location.reload();--}}
			{{--				window.location='/user';--}}
			{{--			} else {--}}
			{{--				alert(res.msg);--}}
			{{--			}--}}
			{{--		}--}}
			{{--	});--}}
			{{--}--}}

            function show_user_profile() {

                myApp.showLoading();

                $.ajax({
                    url: '/user/profile/load',
                    data: {
                        _token: '{!! csrf_token() !!}'
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function(res) {
                        myApp.hideLoading();
                        if ($.trim(res.msg) === '') {

                            $('#editMyProfile').find('#first_name').val(res.first_name);
                            $('#editMyProfile').find('#last_name').val(res.last_name);
                            $('#editMyProfile').find('#phone').val(res.phone);


							if (res.photo !== "") {

								$('#editMyProfile').find('#photo').prop('src', 'data:image/png;base64,' + res.photo);
							}


                            $('#editMyProfile').modal('show');
                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                });

            }

            function read_file_for_profile(input) {

                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#editMyProfile').find('#photo').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            function update_user_profile() {

                var data = new FormData();
                data.append('_token', '{!! csrf_token() !!}');
                data.append('first_name', $('#editMyProfile').find('#first_name').val());
                data.append('last_name', $('#editMyProfile').find('#last_name').val());
                data.append('phone', $('#editMyProfile').find('#phone').val());

                if ($('#editMyProfile').find('#user_photo')[0].files.length > 0) {
                    data.append('photo', $('#editMyProfile').find('#user_photo')[0].files[0]);
                }

                $('#editMyProfile').modal('hide');

                myApp.showLoading();
                $.ajax({
                    url: '/user/profile/update',
                    data: data,
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        myApp.hideLoading();
                        if ($.trim(res.msg) === '') {
                            myApp.showSuccess('Your request has been processed successfully!', function() {
                                $('#editMyProfile').modal('show');

                                // clean up reset password //
                                $('#editMyProfile').find('#current_password').val('');
                                $('#editMyProfile').find('#new_password').val('');
                                $('#editMyProfile').find('#new_password_confirmation').val('');
                            });
                        } else {
                            myApp.showError(res.msg, function() {
                                $('#editMyProfile').modal('show');
                            });
                        }
                    }
                });
            }

            function reset_user_password() {
                $('#editMyProfile').modal('hide');
                myApp.showLoading();

                $.ajax({
                    url: '/user/profile/reset-password',
                    data: {
                        _token: '{!! csrf_token() !!}',
                        current_password: $('#editMyProfile').find('#current_password').val(),
                        new_password: $('#editMyProfile').find('#new_password').val(),
                        new_password_confirmation: $('#editMyProfile').find('#new_password_confirmation').val()
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function(res) {
                        myApp.hideLoading();
                        if ($.trim(res.msg) === '') {
                            myApp.showSuccess('Your request has been processed successfull!', function() {
                                // clean up reset password //
                                $('#editMyProfile').find('#current_password').val('');
                                $('#editMyProfile').find('#new_password').val('');
                                $('#editMyProfile').find('#new_password_confirmation').val('');
                                $('#editMyProfile').modal('show');
                            })
                        } else {
                            myApp.showError(res.msg, function() {
                                $('#editMyProfile').modal('show');
                            })
                        }
                    }
                })
            }
        </script>
    </body>
</html>
