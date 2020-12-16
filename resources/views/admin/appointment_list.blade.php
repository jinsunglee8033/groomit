@extends('includes.admin_default')
@section('contents')
    <h3>Appointments</h3>
    <hr class="">
    <table id="table" class="table table-striped display" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>Requested At</th>
            <th>Reserved At</th>
            <th>Service Address</th>
            <th>Groomer</th>
            <th>Pet</th>
            <th>Photo</th>
            <th>Status</th>
        </tr>
        </thead>

        <tbody>
        @foreach ($appointments as $ap)
            <tr id="appointment/{{ $ap->appointment_id }}">
                <td>{{ $ap->cdate }}</td>
                <td>{{ $ap->reserved_at }}<br>
                    @if($ap->accepted_date)
                        Accepted Date: <b>{{ $ap->accepted_date }}</b>
                    @endif
                </td>
                <td>{{ $ap->address }}</td>
                <td>{{ $ap->groomer["name"] }}{{ $ap->pet}}</td>
                <td>{{ $ap->pet_name }}<br>{{ $ap->age }}</td>
                <td>
                    @if($ap->pet_photo)
                        <img height="40" src="data:image/gif;base64,{{ $ap->pet_photo }}" />
                    @endif
                </td>
                <td>{{ $ap->status_name }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
@stop