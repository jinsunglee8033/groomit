@extends('includes.affiliate_default')
@section('contents')

<script type="text/javascript">

    function custom_code_submit() {

        var regex = /^[A-Za-z0-9 ]+$/;
        var custom_code = $('#custom_code').val();
        var isValid = regex.test(custom_code);

        if(!isValid){
            alert("Contains Special Characters.");
            return;
        }
        if(custom_code.length < 6 || custom_code.length > 20){
            alert('Please enter 6 ~ 20 digit');
            return;
        }

        //document.forms[0].submit();
        $('#frm_app').submit();
    }
</script>

<div class="container-fluid container-grey-nav" style="padding-top:100px;"><!-- Starts Container -->
    <div class="container"><!-- Starts Container -->
        <div class="row"><!-- Starts row -->
            <div class="col-md-10 col-md-offset-1"><!-- Starts col-10 -->
                <div class="grey-nav text-center">
                    <ul>
                        <li><a href="/affiliate/earnings">EARNINGS</a></li>
                        <li class="active"><a href="/affiliate/promo-code">PROMO CODE</a></li>
                        <li><a href="/affiliate/contact-us">CONTACT US</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container container-in-regular"><!-- Starts Container -->
    <div class="row"><!-- Starts row -->
        <div class="col-md-10 col-md-offset-1 text-center"><!-- Starts col-6 -->

            @if ($alert = Session::get('alert'))
                <div class="alert alert-warning">
                    {{ $alert }}
                </div>
            @endif

            <div class="row cont-code"><!-- Starts row -->
                <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                    <div class="row">
                        @foreach ($data as $d)
                        <div class="col-sm-6 text-center"><!-- Starts col-4 -->
                            <a href="#/" class="color-btn grey-white-btn full-w">PROMO CODE: {{ $d->aff_code }}</a>
                            <p class="text-center">Assigned date: {{ $d->assigned_date }} </p>
                        </div>
                        @endforeach
                    </div>
                    <div class="row">
                        <div class="col-sm-6 text-center"><!-- Starts col-4 -->
                            <a href="/affiliate/create-promo-code" class="color-btn white-btn full-w">REQUEST NEW PROMO CODE</a>
                        </div>

                        <div class="col-sm-6 text-center"><!-- Starts col-4 -->
                            <a href="#" class="color-btn white-btn full-w" data-toggle="modal" data-target="#myModal2">CREATE CUSTOM CODE</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 text-center"><!-- Starts col-12 -->
                    <table class="table table-striped text-left promo-code-table">
                        <thead>
                        <tr>
                            <th>Promo Code</th>
                            <th>Advertiser</th>
                            <th>Redemption</th>
                            <th>Commission</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach ($data as $d)
                        <tr>
                            <td>{{ $d->aff_code }}</td>
                            <td>{{ $user->business_name }}</td>
                            <td>{{ $d->cnt }}</td>
                            <td>${{ $d->commission }}</td>
                        </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- Ends col-10 -->
    </div><!-- Ends row -->

</div><!-- Ends Container -->


<!-- Modal -->
<div id="myModal2" class="modal fade modal-affiliates" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content text-center">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h3>CREATE A NEW CUSTOM PROMO CODE</h3>
                <p class="newp">Please enter the Code</p>
                <form id='frm_app' class="form-in" method="post" action="/affiliate/create-custom-promo-code">
                    {!! csrf_field() !!}
                    <input type="text" data-validation="alphanumeric" id="custom_code" name="custom_code" class="text-center">
                    <p>&nbsp;</p>
                    <a href="#" onclick="custom_code_submit();" class="red-btn btn-in">SUBMIT</a>
                </form>
            </div>
        </div>
    </div>
</div>

@stop
