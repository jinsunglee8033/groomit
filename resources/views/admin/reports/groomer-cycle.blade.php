@extends('includes.admin_default')
@section('contents')

    <script type="text/javascript">
        window.onload = function() {
            $( "#month" ).datetimepicker({
                format: 'YYYY-MM'
            });
        };

        function search() {
            $('#excel').val('N');
            $('#frm_search').submit();
        }

        function excel_export() {
            $('#excel').val('Y');
            $('#frm_search').submit();
        }

    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />GROOMER CYCLE (Number of Appointment)</h3>
    </div>

    <div class="container-fluid">

        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/groomer-cycle">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel"/>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Month</label>
                            <div class="col-md-8">
                                <input type="text" style="width:100px; float:left;" class="form-control" id="month" name="month" value="{{ old('month', $month) }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Groomer</label>
                            <div class="col-md-8">
                                <select class="form-control" name="groomer_id" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                    <option value="" {{ old('groomer_id', $groomer_id) == '' ? 'selected' : '' }}>All</option>
                                    @if (count($groomers) > 0)
                                        @foreach ($groomers as $o)
                                            <option value="{{ $o->groomer_id }}" {{ old('groomer_id', $groomer_id) == $o->groomer_id ? 'selected' : '' }}>{{ $o->first_name . ' ' . $o->last_name }}[{{$o->status}}]</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Repeat</label>
                            <div class="col-md-8">
                                <select class="form-control" name="repeat" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                    <option value="" {{ old('repeat', $repeat) == '' ? 'selected' : '' }}>All</option>
                                    <option value="N" {{ old('repeat', $repeat) == 'N' ? 'selected' : ''
                                    }}>First Time Appts</option>
                                    <option value="Y" {{ old('repeat', $repeat) == 'Y' ? 'selected' : ''
                                    }}>Repeated Appts</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Type</label>
                            <div class="col-md-8">
                                <select class="form-control" name="type" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                    <option value="" {{ old('type', $type) == '' ? 'selected' : '' }}>All</option>
                                    <option value="D" {{ old('type', $type) == 'D' ? 'selected' : ''}}>Dog</option>
                                    <option value="C" {{ old('type', $type) == 'C' ? 'selected' : ''}}>Cat</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Size</label>
                            <div class="col-md-8">
                                <select class="form-control" name="size" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                    <option value="" {{ old('size', $size) == '' ? 'selected' : '' }}>All</option>
                                    <option value="2" {{ old('size', $size) == '2' ? 'selected' : ''}}>Small</option>
                                    <option value="3" {{ old('size', $size) == '3' ? 'selected' : ''}}>Medium</option>
                                    <option value="4" {{ old('size', $size) == '4' ? 'selected' : ''}}>Large</option>
                                    <option value="5" {{ old('size', $size) == '5' ? 'selected' : ''}}>Extra-Large</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
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

        <table class="table table-striped display" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Groomer.ID</th>
                    <th>Groomer.Name</th>
                    <th style="text-align: right;">M1</th>
                    <th style="text-align: right;">M2</th>
                    <th style="text-align: right;">M3</th>
                    <th style="text-align: right;">M4</th>
                    <th style="text-align: right;">M5</th>
                    <th style="text-align: right;">M6</th>
                    <th style="text-align: right;">M7</th>
                    <th style="text-align: right;">M8</th>
                    <th style="text-align: right;">M9</th>
                    <th style="text-align: right;">M10</th>
                    <th style="text-align: right;">M11</th>
                    <th style="text-align: right;">M12</th>
                    <th style="text-align: right;">UQTY</th>
                </tr>
            </thead>
            <tbody>
                @if (count($data) > 0)
                    @foreach ($data as $o)
                        <tr>
                            <td>{{ $o->groomer_id }}</td>
                            <td>{{ $o->first_name . ' ' . $o->last_name }}</td>
                            <td style="text-align: right;">{{ $o->m1_qty }}</td>
                            <td style="text-align: right;">{{ $o->m2_qty }}</td>
                            <td style="text-align: right;">{{ $o->m3_qty }}</td>
                            <td style="text-align: right;">{{ $o->m4_qty }}</td>
                            <td style="text-align: right;">{{ $o->m5_qty }}</td>
                            <td style="text-align: right;">{{ $o->m6_qty }}</td>
                            <td style="text-align: right;">{{ $o->m7_qty }}</td>
                            <td style="text-align: right;">{{ $o->m8_qty }}</td>
                            <td style="text-align: right;">{{ $o->m9_qty }}</td>
                            <td style="text-align: right;">{{ $o->m10_qty }}</td>
                            <td style="text-align: right;">{{ $o->m11_qty }}</td>
                            <td style="text-align: right;">{{ $o->m12_qty }}</td>
                            <td style="text-align: right;">{{ $o->unix_qty }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="100" class="text-center">No record found.</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <th colspan="2" class="text-right">Total : </th>
                <th style="text-align: right;">{{ $m1_qty }}</th>
                <th style="text-align: right;">{{ $m2_qty }}</th>
                <th style="text-align: right;">{{ $m3_qty }}</th>
                <th style="text-align: right;">{{ $m4_qty }}</th>
                <th style="text-align: right;">{{ $m5_qty }}</th>
                <th style="text-align: right;">{{ $m6_qty }}</th>
                <th style="text-align: right;">{{ $m7_qty }}</th>
                <th style="text-align: right;">{{ $m8_qty }}</th>
                <th style="text-align: right;">{{ $m9_qty }}</th>
                <th style="text-align: right;">{{ $m10_qty }}</th>
                <th style="text-align: right;">{{ $m11_qty }}</th>
                <th style="text-align: right;">{{ $m12_qty }}</th>
                <th style="text-align: right;">{{ $muqy }}</th>
            </tfoot>
        </table>


        <div class="text-right">
            {{ $data->appends(Request::except('page'))->links() }}
        </div>
        <div class="text-right">
            *. M1 : The number of appointments by groomers.<br>
            *. M2/M3/M4... : The number of Appointments by Users regardless of groomers, who had appoitments for M1.<br>
            *. UQTY : The number of Unique Users from M2 up to <strong>NOW</strong> regardless of groomers, who had appointments for M1.<br>
            *. Sorted by Status/First Name/Last Name of groomers.
        </div>
    </div>
@stop
