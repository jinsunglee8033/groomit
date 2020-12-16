@extends('includes.admin_default')
@section('contents')
    <script type="text/javascript">

        window.onload = function () {
            $('#profile_photo').change(function () {
                previewImage(this, 'img_profile_photo');
            });

            $('#prevweek').hide();


            $('#update_groomer_availability').click(function () {
                update_groomer_availability();
            });

            $("#sdate").datetimepicker({
                format: 'YYYY-MM-DD'
            });
            $("#edate").datetimepicker({
                format: 'YYYY-MM-DD'
            });

        };

        function get_current_earning() {
            hide_search_box();

            $('#api_link').val('/api/v2/groomer/get-current-earning');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/get-current-earning',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function get_earning_history() {
            hide_search_box();
            $('#get_earning_history_box').show();
            $('#date_box').show();

            $('#api_link').val('/api/v2/groomer/get-earning-history');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val(),
                type: $('#earning_history_type').val(),
                from: $('#sdate').val(),
                to: $('#edate').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/get-earning-history',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function get_earning_detail() {
            hide_search_box();
            $('#get_earning_detail_box').show();
            $('#date_box').show();

            $('#api_link').val('/api/v2/groomer/get-earning-detail');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val(),
                from: $('#sdate').val(),
                to: $('#edate').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/get-earning-detail',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function get_open_appointments() {
            hide_search_box();
            $('#get_open_appointments_box').show();

            $('#api_link').val('/api/v2/groomer/open-appointment/list');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val()
                // distance_type: $('#distance_type').val(),
                // x: $('#x').val(),
                // y: $('#y').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/open-appointment/list',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function get_open_appointment_detail() {
            hide_search_box();
            $('#get_open_appointment_detail_box').show();

            $('#api_link').val('/api/v2/groomer/open-appointment/detail');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val(),
                appointment_id: $('#appointment_id').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/open-appointment/detail',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function get_availability() {
            hide_search_box();
            $('#get_availability_box').show();
            $('#date_box').show();

            $('#api_link').val('/api/v2/groomer/availability/get');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val(),
                sdate: $('#sdate').val(),
                edate: $('#edate').val(),
                distance_type: $('#distance_type').val(),
                x:$('#x').val(),
                y:$('#y').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/availability/get',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function set_availability() {
            hide_search_box();
            $('#set_availability_box').show();

            $('#api_link').val('/api/v2/groomer/availability/set');
        }

        function set_availability_submit() {

            var v = $('#availabilities').val();
            var availabilities = JSON.parse(v);

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val(),
                availabilities: availabilities
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/availability/set',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function get_upcoming() {
            hide_search_box();

            $('#api_link').val('/api/v2/groomer/upcoming/list');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/upcoming/list',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function get_upcoming_detail(){
            hide_search_box();
            $('#get_upcoming_detail_box').show();
            $('#api_link').val('/api/v2/groomer/upcoming/detail');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val(),
                appointment_id: $('#appointment_id_u').val(),
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/upcoming/detail',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }


        function get_history() {
            hide_search_box();
            $('#get_history_box').show();
            $('#date_box').show();

            $('#api_link').val('/api/v2/groomer/history/list');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val(),
                sdate: $('#sdate').val(),
                edate: $('#edate').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/history/list',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function get_history_detail() {
            hide_search_box();
            $('#get_history_detail_box').show();

            $('#api_link').val('/api/v2/groomer/history/detail');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val(),
                appointment_id: $('#appointment_id_h').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/history/detail',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function get_groomer_arrived() {
            hide_search_box();
            $('#get_groomer_arrived_box').show();

            $('#api_link').val('/api/v2/groomer/profile/arrived');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val(),
                appointment_id: $('#appointment_id_arrived').val(),
                x: $('#x').val(),
                y: $('#y').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/profile/arrived',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                        $('#appointment_id_arrived').val('');
                        $('#x').val('');
                        $('#y').val('');
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }
        function appt_complete() {
            hide_search_box();
            $('#appt_complete_box').show();

            $('#api_link').val('/api/v2/groomer/service/complete');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val(),
                appointment_id: $('#appointment_id_complete').val(),
                x: $('#x_comp').val(),
                y: $('#y_comp').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/service/complete',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                        $('#appointment_id_arrived').val('');
                        $('#x').val('');
                        $('#y').val('');
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

         function get_profile() {
            hide_search_box();
            $('#get_profile_box').show();

            $('#api_link').val('/api/v2/profile/get');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/profile/get',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        // $.each(res, function(key, value){
                        //     out_table += "<h3>" + key + "</h3>";
                        //     out_table += get_out_table(value);
                        // });
                        //
                        // $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function get_info() {
            hide_search_box();
            $('#get_info_box').show();

            $('#api_link').val('/api/v2/groomer/dashboard/get-info');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/dashboard/get-info',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        // $.each(res, function(key, value){
                        //     out_table += "<h3>" + key + "</h3>";
                        //     out_table += get_out_table(value);
                        // });
                        //
                        // $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function get_groomer_categories() {
            hide_search_box();
            $('#get_groomer_categories_box').show();

            $('#api_link').val('/api/v2/groomer/product/category/get');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/product/category/get',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);

                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }
        function get_groomer_products() {
            hide_search_box();
            $('#get_groomer_products_box').show();

            $('#api_link').val('/api/v2/groomer/product/get');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val(),
                prod_type: $('#prod_type').val(),
                search: $('#search').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/product/get',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);

                        $('#prod_type').val('');
                        $('#search').val('');

                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function add_product_cart(v) {

            hide_search_box();

            $('#add_product_cart_box').show();

            $('#api_link').val('/api/v2/groomer/product/cart/add');

            if(v=='submit') {
                var data = {
                    api_key: $('#api_key').val(),
                    token: $('#token').val(),
                    pr_id: $('#product_id').val(),
                    qty: $('#qty').val()
                };

                document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

                $.ajax({
                    url: '/api/v2/groomer/product/cart/add',
                    data: data,
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function (res) {
                        if ($.trim(res.msg) === '') {
                            var out_table = "";

                            $.each(res, function (key, value) {
                                out_table += "<h3>" + key + "</h3>";
                                out_table += get_out_table(value);
                            });

                            $('#out_area_box').append(out_table);

                            document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);

                            $('#product_id').val('');
                            $('#qty').val('');
                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                });
            }
        }

        function delete_product_cart(v) {
            hide_search_box();
            $('#delete_product_cart_box').show();

            $('#api_link').val('/api/v2/groomer/product/cart/delete');
            if(v=='submit') {
                var data = {
                    api_key: $('#api_key').val(),
                    token: $('#token').val(),
                    del_pr_id: $('#del_pr_id').val()
                };

                document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

                $.ajax({
                    url: '/api/v2/groomer/product/cart/delete',
                    data: data,
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function (res) {
                        if ($.trim(res.msg) === '') {
                            var out_table = "";

                            $.each(res, function (key, value) {
                                out_table += "<h3>" + key + "</h3>";
                                out_table += get_out_table(value);
                            });

                            $('#out_area_box').append(out_table);

                            document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);

                            $('#del_pr_id').val('');
                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                });
            }
        }

        function get_product_cart(v) {
            hide_search_box();
            $('#get_product_cart_box').show();

            $('#api_link').val('/api/v2/groomer/product/cart/get');

            if(v=='submit') {
                var data = {
                    api_key: $('#api_key').val(),
                    token: $('#token').val()
                };

                document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

                $.ajax({
                    url: '/api/v2/groomer/product/cart/get',
                    data: data,
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function (res) {
                        if ($.trim(res.msg) === '') {
                            var out_table = "";

                            $.each(res, function (key, value) {
                                out_table += "<h3>" + key + "</h3>";
                                out_table += get_out_table(value);
                            });

                            $('#out_area_box').append(out_table);

                            document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                });
            }
        }

        // do it later
        function create_groomer_order(v) {
            hide_search_box();
            $('#create_groomer_order_box').show();

            $('#api_link').val('/api/v2/groomer/product/order/create');

            if(v=='submit') {
                var data = {
                    api_key: $('#api_key').val(),
                    token: $('#token').val()
                };

                document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);


                $.ajax({
                    url: '/api/v2/groomer/product/order/create',
                    data: data,
                    cache: false,
                    type: 'post',
                    dataType: 'json',
                    success: function (res) {
                        if ($.trim(res.msg) === '') {
                            var out_table = "";

                            $.each(res, function (key, value) {
                                out_table += "<h3>" + key + "</h3>";
                                out_table += get_out_table(value);
                            });

                            $('#out_area_box').append(out_table);

                            document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);

                            $('#product_id').val('');
                            $('#qty').val('');
                        } else {
                            myApp.showError(res.msg);
                        }
                    }
                });
            }
        }

        function get_order_list() {
            hide_search_box();
            $('#get_product_order_list_box').show();

            $('#api_link').val('/api/v2/groomer/product/order/list');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val(),
                sdate: $('#sdate').val(),
                edate: $('#edate').val(),
                status: $('#status').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/product/order/list',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);

                        $('#product_id').val('');
                        $('#qty').val('');
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function get_order_detail() {
            hide_search_box();
            $('#get_product_order_detail_box').show();

            $('#api_link').val('/api/v2/groomer/product/order/detail');

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val(),
                pr_id: $('#product_id').val(),
                qty: $('#qty').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/product/order/detail',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);

                        $('#product_id').val('');
                        $('#qty').val('');
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function login() {
            hide_search_box();
            $('#login_box').show();

            $('#api_link').val('/api/v2/groomer/login');

            var email = $('#email').val();
            if ($.trim(email) == '') {
                return;
            }
            var pw = $('#pw').val();
            if ($.trim(pw) == '') {
                return;
            }

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val(),
                email: email,
                passwd: pw,
                login_channel: 'i'
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/login',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }
        function login_as() {
            hide_search_box();
            $('#login_as_box').show();

            $('#api_link').val('/api/v2/groomer/login_as');

            var groomer_id = $('#groomer_id').val();
            if ($.trim(groomer_id) == '') {
                return;
            }

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val(),
                groomer_id: groomer_id
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/login_as',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }
        function logout() {
            hide_search_box();
            $('#logout_box').show();

            $('#api_link').val('/api/v2/groomer/logout');


            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/logout',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }
        function update_device_token() {
            hide_search_box();
            $('#device_token_box').show();

            $('#api_link').val('/api/v2/groomer/update_device_token');

            var device_token = $('#device_token').val();
            if (device_token == '') return ;

            var data = {
                api_key: $('#api_key').val(),
                token: $('#token').val(),
                device_token: $('#device_token').val()
            };

            document.getElementById("input_json").innerHTML = JSON.stringify(data, undefined, 2);

            $.ajax({
                url: '/api/v2/groomer/update_device_token',
                data: data,
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    if ($.trim(res.msg) === '') {
                        var out_table = "";

                        $.each(res, function(key, value){
                            out_table += "<h3>" + key + "</h3>";
                            out_table += get_out_table(value);
                        });

                        $('#out_area_box').append(out_table);

                        document.getElementById("output_json").innerHTML = JSON.stringify(res, undefined, 2);
                    } else {
                        myApp.showError(res.msg);
                    }
                }
            });
        }

        function hide_search_box() {
            $('#out_area_box').empty();
            $('#login_box').hide();
            $('#login_as_box').hide();
            $('#logout_box').hide();
            $('#device_token_box').hide();
            $('#get_earning_history_box').hide();
            $('#get_earning_detail_box').hide();
            $('#get_open_appointments_box').hide();
            $('#get_open_appointment_detail_box').hide();
            $('#get_availability_box').hide();
            $('#set_availability_box').hide();
            $('#get_upcoming_detail_box').hide();
            $('#get_history_box').hide();
            $('#get_history_detail_box').hide();
            $('#get_profile').hide();
            $('#get_groomer_categories_box').hide();
            $('#get_groomer_products_box').hide();
            $('#add_product_cart_box').hide();
            $('#delete_product_cart_box').hide();
            $('#get_product_cart_box').hide();
            $('#get_product_order_list_box').hide();
            $('#get_product_order_detail_box').hide();
            $('#get_groomer_arrived_box').hide();
            $('#appt_complete_box').hide();
            $('#create_groomer_order_box').hide();

            $('#date_box').hide();
        }

        function get_out_table(data) {
            var keys = '';
            var values = '';

            vtype = $.type(data);

            if (vtype == 'object') {
                $.each(data, function(k, v){
                    keys += '<th>' + k + '</th>';
                    values += '<td>' + v + '</td>';
                });
            } else if (vtype == 'array') {
                $.each(data, function(i, value) {

                    var vt = $.type(value);

                    values += '<tr>';
                    if (vt == 'object') {
                        $.each(value, function(k, v){
                            if (i == 0) {
                                keys += '<th>' + k + '</th>';
                            }

                            var vt2 = $.type(v);

                            if (vt2 == 'object') {
                                values += '<td>';
                                $.each(v, function(k2, v2){
                                    values += k2 + ': ' + v2 + '<br>';
                                });
                                values += '</td>';
                            } else if (vt2 == 'string') {
                                values += '<td>' + v + '</td>';
                            } else {
                                values += '<td>' + JSON.stringify(v, undefined, 2) + '</td>';
                            }
                        });
                    } else {
                        values += '<td>(' + vt + ') ' + v + '</td>';
                    }

                    values += '</tr>';
                });
            } else {
                values += '<td>(' + vtype + ') ' + data + '</td>';
            }

            return "<table class=\"table table-striped display\"><thead>" + keys + "</thead><tbody>" + values +
                "</tbody></table>";
        }

    </script>


    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />Groomer Detail</h3>
    </div>

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">

            <form>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-1 control-label">API Key</label>
                            <div class="col-md-11">
                                <input type="text" class="form-control" id="api_key" value="{{ getenv('ANDROID_KEY') }}" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-1 control-label">Email:</label>
                            <div class="col-md-11">
                                <input type="text" class="form-control" id="email" value="{{ $email }}" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-1 control-label">Token:</label>
                            <div class="col-md-11">
                                <input type="text" class="form-control" id="token" value="{{ $token }}" />
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <hr>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <button class="btn btn-info" onclick="login()">Login</button>
                        @if ($groomer->is_admin == 'Y')
                            <button class="btn btn-info" onclick="login_as()">Login As</button>
                        @endif
                        <button class="btn btn-info" onclick="logout()">Logout</button>
                        <button class="btn btn-info" onclick="update_device_token()">Update Device Token</button>
                        <button class="btn btn-info" onclick="get_current_earning()">Current Earning</button>
                        <button class="btn btn-info" onclick="get_earning_history()">Earning History</button>
                        <button class="btn btn-info" onclick="get_earning_detail()">Earning Detail</button>
                        <hr>
                        <button class="btn btn-info" onclick="get_open_appointments()">Open Appointments</button>
                        <button class="btn btn-info" onclick="get_open_appointment_detail()">Open Appointment Detail</button>
                        <button class="btn btn-info" onclick="get_availability()">Get Availability(open/my)</button>
                        <button class="btn btn-info" onclick="set_availability()">Set Availability</button>
                        <button class="btn btn-info" onclick="get_upcoming()">Upcoming List</button>
                        <button class="btn btn-info" onclick="get_upcoming_detail()">Upcoming Detail</button>
                        <button class="btn btn-info" onclick="get_history()">History List</button>
                        <button class="btn btn-info" onclick="get_history_detail()">History Detail</button>
                        <hr>
                        <button class="btn btn-info" onclick="get_profile()">Get Profile</button>
                        <button class="btn btn-info" onclick="get_info()">Get Info</button>
                        <hr>
                        <button class="btn btn-info" onclick="get_groomer_categories()">Get Categories</button>
                        <button class="btn btn-info" onclick="get_groomer_products()">Get Groomer Products</button>
                        <button class="btn btn-info" onclick="add_product_cart()">Add Item to Cart</button>
                        <button class="btn btn-info" onclick="delete_product_cart()">Delete Item from Cart</button>
                        <button class="btn btn-info" onclick="get_product_cart()">Returns Cart Items</button>
                        <button class="btn btn-info" onclick="create_groomer_order()">Create Groomer Order</button>
                        <button class="btn btn-info" onclick="get_order_list()">Get Groomer Orders</button>
                        <button class="btn btn-info" onclick="get_order_detail()">Get Order Detail</button>
                        <hr>
                        <button class="btn btn-info" onclick="get_groomer_arrived()">Groomer Arrived</button>
                        <button class="btn btn-info" onclick="appt_complete()">Appt Complete</button>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="col-md-1 control-label">API Link</label>
                        <div class="col-md-11">
                            <input type="text" class="form-control" id="api_link" value="" disabled/>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">
            <div class="row" id="date_box" style="display:none;">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Date</label>
                        <div class="col-md-8">
                            <input type="text" style="width:100px; float:left;" class="form-control" id="sdate"
                                   value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"/>
                            <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                            <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                   class="form-control" id="edate" value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"/>
                        </div>

                    </div>
                </div>
            </div>
            <div class="row" id="get_availability_box" style="display:none;">
            <div class="form-group">
                <label class="col-md-2 control-label">Distance Type(C, H, P)</label>
                <div class="col-md-10">
                    <input type="text" style="width:100px; margin-left: 5px; float:left;"
                           class="form-control" id="distance_type"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">X of groomer(optional)</label>
                <div class="col-md-10">
                    <input type="text" style="width:100px; margin-left: 5px; float:left;"
                           class="form-control" id="x"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">Y of groomer(optional</label>
                <div class="col-md-10">
                    <input type="text" style="width:100px; margin-left: 5px; float:left;"
                           class="form-control" id="y"/>
                </div>
            </div>
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label"></label>
                            <div class="col-md-8">
                                <button class="btn btn-info" onclick="get_availability()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="get_earning_history_box" style="display:none;">

                <hr>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Type:</label>
                            <div class="col-md-8">
                                <select class="form-control" id="earning_history_type">
                                    <option value="W">Weekly</option>
                                    <option value="M">Monthly</option>
                                    <option value="Y">Yearly</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label"></label>
                            <div class="col-md-8">
                                <button class="btn btn-info" onclick="get_earning_history()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{--get_earning_history_box--}}

            <div id="get_earning_detail_box" style="display:none;">

                <hr>

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label"></label>
                            <div class="col-md-8">
                                <button class="btn btn-info" onclick="get_earning_detail()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{--get_earning_history_box--}}


            <div id="get_history_box" style="display:none;">

                <hr>

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label"></label>
                            <div class="col-md-8">
                                <button class="btn btn-info" onclick="get_history()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{--get_earning_history_box--}}

            <div id="get_groomer_categories_box" style="display:none;">
                <hr>
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">


                            <label class="col-md-4 control-label"></label>
                            <div class="col-md-8">
                                <button class="btn btn-info" onclick="get_groomer_categories()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="get_groomer_products_box" style="display:none;">
                <hr>
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">

                            <div class="form-group">
                                <label class="col-md-2 control-label">Type</label>
                                <div class="col-md-10">
                                    <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                           class="form-control" id="prod_type"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Search</label>
                                <div class="col-md-10">
                                    <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                           class="form-control" id="search"/>
                                </div>
                            </div>

                            <label class="col-md-4 control-label"></label>
                            <div class="col-md-8">
                                <button class="btn btn-info" onclick="get_groomer_products()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="add_product_cart_box" style="display:none;">
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Product ID</label>
                            <div class="col-md-10">
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="product_id"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">QTY</label>
                            <div class="col-md-10">
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="qty"/>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label"></label>
                            <div class="col-md-8">
                                <button class="btn btn-info" onclick="add_product_cart('submit')">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="delete_product_cart_box" style="display:none;">
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Product ID (Delete)</label>
                            <div class="col-md-10">
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="del_pr_id"/>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label"></label>
                            <div class="col-md-8">
                                <button class="btn btn-info" onclick="delete_product_cart('submit')">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="get_product_cart_box" style="display:none;">
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label"></label>
                            <div class="col-md-8">
                                <button class="btn btn-info" onclick="get_product_cart('submit')">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="create_groomer_order_box" style="display:none;">
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label"></label>
                            <div class="col-md-8">
                                <button class="btn btn-info" onclick="create_groomer_order('submit')">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="get_product_order_list_box" style="display:none;">
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Date</label>
                            <div class="col-md-8">
                                <input type="text" style="width:100px; float:left;" class="form-control" id="sdate"
                                       value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"/>
                                <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                                <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate"
                                       value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Status</label>
                            <div class="col-md-2">
                                <select class="form-control" id="status">
                                    <option value="">ALL</option>
                                    <option value="N">New</option>
                                    <option value="S">Shipped</option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>
                <hr>
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label"></label>
                            <div class="col-md-8">
                                <button class="btn btn-info" onclick="get_order_list()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="get_open_appointments_box" style="display:none;">
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">

                    </div>
                </div>


                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label"></label>
                            <div class="col-md-10">
                                <button class="btn btn-info" onclick="get_open_appointments()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="get_open_appointment_detail_box" style="display:none;">
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Appointment ID</label>
                            <div class="col-md-10">
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="appointment_id"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label"></label>
                            <div class="col-md-10">
                                <button class="btn btn-info" onclick="get_open_appointment_detail()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="set_availability_box" style="display:none;">

                <hr>
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Availabilities</label>
                            <div class="col-md-10">
                                <textarea id="availabilities" class="form-control"></textarea>
                                Sample: [{"day":"2018-12-03","hours":[{"from":"16","to":"17"},{"from":"14","to":"16"}]}]
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label"></label>
                            <div class="col-md-10">
                                <button class="btn btn-info" onclick="set_availability_submit()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{--get_earning_history_box--}}

            <div id="get_upcoming_detail_box" style="display:none;">
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Appointment ID</label>
                            <div class="col-md-10">
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="appointment_id_u"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label"></label>
                            <div class="col-md-10">
                                <button class="btn btn-info" onclick="get_upcoming_detail()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="get_history_detail_box" style="display:none;">
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Appointment ID</label>
                            <div class="col-md-10">
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="appointment_id_h"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label"></label>
                            <div class="col-md-10">
                                <button class="btn btn-info" onclick="get_history_detail()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="get_profile_box" style="display:none;">

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label"></label>
                            <div class="col-md-10">
                                <button class="btn btn-info" onclick="get_profile()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="get_info_box" style="display:none;">

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label"></label>
                            <div class="col-md-10">
                                <button class="btn btn-info" onclick="get_info()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="get_groomer_arrived_box" style="display:none;">

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Appointment ID</label>
                            <div class="col-md-10">
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="appointment_id_arrived"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">X</label>
                            <div class="col-md-10">
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="x"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Y</label>
                            <div class="col-md-10">
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="y"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label"></label>
                            <div class="col-md-10">
                                <button class="btn btn-info" onclick="get_groomer_arrived()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="appt_complete_box" style="display:none;">

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Appointment ID</label>
                            <div class="col-md-10">
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="appointment_id_complete"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">X</label>
                            <div class="col-md-10">
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="x_comp"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Y</label>
                            <div class="col-md-10">
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="y_comp"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label"></label>
                            <div class="col-md-10">
                                <button class="btn btn-info" onclick="appt_complete()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="login_box" style="display:none;">
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Email</label>
                            <div class="col-md-10">
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="email"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">PW</label>
                            <div class="col-md-10">
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="pw"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label"></label>
                            <div class="col-md-10">
                                <button class="btn btn-info" onclick="login()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="login_as_box" style="display:none;">
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Groomer ID</label>
                            <div class="col-md-10">
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="groomer_id"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label"></label>
                            <div class="col-md-10">
                                <button class="btn btn-info" onclick="login_as()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="logout_box" style="display:none;">

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label"></label>
                            <div class="col-md-10">
                                <button class="btn btn-info" onclick="logout()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="device_token_box" style="display:none;">
                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Device Token</label>
                            <div class="col-md-10">
                                <input type="text" style="width:100px; margin-left: 5px; float:left;"
                                       class="form-control" id="device_token"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-bottom: 16px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label"></label>
                            <div class="col-md-10">
                                <button class="btn btn-info" onclick="update_device_token()">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{--get_earning_history_box--}}

        </div>
    </div>

    <div class="container-fluid">
        <form id="frm_search" class="form-horizontal">

            <div id="out_area_box">

            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group" style="text-align: left;margin:12px;border-bottom: 1px solid #ccc;">
                        <h3>REQUEST (JSON)</h3>
                    </div>
                    <pre id="input_json"></pre>
                </div>
                <div class="col-md-6">
                    <div class="form-group" style="text-align: left;margin:12px;border-bottom: 1px solid #ccc;">
                        <h3>RESPONSE (JSON)</h3>
                    </div>
                    <pre id="output_json"></pre>
                </div>
            </div>
        </form>
    </div>
@stop