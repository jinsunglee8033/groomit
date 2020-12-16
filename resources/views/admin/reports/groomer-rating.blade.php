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
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png"
            />GROOMER RATING REPORT</h3>
    </div>

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/groomer-rating">
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
                            <label class="col-md-4 control-label">Groomer</label>
                            <div class="col-md-8">
                                <select class="form-control" name="groomer_id">
                                    <option value="">All</option>
                                    @foreach ($groomers as $o)
                                        <option value="{{ $o->groomer_id }}" {{ old('groomer_id', $groomer_id) == $o->groomer_id ? 'selected' : '' }}>{{ $o->first_name . ' ' . $o->last_name }}[{{$o->status}}]</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Pet Type</label>
                            <div class="col-md-8">
                                <select class="form-control" name="pet_type">
                                    <option value="">All</option>
                                    <option value="dog" {{ $pet_type == 'dog' ? 'selected' : '' }}>Dog</option>
                                    <option value="cat" {{ $pet_type == 'cat' ? 'selected' : '' }}>Cat</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Customer ID</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="cust_id" name="cust_id" value="{{ old('cust_id', $cust_id) }}"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Customer Name</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="cust_name" name="cust_name" value="{{ old('cust_name', $cust_name) }}"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-md-offset-8 text-right">
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

        @if (!empty($data) && count($data) > 0)
        <table class="table table-bordered display" cellspacing="0" width="100%" style="font-size: 11px;">
            <thead>
            <tr>
                <th>Date</th>
                <th style="text-align: center;">Appt ID</th>
                <th style="text-align: center;">Groomer</th>
                <th style="text-align: center;">Customer</th>
                <th style="text-align: center;">Pet type</th>
                <th style="text-align: center;">Subtotal</th>
                <th style="text-align: center;">Rating</th>
            </tr>
            </thead>
            <tbody>
                @forelse($data as $o)
                <tr>
                    <td style="text-align: left;">{{ $o->cdate }}</td>
                    <td style="text-align: center;"><a href="/admin/appointment/{{ $o->appointment_id }}">{{ $o->appointment_id }}</a></td>
                    <td style="text-align: left;">{{ $o->groomer_id . ', ' . $o->groomer_name }}</td>
                    <td style="text-align: left;">{{ $o->user_id . ', ' . $o->user_name }}</td>
                    <td style="text-align: center;">{{ $o->app_pet_type }}</td>
                    <td style="text-align: right;">${{ number_format($o->sub_total, 2) }}</td>
                    <td style="text-align: right;">{{ $o->rate_score }}</td>
                </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center">No Record Found</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6" style="text-align: right;">Average</th>
                    <th style="text-align: right;">{{ number_format($total->score / $total->qty, 2) }}</th>
                </tr>
            </tfoot>
        </table>


        <div class="text-right">
            {{ $data->appends(Request::except('page'))->links() }}
        </div>

        @endif
    </div>

@stop