@extends('includes.affiliate_default')
@section('contents')
    <div class="container-fluid container-grey-nav" style="padding-top:100px;"><!-- Starts Container -->
        <div class="container"><!-- Starts Container -->
            <div class="row"><!-- Starts row -->
                <div class="col-md-10 col-md-offset-1"><!-- Starts col-10 -->
                    <div class="grey-nav text-center">
                        <ul>
                            <li><a href="/affiliate/earnings">EARNINGS</a></li>
                            <li><a href="/affiliate/promo-code">PROMO CODE</a></li>
                            <li class="active"><a href="/affiliate/contact-us">CONTACT US</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container container-in-regular"><!-- Starts Container -->
        <div class="row"><!-- Starts row -->
            <div class="col-md-10 col-md-offset-1 text-center"><!-- Starts col-10 -->
                <div>
                    <h3 class="text-center">CONTACT US</h3>

                    <form class="form-in form-contact"method="post" action="/affiliate/send-contact-us">
                        {!! csrf_field() !!}

                        @if ($alert = Session::get('alert'))
                            <div class="alert alert-danger">
                                {{ $alert }}
                            </div>
                        @endif
                        <input type="text" name="first_name" placeholder="First Name*" value="{{ $data->first_name }}">
                        <input type="text" name="last_name" placeholder="Last Name*" value="{{ $data->last_name }}" >
                        <input class="full-w" name="email" type="email" placeholder="E-mail Address*" value="{{ $data->email }}" >
                        <input class="full-w" name="subject" type="text" placeholder="Subjet*" >
                        <textarea class="full-w" name="message" placeholder="Message"></textarea>

                        <a href="#" onclick="document.forms[0].submit();" class="red-btn btn-in full-w">SEND</a>
                    </form>


                </div>
            </div><!-- Ends col-10 -->
        </div><!-- Ends row -->

    </div><!-- Ends Container -->
@stop