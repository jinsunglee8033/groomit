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


    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo
        .png"/>GROOMER ORDERS
        </h3>
    </div>

    <div class="container-fluid">

        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/order/history">
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
                                        <option value="{{ $o->groomer_id }}">{{ $o->first_name . ' ' . $o->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
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
                                <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                                <a type="button" class="btn btn-success btn-sm" href="/admin/order">Groomer Order</a>
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
                    <th>Category</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th># of Pets Groomed</th>
                </tr>
            </thead>
            <tbody>
            @php
                $c_groomer_id = 0;
            @endphp
            @foreach ($orders as $o)
                <tr>
                    <td>
                        @if ($c_groomer_id != $o->groomer_id)
                        <a href="/admin/groomer/{{ $o->groomer->groomer_id }}">
                            {{ $o->groomer->groomer_id . ', ' . $o->groomer->first_name }} {{ $o->groomer->last_name }}
                        </a>
                        @php
                            $c_groomer_id = $o->groomer_id;
                        @endphp
                        @endif
                    </td>
                    <td>{{ $o->prod_type }}</td>
                    <td>{{ $o->prod_name }}</td>
                    <td>{{ $o->qty }}</td>
                    <td>{{ $o->pet_num }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>


@stop
