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

            // show select an user initially
            $('#send_to').show();
            $('#user_id').show();
            $('#groomer').hide();

            $('#see_detail').click(function () {
                var indexKey = $(this).data('id');

                $("#notification_id").text($("#notification_id" + indexKey).text());
                $("#type_name").text($("#type_name" + indexKey).text());
                $("#to_name").text($("#to_name" + indexKey).text());
                $("#user_name").text($("#user_name" + indexKey).text());
                $("#groomer_name").text($("#groomer_name" + indexKey).text());
                $("#message").text($("#message" + indexKey).text());
                $("#date").text($("#date" + indexKey).text());
            });

            // search user :
            $('.search_user').click(function() {

                $('#search_result').text('');
                var searchType = $(this).data('id');
                var searchStr = $('#' + searchType + '_str').val();
                var url = '/admin/get_users/' + searchType + '/' + searchStr;

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'text',
                    success: function(data) {
                        data = JSON.parse(data);
                        if (data.msg) {
                            $('#search_result').html('<p class="alert alert-warning">An error has occurred </p>');
                        }  else {
                            $.each( data, function( i, val ) {
                                var $id = $('<input type="radio" name="' + searchType + '_id" value="' + val.id + '"/>');
                                var $name = $('<span class="padding-10 name">').text(val.name);
                                var $email = $('<span class="padding-10">').text(val.email);
                                var $phone = $('<span class="padding-10">').text(val.phone);
                                $('#search_result')
                                        .append($id)
                                        .append($name)
                                        .append($email)
                                        .append($phone)
                                        .append('<br>');
                            });
                        }

                    },
                    error: function(jqXHR, textStatus, errorThrown) {

                        $('#search_result').html('<p class="alert alert-warning">An error has occurred </p>');
                        console.log(textStatus + ': ' + errorThrown);
                    }
                });
            });

            // Typeahead : work on it later when have a time
