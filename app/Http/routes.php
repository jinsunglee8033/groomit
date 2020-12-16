<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

//use Twilio;

### QUERY LOGGING BEGIN ###
use App\Lib\Helper;
use App\Model\ProductGroomerOrder;
//use App\Lib\AppointmentProcessor; //for testing /api/test.
//use App\Lib\Converge; for testing /api/test
use Carbon\Carbon;

\DB::listen(
  function ($query) {
      //  $sql - select * from `ncv_users` where `ncv_users`.`id` = ? limit 1
      //  $bindings - [5]
      //  $time(in milliseconds) - 0.38
      Log::info("### SQL QUERY ###", [
        'PATH' => Request::path(),
        'QUERY' => str_replace("\n", "", $query->sql),
        'BINDINGS' => $query->bindings,
        'TIME' => $query->time . " ms.",
        'UNIQUE_ID' => isset($_SERVER['UNIQUE_ID']) ? $_SERVER['UNIQUE_ID'] : ''
      ]);
  }
);
### QUERY LOGGING END ###

Route::get('/blog', function() {

    $url = "https://blog.groomit.me";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
    //curl_setopt($ch, CURLOPT_URL, urlencode($url));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
    $response = curl_exec($ch);
    curl_close($ch);


    $response = str_replace("https://blog.groomit.me/>", "https://www.groomit.me/blog>", $response);
    $response = str_replace("<script>if(document.location.protocol!=\"https:\"){document.location=document.URL.replace(/^http:/i,\"https:\");}", "", $response);
    $response = str_replace("https://blog.groomit.me/category", "https://www.groomit.me/blog/category", $response);
    $response = str_replace("https://blog.groomit.me/author", "https://www.groomit.me/blog/author", $response);
    $response = str_replace("https://blog.groomit.me/page", "https://www.groomit.me/blog/page", $response);
    $response = str_replace("https://blog.groomit.me/20", "https://www.groomit.me/blog/20", $response);

    echo $response;
});
Route::get('/blog/{wildcard}', function($wildcard) {


//    if(stripos($wildcard, '2019/') !== false ) {
//        $wildcard = substr($wildcard, stripos($wildcard, '2019/'));
//    }else if(stripos($wildcard, '2020/') !== false ) {
//        $wildcard = substr($wildcard, stripos($wildcard, '2020/'));
//    }else if(stripos($wildcard, '2021/') !== false  ) {
//        $wildcard = substr($wildcard, stripos($wildcard, '2021/'));
//    }else if(stripos($wildcard, 'category/') !== false  ) {
//        $wildcard = substr($wildcard, stripos($wildcard, 'category/'));
//    }else if(stripos($wildcard, 'author/')!== false  ) {
//        $wildcard = substr($wildcard, stripos($wildcard, 'author/'));
//    }else if(stripos($wildcard, 'page/')!== false  ) {
//        $wildcard = substr($wildcard, stripos($wildcard, 'page/'));
//    }else {
//        echo 'failed to parse url'. '<br/>';;
//
//    }

    $url = "https://blog.groomit.me/" . $wildcard ;


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
    $response = curl_exec($ch);
//    $info = curl_getinfo($ch);
//    $error = curl_error($ch);
    curl_close($ch);

    $response = str_replace("https://blog.groomit.me/>", "https://www.groomit.me/blog>", $response);
    $response = str_replace("<script>if(document.location.protocol!=\"https:\"){document.location=document.URL.replace(/^http:/i,\"https:\");}", "", $response);
    $response = str_replace("https://blog.groomit.me/category", "https://www.groomit.me/blog/category", $response);
    $response = str_replace("https://blog.groomit.me/author", "https://www.groomit.me/blog/author", $response);
    $response = str_replace("https://blog.groomit.me/page", "https://www.groomit.me/blog/page", $response);
    $response = str_replace("https://blog.groomit.me/20", "https://www.groomit.me/blog/20", $response);

    echo $response;

})->where('wildcard', '.*');
//->where('wildcard', '[A-Za-z]+');
//->where('wildcard', '[0-9]+');
//->where(['id' => '[0-9]+', 'name' => '[a-z]+']);

Route::get('/', function () {
//    $code = \App\Lib\Helper::generate_code(6);
//    Session::put('verification-code', $code);
//    return view('home')->with([
//        'verification_code' => $code
//    ]);
    return view('home');
});


Route::get('/book', function() {
    //return redirect('/user');  //By default, it's 302.
    return Redirect::to('/user', 301);
});

Route::post('/contact_us', 'ContactController@web_add');

Route::post('/application', 'Groomer\ApplicationController@post');
Route::get('/application', function() {
    return view('application');
});

Route::post('/application_temp', 'Groomer\ApplicationController@application_temp');
Route::get('/application_temp', function() {
    return view('application');
});

Route::get('/application2', function() {
    return view('application2');
});
Route::get('/application20', function() {
    return view('application20');
});
Route::get('/pre-apply', function() {
    return view('pre-apply');
});
Route::get('/DODO', function() {
    return view('DODO');
});
Route::get('/press', function() {
    return view('press');
});



Route::post('/pre-apply', 'Groomer\PreApplyController@post');

Route::post('/DODO', 'Groomer\DodoController@post');

Route::get('/faqs', function() {
    return view('faqs');
});

Route::get('/join-us', function() {
    return view('join-us');
});

Route::get('/login', function() {
    return view('login');
});

Route::get('/about-us', function() {
    return view('about-us');
});

Route::get('/privacy', function() {
    return view('privacy');
});

Route::get('/terms', function() {
    return view('terms');
});

Route::get('/terms-privacy', function() {
    return view('terms-privacy');
});


Route::get('/promotions', function() {
    return view('promotions');
});

Route::get('/investors', function() {
    return view('investors/investors');
});

Route::get('/investors/info', function() {
    return view('investors/info');
});

Route::get('/investors/thankyou', function() {
    return view('investors/thankyou');
});

Route::get('/pricing', function() {
    return view('pricing');
});
Route::get('/pricing2', function() { //for testing Geolocation of html5.
    return view('pricing2');
});
Route::get('/miami', function() {
    return view('miami');
});
Route::get('/nyc', function() {
    return view('nyc');
});
Route::get('/customer-cancellation-policy', function() {
    return view('customer-cancellation-policy');
});

Route::get('/groomer-policy', function() {
    return view('groomer-policy');
});

Route::get('/contact', function() {
    $code = \App\Lib\Helper::generate_code(6);
    Session::put('verification-code', $code);
    return view('contact')->with([
        'verification_code' => $code
    ]);
    //return view('contact');
});
//Route::post('/signup', 'GroomerController@store');

/*
Route::get('/welcome', function() {
    return view('welcome');
});
*/

Route::get('/pet/photo/{id}', function($id) {
    try {
        $pet_photo = App\Model\PetPhoto::find($id);
        $response = Response::make($pet_photo->photo, 200);
        $response->header('Content-Type', 'image/jpeg');
        return $response;
    } catch (\Exception $ex) {
        return response()->json([
          'msg' => $ex->getMessage()
        ]);
    }


});

Route::get('/tracking/{no}', function($no) {
   try{

       $order_id = $no;
       $last_order = ProductGroomerOrder::find($order_id);
       $tracking_no = $last_order->tracking_no;
       $delivery_company = $last_order->delivery_company;

       if($delivery_company == 'Fedex'){
           return redirect()->to('https://www.fedex.com/apps/fedextrack/?action=track&action=track&tracknumbers='.$tracking_no);
       }elseif ($delivery_company == 'UPS'){
           return redirect()->to('https://www.ups.com/track?loc=en_US&tracknum='.$tracking_no.'&requester=WT/');
       }elseif ($delivery_company == 'USPS'){
           return redirect()->to('https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1='.$tracking_no);
       }

   } catch (\Exception $ex) {
       return response()->json([
           'msg' => $ex->getMessage()
       ]);
   }
});

Route::post('/api/login', ['uses' => 'Login\SignUpController@login']);
Route::post('/api/login/3rd-party', ['uses' => 'Login\SignUpController@login_3rd_party']);

Route::post('/api/signup_user', ['uses' => 'Login\SignUpController@signup_user']); //Seems not to be used any longer
Route::post('/api/add_pet', ['uses' => 'PetController@add_pet']);
Route::post('/api/update_pet', ['uses' => 'PetController@update_pet']);
Route::post('/api/remove_pet', ['uses' => 'PetController@remove_pet']);
Route::post('/api/get_user_pets', ['uses' => 'PetController@get_user_pets']);
Route::post('/api/get_pet_photos', ['uses' => 'PetController@get_pet_photos']);
Route::post('/api/get_breeds', ['uses' => 'PetController@get_breeds']);
Route::post('/api/get_sizes', ['uses' => 'PetController@get_sizes']);
Route::post('/api/get_user_profile', ['uses' => 'Login\SignUpController@get_user_profile']);
Route::get('/pet-photo/{photo_id}',  ['uses' => 'PetController@show_pet_image']);

Route::post('/api/add_billing',  ['uses' => 'UserController@add_billing']);
Route::post('/api/update_billing',  ['uses' => 'UserController@update_billing']);
Route::post('/api/remove_billing',  ['uses' => 'UserController@remove_billing']);
Route::post('/api/get_user_billing', ['uses' => 'UserController@get_user_billing']);

Route::post('/api/dashboard/get-info', ['uses' => 'DashboardController@get_info']);

Route::post('/api/appointment/get', ['uses' => 'AppointmentController@get_by_id']);
Route::post('/api/appointment/list/pet', ['uses' => 'AppointmentController@get_list_by_pet']);
Route::post('/api/appointment/add', ['uses' => 'AppointmentController@add']);       //Not used any longer.
Route::post('/api/appointment/cancel', ['uses' => 'AppointmentController@cancel']); //Not used any longer.
Route::post('/api/appointment/package/list', ['uses' => 'AppointmentController@get_packages']);
Route::post('/api/appointment/recent', ['uses' => 'AppointmentController@recent']);
Route::post('/api/appointment/upcoming', ['uses' => 'AppointmentController@upcoming']);
Route::post('/api/appointment/rate', ['uses' => 'AppointmentController@rate']);
Route::post('/api/appointment/mark-as-favorite', ['uses' => 'AppointmentController@mark_as_favorite']);
Route::post('/api/appointment/tip', ['uses' => 'AppointmentController@tip']);
Route::post('/api/appointment/groomer-works', ['uses' => 'AppointmentController@groomer_works']);
Route::post('/api/appointment/cat/service', ['uses' => 'AppointmentController@get_cat_service']);

// Apply v2. appointment
Route::post('/api/appointment/sizes', ['uses' => 'AppointmentController@get_sizes']);
Route::post('/api/appointment/package_addon/list', ['uses' => 'AppointmentController@get_package_addon']);

// Apply to new Add on Page
Route::post('/api/appointment/addons/list', ['uses' => 'AppointmentController@get_addons']);
//======================//

Route::post('/api/appointment/tax', ['uses' => 'AppointmentController@get_tax']);
Route::post('/api/appointment/tax-via-zip', ['uses' => 'AppointmentController@get_tax_via_zip']);
Route::post('/api/appointment/edit', ['uses' => 'AppointmentController@edit']);
Route::post('/api/appointment/check-promo-code', 'AppointmentController@checkPromoCode');

// zip code ask & save address
Route::post('/api/appointment/check-address', 'AppointmentController@check_address');
Route::post('/api/appointment/check-if-cats-allowed', 'AppointmentController@check_if_cats_allowed');
Route::post('/api/appointment/save-address-only-zip', 'AppointmentController@save_address_zip_only');

Route::post('/api/contact/add', ['uses' => 'ContactController@add']);

Route::post('/api/user/get_my_pet_photos', ['uses' => 'UserController@get_my_pet_photos']);
Route::post('/api/user/get_popular_photos', ['uses' => 'UserController@get_popular_photos']);
Route::post('/api/user/get_recent_groomed_photos', ['uses' => 'UserController@get_recent_groomed_photos']);

Route::post('/api/user/profile/address/get', ['uses' => 'AddressController@get_by_id']);
Route::post('/api/user/profile/address/list', ['uses' => 'AddressController@get_user_address']);
Route::post('/api/user/profile/address/update', ['uses' => 'AddressController@update_address']);
Route::post('/api/user/profile/address/add', ['uses' => 'AddressController@add_address']);
Route::post('/api/user/profile/address/remove', ['uses' => 'AddressController@remove_address']);
Route::post('/api/user/profile/address/check-zip', ['uses' => 'AddressController@check_zip']);

Route::post('/api/user/profile/basic/update', ['uses' => 'UserController@update_profile_basic']);
Route::post('/api/user/profile/reset_password', ['uses' => 'UserController@reset_password']);
Route::get('/api/user/profile/photo/{id}', ['uses' => 'UserController@showProfilePhoto']);

Route::post('/api/user/profile/pet/get', ['uses' => 'PetController@get_by_id']);
Route::post('/api/user/profile/pet/list', ['uses' => 'PetController@get_user_pets']);
Route::post('/api/user/profile/pet/update', ['uses' => 'PetController@update_pet']);
Route::post('/api/user/profile/pet/add', ['uses' => 'PetController@add_pet']);
Route::post('/api/user/profile/pet/remove', ['uses' => 'PetController@remove_pet']);

Route::post('/api/user/dog/list', ['uses' => 'PetController@get_user_dogs']);
Route::post('/api/user/cat/list', ['uses' => 'PetController@get_user_cats']);

Route::post('/api/user/profile/card/list', ['uses' => 'UserController@get_cards']);
Route::post('/api/user/profile/card/add', ['uses' => 'UserController@add_billing']);
Route::post('/api/user/profile/card/update', ['uses' => 'UserController@update_billing']);
Route::post('/api/user/profile/card/remove', ['uses' => 'UserController@remove_billing']);

Route::post('/api/user/profile/favorites/list',  ['uses' => 'UserController@get_favorite_groomers']); //Not to be used.
Route::post('/api/user/profile/favorites/remove',  ['uses' => 'UserController@remove_favorite_groomer']);

Route::post('/api/groomer/get',  ['uses' => 'GroomerController@get_by_id']);
Route::post('/api/groomer/make_favorite',  ['uses' => 'GroomerController@make_favorite']);

Route::post('/api/user/forgot-password/verify-email', ['uses' => 'User\ForgotPasswordController@verify_email']);
Route::post('/api/user/forgot-password/verify-key', ['uses' => 'User\ForgotPasswordController@verify_key']);
Route::post('/api/user/forgot-password/update-password', ['uses' => 'User\ForgotPasswordController@update_password']);

Route::post('/api/user/messages/list',  ['uses' => 'User\MessageController@messages']);

Route::post('/api/user/update-device-token', ['uses' => 'UserController@update_device_token']);

Route::post('/api/user/get-available-credit', ['uses' => 'UserController@get_available_credit']);

