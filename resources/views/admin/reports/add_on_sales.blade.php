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
            $('#excel').val('N');
        }

    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />Products Sales Quantity</h3>
    </div>

    <div class="container-fluid">

        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/add-on-sales">
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
                            <label class="col-md-4 control-label">Pet Type</label>
                            <div class="col-md-8">
                                <select name="pet_type" class="form-control">
                                    <option value="" {{ empty($pet_type) ? 'selected' : '' }}>All</option>
                                    <option value="dog" {{ $pet_type == 'dog' ? 'selected' : '' }}>Dog</option>
                                    <option value="cat" {{ $pet_type == 'cat' ? 'selected' : '' }}>Cat</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Product Type</label>
                            <div class="col-md-8">
                                <select name="prod_type" class="form-control">
                                    <option value="" {{ empty($prod_type) ? 'selected' : '' }}>All</option>
                                    <option value="P" {{ $prod_type == 'P' ? 'selected' : '' }}>Package</option>
                                    <option value="A" {{ $prod_type == 'A' ? 'selected' : '' }}>Add-on</option>
                                    <option value="S" {{ $prod_type == 'S' ? 'selected' : '' }}>Shampoo</option>
                                    <option value="Fees" {{ $prod_type == 'Fees' ? 'selected' : '' }}>Fees</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">

                        </div>
                    </div>

                    <div class="col-md-4 col-md-offset-4 text-right">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                                <button type="button" class="btn btn-info btn-sm" onclick="excel_export()">Export</button
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <table id="table" class="table table-striped display" cellspacing="0" width="100%">
            <thead>
            <tr>
            </tr>
            <tr>
                <th>Pet Type</th>
                <th>Product Type</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th style="text-align: center;">Amount</th>
            </tr>
            </thead>
            <tbody>
            @php
                $count = 0;
                $sum = 0;
            @endphp
            @foreach ($results as $r)
                @php
                    $count += $r->count;
                    $sum += $r->sum;
                @endphp
                <tr>
                    <td>{{ $r->pet_type }}</td>
                    <td>
                        @if ($r->prod_type == 'P')
                            Product
                        @elseif ($r->prod_type == 'A')
                            Add-on
                        @elseif ($r->prod_type == 'S')
                            Shampoo
                        @else
                            {{ $r->prod_type }}
                        @endif
                    </td>
                    <td>{{ $r->prod_name }}</td>
                    <td>{{ $r->count }}</td>
                    <td style="text-align: center;">${{ number_format($r->sum,2) }}</td>
                    <td></td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td></td>
                    <td style="text-align: right;">Total</td>
                    <td>{{ $count }}</td>
                    <td style="text-align: center;">${{ number_format($sum,2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

@stop
