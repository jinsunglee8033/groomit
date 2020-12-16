@extends('includes.admin_default')
@section('contents')

    <div class="container-fluid top-cont">
        <h3 class="head-title text-center">
            <img class="img-respondive top-logo-img" src="/images/top-logo.png" />Groomer</h3>
    </div>

    <div class="container-fluid">
        <div id="section-to-print" class="detail application">
            <div class="row category" style="margin:0;">Order Detail</div>
            <div class="row">
                <div class="col-md-8 col-md-offset-4 text-right">
                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-4">
                            <input type="button" class="btn btn-primary" onclick="window.print()" value="print"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row no-border" style="margin:0;">
                <table class="table" style="font-size: 12px;">
                    <div class="row category" style="margin:0;">
                        Groomer Name : {{ $order->groomer->first_name }} {{ $order->groomer->last_name }}
                    </div>
                    <thead>
                        <tr>
                            <th>Order#</th>
                            <th>Category</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach ($order->details as $d)
                                <td>{{ $d->id }}</td>
                                <td>{{ $d->prod_type }}</td>
                                <td>{{ $d->prod_name }}</td>
                                <td>{{ $d->qty }}</td>
                                <td>{{ $d->status == 'N' ? 'New' : 'Shipped' }}</td>
                        </tr>
                            @endforeach
                        </tr>

                    </tbody>
                </table>
                <hr>
                <div style="text-align: right; margin-right: 80px;">
                    Address : {{ $order->groomer->street}} {{ $order->groomer->city . ', ' .$order->groomer->state . ' ' . $order->groomer->zip}}
                </div>
                <hr>
            </div>

        </div>
    </div>

@stop