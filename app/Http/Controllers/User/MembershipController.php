<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/14/18
 * Time: 3:54 PM
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Lib\PaymentProcessor;
use App\Lib\Converge;
use App\Model\Message;
use App\Model\UserBilling;
use App\Model\Giftcard;
use App\Model\GiftcardSales;
use App\Model\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MembershipController extends Controller
{

    public function show(Request $request) {
        if (Auth::guard('user')->check()) {
            Session::put('user.menu.show', 'Y');
        } else {
            Session::put('user.menu.show', 'N');
        }

        Session::put('schedule.url', $request->path());

        $giftcards = Giftcard::whereIn('status', ['A', 'P'])->where('type','S')->orderBy('amt', 'asc')->get();

        return view('user.memberships', [
            'giftcards' => $giftcards
        ]);
    }

    public function buy(Request $request) {
        $payments = UserBilling::where('user_id', Auth::guard('user')->user()->user_id)
            ->where('status', 'A')
            ->get();
        $payment = ScheduleProcessor::getPayment();
        if (empty($payment)) {
            if (count($payments) > 0) {
                foreach ($payments as $o) {
                    if ($o->default_card == 'Y') {
                        ScheduleProcessor::setPayment($o);
                        break;
                    }
                }
            }
        }

        return view('user.memberships.buy', [
            'payments'  => $payments,
            'payment'   => $payment
        ]);
    }

    public function payment(Request $request) {

        $voucher = Giftcard::find($request->voucher_id);

        $years  = Helper::get_expire_years();
        $months = Helper::get_expire_months();
        $states = Helper::get_states();

        return view('user.memberships.payment')->with([
            'years'     => $years,
            'months'    => $months,
            'states'    => $states,
            'voucher'   => $voucher
        ]);
    }

    public function buy_process(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'voucher_id' => 'required',
                'is_gift' => 'required',
                'recipient_location' => 'required_if:is_gift,Y',
                'recipient_name'    => 'required_if:is_gift,Y',
                'recipient_email'   => 'required_if:is_gift,Y',
                'card_holder'   => 'required',
                'card_number'   => 'required',
                'expire_mm'     => 'required',
                'expire_yy'     => 'required',
                'cvv'           => 'required',
                'zip'           => 'required',
                'address1'      => 'required',
                'city'          => 'required',
                'state'         => 'required',
                'default_card'  => 'required',
            ], [
                'recipient_location.required_if' => 'Recipient location is required',
                'recipient_name.required_if'    => 'Recipient name is required',
                'recipient_email.required_if'   => 'Recipient email is required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "<br/>") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $user = Auth::guard('user')->user();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Your session has expired. Please login again. [USR_ADDBL]'
                ]);
            }

            $giftcard = Giftcard::find($request->voucher_id);
            if (empty($giftcard) || $giftcard->status != 'A' || $giftcard->type != 'S') {
                return response()->json([
                    'msg' => 'The voucher card is not available. [GC-NIL]'
                ]);
            }

            $code = PromoCode::generate_code_for_subscription($giftcard->amt, $user->user_id, $giftcard->id );

            $sales = new GiftcardSales();
            $sales->giftcard_id = $giftcard->id;
            $sales->amt         = $giftcard->amt;
            $sales->cost        = $giftcard->cost;
            $sales->promo_code  = $code;
            $sales->is_gift     = $request->is_gift;
            $sales->sender      = empty($request->sender) ? $user->first_name : $request->sender;
            $sales->recipient_location = $request->recipient_location;
            $sales->recipient_name = $request->recipient_name;
            $sales->recipient_email = $request->recipient_email;
            $sales->voucher_message = $request->voucher_message;
            $sales->status      = 'N';
            $sales->cdate       = Carbon::now();
            $sales->created_by  = $user->user_id;
            $sales->save();

            ########################################
            ### PAYMENT PROCESS ###
            ########################################
            ### ADD CARD
            $ret = PaymentProcessor::add_card(
                $user->user_id,
                $request->card_holder,
                $request->card_number,
                $request->expire_mm,
                $request->expire_yy,
                $request->cvv,
                $request->address1,
                $request->address2,
                $request->city,
                $request->state,
                $request->zip,
                $request->default_card
            );

            if (!empty($ret['msg'])) {
                if ($ret['code'] != 1) {
                    $sales->status = 'F';
                    $sales->mdate = Carbon::now();
                    $sales->update();

                    Helper::send_mail('tech@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - Memberships sales : ' . $sales->id . '<br> - error : ' . $ret['error_msg']);
                    Helper::send_mail('help@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - Memberships sales : ' . $sales->id . '<br> - error : ' . $ret['error_msg']);

                    return response()->json([
                        'msg' => $ret['msg']
                    ]);
                }
            }

            $card = $ret['card'];

            ### SALES
            // sales($token, $amt, $ref_id, $category = 'S')
            $ret = Converge::voucher_sales($card->card_token, $sales->cost, $sales->id, 'S');

            if (!empty($ret['error_msg'])) {
                $sales->status = 'F';
                $sales->mdate = Carbon::now();
                $sales->update();

                Helper::send_mail('tech@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - Memberships sales : ' . $sales->id . '<br> - error : ' . $ret['error_msg']);
                Helper::send_mail('help@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - Memberships sales : ' . $sales->id . '<br> - error : ' . $ret['error_msg']);

                $msg = 'Voucher sales processing failed : ' . $ret['error_msg'] . ' [' . $ret['error_code'] . ']';
                throw new \Exception($msg, -5);
                //return Redirect::route('admin.appointment', array('id' => $request->id))->with('alert', $msg);
            }

            $sales->status = 'S';
            $sales->mdate = Carbon::now();
            $sales->update();

            ### SEND EMAIL
            $data = [];
            $data['promo_code'] = $sales->promo_code;
            $data['image']      = $giftcard->image;
            $data['message']    = $sales->voucher_message;
            $data['sender']     = $sales->sender;
            $data['name']   = $sales->recipient_name;

            if ($sales->is_gift == 'Y') {
                $data['email']  = $sales->recipient_email;
                $data['subject'] = 'You have received a Groomit Gift Card';

                Helper::log('##### EMAIL DATA #####', [
                    'data' => $data
                ]);

                Helper::send_html_mail('vouchers.membership-gift', $data);

                $data['email']  = $user->email;
                $data['subject'] = 'Thank you for your order, the Groomit Membership Code was sent to your recipient';

                Helper::send_html_mail('vouchers.membership-gift-receipt', $data);

                ## send SMS to User ##
                if (!empty($user->phone)) {
                    $user_message = 'The Membership code was successfully sent to ' . $data['name'];
                    $ret = Helper::send_sms($user->phone, $user_message);
                    if (!empty($ret)) {
                        //throw new \Exception('Groomer SMS Error: ' . $ret);
                        Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $user_message . '/ Membership Sales ID:' . $sales->id);
                    }

                    Message::save_sms_to_user($user_message, $user, null);
                }
            } else {

                $data['name']   = $user->first_name;
                $data['email']  = $user->email;
                $data['subject'] = 'Thank you for your order, here is your Groomit Membership Code';

                Helper::log('##### EMAIL DATA #####', [
                    'data' => $data
                ]);

                Helper::send_html_mail('vouchers.membership-receipt', $data);
            }

            $data['email']  = 'it@jjonbp.com';
            $data['subject'] = '[Membership] Order by [' . $user->user_id . '] ' . $user->email . ' !! $' . $sales->amt;

            Helper::send_html_mail('vouchers.membership-gift-receipt', $data);

            return response()->json([
                'msg' => '',
                'sales_id' => $sales->id
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

}