<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Label</title>

    <!-- Normalize or reset CSS with your favorite library -->
    <link rel="stylesheet" href="/css/receipt/normalize.css">

    <!-- Load paper.css for happy printing -->
    <link rel="stylesheet" href="/css/receipt/paper.css">

    <!-- Set page size here: A5, A4 or A3 -->
    <!-- Set also "landscape" if you need -->
    <style>@page { size: A4 }</style>

    <!-- Custom styles for this document -->
    <link href='https://fonts.googleapis.com/css?family=Tangerine:700' rel='stylesheet' type='text/css'>
    <style>
        body   { font-family: serif }
        h1     { font-size: 40pt; line-height: 18mm}
        h2     { font-size: 19pt; line-height: 2mm }
        h3     { font-size: 14pt; line-height: 7mm }
        h4     { font-size: 32pt; line-height: 14mm }
        h2 + p { font-size: 18pt; line-height: 7mm }
        h3 + p { font-size: 14pt; line-height: 7mm }
        li     { font-size: 11pt; line-height: 5mm }
        h1      { margin: 0 }
        h1 + ul { margin: 2mm 0 5mm }
        h3  { margin: 0 3mm 3mm 0; float: left }
        h3 + p  { margin: 0 0 3mm 30mm;  }
        h4      { margin: 2mm 0 0 30mm; border-bottom: 2px solid black }
        h4 + ul { margin: 5mm 0 0 30mm }
        td { padding: 6px; border: 1px solid #ccc; font-size: 14px;}
    </style>
</head>

<!-- Set "A5", "A4" or "A3" for class name -->
<!-- Set also "landscape" if you need -->
<body class="A4">

<!-- Each sheet element should have the class "sheet" -->
<!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
<section class="sheet padding-20mm">



    <article>
        <h3><img class="img-respondive top-logo-img" src="/images/top-logo.png" />Appointment Detail</h3>

        <table style="width:100%">
            <tbody>
            <tr>
                <td style="width:30%;">
                    Appointment ID
                </td>
                <td class="col-xs-9">{{ $ap->appointment_id }}</td>
            </tr>

            <tr>
                <td>Amount</td>
                <td>${{ $ap->total }}</td>
            </tr>

            <tr>
                <td>Customer Name</td>
                <td>{{ $ap->user->first_name }} {{ $ap->user->last_name }}</td>
            </tr>

            <tr>
                <td>Service Address</td>
                <td>
                    {{ $ap->address }}
                </td>
            </tr>

            <tr>
                <td>Phone #</td>
                <td>{{ $ap->user->phone }}</td>
            </tr>

            <tr>
                <td>Email</td>
                <td>{{ $ap->user->email }}</td>
            </tr>

            @if (!empty($ap->groomer))
                <tr>
                    <td>Groomer Name</td>
                    <td>{{ $ap->groomer->first_name }} {{ $ap->groomer->last_name }}</td>
                </tr>
            @endif
            <tr class="row">
                <td>Date</td>
                <td>{{ $ap->cdate }}</td>
            </tr>
            </tbody>
        </table>

    </article>

    <article>
        <h5>Billing info like below</h5>
        <table style="width:100%">
        <tbody>
            @if (!empty($ap->billing))
                <tr>
                    <td style="width:30%;">Card Holder</td>
                    <td>{{ $ap->billing->card_holder }}</td>
                </tr>
                <tr class="row">
                    <td>Card Number</td>
                    <td>{{ $ap->billing->card_number }}</td>
                </tr>
                <tr class="row">
                    <td>Expire</td>
                    <td>{{ $ap->billing->expire_mm }}/{{ $ap->billing->expire_yy }}</td>
                </tr>
                <tr class="row">
                    <td>Address</td>
                    <td>{{ $ap->billing->address1 . ' ' . $ap->billing->address2 . ', ' .
            $ap->billing->city . ', ' . $ap->billing->state . ' ' . $ap->billing->zip }}</td>
                </tr>
                 @if (!empty($ap->cc_trans_appt))
                     @foreach ($ap->cc_trans_appt as $cc_tx)
                            <tr class="row">
                            <td>Appointment Amount/Date</td>
                            <td>$ {{ $cc_tx->amt }} | {{ $cc_tx->cdate }} </td>
                            </tr>
                    @endforeach
                @endif
            @endif

        </tbody>
        </table>

        @if ( (!empty($ap->cc_trans_tip) && count($ap->cc_trans_tip)>0) ||  (!empty($ap->cc_trans_resc) && count($ap->cc_trans_resc)>0) || (!empty($ap->cc_trans_cancel) && count($ap->cc_trans_cancel)>0))

        <h5>Other Transactions</h5>
        <table style="width:100%">
            <tbody>
                @if (!empty($ap->cc_trans_tip))
                    @foreach ($ap->cc_trans_tip as $cc_tx)
                     <tr class="row">
                            <td>TIP Amount/Date</td>
                             <td>$ {{ $cc_tx->amt }} | {{ $cc_tx->cdate }} </td>
                     </tr>
                    @endforeach
                @endif

                @if (!empty($ap->cc_trans_resc))
                    @foreach ($ap->cc_trans_resc as $cc_tx)
                        <tr class="row">
                            <td>Rescheduling/Cancel Fee/Date</td>
                            <td>$ {{ $cc_tx->amt }} | {{ $cc_tx->cdate }} </td>
                        </tr>
                    @endforeach
                @endif

                @if (!empty($ap->cc_trans_cancel))
                    @foreach ($ap->cc_trans_cancel as $cc_tx)
                        <tr class="row">
                            <td>Cancel Fee/Date</td>
                            <td>$ {{ $cc_tx->amt }} | {{ $cc_tx->cdate }} </td>
                        </tr>
                    @endforeach
                @endif
        </tbody>
        </table>
        @endif



    </article>

</section>
<script type="text/javascript" src="/js/jquery.min.js"></script>

<script type="text/javascript">
    $( document ).ready(function() {
        window.print();
    });
</script>

</body>

</html>

