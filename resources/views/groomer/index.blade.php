@extends('includes.groomer_default')
@section('contents')

<script type="text/javascript" src="https://s3.amazonaws.com/eversign-embedded-js-library/eversign.embedded.latest.js"></script>

<div class="container-fluid top-cont">
    <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png"
        />Groomer Documents</h3>
</div>

<div class="container-fluid">
    <div class="well filter" style="padding-bottom:5px;">

        <div class="row category" style="margin:0;">Trial Document Status</div>
        <div class="row no-border" style="margin:0;">
            <table class="table" style="font-size: 12px;">
                <thead>
                <tr>
                    <th>Type</th>
                    <th>File</th>
                    <th>Signed</th>
                    <th>Created</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($documents as $d)
                    <tr>
                        <td>{{ $d->type_name }}</td>
                        <td>
                            @if ($d->signed == 'Y')
                                @if (!empty($d->e_doc_id))
                                    <a href="https://api.eversign.com/api/download_final_document?access_key=30581f03071c1cf3d21eda05fbf32c39&business_id=56514&document_hash={{ $d->e_doc_id }}" target="_blank">Show eSignature Document</a>
                                @else
                                @endif
                            @endif
                        </td>
                        <td>{{ $d->signed == 'Y' ? 'Signed' : '' }}</td>
                        <td>{{ empty($d->cdate) ? '' : $d->cdate }}</td>
                        <td style="text-align: right;">
                            @if ($d->signed !== 'Y')
                                @if (empty($d->e_doc_id))
                            <button type="button" class="btn btn-info btn-sm" onclick="esign_apply('{{ $d->type }}')">eSign</button>
                                @else
                                    @if (!empty($d->esign_url))
                                        <button type="button" class="btn btn-info btn-sm" onclick="show_esign('{{
                                        $d->esign_url }}', '{{ $d->type }}', {{ $d->id }})">eSignature</button>
                                        @else
                                        eSign Requested
                                        @endif
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
                @foreach ($document_others as $d)
                    <tr>
                        <td>{{ $d->type_name }}</td>
                        <td>
                            @if (!empty($d->file_name))
                                <a href="/groomer/document/view/{{ $groomer->groomer_id }}/{{ $d->id }}">
                                    {{ $d->file_name }}
                                </a>
                            @endif
                        </td>
                        <td>{{ $d->signed == 'Y' ? 'Signed' : '' }}</td>
                        <td>{{ empty($d->cdate) ? '' : $d->cdate }}</td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-info btn-sm" onclick="document_upload('{{
                                    $d->type }}', '{{ $d->type_name }}')">Upload</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                </tfoot>
            </table>

            <script>
                function esign_apply(type) {
                    if (type == 'A') {
                        $('#modal_esign').modal();
                    } else {
                        esign_apply_submit(type)
                    }
                }

                function esign_apply_submit(type) {
                    myApp.showLoading();

                    $.ajax({
                        url: '/groomer/esign/' + type,
                        data: {
                            bank_name: $('#bank_name').val(),
                            account_holder: $('#account_holder').val(),
                            account_number: $('#account_number').val(),
                            routing_number: $('#routing_number').val(),
                        },
                        cache: false,
                        type: 'get',
                        dataType: 'json',
                        success: function (res) {
                            myApp.hideLoading();

                            if (res.code == '0') {
                                $('#modal_esign').modal('hide');
                                show_esign(res.esign_url, type, res.doc_id);
                            } else {
                                myApp.showError(res.msg);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            myApp.hideLoading();
                            myApp.showError(errorThrown);
                        }
                    });
                }

                function show_esign(esign_url, type, doc_id) {
                    var width = $(window).width();
                    var height = $(window).height();

                    eversign.open({
                        url: esign_url,
                        containerID: "esign_box",
                        width: width - 8,
                        height: height - 8,
                        events: {
                            loaded: function () {
                            },
                            signed: function () {
                                $('#esign_box').empty();
                                window.location.href = '/eversign/complete/' + doc_id;
                            },
                            declined: function () {
                                $('#esign_box').empty();
                                window.location.href = '/eversign/declined/' + doc_id;
                            },
                            error: function () {
                                $('#esign_box').empty();
                                esign_apply(type);
                            }
                        }
                    });
                }
            </script>

            <hr>
        </div>

        <div id="esign_box" style="text-align: center;">

        </div>
    </div>
</div>

<!-- Send Modal Start -->
<div class="modal" id="modal_esign" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">REJECT REASON</h4>
            </div>
            <div class="form-group">
                <form method="get" class="form-group">
                    <div class="modal-body">
                        <div class="row padding-10">
                            <div class="col-xs-3">Name of Bank</div>
                            <div class="col-xs-8">
                                <input type="text" id="bank_name" value="" class="form-control"/>
                            </div>
                        </div>
                        <div class="row padding-10">
                            <div class="col-xs-3">Account Holder</div>
                            <div class="col-xs-8">
                                <input type="text" id="account_holder" value="" class="form-control"/>
                            </div>
                        </div>
                        <div class="row padding-10">
                            <div class="col-xs-3">Account Number</div>
                            <div class="col-xs-8">
                                <input type="text" id="account_number" value="" class="form-control"/>
                            </div>
                        </div>
                        <div class="row padding-10">
                            <div class="col-xs-3">Routing Number</div>
                            <div class="col-xs-8">
                                <input type="text" id="routing_number" value="" class="form-control"/>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger" type="button" onclick="esign_apply_submit('A')">Submit</button>
                        <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Send Modal End -->


<script type="text/javascript">
    function document_upload(type, name) {
        $('#document_type').val(type);
        $('#document_upload_title').text(name);

        $('#document_upload_modal').modal();
    }
</script>
<!-- Document Upload Start -->
<div class="modal" id="document_upload_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="/groomer/document/upload" class="form-group"
                  enctype="multipart/form-data">
                {!! csrf_field() !!}

                <input type="hidden" name="type" id="document_type">
                <input type="hidden" name="groomer_id" value="{{ $groomer->groomer_id }}">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel"><span id="document_upload_title"></span> Upload </h4>
                </div>
                <div class="modal-body text-left">
                    <div class="form-group">
                        <label>Select File</label>
                        <input type="file" name="document_file" value="" class="form-control"/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
                    <button class="btn btn-danger" type="submit">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Document Upload End -->
@stop