/* push notifications : To user */
Route::post('/api/message/list',  ['uses' => 'MessageController@messages']);
Route::post('/api/message/detail',  ['uses' => 'MessageController@detail']);
Route::post('/api/message/send',  ['uses' => 'MessageController@send']);

Route::post('/api/token-ex/get-auth-key', 'TokenExController@getAuthKey');

Route::get('/groomer', ['as' => 'groomer', 'uses' => 'Groomer\Web\GroomerController@index']);
Route::get('/groomer/login', ['as' => 'groomer.login', 'uses' => 'Groomer\Web\AuthController@showLoginForm']);
Route::post('/groomer/login', ['uses' => 'Groomer\Web\AuthController@login']);

//After groomer logins
Route::group(['prefix' => '/groomer', 'middleware' => ['groomer']], function() {
    Route::get('/', ['as' => 'groomer', 'uses' => 'Groomer\Web\GroomerController@index']);
    Route::get('/logout', ['uses' => 'Groomer\Web\AuthController@logout']);

    Route::get('/esign/{type}', ['uses' => 'Groomer\Web\GroomerController@esign']);
    Route::post('/document/upload', ['as' => 'groomer.document.upload', 'uses' => 'Groomer\Web\GroomerController@document_upload']);

    Route::get('/document/view/{groomer_id}/{id}', function($groomer_id, $id) {
        try {
            $file = \App\Model\GroomerDocument::where('id', $id)->where('groomer_id', $groomer_id)->first();
            $response = Response::make(base64_decode($file->data), 200);
            $response->header('Content-Type', 'application/octet-stream');
            $response->header('Content-Disposition', 'attachment; filename="' . $file->file_name . '"');

            return $response;
        } catch (\Exception $ex) {
            return response()->json([
              'msg' => $ex->getMessage()
            ]);
        }
    });
});

Route::get('/eversign/complete/{doc_id}', 'Groomer\Web\GroomerController@complete');
Route::get('/eversign/declined/{doc_id}', 'Groomer\Web\GroomerController@declined');

//Groomer APP APIs
Route::group(['prefix' => '/api/v2/groomer'], function() {
    Route::post('/login', 'Groomer\SignUpController@login');
    Route::post('/login_as', 'Groomer\SignUpController@login_as');
    Route::post('/logout', 'Groomer\SignUpController@logout');

    Route::post('/profile/get', 'Groomer\ProfileController@get');
    Route::post('/profile/arrived', 'Groomer\ProfileController@arrived');
    Route::post('/profile/update', 'Groomer\ProfileController@update');
    Route::post('/update_device_token', 'Groomer\ProfileController@update_device_token');
    Route::post('/add_affiliate_code', 'Groomer\ProfileController@add_affiliate_code');

    Route::post('/forgot-password/verify-email', ['uses' => 'Groomer\ForgotPasswordController@verify_email']);
    Route::post('/forgot-password/verify-key', ['uses' => 'Groomer\ForgotPasswordController@verify_key']);
    Route::post('/forgot-password/update-password', ['uses' => 'Groomer\ForgotPasswordController@update_password']);

    Route::post('/get-current-earning', ['uses' => 'Groomer\EarningController@getInfo']);
    Route::post('/get-earning-history', 'Groomer\EarningController@getHistory');
    Route::post('/get-earning-detail', 'Groomer\EarningController@get_detail');
    Route::post('/dashboard/get-info', 'Groomer\DashboardController@getInfo');

    Route::post('/check-in/has-work', 'Groomer\CheckInController@hasWork');
    Route::post('/check-in/get-info', 'Groomer\CheckInController@getInfo');
    Route::post('/check-in/update-image', 'Groomer\CheckInController@updateImage');

    Route::post('/service/get-addons', 'Groomer\ServiceController@getAddons');
    Route::post('/service/update', 'Groomer\ServiceController@update');
    Route::post('/service/complete', 'Groomer\ServiceController@complete');

    Route::post('/open-appointment/list', 'Groomer\OpenAppointmentController@getList');
    Route::post('/open-appointment/confirm', 'Groomer\OpenAppointmentController@confirm');
    Route::post('/open-appointment/detail', 'Groomer\OpenAppointmentController@getDetail');

    Route::post('/history/list', 'Groomer\HistoryController@getList');
    Route::post('/history/detail', 'Groomer\HistoryController@getDetail');
    Route::post('/upcoming/list', 'Groomer\UpcomingController@getList');
    Route::post('/upcoming/detail', 'Groomer\UpcomingController@getDetail');
    Route::post('/upcoming/on-the-way', 'Groomer\UpcomingController@onTheWay');

    Route::post('/message/list', 'Groomer\MessageController@getList');
    Route::post('/message/detail', 'Groomer\MessageController@getDetail');
    Route::post('/message/send', 'Groomer\MessageController@send');

    Route::post('/pet/size/list', 'Groomer\PetController@getSizeList');

    Route::post('/message/user/send', 'Groomer\MessageController@sendToUser');

    Route::post('/availability/get', 'Groomer\GroomerController@get_availability');
    Route::post('/availability/set', 'Groomer\GroomerController@set_availability');

    Route::post('/product/category/get', 'Groomer\GroomerController@get_categories');
    Route::post('/product/get', 'Groomer\GroomerController@get_products');
    Route::post('/product/cart/add', 'Groomer\GroomerController@cart_add_product');
    Route::post('/product/cart/delete', 'Groomer\GroomerController@cart_delete_product');
    Route::post('/product/cart/get', 'Groomer\GroomerController@cart_get');
    Route::post('/product/order/create', 'Groomer\GroomerController@order_create');
    Route::post('/product/order/list', 'Groomer\GroomerController@order_list');
    Route::post('/product/order/detail', 'Groomer\GroomerController@order_detail');


});

/*
Route::post('/api/groomer/login', ['uses' => 'Groomer\GroomerController@login']);
Route::post('/api/groomer/signup', ['uses' => 'Groomer\GroomerController@signup']);
Route::post('/api/groomer/browse-appointments', ['uses' => 'Groomer\BrowseAppointmentController@browse']);
Route::post('/api/groomer/browse-appointments/detail', ['uses' => 'Groomer\BrowseAppointmentController@detail']);
Route::post('/api/groomer/browse-appointments/reject', ['uses' => 'Groomer\BrowseAppointmentController@reject']);
Route::post('/api/groomer/browse-appointments/accept', ['uses' => 'Groomer\BrowseAppointmentController@accept']);
Route::post('/api/groomer/upcoming-appointments',  ['uses' => 'Groomer\UpcomingAppointmentController@browse']);
Route::post('/api/groomer/upcoming-appointments/detail',  ['uses' => 'Groomer\UpcomingAppointmentController@detail']);
Route::post('/api/groomer/upcoming-appointments/cancel',  ['uses' => 'Groomer\UpcomingAppointmentController@cancel']);
Route::post('/api/groomer/upcoming-appointments/leave-now',  ['uses' => 'Groomer\UpcomingAppointmentController@leave_now']);
Route::post('/api/groomer/upcoming-appointments/complete',  ['uses' => 'Groomer\UpcomingAppointmentController@complete']);
*/

/* package tour */
Route::post('/api/appointment/tour_sizes', ['uses' => 'AppointmentController@tour_get_sizes']);
Route::post('/api/appointment/tour_package_addon/list', ['uses' => 'AppointmentController@tour_get_package_addon']);

/** Admin auth **/
Route::get('/admin', ['as' => 'admin', 'uses' => 'Admin\AdminController@index']);
Route::get('/admin/login', ['as' => 'admin.login', 'uses' => 'AdminAuth\AuthController@showLoginForm']);
Route::post('/admin/login', ['uses' => 'AdminAuth\AuthController@login']);

