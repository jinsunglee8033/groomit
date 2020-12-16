@extends('includes.affiliate_default')
@section('contents')
    <script type="text/javascript">

        @if(session()->has('email'))
            $(window).on('load', function () {
            $('#email').val('{!! session('email') !!}');
        });
        @endif

        function reset() {

            myApp.showLoading();
            $.ajax({
                url: '/affiliate/forgot-password/update-password',
                data: {
                    _token: '{{ csrf_token() }}',
                    email : $('#email').val(),
                    password : $('#password').val(),
                    password_confirmation : $('#password_confirmation').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        myApp.showSuccess('Your password updated successfully!', function() {
                            window.location.href = '/affiliate/login';
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }
    </script>


    <div class="container container-in"><!-- Starts Container -->
        <div class="row"><!-- Starts row -->
            <div class="col-md-12"><!-- Starts col-12 -->

            </div><!-- Ends col-12 -->
        </div><!-- Ends row -->

        <div class="row"><!-- Starts row -->
            <div class="col-md-10 col-md-offset-1 text-center cont-cont-white-form"><!-- Starts col-10 -->
                <div class="cont-white-form">
                    <h3 class="text-center">Reset Password</h3>

                    <div class="row"><!-- Starts row -->
                        <div class="col-md-10 col-md-offset-1 text-center cont-cont-white-form"><!-- Starts col-10 -->
                            <input type="text" id="email" name="email" placeholder="Your Email*" required>
                            <input type="password" id="password" name="password" placeholder="Password*" required>
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repeat Password*" required>
                            <a href="#" class="red-btn full-w" onclick="reset()">SUBMIT</a>
                        </div>
                    </div>
                </div>
            </div><!-- Ends col-10 -->
        </div><!-- Ends row -->
    </div><!-- Ends Container -->
@stop
