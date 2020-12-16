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

        function show_upload_modal() {

            $("#upload_modal").modal();
        }

        function file_upload() {

            var file = $('#charge_back_csv_file').val();
            if (file == '') {
                myApp.showError('Please select file to upload');
                return;
            }

            myApp.showLoading();
            $('#frm_upload').submit();
        }

        function view_detail(id) {
            $('#div_view_'+id).modal();
        }

        function hide_div_view_modal(id) {
            $('#div_view_' + id).modal('hide');
        }

        function update_chargeback(id) {

            $.ajax({
                url: '/admin/reports/chargeback/update',
                data: {
                    _token: '{!! csrf_token() !!}',
                    chargeback_id: id,
                    case_stage : $('#case_stage_'+id).val(),
                    transaction_date : $('#transaction_date_'+id).val(),
                    response_expiration : $('#response_expiration_'+id).val(),
                    financial_action_amount : $('#financial_action_amount_'+id).val(),
                    currency : $('#currency_'+id).val(),
                    financial_action_date : $('#financial_action_date_'+id).val(),
                    card_brand_reason_code : $('#card_brand_reason_code_'+id).val(),
                    reason_code_description : $('#reason_code_description_'+id).val(),
                    app_id : $('#app_id_'+id).val(),
                    bakkar_comments : $('#bakkar_comments_'+id).val(),
                    customer_service_comments : $('#customer_service_comments_'+id).val(),
                    groomer_name : $('#groomer_name_'+id).val(),
                    service_date : $('#service_date_'+id).val(),
                    credit_back : $('#credit_back_'+id).val()
                },
                cache: false,
                type: 'post',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    if (res.msg == '') {
                        myApp.showSuccess('Your request has been processed successfully!', function() {
                            window.location.href = '/admin/reports/chargeback';
                        });
                    } else {
                        myApp.showError(res.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            })
        }

    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center">
            <img class="img-respondive top-logo-img" src="/images/top-logo.png"/>
            Charge Back MGMT
        </h3>
    </div>

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/chargeback">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel" value=""/>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Upload Date Range</label>
                            <div class="col-md-8">
                                <input type="text" style="width:100px; float:left;" class="form-control" id="sdate" name="sdate" value="{{ old('sdate', $sdate) }}"/>
                                <span class="control-label" style="margin-left:5px; float:left;"> ~ </span>
                                <input type="text" style="width:100px; margin-left: 5px; float:left;" class="form-control" id="edate" name="edate" value="{{ old('edate', $edate) }}"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-md-offset-8 text-right">
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
{{--                                <button type="button" class="btn btn-info btn-sm" onclick="lookup()">Zip Lookup</button>--}}
                                <button type="button" class="btn btn-primary btn-sm" id="btn_search" onclick="search()">Search</button>
                                <button type="button" class="btn btn-primary btn-sm" onclick="show_upload_modal()">Upload ChargeBack</button>
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

        <table class="table table-bordered display" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th style="text-align: center;">ID</th>
                <th style="text-align: center;">Case.ID</th>
                <th style="text-align: center;">MID</th>
                <th style="text-align: center;">Case.Stage</th>
                <th style="text-align: center;">Transaction.Date</th>
                <th style="text-align: center;">Response.Expiration</th>
                <th style="text-align: center;">Financial.Action.Amount</th>
                <th style="text-align: center;">Currency</th>
                <th style="text-align: center;">Financial.Action.Date</th>
                <th style="text-align: center;">Card.Brand/Reason.Code</th>
                <th style="text-align: center;">Reason.Code.Description</th>
                <th style="text-align: center;">APP.ID</th>
                <th style="text-align: center;">Bakkar.Comments</th>
                <th style="text-align: center;">Customer.Service.Comments</th>
                <th style="text-align: center;">Groomer.Name</th>
                <th style="text-align: center;">Service.Date</th>
                <th style="text-align: center;">Credit.Back</th>
                <th style="text-align: center;">Upload.Date</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $o)
                <tr>
                    <td>
                        <div class="btn-right btn btn-default btn-sm" onclick="view_detail({{ $o->id }})">View ({{ $o->id }})</div>
                    </td>
                    <td>{{ $o->case_id }}</td>
                    <td>{{ $o->m_id }}</td>
                    <td>{{ $o->case_stage }}</td>
                    <td>{{ $o->transaction_date }}</td>
                    <td>{{ $o->response_expiration }}</td>
                    <td>{{ $o->financial_action_amount }}</td>
                    <td>{{ $o->currency }}</td>
                    <td>{{ $o->financial_action_date }}</td>
                    <td>{{ $o->card_brand_reason_code }}</td>
                    <td>{{ $o->reason_code_description }}</td>
                    <td>{{ $o->app_id }}</td>
                    <td>{{ $o->bakkar_comments }}</td>
                    <td>{{ $o->customer_service_comments }}</td>
                    <td>{{ $o->groomer_name }}</td>
                    <td>{{ $o->service_date }}</td>
                    <td>{{ $o->credit_back }}</td>
                    <td>{{ $o->upload_date }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            </tfoot>
        </table>

        <div class="text-right">
            {{ $data->appends(Request::except('page'))->links() }}
        </div>

        @if (!empty($data))
            @foreach ($data as $d)
                <div id="div_view_{{ $d->id }}" class="modal fade " tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                     style="display:none;">
                    <input type="hidden" id="status" name="status" value="{{ $d->id }}">
                    <div class="modal-dialog" role="document" style="width:50%;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">ID - {{ $d->id }}</h4>
                            </div>

                            <div class="modal-body">
                                <div class="row no-border">
                                    <div class="col-xs-3">Case.ID</div>
                                    <div class="col-xs-9">
                                       <p>{{ $d->case_id }}</p>
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-3">MID</div>
                                    <div class="col-xs-9">
                                        <p>{{ $d->m_id}}</p>
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-3">CASE.Stage</div>
                                    <div class="col-xs-9">
                                        <input type="text" name="case_stage_{{$d->id}}" id="case_stage_{{$d->id}}" class="form-control" value="{{ $d->case_stage}}" />
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-3">Transaction.Date</div>
                                    <div class="col-xs-9">
                                        <input type="text" name="transaction_date_{{$d->id}}" id="transaction_date_{{$d->id}}" class="form-control" value="{{ $d->transaction_date}}" />
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-3">Response.Expiration</div>
                                    <div class="col-xs-9">
                                        <input type="text" name="response_expiration_{{$d->id}}" id="response_expiration_{{$d->id}}" class="form-control" value="{{ $d->response_expiration}}" />
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-3">Financial.Action.Amount</div>
                                    <div class="col-xs-9">
                                        <input type="text" name="financial_action_amount_{{$d->id}}" id="financial_action_amount_{{$d->id}}" class="form-control" value="{{ $d->financial_action_amount}}" />
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-3">Currency</div>
                                    <div class="col-xs-9">
                                        <input type="text" name="currency_{{$d->id}}" id="currency_{{$d->id}}" class="form-control" value="{{ $d->currency}}" />
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-3">Financial.Action.Date	</div>
                                    <div class="col-xs-9">
                                        <input type="text" name="financial_action_date_{{$d->id}}" id="financial_action_date_{{$d->id}}" class="form-control" value="{{ $d->financial_action_date}}" />
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-3">Card.Brand/Reason.Code</div>
                                    <div class="col-xs-9">
                                        <input type="text" name="card_brand_reason_code_{{$d->id}}" id="card_brand_reason_code_{{$d->id}}" class="form-control" value="{{ $d->card_brand_reason_code}}" />
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-3">Reason.Code.Description</div>
                                    <div class="col-xs-9">
                                        <input type="text" name="reason_code_description_{{$d->id}}" id="reason_code_description_{{$d->id}}" class="form-control" value="{{ $d->reason_code_description}}" />
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-3">APP.ID</div>
                                    <div class="col-xs-9">
                                        <input type="text" name="app_id_{{$d->id}}" id="app_id_{{$d->id}}" class="form-control" value="{{ $d->app_id}}" />
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-3">Bakkar.Comments</div>
                                    <div class="col-xs-9">
                                        <input type="text" name="bakkar_comments_{{$d->id}}" id="bakkar_comments_{{$d->id}}" class="form-control" value="{{ $d->bakkar_comments}}" />
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-3">Customer.Service.Comments</div>
                                    <div class="col-xs-9">
                                        <input type="text" name="customer_service_comments_{{$d->id}}" id="customer_service_comments_{{$d->id}}" class="form-control" value="{{ $d->customer_service_comments}}" />
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-3">Groomer.Name</div>
                                    <div class="col-xs-9">
                                        <input type="text" name="groomer_name_{{$d->id}}" id="groomer_name_{{$d->id}}" class="form-control" value="{{ $d->groomer_name}}" />
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-3">Service.Date</div>
                                    <div class="col-xs-9">
                                        <input type="text" name="service_date_{{$d->id}}" id="service_date_{{$d->id}}" class="form-control" value="{{ $d->service_date}}" />
                                    </div>
                                </div>

                                <div class="row no-border">
                                    <div class="col-xs-3">Credit.Back</div>
                                    <div class="col-xs-9">
                                        <input type="text" name="credit_back_{{$d->id}}" id="credit_back_{{$d->id}}" class="form-control" value="{{ $d->credit_back}}" />
                                    </div>
                                </div>

                            </div>


                            <div class="modal-footer">
                                <button type="button" class="btn btn-info" onclick="hide_div_view_modal({{ $d->id }})">Close</button>
                                <button type="button" class="btn btn-primary" onclick="update_chargeback({{ $d->id }})">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif


            <div class="modal" id="upload_modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">x</span></button>
                        <h4 class="modal-title" id="title">Upload Charge Back</h4>
                    </div>
                    <div class="modal-body">

                        <form id="frm_upload" action="/admin/reports/chargeback/upload" class="form-horizontal filter"
                              method="post" style="padding:15px;" enctype="multipart/form-data">
                            {{ csrf_field() }}

                            <div class="form-group">
                                <label class="col-sm-4 control-label required">Select CSV File to Upload</label>
                                <div class="col-sm-8">
                                    <input type="file" class="form-control" name="charge_back_csv_file" id="charge_back_csv_file"/>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer" style="margin-right:15px;">
{{--                        <a class="btn btn-warning" href="/upload_template/pin_upload_template.xlsx" target="_blank">Download--}}
{{--                            Template</a>--}}
                        <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="file_upload()">Upload</button>
                    </div>
                </div>
            </div>
        </div>

    </div>


@stop