//After Admin logins.
Route::group(['middleware' => ['admin']], function () {

    Route::any('/admin/privilege', 'Admin\AdminController@privilege');
    Route::get('/admin/privilege/setup', 'Admin\AdminController@privilege_setup');
    Route::get('/admin/privilege/delete/{id}', 'Admin\AdminController@privilege_delete');
    Route::post('/admin/privilege/action/add', 'Admin\AdminController@privilege_action_add');

    // only logged in admin user can add new admin user
    Route::get('/admin/registration', ['as' => 'admin.registration', 'uses' => 'AdminAuth\AuthController@showRegistrationForm']);
    Route::post('/admin/registration', ['uses' => 'AdminAuth\AuthController@registration']);

    Route::get('/admin/logout', ['uses' => 'AdminAuth\AuthController@logout']);
    Route::get('/admin/appointments', ['as' => 'admin.appointments', 'uses' => 'Admin\AppointmentController@show']);
    Route::post('/admin/appointments', ['as' => 'admin.appointments', 'uses' => 'Admin\AppointmentController@show']);
    Route::get('/admin/appointment/{id}', ['as' => 'admin.appointment', 'uses' => 'Admin\AppointmentController@appointment']);
    Route::get('/admin/appointment/{id}/invoice', ['as' => 'admin.appointment', 'uses' => 'Admin\AppointmentController@appointment_invoice']);
    Route::get('/admin/appointment/{id}/refund', ['as' => 'admin.appointment', 'uses' => 'Admin\AppointmentController@appointment_refund']);
    //Route::get('/admin/appointment/{id}/repayment', ['as' => 'admin.appointment', 'uses' => 'Admin\AppointmentController@appointment_repayment']);
    //Route::get('/admin/appointment/{id}/reholding', ['as' => 'admin.appointment', 'uses' => 'Admin\AppointmentController@appointment_reholding']);
    Route::get('/admin/appointment/{id}/chargerefund', ['as' => 'admin.appointment', 'uses' => 'Admin\AppointmentController@appointment_chargerefund']);
    Route::post('/admin/appointment/{id}/adjust', ['as' => 'admin.appointment', 'uses' => 'Admin\AppointmentController@adjust']);
    Route::post('/admin/appointment/toggle-delayed', 'Admin\AppointmentController@toggleDelayed');

    Route::post('/admin/appointment/get-available-groomers', 'Admin\AppointmentController@getAvailableGroomers');

    Route::post('/admin/appointment/assign_groomer', [
      'as' => 'admin.appointment.assign_groomer',
      'uses' => 'Admin\AppointmentController@assign_groomer'
    ]);
    Route::post('/admin/appointment/change_status', [
      'as' => 'admin.appointment.change_status',
      'uses' => 'Admin\AppointmentController@change_status'
    ]);

    Route::post('/admin/appointment/update-op-note', 'Admin\AppointmentController@updateOpNote');

    Route::post('/admin/appointment/groomer_on_the_way', [
      'as' => 'admin.appointment.groomer_on_the_way',
      'uses' => 'Admin\AppointmentController@groomer_on_the_way'
    ]);
    Route::post('/admin/appointment/reminder', [
      'as' => 'admin.appointment.reminder',
      'uses' => 'Admin\AppointmentController@reminder'
    ]);
    Route::post('/admin/appointment/new_notification', [
        'as' => 'admin.appointment.new_notification',
        'uses' => 'Admin\AppointmentController@new_notification'
    ]);
    Route::post('/admin/appointment/confirm_available_groomer', [
      'as' => 'admin.appointment.confirm_available_groomer',
      'uses' => 'Admin\AppointmentController@confirm_available_groomer'
    ]);
    Route::post('/admin/appointment/pay_bonus', [
        'as' => 'admin.appointment.pay_bonus',
        'uses' => 'Admin\AppointmentController@pay_bonus'
    ]);
    Route::post('/admin/appointment/update_requested_time', [
        'as' => 'admin.appointment.update_requested_time',
        'uses' => 'Admin\AppointmentController@update_requested_time'
    ]);
    Route::post('/admin/appointment/update_service', [
      'as' => 'admin.appointment.update_service',
      'uses' => 'Admin\AppointmentController@update_service'
    ]);
    Route::post('/admin/appointment/cancel_fav_groomer', [
        'as' => 'admin.appointment.cancel_fav_groomer',
        'uses' => 'Admin\AppointmentController@cancel_fav_groomer'
    ]);
    Route::post('/admin/appointment/update_fav_groomer', [
        'as' => 'admin.appointment.update_fav_groomer',
        'uses' => 'Admin\AppointmentController@update_fav_groomer'
    ]);
    Route::post('/admin/appointment/update_groomer_note', [
      'as' => 'admin.appointment.update_groomer_note',
      'uses' => 'Admin\AppointmentController@update_groomer_note'
    ]);
    Route::post('/admin/appointment/update_promo_code', [
      'as' => 'admin.appointment.update_promo_code',
      'uses' => 'Admin\AppointmentController@update_promo_code'
    ]);

    Route::post('/admin/appointment/send_service_completion_email', [
      'as' => 'admin.appointment.send_service_completion_email',
      'uses' => 'Admin\AppointmentController@sendServiceCompletionEmail'
    ]);

    Route::get('/admin/appointment_schedule', ['as' => 'admin.appointment_schedule','uses' => 'Admin\AppointmentController@getSchedule']);
    Route::post('/admin/appointment_schedule', ['as' => 'admin.appointment_schedule','uses' => 'Admin\AppointmentController@getSchedule']);
    Route::any('/admin/groomer_fulfillment', ['as' => 'admin.groomer_fulfillment','uses' => 'Admin\AppointmentController@groomer_fulfillment']);

    Route::any('/admin/fulfillment_schedule', ['as' => 'admin.fulfillment_schedule','uses' => 'Admin\AppointmentController@fulfillment_schedule']);

    Route::get('/admin/groomer_schedule', ['as' => 'admin.groomer_schedule','uses' => 'Admin\AppointmentController@getGroomerSchedule']);
    Route::post('/admin/groomer_schedule', ['as' => 'admin.groomer_schedule','uses' => 'Admin\AppointmentController@getGroomerSchedule']);

    Route::get('/admin/upcoming/{id}/{type}', ['as' => 'admin.upcoming', 'uses' => 'Admin\AppointmentController@upcoming']);
    Route::get('/admin/recent/{id}/{type}', ['as' => 'admin.recent', 'uses' => 'Admin\AppointmentController@recent']);
    Route::get('/admin/appointment_cancel/{id}/{type}/pop', ['as' => 'admin.recent', 'uses' => 'Admin\AppointmentController@cancel_pop']);
    Route::get('/admin/voucher/sales/{id}/pop', ['as' => 'admin.recent', 'uses' => 'Admin\Reports\VoucherController@sales_pop']);
    Route::get('/admin/history/{id}/{type}', ['as' => 'admin.history', 'uses' => 'Admin\AppointmentController@history']);

    Route::get('/admin/users', ['as' => 'admin.users', 'uses' => 'Admin\UserController@users']);
    Route::post('/admin/users', ['as' => 'admin.users', 'uses' => 'Admin\UserController@users']);
    Route::get('/admin/user/{id}', ['as' => 'admin.user', 'uses' => 'Admin\UserController@user']);
    Route::post('/admin/user/{id}/add_credit', 'Admin\UserController@add_credit');
    Route::post('/admin/user/login-as', ['as' => 'admin.user.login-as', 'uses' => 'Admin\UserController@loginAs']);
    Route::post('/admin/user/update', ['as' => 'admin.user_update', 'uses' => 'Admin\UserController@update']);
    Route::post('/admin/user/reset_password', ['as' => 'admin.user_reset_password', 'uses' => 'Admin\UserController@reset_password']);
    Route::post('/admin/user/update_address', ['as' => 'admin.user_address', 'uses' => 'Admin\UserController@update_address']);
    Route::post('/admin/user/update_billing', ['as' => 'admin.user_billing', 'uses' => 'Admin\UserController@update_billing']);
    Route::post('/admin/user/change_billing_status', ['as' => 'admin.change_billing_status', 'uses' => 'Admin\UserController@change_billing_status']);
    Route::post('/admin/user/update-op-note', 'Admin\UserController@updateOpNote');
    Route::post('/admin/user/update-yelp-review', 'Admin\UserController@update_yelp_review');
    Route::post('/admin/user/favorite-groomer/remove', 'Admin\UserController@removeFavoriteGroomer');
    Route::post('/admin/user/favorite-groomer/add', 'Admin\UserController@addFavoriteGroomer');
    Route::post('/admin/user/blocked-groomer/remove', 'Admin\UserController@removeBlockedGroomer');
    Route::post('/admin/user/blocked-groomer/add', 'Admin\UserController@addBlockedGroomer');
    Route::post('/admin/user/update-status', 'Admin\UserController@update_status');

    Route::get('/admin/availableDaysByCounty', ['as' => 'admin.available_days_by_county', 'uses' => 'Admin\AvailableDaysByCountyController@index']);
    Route::post('/admin/availableDaysByCounty', ['as' => 'admin.available_days_by_county', 'uses' => 'Admin\AvailableDaysByCountyController@index']);
    Route::get('/admin/availableDaysByCounty/day_update', 'Admin\AvailableDaysByCountyController@day_update');

    Route::get('/admin/groomers', ['as' => 'admin.groomers', 'uses' => 'Admin\GroomerController@groomers']);
    Route::post('/admin/groomers', ['as' => 'admin.groomers', 'uses' => 'Admin\GroomerController@groomers']);
    Route::get('/admin/groomer/{id}', ['as' => 'admin.groomer', 'uses' => 'Admin\GroomerController@groomer']);
    Route::get('/admin/groomer/{id}/add_promocode/{code}', 'Admin\GroomerController@add_promocode');
    Route::get('/admin/groomer/{id}/remove_promocode/{code}', 'Admin\GroomerController@remove_promocode');
    Route::get('/admin/groomer/{id}/history', ['as' => 'admin.groomer.history', 'uses' => 'Admin\GroomerController@history']);
    Route::post('/admin/groomer/update', [
      'as' => 'admin.groomer.update',
      'uses' => 'Admin\GroomerController@groomer_update'
    ]);
    Route::post('/admin/groomer/change_password', [
      'as' => 'admin.groomer.change_password',
      'uses' => 'Admin\GroomerController@groomer_change_password'
    ]);
    Route::post('/admin/groomer/delete', [ 
      'as' => 'admin.groomer.delete',
      'uses' => 'Admin\GroomerController@groomer_delete'
    ]);
    Route::post('/admin/groomer/groomer_schedule_update', [
      'as' => 'admin.groomer.groomer_schedule_update',
      'uses' => 'Admin\GroomerController@groomer_schedule_update'
    ]);
    Route::post('/admin/groomer/get_groomer_schedule', [
      'as' => 'admin.groomer.get_groomer_schedule',
      'uses' => 'Admin\GroomerController@get_groomer_schedule'
    ]);
    Route::post('/admin/groomer/phone_interview_notes', [
        'as' => 'admin.groomer.phone_interview_notes',
        'uses' => 'Admin\GroomerController@phone_interview_notes'
    ]);
    Route::post('/admin/groomer/trial_interview_notes', [
        'as' => 'admin.groomer.trial_interview_notes',
        'uses' => 'Admin\GroomerController@trial_interview_notes'
    ]);
    Route::post('/admin/groomer/update_cs_notes', [
        'as' => 'admin.groomer.update_cs_notes',
        'uses' => 'Admin\GroomerController@update_cs_notes'
    ]);
    Route::post('/admin/groomer/{groomer_id}/document/upload', ['as' => 'admin.groomer.document.upload', 'uses' => 'Admin\GroomerController@document_upload']);

    Route::get('/admin/groomer/{groomer_id}/document/{id}/view', function($groomer_id, $id) {
        try {
            $file = \App\Model\GroomerDocument::where('id', $id)->where('groomer_id', $groomer_id)->first();
            $response = Response::make(base64_decode($file->data), 200);
            $response->header('Content-Type', 'application/octet-stream');
            $response->header('Content-Disposition', 'attachment; filename="' . $file->file_name . '"');

            return $response;
        } catch (\Exception $ex) {
            return response()->json([
              'msg' => $ex->getMessage()
            ]);
        }
    });

    Route::get('/admin/groomer/{groomer_id}/document/{id}/verified', 'Admin\GroomerController@document_verified');

    Route::post('admin/groomer/load-credit', 'Admin\GroomerController@loadCredit');
    Route::post('admin/groomer/save-credit', 'Admin\GroomerController@saveCredit');

    Route::get('admin/groomer/add_service_area/{id}/{county}', 'Admin\GroomerController@add_service_area');
    Route::get('admin/groomer/remove_service_area/{id}/{county}', 'Admin\GroomerController@remove_service_area');

//    Route::get('admin/groomer/add_exclusive_area/{groomer_id}/{weekday}/{alias_id}', 'Admin\GroomerController@add_exclusive_area');
//    Route::get('admin/groomer/remove_exclusive_area/{groomer_id}/{weekday}/{alias_id}', 'Admin\GroomerController@remove_exclusive_area');

    Route::get('admin/groomer/add_service_package/{groomer_id}/{prod_id}', 'Admin\GroomerController@add_service_package');
    Route::get('admin/groomer/remove_service_package/{groomer_id}/{prod_id}', 'Admin\GroomerController@remove_service_package');

    Route::get('admin/groomer/add_blocked_breed/{groomer_id}/{breed_id}', 'Admin\GroomerController@add_blocked_breed');
    Route::get('admin/groomer/remove_blocked_breed/{groomer_id}/{breed_id}', 'Admin\GroomerController@remove_blocked_breed');

    Route::get('admin/groomer/add_notification_type/{groomer_id}/{notification_id}', 'Admin\GroomerController@add_notification_type');
    Route::get('admin/groomer/remove_notification_type/{groomer_id}/{notification_id}', 'Admin\GroomerController@remove_notification_type');

    Route::get('/admin/applications', ['as' => 'admin.applications', 'uses' => 'Admin\GroomerApplicationController@applications']);
    Route::post('/admin/applications', ['as' => 'admin.applications', 'uses' => 'Admin\GroomerApplicationController@applications']);
    Route::get('/admin/application/{id}', ['as' => 'admin.application', 'uses' => 'Admin\GroomerApplicationController@application']);

    Route::get('/admin/pre_apply', ['as' => 'admin.pre_apply', 'uses' => 'Admin\GroomerPreApplyController@pre_apply']);
    Route::post('/admin/pre_apply', ['as' => 'admin.pre_apply', 'uses' => 'Admin\GroomerPreApplyController@pre_apply']);

    Route::get('/admin/application/preapprove/{id}', [
      'as' => 'admin.application.preapprove',
      'uses' => 'Admin\GroomerApplicationController@application_preapprove'
    ]);

    Route::post('/admin/application/approve', [
      'as' => 'admin.application.approve',
      'uses' => 'Admin\GroomerApplicationController@application_approve'
    ]);
    Route::post('/admin/application/reject', [
      'as' => 'admin.application.reject',
      'uses' => 'Admin\GroomerApplicationController@application_reject'
    ]);
    Route::post('/admin/application/maybe', [
      'as' => 'admin.application.maybe',
      'uses' => 'Admin\GroomerApplicationController@application_maybe'
    ]);
    Route::post('/admin/application/remove', [
      'as' => 'admin.application.remove',
      'uses' => 'Admin\GroomerApplicationController@remove'
    ]);
    Route::post('/admin/application/status', [
      'as' => 'admin.application.status',
      'uses' => 'Admin\GroomerApplicationController@status'
    ]);
    Route::post('/admin/application/update-status', [
      'as' => 'admin.application.update-status',
      'uses' => 'Admin\GroomerApplicationController@update_status'
    ]);

    Route::post('/admin/application/update', [
        'as' => 'admin.application.update',
        'uses' => 'Admin\GroomerApplicationController@update'
    ]);

    Route::post('/admin/application/{application_id}/document/upload', ['as' => 'admin.application.document.upload', 'uses' => 'Admin\GroomerApplicationController@document_upload']);

    Route::get('/admin/application/{application_id}/document/{id}/view', function($application_id, $id) {
        try {
            $file = \App\Model\GroomerDocument::where('id', $id)->where('application_id', $application_id)->first();
            $response = Response::make(base64_decode($file->data), 200);
            $response->header('Content-Type', 'application/octet-stream');
            $response->header('Content-Disposition', 'attachment; filename="' . $file->file_name . '"');

            return $response;
        } catch (\Exception $ex) {
            return response()->json([
              'msg' => $ex->getMessage()
            ]);
        }
    });

    Route::get('/admin/pets', ['as' => 'admin.pets', 'uses' => 'Admin\PetController@pets']);
    Route::post('/admin/pets', ['as' => 'admin.pets', 'uses' => 'Admin\PetController@pets']);
    Route::get('/admin/pet/{id}', ['as' => 'admin.pet', 'uses' => 'Admin\PetController@pet']);
    Route::post('/admin/pet/update', ['as' => 'admin.pet.update', 'uses' => 'Admin\PetController@update']);
    Route::get('/admin/pet/remove/{id}', ['as' => 'admin.pet.remove', 'uses' => 'Admin\PetController@remove']);

    Route::get('/admin/pet/document/{id}', function($id) {
        try {
            $pet = \App\Model\Pet::find($id);

            $response = Response::make(base64_decode($pet->vaccinated_image), 200);
            $response->header('Content-Type', 'application/octet-stream');
            $response->header('Content-Disposition', 'attachment; filename="' . $pet->vaccinated_image_name . '"');

            return $response;
        } catch (\Exception $ex) {
            return response()->json([
              'msg' => $ex->getMessage()
            ]);
        }
    });

    Route::get('/admin/admins', ['as' => 'admin.admins', 'uses' => 'Admin\AdminController@admins']);
    Route::post('/admin/admins', ['as' => 'admin.admins', 'uses' => 'Admin\AdminController@admins']);
    Route::get('/admin/admin/{id}', ['as' => 'admin.admin', 'uses' => 'Admin\AdminController@admin']);
    Route::get('/admin/change_admin_status/{id}', ['uses' => 'Admin\AdminController@change_admin_status']);
    Route::post('/admin/admin/reset_password', ['as' => 'admin.admin_reset_password', 'uses' => 'Admin\AdminController@reset_password']);
    Route::post('/admin/admin/update', ['as' => 'admin.admin_update', 'uses' => 'Admin\AdminController@update']);

    Route::get('/admin/contacts', ['as' => 'admin.contacts', 'uses' => 'Admin\ContactController@contacts']);
    Route::post('/admin/contacts', ['as' => 'admin.contacts', 'uses' => 'Admin\ContactController@contacts']);
    Route::get('/admin/contact/{id}', ['as' => 'admin.contact', 'uses' => 'Admin\ContactController@contact']);
    Route::post('/admin/contact/reply', ['as' => 'admin.contact.reply', 'uses' => 'Admin\ContactController@reply']);
    Route::post('/admin/contact/update', ['as' => 'admin.contact.update', 'uses' => 'Admin\ContactController@update']);
    Route::post('/admin/contact/close', ['as' => 'admin.contact.close', 'uses' => 'Admin\ContactController@close']);

    Route::get('/admin/messages', ['as' => 'admin.messages', 'uses' => 'Admin\MessageController@messages']);
    Route::get('/admin/message/{id}', ['as' => 'admin.messages', 'uses' => 'Admin\MessageController@message']);
    Route::get('/admin/message_user/{id}', ['as' => 'admin.messages_user', 'uses' => 'Admin\MessageController@message_user']);
    Route::post('/admin/messages', ['as' => 'admin.messages', 'uses' => 'Admin\MessageController@messages']);
    Route::post('/admin/messages/send', ['as' => 'admin.message.send', 'uses' => 'Admin\MessageController@send']);
    Route::post('/admin/messages/detail', ['as' => 'admin.message.detail', 'uses' => 'Admin\MessageController@detail']);

    Route::get('/admin/promo_codes', ['as' => 'admin.promo_codes', 'uses' => 'Admin\PromoCodeController@promo_codes']);
    Route::get('/admin/promo_codes/{code}', ['as' => 'admin.promo_codes', 'uses' => 'Admin\PromoCodeController@promo_codes']);
    Route::post('/admin/promo_codes', ['as' => 'admin.promo_codes', 'uses' => 'Admin\PromoCodeController@promo_codes']);
    Route::post('/admin/promo_code/change_status', ['as' => 'admin.promo_code.change_status', 'uses' => 'Admin\PromoCodeController@change_status']);
    Route::post('/admin/promo_code/add', ['as' => 'admin.promo_code.add', 'uses' => 'Admin\PromoCodeController@add']);
    Route::post('/admin/promo_code/update', ['as' => 'admin.promo_code.add', 'uses' => 'Admin\PromoCodeController@update']);
    Route::post('/admin/promo_code/load', ['as' => 'admin.promo_code.add', 'uses' => 'Admin\PromoCodeController@load']);
    Route::get('/admin/redeemed_groupon', ['as' => 'admin.redeemed_groupon', 'uses' => 'Admin\PromoCodeController@redeemed_groupon']);
    Route::post('/admin/redeemed_groupon', ['as' => 'admin.redeemed_groupon', 'uses' => 'Admin\PromoCodeController@redeemed_groupon']);
    Route::get('/admin/promo_redeemed_history', ['as' => 'admin.promo_redeemed_history', 'uses' => 'Admin\PromoCodeController@promo_redeemed_history']);
    Route::post('/admin/promo_redeemed_history', ['as' => 'admin.promo_redeemed_history', 'uses' => 'Admin\PromoCodeController@promo_redeemed_history']);

    Route::get('/admin/profit-sharing', ['uses' => 'Admin\ProfitSharing\ProfitSharingController@show']);
    Route::post('/admin/profit-sharing', ['uses' => 'Admin\ProfitSharing\ProfitSharingController@show']);
    Route::post('/admin/profit-sharing/update-default', 'Admin\ProfitSharing\ProfitSharingController@updateDefault');
    Route::post('/admin/profit-sharing/search-groomer', 'Admin\ProfitSharing\ProfitSharingController@searchGroomer');
    Route::post('/admin/profit-sharing/add-exception', 'Admin\ProfitSharing\ProfitSharingController@addException');
    Route::post('/admin/profit-sharing/update-exception', 'Admin\ProfitSharing\ProfitSharingController@updateException');
    Route::post('/admin/profit-sharing/load-detail', 'Admin\ProfitSharing\ProfitSharingController@loadDetail');
    Route::post('/admin/profit-sharing/load-user-list', 'Admin\ProfitSharing\ProfitSharingController@loadUserList');
    Route::post('/admin/profit-sharing/search-user', 'Admin\ProfitSharing\ProfitSharingController@searchUser');
    Route::post('/admin/profit-sharing/add-user-exception', 'Admin\ProfitSharing\ProfitSharingController@addUserException');
    Route::post('/admin/profit-sharing/update-user-exception', 'Admin\ProfitSharing\ProfitSharingController@updateUserException');
    Route::post('/admin/profit-sharing/load-user-detail', 'Admin\ProfitSharing\ProfitSharingController@loadUserDetail');

    Route::get('/admin/profit-sharing/report', ['uses' => 'Admin\ProfitSharing\ProfitSharingController@report']);
    Route::post('/admin/profit-sharing/report', ['uses' => 'Admin\ProfitSharing\ProfitSharingController@report']);
    Route::post('/admin/profit-sharing/report/load-detail', ['uses' => 'Admin\ProfitSharing\ProfitSharingController@loadProfitSharingDetail']);
    Route::any('/admin/profit-sharing/report-new', ['uses' => 'Admin\ProfitSharing\ProfitSharingController@report_new']);

    Route::get('/admin/affiliates', ['as' => 'admin.affiliates', 'uses' => 'Admin\AffiliateController@affiliates']);
    Route::post('/admin/affiliates', ['uses' => 'Admin\AffiliateController@affiliates']);
    Route::get('/admin/affiliate_withdraw_requests', ['as' => 'admin.affiliate_withdraw_requests', 'uses' => 'Admin\AffiliateController@withdraw_requests']);
    Route::post('/admin/affiliate_withdraw_requests', ['uses' => 'Admin\AffiliateController@withdraw_requests']);
    Route::get('/admin/affiliate/{id}', ['as' => 'admin.affiliate', 'uses' => 'Admin\AffiliateController@affiliate']);
    Route::post('/admin/affiliate/change-redeem-status', ['uses' => 'Admin\AffiliateController@change_redeem_status']);
    Route::post('/admin/affiliate/update', [
        'as' => 'admin.affiliate.update',
        'uses' => 'Admin\AffiliateController@update'
    ]);

    Route::get('/admin/reports/march-2018', 'Admin\Reports\March2018Controller@show');
    Route::post('/admin/reports/march-2018', 'Admin\Reports\March2018Controller@show');

    Route::get('/admin/reports/special-promotion', 'Admin\Reports\SpecialPromotionController@show');
    Route::post('/admin/reports/special-promotion', 'Admin\Reports\SpecialPromotionController@show');

    Route::get('/admin/reports/appointment-cycle', 'Admin\Reports\AppointmentCycleController@show');
    Route::post('/admin/reports/appointment-cycle', 'Admin\Reports\AppointmentCycleController@show');

    Route::get('/admin/reports/groomer-evaluation', 'Admin\Reports\EvaluationController@show');
    Route::post('/admin/reports/groomer-evaluation', 'Admin\Reports\EvaluationController@show');

    Route::get('/admin/reports/groomer-rating', 'Admin\Reports\EvaluationController@rating');
    Route::post('/admin/reports/groomer-rating', 'Admin\Reports\EvaluationController@rating');
    Route::get('/admin/reports/groomer-rating/{g_id}/{u_id}', 'Admin\Reports\EvaluationController@rating_link');

    Route::get('/admin/reports/groomer-cycle', 'Admin\Reports\GroomerCycleController@show');
    Route::post('/admin/reports/groomer-cycle', 'Admin\Reports\GroomerCycleController@show');

    Route::get('/admin/reports/groomer-fav', 'Admin\Reports\GroomerFavController@show');
    Route::post('/admin/reports/groomer-fav', 'Admin\Reports\GroomerFavController@show');
    Route::get('/admin/reports/groomer-fav/{id}', 'Admin\Reports\GroomerFavController@show');
    Route::post('/admin/reports/groomer-fav/delete', 'Admin\Reports\GroomerFavController@delete');
    Route::post('/admin/reports/groomer-fav/delete-all', 'Admin\Reports\GroomerFavController@delete_all');

    Route::get('/admin/reports/check-in-out-trend', 'Admin\Reports\CheckInOutTrendController@show');
    Route::post('/admin/reports/check-in-out-trend', 'Admin\Reports\CheckInOutTrendController@show');

    Route::get('/admin/reports/breed_size', 'Admin\Reports\BreedSizeController@show');
    Route::post('/admin/reports/breed_size', 'Admin\Reports\BreedSizeController@show');

    Route::any('/admin/reports/vouchers', 'Admin\Reports\VoucherController@show');

    Route::any('/admin/reports/user-credit', 'Admin\Reports\UserCreditController@show');

    Route::any('/admin/reports/breed-booked', 'Admin\Reports\BreedBookedController@show');

    Route::any('/admin/reports/customer-retention', 'Admin\Reports\CustomerRetentionController@show');
    Route::post('/admin/reports/customer-retention', ['as' => 'admin.reports.customer_retention', 'uses' => 'Admin\Reports\CustomerRetentionController@show']);

    Route::any('/admin/reports/notification', 'Admin\Reports\NotificationController@show');
    Route::any('/admin/reports/rewards', ['as' => 'admin.reports.rewards', 'uses' =>'Admin\Reports\RewardsController@show']);
    Route::post('/admin/reports/rewards_adjust', 'Admin\Reports\RewardsController@adjust');

    Route::any('/admin/reports/add-on-sales', 'Admin\Reports\AddOnSalesController@show');
    Route::post('/admin/reports/add-on-sales', ['as' => 'admin.reports.add_on_sales', 'uses' => 'Admin\Reports\AddOnSalesController@show']);

    Route::get('/admin/reports/survey', 'Admin\Reports\SurveyController@show');
    Route::post('/admin/reports/survey', ['as' => 'admin.reports.survey', 'uses' => 'Admin\Reports\SurveyController@show']);

    Route::any('/admin/reports/cancellationsdetails', 'Admin\Reports\CancellationsSummaryController@showdetails');
    Route::post('/admin/reports/cancellationsdetails', ['as' => 'admin.reports.cancellationsdetails', 'uses' => 'Admin\Reports\CancellationsSummaryController@showdetails']);

    Route::any('/admin/reports/cancellationsummary', 'Admin\Reports\CancellationsSummaryController@show');
    Route::post('/admin/reports/cancellationsummary', ['as' => 'admin.reports.cancellationsummary', 'uses' => 'Admin\Reports\CancellationsSummaryController@show']);

    Route::any('/admin/reports/cancellationsummarygroomer', 'Admin\Reports\CancellationsSummaryController@showgroomer');
//    Route::post('/admin/reports/cancellationsummarygroomer', ['as' => 'admin.reports.cancellationsummarygroomer', 'uses' => 'Admin\Reports\CancellationsSummaryGroomerController@show']);

    Route::get('/admin/reports/paymentsummary', 'Admin\Reports\PaymentSummaryController@show');
    Route::post('/admin/reports/paymentsummary', ['as' => 'admin.reports.paymentsummary', 'uses' => 'Admin\Reports\PaymentSummaryController@show']);

    Route::get('/admin/reports/paymentdetails', 'Admin\Reports\PaymentDetailsController@show');
    Route::post('/admin/reports/paymentdetails', ['as' => 'admin.reports.paymentdetails', 'uses' => 'Admin\Reports\PaymentDetailsController@show']);

    Route::get('/admin/reports/login-history', 'Admin\Reports\LoginHistoryController@show');
    Route::post('/admin/reports/login-history', ['as' => 'admin.reports.login-history', 'uses' => 'Admin\Reports\LoginHistoryController@show']);

    Route::get('/admin/reports/groomer-login-history', 'Admin\Reports\GroomerLoginHistoryController@show');
    Route::post('/admin/reports/groomer-login-history', ['as' => 'admin.reports.groomer-login-history', 'uses' => 'Admin\Reports\GroomerLoginHistoryController@show']);

    Route::get('/admin/reports/groomers-by-countysummary', 'Admin\Reports\GroomerByCountyController@show');
    Route::post('/admin/reports/groomers-by-countysummary', ['as' => 'admin.reports.groomers-by-countysummary', 'uses' => 'Admin\Reports\GroomerByCountyController@show']);

    Route::get('/admin/reports/map-from-batchgeo', function () {
        return view('admin.reports.map-from-batchgeo');
    });

    Route::get('/admin/reports/groomers-by-countydetails', 'Admin\Reports\GroomerByCountyController@showdetails');
    Route::post('/admin/reports/groomers-by-countydetails', ['as' => 'admin.reports.groomers-by-countydetails', 'uses' => 'Admin\Reports\GroomerByCountyController@showdetails']);

    Route::get('/admin/reports/chargeback', 'Admin\Reports\ChargeBackController@show');
    Route::post('/admin/reports/chargeback', ['as' => 'admin.reports.chargeback', 'uses' => 'Admin\Reports\ChargeBackController@show']);
    Route::post('/admin/reports/chargeback/upload', 'Admin\Reports\ChargeBackController@upload');
    Route::post('/admin/reports/chargeback/update', 'Admin\Reports\ChargeBackController@update');

//    Route::get('/admin/reports/rewards', function() {
//        echo 'rewards';
//    });
    Route::any('/admin/reports/ziplookup', 'Admin\Reports\ZipLookupController@show');
    Route::get('/admin/reports/get_zip/{zip}', ['as' => 'admin.get_zip', 'uses' => 'Admin\Reports\ZipLookupController@get_zip']);
    Route::post('/admin/reports/update_lookup', ['as' => 'admin.lookup.update', 'uses' => 'Admin\Reports\ZipLookupController@update_lookup']);

    Route::get('/admin/reports/promocode-performance', 'Admin\Reports\PromocodeController@performance');
    Route::post('/admin/reports/promocode-performance', 'Admin\Reports\PromocodeController@performance');

    # for notification groomer / user typeahead #
    Route::get('/admin/get_receivers/{user_type}/{q}', ['as' => 'admin.get_users', 'uses' => 'Admin\MessageController@get_receivers']);


    Route::any('/admin/profitshare/migration', 'Admin\ProfitSharing\ProfitSharingController@migration');

    #### Groomer Add-on Orders #####
    Route::get('/admin/order', ['uses' => 'Admin\OrderController@index']);
    Route::any('/admin/order/print', ['uses' => 'Admin\OrderController@show_print']);
    Route::post('/admin/order', ['uses' => 'Admin\OrderController@index']);
    Route::post('/admin/order/save', ['uses' => 'Admin\OrderController@save']);
    Route::post('/admin/order/update', ['uses' => 'Admin\OrderController@update']);
    Route::post('/admin/order/shipping/save', ['uses' => 'Admin\OrderController@shipping_save']);
    Route::any('/admin/order/history', ['uses' => 'Admin\OrderController@history']);
    Route::post('/admin/order/bind_orders', ['uses' => 'Admin\OrderController@bind_orders']);
    Route::post('/admin/order/add', ['uses' => 'Admin\OrderController@add']);
    Route::post('/admin/order/delete', ['uses' => 'Admin\OrderController@delete']);
    Route::post('/admin/order/unship', ['uses' => 'Admin\OrderController@unship']);

    #### Groomer API Simulator #####
    Route::get('/admin/groomer-simulator/{groomer_id}', ['uses' => 'Admin\GroomerSimulatorController@index']);
});


