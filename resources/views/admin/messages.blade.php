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
            $('#receiver').show();
            $('#receiver_type').text('user');

            $('#reply').hide();
            $('#send_reply').hide();

            if(($('#g_id').val().length > 0)){

                $('#n_send_method').val('S');
                $('#n_receiver_type').val('B');

                var g_id = $('#g_id').val();
                var g_name = $('#g_name').val();
                var g_email = $('#g_email').val();
                var g_phone = $('#g_phone').val();

                var $id = $('<input type="radio" name="n_receiver_id" value="' + g_id + '" checked/>');
                var $name = $('<span class="padding-10 name">').text(g_name);
                var $email = $('<span class="padding-10">').text(g_email);
                var $phone = $('<span class="padding-10">').text(g_phone);

                $('#search_result')
                    .append($id)
                    .append($name)
                    .append($email)
                    .append($phone)
                    .append('<br>');
            }

            if(($('#u_id').val().length > 0)){

                $('#n_send_method').val('S');
                $('#n_receiver_type').val('A');

                var u_id = $('#u_id').val();
                var u_name = $('#u_name').val();
                var u_email = $('#u_email').val();
                var u_phone = $('#u_phone').val();

                var $id = $('<input type="radio" name="n_receiver_id" value="' + u_id + '" checked/>');
                var $name = $('<span class="padding-10 name">').text(u_name);
                var $email = $('<span class="padding-10">').text(u_email);
                var $phone = $('<span class="padding-10">').text(u_phone);

                $('#search_result')
                    .append($id)
                    .append($name)
                    .append($email)
                    .append($phone)
                    .append('<br>');
            }


            // search user :
            $('.search_receiver').click(function() {

                $('#search_result').text('');
                var searchType = $('#receiver_type').text();
                var searchStr = $('#receiver_str').val();
                var url = '/admin/get_receivers/' + searchType + '/' + searchStr;

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
                                var $id = $('<input type="radio" name="n_receiver_id" value="' + val.id + '"/>');
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

                        $('#search_result').html('<p class="alert alert-warning">An error has occurred.</p>');
                        console.log(textStatus + ': ' + errorThrown);
                    }
                });
            });

            // send reply :
            $('#open_reply').click(function() {
                $('#reply').show();
                $('#open_reply').hide();
                $('#send_reply').show();
            });
        };


        function view_detail(indexKey) {

            $('#detail').modal('show');
            $("#parent_id").text($("#message_id" + indexKey).text());

            //load messages
            detail($("#message_id" + indexKey).text());

            // for reply
            $("#r_parent_id").val($("#message_id" + indexKey).text());
            $("#r_receiver_id").val($("#sender_id" + indexKey).text());
            $("#r_receiver_type").val($("#sender_type" + indexKey).text());
        }
        function excel_export() {
            $('#excel').val('Y');
            $('#frm_search').submit();
        }

        function check_receiver(val) {

            $('#level1').prop('checked', false);
            $('#level2').prop('checked', false);
            $('#level3').prop('checked', false);
            $('#level4').prop('checked', false);
            $('#level5').prop('checked', false);
            $('#area1').prop('checked', false);
            $('#area2').prop('checked', false);
            $('#area3').prop('checked', false);
            $('#area4').prop('checked', false);
            $('#area5').prop('checked', false);

            $('#search_result').text('');

            if (val == 'A') { // a user

                $('#receiver').show();
                $('#receiver_type').text('user');
                $('#groomer_group').hide();

            } else if (val == 'B') { // a groomer
                $('#receiver').show();
                $('#receiver_type').text('groomer');
                $('#groomer_group').hide();

            } else if (val == 'C') { // an admin user

                $('#receiver').show();
                $('#receiver_type').text('admin');
                $('#groomer_group').hide();

            } else if (val == 'R') { // Specific Groomer Group

                $('#receiver').hide();
                $('#receiver_type').text('groomer');
                $('#groomer_group').show();

            } else {
                $('#receiver').hide();
                $('#groomer_group').hide();
            }
        }

        function detail(id) {

            myApp.showLoading();
            $.ajax({
                url: '/admin/messages/detail',
                data: {
                    _token: '{!! csrf_token() !!}',
                    id: id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        $('#detail_body').empty();
                        $.each(res.messages, function (i, o) {
                            if (o.sender_type == 'A') {
                                var html = '<div class="right-message">';
                                var date_align = 'text-right';
                            } else {
                                var html = '<div class="left-message">';
                                var date_align = '';
                            }

                            html += '<div><strong>' + o.sender + '</strong>:</div>';
                            html += '<div>' + o.message + '</div>';
                            html += '</div>';
                            html += '<div class="message-date ' + date_align + '">' + o.cdate + '</div>';

                            $('#detail_body').append(html);
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

        function send() {

            var level = [];
            var area = [];
            $("input[type=checkbox]:checked").each(function() {
                var a = $(this).val();
                if(!isNaN(parseFloat(a)) && !isNaN(a - 0)){
                    level.push($(this).val());
                }else{
                    area.push($(this).val());
                }
            });

            if( ($('#n_receiver_type').val() == 'R' && level.length ==0 ) || ($('#n_receiver_type').val() == 'R' && area.length ==0 )){
                alert("Please select Level and Area");
                return;
            }

            myApp.showLoading();
            $.ajax({
                url: '/admin/messages/send',
                data: {
                    _token: '{!! csrf_token() !!}',
                    send_method: $('#n_send_method').val(),
                    receiver_type: $('#n_receiver_type').val(),
                    receiver_id: $('[name=n_receiver_id]:checked').val(),
                    message_type: $('#n_message_type').val(),
                    subject: $('#n_subject').val(),
                    message: $('#n_message').val(),
                    level: level,
                    area: area
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

        function reply() {
            //$('#send').submit();
            myApp.showLoading();
            $.ajax({
                url: '/admin/messages/send',
                data: {
                    _token: '{!! csrf_token() !!}',
                    send_method: $('#r_send_method').val(),
                    receiver_type: $('#r_receiver_type').val(),
                    receiver_id: $('#r_receiver_id').val(),
                    message_type: $('#r_message_type').val(),
                    message: $('#r_message').val(),
                    parent_id: $('#r_parent_id').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {

                        if (res.parent_id) {
                            detail(res.parent_id);
                        }

                        $('#r_message').val('')
                        $('#open_reply').show();
                        $('#send_reply').hide();
                        $('#reply').hide();

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

        function search() {
            $('#excel').val('N');
            $('#frm_search').submit();
        }

    </script>


    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />MESSAGES
            <div class="btn-right btn btn-info btn-red-top" data-toggle="modal" data-target="#send"><span><img class="img-respondive top-logo-img" src="/images/top-message-icon.png" /></span>SEND A MESSAGE</h3>
    </div>

    <div class="container">

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
        <form id="frm_search" class="form-horizontal" method="post" action="/admin/messages">
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
                        <label class="col-md-4 control-label">Send Method</label>
                        <div class="col-md-8">
                            <select class="form-control" name="send_method" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                <option value="" {{ old('send_method', $send_method) == '' ? 'selected' : '' }}>All</option>
                                @foreach($send_methods as $k=>$v)
                                    <option value="{{ $k }}" {{ old('send_method', $send_method) == $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Message Type</label>
                        <div class="col-md-8">
                            <select class="form-control" name="message_type" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                <option value="" {{ old('message_type', $message_type) == '' ? 'selected' : '' }}>All</option>
                                @foreach($message_types as $k=>$v)
                                    <option value="{{ $k }}" {{ old('message_type', $message_type) == $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Receiver</label>
                        <div class="col-md-8">
                            <select class="form-control" name="receiver_type" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                <option value="" {{ old('receiver_type', $receiver_type) == '' ? 'selected' : '' }}>All</option>
                                @foreach($receiver_types as $k=>$v)
                                    <option value="{{ $k }}" {{ old('receiver_type', $receiver_type) == $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Sender</label>
                        <div class="col-md-8">
                            <select class="form-control" name="sender_type" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                <option value="" {{ old('sender_type', $sender_type) == '' ? 'selected' : '' }}>All</option>
                                @foreach($sender_types as $k=>$v)
                                    <option value="{{ $k }}" {{ old('sender_type', $sender_type) == $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
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
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Subject</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="subject" value="{{ old('subject', $subject) }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Appointment.ID</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="appointment_id" value="{{ old('appointment_id', $appointment_id) }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">User ID</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="user_id" value="{{ old('user_id', !empty($user_id) ? $user_id : '') }}"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Groomer ID</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="groomer_id" value="{{ old('groomer_id', !empty($groomer_id) ? $groomer_id : '') }}"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 col-md-offset-8 text-right">
                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-4">
                            @if (!in_array('messages_search',session('url')))
                            <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                            @endif
                            @if (!in_array('messages_export', session('url')))
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
            <td colspan="9" class="text-right"><strong>TOTAL</strong>: {{ $total }}</td>
        </tr>
        <tr>
            <th>ID</th>
            <th>Appointment ID</th>
            <th>Send Method</th>
            <th>Message Type</th>
            <th>Sender</th>
            <th>Receiver</th>
            <th>Message</th>
            <th>Date</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($messages as $indexKey => $n)
            <tr>
                <td id="message_id{{ $indexKey }}">{{ $n->message_id }}</td>
                <td id="appointment_id{{ $indexKey }}">{{ $n->appointment_id }}</td>
                <td id="send_method_name{{ $indexKey }}">{{ $n->send_method_name }}</td>
                <td id="message_type_name{{ $indexKey }}">{{ $n->message_type_name }}</td>
                <td id="sender_type_name{{ $indexKey }}">
                    {{ $n->sender_type_name }}<br>
                    <span id="sender_name{{ $indexKey }}">@if(!empty($n->sender))(
                    @if ($n->sender_type == 'U')
                        <a href="/admin/user/{{ $n->sender_id }}" target="_blank" id="sender_id{{ $indexKey }}">{{ $n->sender_id }}</a>
                    @else
                        <span id="sender_id{{ $indexKey }}">{{ $n->sender_id }}</span>
                    @endif
                        / {{ $n->sender }})@endif</span>
                    <span id="sender_type{{ $indexKey }}" style="display: none;">{{ $n->sender_type }}</span>
                </td>
                <td id="receiver_type_name{{ $indexKey }}">
                    {{ $n->receiver_type_name }}<br>
                    <span id="receiver_name{{ $indexKey }}">@if(!empty($n->receiver))(
                        @if ($n->receiver_type == 'A')
                            <a href="/admin/user/{{ $n->receiver_id }}" target="_blank" id="receiver_id{{ $indexKey }}">{{ $n->receiver_id }}</a>
                        @elseif ($n->receiver_type == 'B' || $n->receiver_type == 'G')
                            <a href="/admin/groomer/{{ $n->receiver_id }}" target="_blank" id="receiver_id{{ $indexKey }}">{{ $n->receiver_id }}</a>
                        @else
                            <span id="receiver_id{{ $indexKey }}">{{ $n->receiver_id }}</span>
                        @endif
                        / {{ $n->receiver }})@endif</span>
                    <span id="receiver_type{{ $indexKey }}" style="display:none">{{ $n->receiver_type }}</span>
                </td>
                <td id="message{{ $indexKey }}">
                    {{ $n->message }}
                </td>
                <td id="date{{ $indexKey }}">{{ $n->cdate }}</td>
                <span id="message{{ $indexKey }}" style="display:none">{{ $n->message }}</span>
                <td><div class="btn-right btn btn-default btn-sm" onclick="view_detail({{ $indexKey }})">View Detail</div></td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="text-right">
        {{ $messages->appends(Request::except('page'))->links() }}
    </div>


    <!-- See Detail Modal Start -->
    <div class="modal" id="detail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Detail Message (ID:<span id="parent_id"></span>)</h4>
                </div>
                    <div class="modal-body">
                        <div id="detail_body"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-group padding-10" id="reply">
                            <div class="row padding-10">
                                <div class="col-xs-3">Reply Message</div>
                                <div class="col-xs-9">
                                    <textarea class="form-control" id="r_message" rows="5"></textarea>
                                </div>
                            </div>

                            <input type="hidden" id="r_parent_id"/>
                            <input type="hidden" id="r_receiver_type"/>
                            <input type="hidden" id="r_receiver_id"/>
                            <input type="hidden" id="r_message_type" value="M"/>
                            <input type="hidden" id="r_send_method" value="S"/>
                        </div>
                        <button class="btn btn-warning" type="button" id="open_reply">Reply</button>
                        <button class="btn btn-success" type="button" id="send_reply" onclick="reply()">Send</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
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
                    <h4 class="modal-title" id="myModalLabel">Send Message</h4>
                    <input type="hidden" id="g_id" value="{{ !empty($g_id) ? $g_id : '' }}"/>
                    <input type="hidden" id="g_name" value="{{ (!empty($g_name) ? $g_name : '') }}"/>
                    <input type="hidden" id="g_email" value="{{ (!empty($g_email) ? $g_email : '') }}"/>
                    <input type="hidden" id="g_phone" value="{{ (!empty($g_phone) ? $g_phone : '') }}"/>
                    <input type="hidden" id="u_id" value="{{ !empty($u_id) ? $u_id : '' }}"/>
                    <input type="hidden" id="u_name" value="{{ (!empty($u_name) ? $u_name : '') }}"/>
                    <input type="hidden" id="u_email" value="{{ (!empty($u_email) ? $u_email : '') }}"/>
                    <input type="hidden" id="u_phone" value="{{ (!empty($u_phone) ? $u_phone : '') }}"/>
                </div>
                <div class="form-group">
                    <div class="modal-body">
                        <div class="row padding-10">
                            <div class="col-xs-3">Send Method</div>
                            <div class="col-xs-8">
                                <select class="form-control" id="n_send_method">
                                    @foreach($send_methods as $k=>$v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row padding-10">
                            <div class="col-xs-3">Receiver</div>
                            <div class="col-xs-8">
                                @if (!empty($g_id))
                                    <select class="form-control" id="n_receiver_type" value="G" onchange="check_receiver('G')">
                                @elseif (!empty($u_id))
                                    <select class="form-control" id="n_receiver_type" value="A" onchange="check_receiver('A')">
                                @else
                                    <select class="form-control" id="n_receiver_type" onchange="check_receiver(this.value)">
                                @endif
{{--                                <select class="form-control" id="n_receiver_type" onchange="check_receiver(this.value)">--}}

                                    @foreach($receiver_modal_types as $k=>$v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row padding-10" id="receiver">
                            <div class="col-xs-3"></div>
                            <div class="col-xs-8">
                                <input type="text" name="receiver_str" id="receiver_str" class="form-control " placeholder="Enter Name or Phone # or Email">
                                <div class="btn btn-info btn-right search_receiver">search</div>
                                <div id="receiver_type" style="display:none;"></div>
                                <div id="search_result"></div>
                            </div>
                        </div>

                        <div class="row padding-10" id="groomer_group" hidden>
                            <div class="col-xs-3"></div>
                            <div class="col-xs-8">
                                <input type="checkbox" id="level1" value="1"/> Level 1
                                <input type="checkbox" id="level2" value="2"/> Level 2
                                <input type="checkbox" id="level3" value="3"/> Level 3
                                <input type="checkbox" id="level4" value="4"/> Level 4
                                <input type="checkbox" id="level5" value="5"/> Level 5
                            </div>
                            <div class="col-xs-3"></div>
                            <div class="col-xs-8">
                                <input type="checkbox" id="area1" value="NY"/> NY
                                <input type="checkbox" id="area2" value="NJ"/> NJ
                                <input type="checkbox" id="area3" value="CT"/> CT
                                <input type="checkbox" id="area4" value="PA"/> PA
                                <input type="checkbox" id="area5" value="CA"/> CA
                            </div>
                        </div>

                        <div class="row padding-10">
                            <div class="col-xs-3">Message Type</div>
                            <div class="col-xs-8">
                                <select class="form-control" id="n_message_type">
                                    @foreach($message_types as $k=>$v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{--<div class="row padding-10">--}}
                            {{--<div class="col-xs-3">Subject</div>--}}
                            {{--<div class="col-xs-8">--}}
                                {{--<input class="form-control" id="n_subject" />--}}
                            {{--</div>--}}
                        {{--</div>--}}
                        <div class="row padding-10">
                            <div class="col-xs-3">Message</div>
                            <div class="col-xs-8">
                                <textarea class="form-control" id="n_message" rows="5"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-warning" type="button" onclick="send()">Send</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Send Modal End -->
@stop