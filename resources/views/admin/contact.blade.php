@extends('includes.admin_default')
@section('contents')

    <script type="text/javascript">
    function update(id, status) {
        alert(id);
        $('#id').val(id);
        $('#status').val(status);
        $('#frm_update').submit();
    }
    </script>

    <h3>Contact Detail <a href="/admin/contacts" class="btn btn-default">Back</a>
        <div class="btn-right btn btn-info" data-toggle="modal" data-target="#reply">Reply</div>
        @if ($c->status != 'C')
        <div class="btn-right btn btn-primary" data-toggle="modal" data-target="#mark_as_closed">Mark As Closed</div>
        @endif
        {{--<button class="btn-right btn btn-warning" onclick="update({{ $c->contact_id }}, 'C')">Close</button>--}}
    </h3>

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

    <hr>

    <form id="frm_update" method="post" action="/admin/contact/update">
        {{ csrf_field() }}
        <input type="hidden" name="id" id="id" />
        <input type="hidden" name="status" id="status" />
    </form>

    <!-- Reply Modal Start -->
    <div class="modal" id="reply" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Reply</h4>
                </div>
                <form method="post" name="assign_groomer" action="/admin/contact/reply" class="form-group">
                    {!! csrf_field() !!}
                    <div class="modal-body">
                        <div class="row padding-10">
                            <div class="col-xs-3">Name</div>
                            <div class="col-xs-8">
                                {{ $c->first_name }} {{ $c->last_name }}
                            </div>
                        </div>
                        <div class="row padding-10">
                            <div class="col-xs-3">User's Message</div>
                            <div class="col-xs-8">
                                <strong>{{$c->subject}}</strong><br><br>
                                {{ $c->message }}
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-xs-3">Reply</div>
                            <div class="col-xs-8">
                                <textarea class="form-control" name="message" rows="7"></textarea>
                            </div>
                        </div>
                        <input type="hidden" name="id" value="{{$c->contact_id}}" />
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-warning" type="submit">Send</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Reply Modal End -->

    <!-- Mark as Close Modal Start -->
    <div class="modal" id="mark_as_closed" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Mark As Closed</h4>
                </div>
                <form method="post" name="assign_groomer" action="/admin/contact/close" class="form-group">
                    {!! csrf_field() !!}
                    <div class="modal-body">
                        <div class="row padding-10">
                            <div class="col-xs-3">Name</div>
                            <div class="col-xs-8">
                                {{ $c->first_name }} {{ $c->last_name }}
                            </div>
                        </div>
                        <div class="row padding-10">
                            <div class="col-xs-3">User's Message</div>
                            <div class="col-xs-8">
                                <strong>{{$c->subject}}</strong><br><br>
                                {{ $c->message }}
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-xs-3">Closing Note</div>
                            <div class="col-xs-8">
                                <textarea class="form-control" name="note" rows="7"></textarea>
                            </div>
                        </div>
                        <input type="hidden" name="id" value="{{$c->contact_id}}" />
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-warning" type="submit">Submit</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Mark as Close Modal End -->


    <div class="detail">
        <div class="row">
            <div class="col-xs-3">ID</div>
            <div class="col-xs-9">{{ $c->contact_id }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Type</div>
            <div class="col-xs-9">{{ $c->type }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Status</div>
            <div class="col-xs-9">{{ $c->status_name }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">User</div>
            <div class="col-xs-9">{{ $c->first_name }} {{ $c->last_name }} (<a href="/admin/user/{{ $c->user_id }}" target="_blank">{{ $c->user_id }}</a>)</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Phone</div>
            <div class="col-xs-9">{{ $c->phone }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Email</div>
            <div class="col-xs-9">{{ $c->email }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Subject</div>
            <div class="col-xs-9">{{ $c->subject }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Message</div>
            <div class="col-xs-9">{{ $c->message }}</div>
        </div>
        <div class="row">
            <div class="col-xs-3">Date</div>
            <div class="col-xs-9">{{ $c->cdate }}</div>
        </div>

    </div>


    <!-- Reply list -->
    <div class="detail">
        <div class="row">
            <div class="col-xs-12 text-center" style="background-color:#eee;"><h4>Reply</h4></div>
        </div>

        @if (!$reply->isEmpty())
            <div class="row">
                <div class="col-xs-2">Date</div>
                <div class="col-xs-2">Author</div>
                <div class="col-xs-8">Contents</div>
            </div>
            @foreach($reply as $r)
            <div class="row">
                <div class="col-xs-2">{{ $r->cdate }}</div>
                <div class="col-xs-2">{{ $r->name }}</div>
                <div class="col-xs-8">{{ $r->message }}</div>
            </div>
            @endforeach
        @else
            <div class="row text-center text-danger">
                No reply yet
            </div>
        @endif
    </div>

    @if ($c->note)
    <!-- Close Note -->
    <div class="detail">
        <div class="row">
            <div class="col-xs-12 text-center" style="background-color:#eee;"><h4>Closing Note</h4></div>
        </div>
        <div class="row text-info">
            {{ $c->note }}
        </div>
    </div>
    @endif
@stop