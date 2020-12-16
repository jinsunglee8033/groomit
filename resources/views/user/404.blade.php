@extends('user.layout.default')

@section('content')
<div id="error-main">
    <section>
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-lg-offset-3 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
                    <img class="img-responsive" src="/images/error.png" alt="Error 404 - You seem to have found a dead link">
                </div>
            </div>
        </div>
    </section>
    <footer>
        <div class="container">
        <div class="row">
            <div class="col-lg-10 col-lg-offset-1">
            <p class="text-uppercase text-center">&copy; 2020 Groomit - Made with love in NYC.</p>
            </div>
            <!-- /col -->
        </div>
        <!-- /row -->
        </div>
        <!-- /container -->
    </footer>
</div>
@stop
