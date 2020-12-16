<script type="text/javascript" class="init">
$('#table tbody').children('tr').css('cursor','pointer');

    $('#table tbody').on('click', 'tr', function () {
        window.location.href = '/admin/' + this.id;
    });
</script>
<div class="padding-10">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<h3>Voucher Sales (In 365 Days)</h3>
<hr class="">
<table id="table" class="table table-striped display" cellspacing="0" width="100%">
    <thead>
    <tr>
        <th style="text-align: center;">ID</th>
        <th style="text-align: center;">Date.Time</th>
        <th style="text-align: center;">Amt</th>
        <th style="text-align: center;">Cost</th>
        <th style="text-align: center;">Sendor</th>
        <th style="text-align: center;">Recipient<br>Name</th>
        <th style="text-align: center;">Recipient<br>Email</th>
    </tr>
    </thead>
    <tbody>
@if(count($sales)>0)
    @foreach($sales as $o)
        <tr><td style="text-align: center;">{{ $o->id  }}</td>
            <td>{{ $o->cdate }}</td>
            <td style="text-align: right;">${{ number_format($o->amt,2) }}</td>
            <td style="text-align: right;">${{ number_format($o->cost,2) }}</td>
            <td>{{ $o->sender }}</td>
            <td>{{ $o->recipient_name }}</td>
            <td>{{ $o->recipient_email }}</td>
        </tr>
    @endforeach
@else
    <tr>
        <td colspan="7" class="text-center">No Voucher Sales</td>
    </tr>
    @endif
    </tbody>
    </table>
    <div class="padding-10">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>