/** Desktop user auth **/

Route::get('/user', 'User\MainController@show');
Route::post('/user/login', 'User\LoginController@login');
Route::post('/user/login/facebook', 'User\LoginController@FBLogin');
Route::get('/user/logout', 'User\LoginController@logout');
Route::post('/user/register', 'User\LoginController@register');

Route::post('/user/forgot-password/verify-email', 'User\ForgotPasswordController@verify_email_desktop');
Route::post('/user/forgot-password/verify-key', 'User\ForgotPasswordController@verify_key_desktop');
Route::post('/user/forgot-password/update-password', 'User\ForgotPasswordController@update_password_desktop');
Route::post('/user/check-zip', 'User\CheckZipController@post');
Route::get('/user/zip-available', 'User\CheckZipController@showAvailable'); //Not available any longer
Route::get('/user/zip-not-available', 'User\CheckZipController@showNotAvailable');
Route::post('/user/subscribe', 'User\SubscribeController@post');

Route::group(['prefix' => 'user/schedule'], function() {
    Route::get('/select-dog', 'User\Schedule\SelectDogController@show');
    Route::post('/select-dog', 'User\Schedule\SelectDogController@show');
    Route::post('/select-dog/update-size', 'User\Schedule\SelectDogController@updateSize');
    Route::post('/select-dog/update-package', 'User\Schedule\SelectDogController@updatePackage');

    Route::get('/select-cat', 'User\Schedule\SelectCatController@show');
    Route::post('/select-cat', 'User\Schedule\SelectCatController@show');
    Route::post('/select-cat/update-package', 'User\Schedule\SelectCatController@updatePackage');

    Route::get('/select-cat-new', 'User\Schedule\SelectCatController@show_new');
    Route::post('/select-cat-new', 'User\Schedule\SelectCatController@show_new');
    Route::get('/select-addon-new', 'User\Schedule\SelectAddOnController@show_for_test');

    Route::get('/select-addon', 'User\Schedule\SelectAddOnController@show');
    Route::post('/select-addon', 'User\Schedule\SelectAddOnController@show');
    Route::post('/select-addon/update-shampoo', 'User\Schedule\SelectAddOnController@updateShampoo');
    Route::post('/select-addon/add', 'User\Schedule\SelectAddOnController@addAddon');
    Route::post('/select-addon/remove', 'User\Schedule\SelectAddOnController@removeAddon');
    Route::post('/select-addon/dematting', 'User\Schedule\SelectAddOnController@selectDematting');
    Route::post('/select-addon/sendTerm', 'User\Schedule\SelectAddOnController@sendTerm');

    Route::get('/clear', 'User\Schedule\ProcessController@clear');
    Route::get('/clear/{pet_id}', 'User\Schedule\ProcessController@clear');

    Route::get('/select-pet', 'User\Schedule\SelectPetController@show');

    Route::post('/select-pet/post', 'User\Schedule\SelectPetController@post');

    Route::get('/select-rebook/{appointment_id}', 'User\Schedule\SelectRebookController@show');

    //After users logins. Be sure that it's uner /user/schedule/select-date...
    Route::group(['middleware' => 'user'], function() {

        Route::get('/select-date', 'User\Schedule\SelectDateController@show');
        Route::get('/select-date-at', 'User\Schedule\SelectDateController@show');
        Route::post('/select-date/load-times', 'User\Schedule\SelectDateController@loadTimes'); //can be removed
        Route::post('/select-date/load-groomer-times', 'User\Schedule\SelectDateController@loadGroomerTimes'); //can be removed
        Route::post('/select-date/post', 'User\Schedule\SelectDateController@post');
        Route::get('/select-date/load-groomer-calendar/{groomer_id}/{target_date}', 'User\Schedule\SelectDateController@loadGroomerCalendar');
        Route::get('/select-date/load-groomer-calendar2/{groomer_id}/{target_date}', 'User\Schedule\SelectDateController@loadGroomerCalendar2');
        Route::post('/select-date/load-groomer-calendar-availability', 'User\Schedule\SelectDateController@loadGroomerCalendarAvailability');
        Route::get('/select-address', 'User\Schedule\SelectAddressController@show');
        Route::post('/select-address/post', 'User\Schedule\SelectAddressController@post');

        Route::get('/select-payment', 'User\Schedule\SelectPaymentController@show');
        Route::post('/select-payment/post', 'User\Schedule\SelectPaymentController@post');
        Route::post('/select-payment/use-credit', 'User\Schedule\SelectPaymentController@useCredit');
        Route::post('/select-payment/apply-code', 'User\Schedule\SelectPaymentController@applyCode');

        Route::post('/process', 'User\Schedule\ProcessController@process');

        Route::get('/thank-you', 'User\Schedule\SelectPaymentController@thankYou');
    });
});

