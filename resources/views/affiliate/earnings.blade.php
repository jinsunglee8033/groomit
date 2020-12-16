@extends('includes.affiliate_default')
@section('contents')

    <script type="text/javascript">

        window.onload = function() {

            $('#bank_withdraw').click(function () {
                withdraw($('#bank_amount').val(), 'B');
            });

            $('#check_withdraw').click(function () {
                withdraw($('#check_amount').val(), 'C');
            });
        };

        function withdraw(amt, type) {

            if (amt > {!! $earnings !!}) {
                myApp.showError('Withdraw amount cannot exceed your earning amount!');
                return;
            }

            if (amt < 100) {
                myApp.showError('Minimum withdraw amount is $100.00');
                return;
            }

            myApp.showLoading();
            $.ajax({
                url: '/affiliate/withdraw',
                data: {
                    _token: '{{ csrf_token() }}',
                    amt: amt,
                    type : type
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        $('#myModal').modal('hide');
                        $('#myModal2').modal('hide');
                        myApp.showSuccess('Your request has been processed successfully!', function() {
                            window.location.reload();
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

    <div class="container-fluid container-grey-nav" style="padding-top:100px;"><!-- Starts Container -->
        <div class="container"><!-- Starts Container -->
            <div class="row"><!-- Starts row -->
                <div class="col-md-10 col-md-offset-1"><!-- Starts col-10 -->
                    <div class="grey-nav text-center">
                        <ul>
                            <li class="active"><a href="/affiliate/earnings">EARNINGS</a></li>
                            <li><a href="/affiliate/promo-code">PROMO CODE</a></li>
                            <li><a href="/affiliate/contact-us">CONTACT US</a></li>
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
                    <h3 class="text-center">EARNINGS</h3>
                    <h2 class="earning-amount">${{ $earnings }}</h2>
                    <p>Current Earnings</p>

                    <div class="divider-line"></div>

                    <h3 class="text-center">PAYOUT</h3>

                    <ul class="earning-buttons">
                        <li><a href="#" class="color-btn red-btn" data-toggle="modal" data-target="#myModal">BY BANK TRANSFER</a></li>
                        <li><a href="#" class="color-btn white-btn" data-toggle="modal" data-target="#myModal2">BY CHECK</a></li>
                    </ul>

                    <p class="red-text">Minimum Payment amount $100.00</p>

                    <div class="row"><!-- Starts row -->
                        <div class="col-md-8 col-md-offset-2 text-center"><!-- Starts col-10 -->


                        </div>
                    </div>


                </div>
            </div><!-- Ends col-10 -->
        </div><!-- Ends row -->

    </div><!-- Ends Container -->




    <!-- Modal -->
    <div id="myModal" class="modal fade modal-affiliates" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content text-center">
                <div class="modal-body">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h3>PAYOUT</h3>
                    <h2 class="earning-amount">${{ $earnings }}</h2>
                    <p>Current Earnings</p>

                    <form class="form-in">
                        @if($data->bank_name && $data->bank_account_number && $data->routing_number)
                            <p class="red-text">Minimum Payment amount $100.00</p>
                            <p class="newp">Please enter the amount to transfer</p>
                            <input type="number" id="bank_amount" class="text-center" placeholder="$0.00">
                            <p>Bank account</p>
                            <p>{{ $data->bank_account_number }}</p>
                            <a href="#" id="bank_withdraw" class="red-btn btn-in">SUBMIT</a>
                        @else
                            <p style="margin-bottom: 50px;"><a class="text-danger" href="my-account">Please set up bank account information</a></p>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div id="myModal2" class="modal fade modal-affiliates" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content text-center">
                <div class="modal-body">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h3>PAYOUT</h3>
                    <h2 class="earning-amount">${{ $earnings }}</h2>
                    <p>Current Earnings</p>
                    <p class="red-text">Minimum Payment amount $100.00</p>

                    <p class="newp">Please enter the amount</p>

                    <form class="form-in">
                        <input type="number" id="check_amount" class="text-center" placeholder="$0.00">
                        <p>Name and Address to send</p>
                        <p>&nbsp;</p>

                        @if(!empty($data->full_address()))
                            <p>{{ $data->full_name() }}<br>{{ $data->full_address() }}</p>

                            <a href="#" id="check_withdraw" class="red-btn btn-in">SUBMIT</a>
                        @else
                            <p style="margin-bottom: 50px;"><a class="text-danger" href="my-account">Please provide your address</a></p>
                        @endif


                    </form>
                </div>
            </div>
        </div>
    </div>

@stop
