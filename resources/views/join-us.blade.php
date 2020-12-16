@extends('includes.default')

@section('content')
<section id="banner" class="join"> <!-- #banner starts -->
    <div class="container">
        <div class="col-lg-7 top col-md-6 col-sm-6 col-xs-12">
            <img src="images/groomit_text.png" alt="" />
            <h2>Are you a Pet Grommer?</h2>
            <p>Join us to be part of the team!</p>
        </div>
        <div class="col-lg-5 col-md-6 col-sm-6 col-xs-12">
            <div class="join-us" style="display:none;">
                <form class="text-center">
                    <h3>Join Us</h3>
                    <a href="#" class="btn-success">Connect with Facebok</a>
                    <input type="email" placeholder="Email"/>
                    <input type="password" placeholder="Password"/>
                    <p>By creating an account, you agree to our <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a>, and to receive marketing communications from Groomit.</p>
                    <input type="submit" value="Apply"/>
                    <h5>Already have an account? <a href="#">Log In</a></h5>
                    <div class="CloseBtn">

                    </div>
                </form>
            </div>
        </div>
    </div>
</section> <!-- #banner ends -->
<section id="benefits" class="join"> <!-- #benefits starts -->
    <div class="container">
        <div class="col-lg-6 col-sm-5 text-center">
            <img src="images/image_iphone.png" alt="Image Iphone" />
        </div>
        <div class="col-lg-6 col-sm-7">
            <h2>Benefits</h2>
            <h3>Benefits to work with us</h3>
            <img src="images/border-img.jpg" alt=""/>
            <ul>
                <li>- Flexibility of hours</li>
                <li>- Insurance</li>
                <li>- Marketing through GroomIt</li>
                <li>- Make more money with less headache</li>
                <li>- Discounts on grooming products</li>
                <li>- Keep 100% of tips</li>
                <li>- Training & Groomit Certificate</li>
                <li>- Groomit Support Team</li>
                <li>- We bring customers to you</li>
                <li>- Discount on wireless service</li>
            </ul>
        </div>
    </div>
</section> <!-- #benefits ends -->
<section id="downloadapp" class="join"> <!-- #downloadapp starts -->
    <div class="container">
        <div class="col-lg-12 text-center">
            <h2>What do you need to start</h2>
        </div>
    </div>
    <div class="container-fluid">
        <div class="col-lg-4 col-sm-4 text-center col-md-4 col-xs-12">
            <img src="images/img-2.png" alt="" />
            <h3>Love for Animals</h3>
        </div>
        <div class="col-lg-4 col-sm-4 text-center col-md-4 col-xs-12">
            <img src="images/img-1.png" alt="" />
            <h3>Experience with grooming</h3>
        </div>
        <div class="col-lg-4 col-sm-4 text-center col-md-4 col-xs-12">
            <img src="images/img-3.png" alt="" />
            <h3>Responsible</h3>
        </div>
    </div>
</section> <!-- #downloadapp ends -->
<section id="contactus" class="join"> <!-- #contactus starts -->
    <div class="container">
        <div class="col-lg-5 col-sm-6 col-xs-12 text-right">
            <img src="images/faq-img.png" alt=""/>
        </div>
        <div class="col-lg-6 col-sm-6 col-lg-offset-1 col-xs-12">
            <h3>FAQ’s</h3>
            <p>If you have any question go to our Faq’s we have your answer. Also you can contact us.</p>
        </div>
    </div>
</section> <!-- #contactus ends -->
@stop
