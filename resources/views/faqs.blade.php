@extends('includes.default')

@section('content')
<section id="banner" class="bannerFaqs" title="Dog grooming questions"> <!-- #banner starts -->
    <div class="container table-cell">
        <div class="col-lg-12 top col-md-12 text-center col-sm-12 col-xs-12">
            <img src="images/groomit_text.png" alt="" />
            <p class="bannerInP">FAQ's</p>
        </div>
    </div>
</section> <!-- #banner ends -->
<section id="benefits" class="log-out faqsAccordion"> <!-- #benefits starts -->

    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                <div class="panel-group" id="accordion">

                    <h2 class="text-center">Frequently Asked Questions</h2>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title faqTitle">
                                <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse00"  aria-expanded="true" aria-controls="collapse00">How to schedule within Groomit App</a>
                            </h4>
                        </div>
                        <div id="collapse00" class="panel-collapse collapse in">
                            <div class="panel-body">
                                <div class="col-lg-12 text-center">
                                    <iframe width="560" height="315" src="https://www.youtube.com/embed/oD7kiYrmWr0?rel=0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title faqTitle">
                                <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse01" aria-controls="collapse01">What vaccinations do we require?</a>
                            </h4>
                        </div>
                        <div id="collapse01" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="col-lg-12">
                                    <p>We require all dogs to be current on their vaccinations especially Rabies and Distemper vaccines. All necessary vaccinations must be administered at least 24 hours prior to your scheduled appointment.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title faqTitle">
                                <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse02"  aria-expanded="true" aria-controls="collapse02">How much more does GroomIt charge compared to a grooming shop?</a>
                            </h4>
                        </div>
                        <div id="collapse02" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="col-lg-12">
                                <p>We offer competitive pricing and charge between 10-25% more for all in-home grooming services. </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title faqTitle">
                                <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse03"  aria-expanded="true" aria-controls="collapse03">What products do we use to bathe your dog?</a>
                            </h4>
                        </div>
                        <div id="collapse03" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="col-lg-12">
                                <p>We only use all natural and hypoallergenic products.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title faqTitle">
                                <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse04"  aria-expanded="true" aria-controls="collapse04">How long does it take to groom my dog?</a>
                            </h4>
                        </div>
                        <div id="collapse04" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="col-lg-12">
                                <p>On average, we allocate about an hour and a half to groom your dog. However, the size, condition and temperament of your dog will ultimately determine your dog's grooming time.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title faqTitle">
                                <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse05"  aria-expanded="true" aria-controls="collapse05">How quickly will my groomer arrive?</a>
                            </h4>
                        </div>
                        <div id="collapse05" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="col-lg-12">
                                <p>We will always do our best to meet your requested timeframe. We give our client's an hour within which the groomer will arrive. Appointments can be made the same day you call.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title faqTitle">
                                <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse06"  aria-expanded="true" aria-controls="collapse06">Where will the grooming take place?</a>
                            </h4>
                        </div>
                        <div id="collapse06" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="col-lg-12">
                                <p>Your dog will be groomed in the comfort and convenience of your own home. You must have a bathtub or shower accessible for us to bathe all large breeds. For small breeds, we can use your bath or kitchen sink. We only ask that you please provide towels. A secure counter and or table is required for all dogs to safely and properly be groomed.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title faqTitle">
                                <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse07"  aria-expanded="true" aria-controls="collapse07">Do you bathe the dog first?</a>
                            </h4>
                        </div>
                        <div id="collapse07" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="col-lg-12">
                                <p>To get a smooth and even haircut, the hair needs to be clean. Depending on your dog’s breed and style, the groomer may do a “rough cut.” After the rough cut, your dog is washed, fluff dried at which point the haircut is then completed. In other cases, we may wash and dry the dog completely before any length is taken off.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title faqTitle">
                                <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse08"  aria-expanded="true" aria-controls="collapse08">Can you groom my dog to the breed standard?</a>
                            </h4>
                        </div>
                        <div id="collapse08" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="col-lg-12">
                                <p>Yes. Our groomers are familiar with most of the breed standards as well as being highly experienced with the more popular breeds.
                                    Many clients in NYC do not groom to breed standard due to the level of maintenance required. 
                                    Regardless, if you are looking for a specific style, providing pictures allows both you and your groomer to see what your options are. </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title faqTitle">
                                <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse09"  aria-expanded="true" aria-controls="collapse09">Do I get the same groomer each time?</a>
                            </h4>
                        </div>
                        <div id="collapse09" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="col-lg-12">
                                <p>Some clients prefer to work with the same groomer. We recommend this if your dog tends to be anxious and nervous. Many dogs like the familiarity of having the same groomer as it allows them to not only feel more comfortable and relaxed, but it also allows the groomer to establish a consistent relationship with the dog. 
                                    If you would like to have the same groomer just "favorite" the groomer of your choice within the app.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title faqTitle">
                                <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse10"  aria-expanded="true" aria-controls="collapse10">Do you trim the nails?</a>
                            </h4>
                        </div>
                        <div id="collapse10" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="col-lg-12">
                                <p>Yes, trimming the nails is an important part of the grooming process.
                                    If your dog’s nails aren’t trimmed on a regular basis, the nail(s) may bleed. Regular nail trimming can minimize the chance of bleeding, but may not eliminate the risk completely.
                                    Please note we do not offer "nails only" service.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title faqTitle">
                                <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse11"  aria-expanded="true" aria-controls="collapse11">What are anal glands?</a>
                            </h4>
                        </div>
                        <div id="collapse11" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="col-lg-12">
                                <p>They are the scent sacs directly under your dog’s tail. Some dogs empty them naturally, some do not. 
                                    Per your request, please let your groomer know if you would like to have your dog's anal glands emptied (expressed) or if you would rather have your veterinarian do so.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title faqTitle">
                                <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse12"  aria-expanded="true" aria-controls="collapse12">What if I don't like the groom when it is finished?</a>
                            </h4>
                        </div>
                        <div id="collapse12" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="col-lg-12">
                                <p>Please tell us! We want you to be 100% satisfied. 
                                    If there are slight changes you would like done, your groomer can usually make those adjustments right then and there.
                                    In other instances, if the changes you request require more time, we may ask that your groomer come back another day in which case we can start again. If there are two or more people responsible for the grooming result, it is important that they first agree on a style before giving the groomer further instructions.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title faqTitle">
                                <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse13"  aria-expanded="true" aria-controls="collapse13">How much training does your groomer have?</a>
                            </h4>
                        </div>
                        <div id="collapse13" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="col-lg-12">
                                <p>All our groomers receive safety & policy training. We offer advanced and specialized training to all our current groomers as well as providing training to any beginners who are interested in joining our team at GroomIt.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title faqTitle">
                                <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse14"  aria-expanded="true" aria-controls="collapse14">Do we have to tip the groomers?</a>
                            </h4>
                        </div>
                        <div id="collapse14" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="col-lg-12">
                                <p>It is not required and at your full discretion as to whether you chose to tip your groomer. You have the option to tip within in the app.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title faqTitle">
                                <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse15"  aria-expanded="true" aria-controls="collapse15">How can I earn $15 off my next booking?</a>
                            </h4>
                        </div>
                        <div id="collapse15" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="col-lg-12">
                                <p>Once your first grooming is complete you can refer a friend by using your unique code. We will then credit your account $15 to be applied to your next grooming.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title faqTitle">
                                <a class="scrollToTop collapsed" data-toggle="collapse" data-parent="#accordion" data-target="#collapse16"  aria-expanded="true" aria-controls="collapse16">
                                    Should dog's ear be plucked?
                                </a>
                            </h4>
                        </div>
                        <div id="collapse16" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="col-lg-12">
                                <p>Our general recommendations are to pluck the ear hair on a regular basis to avoid buildup of dirt & ear wax and it's much easier on us and your pet to pluck a little ear hair then to wait and have a ton to try to remove at once.  Removing a lot of ear hair can create irritation and so our policy is that we are happy to do it but if you choose not to, then it needs to be done at your veterinarian to avoid complications.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</section> <!-- #benefits ends -->
@stop
