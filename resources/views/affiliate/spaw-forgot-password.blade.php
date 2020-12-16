@extends('includes.affiliate_default_v2')
@section('contents')
    <script type="text/javascript">

        (function() {
        'use strict';
        window.addEventListener('load', function() {
            // fetch all the forms we want to apply custom style
            var inputs = document.getElementsByClassName('form-control')

            // loop over each input and watch blue event
            var validation = Array.prototype.filter.call(inputs, function(input) {

            input.addEventListener('blur', function(event) {
                // reset
                input.classList.remove('is-invalid')
                input.classList.remove('is-valid')

                if (input.checkValidity() === false) {
                    input.classList.add('is-invalid')
                }
                else {
                    input.classList.add('is-valid')
                }
            }, false);
            });
        }, false);
        })()

        function verify_email() {

            event.preventDefault();

            var inputs = document.getElementsByClassName('form-control');
            var invalidFields = $(".is-invalid");

            // loop over each input and watch blue event
            var validation = Array.prototype.filter.call(inputs, function(input) {

            // reset
            input.classList.remove('is-invalid')
            input.classList.remove('is-valid')

                if (input.checkValidity() === false) {
                    input.classList.add('is-invalid')
                }
                else {
                    input.classList.add('is-valid')
                }

            });

            if ($(invalidFields).length == 0) {

                myApp.showLoading();
                $.ajax({
                    url: '/affiliate/forgot-password/spaw-verify-email',
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
                            myApp.showSuccess('Your verification key has been sent. Please, check your email to continue.', function() {
                                window.location.href = '/affiliate/forgot-password/spaw-verify-key';
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

            
        }
    </script>

<div class="content content--affiliates top-bar--hidden pb-5">
    <div class="container content__overlapped">
        <div class="row">
            <div class="col-lg-6 offset-lg-3 col-md-8 offset-md-2 col-sm-10 offset-sm-1">
                <div class="row spaw-heading">
                    <div class="col">
                        <img class="img-fluid" src="../../images/spaw-logo.png" alt="Spaw" />
                    </div>
                    <div class="col text-center">
                        <span class="spaw-heading__txt">is now</span>
                    </div>
                    <div class="col spaw-heading__g-logo">
                        <img class="img-fluid" src="../../images/logo-landscape.png" alt="Groomit" />
                    </div>
                </div>
                <p class="content__subtitle text-center">
                    We're excited to announce that we aquired Spaw Grooming. Welcome to Groomit, NYC's largest in-home grooming platform.
                </p>
                <p class="content__subtitle text-center"><strong>Schedule your first appointment with Groomit.</strong></p>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-lg-4 offset-lg-4 col-md-6 offset-md-3 col-sm-8 offset-sm-2 text-center">
                <h4 class="text-center mb-3">PLEASE VERIFY EMAIL</h4>
                <div class="form-group">
                    <input class="form-control ga-form-control" type="email" id="email" name="email" placeholder="Email" required>
                    <div class="invalid-feedback">
                        The email field is required.
                    </div>
                </div>
                <a href="#" class="btn btn-primary--groomit px-5 mt-3" onclick="verify_email()">SUBMIT</a>
            </div>
        </div>
    </div>
</div>
@stop
