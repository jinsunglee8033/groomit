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

        function update_redeem_status(id) {
            myApp.showLoading();
            $.ajax({
                url: '/admin/affiliate/change-redeem-status',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    status: $("#redeem_status" + id).val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        $('#change_status').modal('hide');
                        myApp.showSuccess('Your request has been processed successful!', function() {
                            window.location.reload();
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

    </script>

  <div class="container-fluid top-cont">
      <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />AFFILIATE WITHDRAW REQUESTS</h3>
  </div>

  <div class="container">

      <div class="well filter" style="padding-bottom:5px;">
          <form id="frm_search" class="form-horizontal" method="post" action="/admin/affiliate_withdraw_requests">
              {{ csrf_field() }}
              <input type="hidden" name="excel" id="excel"/>
              <div class="row">
                  <div class="col-md-4">
                      <div class="form-group">
                          <label class="col-md-4 control-label">Request Date</label>
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
                              @if (\App\Lib\Helper::get_action_privilege('affiliates_widthraw_search', 'Affiliates Withdraw Search'))
                              <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                              @endif
                              @if (\App\Lib\Helper::get_action_privilege('affiliates_widthraw_export', 'Affiliates Withdraw Export'))
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
            <td colspan="10" class="text-right"><strong>TOTAL</strong>: {{ $total }}</td>
        </tr>
        <tr>
            <th>ID</th>
            <th>Business Name</th>
            <th>Email / Phone</th>
            <th>Name</th>
            <th>Send.To</th>
            <th>Amount</th>
            <th>Created.Date</th>
            <th>Updated.Date</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($withdraw_req as $a)
            <tr>
                <td>{{ $a->aff_redeemed_id }}</td>
                <td><a href="/admin/affiliate/{{ $a->aff_id }}" target="_blank">{{ $a->business_name }} ({{ $a->aff_id }})</a></td>
                <td>{{ $a->email }} <br>{{ $a->phone }}</td>
                <td>{{ $a->first_name }} {{ $a->last_name }}</td>
                <td>
                    @if (!empty($a->type))
                        @if($a->type == 'B')
                            <strong>Transfer To</strong> : <br>
                            {{ $a->bank_name }}<br>{{ $a->bank_account_number }}<br>{{ $a->routing_number }}
                        @else
                            <strong>Send Check To</strong> : <br>
                            {{ $a->address }} {{ $a->address2 }}<br>
                            {{ $a->city }}, {{ $a->state }} {{ $a->zip }}
                        @endif
                    @endif
                </td>
                <td class="text-right">${{ $a->amount }}</td>
                <td>{{ $a->cdate }}</td>
                <td>{{ $a->mdate }}</td>
                <td>

                    @if (empty($a->type))
                        New Earned
                    @else
                    <select id="redeem_status{{ $a->aff_redeemed_id }}">
                        <option value="N" @if($a->status == 'N') selected @endif>New</option>
                        <option value="S" @if($a->status == 'S') selected @endif>Processing</option>
                        <option value="P" @if($a->status == 'P') selected @endif>Paid</option>
                        <option value="C" @if($a->status == 'C') selected @endif>Canceled</option>
                    </select>
                    <button type="button" class="btn btn-xs btn-primary" onclick="update_redeem_status({{ $a->aff_redeemed_id }})">Update</button>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
  </div>
@stop
