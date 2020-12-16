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

        function day_update(id, day, val) {

            myApp.showLoading();
            $.ajax({
                url: '/admin/availableDaysByCounty/day_update',
                data: {
                    id: id,
                    day: day,
                    val: val
                },
                cache: false,
                type: 'get',
                dataType: 'json',
                success: function(res) {
                    myApp.hideLoading();
                    $('#msg_' + id + "_" + day).text(res.msg);
                    window.location.href = '/admin/availableDaysByCounty';
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    myApp.hideLoading();
                    myApp.showError(errorThrown);
                }
            });
        }

    </script>

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />Available Days by County</h3>
    </div>

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/groomers-by-countysummary">
                {{ csrf_field() }}
                <input type="hidden" name="excel" id="excel"/>

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
                <th style="text-align: center;">County</th>
                <th style="text-align: center;">Monday</th>
                <th style="text-align: center;">Tuesday</th>
                <th style="text-align: center;">Wednesday</th>
                <th style="text-align: center;">Thursday</th>
                <th style="text-align: center;">Friday</th>
                <th style="text-align: center;">Saturday</th>
                <th style="text-align: center;">Sunday</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($results as $r)
                <tr>
                    <td style="text-align: center;">{{ $r->area_name }}</td>
                    <td style="text-align: center;">
                        <input type="checkbox" onclick="day_update('{{ $r->area_id }}', 'mon', '{{$r->mon}}')" {{ ($r->mon == 'Y') ? 'checked' : '' }}>
                        <br>
                        <small id="msg_{{$r->area_id}}_mon"></small>
                    </td>
                    <td style="text-align: center;">
                        <input type="checkbox" onclick="day_update('{{ $r->area_id }}', 'tue', '{{$r->tue}}')" {{ ($r->tue == 'Y') ? 'checked' : '' }}>
                        <br>
                        <small id="msg_{{$r->area_id}}_tue"></small>
                    </td>
                    <td style="text-align: center;">
                        <input type="checkbox" onclick="day_update('{{ $r->area_id }}', 'wed', '{{$r->wed}}')" {{ ($r->wed == 'Y') ? 'checked' : '' }}>
                        <br>
                        <small id="msg_{{$r->area_id}}_wed"></small>
                    </td>
                    <td style="text-align: center;">
                        <input type="checkbox" onclick="day_update('{{ $r->area_id }}', 'thu', '{{$r->thu}}')" {{ ($r->thu == 'Y') ? 'checked' : '' }}>
                        <br>
                        <small id="msg_{{$r->area_id}}_thu"></small>
                    </td>
                    <td style="text-align: center;">
                        <input type="checkbox" onclick="day_update('{{ $r->area_id }}', 'fri', '{{$r->fri}}')" {{ ($r->fri == 'Y') ? 'checked' : '' }}>
                        <br>
                        <small id="msg_{{$r->area_id}}_fri"></small>
                    </td>
                    <td style="text-align: center;">
                        <input type="checkbox" onclick="day_update('{{ $r->area_id }}', 'sat', '{{$r->sat}}')" {{ ($r->sat == 'Y') ? 'checked' : '' }}>
                        <br>
                        <small id="msg_{{$r->area_id}}_sat"></small>
                    </td>
                    <td style="text-align: center;">
                        <input type="checkbox" onclick="day_update('{{ $r->area_id }}', 'sun', '{{$r->sun}}')" {{ ($r->sun == 'Y') ? 'checked' : '' }}>
                        <br>
                        <small id="msg_{{$r->area_id}}_sun"></small>
                    </td>
                </tr>
            @endforeach
            </tbody>

            <tfoot>

            </tfoot>
        </table>

    </div>


@stop
