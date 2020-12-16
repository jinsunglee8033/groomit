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
        <h3 class="head-title text-center"><img class="img-respondive top-logo-img" src="/images/top-logo.png" />PETS</h3>
    </div>

    <div class="container">

    <div class="well filter" style="padding-bottom:5px;">
        <form id="frm_search" class="form-horizontal" method="post" action="/admin/pets">
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
                        <label class="col-md-4 control-label">Size</label>
                        <div class="col-md-8">
                            <select class="form-control" name="size" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false}'>
                                <option value="" {{ old('size', $size) == '' ? 'selected' : '' }}>All</option>
                                <option value="2" {{ old('size', $size) == '2' ? 'selected' : '' }}>Small</option>
                                <option value="3" {{ old('size', $size) == '3' ? 'selected' : '' }}>Medium</option>
                                <option value="4" {{ old('size', $size) == '4' ? 'selected' : '' }}>Large</option>
                                <option value="5" {{ old('size', $size) == '5' ? 'selected' : '' }}>Extra Large</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Owner</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="owner" value="{{ old('owner', $owner) }}"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Pet Type</label>
                        <div class="col-md-8">
                            <select class="form-control" name="pet_type" data-jcf='{"wrapNative": false,
                            "wrapNativeOnMobile": false}'>
                                <option value="" {{ old('pet_type', $pet_type) == '' ? 'selected' : '' }}>All</option>
                                <option value="dog" {{ old('pet_type', $pet_type) == 'dog' ? 'selected' : ''
                                }}>Dog</option>
                                <option value="cat" {{ old('pet_type', $pet_type) == 'cat' ? 'selected' : ''
                                }}>Cat</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Pet Name</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="name" value="{{ old('name', $name) }}"/>
                        </div>

                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-4">
                            @if (\App\Lib\Helper::get_action_privilege('pets_search', 'Pets Search'))
                            <button type="submit" class="btn btn-primary btn-sm" id="btn_search">Search</button>
                            @endif
                            @if (\App\Lib\Helper::get_action_privilege('pets_export', 'Pets Export'))
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
            <td colspan="7" class="text-right"><strong>TOTAL</strong>: {{ $total }}</td>
        </tr>
        <tr>
            <th>ID</th>
            <th>Owner</th>
            <th>Pet Type</th>
            <th>Pet Name</th>
            <th>Age (D.O.B.)</th>
            <th>Gender</th>
            <th>Size</th>
            <th>Breed</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($pets as $p)
            <tr id="pet/{{ $p->pet_id }}">
                <td>{{ $p->pet_id }}</td>
                <td>{{ $p->owner }}</td>
                <td>{{ $p->type }}</td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->new_dob }}</td>
                <td>{{ $p->gender }}</td>
                <td>{{ $p->size }}</td>
                <td>{{ $p->breed }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="text-right">
        {{ $pets->appends(Request::except('page'))->links() }}
    </div>
    </div>
@stop
