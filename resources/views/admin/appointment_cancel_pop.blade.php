
<script type="text/javascript" class="init">
$('#table tbody').children('tr').css('cursor','pointer');

    $('#table tbody').on('click', 'tr', function () {
        window.location.href = '/admin/' + this.id;
    });
</script>
<div class="padding-10">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<h3>Cancelled Appointments</h3>
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
        <th class="text-center">Sub Total</th>
        <th class="text-center">Promo Amt</th>
        <th class="text-center">Credit Amt</th>
        <th class="text-center">Safety Insurance</th>
        <th class="text-center">Tax</th>
        <th class="text-center">Total</th>
        <th class="text-center">Groomer</th>
    </tr>
    </thead>
    <tbody>
@if(count($appointments)>0)
    @foreach ($appointments as $ap)
        <tr id="appointment/{{ $ap->appointment_id }}">
            <td>{{ $ap->appointment_id }}</td>
            <td>{{ $ap->accepted_date }}</td>
            <td>{{ $ap->reserved_at }}</td>
            <td>{{ $ap->status_name }}</td>
            <td>{{ $ap->name }}</td>
            <td>{{ $ap->phone }}</td>
            <td>{{ $ap->address }}</td>
            <td>${{ $ap->sub_total }}</td>
            <td>${{ $ap->promo_amt }}</td>
            <td>${{ $ap->credit_amt }}</td>
            <td>${{ $ap->safety_insurance }}</td>
            <td>${{ $ap->tax }}</td>
            <td>${{ $ap->total }}</td>
            <td>{{ $ap->groomer_name }}</td>
        </tr>
    @endforeach
@else
    <tr>
        <td colspan="14" class="text-center">No upcoming appointment</td>
    </tr>
    @endif
    </tbody>
    </table>
    <div class="padding-10">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>
