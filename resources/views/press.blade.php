@extends('includes.default')

@section('content')
<!-- Banner -->
<section id="GroomitBannerClean" class="bannerPress" title="In-Home pet grooming"> <!-- #banner starts -->
	<div class="container cont-banner-white-space">
		<div class="banner-white-space">
            <div id="tabs-container">
                <ul class="nav nav-tabs" role="tablist" id="press-section-tabs">
                    <li role="presentation" class="active"><a href="#press-coverage" aria-controls="press-coverage" role="tab" data-toggle="tab">Press Coverage</a></li>
                    <li role="presentation"><a href="#media-resources" aria-controls="media-resources" role="tab" data-toggle="tab">Media Resources</a></li>
                </ul>
            </div>
        </div>
	</div>
</section> 
<!-- #banner ends -->

<section id="press" class=" pt-5"> 
	<div class="container container-content-clean" id="press-main" >
		<div class="row">
            <div class="col-lg-12 pt-2">
                <section id="press-tabs">
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <!-- Press Coverage -->
                        <div role="tabpanel" class="tab-pane active" id="press-coverage">
                            <div class="grid" id="press">
                                <div class="grid-sizer"></div>
                                <div class="gutter-sizer"></div>
                            </div>
                        <!-- /grid -->
                        </div>
                        <!-- Media Resources -->
                        <div role="tabpanel" class="tab-pane" id="media-resources">
                            <div class="grid" id="media">
                                <div class="grid-sizer"></div>
                                <div class="gutter-sizer"></div>
                            </div>
                            <!-- /grid -->
                        </div>
                    </div>
                </section>
            </div>
		</div>
	</div>
</section> 
<!-- #investor ends -->



@stop
