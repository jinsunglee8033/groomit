@extends('includes.admin_default')
@section('contents')

    <script type="text/javascript">
        window.onload = function () {
            $("#sdate").datetimepicker({
                format: 'YYYY-MM-DD'
            });
            $("#edate").datetimepicker({
                format: 'YYYY-MM-DD'
            });

            @if (session()->has('success'))
                $('#success').modal();
            @endif

            @if (count($errors) > 0)
                $('#error').modal();
            @endif
        };

        function search() {
            $('#frm_search').submit();
        }

        function load_user_detail(groomer_id, user_id) {
            myApp.showLoading();
            $.ajax({
                url: '/admin/profit-sharing/load-user-detail',
                data: {
                    groomer_id: groomer_id,
                    user_id: user_id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        var o = res.user_exception;

                        $('#nud_groomer_id').val(o.groomer_id);
                        $('#nud_orig_user_id').val('ID: ' + o.user_id + ', Name: ' + o.user.first_name + ' ' + o.user.last_name + ', E-Mail: ' + o.user.email + ', Phone: ' + o.user.phone);
                        $('#nud_user_search').val('');
                        $('#tbody_user_detail').empty();
                        $('#tbody_user_detail').append('<tr><td colspan="5">No Record Found</td></tr>');
                        $('[name=nud_package_id][value="' + o.package_id + '"]').prop('checked', true);
                        $('#nud_groomer_profit').val(o.groomer_profit);
                        $('#nud_last_updated').val(o.last_updated);

                        show_user_detail(groomer_id, user_id);

                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function save_user_detail(groomer_id, user_id) {
            var action = typeof user_id === 'undefined' ? 'add-user-exception' : 'update-user-exception';
            myApp.showLoading();
            $.ajax({
                url: '/admin/profit-sharing/' + action,
                data: {
                    groomer_id: groomer_id,
                    orig_user_id: user_id,
                    user_id: $('[name=nud_user_id]').val(),
                    package_id: $('[name=nud_package_id]:checked').val(),
                    groomer_profit: $('#nud_groomer_profit').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {

                        myApp.showSuccess('Your request has been successfully processed!', function () {
                            $('#div_user_detail').modal('hide');
                            load_user_exception_list(groomer_id);
                        });

                    } else {
                        alert(res.msg);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            })
        }

        function search_user(groomer_id) {
            myApp.showLoading();
            $.ajax({
                url: '/admin/profit-sharing/search-user',
                data: {
                    groomer_id: groomer_id,
                    user_search: $('#nud_user_search').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        $('#tbody_user_detail').empty();
                        $.each(res.users, function (i, o) {
                            var html = '<tr>';

                            html += '<td>' + o.user_id + '</td>';
                            html += '<td>' + o.first_name + ' ' + o.last_name + '</td>';
                            html += '<td>' + o.email + '</td>';
                            html += '<td>' + o.phone + '</td>';
                            html += '<td><input type="radio" name="nud_user_id" value="' + o.user_id + '"/>';

                            html += '</tr>';
                            $('#tbody_user_detail').append(html);
                        });

                        if (res.users.length == 0) {
                            var html = '<tr><td colspan="6">No Record Found</td></tr>';
                            $('#tbody_user_detail').append(html);
                        }
                    } else {
                        alert(res.msg);
                    }

                },
                error: function (jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function show_user_detail(groomer_id, user_id) {
            var mode = typeof user_id === 'undefined' ? 'new' : 'edit';
            if (mode == 'new') {
                $('.user-edit').hide();
                $('#user_title').text('New Groomer Profit Exception of User');
                $('#btn_save_user_detail').attr('onclick', 'save_user_detail(' + groomer_id + ')');
            } else {
                $('.user-edit').show();
                $('#user_title').text('Edit Groomer Profit Exception of User');
                $('#btn_save_user_detail').attr('onclick', 'save_user_detail(' + groomer_id + ', ' + user_id + ')');
            }

            $('#nud_groomer_id').val(groomer_id);
            $('#btn_search_user_detail').attr('onclick', 'search_user(' + groomer_id + ')');
            $('#div_user_detail').modal();
        }

        function load_user_exception_list(groomer_id) {
            myApp.showLoading();
            $.ajax({
                url: '/admin/profit-sharing/load-user-list',
                data: {
                    groomer_id: groomer_id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {

                        $('#btn_search_user_list').attr('onclick', 'load_user_exception_list(' + groomer_id + ')');
                        $('#btn_search_user_add').attr('onclick', 'show_user_detail(' + groomer_id + ')');
                        $('#tbody_user_list').empty();
                        $.each(res.users, function (i, o) {
                            var html = '<tr>';

                            html += '<td>' + o.groomer_id + '</td>';
                            html += '<td>' + o.user_id + '</td>';
                            html += '<td>' + o.first_name + ' ' + o.last_name + '</td>';
                            html += '<td><a href="javascript:load_user_detail(' + o.groomer_id + ', ' + o.user_id + ')">' + o.email + '</a></td>';
                            html += '<td>' + o.package + '</td>';
                            html += '<td>' + o.groomer_profit + '</td>';

                            html += '</tr>';

                            $('#tbody_user_list').append(html);
                        });

                        if (res.users.length == 0) {
                            $('#tbody_user_list').append('<tr><td colspan="5">No Record Found</td></tr>');
                        }


                        $('#div_user_list').modal();
                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function load_detail(id) {
            myApp.showLoading();
            $.ajax({
                url: '/admin/profit-sharing/load-detail',
                data: {
                    id: id
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {

                        var o = res.groomer_exception;
                        $('#n_groomer_id').val(o.groomer_id);

                        $('#tbody_groomer').empty();
                        $('#tbody_groomer').append('<tr><td colspan="6">No Record Found</td></tr>');

                        $('[name="n_package_id"][value="' + o.package_id + '"]').prop('checked', true);

                        $('#n_groomer_profit').val(o.groomer_profit);
                        $('#n_last_updated').val(o.last_updated);

                        show_detail(id);
                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function show_detail(id) {

            var mode = typeof id === 'undefined' ? 'new' : 'edit';
            if (mode === 'new') {
                $('#title').text('New Groomer Exception Detail');
                $('#n_groomer_search').val('');
                $('#n_groomer_profit').val('');

                $('#tbody_groomer').empty();
                $('#tbody_groomer').append('<tr><td colspan="6">No Record Found</td></tr>');

                $('.edit').hide();
                $('#btn_save').attr('onclick', 'save_detail()');
            } else {
                $('#title').text('Edit Groomer Exception Detail');
                $('.edit').show();
                $('#btn_save').attr('onclick', 'save_detail(' + id + ')');
            }

            $('#div_detail').modal();
        }

        function search_groomer() {
            myApp.showLoading();
            $.ajax({
                url: '/admin/profit-sharing/search-groomer',
                data: {
                    groomer_search: $('#n_groomer_search').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {
                        $('#tbody_groomer').empty();
                        $.each(res.groomers, function (i, o) {
                            var html = '<tr>';

                            html += '<td>' + o.groomer_id + '</td>';
                            html += '<td>' + o.first_name + ' ' + o.last_name + '</td>';
                            html += '<td>' + o.email + '</td>';
                            html += '<td>' + o.phone + '</td>';
                            html += '<td>' + o.mobile_phone + '</td>';
                            html += '<td><input type="radio" name="n_groomer_id" value="' + o.groomer_id + '"/>';

                            html += '</tr>';
                            $('#tbody_groomer').append(html);
                        });

                        if (res.groomers.length == 0) {
                            var html = '<tr><td colspan="6">No Record Found</td></tr>';
                            $('#tbody_groomer').append(html);
                        }

                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

        function save_detail(groomer_id) {
            var action = typeof groomer_id === 'undefined' ? 'add-exception' : 'update-exception';

            myApp.showLoading();
            $.ajax({
                url: '/admin/profit-sharing/' + action,
                data: {
                    orig_groomer_id: groomer_id,
                    groomer_id: $('[name=n_groomer_id]:checked').val(),
                    package_id: $('[name=n_package_id]:checked').val(),
                    groomer_profit: $('#n_groomer_profit').val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    myApp.hideLoading();
                    if ($.trim(res.msg) === '') {

                        myApp.showSuccess('Your request has been processed successfully!', function () {
                            search();
                        });

                    } else {
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

    @if (session()->has('success'))
        <div id="success" class="modal fade " tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false"
             style="display:block;">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Success</h4>
                    </div>
                    <div class="modal-body">
                        <p>
                            {{ session('success') }}
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (count($errors) > 0)
        <div id="error" class="modal fade " tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false"
             style="display:block;">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Error</h4>
                    </div>
                    <div class="modal-body">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png"/>PROFIT
                SHARING SETUP</h3>
    </div>

    <div class="container-fluid">

        <div class="well filter">
            <form class="form-horizontal" method="post" action="/admin/profit-sharing/update-default">
                {{ csrf_field() }}

                @foreach ($share_setups as $s)
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="col-md-8 control-label">Default Groomer Profit - {{ strtoupper($s->pet_type)
                             }} - {{ $s->prod_name }} (%):</label>
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="groomer_profit_{{ $s->prod_id }}"
                                       value="{{ $s->groomer_profit }}"/>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                <div class="row">
                    <div class="col-md-8 text-right">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                @if (\App\Lib\Helper::get_action_privilege('profitshare_setup_update', 'Profit Share Setup Update'))
                                <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <h3>Groomer Profit Exception</h3>
        <hr class="">

        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/profit-sharing"
                  onsubmit="myApp.showLoading()">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">ID</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="id" value="{{ $id }}"/>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Name</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="name" value="{{ $name }}"/>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Phone</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="phone" value="{{ $phone }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Email</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="email" value="{{ $email }}"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-md-offset-4 text-right">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                @if (\App\Lib\Helper::get_action_privilege('profitshare_setup_search', 'Profit Share Setup Search'))
                                <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                                @endif
                                @if (\App\Lib\Helper::get_action_privilege('profitshare_setup_add_new', 'Profit Share Setup Add New'))
                                <button type="button" class="btn btn-info btn-sm" onclick="show_detail()">Add New
                                </button>
                                @endif
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
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Package</th>
                <th>Profit(%)</th>
                <th>Last.Updated</th>
                <th>User.Exceptions</th>
            </tr>
            </thead>
            @foreach($groomer_exceptions as $o)
                <tr>
                    <td>{{ $o->groomer_id }}</td>
                    <td>{{ $o->name }}</td>
                    <td><a href="javascript:load_detail('{{ $o->id }}')">{{ $o->email }}</a></td>
                    <td>{{ $o->phone }}</td>
                    <td>{{ $o->package }}</td>
                    <td>{{ $o->groomer_profit }}</td>
                    <td>{{ $o->last_updated }}</td>
                    <td>
                        {{ $o->user_exception_count }}
                        @if (\App\Lib\Helper::get_action_privilege('profitshare_setup_view_list', 'Profit Share Setup View List'))
                        <button class="btn btn-sm btn-info" onclick="load_user_exception_list({{ $o->groomer_id }})">
                            View List
                        </button>
                        @endif
                    </td>
                </tr>
            @endforeach
            <tbody>
            </tbody>
        </table>

        <div class="modal" id="div_detail" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">x</span></button>
                        <h4 class="modal-title" id="title">New Groomer Profit Exception</h4>
                    </div>
                    <div class="modal-body">

                        <form id="frm_transaction" class="form-horizontal filter" method="post" style="padding:15px;">
                            <div class="form-group edit">
                                <label class="col-sm-3 control-label">Current Groomer</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="n_groomer_id" readonly/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Find Groomer</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="n_groomer_search"
                                           placeholder="Name, Email or Phone number"/>
                                </div>
                                <div class="col-sm-3">
                                    <button type="button" class="btn btn-sm btn-primary" onclick="search_groomer()">
                                        Search
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-9 col-sm-offset-3">
                                    <table class="table table-responsive table-condensed table-bordered">
                                        <thead class="thead-default">
                                        <tr>
                                            <th>Groomer.ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Mobile.Phone</th>
                                            <th>Select</th>
                                        </tr>
                                        </thead>
                                        <tbody id="tbody_groomer">
                                        <tr>
                                            <td colspan="6">No Record Found</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label required">Package</label>
                                <div class="col-sm-6">
                                    @foreach ($share_setups as $s)
                                    <label>
                                        <input type="radio" id="n_pacakge_id" name="n_package_id" value="{{ $s->prod_id
                                        }}"/> {{ strtoupper($s->pet_type) . '-' . $s->prod_name }} &nbsp;&nbsp;
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label required">Groomer Profit(%)</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="n_groomer_profit"/>
                                </div>
                            </div>
                            <div class="form-group edit">
                                <label class="col-sm-3 control-label">Last.Updated</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="n_last_updated" readonly/>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer" style="margin-right:15px;">
                        <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="btn_save" onclick="save_detail()">Save
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal" id="div_user_list" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">x</span></button>
                        <h4 class="modal-title" id="title">User Exception List</h4>
                    </div>
                    <div class="modal-body">

                        <form class="form-horizontal well filter" method="post" style="padding:15px;">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Find User</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="nul_search"
                                           placeholder="Name, Email or Phone number"/>
                                </div>
                                <div class="col-sm-3">
                                    <button type="button" class="btn btn-sm btn-primary" id="btn_search_user_list">
                                        Search
                                    </button>
                                    <button type="button" class="btn btn-sm btn-info" id="btn_search_user_add">Add
                                        New
                                    </button>
                                </div>
                            </div>
                        </form>

                        <table class="table table-responsive table-condensed table-bordered">
                            <thead class="thead-default">
                            <tr>
                                <th>Groomer.ID</th>
                                <th>User.ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Package</th>
                                <th>Groomer.Profit(%)</th>
                            </tr>
                            </thead>
                            <tbody id="tbody_user_list">
                            <tr>
                                <td colspan="15">No Record Found</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer" style="margin-right:15px;">
                        <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal" id="div_user_detail" tabindex="-1" role="dialog" data-backdrop="static"
             data-keyboard="false">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">x</span></button>
                        <h4 class="modal-title" id="user_title">New Groomer Profit Exception of User</h4>
                    </div>
                    <div class="modal-body" style="overflow-y:scroll; max-height:600px;">

                        <form id="frm_transaction" class="form-horizontal filter" method="post" style="padding:15px;">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Groomer ID</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="nud_groomer_id" readonly/>
                                </div>
                            </div>
                            <div class="form-group user-edit">
                                <label class="col-sm-3 control-label">Current User</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="nud_orig_user_id" readonly/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Find User</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="nud_user_search"
                                           placeholder="Name, Email or Phone number"/>
                                </div>
                                <div class="col-sm-3">
                                    <button type="button" class="btn btn-sm btn-primary" id="btn_search_user_detail">
                                        Search
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-9 col-sm-offset-3">
                                    <table class="table table-responsive table-condensed table-bordered">
                                        <thead class="thead-default">
                                        <tr>
                                            <th>User.ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Select</th>
                                        </tr>
                                        </thead>
                                        <tbody id="tbody_user_detail">
                                        <tr>
                                            <td colspan="5">No Record Found</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label required">Package</label>
                                <div class="col-sm-6">
                                    @foreach ($share_setups as $s)
                                        <label>
                                            <input type="radio" id="nud_package_id" name="nud_package_id" value="{{ $s->prod_id
                                        }}"/> {{ strtoupper($s->pet_type) . '-' . $s->prod_name }} &nbsp;&nbsp;
                                        </label>
                                    @endforeach

{{--                                    <label>--}}
{{--                                        <input type="radio" id="nud_package_id" name="nud_package_id" value="1"/> Gold--}}
{{--                                    </label>--}}
{{--                                    <label>--}}
{{--                                        <input type="radio" id="nud_package_id" name="nud_package_id" value="2"/> Silver--}}
{{--                                    </label>--}}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label required">Groomer Profit(%)</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="nud_groomer_profit"/>
                                </div>
                            </div>
                            <div class="form-group user-edit">
                                <label class="col-sm-3 control-label">Last.Updated</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="nud_last_updated" readonly/>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer" style="margin-right:15px;">
                        <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="btn_save_user_detail">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
