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

</script>

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
    <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />
        Breeds by Booked Quantity REPORT</h3>
</div>

<div class="container-fluid">
    <div class="well filter" style="padding-bottom:5px;">
        <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/breed-booked">
            {{ csrf_field() }}
            <input type="hidden" name="excel" id="excel" value=""/>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Pets Sign Up Date</label>
                        <div class="col-md-8">
                            <input type="text" style="width:100px; float:left;" class="form-control" id="sdate" name="sdate" value="{{ old('sdate', $sdate) }}"/>
                            <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                            <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate" name="edate" value="{{ old('edate', $edate) }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-right">
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

    <style>
        th {
            background-color: #f5f5f5;
        }
    </style>

    <table class="table table-bordered display" cellspacing="0" width="100%" style="font-size: 11px;">
        <thead>
        <tr>
            <th style="text-align: center;">Breed</th>
            <th style="text-align: center;">No Order</th>
            <th style="text-align: center;">One Order</th>
            <th style="text-align: center;">Two Orders</th>
            <th style="text-align: center;">Multiple Orders</th>
            <th style="text-align: center;">Total</th>
        </tr>
        </thead>
        <tbody>

        @foreach($data as $o)
            <tr>
                <td style="text-align: left;">{{ $o->breed_name }}</td>
                <td style="text-align: right;">{{ $o->a }}</td>
                <td style="text-align: right;">{{ $o->b }}</td>
                <td style="text-align: right;">{{ $o->c }}</td>
                <td style="text-align: right;">{{ $o->d }}</td>
                <td style="text-align: right;">{{ $o->cnt }}</td>
            </tr>
        @endforeach

        </tbody>
        <tfoot>
            <tr>
                <th>Total:</th>
                <th style="text-align: right;">{{ $total->a_total }}</th>
                <th style="text-align: right;">{{ $total->b_total }}</th>
                <th style="text-align: right;">{{ $total->c_total }}</th>
                <th style="text-align: right;">{{ $total->d_total }}</th>
                <th style="text-align: right;">{{ $total->a_total + $total->b_total + $total->c_total +  $total->d_total }}</th>

            </tr>
        </tfoot>
    </table>
</div>

@stop