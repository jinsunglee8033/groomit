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

            $("#nc_sdate").datetimepicker({
                format: 'YYYY-MM-DD'
            });
            $("#nc_edate").datetimepicker({
                format: 'YYYY-MM-DD'
            });

            $(function () {
                $('[data-toggle="tooltip"]').tooltip()
            })
        };

        function new_order() {
            $('#div_new_order').modal();
        }

        function hide_new_order_modal() {
            $('#div_new_order').modal("hide");
        }

        function save_new_order() {

            var data = [];

            @foreach ($products as $p)
            var qty = $('#prod_qty_{{ $p->id }}').val()
            if (qty > 0) {
                data.push({
                    prod_id: {{ $p->id }},
                    qty: qty
                });
            }
            @endforeach

            $.ajax({
                url: '/admin/order/save',
                data: {
                    _token: '{!! csrf_token() !!}',
                    groomer_id: $('#ncd_groomer_id').val(),
                    data: data
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if (res.code == '0') {

                        myApp.showSuccess('Your request has been processed successfully!', function() {
                            window.location.href = '/admin/order';
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
        }

        function unship(value) {

            var delivery_company = value.split('-')[0];
            var tracking_no = value.split('-')[1];

            $.ajax({
                url: '/admin/order/unship',
                data: {
                    _token: '{!! csrf_token() !!}',
                    delivery_company: delivery_company,
                    tracking_no: tracking_no
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if (res.code == '0') {
                        myApp.showSuccess('Your request has been processed successfully!', function() {

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
        }

        function start_kit(item){

            if(item.value == 'kit_1'){
                $('#prod_qty_42').val('1');
                $('#prod_qty_57').val('1');
                $('#prod_qty_28').val('1');
                $('#prod_qty_30').val('1');
                $('#prod_qty_36').val('1');
                $('#prod_qty_38').val('1');
                $('#prod_qty_41').val('1');
                $('#prod_qty_39').val('1');
                $('#prod_qty_43').val('15');
                $('#prod_qty_58').val('1');
                $('#prod_qty_45').val('1');
                $('#prod_qty_29').val('1');
                $('#prod_qty_37').val('1');
                $('#prod_qty_40').val('15');
            }
        }

        function order_reset(){
            $('[id^="prod_qty"]').val("");
        }

    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png"/>GROOMERS
        </h3>
    </div>

    <div class="container-fluid">

        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/order">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel"/>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Date</label>
                            <div class="col-md-8">
                                <input type="text" style="width:100px; float:left;" class="form-control" id="sdate"
                                       name="sdate" value="{{ old('sdate', $sdate) }}"/>
                                <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="edate" name="edate" value="{{ old('edate', $edate) }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Groomer</label>
                            <div class="col-md-8">
                                <select name="groomer_id" class="form-control">
                                    <option value="">Please Select</option>
                                    @foreach ($groomers as $o)
                                        <option value="{{ $o->groomer_id }}">{{ $o->first_name . ' ' . $o->last_name }}[{{$o->status}}]</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Status</label>
                            <div class="col-md-8">
                                <select class="form-control" name="status"
                                        data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                    <option value="" {{ old('status', $status) == '' ? 'selected' : '' }}>All</option>
                                    <option value="N" {{ old('status', $status) == 'N' ? 'selected' : '' }}>New
                                    </option>
                                    <option value="S" {{ old('status', $status) == 'S' ? 'selected' : '' }}>Shipped
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">

                        </div>
                    </div>
                    <div class="col-md-8 col-md-offset-4 text-right">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                @if (!in_array('order_search', session('url')))
                                <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                                @endif
                                @if (!in_array('order_new_order', session('url')))
                                <button type="button" class="btn btn-warning btn-sm" onclick="new_order()">New
                                    Order</button>
                                @endif
                                @if (!in_array('order_order_history',session('url')))
                                <a type="button" class="btn btn-success btn-sm" href="/admin/order/history">Order History</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <table class="table table-bordered display" cellspacing="0" width="100%">
            <h4>Groomer Orders</h4>
            <thead>
                <tr>
                    <th>Groomer</th>
                    <th>Address & Phone</th>
                    <th>Shipping</th>
                    <th>Order#</th>
                    <th>Category</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Created</th>
                    <th>Shipped</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($orders as $o)
                <tr>
                    <td rowspan="{{ count($o->details) }}">
                        <a href="/admin/groomer/{{ $o->groomer->groomer_id }}">
                            {{ $o->groomer->first_name }} {{ $o->groomer->last_name }}
                        </a>
                    </td>
                    <td rowspan="{{ count($o->details) }}">{{ $o->groomer->street . ', ' . $o->groomer->city . ', ' .
                    $o->groomer->state . ' ' . $o->groomer->zip
                    }} <br> Tel: {{ $o->groomer->phone }}</td>
                    <td rowspan="{{ count($o->details) }}">
                        @if (empty($o->tracking_no))
                            <button type="button" onclick="call_edit_modal({{ $o->groomer->groomer_id }})">Edit</button>
                        @else
                        {{ $o->delivery_company }} <br>
                            @if ($o->delivery_company == 'Fedex')
                            <a target="_blank" href="https://www.fedex.com/apps/fedextrack/?action=track&action=track&tracknumbers={{ $o->tracking_no }}">{{ $o->tracking_no }}</a>
                                <br><button type="button" onclick="unship('{{ $o->delivery_company }}-{{$o->tracking_no}}')">UnShip</button>
                            @else
                            {{ $o->tracking_no }}
                                <br><button type="button" onclick="unship('{{ $o->delivery_company }}-{{$o->tracking_no}}')">UnShip</button>
                            @endif
                        @endif

                            <form action='/admin/order/print' method='post' target="_blank">
                                {{ csrf_field() }}
                                <input type="hidden" id="groomer_info" name="groomer_info" value="{{ $o->groomer }}"/>
                                <input type="hidden" id="details" name="details" value="{{ $o->details }}"/>
                                <input type="hidden" id="order" name="order" value="{{ $o }}"/>
                                <input type="submit" value="Print">
                            </form>

                    </td>
                @foreach ($o->details as $d)
                    <td>{{ $d->id }}</td>
                    <td>{{ $d->prod_type }}</td>
                    <td>{{ $d->prod_name }}</td>
                    @if (!empty($o->tracking_no))
                    <td>{{ $d->qty }}</td>
                    @else
                    <td>
                        <input type="number" id="o_qty_{{ $d->id }}" value="{{ $d->qty }}"
                               onchange="order_qty_updated({{ $d->id }})" style="max-width: 60px;">
                        <small><span id="msg_qty_{{ $d->id }}"></span></small>
                    </td>
                    @endif
                    <td>{{ $d->status == 'N' ? 'New' : 'Shipped' }}</td>
                    <td>{{ $d->created_by }}</td>
                    <td>{{ $d->cdate }}</td>
                    <td>{{ $d->ship_date }}</td>
                </tr>
                <tr>
                @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>


        <div class="text-right">
            {{ $orders->appends(Request::except('page'))->links() }}
        </div>
    </div>

    <div id="div_new_order" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         style="display:none;">
        <div class="modal-dialog" role="document" style="width:80%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Product Order</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="col-md-2 control-label">Groomer</label>
                                <div class="col-md-4">
                                    <select id="ncd_groomer_id" name="ncd_groomer_id" class="form-control">
                                        <option value="">Please Select</option>
                                        @foreach ($groomers as $o)
                                            <option value="{{ $o->groomer_id }}">{{ $o->first_name . ' ' . $o->last_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12" style="margin-top: 8px;">
                            <div class="form-group">
                                <label class="col-md-2 control-label">Start Kit</label>
                                <div class="col-md-4">
                                    <select id="start_kit" name="start_kit" class="form-control" onchange="start_kit(this)">
                                        <option value="">Please Select</option>
                                        <option value="kit_1">Start Kit I</option>
{{--                                        <option value="kit_2">Start Kit II</option>--}}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        @foreach ($products as $p)
                            <div class="col-md-6" style="margin-top: 8px;">
                                <div class="form-group">
                                    <div class="col-md-9" style="vertical-align:middle;text-align: right;">
                                        <small><i>{{ $p->prod_type }}</i></small><br>
                                        <strong>{{ $p->prod_name }}</strong> ({{ $p->size }}):
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" class="prod_qty" id="prod_qty_{{ $p->id }}"/>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" onclick="hide_new_order_modal()">Close</button>
                    <button type="button" class="btn btn-info" onclick="order_reset()">Reset</button>
                    <button type="button" class="btn btn-primary" onclick="save_new_order()">Submit</button>
                </div>
            </div>
        </div>
    </div>

    @if (!empty($new_orders))
    @foreach ($new_orders as $new_order)
    <div id="div_edit_shipping_{{ $new_order->groomer_id }}" class="modal fade " tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         style="display:none;">
        <input type="hidden" id="sdate" name="sdate" value="{{ $sdate }}">
        <input type="hidden" id="edate" name="sdate" value="{{ $edate }}">
        <input type="hidden" id="status" name="status" value="{{ $status }}">
        <div class="modal-dialog" role="document" style="width:50%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Shipping information edit</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" onclick="hide_edit_shipping_modal({{ $new_order->groomer_id }})">Close</button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered display" cellspacing="0">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="groomer_{{ $new_order->groomer_id }}_order_all"
                                           onclick="check_order_all({{ $new_order->groomer_id }})
                                            "></th>
                                <th>Order#</th>
                                <th>Category</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_groomer_order_orig" class="tbody_groomer_order_orig">
                        @foreach ($new_order->details as $n)
                        <tr>
                            <td><input type="checkbox" class="groomer_{{ $new_order->groomer_id }}" id="groomer_{{ $new_order->groomer_id }}_order_id_{{$n->id }}"></td>
                            <td>{{ $n->id }}</td>
                            <td>{{ $n->prod_type }}</td>
                            <td>{{ $n->prod_name . ' - ' . $n->size }}</td>
                            <td id="new_order_qty_{{ $n->id }}">{{ $n->qty }}</td>
                            <td><button type="button" class="btn btn-primary" onclick="delete_order({{ $n->id }} + '-' + {{ $new_order->groomer_id }})">Del</button></td>
                        </tr>
                        @endforeach
                        </tbody>

                        <tbody id="tbody_groomer_order" class="tbody_groomer_order">
                        </tbody>
                    </table>

                    <table class="table table-bordered display" cellspacing="0">
                        <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select id="add_order_id_{{ $new_order->groomer_id }}" class="form-control">
                                        <option value="0">Please Select</option>
                                        @foreach ($products as $p)
                                            <option value="{{ $p->id }}">{{ $p->prod_type . ' - ' . $p->prod_name . ' - ' . $p->size }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" id="add_order_qty_{{ $new_order->groomer_id }}" class="form-control"></td>
                                <td>
                                    <button type="button" class="btn btn-primary" onclick="add_order({{ $new_order->groomer_id }})">Add</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="row">
                        <div class="col-md-6" style="margin-top: 8px;">
                            <div class="form-group">
                                <select id="order_delivery_company_{{ $new_order->groomer_id }}" class="form-control">
                                    <option value="Fedex">Fedex</option>
                                    <option value="UPS">UPS</option>
                                    <option value="USPS">USPS</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" style="margin-top: 8px;">
                            <div class="form-group">
                                <input type="text" id="order_tracking_no_{{ $new_order->groomer_id }}" class="form-control">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" onclick="hide_edit_shipping_modal({{ $new_order->groomer_id }})">Close</button>
                    <button type="button" class="btn btn-primary" onclick="save_shipping_info({{ $new_order->groomer_id }})">Submit</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <script type="text/javascript">

        function edit_shipping(groomer_id) {

            var sdate = $('#sdate').val();
            var edate = $('#edate').val();
            var status = $('#status').val();

            $.ajax({
                url: '/admin/order/bind_orders',
                data: {
                    _token: '{!! csrf_token() !!}',
                    groomer_id: groomer_id,
                    sdate: sdate,
                    edate: edate,
                    status: status
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if (res.code == '0') {

                        bind_orders(res.new_orders);
                        // $('#div_edit_shipping_' + groomer_id).modal();

                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            })

            $('#div_edit_shipping_' + groomer_id).modal();
        }

        function bind_orders(new_orders) {

            $('.tbody_groomer_order').empty();
            $('.tbody_groomer_order_orig').empty();
            $.each(new_orders, function (i, o) {
                var order = o.details;
                $.each(order, function (i, o) {
                    var html = '<tr>';
                    html += '<td>' + '<input type="checkbox" class="groomer_'+o.groomer_id+'" id="groomer_'+o.groomer_id+'_order_id_'+o.id+'"></td>';
                    html += '<td>' + o.id + '</td>';
                    html += '<td>' + o.prod_type + '</td>';
                    html += '<td>' + o.prod_name + ' - ' + o.size + '</td>';
                    html += '<td>' + o.qty + '</td>';
                    html += '<td>' + '<button type="button" class="btn btn-primary" onclick="delete_order('+ o.id + " + '-' + " + o.groomer_id +')">Del</button>' + '</td>';
                    html += '</tr>';
                    $('.tbody_groomer_order').append(html);
                });
            });
        }

        function call_edit_modal(groomer_id) {
            $('#div_edit_shipping_' + groomer_id).modal();
        }

        function hide_edit_shipping_modal(groomer_id) {
            $('#div_edit_shipping_' + groomer_id).modal('hide');
        }

        function check_order_all(groomer_id) {

        if ($('#groomer_' + groomer_id + '_order_all').prop('checked')) {
                $('.groomer_'+groomer_id).prop('checked', true);
            } else {
                $('.groomer_'+groomer_id).prop('checked', false);
            }
        }

        function save_shipping_info(groomer_id) {

            var data = [];

            @foreach ($new_orders as $new_order)
            @foreach ($new_order->details as $p)
            if ($('#groomer_' + groomer_id + '_order_id_{{ $p->id }}').prop('checked')) {
                data.push({{ $p->id }});
            }
            @endforeach
            @endforeach

            if (data.length < 1) {
                alert('Please select order !!');
                return;
            }

            $.ajax({
                url: '/admin/order/shipping/save',
                data: {
                    _token: '{!! csrf_token() !!}',
                    delivery_company: $('#order_delivery_company_' + groomer_id).val(),
                    tracking_no: $('#order_tracking_no_' + groomer_id).val(),
                    groomer_id: groomer_id,
                    data: data
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if (res.code == '0') {
                        myApp.showSuccess('Your request has been processed successfully!', function() {
                            window.location.href = '/admin/order';
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
        }

        function order_qty_updated(order_id) {

            var qty = $('#o_qty_' + order_id).val();
            $.ajax({
                url: '/admin/order/update',
                data: {
                    _token: '{!! csrf_token() !!}',
                    order_id: order_id,
                    qty: qty
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    $('#msg_qty_' + order_id).text(res.msg);

                    if (res.code == '0') {
                        $('#new_order_qty_' + order_id).text(qty);
                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            })
        }

        function add_order(groomer_id) {

            var product_id  = $('#add_order_id_'+ groomer_id).val();
            var qty         = $('#add_order_qty_'+ groomer_id).val();
            var sdate       = $('#sdate').val();
            var edate       = $('#edate').val();
            var data = [];

            if(product_id == '0'){
                alert("Please select Product");
                return;
            }

            if(qty == ''){
                alert("Please select QTY");
                return;
            }

            data.push({
                prod_id: product_id,
                qty: qty
            });

            $.ajax({
                url: '/admin/order/add',
                data: {
                    _token: '{!! csrf_token() !!}',
                    groomer_id: groomer_id,
                    sdate: sdate,
                    edate: edate,
                    data: data
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if (res.code == '0') {
                        myApp.showSuccess('Your request has been processed successfully!', function() {

                        bind_orders(res.new_orders);

                        // edit_shipping(grommer_id);
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
        }

        function delete_order(value) {

            var order_id = value.split('-')[0];
            var groomer_id = value.split('-')[1];
            var sdate       = $('#sdate').val();
            var edate       = $('#edate').val();
            var data = [];

            $.ajax({
                url: '/admin/order/delete',
                data: {
                    _token: '{!! csrf_token() !!}',
                    groomer_id: groomer_id,
                    order_id: order_id,
                    sdate: sdate,
                    edate: edate,
                    data: data
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if (res.code == '0') {
                        myApp.showSuccess('Your request has been processed successfully!', function() {

                            bind_orders(res.new_orders);

                            // edit_shipping(grommer_id);
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
        }

    </script>
    @endif

@stop
