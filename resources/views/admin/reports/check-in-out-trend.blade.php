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
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />CHECK-IN/OUT TREND</h3>
    </div>

    <div class="container-fluid">

        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/check-in-out-trend">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel"/>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Groomer</label>
                            <div class="col-md-8">
                                <select class="form-control" name="groomer_id" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                    <option value="">All</option>
                                    @if (count($groomers) > 0)
                                        @foreach ($groomers as $o)
                                            <option value="{{ $o->groomer_id }}" {{ old("groomer_id", $groomer_id) == $o->groomer_id ? 'selected' : '' }}>{{ $o->first_name }} {{ $o->last_name }}[{{$o->status}}]</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>


                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Pet</label>
                                <div class="col-md-8">
                                    <select class="form-control" name="pet_type" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                        <option value="">All</option>
                                        <option value="dog" {{ old("pet_type", $pet_type) == 'dog' ? 'selected' : '' }}>Dog</option>
                                        <option value="cat" {{ old("pet_type", $pet_type) == 'cat' ? 'selected' : '' }}>Cat</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Service Date</label>
                            <div class="col-md-8">
                                <input type="text" style="width:100px; float:left;" class="form-control" id="sdate" name="sdate" value="{{ old('sdate', $sdate) }}"/>
                                <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                                <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate" name="edate" value="{{ old('edate', $edate) }}"/>
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
                                <button type="button" class="btn btn-primary btn-sm" id="btn_search" onclick="search()">Search</button>
                                <button type="button" class="btn btn-info btn-sm" onclick="excel_export()">Export</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <table class="table table-bordered display" cellspacing="0" width="100%" style="font-size: 11px;">
            <thead>
                <tr>
                    <th rowspan="2">Appointment.ID</th>
                    <th rowspan="2">C.Date</th>
                    <th colspan="4">Groomer</th>
                    <th rowspan="2">Size.Name</th>
                    <th rowspan="2">Package</th>
                    <th rowspan="2">Breed.Name</th>
                    <th rowspan="2">Distance(ft)</th>
                    <th rowspan="2">Arrived</th>
                    <th rowspan="2">Distance<br/>(Comp./Arrival)</th>
                    <th rowspan="2">Distance<br/>(Comp./Address)</th>
                    <th rowspan="2">Service Time</th>
                    <th rowspan="2">Check-In.Date</th>
                    <th rowspan="2">Check-In.Photo</th>
                    <th rowspan="2">Check-Out.Date</th>
                    <th rowspan="2">Check-Out.Photo</th>
                    <th rowspan="2" style="text-align: right;">Grooming Time <br>(MIN)</th>
                    <th rowspan="2" style="text-align: right;">Delay Time <br>(MIN)</th>
                    <th rowspan="2">Groomer Earning <br>($)</th>
                </tr>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Assign.Date</th>
                    <th>Assign.Diff <br>(MIN)</th>
                </tr>
            </thead>
            <tbody>
                @if (count($data) > 0)
                    @foreach ($data as $o)
                        <tr>
                            <td><a href="/admin/appointment/{{ $o->appointment_id }}">{{ $o->appointment_id }}</a></td>
                            <td>{{ $o->cdate }}</td>
                            <td><a href="/admin/groomer/{{ $o->groomer_id }}">{{ $o->groomer_id }}</a></td>
                            <td>{{ $o->groomer_name }}</td>
                            <td>{{ $o->groomer_assign_date }}</td>
                            <td style="text-align: right;{{ $o->assign_diff >= 15 ? 'color:red;' : '' }}">{{
                            number_format
                            ($o->assign_diff, 0)
                             }}</td>
                            <td>{{ $o->size_name }}</td>
                            <td>{{ $o->package }}</td>
                            <td>{{ $o->breed_name }}</td>
                            <td>{{ number_format($o->distance,0) }}</td>
                            <td>{{ $o->ga_cdate }}</td>
                            <td>{{ number_format($o->distance_comp_app,0) }}</td>
                            <td>{{ number_format($o->distance_comp_google,0) }}</td>
                            <td>{{ $o->accepted_date }}</td>
                            <td>{{ $o->check_in }}</td>
                            <td style="cursor:pointer;">
                                @if (isset($o->check_in_photo))
                                <img style="max-width: 100px; height:auto;" src="data:image/png;base64,{{ base64_encode
                                ($o->check_in_photo) }}" onclick="show_photo(this, 'Check IN')"/>
                                @endif
                            </td>
                            <td>{{ $o->check_out }}</td>
                            <td style="cursor:pointer;">
                                @if (isset($o->check_out_photo))
                                <img style="max-width: 100px; height:auto;" src="data:image/png;base64,{{
                                base64_encode($o->check_out_photo) }}" onclick="show_photo(this, 'Check Out')"/>
                                @endif
                            </td>
                            <td style="text-align: right;">{{ $o->diff }}</td>
                            <td style="text-align: right; {{ $o->service_time_diff < 0 ? 'color:orange' : '' }}">{{ $o->service_time_diff }}</td>
                            <td style="text-align: right;">{{ number_format($o->groomer_profit_amt / $o->pet_qty, 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="100" class="text-center">No record found.</td>
                    </tr>
                @endif
            </tbody>
        </table>


        <div class="text-right">
            Total {{ $data->total() }} record(s) found.<br/>
            {{ $data->appends(Request::except('page'))->links() }}
        </div>
    </div>


    <script type="text/javascript">
        function show_photo(p, title) {
            $('#nameofid').text(title);
            $('#orig_photo').attr('src', p.src);
            $('#modal_show_photo').modal();
        }
    </script>

    <!-- Send Modal Start -->
    <div class="modal" id="modal_show_photo" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><span id="nameofid"></span> Photo</h4>
                </div>
                <div class="modal-body">
                    <img id="orig_photo" src="">
                </div>
            </div>
        </div>
    </div>
    <!-- Send Modal End -->
@stop
