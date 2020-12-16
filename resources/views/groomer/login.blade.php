<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Groomer Login</title>

    <link rel="stylesheet" type="text/css" href="/css/admin.css"/>

    <!-- Bootstrap core CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>
<body>
<div class="container">

    <!-- Static navbar -->
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="navbar-header">
            <a class="navbar-brand" href="/groomer">Groomer</a>
        </div>
    </nav>
    <!-- /navbar -->

    <form method="post" action="/groomer/login" class="form-signin">
        {!! csrf_field() !!}
        <h2 class="form-signin-heading">Sign in</h2>


        @if ($alert = Session::get('alert'))
            <div class="alert alert-danger">
                {{ $alert }}
            </div>
        @endif

        <label for="inputEmail" class="sr-only">Email</label>
        <input type="email" name="email" id="inputEmail" class="form-control" placeholder="Email address" required autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
        {{--<div class="checkbox">--}}
        {{--<label>--}}
        {{--<input type="checkbox" value="remember"> Remember me--}}
        {{--</label>--}}
        {{--</div>--}}
        <button class="btn btn-lg btn-info btn-block" type="submit">Sign in</button>
    </form>

    @include('includes.admin_footer')

</div>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.2.0/js/tether.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

</body>
</html>