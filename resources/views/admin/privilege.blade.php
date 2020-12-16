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

            $( "#date" ).datetimepicker({
                format: 'YYYY-MM-DD'
            });

        };

        function update_privilege(id, url, group) {

            myApp.showLoading();
            $.ajax({
                url: '/admin/privilege/setup',
                data: {
                    group: group,
                    url: url
                },
                cache: false,
                type: 'get',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();

                    $('#msg_' + group + "_" + id).text(res.msg);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }
    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png"
            />Admin Privilege</h3>
    </div>

    <div class="container">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/privilege">
                {{ csrf_field() }}

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <select class="form-control" name="type">
                                <option value="B" {{ $type == 'B' ? 'selected' : '' }}>Button</option>
                                <option value="L" {{ $type == 'L' ? 'selected' : '' }}>Link</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                        </div>
                    </div>

                    <div class="col-md-4 text-right">
                        <div class="form-group">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary" id="btn_search">Search</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <table class="table table-bordered text-center" cellspacing="0" width="100%">
            <thead>
            <tr style="font-size: 12px;">
                <th class="text-center warning">Type</th>
                <th class="text-center warning">Label</th>
                <th class="text-center warning">Url/Code</th>
                <th class="text-center warning">CS1</th>
                <th class="text-center warning">CS2</th>
                <th class="text-center warning">SHIP1</th>
                <th class="text-center warning">SHIP2</th>
                <th class="text-center warning">ACCT1</th>
                <th class="text-center warning">ACCT2</th>
                <th class="text-center warning">PT1</th>
                <th class="text-center warning">PT2</th>
                <th class="text-center warning">MG1</th>
                <th class="text-center warning">MG2</th>
            </tr>
            </thead>
            <tbody>
            @php
                $i = 0;
            @endphp
            @foreach ($privileges as $p)
                @if ($i++ % 10 == 9)
                <tr style="font-size: 12px;">
                    <td class="text-center warning"></td>
                    <td class="text-center warning"></td>
                    <td class="text-center warning"></td>
                    <td class="text-center warning">CS1</td>
                    <td class="text-center warning">CS2</td>
                    <td class="text-center warning">SHIP1</td>
                    <td class="text-center warning">SHIP2</td>
                    <td class="text-center warning">ACCT1</td>
                    <td class="text-center warning">ACCT2</td>
                    <td class="text-center warning">PT1</td>
                    <td class="text-center warning">PT2</td>
                    <td class="text-center warning">MG1</td>
                    <td class="text-center warning">MG2</td>
                </tr>
                @endif
                <tr style="font-size: 12px;">
                    <td>{{ $p->type }}</td>
                    <td class="text-left">{{ $p->label }}</td>
                    <td class="text-left">{{ $p->url }}</td>
                    <td>
                        <input type="checkbox" onclick="update_privilege('{{ $p->id }}', '{{ $p->url }}', 'CS1')"
                                {{ empty($p->cs1_type) ? 'checked' : '' }}>
                        <br>
                        <small id="msg_CS1_{{ $p->id }}"></small>
                    </td>
                    <td>
                        <input type="checkbox" onclick="update_privilege('{{ $p->id }}', '{{ $p->url }}', 'CS2')"
                                {{ empty($p->cs2_type) ? 'checked' : '' }}>
                        <br>
                        <small id="msg_CS2_{{ $p->id }}"></small>
                    </td>
                    <td>
                        <input type="checkbox" onclick="update_privilege('{{ $p->id }}', '{{ $p->url }}', 'SHIP1')"
                                {{ empty($p->ship1_type) ? 'checked' : '' }}>
                        <br>
                        <small id="msg_SHIP1_{{ $p->id }}"></small>
                    </td>
                    <td>
                        <input type="checkbox" onclick="update_privilege('{{ $p->id }}', '{{ $p->url }}', 'SHIP2')"
                                {{ empty($p->ship2_type) ? 'checked' : '' }}>
                        <br>
                        <small id="msg_SHIP2_{{ $p->id }}"></small>
                    </td>
                    <td>
                        <input type="checkbox" onclick="update_privilege('{{ $p->id }}', '{{ $p->url }}', 'ACCT1')"
                                {{ empty($p->acct1_type) ? 'checked' : '' }}>
                        <br>
                        <small id="msg_ACCT1_{{ $p->id }}"></small>
                    </td>
                    <td>
                        <input type="checkbox" onclick="update_privilege('{{ $p->id }}', '{{ $p->url }}', 'ACCT2')"
                                {{ empty($p->acct2_type) ? 'checked' : '' }}>
                        <br>
                        <small id="msg_ACCT2_{{ $p->id }}"></small>
                    </td>
                    <td>
                        <input type="checkbox" onclick="update_privilege('{{ $p->id }}', '{{ $p->url }}', 'PT1')"
                                {{ empty($p->pt1_type) ? 'checked' : '' }}>
                        <br>
                        <small id="msg_PT1_{{ $p->id }}"></small>
                    </td>
                    <td>
                        <input type="checkbox" onclick="update_privilege('{{ $p->id }}', '{{ $p->url }}', 'PT2')"
                                {{ empty($p->pt2_type) ? 'checked' : '' }}>
                        <br>
                        <small id="msg_PT2_{{ $p->id }}"></small>
                    </td>
                    <td>
                        <input type="checkbox" onclick="update_privilege('{{ $p->id }}', '{{ $p->url }}', 'MG1')"
                                {{ empty($p->mg1_type) ? 'checked' : '' }}>
                        <br>
                        <small id="msg_MG1_{{ $p->id }}"></small>
                    </td>
                    <td>
                        <input type="checkbox" onclick="update_privilege('{{ $p->id }}', '{{ $p->url }}', 'MG2')"
                                {{ empty($p->mg2_type) ? 'checked' : '' }}>
                        <br>
                        <small id="msg_MG2_{{ $p->id }}"></small>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <form id="frm_search" class="form-horizontal" method="post" action="/admin/privilege/action/add">
                    {{ csrf_field() }}
                    <tr>
                        <th>
                            <select class="form-control" name="type">
                                <option value="B">Button</option>
                                <option value="L">Link</option>
                            </select>
                        </th>
                        <th>
                            <input type="text" name="label" class="form-control">
                        </th>
                        <th>
                            <input type="text" name="url" class="form-control">
                        </th>
                        <th colspan="10"><button type="submit" class="btn btn-sm btn-primary">Add New</button></th>
                    </tr>
                </form>
            </tfoot>
        </table>

        <div class="row">
            <div class="col-xs-1 text-center" style="border: 1px solid #ddd;"> CS1 </div><div class="col-xs-11">
                Admin Groomer applications, Admin Users, Profit sharing, Affiliates, Reports</div>
        </div>

        <div class="row">
            <div class="col-xs-1 text-center" style="border: 1px solid #ddd;"> CS2 </div><div class="col-xs-11">
                Staff - Adjustments</div>
        </div>

        <div class="row">
            <div class="col-xs-1 text-center" style="border: 1px solid #ddd;"> SHIP1 </div><div class="col-xs-11">
                Allow Order only</div>
        </div>

        <div class="row">
            <div class="col-xs-1 text-center" style="border: 1px solid #ddd;"> SHIP2 </div><div class="col-xs-11">
                Allow Order only: Optional use</div>
        </div>

        <div class="row">
            <div class="col-xs-1 text-center" style="border: 1px solid #ddd;"> ACCT1 </div><div class="col-xs-11">
                Accounting</div>
        </div>

        <div class="row">
            <div class="col-xs-1 text-center" style="border: 1px solid #ddd;"> ACCT2 </div><div class="col-xs-11">
                Accounting: Optional use</div>
        </div>

        <div class="row">
            <div class="col-xs-1 text-center" style="border: 1px solid #ddd;"> PT1 </div><div class="col-xs-11">
                Partner</div>
        </div>

        <div class="row">
            <div class="col-xs-1 text-center" style="border: 1px solid #ddd;"> PT2 </div><div class="col-xs-11">
                Partner, Optional Use</div>
        </div>

        <div class="row">
            <div class="col-xs-1 text-center" style="border: 1px solid #ddd;"> MG1 </div><div class="col-xs-11">
                Mgmt 1 : Allow all</div>
        </div>

        <div class="row">
            <div class="col-xs-1 text-center" style="border: 1px solid #ddd;"> MG2 </div><div class="col-xs-11">
                Mgmt 2 : Allow all, Optional Use(No PL)</div>
        </div>

    </div>
@stop