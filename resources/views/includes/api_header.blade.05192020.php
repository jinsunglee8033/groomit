<!-- Static navbar -->
<nav class="navbar navbar-inverse navbar-top">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="/user/api/simulator">Groomit API</a>
    </div>
    <div id="navbar" class="navbar-collapse collapse">
        <ul class="nav navbar-nav">
            <li class="nav-item {{ $api == 'signup' ? 'active': '' }}">
                <a class="nav-link" href="/user/api/simulator?api=signup">SIGNUP</a>
            </li>
            <li class="nav-item dropdown {{ (in_array($api, ['login', 'login_3rd_party']) ?
            'active':
            '') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">LOGIN</a>
                <ul class="dropdown-menu">
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=login">Login</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=logout">Logout</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=login_3rd_party">Login 3rd Party</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown {{ (in_array($api, ['verify_email', 'verify_key', 'address_remove']) ?
            'active':
            '') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Forgot Password</a>
                <ul class="dropdown-menu">
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=verify_email">Verify Email</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=update_password">Update Password</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown {{ (in_array($api, ['profile_update', 'get_available_credit', 'update_device_token']) ?
            'active':
            '') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Profile</a>
                <ul class="dropdown-menu">
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=profile_update">Update</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=get_available_credit">Available Credit</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=update_device_token">Update Device Token</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=profile_delete">Delete</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown {{ (in_array($api, ['appointment', 'appointment_product',
            'appointment_times','appointment_places', 'appointment_confirm', 'appointment_post', 'appointment_update',
            'appointment_rating', 'appointment_tip', 'appointment_cancel', 'appointment_history',
            'appointment_upcoming', 'appointment_last', 'appointment_count' ]) ? 'active': '')
            }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Appointment</a>
                <ul class="dropdown-menu">
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=appointment">For Dashboard</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=appointment_product">Product List</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=appointment_times">Times</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=appointment_places">Places</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=appointment_confirm">Confirm</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=appointment_post">Post</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=appointment_update">Update</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=appointment_rating">Rating</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=appointment_tip">Tip</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=appointment_cancel">Cancel</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=appointment_history">History</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=appointment_upcoming">Upcoming</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=appointment_last">Last</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=appointment_get_by_id">Get by ID</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=appointment_count">Appointment Count</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown {{ (in_array($api, ['pet_available_type', 'pet', 'pet_dogs', 'pet_cats',
            'pet_breed_list',
            'pet_size_list', 'pet_save', 'pet_remove']) ? 'active': '') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Pet</a>
                <ul class="dropdown-menu">
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=pet_available_type">Available Type</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=pet">Pets</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=pet_dogs">Dogs</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=pet_cats">Cats</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=pet_breed_list">Breed List</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=get_promo_images">Get Promo Images</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=pet_size_list">Size List</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=pet_temperment_list">Temperment List</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=pet_save">Save/Update</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=pet_remove">Remove</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown {{ (in_array($api, ['address', 'address_save', 'address_remove', 'address_check_zip']) ? 'active':
            '') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Address</a>
                <ul class="dropdown-menu">
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=address">Address List</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=address_save">Address Save</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=address_remove">Address Remove</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=address_check_zip">Check Zip</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown {{ (in_array($api, ['billing', 'billing_save', 'billing_remove']) ? 'active':
            '') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Billing</a>
                <ul class="dropdown-menu">
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=billing">Billing List</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=billing_save">Save</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=billing_remove">Remove</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=billing_set_default">Set Default</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown {{ (in_array($api, ['messages', 'message_detail', 'message_send']) ?
            'active':
            '') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Message</a>
                <ul class="dropdown-menu">
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=messages">Message List</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=message_detail">Message Detail</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=message_send">Message Send</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown {{ (in_array($api, ['groomer', 'groomer_detail', 'groomer_make_favorite',
            'message_send']) ?
            'active':
            '') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Groomer</a>
                <ul class="dropdown-menu">
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=groomer_list">Groomer List</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=groomer_detail">Detail Information</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=groomer_make_favorite">Make Favorite</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=groomer_make_blocked">Make Blocked</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=favorite_groomer_list">Favorite Groomer List</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=blocked_groomer_list">Blocked Groomer List</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=groomer_calendar">groomer_calendar</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown {{ (in_array($api, ['explode_get_sizes', 'explode_get_package_addon']) ?
            'active':
            '') }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Explode</a>
                <ul class="dropdown-menu">
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=explode_get_sizes">Explode Sizes</a>
                    </li>
                    <li class="nav-item" tabindex="-1">
                        <a class="nav-link" href="/user/api/simulator?api=explode_get_package_addon">Explode
                            Packages</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item {{ $api == 'contact_us' ? 'active': '' }}">
                <a class="nav-link" href="/user/api/simulator?api=contact_us">Contact Us</a>
            </li>
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