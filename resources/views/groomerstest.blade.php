@extends('user.layout.default')
@section('content')
    <link href="/desktop/css/appointment.css" rel="stylesheet" type="text/css">

    <div id="main">
        <!-- Page Content -->

        <!-- PROGRESS -->
        <div class="container-fluid" id="progress-bar">
            <div class="row">
                <div class="col-xs-3 line-status complete"></div>
                <div class="col-xs-3 line-status complete"></div>
                <div class="col-xs-3 line-status complete"></div>
                <div class="col-xs-3 line-status complete"></div>
            </div>
            <!-- row -->
        </div>
        <!-- /progress-bar -->

        <!-- MY GROOMERS -->
        <section id="my-groomers">
            <div class="container">
                

                <div class="row" id="groomers-no-groomers">
                    <div class="col-md-12 text-center">
                        <h3><span class="title-icon"><img src="/desktop/img/groomers-menu-icon.svg" width="38"
                                                          alt="My Groomers"></span> MY GROOMERS</h3>
                    </div>
                    <div class="col-md-12 text-center">
                        <p><em>Add a favorite Groomer after your first appointment</em></p>
                        <div class="col-lg-12">
                            <button type="button" class="groomit-btn rounded-btn red-btn long-btn" > Schedule Appointment
                            </button>
                        </div>
                    </div>
                </div>
                <!-- /row -->
                
                
                <div class="row after-title" id="groomers-no-favs">

                    <div class="col-md-12 text-center">
                        <h3><span class="title-icon"><img src="/desktop/img/groomers-menu-icon.svg" width="38"
                                                          alt="My Groomers"></span> MY GROOMERS</h3>
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-6 card-wrapper">
                                    <div class="media my-card">
                                        <div class="media-left"> 
                                            <div class="bh-pet-avatar media-object">
                                                <div class="table-cell media-middle">
                                                    <img class=" img-circle" src="/desktop/img/dog-icon.svg" alt="Groomer">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="media-body media-middle">
                                            <div class="display-table">
                                                <div class="table-cell">
                                                    <h4 class="media-heading">Soudeh 

                                                    <a href="#" data-toggle="modal" data-id="" data-target="#groomer-info">
                                                            <img class="infoiconimg" src="/desktop/img/ionicons_2-0-1_ios-information-outline_48_0_bd1a2a_none.png" width="18" height="18" alt="More Info">
                                                    </a>
                                                    </h4>
                                                    <p class="fav-action"><span class="glyphicon glyphicon-heart-empty make-fav-icon  make-fav-icon-gr"></span> <em>Make favorite</em></p>
                                                </div>
                                            </div>
                                            <!-- /row -->
                                        </div>
                                        <!-- /media-body -->
                                    </div>
                                    <!-- /my-dog-card -->
                    </div>
                    <!-- /col-3 -->
                    </div>
                    <!-- /row -->
            




            <div class="row after-title" id="groomers-favs">

                <div class="col-md-12 text-center">
                        <h3><span class="title-icon"><span class="glyphicon glyphicon-heart make-fav-icon make-fav-icon-gr"></span></span>MY FAVORITE</h3>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6 card-wrapper">
                                <div class="media my-card">
                                    <div class="media-left"> 
                                        <div class="bh-pet-avatar media-object">
                                            <div class="table-cell media-middle">
                                                <img class=" img-circle" src="/desktop/img/dog-icon.svg" alt="Groomer">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="media-body media-middle">
                                        <div class="display-table">
                                            <div class="table-cell">
                                                <h4 class="media-heading">Soudeh 
                                                    <a href="#" data-toggle="modal" data-id="" data-target="#groomer-info">
                                                            <img class="infoiconimg" src="/desktop/img/ionicons_2-0-1_ios-information-outline_48_0_bd1a2a_none.png" width="18" height="18" alt="More Info">
                                                    </a>
                                                </h4>
                                                <p class="fav-action"><span class="glyphicon glyphicon-heart make-fav-icon make-fav-icon-gr"></span> <em>Favorite</em></p>
                                            </div>
                                        </div>
                                        <!-- /row -->
                                    </div>
                                    <!-- /media-body -->
                                </div>
                                <!-- /my-dog-card -->
                            </div>
                            <!-- /col-3 -->
                </div>
                <!-- /row -->
            </div>






            </div>
            <!-- /container -->
        </section>
        <!-- /my-pets -->
    </div>
    <!-- /main -->

    <!-- MODALS -->

    
    <!-- RESCHEDULE APPOINTMENT CONFIRMATION MODAL -->
<div class="modal fade auto-width" id="groomer-info" tabindex="-1" role="dialog" aria-labelledby="reschedule-title">
	<div class="modal-dialog auto-width" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close close-reschedule" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
               
                        <!-- PET -->
                        <div class="  row-pet" >
                                <div class="text-center">
                                    <div class="history-pet">
                                        <!-- pet avatar -->
                                        <div class="bh-pet-avatar groomer-ph center text-center center-text">
                                            <img src="/desktop/img/dog-icon.svg"
                                                width="53" height="53" alt="Image">
                                        </div>
                                        <p class="text-center"><strong>Groomer Name</strong></p>
                                        <!-- pet photo -->
                                        <p class="text-center">
                                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation.
                                        </p>

                                        <div class="cell-rating">
                                            <div class="starrr" data-rating="3"></div>
                                            <input type="hidden" class="rating" name="rating" value=""/>
                                        </div>
                                        <p class="completed-g text-center"><strong>Completed Groomings: 100</strong></p>
                                        <p><span class="glyphicon glyphicon-heart make-fav-icon make-fav-icon-gr"></span> <em>Favorite</em></p>

                                    </div>
                                    <!-- media -->
                                </div>
                                <!-- /col -->
                                
                        </div>
                        <!-- /row -->
                    

			</div>
			<!-- /modal-body -->
		</div>
		<!-- /modal-content -->

	</div>
</div>
<!-- /MODAL-->


    <script type="text/javascript">

      

    </script>
@stop