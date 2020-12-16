@extends('user.layout.default')
@section('content')
<link href="/desktop/css/appointment.css" rel="stylesheet" type="text/css">
<link href="/desktop/css/my-account.css" rel="stylesheet">

<style>
        .bh-pet-avatar-new {
            border: solid 1px #cacaca;
            background-color: #f2f1f1;
            border-radius: 50%;
            display: table;
            padding: 0px;
            width: 95px;
            height: 95px !important;
        }
 </style>
    <main class="main my-account" id="main">
        <div class="container">
            <h1 class="main__title text-center">My Account</h1>
            <div class="row">
                <div class="col-md-10 col-md-offset-1 col-sm-10 col-sm-offset-1">
                    <form action="">
                            <!-- Avatar, Name & Email -->
                            <div class="row align-items-end">

                                <div class="col-sm-6 px-30">

                                  <h3 class="groomit-card-title">&nbsp;</h3>

                                <!-- Account info cont start-->
                                  <div class="groomit-card mb-3">
                                      <div class="row">
                                          <div class="col-sm-4 col-md-offset-4 text-center groomit-card_account_photo">
                                            <div class="media-center media-middle text-center">
                                                <div class="c-photo c-photo--myaccount mx-auto">
                                                    <div class="c-photo__mask c-photo__mask--myaccount">
                                                        <img src="data:image/png;base64,{{ $user->photo }}" class="  c-photo__uploaded-photo" alt="Avatar">
                                                    </div>
                                                </div> 
                                            </div>
                                          </div>
{{--                                          <div class="col-sm-4 text-xs-center text-right">--}}
{{--                                            <label>ID: {{ $user->user_id }}</label>--}}
{{--                                          </div>--}}
                                      </div>
                                      <div class="row">
                                        <div class="col-sm-12 mt-2 text-center mb-3">
                                          <div class="inline-block text-left">
                                            <h2>{{ $user->first_name }} {{ $user->last_name }}</h2>
                                            <label for="">Referral code</label>
                                            <h3>{{ $user->referral_code }}</h3>
                                          </div>  
                                        </div>
                                        <div class="col-sm-6 mt-2 text-left">
                                            <label for="">Phone Number</label>
                                            <h3>{{ $user->phone }}</h3>
                                        </div>
                                        <div class="col-sm-6 mt-2 text-left">
                                            <label for="">Email</label>
                                            <h>{{ $user->email }}</h>
                                        </div>
                                        <div class="col-sm-6 mt-2 text-left">
                                            @if (!empty($user->available_credit))
                                                <label for="">Groomit Credits</label>
                                                <h3>${{ $user->available_credit }}</h3>
                                            @endif
                                        </div>
                                        <div class="col-sm-6 mt-2 text-left">
                                            @if ($user->dog == 'Y' && $user->cat == 'Y')
                                                <label for="">Pets</label>
                                                <h3>Dog & Cat</h3>
                                            @elseif ($user->dog == 'Y')
                                                <label for="">Pets</label>
                                                <h3>Dog</h3>
                                            @elseif ($user->cat == 'Y')
                                                <label for="">Pets</label>
                                                <h3>Cat</h3>
                                            @endif
                                        </div>
                                        <div class="col-xs-12 mt-5 text-center">
                                            <button type="button" class="groomit-btn red-btn rounded-btn long-btn type-submit" onclick="window.location.href='/user/myaccount-edit'">Edit</button>
                                        </div>       
                                      </div>
                                  </div>
                                <!-- Account info cont ends-->


                                <h3 class="groomit-card-title mt-5">My Favorite Groomers</h3>


                                    @if (count($user->favorite_groomers) > 0)
                                        @foreach($user->favorite_groomers as $o)
                                            <div class="groomit-card groomit-card-pet mb-3">
                                                <div class="row row-flex align-items-center">
                                                    <div class="col-xs-3 col-flex text-center groomit-card_account_photo">
                                                        <div class="c-photo c-photo_small ">
                                                            <div class="c-photo__mask c-photo__mask_small ">
                                                                @if (!empty($o->profile_photo))
                                                                    <img src="data:image/png;base64,{{ $o->profile_photo }}" class="  c-photo__uploaded-photo" alt="Avatar">
                                                                @else
                                                                    <img src="/images/banner-investors_sm.jpg" class="  c-photo__uploaded-photo" alt="Avatar">
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-9text-center col-flex">
                                                        <div class="text-left">
                                                            <h3>
                                                                <strong>{{ $o->first_name }} {{ $o->last_name }}</strong>
                                                                <a href="#" class="info-link" data-toggle="modal" data-id="" data-target="#fav-groomer-info-{{ $o->groomer_id }}">
                                                                    <i class="fas fa-info"></i>
                                                                </a>
                                                            </h3>
                                                            @if($o->dog == 'Y' && $o->cat == 'Y')
                                                                <h3>Dog and Cat Groomer</h3>
                                                            @elseif($o->dog == 'Y')
                                                                <h3>Dog roomer</h3>
                                                            @elseif($o->cat == 'Y')
                                                                <h3>Cat roomer</h3>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>


                                <!-- Groomer modal -->
                                @if (count($user->favorite_groomers) > 0)
                                    @foreach($user->favorite_groomers as $o)
                                        <div class="modal fade auto-width" id="fav-groomer-info-{{ $o->groomer_id }}" tabindex="-1" role="dialog" aria-labelledby="reschedule-title">
                                            <div class="modal-dialog auto-width" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close close-reschedule" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row-pet" >
                                                            <div class="text-center">
                                                                <div class="history-pet">
                                                                    <div class="bh-pet-avatar-new groomer-ph center text-center center-text">
                                                                        @if(!empty($o->profile_photo))
                                                                            <img class="media-object img-circle" src="data:image/png;base64,{{ $o->profile_photo }}" width="95" height="95" alt="Avatar">
                                                                        @else
                                                                            <img src="/desktop/img/dog-icon.svg" width="95" height="95" alt="Avatar">
                                                                        @endif
                                                                    </div>
                                                                    <p class="text-center"><strong>{{ $o->first_name }} {{ $o->last_name }}</strong></p>
                                                                    <p class="text-center">
                                                                        {{ $o->bio }}
                                                                    </p>
                                                                    @if ($o->total_appts >= 50)
                                                                    <div class="cell-rating">
                                                                        <div class="starrr" data-rating="{{ \App\Lib\Helper::get_avg_rating($o->groomer_id) }}" style="pointer-events: none;"></div>
                                                                        <input type="hidden" class="rating" name="rating" value=""/>
                                                                    </div>
                                                                    @endif
                                                                    <p class="mt-2"><span class="glyphicon glyphicon-heart make-fav-icon make-fav-icon-gr" onclick="remove_favorite({{$o->groomer_id}})"></span> <em>Favorite</em></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                                <!-- /groomer modal -->

                                
                                <div class="col-sm-6 px-30">

                                    <h3 class="groomit-card-title">Payment Method</h3>
                                  <!-- Payment Method cont start-->
                                    @if (count($user->payments) > 0)
                                        @foreach ($user->payments as $o)
                                            <div class="groomit-card mb-3">
                                                  <div class="row row-flex align-items-center">
                                                      <div class="col-xs-10 col-sm-10 col-md-10 col-lg-10 text-center col-flex">
                                                          <div class="text-left">
                                                              <label class="card-label" for="">{{ $o->card_number }}</label>
                                                              <label class="card-label" for="">VALID THRU {{ $o->expire_mm }}/{{ $o->expire_yy }}</label>
                                                          </div>
                                                      </div>
                                                      <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right col-flex">
                                                          <a class="inline-block" title="Edit" href="#/" onclick="javascript:show_card({{ $o->billing_id }})">
                                                              <i class="far fa-edit edit-i"></i>
                                                          </a>
                                                      </div>
                                                  </div>
                                            </div>
                                        @endforeach
                                    @endif
                                <!-- Payment Method cont ends-->

                                <!-- Modal CARD -->
                                    <div class="modal fade" id="modal-card" tabindex="-1" role="dialog" aria-labelledby="editCardLabel">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <h4 class="modal-title text-center" id="editCardLabel">PAYMENT</h4>

                                                    <!-- INIT FORM -->
                                                    <form action="" class="" role="form" method="post" id="paymentForm">
                                                        <input type="hidden" id="billing_id" value=""/>
                                                        <fieldset>

                                                            <!-- PAYMENT -->
                                                            <section id="payment">
                                                                <div class="container">
                                                                    <div class="row">
                                                                        <div class="col-sm-6">
                                                                            <div class="form-group">
                                                                                <label class="control-label" for="">Cardholder Full Name</label>
                                                                                <input class="form-control" name="card_holder" id="card_holder" type="text" maxlenght="100"
                                                                                       required/>
                                                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                                            </div>
                                                                            <!-- /form-group -->
                                                                        </div>
                                                                        <!-- col-6 -->
                                                                        <div class="col-sm-6">
                                                                            <div class="form-group">
                                                                                <label class="control-label" for="">Card Number</label>
                                                                                <input class="form-control credit-card" name="card_number" id="card_number" type="text"
                                                                                       required/>
                                                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                                            </div>
                                                                            <!-- /form-group -->
                                                                        </div>
                                                                        <!-- /col-6 -->
                                                                    </div>

                                                                    <!-- /row -->
                                                                    <div class="row">
                                                                        <div class="col-sm-6">
                                                                            <div class="row">
                                                                                <div class="col-lg-5 col-xs-6">
                                                                                    <div class="form-group select-form-group">
                                                                                        <label class="control-label" for="">Expiration Month</label>
                                                                                        <select class="form-control" name="expire_mm" id="expire_mm" required>
                                                                                            <option value="">Please Select</option>
                                                                                            @if (count($months) > 0)
                                                                                                @foreach ($months as $o)
                                                                                                    <option value="{{ $o['code'] }}">{{ $o['name'] }}</option>
                                                                                                @endforeach
                                                                                            @endif
                                                                                        </select>
                                                                                    </div>
                                                                                    <!-- /form-group -->
                                                                                </div>
                                                                                <!-- /col -->
                                                                                <div class="col-lg-7 col-xs-6">
                                                                                    <div class="form-group select-form-group">
                                                                                        <label class="control-label" for="">Expiration Year &nbsp; &nbsp;</label>
                                                                                        <select class="form-control" name="expire_yy" id="expire_yy" required>
                                                                                            <option value="">Please Select</option>
                                                                                            @if (count($years) > 0)
                                                                                                @foreach ($years as $o)
                                                                                                    <option value="{{ $o['code'] }}">{{ $o['name'] }}</option>
                                                                                                @endforeach
                                                                                            @endif
                                                                                        </select>
                                                                                    </div>
                                                                                    <!-- /form-group -->
                                                                                </div>
                                                                                <!-- /col -->
                                                                            </div>
                                                                            <!-- /row -->
                                                                        </div>
                                                                        <!-- col-6 -->
                                                                        <div class="col-sm-6">
                                                                            <div class="row">
                                                                                <div class="col-lg-6 col-xs-6">
                                                                                    <div class="form-group">
                                                                                        <label class="control-label" for="">CVV</label>
                                                                                        <input class="form-control" name="cvv" id="cvv" type="number" min="000"
                                                                                               max="9999" maxlength="4" required/>
                                                                                        <span class="form-highlight"></span> <span
                                                                                                class="form-bar"></span>
                                                                                    </div>
                                                                                    <!-- /form-group -->
                                                                                </div>
                                                                                <!-- /col -->
                                                                            </div>
                                                                            <!-- /row -->
                                                                        </div>
                                                                        <!-- /col-6 -->
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="col-sm-6">
                                                                            <div class="form-group">
                                                                                <div class="checkbox">
                                                                                    <label for="default_card">
                                                                                        <input type="checkbox" name="default_card" id="default_card">
                                                                                        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                                                                                        Make this credit card as default</label>
                                                                                </div>
                                                                                <!-- /checkbox -->
                                                                            </div>
                                                                            <!-- /form-group -->
                                                                        </div>
                                                                        <!-- /col-6 -->
                                                                    </div>
                                                                    <!-- /row -->

                                                                    <div class="row" id="div_same_address">
                                                                        <div class="col-sm-6">
                                                                            <div class="form-group">
                                                                                <div class="checkbox">
                                                                                    <label for="same_address">
                                                                                        <input type="checkbox" name="same_address" id="same_address" onclick="get_service_address()">
                                                                                        <span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
                                                                                        Make billing address same as service address</label>
                                                                                </div>
                                                                                <!-- /checkbox -->
                                                                            </div>
                                                                            <!-- /form-group -->
                                                                        </div>
                                                                        <!-- /col-6 -->
                                                                    </div>
                                                                    <!-- /row -->

                                                                    <div class="row">
                                                                        <div class="col-sm-6">
                                                                            <div class="form-group">
                                                                                <label class="control-label" for="">Address 1</label>
                                                                                <input class="form-control" name="address1" id="address1"
                                                                                       value="" type="text" maxlenght="100"
                                                                                       required/>
                                                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                                            </div>
                                                                            <!-- /form-group -->
                                                                        </div>
                                                                        <!-- col-6 -->
                                                                        <div class="col-sm-6">
                                                                            <div class="form-group">
                                                                                <label class="control-label" for="">Address 2</label>
                                                                                <input class="form-control" name="address2" id="address2" type="text" maxlenght="100"
                                                                                       required/>
                                                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                                            </div>
                                                                            <!-- /form-group -->
                                                                        </div>
                                                                        <!-- /col-6 -->
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-sm-6">
                                                                            <div class="form-group">
                                                                                <label class="control-label" for="">City</label>
                                                                                <input class="form-control" name="city" id="city" type="text" maxlenght="100"
                                                                                       required/>
                                                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                                                            </div>
                                                                            <!-- /form-group -->
                                                                        </div>
                                                                        <!-- col-6 -->
                                                                        <div class="col-sm-3">
                                                                            <div class="form-group select-form-group">
                                                                                <label class="control-label" for="">State</label>
                                                                                <select class="form-control" name="state" id="state" required>
                                                                                    <option value="">Please Select</option>
                                                                                    @if (count($states) > 0)
                                                                                        @foreach ($states as $o)
                                                                                            <option value="{{ $o->code }}">{{ $o->name }}</option>
                                                                                        @endforeach
                                                                                    @endif
                                                                                </select>
                                                                            </div>
                                                                            <!-- /form-group -->
                                                                        </div>
                                                                        <!-- /col-6 -->

                                                                        <div class="col-sm-3">
                                                                            <div class="form-group">
                                                                                <label class="control-label" for="">Zip Code</label>
                                                                                <input class="form-control" name="zip" id="zip" type="text" maxlength="11"
                                                                                       required/>
                                                                                <span class="form-highlight"></span> <span
                                                                                        class="form-bar"></span>
                                                                            </div>
                                                                            <!-- /form-group -->
                                                                        </div>
                                                                        <!-- /col -->

                                                                    </div>
                                                                    <!-- /row -->
                                                                </div>
                                                                <!-- /container -->
                                                            </section>
                                                            <section id="confirm-payment">
                                                                <div class="container">
                                                                    <div class="row">
                                                                        <div class="col-xs-12 text-center mb-4">
                                                                            <a class="red-link" id="delete_card_link" href="javascript:delete_card()"><strong>DELETE CARD</strong></a>
                                                                        </div>
                                                                    </div>
                                                                    <!-- row -->
                                                                    <div class="row">
                                                                        <div class="col-xs-6 text-right">
                                                                            <button type="button" class="groomit-btn black-btn rounded-btn" data-dismiss="modal">
                                                                                CANCEL
                                                                            </button>
                                                                        </div>
                                                                        <div class="col-xs-6 text-left">
                                                                            <button type="button" id="save_card_btn" class="groomit-btn red-btn rounded-btn" onclick="save_card()">
                                                                                SUBMIT
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    <!-- row -->
                                                                </div>
                                                                <!-- /container -->
                                                            </section>
                                                        </fieldset>
                                                    </form>

                                                    <!-- /FORM -->

                                                </div>
                                                <!-- /modal-body -->

                                            </div>
                                            <!-- /modal-content -->

                                        </div>
                                    </div>
                                    <!-- /Modal CARD -->


                                    <h3 class="groomit-card-title mt-5">Service Address</h3>
                                  <!-- Service Address cont start-->

                                    @if (count($user->addresses) > 0 )
                                        @foreach ($user->addresses as $a)
                                          <div class="groomit-card mb-3">
                                              <div class="row row-flex align-items-center">
                                                <div class="col-xs-10 col-sm-10 col-md-10 col-lg-10 text-center col-flex">
                                                  <div class="text-left">
                                                    <h3>{{ $a->address1 }} {{ $a->address2 }}<br>
                                                        {{ $a->city }} {{ $a->state }}, {{ $a->zip }}</h3>
                                                  </div>
                                                </div>
                                                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right col-flex">
                                                <a class="inline-block" title="Edit" href="#/" onclick="javascript:show_address({{ $a->address_id }})">
                                                      <i class="far fa-edit edit-i"></i>
                                                    </a>
                                                </div>
                                              </div>
                                          </div>
                                        @endforeach
                                    @endif

                                  <!-- Service Address cont ends-->  

                                <h3 class="groomit-card-title mt-5">Pets</h3>

                                    @if (count($user->dog_pets) > 0 )
                                        @foreach ($user->dog_pets as $p)
                                            <div class="groomit-card groomit-card-pet mb-3">
                                                <div class="row row-flex align-items-center">
                                                    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 col-flex text-center groomit-card_account_photo">
                                                        <div class="c-photo c-photo_small ">
                                                            <div class="c-photo__mask c-photo__mask_small ">
                                                                @if (!empty($p->photo))
                                                                    <img src="data:image/png;base64,{{ $p->photo }}" class="c-photo__uploaded-photo" alt="Avatar">
                                                                @else
                                                                    <img src="/images/banner-investors_sm.jpg" class="c-photo__uploaded-photo" alt="Avatar">
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-7 col-sm-7 col-md-7 col-lg-7 text-center col-flex">
                                                        <div class="text-left">
                                                            <h3><strong>{{ $p->name }} {{ strtoupper($p->type) }}</strong></h3>
                                                            <h3>{{ $p->breed_name }}</h3>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right col-flex">
                                                        <a class="inline-block" title="Edit" href="javascript:show_pet_dog({{ $p->pet_id }})">
                                                            <i class="far fa-edit edit-i"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                                    @if (count($user->cat_pets) > 0 )
                                        @foreach ($user->cat_pets as $p)
                                            <div class="groomit-card groomit-card-pet">
                                                <div class="row row-flex align-items-center">
                                                    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 col-flex text-center groomit-card_account_photo">
                                                        <div class="c-photo c-photo_small ">
                                                            <div class="c-photo__mask c-photo__mask_small ">
                                                                @if (!empty($p->photo))
                                                                    <img src="data:image/png;base64,{{ $p->photo }}" class="  c-photo__uploaded-photo" alt="Avatar">
                                                                @else
                                                                    <img src="/images/banner-investors_sm.jpg" class="  c-photo__uploaded-photo" alt="Avatar">
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-7 col-sm-7 col-md-7 col-lg-7 text-center col-flex">
                                                        <div class="text-left">
                                                            <h3><strong>{{ $p->name }} {{ strtoupper($p->type) }}</strong></h3>
                                                            <h3>{{ $p->breed_name }}</h3>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2 text-right col-flex">
                                                        <a class="inline-block" title="Edit" href="javascript:show_pet_cat({{ $p->pet_id }})">
                                                            <i class="far fa-edit edit-i"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                                </div>
                            </div>
                    </form>
                </div>
            </div>
        </div>
