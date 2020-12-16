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
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />CONTACT US</h3>
    </div>

    <div class="container">
    <div class="well filter" style="padding-bottom:5px;">
        <form id="frm_search" class="form-horizontal" method="post" action="/admin/contacts">
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
                        <label class="col-md-4 control-label">Name</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="name" value="{{ old('name', $name) }}"/>
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
                        <label class="col-md-4 control-label">Subject</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="subject" value="{{ old('subject', $subject) }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Status</label>
                        <div class="col-md-8">
                            <select class="form-control" name="status" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                <option value="" {{ old('status', $status) == '' ? 'selected' : '' }}>All</option>
                                <option value="N" {{ old('status', $status) == 'N' ? 'selected' : '' }}>New</option>
                                <option value="A" {{ old('status', $status) == 'A' ? 'selected' : '' }}>Answered</option>
                                <option value="C" {{ old('status', $status) == 'C' ? 'selected' : '' }}>Closed</option>
                            </select>

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
                            @if (\App\Lib\Helper::get_action_privilege('contacts_search', 'Contacts Search'))
                            <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                            @endif
                            @if (\App\Lib\Helper::get_action_privilege('contacts_export', 'Contacts Export'))
                            <button type="button" class="btn btn-info btn-sm" onclick="excel_export()">Export</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <table id="table" class="table table-striped display" cellspacing="0" width="100%">
        <thead>
        <tr>
            <td colspan="9" class="text-right"><strong>TOTAL</strong>: {{ $total }}</td>
        </tr>
        <tr>
            <th>ID</th>
            <th>Type</th>
            <th>Status</th>
            <th>Name</th>
            <th>Subject</th>
            <th>Phone</th>
            <th>Email</th>
            <th># of Appointments</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($contacts as $c)
            <tr id="contact/{{ $c->contact_id }}">
                <td>{{ $c->contact_id }}</td>
                <td>{{ $c->type }}</td>
                <td>{{ $c->status_name }}</td>
                <td>{{ $c->first_name }} {{ $c->last_name }}</td>
                <td>{{ $c->subject }}</td>
                <td>{{ $c->phone }}</td>
                <td>{{ $c->email }}</td>
                <td class="text-center">{{ $c->getAppointmentNumbers() }}</td>
                <td>{{ $c->cdate }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="text-right">
        {{ $contacts->appends(Request::except('page'))->links() }}
    </div>
    </div>
@stop
