@extends('includes.affiliate_default')
@section('contents')

    <div class="container " style="padding-top:100px;">
        <div class="row"><!-- Starts row -->
            <div class="col-md-8 cont-cont-white-form cont-cont-white-form-my-account"><!-- Starts col-10 -->
                <h3 class="text-left">MY ACCOUNT</h3>
            </div>
        </div>
        <div class="row"><!-- Starts row -->
            @if ($alert = Session::get('alert'))
                <div class="alert alert-success">
                    {{ $alert }}
                </div>
            @endif
            @if ($error = Session::get('error'))
                <div class="alert alert-danger">
                    {{ $error }}
                </div>
            @endif
            <div class="col-md-4 text-center cont-cont-white-form cont-cont-white-form-my-account"><!-- Starts col-8 -->
                <div class="cont-white-form">
                    <h3 class="text-center">Personal Details</h3>

                    <form class="form-in" name="account" method="post" action="/affiliate/update-my-account">
                        {!! csrf_field() !!}
                        <input type="hidden" name="type" value="account">

                        <div class="form-group full-w">
                            <label for="first-name">First Name*:</label>
                            <input name="first_name" type="text" value="{{ $acct->first_name }}" required>
                        </div>
                        <div class="form-group full-w">
                            <label for="last-name">Last Name*:</label>
                            <input name="last_name" type="text" value="{{ $acct->last_name }}" required>
                        </div>
                        <div class="form-group full-w">
                            <label for="email">Email*:</label>
                            <input name="email" type="email" value="{{ $acct->email }}" required>
                        </div>
                        <div class="form-group full-w">
                            <label>Business Name:</label>
                            <input name="business_name" type="text" value="{{ $acct->business_name }}">
                        </div>
                        <div class="form-group full-w">
                            <label>Password*:</label>
                            <input name="password" type="password" required>
                        </div>
                        <div class="form-group full-w">
                            <label>Repeat Password*:</label>
                            <input name="password_confirmation" type="password" required>
                        </div>
                        <a href="#" onclick="document.account.submit();" class="red-text red-text-save">SAVE</a>
                    </form>
                </div>
            </div><!-- Ends col-8 -->

            <div class="col-md-4 text-center cont-cont-white-form cont-cont-white-form-my-account">
                <div class="cont-white-form">
                    <h3 class="text-center">Address</h3>

                    <form class="form-in" name="address" method="post" action="/affiliate/update-my-account">
                        {!! csrf_field() !!}
                        <input type="hidden" name="type" value="address">
                        <div class="form-group full-w">
                            <label for="address">Address*:</label>
                            <input name="address" type="text" value="{{ $acct->address }}" required>
                        </div>
                        <div class="form-group full-w">
                            <label for="address2">Address2:</label>
                            <input name="address2" type="text" value="{{ $acct->address2 }}">
                        </div>
                        <div class="form-group full-w">
                            <label for="email">City*:</label>
                            <input name="city" type="text" value="{{ $acct->city }}" required>
                        </div>
                        <div class="form-group full-w">
                            <label>State*:</label>
                            <input name="state" type="text" value="{{ $acct->state }}" required>
                        </div>
                        <div class="form-group full-w">
                            <label>Zip code*:</label>
                            <input name="zip" type="text" value="{{ $acct->zip }}" maxlength="5" required>
                        </div>
                        <div class="form-group full-w">
                            <label for="phone">Phone*:</label>
                            <input name="phone" type="text" value="{{ $acct->phone }}" maxlength="10" required>
                        </div>
                        <a href="#" onclick="document.address.submit();" class="red-text red-text-save">SAVE</a>
                    </form>
                </div>
            </div>


        <div class="col-md-4 text-center cont-cont-white-form cont-cont-white-form-my-account"><!-- Starts col-10 -->
                <div class="cont-white-form">
                    <h3 class="text-center">Bank Details</h3>
                    <form class="form-in" name="bank" method="post" action="/affiliate/update-my-account">
                        {!! csrf_field() !!}
                        <input type="hidden" name="type" value="bank">

                        <div class="form-group full-w">
                            <label for="bank_name">Bank Name:</label>
                            <input name="bank_name" value="{{ $acct->bank_name }}" type="text">
                        </div>
                        <div class="form-group full-w">
                            <label for="bank_account_number">Bank Account Number:</label>
                            <input name="bank_account_number" value="{{ $acct->bank_account_number }}" type="text">
                        </div>
                        <div class="form-group full-w">
                            <label for="routing_number">Routing Number:</label>
                            <input name="routing_number" value="{{ $acct->routing_number }}" type="text">
                        </div>
                        <a href="#" onclick="document.bank.submit();" class="red-text red-text-save">SAVE</a>
                    </form>
                </div>
            </div><!-- Ends col-4 -->
        </div><!-- Ends row -->


    </div><!-- Ends Container -->
@stop