//New API for CA.
Route::get('/user/api/simulator', 'User\API\SimulatorController@index');

Route::group(['prefix' => 'user/api'], function() {

    Route::any('/contact/add', ['uses' => 'ContactController@add']);

    Route::any('/signup', 'User\API\SignUpController@signup');
    Route::any('/signup2', 'User\API\SignUpController@signup2');
    Route::any('/login', 'User\API\SignUpController@login');
    Route::any('/logout', 'User\API\SignUpController@logout');
    Route::any('/login_3rd_party', 'User\API\SignUpController@login_3rd_party');
    Route::any('/forgot_password/verify_email', ['uses' => 'User\API\ForgotPasswordController@verify_email']);
    Route::any('/forgot_password/verify_key', ['uses' => 'User\API\ForgotPasswordController@verify_key']);
    Route::any('/forgot_password/update_password', ['uses' => 'User\API\ForgotPasswordController@update_password']);

    Route::any('/profile/update', 'User\API\ProfileController@update');
    Route::any('/profile/delete', 'User\API\ProfileController@delete');
    Route::any('/credit/get_available', 'User\API\CreditController@get_available');
    Route::any('/update_device_token', 'User\API\ProfileController@update_device_token');
    Route::any('/credit/get_referals', 'User\API\CreditController@get_referals');

    Route::any('/pet', 'User\API\PetController@show');
    Route::any('/pet/available_type', 'User\API\PetController@available_type');
    Route::any('/pet/dogs', 'User\API\PetController@dogs');
    Route::any('/pet/cats', 'User\API\PetController@cats');
    Route::any('/pet/breed_list', 'User\API\PetController@breed_list');
    Route::any('/pet/get_promo_images', 'User\API\PetController@get_promo_images');
    Route::any('/pet/size_list', 'User\API\PetController@size_list');
    Route::any('/pet/temperment_list', 'User\API\PetController@temperment_list');
    Route::any('/pet/save', 'User\API\PetController@save');
    Route::any('/pet/remove', 'User\API\PetController@remove');

    Route::any('/appointment', 'User\API\AppointmentController@show');
    Route::any('/appointment/product', 'User\API\AppointmentController@product');
    Route::any('/appointment/times', 'User\API\AppointmentController@times');
    Route::any('/appointment/places', 'User\API\AppointmentController@places');
    Route::any('/appointment/confirm', 'User\API\AppointmentController@confirm');
    Route::any('/appointment/post', 'User\API\AppointmentController@post');
    Route::any('/appointment/update', 'User\API\AppointmentController@update');
    Route::any('/appointment/cancel', 'User\API\AppointmentController@cancel');
    Route::any('/appointment/get_fee', 'User\API\AppointmentController@get_fee');   //Return Rescheduling fee or Cancelling fee or Fav groomer fee
    Route::any('/appointment/tip', 'User\API\AppointmentController@tip');
    Route::any('/appointment/rating', 'User\API\AppointmentController@rating');
    Route::any('/appointment/survey', 'User\API\AppointmentController@survey');
    Route::any('/appointment/survey_get', 'User\API\AppointmentController@survey_get');
    Route::any('/appointment/history', 'User\API\AppointmentController@history');
    Route::any('/appointment/upcoming', 'User\API\AppointmentController@upcoming');
    Route::any('/appointment/last', 'User\API\AppointmentController@last');
    Route::any('/appointment/get_by_id', 'User\API\AppointmentController@get_by_id');
    Route::any('/appointment/count', 'User\API\AppointmentController@get_count');
    Route::any('/appointment/get_survey_appt_id', 'User\API\AppointmentController@get_survey_appt_id');

    Route::any('/appointment/explode_get_sizes', ['uses' => 'User\API\AppointmentController@explode_get_sizes']);
    Route::any('/appointment/explode_get_package_addon', ['uses' => 'User\API\AppointmentController@explode_get_package_addon']);

    Route::any('/address', 'User\API\AddressController@show');
    Route::any('/address/save', 'User\API\AddressController@save');
    Route::any('/address/remove', 'User\API\AddressController@remove');
    Route::any('/address/check_zip', 'User\API\AddressController@check_zip');

    Route::any('/billing', 'User\API\BillingController@show');
    Route::any('/billing/save', 'User\API\BillingController@save');
    Route::any('/billing/remove', 'User\API\BillingController@remove');
    Route::any('/billing/set_default', 'User\API\BillingController@set_default');
    Route::any('/billing/resend_amt', 'User\API\BillingController@resend_amt');

    Route::any('/messages', 'User\API\MessageController@show');
    Route::any('/message/detail', 'User\API\MessageController@detail');
    Route::any('/message/send', 'User\API\MessageController@send');

    Route::any('/groomer/detail', 'User\API\GroomerController@detail');
    Route::any('/groomer/make_favorite', 'User\API\GroomerController@make_favorite');
    Route::any('/groomer/make_blocked', 'User\API\GroomerController@make_blocked');
    Route::any('/groomer/get_list', 'User\API\GroomerController@get_list');
    Route::any('/groomer/get_favorite_list', 'User\API\GroomerController@get_favorite_list');
    Route::any('/groomer/get_blocked_list', 'User\API\GroomerController@get_blocked_list');
    Route::post('/groomer/groomer_calendar',  ['uses' => 'User\API\GroomerController@groomer_calendar']);
    Route::post('/groomer/groomer_calendar2',  ['uses' => 'User\API\GroomerController@groomer_calendar2']);
    Route::post('/groomer/groomer_calendar_availability',  ['uses' => 'User\API\GroomerController@groomer_calendar_availability']);

    //Route::any('/pet_size_list', 'User\APIController@pet_size_list');
    //Route::any('/pet_list', 'User\APIController@pet_list');
    //Route::any('/billing_list', 'User\APIController@billing_list');
    //Route::any('/times', 'User\AppointmentController@times');
    //Route::any('/places', 'User\AppointmentController@places');

    //Seems not to be used.
    //Route::any('/post_appointment', 'User\APIController@post_appointment');

    Route::any('/payment/set-default', 'User\API\PaymentController@set_default');
    Route::any('/pet/load/photo', 'User\API\PetController@load_photo');
    Route::any('/pet/upload/photo', 'User\API\PetController@upload_photo');
    Route::any('/pet/remove', 'User\API\PetController@remove');
});

Route::get('/user/gift-cards', 'User\GiftCardController@show');
Route::get('/user/memberships', 'User\MembershipController@show');

//Shared by APIs & WEB together as common modules
Route::group(['middleware' => 'user', 'prefix' => 'user'], function() {
    Route::get('/home', 'User\HomeController@show');

    Route::post('/pet/add', 'User\PetController@add');
    Route::post('/pet/update', 'User\PetController@update');
    Route::any('/pet/load', 'User\PetController@load');

    Route::post('/address/add', 'User\AddressController@add');
    Route::post('/address/update', 'User\AddressController@update');
    Route::post('/address/load', 'User\AddressController@load');

    Route::get('/payments', 'User\PaymentController@show');
    Route::post('/payment/add', 'User\PaymentController@add');       //Used from WEB order completion too. Adding a new CC.
    Route::post('/payment/update', 'User\PaymentController@update'); //Used from WEB order completion too.
    Route::post('/payment/load', 'User\PaymentController@load');
    Route::post('/payment/delete_card', 'User\PaymentController@delete_card');
    Route::post('/payment/verify_amt', 'User\PaymentController@verify_amt');
    Route::post('/payment/resend_amt', 'User\PaymentController@resend_amt');
    Route::post('/payment/get_service_address', 'User\PaymentController@get_service_address');

    Route::get('/mygroomer', 'User\MyGroomerController@show');
    Route::post('/mygroomer/make-favorite', 'User\MyGroomerController@makeFavorite');
    Route::post('/mygroomer/remove-favorite', 'User\MyGroomerController@removeFavorite');

    Route::get('/appointment/list', 'User\Appointment\ListController@show');
    Route::post('/appointment/rate', 'User\Appointment\ListController@rate');
    Route::post('/appointment/tip', 'User\Appointment\ListController@tip');
    Route::post('/appointment/mark-as-favorite', 'User\Appointment\ListController@markAsFavorite');
    Route::post('/appointment/rebook', 'User\Appointment\ListController@rebook');

    Route::get('/appointment/edit/{appointment_id}', 'User\Appointment\EditController@show');
    Route::post('/appointment/edit/post', 'User\Appointment\EditController@post');
    Route::get('/appointment/delete', 'User\Appointment\EditController@delete');
    Route::get('/appointment/cancel/{appointment_id}', 'User\Appointment\EditController@show_cancel');

    Route::get('/pets/dogs', 'User\Pets\DogsController@show');
    Route::get('/pets/cats', 'User\Pets\CatsController@show');

    Route::get('/myaccount', 'User\ProfileController@myaccount');
    Route::get('/myaccount-edit', 'User\ProfileController@myaccount_edit');
    Route::post('/myaccount/user_update', 'User\ProfileController@user_update');

    Route::post('/myaccount/dog_update', 'User\ProfileController@dog_update');
    Route::post('/myaccount/cat_update', 'User\ProfileController@cat_update');
    Route::post('/myaccount/address_update', 'User\ProfileController@address_update');

    Route::post('/profile/load', 'User\ProfileController@load');
    Route::post('/profile/load_temp', 'User\ProfileController@load_temp');
    Route::post('/profile/update', 'User\ProfileController@update');
    Route::post('/profile/reset-password', 'User\ProfileController@resetPassword');

    Route::post('/gift-cards/buy', 'User\GiftCardController@buy');
    Route::any('/gift-cards/payment', 'User\GiftCardController@payment');
    Route::post('/gift-cards/buy/process', 'User\GiftCardController@buy_process');

    Route::post('/memberships/buy', 'User\MembershipController@buy');
    Route::any('/memberships/payment', 'User\MembershipController@payment');
    Route::post('/memberships/buy/process', 'User\MembershipController@buy_process');


});

Route::get('/user/help',['as' => 'user', function() {
    return view('user/faqs');
}]);

//Route::get('/user/survey',['as' => 'user', function() {
//    return view('user/survey');
Route::get('/user/survey', 'User\UserSurveyController@show');
Route::post('/user/survey', 'User\UserSurveyController@submit');



//Route::get('/user/sign-up',['as' => 'user', function() {
//    return view('user/sign-up');
//}]);

Route::get('/user/sign-up', 'User\LoginController@sign_up');

/*

Route::get('/user/home',['as' => 'user.home', function() {
    return view('user/home');
}]);

Route::post('/user/login', ['as' => 'user.login','uses' => 'UserAuth\AuthController@login']);
Route::post('/user/login/3rd-party', ['as' => 'user.login.3rd-party','uses' => 'UserAuth\SignUpController@login_3rd_party']);
Route::get('/user/logout', ['as' => 'user.logout','uses' => 'UserAuth\AuthController@logout']);

Route::post('/user/check-zip', ['as' => 'user.check-zip', 'uses' => 'User\AddressController@checkZip']);
Route::get('/user/zip-not-available', ['as' => 'user.zip-not-available',function() {
    return view('user/zip-not-available');
}]);
Route::post('/user/zip-not-available', ['uses' => 'User\AddressController@saveZipNotAvailable']);
Route::get('/user/zip-available', ['as' => 'user.zip-available',function() {
    return view('user/zip-available');
}]);

Route::post('/user/signup_user', ['uses' => 'UserAuth\AuthController@registration']);

Route::get('/user/appointments', ['as' => 'user.appointments', function() {
    return view('user/appointments');
}]);

Route::get('/user/appointment/select-service', ['as' => 'user.appointment.select-service', 'uses' => 'User\AppointmentController@selectService']);
Route::post('/user/appointment/get-package', ['as' => 'user.appointment.get-package', 'uses' => 'User\AppointmentController@getPackage']);
Route::post('/user/appointment/update-appointment', ['as' => 'user.appointment.update-appointment', 'uses' => 'User\AppointmentController@updateAppointment']);
Route::post('/user/appointment/update-service', ['as' => 'user.appointment.update-service', 'uses' => 'User\AppointmentController@updateService']);
Route::post('/user/appointment/update-addons', ['as' => 'user.appointment.update-addons', 'uses' => 'User\AppointmentController@updateAddons']);

Route::get('/user/appointment/add-ons', ['as' => 'user.appointment.add-ons', 'uses' => 'User\AppointmentController@getAddons']);

Route::get('/user/appointment/login-signup', ['as' => 'user.appointment.login-signup', function() {
    if (Auth::guard('user')->check()) {
        if(!empty(Session::get('appointment:service'))) {
            return redirect('/user/appointment/select-pet/' . Session::get('appointment:service')->pet_type);
        } else {
            return redirect('/user/appointment/select-service');

        }
    } else {
        return view('user/appointment/login-signup');
    }
}]);

Route::group(['middleware' => ['user']], function () {
    Route::get('/user/appointment/select-pet/{pet_type}', ['as' => 'user.appointment.select-pet', 'uses' => 'User\PetController@getUserPets']);
    Route::post('/user/appointment/update-pet', ['as' => 'user.appointment.update-pet', 'uses' => 'User\AppointmentController@updatePet']);


    Route::get('/user/appointment/date-time', ['as' => 'user.appointment.date-time', function() {
        return view('user/appointment/date-time');
    }]);
    Route::post('/user/appointment/update-date-time', ['as' => 'user.appointment.update-date-time', 'uses' => 'User\AppointmentController@updateDatetime']);


    Route::get('/user/appointment/select-address', ['as' => 'user.appointment.select-address', function() {
        return view('user/select-address');
    }]);
    Route::get('/user/appointment/select-payment', ['as' => 'user.appointment.select-payment', function() {
        return view('user/appointment/select-payment');
    }]);
    Route::get('/user/appointment/book', ['as' => 'user.appointment.book', function() {
        return view('user/appointment/book');
    }]);
    Route::get('/user/appointment/confirm', ['as' => 'user.appointment.confirm', function() {
        return view('user/appointment/confirm');
    }]);

    # pet
    Route::post('/user/pet/update', ['as' => 'user.pet.update', 'uses' => 'User\PetController@updatePet']);
    Route::post('/user/pet/get-by-id', ['as' => 'user.pet.get-by-id', 'uses' => 'User\PetController@getById']);


});
*/

