<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 6/14/18
 * Time: 4:01 PM
 */

namespace App\Lib;


use App\Model\AppointmentList;
use App\Model\UserBilling;
use Carbon\Carbon;

class PaymentProcessor
{

    public static function add_card(
        $user_id,
        $card_holder,
        $card_number,
        $expire_mm,
        $expire_yy,
        $cvv,
        $address1,
        $address2,
        $city,
        $state,
        $zip,
        $default_card
    ) {
        ### Duplication Check ###
        $user_billing = UserBilling::whereRaw('RIGHT(card_number,4) = \''.substr($card_number, -4).'\'')
            ->where('user_id', $user_id)
            ->where('status', 'A')
            ->first();

        if (!empty($user_billing)) {
            return [
                'code'  => '1',
                'msg'   => 'The same credit card already exists on this User.',
                'card'  => $user_billing
            ];
        }

        ### Token generation ###
        $ret = Converge::get_token(
            $card_number,
            $expire_mm . $expire_yy,
            $cvv,
//                $request->address1 . ' ' . $request->address2,
//                $request->city,
//                $request->state,
            $zip
        );

        if (!empty($ret['error_msg'])) {
            return [
                'code' => '-1',
                'msg' =>   'Credit card verification failed: ' . $ret['error_msg'] . ' [' . $ret['error_code'] . ']'
            ];
        }

        $user_billing = new UserBilling;
        $user_billing->user_id = $user_id;
        //$user_billing->card_type = $request->card_type;
        $user_billing->card_number = $ret['card_number'];
        $user_billing->card_holder = $card_holder;
        $user_billing->expire_mm = $expire_mm;
        $user_billing->expire_yy = $expire_yy;
        //$user_billing->cvv = $request->cvv;
        $user_billing->cvv = ''; # CVV used only for validation
        $user_billing->address1 = $address1;
        $user_billing->address2 = $address2;
        $user_billing->city = $city;
        $user_billing->state = $state;
        $user_billing->zip = $zip;
        $user_billing->default_card = $default_card;
        $user_billing->card_token = $ret['token'];
        $user_billing->cdate = Carbon::now();

        self::check_default_card($user_billing);

        //Need to check dup requests again, because WEB/APP could send the requests multiple times, while Token processings.
        ### Duplication Check Again ###
        $user_billing2 = UserBilling::whereRaw('RIGHT(card_number,4) = \''.substr($card_number, -4).'\'')
            ->where('user_id', $user_id)
            ->where('status', 'A')
            ->first();

        if (!empty($user_billing2)) {
            return [
                'code'  => '1',
                'msg'   => 'The same credit card already exists on this User. You might click the button repeatedly.',
                'card'  => $user_billing
            ];
        }


        $user_billing->save();

        //Update with new one at existing appointments, if it's not completed yet.
        //That's because if the credit card payments failed in charge/holding of $0.01, customer should update or add new one.
        AppointmentList::where('user_id', $user_id)
        ->where('accepted_date', '>', Carbon::now() )
        ->whereNotIn('status', ['C','L','P'] )
        ->update([
            'payment_id' => $user_billing->billing_id,
            'modified_by' =>'NewCC',
            'mdate' => Carbon::now()
            ]);

        return [
            'code' => '0',
            'msg' => '',
            'card' => $user_billing
        ];
    }

    public static function update_card(
        $user_id,
        $billing_id,
        $card_holder,
        $card_number,
        $expire_mm,
        $expire_yy,
        $cvv,
        $address1,
        $address2,
        $city,
        $state,
        $zip,
        $default_card
    ) {
        ### Duplication Check ###
        $user_billing = UserBilling::whereRaw('RIGHT(card_number,4) = \''.substr($card_number, -4).'\'')
            ->where('user_id', $user_id)
            ->where('status', 'A')
            ->where('billing_id', '!=', $billing_id)
            ->first();

        if (!empty($user_billing)) {
            return [
                'msg' => 'User Billing with same credit card # already exists'
            ];
        }


        $user_billing = UserBilling::find($billing_id);
        if (empty($user_billing)) {
            return [
                'msg' => 'Invalid billing ID provided'
            ];
        }

        if ($user_billing->user_id != $user_id) {
            return [
                'msg' => 'Provided token does not match'
            ];
        }

        if (substr($user_billing->card_number, -4) != substr($card_number, -4)) {
            $ret = Converge::get_token(
                $card_number,
                $expire_mm . $expire_yy,
                $cvv,
//                $request->address1 . ' ' . $request->address2,
//                $request->city,
//                $request->state,
                $zip
            );
        } else {
            $ret = Converge::update_token(
                $user_billing->card_token,
                $card_number,
                $expire_mm . $expire_yy,
                $cvv,
//                $request->address1 . ' ' . $request->address2,
//                $request->city,
//                $request->state,
                $zip
            );
        }

        if (!empty($ret['error_msg'])) {
            return [
                'msg' => 'Credit card verification failed: ' . $ret['error_msg'] . ' [' . $ret['error_code'] . ']'
            ];
        }

        //$user_billing->card_type = $request->card_type;
        $user_billing->card_number = $ret['card_number'];
        $user_billing->card_holder = $card_holder;
        $user_billing->expire_mm = $expire_mm;
        $user_billing->expire_yy = $expire_yy;
        //$user_billing->cvv = $request->cvv;
        $user_billing->cvv = ''; # CVV used only for validation
        $user_billing->address1 = $address1;
        $user_billing->address2 = $address2;
        $user_billing->city = $city;
        $user_billing->state = $state;
        $user_billing->zip = $zip;
        $user_billing->card_token = $ret['token'];
        $user_billing->mdate = Carbon::now();
        $user_billing->default_card = $default_card;
        $user_billing->status = 'A' ;
        $user_billing->modified_by = 'User: ' . $user_billing->user_id;
        self::check_default_card($user_billing);

        $user_billing->save();

        ### Update status of rejected appointment, for recharge again, into 'Groomer Assigned(D)
        AppointmentList::where('user_id', $user_id)->where('payment_id', $user_billing->billing_id)
            ->where('status', 'R')
            ->update([
                'status' => 'D'
            ]);

        //Update with new one at existing appointments, if it's not completed yet.
        //That's because if the credit card payments failed in charge/holding of $0.01, customer should update or add new one.
        AppointmentList::where('user_id', $user_id)
            ->where('accepted_date', '>', Carbon::now() )
            ->whereNotIn('status', ['C','L','P'] )
            ->update([
                'payment_id' => $user_billing->billing_id,
                'modified_by' =>'UpdCC',
                'mdate' => Carbon::now()
            ]);

        return [
            'msg' => '',
            'card' => $user_billing
        ];
    }

    private static function check_default_card($card)
    {
        if ($card->default_card == 'Y') {

            UserBilling::where('user_id', '=', $card->user_id)
                ->where('billing_id', '!=', $card->billing_id)
                ->where('default_card', '=', 'Y')
                ->update(array('default_card' => 'N'));
        }
    }

    public static function set_default_card($card) {
        UserBilling::where('user_id', '=', $card->user_id)
          ->where('billing_id', '!=', $card->billing_id)
          ->where('default_card', '=', 'Y')
          ->update([
            'default_card' => 'N'
          ]);

        UserBilling::where('user_id', '=', $card->user_id)
          ->where('billing_id',  $card->billing_id)
          ->update([
            'default_card' => 'Y'
          ]);
    }

}