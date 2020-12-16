@extends('includes.admin_default')
@section('contents')

  <div class="container-fluid top-cont">
      <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />CREATE A NEW ADMIN <a href="{{ URL::previous() }}" class="btn btn-default">BACK</a></h3>
  </div>

  <div class="container">
    <div class="detail">
    <form method="post" action="/admin/registration" class="form-signin">
        {!! csrf_field() !!}

        @if ($alert = Session::get('alert'))
            <div class="alert alert-danger">
                {{ $alert }}
            </div>
        @endif

        <label for="inputName" class="sr-only">Name</label>
        <input type="text" name="name" id="inputName" class="form-control" placeholder="Name" required autofocus>
        <br/>
        <label for="inputEmail" class="sr-only">Email</label>
        <input type="email" name="email" id="inputEmail" class="form-control" placeholder="Email address" required>
        <br/>
        <label for="inputGroup" class="sr-only">Privilege</label>
        <select  class="form-control" name="group" id="inputGroup" required>
            @if (is_array($groups))
                    <option value="">Select</option>
                @foreach ($groups as $o)
                    <option value="{{ $o->group }}">{{ $o->group }}</option>
                @endforeach
            @endif
        </select>
        <br/>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
        <br/>
        <label for="inputPasswordConfirmation" class="sr-only">Confirm Password</label>
        <input type="password" name="password_confirmation" id="inputPasswordConfirmation" class="form-control" placeholder="Confirm Password" required>
        <br/>
        <button class="btn btn-lg btn-info btn-block" type="submit">Create</button>
    </form>
    </div>
  </div>

  @include('includes.admin_footer')

@stop
