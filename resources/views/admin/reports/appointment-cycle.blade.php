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
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />APPOINTMENT CYCLE</h3>
    </div>

    <div class="container">

        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/appointment-cycle">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel"/>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Month</label>
                            <div class="col-md-8">
                                <input type="text" style="width:100px; float:left;" class="form-control" id="month" name="month" value="{{ old('month', $month) }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Promo.Code.Type</label>
                            <div class="col-md-8">
                                <select class="form-control" name="promo_code_type" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                    <option value="" {{ old('promo_code_type', $promo_code_type) == '' ? 'selected' : '' }}>All</option>
                                    <option value="X" {{ old('promo_code_type', $promo_code_type) == 'X' ? 'selected' : '' }}>None</option>
                                    <option value="B" {{ old('promo_code_type', $promo_code_type) == 'A' ? 'selected' : '' }}>Affiliate</option>
                                    <option value="R" {{ old('promo_code_type', $promo_code_type) == 'R' ? 'selected' : '' }}>Refer a Friend</option>
                                    <option value="N" {{ old('promo_code_type', $promo_code_type) == 'N' ? 'selected' : '' }}>Normal</option>
                                    <option value="G" {{ old('promo_code_type', $promo_code_type) == 'G' ? 'selected' : '' }}>Groupon</option>
                                    <option value="T" {{ old('promo_code_type', $promo_code_type) == 'T' ? 'selected' : '' }}>Gilt</option>
                                </select>
                            </div>
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
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">User.Name</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $name) }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Email</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="email" name="email" value="{{ old('email', $email) }}"/>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <table class="table table-striped display" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>User.ID</th>
                    <th>Promo.Code.Type</th>
                    <th>User.Name</th>
                    <th>Email</th>
                    <th>M1</th>
                    <th>M2</th>
                    <th>M3</th>
                    <th>M4</th>
                    <th>M5</th>
                    <th>M6</th>
                    <th>M7</th>
                    <th>M8</th>
                    <th>M9</th>
                    <th>M10</th>
                    <th>M11</th>
                    <th>M12</th>
                    <th>Total</th>
                    <!--th>First.Date</th>
                    <th>Last.Date</th>
                    <th>Date.Diff</th-->
                    <th>Avg.Days</th>
                </tr>
            </thead>
            <tbody>
                @if (count($data) > 0)
                    @foreach ($data as $o)
                        <tr>
                            <td>{{ $o->user_id }}</td>
                            <td>{{ $o->promo_code_type }}</td>
                            <td>{{ $o->user_name }}</td>
                            <td>{{ $o->email }}</td>
                            <td>{{ $o->m1_qty }}</td>
                            <td>{{ $o->m2_qty }}</td>
                            <td>{{ $o->m3_qty }}</td>
                            <td>{{ $o->m4_qty }}</td>
                            <td>{{ $o->m5_qty }}</td>
                            <td>{{ $o->m6_qty }}</td>
                            <td>{{ $o->m7_qty }}</td>
                            <td>{{ $o->m8_qty }}</td>
                            <td>{{ $o->m9_qty }}</td>
                            <td>{{ $o->m10_qty }}</td>
                            <td>{{ $o->m11_qty }}</td>
                            <td>{{ $o->m12_qty }}</td>
                            <td>{{ $o->total_qty }}</td>
                            <!--td>{{ $o->first_date }}</td>
                            <td>{{ $o->last_date }}</td>
                            <td>{{ $o->date_diff }}</td-->
                            <td>{{ empty($o->avg_days) ? '' : number_format($o->avg_days, 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="100" class="text-center">No record found.</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <th colspan="4" class="text-right">Total : </th>
                <th>{{ $m1_qty }}</th>
                <th>{{ $m2_qty }}</th>
                <th>{{ $m3_qty }}</th>
                <th>{{ $m4_qty }}</th>
                <th>{{ $m5_qty }}</th>
                <th>{{ $m6_qty }}</th>
                <th>{{ $m7_qty }}</th>
                <th>{{ $m8_qty }}</th>
                <th>{{ $m9_qty }}</th>
                <th>{{ $m10_qty }}</th>
                <th>{{ $m11_qty }}</th>
                <th>{{ $m12_qty }}</th>
                <th>{{ $total_qty }}</th>
                <th>{{ $avg_days }}</th>
            </tfoot>
        </table>


        <div class="text-right">
            {{ $data->appends(Request::except('page'))->links() }}
        </div>
        <div class="text-right">
            *. Show Users who made appointments based on above filters.<br/>
            *.Month : Month of appointments<br/>
            *. PromoCode Type : Shows appointments with this promo code type only<br/>
        </div>
    </div>
@stop
