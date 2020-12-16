@extends('includes.admin_default')
@section('contents')
    <h3>Admin User Detail
        <a href="/admin/admins" class="btn btn-default">Back</a>
        <div class="btn-right btn btn-info" data-toggle="modal" data-target="#update">Update Profile</div>
        <div class="btn-right btn btn-success" data-toggle="modal" data-target="#reset_password">Reset Password</div>
    </h3>
    <hr class="">
    <div class="detail application">
        @if ($alert = Session::get('alert'))
            @if ($alert == 'Success')
                <div class="alert alert-success detail">
                    {{ $alert }}
                </div>
            @else
                <div class="alert alert-danger detail">
                    {{ $alert }}
                </div>
            @endif
        @endif

        <div class="row">
            <div class="col-xs-3">Name</div>
            <div class="col-xs-9">{{ $admin->name }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Email</div>
            <div class="col-xs-9">{{ $admin->email }}</div>
        </div>
            <div class="row">
                <div class="col-xs-3">Group</div>
                <div class="col-xs-9">{{ $admin->group }}</div>
            </div>
        <div class="row">
            <div class="col-xs-3">Status</div>
            <div class="col-xs-9">{{ $admin->status_name }} &nbsp; <a href="/admin/change_admin_status/{{ $admin->admin_id }}" class="btn btn-primary small">Change Status</a></div>
        </div>
        <div class="row">
            <div class="col-xs-3">Last Login</div>
            <div class="col-xs-9">{{ $admin->last_login_date }}</div>
        </div>
    </div>

    <!-- Reset Password Modal start-->
    <div class="modal fade" id="reset_password" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Reset Password</h4>
                </div>
                <form method="post" name="update" action="/admin/admin/reset_password" class="form-group">
                    {!! csrf_field() !!}

                    <input type="hidden" name="id" value="{{$admin->admin_id}}" />
                    <div class="modal-body">
                        <div class="row no-border">
                            <div class="col-xs-4">Password</div>
                            <div class="col-xs-8">
                                <input type="password" name="password" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="row no-border">
                            <div class="col-xs-4">Confirm Password</div>
                            <div class="col-xs-8">
                                <input type="password" name="confirm_password" class="form-control" value=""/>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success" type="submit">Reset</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Reset Password Modal end-->

    <!-- Update Profile Modal start-->
    <div class="modal fade" id="update" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Update User's Information</h4>
                </div>
                <form method="post" name="update" action="/admin/admin/update" class="form-group">
                    {!! csrf_field() !!}

                    <input type="hidden" name="id" value="{{$admin->admin_id}}" />
                    <div class="modal-body">
                        <div class="row no-border">
                            <div class="col-xs-3">Name</div>
                            <div class="col-xs-9">
                                <input type="text" name="name" class="form-control" value="{{ $admin->name }}" />
                            </div>
                        </div>

                        <div class="row no-border">
                            <div class="col-xs-3">Group</div>
                            <div class="col-xs-9">
                                <select class="form-control" id="group" name="group">
                                    <option value="">Select</option>
                                    <option value="CS1" {{ old('group', $admin->group) == 'CS1' ? 'selected' : '' }}>CS1</option>
                                    <option value="CS2" {{ old('group', $admin->group) == 'CS2' ? 'selected' : '' }}>CS2</option>
                                    <option value="SHIP1" {{ old('group', $admin->group) == 'SHIP1' ? 'selected' : '' }}>SHIP1</option>
                                    <option value="SHIP2" {{ old('group', $admin->group) == 'SHIP2' ? 'selected' : '' }}>SHIP2</option>
                                    <option value="ACCT1" {{ old('group', $admin->group) == 'ACCT1' ? 'selected' : '' }}>ACCT1</option>
                                    <option value="ACCT2" {{ old('group', $admin->group) == 'ACCT2' ? 'selected' : '' }}>ACCT2</option>
                                    <option value="PT1" {{ old('group', $admin->group) == 'PT1' ? 'selected' : '' }}>PT1</option>
                                    <option value="PT2" {{ old('group', $admin->group) == 'PT2' ? 'selected' : '' }}>PT2</option>
                                    <option value="MG1" {{ old('group', $admin->group) == 'MG1' ? 'selected' : '' }}>MG1</option>
                                    <option value="MG2" {{ old('group', $admin->group) == 'MG2' ? 'selected' : '' }}>MG2</option>

{{--                                    @foreach ($groups as $o)--}}
{{--                                        <option value="{{ $o->group }}" {{ old('status', $admin->group) == $o->group ? 'selected' : '' }}>{{ $o->group }}</option>--}}
{{--                                    @endforeach--}}
                                </select>
{{--                                <input type="group" name="group" class="form-control" value="{{ $admin->group }}" />--}}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success" type="submit">UPDATE</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Update Profile Modal end-->

@stop