</main>

<!-- /main -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-lg-10 col-lg-offset-1">
                <p>&copy; 2020 Groomit - Made with love in NYC.</p>
            </div>
            <!-- /col -->
        </div>
        <!-- /row -->
    </div>
    <!-- /container -->
</footer>

<!-- EDIT DOG MODAL -->
<div class="modal fade" id="modal-dog" tabindex="-1" role="dialog" aria-labelledby="editPetLabeldog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h4 class="modal-title text-center" id="editPetLabeldog">DOG PROFILE</h4>

                    <!-- INIT FORM -->
                    <form action="/user/myaccount/dog_update" role="form" enctype="multipart/form-data"
                          method="post" id="frm_pet" onsubmit="window.location.reload();" class="form-profile-info">
                        {!! csrf_field() !!}
                        <input type="hidden" id="pet_id" name="pet_id" value=""/>
                        <input type="hidden" id="type" name="type" value="dog"/>

                        <fieldset>

                            <!-- PROFILE -->
                            <section id="profile">
                                <div class="container" class="st-opt">
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="pet-photo">
                                                <!-- DEFAULT PROFILE PHOTO -->
                                                <!-- required "demo-photo" class -->
                                                <img id="img_pet_photo" class="demo-photo" src="/desktop/img/dog-icon.svg" width="150">
                                                <!-- CUSTOM PROFILE PHOTO -->
                                                <!-- remove "demo-photo" class -->
                                                <!--<img src="/desktop/img/demo.jpg" width="221" height="225">-->
                                                <input type="file" id="pet_photo" name="pet_photo" style="display:none;" onchange="read_file(this)"/>
                                                <div id="add-photo" onclick="$('#pet_photo').click();"><span
                                                            class="glyphicon glyphicon-camera center-block"
                                                            aria-hidden="true"></span>Add photo
                                                </div>
                                            </div>
                                        </div>
                                        <!-- /col-3 -->
                                        <div class="col-md-8" id="pet-binfo">
                                            <div class="row mb-4">
                                                <div class="col-xs-6">
                                                    <div class="form-group">
                                                    <label class="control-label" for="">Dog's name </label>
                                                        <input class="form-control" name="name" id="name" type="text" maxlenght="100"
                                                               required/>
                                                        <span class="form-highlight"></span> <span
                                                                class="form-bar"></span>
                                                       
                                                    </div>
                                                    <!-- /form-group -->
                                                </div>
                                                <!-- /col-6 -->
                                                <div class="col-xs-6">
                                                    <div class="form-group select-form-group">
                                                    <label class="control-label" for="age">Dog's age </label>
                                                        <select class="form-control" name="age" id="age" required>
                                                            <option value="">Please Select</option>
                                                            <option value="1">1 year or less</option>
                                                            <option value="2">2 years old</option>
                                                            <option value="3">3 years old</option>
                                                            <option value="4">4 years old</option>
                                                            <option value="5">5 years old</option>
                                                            <option value="6">6 years old</option>
                                                            <option value="7">7 years old</option>
                                                            <option value="8">8 years old</option>
                                                            <option value="9">9 years old</option>
                                                            <option value="10">10 years old</option>
                                                            <option value="11">11 years old</option>
                                                            <option value="12">12 years old</option>
                                                            <option value="13">13 years old</option>
                                                            <option value="14">14 years old</option>
                                                            <option value="15">15 years old</option>
                                                            <option value="+15">+15 years old</option>
                                                        </select>
                                                        
                                                    </div>
                                                    <!-- /form-group -->
                                                </div>
                                                <!-- /col-6 -->
                                            </div>
                                            <!-- /row -->

                                            <div class="row">
                                                <div class="col-sm-6 mb-4">

                                                    <p class="control-label">Gender</p>
                                                    <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                        <label class="btn btn-st-opt flex-fill">
                                                            <input type="radio" name="gender" value="F" autocomplete="off" checked>
                                                            <span class="glyphicon glyphicon-ok hidden-xs"
                                                                  aria-hidden="true"></span>Female</label>
                                                        <label class="btn btn-st-opt flex-fill">
                                                            <input type="radio" name="gender" value="M" autocomplete="off">
                                                            <span class="glyphicon glyphicon-ok hidden-xs"
                                                                  aria-hidden="true"></span>Male</label>
                                                    </div>
                                                </div>
                                                <!-- /col-6 -->
                                                <div class="col-sm-6">
                                                    <div class="form-group select-form-group">
                                                    <label class="control-label" for="breed">Dog's breed </label>
                                                        <select class="form-control" name="breed" id="breed" required>
                                                            <option value="">Please Select</option>
                                                            @if (count($breeds) > 0)
                                                                @foreach ($breeds as $o)
                                                                    <option value="{{ $o->breed_id }}">{{ $o->breed_name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                       
                                                    </div>
                                                    <!-- /form-group -->
                                                </div>
                                                <!-- /col-6 -->
                                            </div>
                                            <!-- /row -->

                                        </div>
                                        <!-- /col-10 -->
                                    </div>
                                    <!-- /row -->

                                    <div class="row mb-4">
                                        <div class="col-xs-12">
                                            <p class="control-label" for="">Size &amp; weight </p>
                                            <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="size" value="2" autocomplete="off" checked>
                                                    S (<20lbs)</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="size" value="3" autocomplete="off">
                                                    M (21~40lbs)</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="size" value="4" autocomplete="off">
                                                    L (41~80lbs)</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="size" value="5" autocomplete="off">
                                                    XL (>81lbs)</label>
                                            </div>
                                        </div>
                                        <!-- /col -->
                                    </div>
                                    <!-- /row -->
                                    <div class="row mb-4">
                                        <div class="col-xs-12">
                                            <p class="control-label" for="">Temperament </p>
                                            <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="temperment" value="Friendly" autocomplete="off" checked>
                                                    Friendly</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="temperment" value="Anxious" autocomplete="off">
                                                    Anxious</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="temperment" value="Fatigue" autocomplete="off">
                                                    Fatigue</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="temperment" value="Aggressive" autocomplete="off">
                                                    Aggressive</label>
                                            </div>
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <!-- /row -->

                                    <div class="row">
                                        <div class="col-sm-12">
                                            <p class="control-label">Is your dog up-to-date with rabies vaccination?</p>
                                        </div>
                                        <div class="col-sm-6 mb-3">
                                            <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="vaccinated" value="Y" autocomplete="off" checked>
                                                    Yes</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="vaccinated" value="N" autocomplete="off">
                                                    No</label>
                                            </div>
                                        </div>
                                        <!-- /col -->

                                        <div class="col-sm-6 mb-4">
                                            <div class="form-group">
                                                <input type="file" name="upload_certificate" id="upload_certificate" value="" >
                                                <a href="#/" class="w-100 vaccination_certificate_upload upload_input btn outline-btn rounded-btn groomit-btn table-cell align-middle" role="button" id="login_pg_register_button">
                                                        Upload Certificate
                                                </a> 
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col -->

                                    </div>
                                    <div class="row mb-4">
                                                <div class="col-xs-6">
                                                    <div class="form-group">
                                                        <label class="control-label" for="vet">Vet's name (optional)</label>
                                                        <input class="form-control" name="vet" id="vet" type="text" maxlenght="100"/>
                                                        <span class="form-highlight"></span> <span
                                                                class="form-bar"></span>
                                                    </div>
                                                    <!-- /form-group -->
                                                </div>
                                                <!-- /col -->
                                                <div class="col-xs-6">
                                                    <div class="form-group">
                                                        <label class="control-label" for="vet_phone">Vet's phone (optional)</label>
                                                        <input class="form-control" name="vet_phone" id="vet_phone" type="text" maxlenght="30"/>
                                                        <span class="form-highlight"></span> <span
                                                                class="form-bar"></span>
                                                    </div>
                                                    <!-- /form-group -->
                                                </div>
                                                <!-- /col -->
                                            </div>
                                            <!-- /row -->
                                      

                                    <div class="row mb-4">
                                        <div class="col-xs-12">
                                            <p class="control-label" for="last_groom">Last groom </p>
                                            <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="last_groom" autocomplete="off" checked value="< 6 weeks">
                                                    <6 weeks</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="last_groom" value="<6 months" autocomplete="off">
                                                    <6 months</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="last_groom" value=">6 months" autocomplete="off">
                                                    >6 months</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="last_groom" value="Never" autocomplete="off">
                                                    Never</label>
                                            </div>
                                        </div>
                                        <!-- /col -->
                                    </div>
                                    <!-- /row -->
                                    <div class="row mb-4">
                                        <div class="col-xs-12">
                                            <p class="control-label" for="coat_type">Coat type </p>
                                            <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="coat_type" value="Silky" autocomplete="off" checked>
                                                    Silky</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="coat_type" value="Wiry" autocomplete="off">
                                                    Wiry</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="coat_type" value="Double Coat" autocomplete="off">
                                                    Double Coat</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="coat_type" value="Curly" autocomplete="off">
                                                    Curly</label>
                                            </div>
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <!-- /row -->

                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="form-group">
                                                <label class="control-label" for="">Notes to groomer (optional)</label>
                                                <textarea class="form-control" name="special_note" id="special_note" maxlength="250" rows="4"
                                                          placeholder="Allergies, Health Conditions, etc."></textarea>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                            </div>
                                            <!-- /form-group --> </div>

                                    </div>
                                    <!-- /row -->
                                </div>
                                <!-- /container -->
                            </section>
                            <!-- /other-addons -->

                            <section id="next-btn">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-xs-6 text-right" id="btn_cancel" >
                                            <button type="button" class="groomit-btn rounded-btn black-btn space-btn" data-dismiss="modal" aria-label="Close">
                                                CANCEL
                                            </button>
                                        </div>
                                        <div class="col-xs-6 text-left">
                                            <button type="submit" class="groomit-btn rounded-btn red-btn " id="dog_btn_submit" onclick="dog_submit()" >
                                                SUBMIT
                                            </button>
                                        </div>
                                    </div>
                                    <!-- row -->
                                </div>
                                <!-- container -->
                            </section>
                        </fieldset>
                    </form>


                    <!-- /FORM -->


                </div>
                <!-- /modal-body -->

            </div>
            <!-- /modal-content -->

        </div>
    </div>
    <!-- /edit dog Modal -->

<!-- EDIT cat -->
<div class="modal fade" id="modal-cat" tabindex="-1" role="dialog" aria-labelledby="editPetLabelcat">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h4 class="modal-title text-center" id="editPetLabelcat">CAT PROFILE</h4>

                    <!-- INIT FORM -->
                    <form action="/user/myaccount/cat_update" enctype="multipart/form-data" role="form"
                          method="post" id="frm_pet_cat" onsubmit="window.location.reload();" class="form-profile-info">
                        <input type="hidden" id="cat_pet_id" name="cat_pet_id"/>
                        <input type="hidden" id="type" name="type" value="cat"/>
                        {!! csrf_field() !!}
                        <fieldset>

                            <!-- PROFILE -->
                            <section id="profile">
                                <div class="container" class="st-opt">
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="pet-photo">
                                                <!-- DEFAULT PROFILE PHOTO -->
                                                <!-- required "demo-photo" class -->
                                                <img id="img_pet_photo_cat" class="demo-photo" src="/desktop/img/cat-icon.svg" width="150">
                                                <!-- CUSTOM PROFILE PHOTO -->
                                                <!-- remove "demo-photo" class -->
                                                <!--<img src="/desktop/img/demo.jpg" width="221" height="225">-->
                                                <input type="file" id="cat_pet_photo" name="cat_pet_photo" style="display:none;" onchange="cat_read_file(this)"/>
                                                <div id="add-photo" onclick="$('#cat_pet_photo').click();"><span
                                                            class="glyphicon glyphicon-camera center-block"
                                                            aria-hidden="true"></span>Add photo
                                                </div>
                                            </div>
                                        </div>
                                        <!-- /col-3 -->
                                        <div class="col-md-8" id="pet-binfo">
                                            <div class="row mb-4">
                                                <div class="col-xs-6">
                                                    <div class="form-group">
                                                        <label class="control-label" for="">Cat's Name </label>
                                                        <input class="form-control" name="cat_name" id="cat_name" type="text" maxlenght="100" required />
                                                        <span class="form-highlight"></span> <span class="form-bar"></span>
                                                    </div>
                                                    <!-- /form-group -->
                                                </div>
                                                <!-- /col-6 -->
                                                <div class="col-xs-6">
                                                    <div class="form-group select-form-group">
                                                        <label class="control-label" for="">Cat's Age</label>
                                                        <select class="form-control" name="cat_age" id="cat_age" required>
                                                            <option value="">Please Select</option>
                                                            <option value="1">1 year or less</option>
                                                            <option value="2">2 years old</option>
                                                            <option value="3">3 years old</option>
                                                            <option value="4">4 years old</option>
                                                            <option value="5">5 years old</option>
                                                            <option value="6">6 years old</option>
                                                            <option value="7">7 years old</option>
                                                            <option value="8">8 years old</option>
                                                            <option value="9">9 years old</option>
                                                            <option value="10">10 years old</option>
                                                            <option value="11">11 years old</option>
                                                            <option value="12">12 years old</option>
                                                            <option value="13">13 years old</option>
                                                            <option value="14">14 years old</option>
                                                            <option value="15">15 years old</option>
                                                            <option value="+15">+15 years old</option>
                                                        </select>
                                                    </div>
                                                    <!-- /form-group -->
                                                </div>
                                                <!-- /col-6 -->
                                            </div>
                                            <!-- /row -->

                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <p class="control-label">Gender </p>
                                                    <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                        <label class="btn btn-st-opt flex-fill">
                                                            <input type="radio" name="cat_gender" autocomplete="off" checked value="F">
                                                            Female</label>
                                                        <label class="btn btn-st-opt flex-fill">
                                                            <input type="radio" name="cat_gender" autocomplete="off" value="M">
                                                            Male</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /row -->

                                        </div>
                                        <!-- /col-10 -->
                                    </div>
                                    <!-- /row -->
                                    <div class="row mb-4">
                                        <div class="col-xs-12">
                                            <p class="control-label" for="">Temperament </p>
                                            <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="cat_temperment" autocomplete="off" checked value="Friendly">
                                                    Friendly</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="cat_temperment" autocomplete="off" value="Anxious">
                                                    Anxious</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="cat_temperment" autocomplete="off" value="Fatigue">
                                                    Fatigue</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="cat_temperment" autocomplete="off" value="Aggressive">
                                                    Aggressive</label>
                                            </div>
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <!-- /row -->

                                    <div class="row">
                                        <div class="col-sm-6 mb-4">
                                            <p class="control-label">Vaccinated? </p>
                                            <div class="btn-group btn-group-justified btn-group--new" data-toggle="buttons">
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="cat_vaccinated" autocomplete="off" checked value="Y">
                                                    Yes</label>
                                                <label class="btn btn-st-opt flex-fill">
                                                    <input type="radio" name="cat_vaccinated" autocomplete="off" value="N">
                                                    No</label>
                                            </div>
                                        </div>
                                        <!-- /col -->
                                        <div class="col-sm-6 mb-4">
                                                    <div class="form-group">
                                                        <label class="control-label" for="">Vaccination Certificate</label><br>
                                                        <input type="file" class="upload_certificate" name="upload_certificate_cat" id="upload_certificate_cat" value="Upload" >
                                                        <a href="#/" class="w-100 vaccination_cat_certificate_upload upload_input btn outline-btn rounded-btn groomit-btn table-cell align-middle" role="button" id="login_pg_register_button">
                                                                Upload 
                                                        </a> 
                                                    </div>
                                                    <!-- /form-group -->
                                        </div>
                                        <!-- /col -->
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-xs-6">
                                                    <div class="form-group">
                                                        <label class="control-label" for="">Vet's Name (optional)</label>
                                                        <input class="form-control" name="cat_vet" id="cat_vet" type="text" maxlenght="100"  />
                                                        <span class="form-highlight"></span> <span class="form-bar"></span>
                                                    </div>
                                                    <!-- /form-group -->
                                        </div>
                                        <!-- /col -->
                                        <div class="col-xs-6">
                                                    <div class="form-group">
                                                        <label class="control-label" for="">Vet's Phone (optional)</label>
                                                        <input class="form-control" name="cat_vet_phone" id="cat_vet_phone" type="text" maxlenght="10"  />
                                                        <span class="form-highlight"></span> <span class="form-bar"></span>
                                                    </div>
                                                    <!-- /form-group -->
                                        </div>
                                        <!-- /col -->                                        
                                    </div>
                                    <!-- /row -->

                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="form-group">
                                                <label class="control-label" for="">Notes to groomer (optional)</label>
                                                <textarea class="form-control" name="cat_special_note" id="cat_special_note" rows="4" placeholder="Allergies, Health Condition, Etc."></textarea>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                            </div>
                                            <!-- /form-group --> </div>

                                    </div>
                                    <!-- /row -->
                                </div>
                                <!-- /container -->
                            </section>
                            <!-- /other-addons -->

                            <section id="next-btn">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-xs-6 text-right" id="btn_cancel">
                                            <button type="button" class="groomit-btn rounded-btn black-btn space-btn" data-dismiss="modal" aria-label="Close">
                                                CANCEL
                                            </button>
                                        </div>
                                        <div class="col-xs-6 text-left">
                                            <button type="submit" class="groomit-btn rounded-btn red-btn " id="cat_btn_submit" onclick="cat_submit()">
                                                SUBMIT
                                            </button>
                                        </div>
                                    </div>
                                    <!-- row -->
                                </div>
                                <!-- container -->
                            </section>
                        </fieldset>
                    </form>


                    <!-- /FORM -->


                </div>
                <!-- /modal-body -->

            </div>
            <!-- /modal-content -->

        </div>
    </div>
    <!-- /editCatModal -->


<!-- Modal ADDRESS -->
<div class="modal fade" id="modal-address" tabindex="-1" role="dialog" aria-labelledby="editAddressLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h4 class="modal-title text-center" id="editAddressLabel">ADDRESS</h4>

                    <!-- INIT FORM -->
                    <form action="" class="" role="form" method="post" id="addressForm">
                        <input type="hidden" name="s_address_id" id="s_address_id" value=""/>
                        <fieldset>

                            <section id="address">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="control-label" for="">Address 1</label>
                                                <input class="form-control" name="s_address1" id="s_address1" type="text" maxlenght="100"
                                                       required/>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- col-6 -->
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="control-label" for="">Address 2</label>
                                                <input class="form-control" name="s_address2" id="s_address2" type="text" maxlenght="100"
                                                       required/>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col-6 -->
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="control-label" for="">City</label>
                                                <input class="form-control" name="s_city" id="s_city" type="text" maxlenght="100"
                                                       required/>
                                                <span class="form-highlight"></span> <span class="form-bar"></span>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- col-6 -->
                                        <div class="col-sm-3">
                                            <div class="form-group select-form-group">
                                                <label class="control-label" for="">State</label>
                                                <select class="form-control" name="s_state" id="s_state" required>
                                                    <option value="">Please Select</option>
                                                    @if (count($states) > 0)
                                                        @foreach ($states as $o)
                                                            <option value="{{ $o->code }}">{{ $o->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <!-- /form-group -->
                                        </div>
                                        <!-- /col-6 -->

                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label class="control-label" for="">Zip Code</label>
                                                <input class="form-control" name="s_zip" id="s_zip" type="text" maxlength="11"
                                                       required/>
                                                <span class="form-highlight"></span> <span
                                                            class="form-bar"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <section id="confirm-payment">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-xs-6 text-right">
                                            <button type="button" class="groomit-btn black-btn rounded-btn" data-dismiss="modal">
                                                CANCEL
                                            </button>
                                        </div>
                                        <div class="col-xs-6 text-left">
                                            <button type="button" id="address_submit_btn" class="groomit-btn red-btn rounded-btn" onclick="address_submit()">
                                                SUBMIT
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </fieldset>
                    </form>

                    <!-- /FORM -->

                </div>
                <!-- /modal-body -->

            </div>
            <!-- /modal-content -->

        </div>
    </div>
<!-- /Modal ADDRESS -->


<!-- Crop Tool -->
<div class="modal fade" id="crop-modal" tabindex="-1" role="dialog" aria-labelledby="crop-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body text-center upload-demo">
                <h3 class="main__title" id="crop-modal-label">Crop</h3>

                <div class="upload-demo-wrap mb-5">
                    <div id="upload-demo"></div>
                </div>
                <div class="pt-5 mb-5">
                    <button type="button" role="button" class="groomit-btn black-btn outline-btn long-btn rounded-btn type-submit mr-3 mb-3 mb-sm-0" id="cancel">Cancel</button>
                    <button type="button" role="button" class="groomit-btn red-btn rounded-btn long-btn type-submit" id="upload-result">Set as profile photo</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /MODAL-->

<iframe id="ifm_upload" name="ifm_upload" style="display:none;"></iframe>

    <script>

        function dog_submit(){
            $('#dog_btn_submit').attr('disabled', true);
            $('#frm_pet').submit();
        }

        function cat_submit(){
            $('#cat_btn_submit').attr('disabled', true);
            $('#frm_pet_cat').submit();
        }

        $("body").on('click', '.vaccination_certificate_upload', function() {
            $('#upload_certificate').trigger('click');
        });

        $("body").on('click', '.vaccination_cat_certificate_upload', function() {
            $('#upload_certificate_cat').trigger('click');
        });

        function remove_favorite(groomer_id) {
            var target = $('.fav-groomer-info-' + groomer_id);
            var add_to_favorite = target.hasClass('glyphicon-heart') ? 'N' : 'Y';

            if (add_to_favorite === 'N') {
                target.removeClass('glyphicon-heart').addClass('glyphicon-heart-empty');
            }else {
                target.removeClass('glyphicon-heart-empty').addClass('glyphicon-heart');
            }

            $.ajax({
                url: '/user/mygroomer/remove-favorite',
                data: {
                    _token: '{!! csrf_token() !!}',
                    groomer_id: groomer_id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {

                        window.location.href = "/user/myaccount";

                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function address_submit() {

            $('#address_submit_btn').attr('disabled', true);

            $.ajax({
                url: '/user/myaccount/address_update',
                data: {
                    _token: '{!! csrf_token() !!}',
                    address_id: $('#s_address_id').val(),
                    address1: $('#s_address1').val(),
                    address2: $('#s_address2').val(),
                    city: $('#s_city').val(),
                    state: $('#s_state').val(),
                    zip: $('#s_zip').val(),
                    special_note: $('#cat_special_note').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        myApp.showSuccess('Your request has been processed successful!', function() {
                            window.location.reload();
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }


        function show_address(address_id) {

            $.ajax({
                url: '/user/address/load',
                data: {
                    _token: '{!! csrf_token() !!}',
                    address_id: address_id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {

                        var o = res.data;
                        if(o.address1.length > 0) {
                            $('#s_address1').val(o.address1);
                        }
                        $('#s_address2').val(o.address2);
                        if(o.city.length > 0) {
                            $('#s_city').val(o.city);
                        }
                        if(o.state.length > 0) {
                            $('#s_state').val(o.state);
                        }
                        $('#s_zip').val(o.zip);
                        $('#s_address_id').val(o.address_id);

                        $('#modal-address').modal();
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });

        }


        {{--function cat_submit() {--}}

        {{--    $.ajax({--}}
        {{--        url: '/user/myaccount/cat_update',--}}
        {{--        data: {--}}
        {{--            _token: '{!! csrf_token() !!}',--}}
        {{--            pet_id: $('#cat_pet_id').val(),--}}
        {{--            name: $('#cat_name').val(),--}}
        {{--            age: $('#cat_age').val(),--}}
        {{--            gender: $("input[name='cat_gender']:checked").val(),--}}
        {{--            temperment: $("input[name='cat_temperment']:checked").val(),--}}
        {{--            vaccinated: $("input[name='cat_vaccinated']:checked").val(),--}}
        {{--            vet: $('#cat_vet').val(),--}}
        {{--            vet_phone: $('#cat_vet_phone').val(),--}}
        {{--            last_groom: $("input[name='cat_last_groom']:checked").val(),--}}
        {{--            coat_type: $("input[name='cat_coat_type']:checked").val(),--}}
        {{--            special_note: $('#cat_special_note').val()--}}
        {{--        },--}}
        {{--        cache: false,--}}
        {{--        type: 'post',--}}
        {{--        dataType: 'json',--}}
        {{--        success: function (res) {--}}
        {{--            myApp.hideLoading();--}}
        {{--            if ($.trim(res.msg) === '') {--}}
        {{--                myApp.showSuccess('Your request has been processed successful!', function() {--}}
        {{--                    window.location.reload();--}}
        {{--                });--}}
        {{--            } else {--}}
        {{--                myApp.showError(res.msg);--}}
        {{--            }--}}
        {{--        }--}}
        {{--    });--}}
        {{--}--}}



        {{--function dog_submit() {--}}

        {{--    $.ajax({--}}
        {{--        url: '/user/myaccount/dog_update',--}}
        {{--        data: {--}}
        {{--            _token: '{!! csrf_token() !!}',--}}
        {{--            pet_id: $('#pet_id').val(),--}}
        {{--            pet_photo: $('#pet_photo'),--}}
        {{--            name: $('#name').val(),--}}
        {{--            age: $('#age').val(),--}}
        {{--            gender: $("input[name='gender']:checked").val(),--}}
        {{--            breed: $('#breed').val(),--}}
        {{--            size: $("input[name='size']:checked").val(),--}}
        {{--            temperment: $("input[name='temperment']:checked").val(),--}}
        {{--            vaccinated: $("input[name='vaccinated']:checked").val(),--}}
        {{--            upload_certificate: $('#upload_certificate').val(),--}}
        {{--            vet: $('#vet').val(),--}}
        {{--            vet_phone: $('#vet_phone').val(),--}}
        {{--            last_groom: $("input[name='last_groom']:checked").val(),--}}
        {{--            coat_type: $("input[name='coat_type']:checked").val(),--}}
        {{--            special_note: $('#special_note').val()--}}
        {{--        },--}}
        {{--        cache: false,--}}
        {{--        type: 'post',--}}
        {{--        dataType: 'json',--}}
        {{--        success: function (res) {--}}
        {{--            myApp.hideLoading();--}}
        {{--            if ($.trim(res.msg) === '') {--}}
        {{--                myApp.showSuccess('Your request has been processed successful!', function() {--}}
        {{--                    window.location.reload();--}}
        {{--                });--}}
        {{--            } else {--}}
        {{--                myApp.showError(res.msg);--}}
        {{--            }--}}
        {{--        }--}}
        {{--    });--}}
        {{--}--}}



        function show_pet_dog(pet_id) {

            $.ajax({
                url: '/user/pet/load',
                data: {
                    _token: '{!! csrf_token() !!}',
                    pet_id: pet_id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {

                        var o = res.data;
                        if (o.photo) {
                            $('#img_pet_photo').prop('src', 'data:image/png;base64,' + o.photo);
                            $('#img_pet_photo').removeClass("demo-photo");
                        } else {
                            $('#img_pet_photo').prop('src', '/desktop/img/dog-icon.svg');
                            $('#img_pet_photo').addClass("demo-photo");
                        }
                        $('#name').val(o.name);
                        $('#age').val(o.age);

                        $('[name="gender"][value="' + o.gender + '"]').prop('checked', true);
                        $('[name="gender"][value!="' + o.gender + '"]').parent().removeClass('active');
                        $('[name="gender"][value="' + o.gender + '"]').parent().addClass('active');

                        $('#breed').val(o.breed);

                        $('[name="size"][value="' + o.size + '"]').prop('checked', true);
                        $('[name="size"][value!="' + o.size + '"]').parent().removeClass('active');
                        $('[name="size"][value="' + o.size + '"]').parent().addClass('active');

                        $('[name="temperment"][value="' + o.temperament + '"]').prop('checked', true);
                        $('[name="temperment"][value!="' + o.temperament + '"]').parent().removeClass('active');
                        $('[name="temperment"][value="' + o.temperament + '"]').parent().addClass('active');

                        $('[name="vaccinated"][value="' + o.vaccinated + '"]').prop('checked', true);
                        $('[name="vaccinated"][value!="' + o.vaccinated + '"]').parent().removeClass('active');
                        $('[name="vaccinated"][value="' + o.vaccinated + '"]').parent().addClass('active');

                        $('#vet').val(o.vet);
                        $('#vet_phone').val(o.vet_phone);

                        $('[name="last_groom"][value="' + o.last_groom + '"]').prop('checked', true);
                        $('[name="last_groom"][value!="' + o.last_groom + '"]').parent().removeClass('active');
                        $('[name="last_groom"][value="' + o.last_groom + '"]').parent().addClass('active');

                        $('[name="coat_type"][value="' + o.coat_type + '"]').prop('checked', true);
                        $('[name="coat_type"][value!="' + o.coat_type + '"]').parent().removeClass('active');
                        $('[name="coat_type"][value="' + o.coat_type + '"]').parent().addClass('active');

                        $('#special_note').val(o.special_note);
                        $('#pet_id').val(o.pet_id);

                        // change_mode('view');

                        $('#modal-dog').modal();
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function show_pet_cat(pet_id) {
            $.ajax({
                url: '/user/pet/load',
                data: {
                    _token: '{!! csrf_token() !!}',
                    pet_id: pet_id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {

                        var o = res.data;
                        if (o.photo) {
                            $('#img_pet_photo_cat').prop('src', 'data:image/png;base64,' + o.photo);
                            $('#img_pet_photo_cat').removeClass("demo-photo");
                        } else {
                            $('#img_pet_photo').prop('src', '/desktop/img/cat-icon.svg');
                            $('#img_pet_photo').addClass("demo-photo");
                        }
                        $('#cat_name').val(o.name);
                        $('#cat_age').val(o.age);

                        $('[name="cat_gender"][value="' + o.gender + '"]').prop('checked', true);
                        $('[name="cat_gender"][value!="' + o.gender + '"]').parent().removeClass('active');
                        $('[name="cat_gender"][value="' + o.gender + '"]').parent().addClass('active');

                        $('#cat_breed').val(o.breed);

                        $('[name="cat_size"][value="' + o.size + '"]').prop('checked', true);
                        $('[name="cat_size"][value!="' + o.size + '"]').parent().removeClass('active');
                        $('[name="cat_size"][value="' + o.size + '"]').parent().addClass('active');

                        $('[name="cat_temperment"][value="' + o.temperament + '"]').prop('checked', true);
                        $('[name="cat_temperment"][value!="' + o.temperament + '"]').parent().removeClass('active');
                        $('[name="cat_temperment"][value="' + o.temperament + '"]').parent().addClass('active');

                        $('[name="cat_vaccinated"][value="' + o.vaccinated + '"]').prop('checked', true);
                        $('[name="cat_vaccinated"][value!="' + o.vaccinated + '"]').parent().removeClass('active');
                        $('[name="cat_vaccinated"][value="' + o.vaccinated + '"]').parent().addClass('active');

                        $('#cat_vet').val(o.vet);
                        $('#cat_vet_phone').val(o.vet_phone);

                        $('[name="cat_last_groom"][value="' + o.last_groom + '"]').prop('checked', true);
                        $('[name="cat_last_groom"][value!="' + o.last_groom + '"]').parent().removeClass('active');
                        $('[name="cat_last_groom"][value="' + o.last_groom + '"]').parent().addClass('active');

                        $('[name="cat_coat_type"][value="' + o.coat_type + '"]').prop('checked', true);
                        $('[name="cat_coat_type"][value!="' + o.coat_type + '"]').parent().removeClass('active');
                        $('[name="cat_coat_type"][value="' + o.coat_type + '"]').parent().addClass('active');

                        $('#cat_special_note').val(o.special_note);
                        $('#cat_pet_id').val(o.pet_id);

                        // change_mode('view');

                        $('#modal-cat').modal();
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }


        var current_billing_id = null;

        function show_card(billing_id) {

            current_billing_id = billing_id;

            $('#same_address').prop('checked', false);

            myApp.showLoading();
            $.ajax({
                url: '/user/payment/load',
                data: {
                    _token: '{!! csrf_token() !!}',
                    billing_id: billing_id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {

                        var o = res.data;

                        $('#card_holder').val(o.card_holder);
                        $('#card_number').val(o.card_number);
                        $('#expire_mm').val(o.expire_mm);
                        $('#expire_yy').val(o.expire_yy);
                        $('#cvv').val(o.cvv);
                        $('#address1').val(o.address1);
                        $('#address2').val(o.address2);
                        $('#city').val(o.city);
                        $('#state').val(o.state);
                        $('#zip').val(o.zip);
                        $('#default_card').prop('checked', o.default_card === 'Y');

                        $('#billing_id').val(billing_id);

                        $('#modal-card').modal();

                    } else {
                        myApp.showError(res.msg);
                    }
                }
            })
        }

        function get_service_address() {

            if($("#same_address").prop('checked')==false) {
                $('#address1').val('');
                $('#address2').val('');
                $('#city').val('');
                $('#state').val('');
                $('#zip').val('');
            } else {
                $.ajax({
                    url: '/user/payment/get_service_address',
                    data: {
                        _token: '{!! csrf_token() !!}'
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function(res) {
                        myApp.hideLoading();
                        if ($.trim(res.msg) === '') {

                            var o = res.data;

                            $('#address1').val(o.address1);
                            $('#address2').val(o.address2);
                            $('#city').val(o.city);
                            $('#state').val(o.state);
                            $('#zip').val(o.zip);
                        } else {
                            $('#address1').val('');
                            $('#address2').val('');
                            $('#city').val('');
                            $('#state').val('');
                            $('#zip').val('');
                        }
                    }
                });
            }
        }

        function save_card() {

            $('#save_card_btn').attr('disabled', true);

            myApp.showLoading();
            $.ajax({
                url: '/user/payment/update',
                data: {
                    _token: '{!! csrf_token() !!}',
                    billing_id: current_billing_id,
                    card_holder: $('#card_holder').val(),
                    card_number: $('#card_number').inputmask('unmaskedvalue'),
                    expire_mm: $('#expire_mm').val(),
                    expire_yy: $('#expire_yy').val(),
                    cvv: $('#cvv').val(),
                    address1: $('#address1').val(),
                    address2: $('#address2').val(),
                    city: $('#city').val(),
                    state: $('#state').val(),
                    zip: $('#zip').val(),
                    default_card: $('#default_card').is(':checked') ? 'Y' : 'N'
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        myApp.showSuccess('Thank you, your request has been processed successfully!', function() {
                            window.location.reload();
                        });
                    } else {
                        $('#save_card_btn').attr('disabled', false);
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function delete_card() {

            $('#delete_card_link').hide();

            var b_id = $('#billing_id').val();

            myApp.showLoading();
            $.ajax({
                url: '/user/payment/delete_card',
                data: {
                    _token: '{!! csrf_token() !!}',
                    billing_id: b_id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        myApp.showSuccess('Thank you, your request has been processed successfully!', function() {
                            window.location.reload();
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function read_file(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#img_pet_photo').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function cat_read_file(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#img_pet_photo_cat').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

    </script>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBQGtikp7nu9OJqF2Ogds59SsilNlPYLTw&libraries=places&callback=initMap"
            async defer>
    </script>


    <script type="text/javascript">
        var onload_func = window.onload;
        window.onload = function() {
            if (onload_func) {
                onload_func();
            }
        }

        function fitImage(image) {
            const aspectRatio = image.naturalWidth / image.naturalHeight;
            // If image is portrait
            if (aspectRatio < 1) {
                image.style.width = '100%';
                image.style.height = 'auto';
                image.style.maxHeight = 'none';
                image.style.maxWidth = '100%';
            }
            // If image is landscape
            else if (aspectRatio > 1) {
                image.style.width = 'auto';
                image.style.height = '100%';
                image.style.maxHeight = '100%';
                image.style.maxWidth = 'none';
            }
            // Otherwise, image is square
            else {
                image.style.width = '100%';
                image.style.height = 'auto';
                image.style.maxHeight = 'none';
                image.style.maxWidth = '100%';
            }
        }

        //Change images CSS according to aspect (groomer profile pic / grooming photos)
        const images = document.querySelectorAll('img.c-photo__uploaded-photo');
        Array.from(images).forEach(image => {
            image.addEventListener('load', () => fitImage(image));
            if (image.complete && image.naturalWidth !== 0)
            fitImage(image);
        });
    </script>

    <script>

    function initMap() {
        var input = document.getElementById('s_address1');
        var options = {
            componentRestrictions: {country: "us"}
        };
        var autocomplete = new google.maps.places.Autocomplete(input, options);
        autocomplete.setFields(['address_components', 'geometry', 'icon', 'name']);
        autocomplete.addListener('place_changed', function() {
            var place = autocomplete.getPlace();
            if (!place.geometry) {
                window.alert("No details available for input: '" + place.name + "'");
                return;
            }

            var address1  = '';
            var city      = '';
            var state     = '';
            var zip       = '';

            var street_number = '';
            var route         = '';
            var locality      = '';
            var administrative_area_level_1 = '';
            var postal_code   = '';

            for (var i = 0; i < place.address_components.length; i++) {
                if (place.address_components[i].types[0] == "street_number") {
                    street_number = place.address_components[i].short_name;
                }
                if (place.address_components[i].types[0] == "route") {
                    route = place.address_components[i].short_name;
                }
                if (place.address_components[i].types[0] == "locality") {
                    locality = place.address_components[i].short_name;
                }
                if (place.address_components[i].types[0] == "administrative_area_level_1") {
                    administrative_area_level_1 = place.address_components[i].short_name;
                }
                if (place.address_components[i].types[0] == "postal_code") {
                    postal_code = place.address_components[i].short_name;
                }
            }

            var address1  = street_number + " " + route;
            var city      = locality;
            var state     = administrative_area_level_1;
            var zip       = postal_code;

            // alert(address1 + city + state + zip);

            $('#s_address1').val(address1);
            $('#s_city').val(city);
            $('#s_state').val(state);
            $('#s_zip').val(zip);

        });
    }
    </script>
    <style>
        .pac-container {
            z-index: 10000 !important;
        }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBQGtikp7nu9OJqF2Ogds59SsilNlPYLTw&libraries=places&callback=initMap"
            async defer>
    </script>

    @if ($internal == 'Y' )
    <script>
        dataLayer.push({'internal_staff': 'Y'});
    </script>
    @endif


@stop