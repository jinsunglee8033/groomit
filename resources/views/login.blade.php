@extends('includes.default')

@section('content')
    <section id="banner" class="log-out"> <!-- #banner starts -->
        <div class="container">
            <div class="col-lg-12 top col-md-12 text-center col-sm-12 col-xs-12">
                <img src="images/groomit_text.png" alt="" />
            </div>
        </div>
    </section> <!-- #banner ends -->
<section id="benefits" class="log-out"> <!-- #benefits starts -->
    <div class="container">
        <div class="col-lg-8 col-lg-offset-2 text-center col-sm-8 col-sm-offset-2 col-xs-8 col-xs-offset-2">

            <h2>SIGN UP</h2>


            <form>
                <div class="field">
                    <div class="col-lg-12 normal-first">
                        <input type="email" placeholder="Email"/>
                    </div>
                </div>

                <div class="field">
                    <div class="col-lg-12 normal-first">
                        <input type="password" placeholder="Password"/>
                    </div>
                </div>

                <div class="field">
                    <div class="col-lg-12  text-center normal-first">
                        <input type="submit" class="signUpSubmit" value="Submit"/>
                    </div>
                </div>
            </form>



        </div>

    </div>
    </div>

</section>
@stop