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
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />Client Login History</h3>
    </div>

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/login-history">
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
                             <label class="col-md-4 control-label">Log In or Log Out</label>
                              <div class="col-md-8">
                                  <select class="form-control" id="log_inout" name="log_inout">
                                      <option value="">All</option>
                                      <option value="I" {{ old('log_inout', $log_inout) == 'I' ? 'selected' : '' }} >Log In</option>
                                      <option value="O" {{ old('log_inout', $log_inout) == 'O' ? 'selected' : '' }}>Log Out</option>
                                  </select>
                                </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Customer ID</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="user_id" name="user_id" value="{{ old('user_id', $user_id) }}"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Result</label>
                            <div class="col-md-8">
                                <select class="form-control" id="result" name="result">
                                    <option value="">All</option>
                                    <option value="S" {{ old('result', $result) == 'S' ? 'selected' : '' }} >Success</option>
                                    <option value="F" {{ old('result', $result) == 'F' ? 'selected' : '' }}>Failure</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Login Channel</label>
                            <div class="col-md-8">
                                <select class="form-control" id="login_channel" name="login_channel">
                                    <option value="">All</option>
                                    <option value="P" {{ old('login_channel', $login_channel) == 'P' ? 'selected' : '' }} >App</option>
                                    <option value="E" {{ old('login_channel', $login_channel) == 'E' ? 'selected' : '' }}>End User Web</option>
                                    <option value="3" {{ old('login_channel', $login_channel) == '3' ? 'selected' : '' }}>3rd Party Login</option>
                                    <option value="A" {{ old('login_channel', $login_channel) == 'A' ? 'selected' : '' }}>Admin Login</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">IP Address</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="ip_addr" name="ip_addr" value="{{ old('ip_addr', $ip_addr) }}"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8 text-right">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-8">
                                <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
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
                <th style="text-align: center;">Log In/Log Out</th>
                <th style="text-align: center;">User ID</th>
                <th style="text-align: center;">User Name</th>
                <th style="text-align: center;">Result</th>
                <th style="text-align: center;">Login Channel</th>
                <th style="text-align: center;">IP Address</th>
                <th style="text-align: center;">Date</th>

            </tr>
            </thead>
            <tbody>
            @foreach ($results as $r)
                <tr>
                    <td style="text-align: center;">{{ $r->log_inout }}</td>
                    <td style="text-align: center;">{{$r->user_id }}</td>
                    <td style="text-align: center;"><a href="/admin/user/{{ $r->user_id }}">{{ $r->first_name . ' ' . $r->last_name }} </a></td>
                    <td style="text-align: center;">{{ $r->result }}</td>
                    <td style="text-align: center;">{{ $r->login_channel }}</td>
                    <td style="text-align: center;">{{ $r->ip_addr }}</td>
                    <td style="text-align: center;">{{ $r->cdate }}</td>
{{--                    <td>{{$r->total}}</td>--}}
                </tr>
            @endforeach

            </tbody>

            <tfoot>
            <tr>
                @if(count($results)>0)
                    <td style="text-align: center;">Total of {{count($results)}} Rows</td>
                    <td style="text-align: center;"></td>
                    <td style="text-align: center;"></td>
                    <td style="text-align: center;"></td>
                    <td style="text-align: center;"></td>
                    <td style="text-align: center;"></td>
                    <td style="text-align: center;"></td>

                @endif
            </tr>

            </tfoot>

{{--<tfoot>--}}
{{--<tr>--}}
{{--    @if(count($results)>0)--}}
{{--        <td style="text-align: center;">Average of {{count($results)}} survey results:</td>--}}
{{--        <td style="text-align: center;"></td>--}}
{{--        <td style="text-align: center;">{{ round($total->ov_total/count($results), 2) }}</td>--}}
{{--        <td style="text-align: center;">{{ round($total->sc_total/count($results), 2)  }}</td>--}}
{{--        <td style="text-align: center;">{{ round($total->gq_total/count($results), 2)  }}</td>--}}
{{--        <td style="text-align: center;">{{ round($total->cl_total/count($results), 2)  }}</td>--}}
{{--        <td style="text-align: center;">{{ round($total->va_total/count($results), 2) }}</td>--}}
{{--        <td style="text-align: center;">{{ round($total->cs_total/count($results), 2) }}</td>--}}
{{--        <td style="text-align: center;"></td>--}}
{{--        <td style="text-align: center;"></td>--}}

{{--    @endif--}}
{{--</tr>--}}

{{--</tfoot>--}}

        </table>

    </div>


@stop
