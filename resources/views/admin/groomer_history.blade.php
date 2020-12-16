
<hr class="">
<table id="table" class="table table-striped display" cellspacing="0" width="100%">
    <thead>
    <tr>
        <th class="text-center">ID</th>
        <th class="text-center">Service Time</th>
        <th class="text-center">Requested Time</th>
        <th class="text-center">Status</th>
        <th class="text-center">User Name</th>
        <th class="text-center">Phone</th>
        <th class="text-center">Address</th>
        <th class="text-center">Groomer</th>
        <th class="text-center">Promo.Amt</th>
        <th class="text-center">Total</th>
    </tr>
    </thead>
    <tbody>
    @if(count($appointments)>0)
        @foreach ($appointments as $ap)
            <tr @if($ap->status == 'C')style="background-color: #3e4255; color: #fff;"@endif>
                <td><a href="/admin/appointment/{{ $ap->appointment_id }}" target="_blank">{{ $ap->appointment_id }}</a></td>
                <td>{{ $ap->accepted_date }}</td>
                <td>{{ $ap->reserved_at }}</td>
                <td>{{ $ap->status_name }}</td>
                <td>{{ $ap->name }}</td>
                <td>{{ $ap->phone }}</td>
                <td>{{ $ap->address }}</td>
                <td>@if($ap->groomer_name)<a href="/admin/groomer/{{ $ap->groomer_id }}" target="_blank">{{ $ap->groomer_name }}</a>@endif</td>
                <td @if($ap->promo_amt > 0)style="color: #c9302c"@endif>${{ $ap->promo_amt }}</td>
                <td><b>${{ $ap->total }}</b></td>
            </tr>
        @endforeach
    @else
        <tr>
            <td colspan="10" class="text-center">No recent appointment</td>
        </tr>
    @endif
    </tbody>
</table>
<div class="padding-10">
    <button id="close_btn" type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>