//spaw migration
Route::get('/affiliate/forgot-password/spaw-verify-email', [
    'as' => 'affiliate.forgot-password.spaw-verify-email',
    'uses' => 'AffiliateAuth\SpawForgotPasswordController@show_verify_email_form'
]);
Route::post('/affiliate/forgot-password/spaw-verify-email', ['uses' => 'AffiliateAuth\SpawForgotPasswordController@verify_email']);
Route::get('/affiliate/forgot-password/spaw-verify-key', [
    'as' => 'affiliate.forgot-password.spaw-verify-key',
    'uses' => 'AffiliateAuth\SpawForgotPasswordController@show_verify_key_form'
]);
Route::post('/affiliate/forgot-password/spaw-verify-key', ['uses' => 'AffiliateAuth\SpawForgotPasswordController@verify_key']);

Route::get('/affiliate/forgot-password/spaw-update-password', [
    'as' => 'affiliate.forgot-password.spaw-update-password',
    'uses' => 'AffiliateAuth\SpawForgotPasswordController@show_update_password_form'
]);
Route::post('/affiliate/forgot-password/spaw-update-password', ['uses' => 'AffiliateAuth\SpawForgotPasswordController@update_password']);


/** Affiliate auth **/
Route::get('/affiliate', ['as' => 'affiliate', 'uses' => 'Affiliate\AffiliateController@index']);

Route::get('/affiliate/apply', ['as' => 'affiliate.apply', 'uses' => 'AffiliateAuth\AuthController@showApplyForm']);
Route::post('/affiliate/apply', ['uses' => 'AffiliateAuth\AuthController@register']);

Route::get('/affiliate/login', ['as' => 'affiliate.login', 'uses' => 'AffiliateAuth\AuthController@showLoginForm']);
Route::post('/affiliate/login', ['uses' => 'AffiliateAuth\AuthController@login']);
Route::post('/affiliate/login-as', ['uses' => 'AffiliateAuth\AuthController@loginAs']);

# forgot password
Route::get('/affiliate/forgot-password/verify-email', [
  'as' => 'affiliate.forgot-password.verify-email',
  'uses' => 'AffiliateAuth\ForgotPasswordController@show_verify_email_form'
]);
Route::post('/affiliate/forgot-password/verify-email', ['uses' => 'AffiliateAuth\ForgotPasswordController@verify_email']);

Route::get('/affiliate/forgot-password/verify-key', [
  'as' => 'affiliate.forgot-password.verify-key',
  'uses' => 'AffiliateAuth\ForgotPasswordController@show_verify_key_form'
]);
Route::post('/affiliate/forgot-password/verify-key', ['uses' => 'AffiliateAuth\ForgotPasswordController@verify_key']);

Route::get('/affiliate/forgot-password/update-password', [
  'as' => 'affiliate.forgot-password.update-password',
  'uses' => 'AffiliateAuth\ForgotPasswordController@show_update_password_form'
]);
Route::post('/affiliate/forgot-password/update-password', ['uses' => 'AffiliateAuth\ForgotPasswordController@update_password']);

# after affiliate login
Route::group(['middleware' => ['affiliate']], function () {

    Route::get('/affiliate/logout', ['uses' => 'AffiliateAuth\AuthController@logout']);
    Route::get('/affiliate/earnings', ['as' => 'affiliate.earnings', 'uses' => 'Affiliate\AffiliateController@earnings']);
    Route::post('/affiliate/earnings', ['as' => 'affiliate.earnings', 'uses' => 'Affiliate\AffiliateController@earnings']);

    Route::post('/affiliate/withdraw', ['uses' => 'Affiliate\AffiliateController@withdraw']);


    Route::get('/affiliate/promo-code', ['as' => 'affiliate.promo-code', 'uses' => 'Affiliate\AffiliateController@promo_code']);
    Route::post('/affiliate/promo-code', ['as' => 'affiliate.promo-code', 'uses' => 'Affiliate\AffiliateController@promo_code']);
    Route::get('/affiliate/create-promo-code', ['uses' => 'Affiliate\AffiliateController@create_promo_code']);
    Route::post('/affiliate/create-custom-promo-code', ['uses' => 'Affiliate\AffiliateController@create_custom_promo_code']);

    Route::get('/affiliate/contact-us', ['as' => 'affiliate.contact-us', 'uses' => 'Affiliate\AffiliateController@contact_us']);
    Route::post('/affiliate/contact-us', ['as' => 'affiliate.contact-us', 'uses' => 'Affiliate\AffiliateController@contact_us']);
    Route::post('/affiliate/send-contact-us', ['uses' => 'Affiliate\AffiliateController@send_contact_us']);

    Route::get('/affiliate/my-account', ['as' => 'affiliate.my-account', 'uses' => 'Affiliate\AffiliateController@my_account']);
    Route::post('/affiliate/my-account', ['as' => 'affiliate.my-account', 'uses' => 'Affiliate\AffiliateController@my_account']);
    Route::post('/affiliate/update-my-account', ['uses' => 'Affiliate\AffiliateController@update_my_account']);
});

Route::get('/sms-reply', ['uses' => 'SMSReplyController@process']);
Route::post('/sms-reply', ['uses' => 'SMSReplyController@process']);

Route::get('/gc/sms-reply', ['uses' => 'SMSReplyController@gc_process']);
Route::post('/gc/sms-reply', ['uses' => 'SMSReplyController@gc_process']);

Route::get('/api/twilio', function() {
    //$ret = \App\Lib\Applozic::get_user_detail(34); //Jun
    //$ret = \App\Lib\Applozic::get_user_detail(60); //Charles...
    //$ret = \App\Lib\Applozic::block_user( 34,'B' ); //block a user
    //$ret = \App\Lib\Applozic::block_user( 34 ,'U' ); //unblock a user

    //$ret = \App\Lib\Applozic::get_group_list( ); //get groups list.
    //$ret = \App\Lib\Applozic::get_group_info('16321318' ); //get groups info of exchange
    //$ret = \App\Lib\Applozic::get_group_info('16321304' ); //get groups info of photo sharing

    //$ret = \App\Lib\Applozic::delete_group('16321304' ); //delete info of photo sharing
    //$ret = \App\Lib\Applozic::delete_group('16321318' ); //delete info of exchange
    //$ret = \App\Lib\Applozic::delete_group('31280121' ); //delete Announcement

    //delete a user from exchange group
    //$ret = \App\Lib\Applozic::add_user_to_group( 34, '16321318', 'D' );

    //add a user to exchange group as role 3
    //$ret = \App\Lib\Applozic::add_user_to_group( 60, '16321318', 'A', 3 );

    //delete a user from Photo Sharing group
    //$ret = \App\Lib\Applozic::add_user_to_group( 34, '16321304', 'D' );

    //add a user to Photo Sharing group as role 3
    //$ret = \App\Lib\Applozic::add_user_to_group( 60, '16321304', 'A', 3 );


//    $ret = App\Lib\Helper::send_sms('6788625954', 'test message');
//    echo "<pre>";
//    var_dump($ret);

});

//Route::get('/create-admin-user', function() {
//    $admin = new App\Model\Admin;
//    $admin->email = 'jun@jjonbp.com';
//    $admin->name = 'Jun';
//    $admin->password = bcrypt('xxxxxxxx1');
//    $admin->status = 'A';
//    $admin->cdate = Carbon\Carbon::now();
//    $admin->save();
//});

