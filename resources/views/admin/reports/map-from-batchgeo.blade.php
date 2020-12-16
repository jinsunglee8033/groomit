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
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />Map from BatchGEO</h3>
    </div>

    <div class="container-fluid">
        <div class="well filter" style="padding-bottom:5px;">
            <form id="frm_search" class="form-horizontal" method="post" action="/admin/reports/groomers-by-countysummary">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Link</label>
                            <div class="col-md-8">
                                <input type="text" style="width:100px; float:left;" class="form-control" id="sdate" name="sdate" value=""/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group text-right">
                            <div class="col-md-8 col-md-offset-8">
                                <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>


        <div class="container-fluid">
            <div class="row">
                <iframe src="https://batchgeo.com/map/43237eb945215581b25f39efce039073" scrolling="yes" style="width: 100%; height: 1500px; border: 0; "></iframe>
            </div>
        </div>



    </div>


@stop
