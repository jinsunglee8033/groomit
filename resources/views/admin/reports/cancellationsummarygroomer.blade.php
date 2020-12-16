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

    function excel_export3() {
        $('#excel').val('Y');
        $('#frm_search').submit();
        $('#excel').val('N');
    }

</script>



<div class="container-fluid top-cont">
    <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />Cancellations Groomer Summary</h3>
</div>

<div class="container-fluid">

    <div class="well filter" style="padding-bottom:5px;">
        <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/cancellationsummarygroomer">
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
                        <label class="col-md-4 control-label">Groomer</label>
                        <div class="col-md-8">
                            <select class="form-control" id="groomer_id" name="groomer_id">
                                <option value="">All</option>
                                @foreach ($groomers as $r)
                                    <option value="{{ $r->groomer_id }}" {{ old('groomer_id', $groomer_id) == $r->groomer_id ? 'selected' : '' }}>{{ $r->first_name . ' ' . $r->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 text-right">
                    <div class="form-group">
                        <div class="col-md-8">
                            <button type="button" class="btn btn-warning btn-sm" onclick="window.location.href='/admin/reports/cancellationsummary'">Summary</button>
                            <button type="button" class="btn btn-warning btn-sm" onclick="window.location.href='/admin/reports/cancellationsdetails'">Details</button>
                        </div>
                    </div>
                </div>



                <div class="col-md-4 text-right">
                    <div class="form-group">
                        <div class="col-md-8">
                            <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                            <button type="button" class="btn btn-info btn-sm" onclick="excel_export3()">Export</button>
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

            <th>Groomer</th>
            <th>Reason</th>
            <th>Number of times cited</th>

        </tr>
        </thead>
        <tbody>
        @foreach ($results as $r)
        <tr>

            <td style="text-align: center;">{{ ( $r->groomer_id> 0 ) ?  $r->first_name . ' ' . $r->last_name . '(' . $r->groomer_id . ')':  '' }} </td>
            <td>{{ $r->note }}</td>
            <td>{{ $r->num }} </td>

        </tr>
        @endforeach


        </tbody>
    </table>
</div>

@stop
