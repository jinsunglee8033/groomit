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
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png"
            />BREED/SIZE</h3>
    </div>

    <div class="container-fluid">

        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/breed_size">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel"/>
                <div class="row">
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
                            <label class="col-md-4 control-label">Breed</label>
                            <div class="col-md-8">
                                <input type="text" name="breed" class="form-control" value="{{ $breed }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Size</label>
                            <div class="col-md-8">
                                <select name="size" class="form-control">
                                    <option value="" {{ empty($size) ? 'selected' : '' }}>All</option>
                                    <option value="2" {{ $size == 2 ? 'selected' : '' }}>Small</option>
                                    <option value="3" {{ $size == 3 ? 'selected' : '' }}>Medium</option>
                                    <option value="4" {{ $size == 4 ? 'selected' : '' }}>Large</option>
                                    <option value="5" {{ $size == 5 ? 'selected' : '' }}>Extra Large</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                        </div>
                    </div>

                    <div class="col-md-4">
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
                <th>App #</th>
                <th>Service.Date</th>
                <th>Groomer</th>
                <th>Breed.Name</th>
                <th>Pet Size</th>
                <th>Amount</th>
                <th>Check-In.Date</th>
                <th>Check-Out.Date</th>
                <th style="text-align: right;">Grooming Time <br>(MIN)</th>
            </tr>
            </thead>
            <tbody>
            @if (count($data) > 0)
                @foreach ($data as $o)
                    <tr>
                        <td><a href="/admin/appointment/{{ $o->appointment_id }}">{{ $o->appointment_id }}</a></td>
                        <td>{{ $o->accepted_date }}</td>
                        <td><a href="/admin/groomer/{{ $o->groomer_id }}">{{ $o->groomer_id }}, {{ $o->groomer_name }}</a></td>
                        <td>{{ $o->breed_name }}</td>
                        <td>{{ $o->size_name }}</td>
                        <td style="text-align: right;">{{ number_format($o->sub_total, 2) }}</td>
                        <td>{{ $o->check_in }}</td>
                        <td>{{ $o->check_out }}</td>
                        <td style="text-align: right;">{{ $o->diff }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="100" class="text-center">No record found.</td>
                </tr>
            @endif
            </tbody>
            <tfoot>
                <tr>
                    <th style="text-align: right;">{{ $total->apps }} EA</th>
                    <th colspan="3"></th>
                    <th style="text-align: right;">{{ $total->pet_qty }} EA</th>
                    <th style="text-align: right;">{{ number_format($total->amount, 2) }}</th>
                    <th colspan="2" style="text-align: right;"></th>
                    <th style="text-align: right;">AVG: {{ $total->pet_qty > 0 ? number_format($total->times /
                    $total->pet_qty, 2) : 0 }}</th>
                </tr>
            </tfoot>
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
