<!-- Static navbar -->
<nav class="navbar navbar-inverse navbar-top">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand " href="/admin">Groomit Admin</a>

    </div>
    @php
        //$admin = Auth::guard('admin')->user();
        $group = session('group');
    @endphp
    <div id="navbar" class="navbar-collapse collapse">
        <ul class="nav navbar-nav">

        {{--  Group CS1   --}}
            @if($group == 'CS1')
                <li class="nav-item {{ (strpos(request()->segment(2), 'appointment') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/appointments">Appointments</a>
                </li>

                <li class="nav-item {{ (strpos(request()->segment(2), 'user') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Users</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/users">Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/availableDaysByCounty">Available Days by County</a></li>
                    </ul>
                </li>

                <li class="nav-item {{ (strpos(request()->segment(2), 'groomer') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/groomers">Groomers</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'application') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Groomer Applications</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/applications">Groomer Applications</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/pre_apply">Groomer PreApply</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'pet') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/pets">Pets</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'admin') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/admins">Admin Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/privilege">Admin Privilege</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'contact') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/contacts">Contact Us</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'message') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/messages">Messages</a>
                </li>

                <li class="nav-item dropdown {{ ((strpos(request()->segment(2), 'promo_codes') === 0 || strpos(request()->segment(2), 'redeemed_groupon') === 0) ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Promo Code</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/promo_codes">Promo Codes</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/redeemed_groupon">Redeemed Groupon</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown {{ (strpos(request()->segment(2), 'profit-sharing') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Profit Sharing</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/profit-sharing/report-new">Report</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/profit-sharing/report">Report.old</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/profit-sharing">Setup</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown {{ (strpos(request()->segment(2), 'affiliate') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Affiliates</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/affiliates">Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/affiliate_withdraw_requests">Withdraw Request</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown {{ (strpos(request()->segment(2), 'reports') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Reports</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/special-promotion?spcode=FREESILVER">Free Silver
                                Promotion</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/special-promotion?spcode=HALLOWEEN">HALLOWEEN Promotion</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/appointment-cycle">Appointment Cycle</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-evaluation">Groomer Evaluation</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-rating">Groomer Rating</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-cycle">Groomer Cycle</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-fav">Fav Groomer</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/breed_size">Breed/Size Report</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/check-in-out-trend">Check-In/Out Trend</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/vouchers">Voucher Sales</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/ziplookup">Zip Lookup History</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/promocode-performance">Performance by Promocodes</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/user-credit">User Credit</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/breed-booked">Breed Booked</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/customer-retention">Customer Retention</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/notification">Notifications</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'order') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/order">Order</a>
                </li>
            @endif

        {{--  Group CS2   --}}
            @if($group == 'CS2')
                <li class="nav-item {{ (strpos(request()->segment(2), 'appointment') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/appointments">Appointments</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'user') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Users</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/users">Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/availableDaysByCounty">Available Days by County</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'groomer') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/groomers">Groomers</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'application') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Groomer Applications</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/applications">Groomer Applications</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/pre_apply">Groomer PreApply</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'pet') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/pets">Pets</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'admin') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/admins">Admin Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/privilege">Admin Privilege</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'contact') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/contacts">Contact Us</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'message') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/messages">Messages</a>
                </li>

                <li class="nav-item dropdown {{ ((strpos(request()->segment(2), 'promo_codes') === 0 || strpos(request()->segment(2), 'redeemed_groupon') === 0) ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Promo Code</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/promo_codes">Promo Codes</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/redeemed_groupon">Redeemed Groupon</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown {{ (strpos(request()->segment(2), 'profit-sharing') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Profit Sharing</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/profit-sharing/report-new">Report</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/profit-sharing/report">Report.old</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/profit-sharing">Setup</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown {{ (strpos(request()->segment(2), 'affiliate') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Affiliates</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/affiliates">Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/affiliate_withdraw_requests">Withdraw Request</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown {{ (strpos(request()->segment(2), 'reports') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Reports</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/special-promotion?spcode=FREESILVER">Free Silver
                                Promotion</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/special-promotion?spcode=HALLOWEEN">HALLOWEEN Promotion</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/appointment-cycle">Appointment Cycle</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-evaluation">Groomer Evaluation</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-rating">Groomer Rating</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-cycle">Groomer Cycle</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-fav">Favorite Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/breed_size">Breed/Size Report</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/check-in-out-trend">Check-In/Out Trend</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/vouchers">Voucher Sales</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/ziplookup">Zip Lookup History</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/promocode-performance">Performance by Promocodes</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/user-credit">User Credit</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/breed-booked">Breed Booked</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/customer-retention">Customer Retention</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/notification">Notifications</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'order') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/order">Order</a>
                </li>
            @endif

        {{-- Group SHIP1 --}}
            @if($group == 'SHIP1')
                <li class="nav-item {{ (strpos(request()->segment(2), 'groomer') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/groomers">Groomers</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'application') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Groomer Applications</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/applications">Groomer Applications</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/pre_apply">Groomer PreApply</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown {{ (strpos(request()->segment(2), 'reports') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Reports</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/breed_size">Breed/Size Report</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/breed-booked">Breed Booked</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'order') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/order">Order</a>
                </li>
            @endif

        {{-- Group SHIP2 --}}
            @if($group == 'SHIP2')
                <li class="nav-item {{ (strpos(request()->segment(2), 'groomer') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/groomers">Groomers</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'application') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Groomer Applications</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/applications">Groomer Applications</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/pre_apply">Groomer PreApply</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown {{ (strpos(request()->segment(2), 'reports') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Reports</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/breed_size">Breed/Size Report</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/breed-booked">Breed Booked</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'order') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/order">Order</a>
                </li>
            @endif

        {{-- Group ACCT1 --}}
            @if($group == 'ACCT1')
                <li class="nav-item {{ (strpos(request()->segment(2), 'appointment') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/appointments">Appointments</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'user') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Users</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/users">Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/availableDaysByCounty">Available Days by County</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'groomer') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/groomers">Groomers</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'application') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Groomer Applications</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/applications">Groomer Applications</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/pre_apply">Groomer PreApply</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'pet') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/pets">Pets</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'admin') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/admins">Admin Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/privilege">Admin Privilege</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'contact') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/contacts">Contact Us</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'message') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/messages">Messages</a>
                </li>
                <li class="nav-item dropdown {{ ((strpos(request()->segment(2), 'promo_codes') === 0 || strpos(request()->segment(2), 'redeemed_groupon') === 0) ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Promo Code</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/promo_codes">Promo Codes</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/redeemed_groupon">Redeemed Groupon</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown {{ (strpos(request()->segment(2), 'profit-sharing') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Profit Sharing</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/profit-sharing/report-new">Report</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/profit-sharing/report">Report.old</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/profit-sharing">Setup</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown {{ (strpos(request()->segment(2), 'affiliate') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Affiliates</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/affiliates">Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/affiliate_withdraw_requests">Withdraw Request</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown {{ (strpos(request()->segment(2), 'reports') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Reports</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/special-promotion?spcode=FREESILVER">Free Silver
                                Promotion</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/special-promotion?spcode=HALLOWEEN">HALLOWEEN Promotion</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/appointment-cycle">Appointment Cycle</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-evaluation">Groomer Evaluation</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-rating">Groomer Rating</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-cycle">Groomer Cycle</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-fav">Favorite Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/breed_size">Breed/Size Report</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/check-in-out-trend">Check-In/Out Trend</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/vouchers">Voucher Sales</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/ziplookup">Zip Lookup History</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/promocode-performance">Performance by Promocodes</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/user-credit">User Credit</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/breed-booked">Breed Booked</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/customer-retention">Customer Retention</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/notification">Notifications</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/add-on-sales">Products Sales Quantity</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/survey">Survey</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/paymentsummary">CC Payment</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/chargeback">Charge Back</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomers-by-countysummary">Groomers by County</a></li>
{{--                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/map-from-batchgeo">Map From BatchGEO</a></li>--}}
{{--                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/cancellationsummary">Cancellations</a></li>--}}
{{--                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/login-history">Login History</a></li>--}}
{{--                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-login-history">Groomer Login History</a></li>--}}
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'order') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/order">Order</a>
                </li>
            @endif
            @if($group == 'ACCT2')

                <li class="nav-item {{ (strpos(request()->segment(2), 'groomer') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/groomers">Groomers</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'application') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Groomer Applications</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/applications">Groomer Applications</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/pre_apply">Groomer PreApply</a></li>
                    </ul>
                </li>
            @endif
        {{-- Group PT1 --}}
            @if($group == 'PT1')
                <li class="nav-item {{ (strpos(request()->segment(2), 'appointment') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/appointments">Appointments</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'user') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/users">Users</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'groomer') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/groomers">Groomers</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'application') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Groomer Applications</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/applications">Groomer Applications</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/pre_apply">Groomer PreApply</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'pet') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/pets">Pets</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'admin') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/admins">Admin Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/privilege">Admin Privilege</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'contact') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/contacts">Contact Us</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'message') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/messages">Messages</a>
                </li>
                <li class="nav-item dropdown {{ ((strpos(request()->segment(2), 'promo_codes') === 0 || strpos(request()->segment(2), 'redeemed_groupon') === 0) ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Promo Code</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/promo_codes">Promo Codes</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/redeemed_groupon">Redeemed Groupon</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown {{ (strpos(request()->segment(2), 'profit-sharing') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Profit Sharing</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/profit-sharing/report-new">Report</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/profit-sharing/report">Report.old</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/profit-sharing">Setup</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown {{ (strpos(request()->segment(2), 'affiliate') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Affiliates</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/affiliates">Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/affiliate_withdraw_requests">Withdraw Request</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown {{ (strpos(request()->segment(2), 'reports') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Reports</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/special-promotion?spcode=FREESILVER">Free Silver
                                Promotion</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/special-promotion?spcode=HALLOWEEN">HALLOWEEN Promotion</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/appointment-cycle">Appointment Cycle</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-evaluation">Groomer Evaluation</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-rating">Groomer Rating</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-cycle">Groomer Cycle</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-fav">Favorite Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/breed_size">Breed/Size Report</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/check-in-out-trend">Check-In/Out Trend</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/vouchers">Voucher Sales</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/ziplookup">Zip Lookup History</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/promocode-performance">Performance by Promocodes</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/user-credit">User Credit</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/breed-booked">Breed Booked</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/customer-retention">Customer Retention</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/notification">Notifications</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'order') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/order">Order</a>
                </li>
            @endif
            @if($group == 'PT2')
                <li class="nav-item {{ (strpos(request()->segment(2), 'appointment') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/appointments">Appointments</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'user') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/users">Users</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'groomer') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/groomers">Groomers</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'application') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Groomer Applications</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/applications">Groomer Applications</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/pre_apply">Groomer PreApply</a></li>
                    </ul>
                </li>
            @endif
        {{-- Group MG1 --}}
            @if( in_array($group , ['MG1', 'MG2']))
                <li class="nav-item {{ (strpos(request()->segment(2), 'appointment') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/appointments">Appointments</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'user') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Users</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/users">Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/availableDaysByCounty">Available Days by County</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'groomer') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/groomers">Groomers</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'application') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Groomer Applications</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/applications">Groomer Applications</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/pre_apply">Groomer PreApply</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'pet') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/pets">Pets</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'admin') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/admins">Admin Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/privilege">Admin Privilege</a></li>
                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'contact') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/contacts">Contact Us</a>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'message') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/messages">Messages</a>
                </li>
                <li class="nav-item dropdown {{ ((strpos(request()->segment(2), 'promo_codes') === 0 || strpos(request()->segment(2), 'redeemed_groupon') === 0) ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Promo Code</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/promo_codes">Promo Codes</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/redeemed_groupon">Redeemed Groupon</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/promo_redeemed_history">Promo Redeemed History</a></li>
                    </ul>
                </li>
                 @if($group == 'MG1')
                <li class="nav-item dropdown {{ (strpos(request()->segment(2), 'profit-sharing') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Profit Sharing</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/profit-sharing/report-new">Report</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/profit-sharing/report">Report.old</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/profit-sharing">Setup</a></li>
                    </ul>
                </li>
                @endif
                <li class="nav-item dropdown {{ (strpos(request()->segment(2), 'affiliate') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Affiliates</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/affiliates">Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/affiliate_withdraw_requests">Withdraw Request</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown {{ (strpos(request()->segment(2), 'reports') === 0 ? 'active': '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Reports</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/special-promotion?spcode=FREESILVER">Free Silver
                                Promotion</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/special-promotion?spcode=HALLOWEEN">HALLOWEEN Promotion</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/appointment-cycle">Appointment Cycle</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-evaluation">Groomer Evaluation</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-rating">Groomer Rating</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-cycle">Groomer Cycle</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-fav">Favorite Users</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/breed_size">Breed/Size Report</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/check-in-out-trend">Check-In/Out Trend</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/vouchers">Voucher Sales</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/ziplookup">Zip Lookup History</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/promocode-performance">Performance by Promocodes</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link"
                                                              href="/admin/reports/user-credit">User Credit</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/breed-booked">Breed Booked</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/customer-retention">Customer Retention</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/notification">Notifications</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/add-on-sales">Products Sales Quantity</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/survey">Survey</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/paymentsummary">CC Payment</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/chargeback">Charge Back</a></li>
                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomers-by-countysummary">Groomers by County</a></li>
{{--                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/map-from-batchgeo">Map From BatchGEO</a></li>--}}
{{--                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/cancellationsummary">Cancellations</a></li>--}}
{{--                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/login-history">Login History</a></li>--}}
{{--                        <li class="nav-item" tabindex="-1"><a class="nav-link" href="/admin/reports/groomer-login-history">Groomer Login History</a></li>--}}

                    </ul>
                </li>
                <li class="nav-item {{ (strpos(request()->segment(2), 'order') === 0 ? 'active': '') }}">
                    <a class="nav-link" href="/admin/order">Order</a>
                </li>
            @endif

            {{--<li class="nav-item {{ (Request::is("admin/profit-sharing") ? 'active': '') }}">--}}
                {{--<a class="nav-link" href="/admin/profit-sharing">Profit Sharing Setup</a>--}}
            {{--</li>--}}
            {{--<li class="nav-item {{ (Request::is("admin/profit-sharing/report") ? 'active': '') }}">--}}
                {{--<a class="nav-link" href="/admin/profit-sharing/report">Profit Sharing Report</a>--}}
            {{--</li>--}}
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li class="nav-item">
                <a class="nav-link" href="/admin/logout">Logout</a>
            </li>
        </ul>
    </div><!--/.nav-collapse -->
</nav>

<!-- /navbar -->