Route::get('/test/{appointment_id}', function($appointment_id) {

//    $payload = [
//        'action' => 'survey'
//        ];
//    $message='test push with payload of data on Survey';
//    //Nacho's device ID.
//    //$device_token = 'e-chSb8jRmq8DNZnP_4KAq:APA91bEyahfEecrExA6LIfiBwAGw44la8Gie6pH2NyqsHB9S4yqBvaoSraqOgliQMNLCVS3yGOKqLNcvencL2FQIxN6V7-jy-K8P_BTels2KE68id1IHjHtLM1lOsD4pLMBgPSnUeikP';
//    //Nacho's iphone ID.
//    // $device_token = 'e-chSb8jRmq8DNZnP_4KAq:APA91bEyahfEecrExA6LIfiBwAGw44la8Gie6pH2NyqsHB9S4yqBvaoSraqOgliQMNLCVS3yGOKqLNcvencL2FQIxN6V7-jy-K8P_BTels2KE68id1IHjHtLM1lOsD4pLMBgPSnUeikP';
//    //Jun's android
//    //$device_token = 'cHOrmRPwTv2Q-fQ1oWuxT5:APA91bHp7BaMeHh851GcLfrTHgatjvDcS3QBdGwO6OfE5jNe9wvId1GDzln2Ws9BZgnVtDhv5TdHEMdsCCwR5Gk5QpWY3YhRFykF1qUwX3fCaNp3Qb9iVQ_AqwNUTdQ6FNmc8v7sgGP8';
//    //Jun's iPhone
//    $device_token = 'e0Fg5Vm62UZYozk7LOCJUy:APA91bFcyz2ydAq_birPQNQHnYcU_8s6JZ4GITy67FHSYES11yYWWtCuuGM58ZsukEH6ZLRNvgOY3RJGW9abo1JUAOtCGETWW9Q3iDCR2bg_C5_GmQWY2A9QIjawCBdqIukbG80FGh8g';
//    //Sofia's sofia@groomit.me
//    //$device_token = 'd-onmWJALUA:APA91bHmnCP8fhNxxP-QZ_W5qXeOYneSYmGjfOKxAN5o2FPdrz0_7m3v_bINpRXqDQZXUPwbA_jO4SEtt2vXO4NHhXNppV5hPSrxIhkTeezM7ZphUsaC1bGh0GcAAOuare5zYBGP2p2B';
//    //Tati tatianacagnolo@gmail.com
////    $device_token = 'ek3jTb4oR4KR1gnGFmKGOo:APA91bFc-LPmMiIyqF7DI4rO68shoHUUyHEU8rbgHc4AlWRMsDQYau7TduBwxa60H3nWaYChgLnPs1hfmA1vDZIxvpeMhLHHMvR99VoCE7wZpCZqbZdzEg_grd0nPZPHLTrDnfork1Tp';
////
////
//    $subject = 'Push Title';
//    $error = Helper::send_notification('groomit', $message, $device_token, $subject, $payload);
//    if (!empty($error)) {
//        Helper::send_mail('jun@jjonbp.com', '[Groomit][' . getenv('APP_ENV') . '] Push Notification to User', $message );
//    }

    //echo \Crypt::encrypt('jun@jjonbp.com');
    // Jun Token : eyJpdiI6IkVDVzRHbVpNdThycEtmbVh0YkdXZFE9PSIsInZhbHVlIjoiSG42bEdLTE53bE5wK250dGNmbnppbzllZVB5RmtuNUo2VUFOVFVoN01lND0iLCJtYWMiOiI1MTU3ODJjY2M1YzMwYjUwYzhkMDUyZGYzN2Q0YmEzNWJjN2IyYWEwMjU4NTdkMDFmMzQ4YjVlMjZiOGZlYzk2In0
    //$ret = Converge::get_token('4147202291734604','0622','437', 10954);
    //$ret = Converge::sales('4063224194944604', 2.00,0, 'S');
    //$ret = Converge::void(0, 'S', '080420EBF-457E37F0-F315-41BD-AF84-E4AF7556D01F', 'S', 1.00);

    //$ret = Converge::auth_only('4063224194944604',3,1,'S');
    //array('error_code' => '', 'error_msg' => '', 'void_ref' => '080420A44-9D1ECBA5-A471-4F4C-91B4-C84CF48049C1'
//    $ret = Converge::void(1, 'S', '080420A44-9D1ECBA5-A471-4F4C-91B4-C84CF48049C1', 'A', 1.50);
//
//    echo $ret;
    //4063224194944604
    //Converge::sales : $ret' => array('error_code' => '', 'error_msg' => '', 'void_ref' => '080420EBF-457E37F0-F315-41BD-AF84-E4AF7556D01F')

//    echo substr(Carbon::now()->toIso8601String(),0,19);
//    $appointment = \App\Model\AppointmentList::find( 1272 );
//    AppointmentProcessor::send_groomer_notification2($appointment);

    /*$msg = "Test Body";
    $device_token = "e2eTjZzEdks:APA91bEqBJ5X9mWsrH0c460Pc2BUaiktCUSCThh8-_IaLVPibNcerGJ_kjybtORCOQOLZ2Z144s0rdJIomTRTp2_bn3VspPpyqBloH3aUp88ES84FxvwdfFLo4aoUBQMHeNOSNKKoilJ";
    $title = "Test Title";
    $payload = "test";
    //$res = \App\Lib\Helper::send_notification("", $msg, $device_token, $title, $payload);
    //var_dump($res);*/

    ### 1. auth_only
    /*$token = '4413720997560990';
    $amt = 0.01;
    $ref_id = 9999999;
    $void_ref = '280218A14-BF1FA84D-58BD-4E96-A3B5-14DAA162EE7E';
    $ret = \App\Lib\Converge::auth_only($token, $amt, $ref_id);
    //$ret = \App\Lib\Converge::void($ref_id, 'S', $void_ref, 'A');
    echo "<pre>";
    var_dump($ret);*/

    /*$client_secret_key = 'E3Bhx2dHNFWUvxin5HpNF7AWycMcXy4A1yPOlrum';
    $token_ex_id = '9118090813754290';
    $origin = 'https://www.groomit.me';
    $timestamp = '20180622113939';//Carbon::now()->format('YmdHis');
    $token_scheme = 'sixTOKENfour';

    $string = $token_ex_id . '|' . $origin . '|' . $timestamp . '|' . $token_scheme;

    $sig = hash_hmac('sha256', $string, $client_secret_key);
    echo '<pre>';
    echo 'sig: ' . base64_encode($sig);

    $sig = 'aaa01d4eab7273d3ec2123a245863fb02760babe1542024b271b6781066c34d0';*/

    /*$NumericAddresses = ['100 Baker Street',
        '109 - 111 Wharfside Street',
        '40-42 Parkway',
        '25b-26 Sun Street',
        '43a Garden Walk',
        '6/7 Marine Road',
        '10 - 12 Acacia Ave',
        '4513 3RD STREET CIRCLE WEST',
        '0 1/2 Fifth Avenue',
        '194-03 1/2 50th Avenue'];

    $output = '';
    foreach($NumericAddresses as $o) {
        $building_number = \App\Lib\AddressProcessor::get_building_number($o);
        $output .= $o . ' matched : ' . $building_number . '<br/>';
    }

    echo $output;*/

    /*$photo = \App\Model\PetPhoto::find(507);
    $new_img = \App\Lib\ImageProcessor::optimize($photo->photo);
    //$new_img = $photo->photo;

    //header('Content-Type: image/gif');
    return response($new_img, 200)
        ->header('Content-Type', 'application/octet-stream')
        ->header('Content-Transfer-Encoding', 'binary')
        ->header('Content-Length', strlen($new_img));*/
    /*$app = \App\Model\AppointmentList::find(546);
    $info = \App\Lib\AppointmentProcessor::get_info($app);

    dd($info);*/

/*    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .*/
//      '<txn><ssl_transaction_type>COMPLETE</ssl_transaction_type><ssl_card_number>52**********6278</ssl_card_number><ssl_result>0</ssl_result><ssl_txn_id>130818A42-4C506BAF-BB51-430F-ACBD-E7B131D6F498</ssl_txn_id><ssl_avs_response></ssl_avs_response><ssl_approval_code>736530</ssl_approval_code><ssl_salestax>0.00</ssl_salestax><ssl_amount>32.67</ssl_amount><ssl_txn_time>08/13/2018 01:43:33 PM</ssl_txn_time><ssl_account_balance>0.00</ssl_account_balance><ssl_exp_date>0622</ssl_exp_date><ssl_result_message>APPROVAL</ssl_result_message><ssl_card_short_description>MC</ssl_card_short_description><ssl_customer_code>2453S</ssl_customer_code><ssl_card_type>CREDITCARD</ssl_card_type><ssl_invoice_number>2453S</ssl_invoice_number><ssl_cvv2_response></ssl_cvv2_response></txn>';

    /*$ret = simplexml_load_string($xml);
    //
    echo '<pre>';
    echo ' - ssl_result: ' . $ret->ssl_result . '<br/>';
    echo ' - ssl_avs_response: ' . (string)$ret->ssl_avs_response . '<br/>';
    dd($ret);*/

    /*$ret = json_decode(json_encode(simplexml_load_string($xml)), true);

    echo '<pre>';
    echo ' - ssl_result : ' . $ret['ssl_result'] . '<br/>';
    echo ' - ssl_result : ' . ($ret['ssl_result'] != 0 ? 'failed' : 'success') . '<br/>';
    echo ' - errorCode: ' . (isset($ret['errorCode']) ? $ret['errorCode'] : '?') . '<br/>';
    dd($ret);*/
//
//    $token = 'eyJpdiI6ImtyXC9QcGE5cUhqYkFkalFuaFcxUGtnPT0iLCJ2YWx1ZSI6IklEOHJ0R201Q2FqeDBoWVBKTUZrNzhqdksxSUZpNkJpdklcLzdLWFFRM09zPSIsIm1hYyI6ImI2NzcxMzI0NzFlODVmMmRjM2U2YzYwNjI2MmVjZDgwMDZhNzNjYTA4MDRlZDVhZjY3YjNkY2NjY2ViYWUwMzMifQ';
//    $email = \Illuminate\Support\Facades\Crypt::decrypt($token);
//
//    echo 'email: ' . $email;

    /*
    $photos = \App\Model\UserPhoto::all();

    $html = '<div>';
    foreach ($photos as $o) {
        $html .= '<div>';
        $html .= '<div>' . $o->user_id . '</div>';
        $html .= '<div><img src="data:image/png;base64,' . base64_encode($o->photo) . '"/></div>';
        $html .= '</div>';
    }

    echo $html;
*/
    /*
    $dt1 = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', '2018-04-11 20:19:00');
    $dt2 = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', '2018-04-11 20:00:00');
    $min = $dt2->diffInMinutes($dt1);



    $date_array = explode(" ", '4/11/2018 06:00pm - 08:00pm');
    $reserved_date = new DateTime($date_array[0] . ' ' . '18:00:00');
    $reserved_datetime = $reserved_date->format('l, F j Y, h:i A');
    $reserved_date = $reserved_date->format('Y-m-d H:i:s');

    $now = $dt1;//Carbon::now();
    $r_date = Carbon::parse($reserved_date);
    $time_diff = $r_date->diffInMinutes($now, true);
    $time_diff2 = $r_date->diffInMinutes($now, false);

    echo ' - now : ' . $now . '<br/>';
    echo ' - r_date : ' . $r_date . '<br/>';
    echo ' - diff : ' . $time_diff . '<br/>';
    echo ' - diff2 : ' . $time_diff2 . '<br/>';*/

    /*$response = "{\"multicast_id\":5180454310201715699,\"success\":0,\"failure\":1,\"canonical_ids\":0,\"results\":[{\"error\":\"InvalidRegistration\"}]}";
    $res = json_decode($response);
    //dd($res);
    dd($res->results[0]->error);*/

    //$email = 'jyk2000@gmail.com';
    //$token = \Crypt::encrypt($email);
    //$token = 'eyJpdiI6IjgzaWtpTElSUHhYUzZ3ajNFQURxVGc9PSIsInZhbHVlIjoicjVJK2ZtUlRcL2dlZHJ0NkYxeWxldzEwVXQybVB1SGdGT1JuR2UybnkxVVFDR3lWYW91Q3VrSVlYcXFlWGFqMlQiLCJtYWMiOiJkMjFmODYwNDA3NDdiMjc2MDFkMWU3MjU5ZDQwOWYyNzQxMzJiOTVjNGE0ZDkyYTIxODJkNmVlOTY4NWJlNzA4In0=';
    //$token = \Crypt::decrypt($token);

    //echo $token;

    //$card_number = '4159282222222221';
    //$exp_date = '1219';
    /*$card_number = '4147202247370990';
    $exp_date = '0219';

    $cvv2 = 305;
    $avs_address = '51 Lakeview Ave B';
    $avs_city = 'Leonia';
    $avs_state = 'NJ';
    $avs_zip = '07605';

    $data = [
        'firstname' => '',
        'lastname' => '',
        'street' => '800 Park Ave PH2F',
        'city' => 'Fort Lee',
        'state' => 'NJ',
        'zip' => '07024',
        'cvv' => 305
    ];

    $ret = App\Lib\Converge::get_token($card_number, $exp_date, $cvv2, $avs_address, $avs_zip);
    //$ret = App\Lib\Converge::avs_check($card_number, $exp_date, $avs_address, $avs_city, $avs_state, $avs_zip);
    //$api = new App\Lib\converge_curl();
    //$ret = $api->get_token($card_number, $exp_date, $data);
    //$ret = $api->authorization($card_number, $exp_date, 1, 'Yong K. Jun', 'USD', $data);

    echo "<pre>";
    //echo $api->get_errors();

    //var_dump($ret);*/

    /*$user = App\Model\Admin::where('email', 'design@groomit.me')->first();
    if (!empty($user)) {
        $user->password = bcrypt('aaaa1234');
        $user->save();
        echo "password updated";
    } else {
        echo "user cannot be found";
    }*/
});
//
//Route::get('/test2/{appointment_id}', function($appointment_id) {
//
//    $payload = [
//        'action' => 'links',
//        'url' => "https://wefunder.com/groomit"
//    ];
//
//    $message='test push with payload of data on external link';
//    //Nacho's device ID.
//    //$device_token = 'e-chSb8jRmq8DNZnP_4KAq:APA91bEyahfEecrExA6LIfiBwAGw44la8Gie6pH2NyqsHB9S4yqBvaoSraqOgliQMNLCVS3yGOKqLNcvencL2FQIxN6V7-jy-K8P_BTels2KE68id1IHjHtLM1lOsD4pLMBgPSnUeikP';
//    //Nacho's iPhone
//    //$device_token = 'dem8BbWKAk4AjqbWXETHPH:APA91bHrpLjuPvsIZjrv9-7Z4RNw3IqEbqnZP_iF2hA9Jt5NaWBJ9gJcITdD6TVPRzQvpu_qWmme3A8-0-fPFCkV6b3EBDZSDZN9yj6jzmy9wvWvfI2ZYrErfFdtjpuLR2WKE:APA91bHX_PHrjwGHkhDnxpcsvjEFlkUlpVUMRTD385znAdnQG1ZferX0CZGY5oa4ArLcxJIys1Ud8UejqjyNxLL8vnaRtHIs8PHhjKUk1ikWIsplvG7oLf2MKrJE8vtRHIhrJ1y8zfJ5';
//    //Jun's device ID of Android.
//    //$device_token = 'cHOrmRPwTv2Q-fQ1oWuxT5:APA91bHp7BaMeHh851GcLfrTHgatjvDcS3QBdGwO6OfE5jNe9wvId1GDzln2Ws9BZgnVtDhv5TdHEMdsCCwR5Gk5QpWY3YhRFykF1qUwX3fCaNp3Qb9iVQ_AqwNUTdQ6FNmc8v7sgGP8';
//    //Jun's device ID of iPhone
//    $device_token = 'fFrHztH72kRyss8VGAzzd7:APA91bE08mJSyHvTYPsF0BmfJzfwjj_lARhI0Z1_gKaBDCB2RQJFw1W3NS9-h9-5Uzq4kg2XkNleBDAwqJfzS6WehL7ya-qj2ba3B-DG6WuVEuUC7kISo3iE2ApwtmEedebYP2xRfdJS';
//    //Sofia's sofia@groomit.me
//    //$device_token = 'd-onmWJALUA:APA91bHmnCP8fhNxxP-QZ_W5qXeOYneSYmGjfOKxAN5o2FPdrz0_7m3v_bINpRXqDQZXUPwbA_jO4SEtt2vXO4NHhXNppV5hPSrxIhkTeezM7ZphUsaC1bGh0GcAAOuare5zYBGP2p2B';
//    //Tati tatianacagnolo@gmail.com
////    $device_token = 'ek3jTb4oR4KR1gnGFmKGOo:APA91bFc-LPmMiIyqF7DI4rO68shoHUUyHEU8rbgHc4AlWRMsDQYau7TduBwxa60H3nWaYChgLnPs1hfmA1vDZIxvpeMhLHHMvR99VoCE7wZpCZqbZdzEg_grd0nPZPHLTrDnfork1Tp';
////
////
//    $subject = 'Push Title';
//    $error = Helper::send_notification('groomit', $message, $device_token, $subject, $payload);
//    if (!empty($error)) {
//        Helper::send_mail('jun@jjonbp.com', '[Groomit][' . getenv('APP_ENV') . '] Push Notification to User', $message );
//    }
//
//});
//Route::get('/test5/{appt_id}', function($appt_id){
//    $token = \Crypt::encrypt('jun@jjonbp.com');
//    echo $token;
//});
//
//Route::get('/test4/{appointment_id}', function($appointment_id) {
//    $apps = DB::select("
//                            select  distinct 111 appointment_id, b.user_id, b.email, b.first_name, b.last_name, b.phone, b.device_token
//                            from user b
//                            where b.email = 'jun@jjonbp.com'
//                        ");
//
//    if (!empty($apps) && count($apps) > 0) {
//        $expire_date = Carbon::today()->addDays(365);
//
//        foreach ($apps as $app) {
//            $new_promo_code = 'S' . $app->user_id  . mt_rand(111, 999);;
//
//            try {
//                $promo_code = new PromoCode;
//                $promo_code->code = $new_promo_code;
//                $promo_code->type = 'N';
//                $promo_code->amt_type = 'A';
//                $promo_code->amt = 20; //$20
//                $promo_code->status = 'A';
//                $promo_code->first_only = 'N';
//                $promo_code->expire_date = $expire_date;
//                $promo_code->valid_user_ids = $app->user_id;
//                $promo_code->no_insurance = 'N';
//                $promo_code->include_tax = 'N';
//                $promo_code->cdate = Carbon::now();
//                $promo_code->created_by = 19; //SystemAdmin
//                $promo_code->note = 'SPOOKY Promotion[' . $app->appointment_id . "]";
//                $promo_code->save();
//
//                //Emails.
//
//                $data = [];
//                $data['promo_code'] = $new_promo_code;
//                $data['name']   = $app->first_name . ' ' . $app->last_name ;
//
//                $data['email']  = $app->email;
//                $data['subject'] = 'You have received a Giftcard code from Groomit';
//                $data['bcc']  = 'jun@jjonbp.com';
//                            Helper::log('##### EMAIL DATA #####', [
//                                'data' => $data
//                            ]);
//
//                $pu = new PromoCodeUsers();
//                $pu->promo_code = $new_promo_code;
//                $pu->user_id = $app->user_id;
//                $pu->cdate = Carbon::now();
//                $pu->save();
//
//                Helper::send_html_mail('vouchers.spooky', $data);
//
//            }catch (\Exception $ex) {
//                $msg = "[" .$app->appointment_id . "]" . $ex->getCode() ;
//
//                Helper::send_mail('jun@jjonbp.com', '[GROOMIT.ME][' . getenv('APP_ENV') . '] SPOOKY Job Error', $msg);
//            }
//
//
//
//
//        }
//    }
//
//});
Route::group(['prefix' => 'demo'], function() {
    Route::get('index', function() {
        return view('demo.index');
    });

    Route::get('join-us', function() {
        return view('demo.join-us');
    });

    Route::get('login', function() {
        return view('demo.login');
    });
});


##### EMAIL TEMPLATES : Temp. routes #######
//

