@extends('includes.investors_default_v2')

@section('contents')
<style>
h1, p{
	margin-left:2% !important;
}
iframe{
	margin: 0 4%;
}

@media only screen and (max-width: 991px) {
	iframe{
		width:100%;
		margin: 0;
	}

	h1, p{
		margin-left:0 !important;
	}
}

</style>
<!-- Banner -->
<div class="top-banner top-banner--position-top top-banner--investors"></div>
<div class="content content--terms top-bar--hidden pb-5">
    <div class="container content__overlapped">
        <div class="row mt-3 mt-lg-5">
			<div class="col-lg-5 offset-lg-1">
				<h1 class="text-left mb-4 content__main-title content__title--neutra-disp">BECOME AN INVESTOR</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-5 offset-lg-1">
				<div class="contBMForm">
					<p class=" mb-0"><strong>Thank you</strong></p>
					<p class=" mt-1">We will be emailing you our investors with the information on investing in Groomit.</p>
				</div>
			</div>
			<div class="col-lg-5">
				<iframe width="92%" height="242" src="https://www.youtube.com/embed/M8lpxAm6JG0?rel=0" frameborder="0" allowfullscreen></iframe>
			</div>
        </div>
    </div>
</div>
@stop