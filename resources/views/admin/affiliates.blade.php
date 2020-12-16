@extends('includes.admin_default')
@section('contents')

    <script type="text/javascript">
        window.onload = function() {
            $( "#sdate" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });
            $( "#edate" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });
        };

        function excel_export() {
            $('#excel').val('Y');
            $('#frm_search').submit();
        }

    </script>

  <div class="container-fluid top-cont">
      <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />AFFILIATE USERS</h3>
  </div>

  <div class="container">

      <div class="well filter" style="padding-bottom:5px;">
          <form id="frm_search" class="form-horizontal" method="post" action="/admin/affiliates">
              {{ csrf_field() }}
              <input type="hidden" name="excel" id="excel"/>
              <div class="row">
                  <div class="col-md-4">
                      <div class="form-group">
                          <label class="col-md-4 control-label">Date</label>
                          <div class="col-md-8">
                              <input type="text" style="width:100px; float:left;" class="form-control" id="sdate" name="sdate" value="{{ old('sdate', $sdate) }}"/>
                              <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                              <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate" name="edate" value="{{ old('edate', $edate) }}"/>
                          </div>
                      </div>
                  </div>
                  <div class="col-md-4">
                      <div class="form-group">
                          <label class="col-md-4 control-label">Name</label>
                          <div class="col-md-8">
                              <input type="text" class="form-control" name="name" value="{{ old('name', $name) }}"/>
                          </div>

                      </div>
                  </div>
                  <div class="col-md-4">
                      <div class="form-group">
                          <label class="col-md-4 control-label">Business Name</label>
                          <div class="col-md-8">
                              <input type="text" class="form-control" name="business_name" value="{{ old('business_name', $business_name) }}"/>
                          </div>
                      </div>
                  </div>
              </div>

              <div class="row">
                  <div class="col-md-4">
                      <div class="form-group">
                          <label class="col-md-4 control-label">Email</label>
                          <div class="col-md-8">
                              <input type="text" class="form-control" name="email" value="{{ old('email', $email) }}"/>
                          </div>
                      </div>
                  </div>

                  <div class="col-md-4 col-md-offset-4 text-right">
                      <div class="form-group">
                          <div class="col-md-8 col-md-offset-4">
                              @if (\App\Lib\Helper::get_action_privilege('affiliates_user_search', 'Affiliates User Search'))
                              <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                              @endif
                              @if (\App\Lib\Helper::get_action_privilege('affiliates_user_export', 'Affiliates User Export'))
                              <button type="button" class="btn btn-info btn-sm" onclick="excel_export()">Export</button>
                              @endif
                          </div>
                      </div>
                  </div>
              </div>
          </form>
      </div>

    <table id="table" class="table table-striped display" cellspacing="0" width="100%">
        <thead>
        <tr>
            <td colspan="8" class="text-right"><strong>TOTAL</strong>: {{ $total }}</td>
        </tr>
        <tr>
            <th>ID</th>
            <th>Business Name</th>
            <th>Name</th>
            <th>Email</th>
            <th>Earnings</th>
            <th>Redeemed Amount</th>
            <th>Status</th>
            <th>Last Login</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($affiliates as $a)
            <tr id="affiliate/{{ $a->aff_id }}">
                <td>{{ $a->aff_id }}</td>
                <td>{{ $a->business_name }}</td>
                <td>{{ $a->full_name() }}</td>
                <td>{{ $a->email }}</td>
                <td>{{ $a->earnings }}</td>
                <td>{{ $a->redeemed_amt }}</td>
                <td>{{ $a->status_name() }}</td>
                <td>{{ $a->last_login_date }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
  </div>
@stop
