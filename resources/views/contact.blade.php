@extends('includes.default_v2')
@section('contents')
<!-- Banner -->
<div class="top-banner top-banner--position-top top-banner--contact" title="Dog grooming near me"></div>
<div class="content content--contact top-bar--hidden pb-5">
    <div class="container content__overlapped">
        <div class="row mt-3 mt-lg-5">
            <div class="col-lg-8 offset-lg-2 col-sm-10 offset-sm-1">
                <h1 class="text-center mb-5 content__main-title content__title--neutra-disp">CONTACT US</h1>
                <form id="frm_contact" name="frm_contact" method="post" action="/contact_us" onsubmit="return checkForm(this);" novalidate>
                    {!! csrf_field() !!}

                    @if($errors->any())
                    <div class="alert alert-dark" role="alert" sr-only="Feedback message">
                        {{$errors->first()}}
                    </div>
                    @endif
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="ga-label" for="first_name">First Name</label>
                                <input class="form-control ga-form-control" type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                <div class="invalid-feedback">
                                    This field is required.
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="ga-label" for="last_name">Last Name</label>
                                <input class="form-control ga-form-control" type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                <div class="invalid-feedback">
                                    This field is required.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="ga-label" for="email">Email Address</label>
                                <input class="form-control ga-form-control" type="email" id="email" name="email" value="{{ old('email') }}" required>
                                <div class="invalid-feedback">
                                    This field is required and must be a valid email address.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="ga-label" for="message">Message</label>
                                <textarea class="form-control ga-form-control" name="message" row="10">{{ old('message') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-0">
                                <label class="ga-label" for="myCanvas">Verification Code</label>
                                <canvas id="myCanvas" style="width: 100%; height: 100px;"></canvas>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="ga-label" for="last_name">Enter <span class="d-none d-sm-inline">verification</span> code</label>
                                <input class="form-control ga-form-control" type="text" id="verification_code" name="verification_code" required>
                                <div class="invalid-feedback ga-invalid-feedback">
                                    This field is required.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group text-center">
                                <input class="btn btn-primary--groomit px-5" type="submit" name="submitBtn" value="SEND">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    var canvas = document.getElementById('myCanvas');
    var context = canvas.getContext('2d');

    context.fillStyle = "#FFF";

    var w = context.width;
    var h = context.height;

    //context.fillRect(0, 0, 320, 93);

    //context.font = "15px Open Sans";
    context.fillStyle = "#bd1a29";
    //context.fillText('Verification Code', 20, 25);
    context.font = "40px Open Sans";
    context.fillRect(0, 5, 200, 3);
    context.fillRect(0, 25, 200, 3);
    context.fillRect(0, 45, 200, 3);
    context.fill();
    context.fillText('{{ $verification_code }}', 20, 40);

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

    function checkForm(form) {

        // event.preventDefault();
        var inputs = document.getElementsByClassName('form-control');
        var no_error = 0;
        // loop over each input and watch blue event
        var validation = Array.prototype.filter.call(inputs, function(input) {

            // reset
            input.classList.remove('is-invalid')
            input.classList.remove('is-valid')

            if (input.checkValidity() === false) {
                input.classList.add('is-invalid')
                no_error =  1;
            }
            else {
                input.classList.add('is-valid')
            }

        });

        //var invalidFields = $(".is-invalid");
        //console.log(invalidFields);

        // alert(`${JSON.stringify(invalidFields)}`);
        // alert( 'lengh:' + $(invalidFields).length  );
        //if ($(invalidFields).length == 0) {
        if ( no_error === 0) {
            form.submitBtn.disabled = true;
            form.submitBtn.value = "Please wait...";
            form.submitBtn.style = "background-color:#c55762";
            return true;
        }else {
            return false;
        }


    }
</script>
@stop
