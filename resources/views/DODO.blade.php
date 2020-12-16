@extends('includes.default')

@section('content')

    <script type="text/javascript">
        var onload_events = window.onload;
        var validator = null;
        window.onload = function () {
            if (onload_events) {
                onload_events();
            }

            validator = $('#frm_app').validate({
                focusInvalid: true,
                focusCleanup: true,
                ignore: [],
                rules: {
                    phone: {
                        regex: /^\d{10}$/
                    }
                },
                messages: {
                    phone: {
                        regex: 'Please enter valid 10 digit phone number!!!'
                    }
                },
                tooltip_options: {
                    //_all_: {container: 'body'},
                    _all_: {trigger: 'focus'}
                }
            });


            @if (session()->has('success') && session('success') == 'Y')
                $('#success').modal();
            @endif

            @if (count($errors) > 0)
                $('#error').modal();
            @endif
        };


        function submit_form() {

            if (
                !$('#phone').valid())  {

                location.href = "#DODO";
                validator.focusInvalid();
                return;
            }

            $('#frm_app').submit();
        }

        /*function readURL(input) {

            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#img_groomed_photo').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }*/
    </script>

    @if (session()->has('success') && session('success') == 'Y')
        <div id="success" class="modal fade " tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false"
             style="display:block;">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Success</h4>
                    </div>
                    <div class="modal-body">
                        <p>
                            Your request is being processed.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (count($errors) > 0)
        <div id="error" class="modal fade " tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false"
             style="display:block;">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Error</h4>
                    </div>
                    <div class="modal-body">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div id="application-main">
        <section id="banner" class="display-table" style="padding:0 !important;padding-top:0 !important; position:relative;"> <!-- #banner starts -->
            <!-- <div class="container container-banner-info table-cell">
                <div class="col-xs-12" id="centerBannerHome" >
                    <h1 data-aos="zoom-in" data-aos-delay="750">Work with us</h1>
                </div>
            </div> -->
        </section>
        <!-- /end -->
        <section id="benefits" class="log-out"> <!-- #benefits starts -->

            <form id="frm_app" name="frm_app" method="post" action="/DODO" enctype="multipart/form-data">
                {!! csrf_field() !!}
                <div class="container">

                    <div class="row">
                        <div class="col-lg-8 text-center col-lg-offset-2">
{{--                        <h2 class="h2title text-center">DODO</h2>--}}
                        </div>

                        <div class="col-lg-8 text-center col-lg-offset-2">
                            <div class="formSignUpApp">

                                <div class="field">
                                    <div class="col-lg-12 col-md-12 col-sm-12 normal-first">
                                        <input type="text" id="full_name" name="full_name" placeholder="Full Name" value="{{old('full_name') }}"/>
                                    </div>
                                </div>

                                <div class="field">
                                    <div class="col-lg-12 col-md-12 col-sm-12 normal-first">
                                        <input type="email" id="email" name="email" placeholder="Email" value="{{ old('email') }}"/>
                                    </div>
                                </div>

                                <div class="field">
                                    <div class="col-lg-12 col-md-12 col-sm-12 normal-first">
                                        <input type="text" id="phone" name="phone" placeholder="Phone Number" value="{{ old('phone') }}" required/>
                                    </div>
                                </div>

                                <div class="field">
                                    <div class="col-lg-12 col-md-12 col-sm-12 normal-first">
                                        <input type="text" id="zip" name="zip" placeholder="zip" value="{{ old('zip') }}"/>
                                    </div>
                                </div>

                                <div class="field">
                                    <div class="col-lg-12  text-center normal-first">
                                        <!--a class="scrollToTop collapsed submitBtn signUpSubmit enableDisabled" data-toggle="collapse" data-parent="#accordion" data-target="#collapseOne"   aria-controls="collapseOne">CONTINUE</a-->
                                        <a class="scrollToTop collapsed submitBtn signUpSubmit enableDisabled"
                                        href="javascript:submit_form()">Submit</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </form>
            <div class="clearfix"></div>
        </section>
    </div>

@stop
