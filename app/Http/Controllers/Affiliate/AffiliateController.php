<?php

namespace App\Http\Controllers\Affiliate;

use App\Model\Affiliate;
use App\Model\AffiliateCode;
use App\Model\AffiliateRedeemHistory;
use App\Model\AppointmentList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Log;
use Validator;
use Redirect;
use App\Lib\Helper;

class AffiliateController extends Controller
{

    public function index() {

        if (Auth::guard('affiliate')->check()) {
            return Redirect::route('affiliate.earnings');
        } else {
            return view('affiliate.affiliates');
        }

    }

    public function earnings() {
        try {

            if ($auth = Auth::guard('affiliate')->user()) {
                $aff_id = $auth->aff_id;
            } else {
                return Redirect::route('affiliate.login');
            }

            $earned_amt = 0;
            $redeemed_amt = 0;
            $data = AffiliateRedeemHistory::where('aff_id', $aff_id)->where('status', '<>', 'C')->get();
            if (!empty($data)) {
                foreach($data as $d) {
                    $earned_amt += $d->amount;

                    if ($d->status == 'P') {
                        $redeemed_amt += $d->amount;
                    }
                }
            }
            $earnings = $earned_amt - $redeemed_amt;

            $acct = Affiliate::findOrFail($aff_id);

            return view('affiliate.earnings', [
                'msg' => '',
                'earnings' => $earnings,
                'data' => $acct
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function withdraw (Request $request) {

        try {
            if ($auth = Auth::guard('affiliate')->user()) {
                $aff_id = $auth->aff_id;
            } else {
                return Redirect::route('affiliate.login');
            }

            $v = Validator::make($request->all(), [
                'amt' => 'required',
                'type' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            if ($request->amt < 100) {
                return response()->json([
                    'msg' => 'Minimum withdraw amount is $100.00'
                ]);
            }

            $earnings = AffiliateRedeemHistory::earnings($aff_id);

            if ($request->amt > $earnings) {
                return response()->json([
                    'msg' => 'Withdraw amount cannot exceed your earning amount!'
                ]);
            }

            $new_redeem = new AffiliateRedeemHistory;
            $new_redeem->aff_id = $aff_id;
            $new_redeem->amount = $request->amt;
            $new_redeem->type = $request->type;
            $new_redeem->status = 'N';
            $new_redeem->redeemed_by = $aff_id;
            $new_redeem->save();

            # send email
            $subject = '[Groomit][' . getenv('APP_ENV') . '][Affiliate ID: ' . $aff_id . '] Affiliate Withdraw Request: $' . $request->amt;
            $message = "* Affiliate Withdraw Request \n\n";
            $message .= "Name: " . $auth->first_name . " " . $auth->last_name . "\n";
            if ($auth->bhusiness_name) {
                $message .= "Business: " . $auth->bhusiness_name . "\n";
            }
            $message .= "Email: " . $auth->email . "\n\n";
            $message .= "Amount: $" . $request->amt . "\n";
            $message .= "By: " . $new_redeem->type_name() . "\n";
            $message .= "Request Date: " . date("m/d/Y h:i:s A");

            if (getenv('APP_ENV') == 'DEMO') {
                Helper::send_mail('it@jjonbp.com', $subject, $message);
            } else {
                Helper::send_mail('tech@groomit.me', $subject, $message);
            }

            Helper::send_mail('tech@groomit.me', $subject, $message);

            return response()->json([
                'msg' => ''
            ]);


        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function promo_code () {

        try {
            if ($auth = Auth::guard('affiliate')->user()) {
                $aff_id = $auth->aff_id;
            } else {
                return Redirect::route('affiliate.login');
            }

            $data = AffiliateCode::where('aff_id', $aff_id)->orderBy('cdate', 'ASC')->get();

            foreach($data as $d) {

                $code = strtoupper($d->aff_code);
                $d->cnt = 0;
                $d->commission = 0;

                $a = AppointmentList::whereRaw("promo_code = '$code'")
                    ->where('status','P')
                    ->selectRaw("COUNT(*) as cnt")
                    ->first();

                if (!empty($a)) {
                    if ($a->cnt > 0) {
                        $d->cnt = $a->cnt;
                        $d->commission = $a->cnt * 25; // for now, each appointment $25
                    }
                }
            }

            return view('affiliate.promo-code', [
                'msg' => '',
                'data' => $data,
                'user' => $auth
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function create_promo_code () {

        try {
            if ($auth = Auth::guard('affiliate')->user()) {
                $aff_id = $auth->aff_id;
            } else {
                return Redirect::route('affiliate.login');
            }

            if (AffiliateCode::newAffiliateCode($aff_id)) {
                $msg = 'New Promo Code was created successfully!';
            } else {
                $msg = 'Failed to create New Promo Code.';
            }

            return Redirect::route('affiliate.promo-code')->with('alert', $msg);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function create_custom_promo_code (Request $request) {

        try {
            if ($auth = Auth::guard('affiliate')->user()) {
                $aff_id = $auth->aff_id;
            } else {
                return Redirect::route('affiliate.login');
            }

            if (empty($request->custom_code)) {
                return response()->json([
                    'msg' => 'Please Type a New Code'
                ]);
            }

            if (AffiliateCode::newCustomAffiliateCode($aff_id, $request->custom_code)) {
                $msg = 'New Custom Promo Code was created successfully!';
            } else {
                $msg = 'Failed to create New Promo Code.';
            }

            return Redirect::route('affiliate.promo-code')->with('alert', $msg);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function contact_us () {

        try {
            if ($auth = Auth::guard('affiliate')->user()) {
                $user = $auth;
            } else {
                return Redirect::route('affiliate.login');
            }

            return view('affiliate.contact-us', [
                'msg' => '',
                'data' => $user
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function send_contact_us (Request $request) {

        try {
            if ($auth = Auth::guard('affiliate')->user()) {
                $aff_id = $auth->aff_id;
            } else {
                return Redirect::route('affiliate.login');
            }


            $v = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
                'subject' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $subject = '[Groomit][' . getenv('APP_ENV') . '][Affiliate ID: ' . $aff_id . '] Affiliate Contact Us:' . $request->subject;
            $message = "Subject: " . $request->subject . "\n\n";
            $message .= "Name: " . $request->first_name . " " . $request->last_name . "\n";
            $message .= "Email: " . $request->email . "\n";
            $message .= "Message: " . $request->message;


            if (getenv('APP_ENV') == 'DEMO') {
                Helper::send_mail('it@jjonbp.com', $subject, $message);
            } else {
                Helper::send_mail('lars@groomit.me', $subject, $message);
            }

            Helper::send_mail('tech@groomit.me', $subject, $message);

            return Redirect::route('affiliate.contact-us')->with('alert', 'Your message was sent successfully!');

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }


    public function my_account () {

        try {
            if ($auth = Auth::guard('affiliate')->user()) {
                $aff_id = $auth->aff_id;
            } else {
                return Redirect::route('affiliate.login');
            }

            $acct = Affiliate::where('aff_id', $aff_id)->first();


            return view('affiliate.my-account', [
                'msg' => '',
                'acct' => $acct
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }


    public function update_my_account (Request $request) {

        try {

            if ($auth = Auth::guard('affiliate')->user()) {
                $aff_id = $auth->aff_id;
            } else {
                return Redirect::route('affiliate.login');
            }

            $acct = Affiliate::where('aff_id', $aff_id)->first();

            switch ($request->type) {

                case "account":
                    $v = Validator::make($request->all(), [
                        'business_name' => 'required',
                        'first_name' => 'required',
                        'last_name' => 'required',
                        'email' => 'required|email',
                        'password' => 'required|confirmed',
                        'type' => 'required'
                    ]);

                    if ($v->fails()) {
                        $msg = '';
                        foreach ($v->messages()->toArray() as $k => $v) {
                            $msg .= (empty($msg) ? '' : "|") . $v[0];
                        }

                        return Redirect::route('affiliate.my-account')->with('error', $msg);
                    }


                    $chk_email = Affiliate::whereRaw("lower(email) = '". strtolower($request->email) ."'")
                        ->where('aff_id', '<>', $aff_id)->first();
                    if (!empty($chk_email)) {
                        return Redirect::route('affiliate.my-account')->with('error', 'Duplicated email.');
                    }

                    $acct->first_name = $request->first_name;
                    $acct->last_name = $request->last_name;
                    $acct->business_name = $request->business_name;
                    $acct->email = $request->email;
                    $acct->password = bcrypt($request->password);

                    break;
                case "address":
                    $v = Validator::make($request->all(), [
                        'phone' => 'required|digits:10',
                        'address' => 'required',
                        'city' => 'required',
                        'state' => 'required',
                        'zip' => 'required|digits:5'
                    ]);

                    if ($v->fails()) {
                        $msg = '';
                        foreach ($v->messages()->toArray() as $k => $v) {
                            $msg .= (empty($msg) ? '' : "|") . $v[0];
                        }

                        return Redirect::route('affiliate.my-account')->with('error', $msg);
                    }

                    $acct->phone = $request->phone;
                    $acct->address = $request->address;
                    $acct->address2 = $request->address2;
                    $acct->city = $request->city;
                    $acct->state = $request->state;
                    $acct->zip = $request->zip;

                    break;

                case "bank":

                    $v = Validator::make($request->all(), [
                        'bank_name' => 'required',
                        'bank_account_number' => 'required',
                        'routing_number' => 'required'
                    ]);

                    if ($v->fails()) {
                        $msg = '';
                        foreach ($v->messages()->toArray() as $k => $v) {
                            $msg .= (empty($msg) ? '' : "|") . $v[0];
                        }

                        return Redirect::route('affiliate.my-account')->with('error', $msg);
                    }

                    $acct->bank_name = $request->bank_name;
                    $acct->bank_account_number = $request->bank_account_number;
                    $acct->routing_number = $request->routing_number;
                    break;

                default:
                    break;
            }

            $acct->save();

            return Redirect::route('affiliate.my-account')->with('alert', 'Your account information was updated successfully!');


        } catch (\Exception $ex) {
            return Redirect::route('affiliate.my-account')->with('error', 'Failed to update your account information.'.$ex->getMessage() . ' [' . $ex->getCode() . ']');
        }

    }
}
