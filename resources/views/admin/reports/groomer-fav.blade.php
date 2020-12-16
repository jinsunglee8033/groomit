@extends('includes.admin_default')
@section('contents')

    <script type="text/javascript">
        window.onload = function() {
            var val = $("#groomer_id").find('option:selected').text();
            $('#g_name').text(val);
        };

        function search() {
            $('#excel').val('N');
            $('#frm_search').submit();
        }

        function excel_export() {
            $('#excel').val('Y');
            $('#frm_search').submit();
        }

        function delete_all() {
            var groomer_id = $('#groomer_id').val();

            if (groomer_id == '') {
                alert("Please select Groomer!");
                return;
            }

            myApp.showConfirm('[Delete All] Are you sure to prooceed?', function() {
                $.ajax({
                    url: '/admin/reports/groomer-fav/delete-all',
                    data: {
                        _token: '{{ csrf_token() }}',
                        groomer_id: groomer_id
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function (res) {
                        myApp.hideLoading();
                        if (res.msg == '') {

                            myApp.showSuccess('Your request has been processed successful!', function () {
                                window.location.reload();
                            });
                        } else {
                            myApp.showError(res.msg);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        myApp.hideLoading();
                        myApp.showError(errorThrown);
                    }
                })
            })
        }

        function delete_row(groomer_id, user_id) {

            myApp.showConfirm('[Delete] Are you sure to prooceed?', function() {
                $.ajax({
                    url: '/admin/reports/groomer-fav/delete',
                    data: {
                        _token: '{{ csrf_token() }}',
                        groomer_id: groomer_id,
                        user_id: user_id
                    },
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function(res) {
                        myApp.hideLoading();
                        if (res.msg == ''){

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
                })
            })
        }

    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />Favorite Users</h3>
    </div>

    <div class="container-fluid">

        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/groomer-fav">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel"/>
                <div class="row">

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Groomer</label>
                            <div class="col-md-8">
                                @if ($link_g_id == '')
                                    <select class="form-control" name="groomer_id" id="groomer_id" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                        <option value="" {{ old('groomer_id', $groomer_id) == '' ? 'selected' : '' }}>All Groomers</option>
                                        @if (count($groomers) > 0)
                                            @foreach ($groomers as $o)
                                                <option value="{{ $o->groomer_id }}" {{ old('groomer_id', $groomer_id) == $o->groomer_id ? 'selected' : '' }}>{{ $o->first_name . ' ' . $o->last_name }}[{{$o->status}}]</option>
                                            @endforeach
                                        @endif
                                    </select>
                                @else
                                    <select class="form-control" name="groomer_id" id="groomer_id" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                        <option value="" {{ old('groomer_id', $link_g_id) == '' ? 'selected' : '' }}>All Groomers</option>
                                        @if (count($groomers) > 0)
                                            @foreach ($groomers as $o)
                                                <option value="{{ $o->groomer_id }}" {{ old('groomer_id', $link_g_id) == $o->groomer_id ? 'selected' : '' }}>{{ $o->first_name . ' ' . $o->last_name }}[{{$o->status}}]</option>
                                            @endforeach
                                        @endif
                                    </select>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="button" class="btn btn-primary btn-sm" id="btn_search" onclick="search()">Search</button>
                                <button type="button" class="btn btn-info btn-sm" onclick="excel_export()">Export</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <table class="table table-striped display" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Groomer.ID</th>
                    <th>Groomer.Name</th>
                    <th>User.ID</th>
                    <th>User.Name</th>
                    <th>Create.Date</th>
                    <th>Last.Groomed.Date</th>
                    <th>Last.Groomer.Name</th>
                    <th><button type="button" class="btn btn-sm btn-primary" onclick="delete_all()">Delete All</button></th>
                </tr>
            </thead>
            <tbody>
                @if (count($data) > 0)
                    @foreach ($data as $o)
                        <tr>
                            <td>{{ $o->groomer_id }}</td>
                            <td>{{ $o->g_name }}</td>
                            <td>{{ $o->user_id }}</td>
                            <td>
                                <a href="/admin/user/{{ $o->user_id }}">
                                    {{ $o->first_name . ' ' . $o->last_name }}
                                </a>
                            </td>
                            <td>{{ $o->cdate }}</td>
                            <td>{{ $o->last_date }}</td>
                            <td>{{ $o->last_groomer_f . ' ' . $o->last_groomer_l }}</td>
                            <td><button type="button" class="btn btn-sm btn-primary" onclick="delete_row({{$o->groomer_id}}, {{$o->user_id}})">Delete</button></td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="100" class="text-center">No record found.</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
            </tfoot>
        </table>

        <div class="text-right">
            {{ $data->appends(Request::except('page'))->links() }}
        </div>
    </div>
@stop
