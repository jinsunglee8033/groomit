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

            @if (count($errors) > 0)
            $('#error').modal();
            @endif

            $('.search_receiver').click(function() {
                $('#search_result').text('');
                var zip = $('#zip').val();
                var url = '/admin/reports/get_zip/' + zip;
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'text',
                    success: function(data) {
                        data = JSON.parse(data);
                        if (data.msg) {
                            $('#search_result').html('<p class="alert alert-warning">An error has occurred </p>');
                        }  else {
                            // $('#search_result')
                            //     .append('<span class="padding-10 name">-</span>').append('<span class="padding-10 name">zip</span>').append('<span class="padding-10 name">County</span>').append('<span class="padding-10 name">City</span>').append('<span class="padding-10 name">Level</span>').append('<span class="padding-10 name">Short Name</span>').append('<span class="padding-10 name">Available</span>').append('<br>')
                            $.each( data, function( i, val ) {
                                var $id = $('<input type="radio" name="allowed_zip_id" id="allowed_zip_id" value="' + val.id + '"/>');
                                var $zip = $('<span class="padding-10 name">').text(val.zip);
                                var $county_name = $('<span class="padding-10 name">').text(val.county_name);
                                var $name = $('<span class="padding-10 name">').text(val.city_name);
                                if(val.group_id == '1'){
                                    var $group_id = $('<span class="padding-10 name">').text('Price Level 1');
                                }else{
                                    var $group_id = $('<span class="padding-10 name">').text('Price Level 2');
                                }
                                var $short_name = $('<span class="padding-10 name">').text(val.short_name);
                                if(val.available == 'x'){
                                    var $available = $('<span class="padding-10 name">').text('Allowed');
                                }else{
                                    var $available = $('<span class="padding-10 name">').text('Not Allowed');
                                }
                                $('#search_result')
                                    .append($id)
                                    .append($zip)
                                    .append($county_name)
                                    .append($name)
                                    .append($group_id)
                                    .append($short_name)
                                    .append($available)
                                    .append('<br>');
                            });
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {

                        $('#search_result').html('<p class="alert alert-warning">An error has occurred.</p>');
                        console.log(textStatus + ': ' + errorThrown);
                    }
                });
            });
        };

        function search() {
            myApp.showLoading();
            $('#excel').val('N');
            $('#frm_search').submit();
        }

        function excel_export() {
            $('#excel').val('Y');
            $('#frm_search').submit();
        }

        function lookup() {

            $("#lookup").modal();
        }

        function update_lookup() {


            if(!$("input[name='allowed_zip_id']:checked").val()){
                alert("Please Enter or Select Zip");
                return;
            }

            var zip_id = $("input[name='allowed_zip_id']:checked").val();
            var short_name = $('#short_name').val();
            var group_id = $('#group_id').val();
            var available = $('#available').val();

            myApp.showLoading();
            $.ajax({
                url: '/admin/reports/update_lookup',
                data: {
                    _token: '{!! csrf_token() !!}',
                    zip_id: zip_id,
                    short_name : short_name,
                    group_id : group_id,
                    available: available
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        myApp.showSuccess('Your request has been processed successfully!', function () {
                            search();
                        });
                    } else {
                        //alert(res.msg);
                        myApp.showError(res.msg);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png"
            />ZIP Lookup REPORT</h3>
    </div>

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/ziplookup">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel" value=""/>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Date Range</label>
                            <div class="col-md-8">
                                <input type="text" style="width:100px; float:left;" class="form-control" id="sdate" name="sdate" value="{{ old('sdate', $sdate) }}"/>
                                <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                                <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate" name="edate" value="{{ old('edate', $edate) }}"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">State</label>
                            <div class="col-md-8">
                                <input type="text" style="width:100px; float:left;" class="form-control" id="state" name="state" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-md-offset-8 text-right">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="button" class="btn btn-info btn-sm" onclick="lookup()">Zip Lookup</button>
                                <button type="button" class="btn btn-primary btn-sm" id="btn_search" onclick="search()">Search</button>
                                <button type="button" class="btn btn-info btn-sm" onclick="excel_export()">Export</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <style>
            th {
                background-color: #f5f5f5;
            }
        </style>

        <table class="table table-bordered display" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th style="text-align: center;">Date.Time</th>
                <th style="text-align: center;">Zip</th>
                <th style="text-align: center;">City</th>
{{--                <th style="text-align: center;">County</th>--}}
                <th style="text-align: center;">State</th>
                <th style="text-align: center;">Name</th>
                <th style="text-align: center;">Phone</th>
                <th style="text-align: center;">Email</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $o)
                <tr>
                    <td>{{ $o->cdate }}</td>
                    <td>{{ $o->zip }}</td>
                    <td>{{ $o->city}}</td>
{{--                    <td>{{ $o->county_name }}</td>--}}
                    <td>{{ $o->state }}</td>
                    <td>{{ $o->first_name . ' ' . $o->last_name }}</td>
                    <td>{{ $o->phone }}</td>
                    <td>{{ $o->email }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            </tfoot>
        </table>

        <div class="text-right">
            {{ $data->appends(Request::except('page'))->links() }}
        </div>




        <div class="modal" id="lookup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content modal-lg">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="promo_code_title">Zip Lookup</h4>
                    </div>
                    <div class="form-group">
                        <div class="modal-body">

                            <div class="row padding-10" id="receiver">
                                <div class="col-xs-2">Zip</div>
                                <div class="col-xs-10">
                                    <input type="text" name="zip" id="zip" class="form-control " placeholder="Enter Zip..">
                                    <div class="btn btn-info btn-right search_receiver">search</div>
                                    <div id="receiver_type" style="display:none;"></div>
                                    <div id="search_result"></div>
                                </div>
                            </div>

                            <div class="row padding-10">
                                <div class="col-xs-2">Short Name</div>
                                <div class="col-xs-10">
                                    <input class="form-control" name="short_name" id="short_name" maxlength="10">
                                </div>
                            </div>

                            <div class="row padding-10">
                                <div class="col-xs-2">Group ID</div>
                                <div class="col-xs-10">
                                    <select class="form-control" name="group_id" id="group_id">
                                        <option value="">Select</option>
                                        <option value="1">Price Level 1</option>
                                        <option value="2">Price Level 2</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row padding-10">
                                <div class="col-xs-2">Available</div>
                                <div class="col-xs-10">
                                    <select class="form-control" name="available" id="available">
                                        <option value="">Select</option>
                                        <option value="E">Enable (Allow)</option>
                                        <option value="D">Disable (Not Allow)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-warning" type="button" onclick="update_lookup()">Submit</button>
                            <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


@stop