Route::get('/emails/terms', function() {
    $data = [];

    return view('emails/terms', ['data' =>$data]);
});
//Route::get('/emails/survey', function() {
//    $data = [];
//
//    return view('emails/survey-email', ['data' =>$data]);
//});
//Route::get('/emails/welcome', function() {
//    $data = [];
//    $data['email'] = 'aaaaa@bbbbb.com';
//    $data['referral_code'] = 'AAAA1234';
//    $data['referral_amount'] = env('REFERRAL_CODE_AMT');
//    $data['name'] = 'User Name';
//    $data['subject'] = "Welcome to GROOMIT";
//
//    return view('emails/welcome', ['data' =>$data]);
//});
////
//Route::get('/emails/forgot_password', function() {
//    $data = [];
//    $data['email'] = 'aaaaa@bbbbb.com';
//    $data['name'] = 'User Name';
//    $data['referral_code'] = 'AAAA1234';
//    $data['subject'] = "[Groomit] Your forgot password temporary key";
//    $data['temp_key'] = 'Temp. Key';
//
//    return view('emails/forgot_password', ['data' =>$data]);
//});
//
//Route::get('/emails/new-groomer', function() {
//    $data = [];
//    $data['email'] = 'aaaa@bbbbb.com';
//    $data['name'] = 'User Name';
//    $data['message'] = 'Thank you for your interest to work with Groomit. We will be in touch with you very soon.';
//    $data['subject'] = "Thank you for your interest to work with Groomit. We will be in touch with you very soon.";
//
//    return view('emails/groomer/new-groomer', ['data' =>$data]);
//});
//
////
//Route::get('/emails/new_appointment', function() {
//    $data = [];
//    $data['email'] = 'aaaaa@bbbbb.com';
//    $data['name'] = 'User Name';
//    $data['subject'] = "Your Groomit Appointment has been sent.";
//    $data['groomer'] = 'Groomer name';
//    $data['address'] = 'Address';
//    $data['referral_code'] = 'AAAA1234';
//    $data['pet'][0]['pet_name'] = 'Pet Name';
//    $data['pet'][0]['package_name'] = 'Package name';
//    $data['reserved_date'] = 'Reserved Date';
//
//    return view('emails/new_appointment', ['data' =>$data]);
//});
//
////
//Route::get('/emails/appointment_confirmation', function() {
//    $data = [];
//    $data['email'] = 'aaaaa@bbbbb.com';
//    $data['name'] = 'User Name';
//    $data['subject'] = "Your Groomit Appointment has been confirmed";
//    $data['groomer'] = 'Groomer name';
//    $data['address'] = 'Address here';
//    $data['referral_code'] = 'AAAA1234';
//    $data['pet'][0]['pet_name'] = 'Pet Name';
//    $data['pet'][0]['package_name'] = 'Package name';
//    $data['accepted_date'] = 'Accepted Date';
//
//    return view('emails/appointment_confirmation', ['data' =>$data]);
//});
//
//
//Route::get('/emails/groomer_assigned_for_groomer', function() {
//    $data = [];
//    $data['email'] = 'aaaaa@bbbbb.com';
//    $data['name'] = 'Groomer Name';
//    $data['subject'] = "You have an assigned Groomit schedule.";
//    $data['groomer'] = 'Groomer name';
//    $data['address'] = 'Address';
//    $data['referral_code'] = 'AAAA1234';
//    $data['pet'][0]['pet_name'] = 'Pet Name';
//    $data['pet'][0]['package_name'] = 'Package name';
//    $data['pet'][0]['breed_name'] = 'Breed';
//    $data['pet'][0]['size_name'] = 'Size';
//    $data['accepted_date'] = 'Accepted Date';
//
//    return view('emails/groomer_assigned_for_groomer', ['data' =>$data]);
//});
//
//
//Route::get('/emails/reminder', function() {
//    $data = [];
//    $data['email'] = 'aaaaa@bbbbb.com';
//    $data['name'] = 'User Name';
//    $data['subject'] = "You have an upcoming Groomit Appointment tomorrow.";
//    $data['groomer'] = 'Groomer name';
//    $data['address'] = 'Address';
//    $data['referral_code'] = 'AAAA1234';
//    $data['reminder_type'] = "tomorrow";
//    $data['pet'][0]['pet_name'] = 'Pet Name';
//    $data['pet'][0]['package_name'] = 'Package name';
//    $data['accepted_date'] = 'Accepted Date';
//
//    return view('emails/reminder', ['data' =>$data]);
//});
//
//
//Route::get('/emails/reminder_for_groomer', function() {
//    $data = [];
//    $data['email'] = 'aaaaa@bbbbb.com';
//    $data['name'] = 'User Name';
//    $data['subject'] = "You have an upcoming Groomit Appointment tomorrow.";
//    $data['groomer'] = 'Groomer name';
//    $data['address'] = 'Address';
//    $data['referral_code'] = 'AAAA1234';
//    $data['reminder_type'] = "tomorrow";
//    $data['pet'][0]['pet_name'] = 'Pet Name';
//    $data['pet'][0]['package_name'] = 'Package name';
//    $data['accepted_date'] = 'Accepted Date';
//
//    return view('emails/reminder_for_groomer', ['data' =>$data]);
//});
//
//
//Route::get('/emails/appointment_cancelled_for_groomer', function() {
//    $data = [];
//    $data['email'] = 'aaaaa@bbbbb.com';
//    $data['name'] = 'Groomer Name';
//    $data['subject'] = "Your scheduled appointment has been cancelled";
//    $data['address'] = 'Address';
//    $data['referral_code'] = 'AAAA1234';
//    $data['reminder_type'] = "tomorrow";
//    $data['pet'][0]['pet_name'] = 'Pet Name';
//    $data['pet'][0]['package_name'] = 'PAckage name';
//    $data['accepted_date'] = 'Accepted Date';
//    $data['groomer'] = 'Groomer Name';
//
//    return view('emails/appointment_cancelled_for_groomer', ['data' =>$data]);
//});
//
//Route::get('/emails/appointment_cancelled', function() {
//    $data = [];
//    $data['email'] = 'aaaaa@bbbbb.com';
//    $data['name'] = 'Groomer Name';
//    $data['subject'] = "Your scheduled appointment has been cancelled";
//    $data['address'] = 'Address';
//    $data['referral_code'] = 'AAAA1234';
//    $data['reminder_type'] = "tomorrow";
//    $data['pet'][0]['pet_name'] = 'Pet Name';
//    $data['pet'][0]['package_name'] = 'PAckage name';
//    $data['accepted_date'] = 'Accepted Date';
//    $data['groomer'] = 'Groomer Name';
//
//    return view('emails/appointment_cancelled', ['data' =>$data]);
//});
//
//Route::get('/emails/service_completion', function() {
//
//    $data = [];
//    $data['appointment_id'] = 'Appointment ID';
//    $data['email'] = 'aaaaa@bbbbb.com';
//    $data['name'] = 'Email Test';
//    $data['subject'] = "Your Groomit Appointment has been completed";
//    $data['groomer'] = 'Groomer name';
//    $data['card_holder'] = 'Card holder';
//    $data['card_type'] = 'Visa';
//    $data['card_number'] = '1111';
//    $data['safety_insurance'] = '4.00';
//    $data['referral_code'] = 'AAAA1234';
//    $data['tax'] = '10.00';
//    $data['sub_total'] = '120.00';
//    $data['total'] = '109.00';
//    $data['payment_date'] = 'Payment Date';
//    $data['accepted_date'] = 'Accepted Date';
//    $data['promo_code'] = 'PROMOCODE';
//    $data['promo_amt'] = '25.00';
//    $data['credit_amt'] = '25.00';
//    $data['address'] = 'test address';
//
//    $data['pet'][0]['pet_name'] = 'Pet Name';
//    $data['pet'][0]['package_name'] = 'Package Name';
//    $data['pet'][0]['sub_total'] = '120.00';
//    $data['pet'][1]['pet_name'] = 'Second Pet';
//    $data['pet'][1]['package_name'] = 'Package Name';
//    $data['pet'][1]['sub_total'] = '120.00';
//    $data['pet'][2]['pet_name'] = 'Third Pet';
//    $data['pet'][2]['package_name'] = 'Package Name';
//    $data['pet'][2]['sub_total'] = '120.00';
//
//
//    return view('emails/service_completion', ['data' =>$data]);
//});
//
//
//Route::get('/emails/payment_failure', function() {
//    $data = [];
//    $data['email'] = 'aaaaa@bbbbb.com';
//    $data['name'] = 'User Name';
//    $data['referral_code'] = 'AAAA1234';
//    $data['subject'] = "Your payment was unsuccessful. Please update your payment method within the App.";
//    return view('emails/payment_failure', ['data' =>$data]);
//});
//
//Route::get('/emails/tip_failure', function() {
//    $data = [];
//    $data['email'] = 'aaaaa@bbbbb.com';
//    $data['name'] = 'User Name';
//    $data['subject'] = "Your tip payment was unsuccessful. Please update your payment method within the App.";
//    $data['appointment_id'] = 'Appointment ID';
//    $data['card_holder'] = 'Card Holder Name';
//    $data['card_number'] = '1111';
//    $data['address'] = 'Address';
//    $data['pet'][0]['pet_name'] = 'Pet Name';
//    $data['pet'][0]['package_name'] = 'Package name';
//    $data['tip'] = 30;
//    $data['total'] = 124.68;
//    $data['groomer'] = 'Groomer Name';
//    $data['accepted_date'] = 'Accepted Date';
//    $data['referral_code'] = 'DKFEWA';
//    $data['referral_amount'] =  env('REFERRAL_CODE_AMT');
//
//    return view('emails/tip_failure', ['data' =>$data]);
//});
//
//Route::get('/emails/tip_success', function() {
//    $data = [];
//    $data['email'] = 'aaaaa@bbbbb.com';
//    $data['name'] = 'User Name';
//    $data['subject'] = "Your tip payment was successful. The whole amount will be transferred  to the groomer.";
//    $data['appointment_id'] = 'Appointment ID';
//    $data['card_holder'] = 'Card Holder Name';
//    $data['card_number'] = '1111';
//    $data['address'] = 'Address';
//    $data['pet'][0]['pet_name'] = 'Pet Name';
//    $data['pet'][0]['package_name'] = 'Package name';
//    $data['tip'] = 30;
//    $data['total'] = 124.68;
//    $data['groomer'] = 'Groomer Name';
//    $data['accepted_date'] = 'Accepted Date';
//
//    $data['payment_date'] = 'Payment date';
//    $data['referral_code'] = 'DKFEWA';
//    $data['referral_amount'] =  env('REFERRAL_CODE_AMT');
//
//    return view('emails/tip_success', ['data' =>$data]);
//});
//
//
//Route::get('/emails/contact_reply', function() {
//    $data = [];
//    $data['email'] = 'aaaaa@bbbbb.com';
//    $data['name'] = 'User Name';
//    $data['subject'] = "Re: contact subject" ;
//    $data['message'] = 'Reply Message';
//    $data['accepted_date'] = 'Accepted Date';
//    $data['referral_code'] = 'DKFEWA';
//    $data['referral_amount'] =  env('REFERRAL_CODE_AMT');
//    return view('emails/contact_reply', ['data' =>$data]);
//});
//
//Route::get('/emails/groomer/forgot-password', function() {
//    $data = [];
//    $data['temp_key'] = 'temp_key_dummy';
//    return view('emails/groomer/forgot-password', [
//      'data' => $data
//    ]);
//});
//
//Route::get('/emails/groomer_on_the_way_for_groomer', function() {
//    $data = [];
//    $data['address'] = 'Address 1, Newyork NY 10032';
//    $data['accepted_date'] = '2019-02-19 13:00:00';
//    $data['pet'] = [];
//    $data['pet'][] = [
//        'pet_name' => 'Pet Name',
//      'breed_name' => 'Breed Name',
//      'size_name' => 'Small',
//      'age' => 'age3',
//      'note' => 'note',
//      'package_name' => 'package_name',
//      'shampoo' => 'shampoo',
//      'addon' => 'addon'
//      ];
//    return view('emails/groomer_on_the_way_for_groomer', [
//      'data' => $data
//    ]);
//});
//
//Route::get('/emails/groomer_on_the_way', function() {
//    $data = [];
//    $data['address'] = 'Address 1, Newyork NY 10032';
//    $data['referral_code'] = '2131212';
//    $data['referral_amount'] =  env('REFERRAL_CODE_AMT');
//    $data['accepted_date'] = '2019-02-19 13:00:00';
//    $data['groomer'] = 'Groomer Name';
//    $data['pet'] = [];
//    $data['pet'][] = [
//        'pet_name' => 'Pet Name',
//        'breed_name' => 'Breed Name',
//        'size_name' => 'Small',
//        'age' => 'age3',
//        'note' => 'note',
//        'package_name' => 'package_name',
//        'shampoo' => 'shampoo',
//        'addon' => 'addon'
//    ];
//    return view('emails/groomer_on_the_way', [
//        'data' => $data
//    ]);
//});
//
//
//
//Route::get('/emails/affiliate_forgot_password', function() {
//    $data = [];
//    $data['temp_key'] = 'temp_key_dummy';
//    return view('emails/affiliate_forgot_password', [
//      'data' => $data
//    ]);
//});
//
//Route::get('/emails/spaw_affiliate_forgot_password', function() {
//    $data = [];
//    $data['temp_key'] = 'temp_key_dummy';
//    return view('emails/spaw_affiliate_forgot_password', [
//        'data' => $data
//    ]);
//});

//Route::get('/emails/vouchers/spooky', function() {
//    $data = [];
//    $data['name'] = 'Jun Seo';
//    //$data['image'] = '/desktop/img/vouchers/voucher-100.png';
//    //$data['sender'] = 'Rocket';
//    //$data['message'] = 'Good Day !!';
//    $data['promo_code'] = 'S12345';
//    return view('emails/vouchers/spooky', [
//      'data' => $data
//    ]);
//});
//
//Route::get('/emails/vouchers/voucher-gift-receipt', function() {
//    $data = [];
//    $data['name'] = 'Tomas';
//    $data['image'] = '/desktop/img/vouchers/voucher-100.png';
//    $data['sender'] = 'Rocket';
//    $data['message'] = 'Good Day !!';
//    $data['promo_code'] = 'GJIEFJIEY';
//    return view('emails/vouchers/voucher-gift-receipt', [
//      'data' => $data
//    ]);
//});
//
//Route::get('/emails/vouchers/voucher-receipt', function() {
//    $data = [];
//    $data['name'] = 'Tomas';
//    $data['image'] = '/desktop/img/vouchers/voucher-100.png';
//    $data['sender'] = 'Rocket';
//    $data['message'] = 'Good Day !!';
//    $data['promo_code'] = 'GJIEFJIEY';
//    return view('emails/vouchers/voucher-receipt', [
//      'data' => $data
//    ]);
//});

//Route::get('/emails/groomer/groomer-supplies-shipped', function() {
//    $data = [];
//    $data['name'] = 'Tomas';
//    $data['image'] = '/desktop/img/vouchers/voucher-100.png';
//    $data['sender'] = 'Rocket';
//    $data['message'] = 'Good Day !!';
//    $data['promo_code'] = 'GJIEFJIEY';
//    return view('emails/groomer/groomer-supplies-shipped', [
//        'data' => $data
//    ]);
//});

## Please switch template_name to real template name.
Route::get('/emails/template_name', function() {
    return view('emails/template_name');
});
