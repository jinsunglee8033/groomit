@extends('includes.admin_default')
@section('contents')

  <div class="container-fluid top-cont">
      <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />ADMIN USERS <a href="/admin/registration" class="btn btn-info btn-red-top" >+ ADD USER</a></h3>
  </div>

  <div class="container">
    <table id="table" class="table table-striped display" cellspacing="0" width="100%">
        <thead>
        <tr>
            <td colspan="4" class="text-right"><strong>TOTAL</strong>: {{ $total }}</td>
        </tr>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Privilege</th>
            <th>Last Login</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($admins as $ad)
            <tr id="admin/{{ $ad->admin_id }}">
                <td>{{ $ad->name }}</td>
                <td>{{ $ad->email }}</td>
                <td>{{ $ad->status_name }}</td>
                <td>{{ $ad->group }}</td>
                <td>{{ $ad->last_login_date }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
  </div>
@stop
