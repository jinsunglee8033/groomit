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
        }

    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />Pre Apply</h3>
    </div>

    <div class="container">

        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/pre_apply">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel"/>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Date</label>
                            <div class="col-md-8">
                                <input type="text" style="width:100px; float:left;" class="form-control" id="sdate" name="sdate" value="{{ old('sdate', $sdate) }}"/>
                                <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                                <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate" name="edate" value="{{ old('edate', $edate) }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Name</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="full_name" value="{{ old('full_name', $full_name) }}"/>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Phone</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="phone" value="{{ old('phone', $phone) }}"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Email</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="email" value="{{ old('email', $email) }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Referred By</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="referred_by" value="{{ old('referred_by', $referred_by) }}"/>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">

                        </div>
                    </div>
                    <div class="col-md-4 col-md-offset-4 text-right">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                @if (\App\Lib\Helper::get_action_privilege('applications_search', 'Pre Apply Search'))
                                    <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                                @endif
                                @if (\App\Lib\Helper::get_action_privilege('applications_export', 'Pre Apply Export'))
                                    <button type="button" class="btn btn-info btn-sm" onclick="excel_export()">Export</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>


        <table id="table" class="table table-striped" cellspacing="0" width="100%">
            <thead>
            <tr>
                <td colspan="8" class="text-right"><strong>TOTAL</strong>: {{ $total }}</td>
            </tr>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Zip</th>
                <th>Referred By</th>
                <th>Applied Date</th>
                <th>Application ID</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($preapplys as $a)
                <tr>
                    <td>{{ $a->id }}</td>
                    <td>{{ $a->full_name }}</td>
                    <td>{{ $a->email }}</td>
                    <td>{{ $a->phone }}</td>
                    <td>{{ $a->zip }}</td>
                    <td>{{ $a->referred_by }}</td>
                    <td>{{ $a->cdate }}</td>
                    <td>
                        @if(!empty($a->ap_id))
                            <a href="/admin/application/{{ $a->ap_id }}">{{ $a->ap_id }}</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>


        <div class="text-right">
            {{ $preapplys->appends(Request::except('page'))->links() }}
        </div>

    </div>

@stop
