@extends('includes.affiliate_default')
@section('contents')

    <div class="container container-in"><!-- Starts Container -->
        <div class="row"><!-- Starts row -->
            <div class="col-md-12"><!-- Starts col-12 -->

            </div><!-- Ends col-12 -->
        </div><!-- Ends row -->

        <div class="row"><!-- Starts row -->
            <div class="col-md-10 col-md-offset-1 text-center cont-cont-white-form"><!-- Starts col-10 -->
                <div class="cont-white-form">
                    <h3 class="text-center">Login</h3>

                    <div class="row"><!-- Starts row -->
                        <div class="col-md-8 col-md-offset-2 text-center cont-cont-white-form"><!-- Starts col-10 -->
                            <form id="frm_login" class="form-in form-in-login" method="post" action="/affiliate/login">
                                {!! csrf_field() !!}
                                @if ($alert = Session::get('alert'))
                                    <div class="alert alert-danger">
                                        {{ $alert }}
                                    </div>
                                @endif
                                <input type="text" name="email" placeholder="Email*" >
                                <input type="password" name="password" placeholder="Password*" >
                                <div class="full-w text-right">
                                    <a class="red-link" href="/affiliate/forgot-password/verify-email">Forgot your password?</a>
                                </div>
{{--                                <a href="#" onclick="document.forms[0].submit();" class="red-btn btn-in full-w">LOGIN</a>--}}
                                <button class="btn btn-default btn-rounded btn-red aos-init aos-animate" type="submit">LOGIN</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div><!-- Ends col-10 -->
        </div><!-- Ends row -->
    </div><!-- Ends Container -->

    <script type="text/javascript">
        $(document).ready(function(){
            lastElementTop = $('#frm_login').position().top ;
            console.log(lastElementTop);

            var scrollAmount = lastElementTop - 200 ;

            //window.scrollTo(0, scrollAmount);
            //window.scrollTo(0, 10000);
            $('body,html').animate({scrollTop: scrollAmount},200);
        });
    </script>
@stop
