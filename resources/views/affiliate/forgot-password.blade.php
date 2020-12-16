@extends('includes.affiliate_default')
@section('contents')
    <script type="text/javascript">

        function verify_email() {

            myApp.showLoading();
            $.ajax({
                url: '/affiliate/forgot-password/verify-email',
                data: {
                    _token: '{{ csrf_token() }}',
                    email : $('#email').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        myApp.showSuccess('Your Verification key has been sent. Please check your email and use it here.', function() {
                            window.location.href = '/affiliate/forgot-password/verify-key';
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
                    <h3 class="text-center">Verify Email</h3>

                    <div class="row"><!-- Starts row -->
                        <div class="col-md-10 col-md-offset-1 text-center cont-cont-white-form"><!-- Starts col-10 -->
                            <input type="email" id="email" name="email" placeholder="Your Email*" required>
                            <a href="#" class="red-btn full-w" onclick="verify_email()">SUBMIT</a>
                        </div>
                    </div>
                </div>
            </div><!-- Ends col-10 -->
        </div><!-- Ends row -->
    </div><!-- Ends Container -->
@stop