//            // search user
//            suggestion("user");
//            // search groomer
//            suggestion("groomer");

        };


        function excel_export() {
            $('#excel').val('Y');
            $('#frm_search').submit();
        }

        function check_to(val) {
            $('#search_result').text('');

            if (val == 'A') { // a user

                $('#send_to').show();
                $('#user').show();
                $('#groomer').hide();

            } else if (val == 'B') { // a groomer

                $('#send_to').show();
                $('#user').hide();
                $('#groomer').show();

            } else {
                $('#send_to').hide();
            }
        }

        function send() {
            $('#send').submit();
        }

    </script>

    <h3>Push Notification
        <div class="btn-right btn btn-info" data-toggle="modal" data-target="#send">Send Push Notification</div>
    </h3>

    <hr>

    @if ($alert = Session::get('alert'))
        @if ($alert == 'Success')
            <div class="alert alert-success detail">
                {{ $alert }}
            </div>
        @else
            <div class="alert alert-danger detail">
                {{ $alert }}
            </div>
        @endif
    @endif

    <div class="well filter" style="padding-bottom:5px;">
        <form id="frm_search" class="form-horizontal" method="post" action="/admin/push_notifications">
            {{ csrf_field() }}
            <input type="hidden" name="excel" id="excel"/>
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
                        <label class="col-md-4 control-label">Name</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="name" value="{{ old('name', $name) }}"/>
                        </div>

                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Type</label>
                        <div class="col-md-8">
                            <select class="form-control" name="type" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                <option value="" {{ old('type', $type) == '' ? 'selected' : '' }}>All</option>
                                @foreach($notification_types as $k=>$v)
                                    <option value="{{ $k }}" {{ old('type', $type) == $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">To</label>
                        <div class="col-md-8">
                            <select class="form-control" name="to" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                <option value="" {{ old('to', $to) == '' ? 'selected' : '' }}>All</option>
                                @foreach($notification_to as $k=>$v)
                                    <option value="{{ $k }}" {{ old('to', $to) == $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Message</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="message" value="{{ old('message', $message) }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-4">
                            <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                            <button type="button" class="btn btn-info btn-sm" onclick="excel_export()">Export</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <table id="table" class="table table-striped display" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>ID</th>
            <th>Type</th>
            <th>To</th>
            <th>Groomer</th>
            <th>User</th>
            <th>Message</th>
            <th>Date</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($notifications as $indexKey => $n)
            <tr>
                <td id="notification_id{{ $indexKey }}">{{ $n->notification_id }}</td>
                <td id="type_name{{ $indexKey }}">{{ $n->type_name }}</td>
                <td id="to_name{{ $indexKey }}">{{ $n->to_name }}</td>
                <td id="groomer_name{{ $indexKey }}">@if(!empty($n->g_fname)){{ $n->g_fname }} {{ $n->g_lname }}@endif</td>
                <td id="user_name{{ $indexKey }}">@if(!empty($n->u_fname)){{ $n->u_fname }} {{ $n->u_lname }}@endif</td>
                <td id="message{{ $indexKey }}">{{ $n->message }}</td>
                <td id="date{{ $indexKey }}">{{ $n->cdate }}</td>
                <td><div class="btn-right btn btn-default" id="see_detail" data-toggle="modal" data-id="{{ $indexKey }}" data-target="#detail">See Detail</div></td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="text-right">
        {{ $notifications->appends(Request::except('page'))->links() }}
    </div>


    <!-- See Detail Modal Start -->
    <div class="modal" id="detail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Push Notification Detail</h4>
                </div>
                    <div class="modal-body">
                        <div class="row padding-10">
                            <div class="col-xs-3">ID</div>
                            <div class="col-xs-8" id="notification_id"></div>
                        </div>
                        <div class="row padding-10">
                            <div class="col-xs-3">Message Type</div>
                            <div class="col-xs-8" id="type_name"></div>
                        </div>
                        <div class="row padding-10">
                            <div class="col-xs-3">Message To</div>
                            <div class="col-xs-8" id="to_name"></div>
                        </div>
                        <div class="row padding-10">
                            <div class="col-xs-3">Sent To</div>
                            <div class="col-xs-8"><span id="groomer_name"></span><span id="user_name"></span></div>
                        </div>
                        <div class="row padding-10">
                            <div class="col-xs-3">Message</div>
                            <div class="col-xs-8" id="message"></div>
                        </div>
                        <div class="row padding-10">
                            <div class="col-xs-3">Sent Date</div>
                            <div class="col-xs-8" id="date"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
            </div>
        </div>
    </div>
    <!-- See Detail Modal End -->


    <!-- Send Modal Start -->
    <div class="modal" id="send" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Send Push Notification</h4>
                </div>
                <form method="post" id="send" action="/admin/push_notification/send" class="form-group">
                    {!! csrf_field() !!}
                    <div class="modal-body">
                        <div class="row padding-10">
                            <div class="col-xs-3">Send To</div>
                            <div class="col-xs-8">
                                <select class="form-control" name="to" onchange="check_to(this.value)">
                                    @foreach($notification_to as $k=>$v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row padding-10" id="send_to">
                            <div class="col-xs-3"></div>
                            <div class="col-xs-8">

                                <div id="user">
                                    {{--<input type="text" name="user_name" id="user_name" class="form-control typeahead" autocomplete="off" placeholder="Enter User's Name">--}}
                                    <input type="text" name="user_str" id="user_str" class="form-control " placeholder="Enter Name or Phone # or Email">
                                    <div class="btn btn-info btn-right search_user" data-id="user">search</div>
                                </div>

                                <div id="groomer">
                                    {{--<input type="text" name="groomer" id="groomer" class="form-control typeahead" autocomplete="off" placeholder="Enter Groomer's Name">--}}
                                    <input type="text" name="groomer_str" id="groomer_str" class="form-control" placeholder="Enter Name or Phone # or Email">
                                    <div class="btn btn-info btn-right search_user" data-id="groomer">search</div>
                                </div>

                                <div id="search_result"></div>
                            </div>


                        </div>

                        <div class="row padding-10">
                            <div class="col-xs-3">Message Type</div>
                            <div class="col-xs-8">
                                <select class="form-control" name="type">
                                    @foreach($notification_types as $k=>$v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row padding-10">
                            <div class="col-xs-3">Message</div>
                            <div class="col-xs-8">
                                <textarea class="form-control" name="message" rows="7"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-warning" type="submit">Send</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Send Modal End -->
@stop