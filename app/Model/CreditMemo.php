<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 10/23/18
 * Time: 2:18 PM
 */

namespace App\Model;

use App\Lib\Helper;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreditMemo extends Model
{
    protected $table = 'credit_memo';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

    public function getTypeNameAttribute() {
        $type = $this->attributes['type'];
        switch ($type) {
            case 'C':
                return 'Credit';
            case 'D':
                return 'Debit';
            default:
                return $type;
        }
    }

    ### Credit Memo ###
    public static function create_memo($user_id, $type, $amt, $expire_date, $ref_type, $ref, $appointment_id, $created_by, $orig_id = null) {
        try {
            $expire_date = empty($expire_date) ? '2099-12-31' : $expire_date;

            $memo = new CreditMemo();
            $memo->user_id      = $user_id;
            $memo->type         = $type;
            $memo->amt          = $type == 'C' ? $amt : -$amt;
            $memo->ref_type     = $ref_type;
            $memo->ref          = $ref;
            $memo->expire_date  = $expire_date;
            $memo->appointment_id = $appointment_id;
            $memo->status       = 'A';
            $memo->created_by   = $created_by;
            $memo->cdate        = \Carbon\Carbon::now();
            $memo->save();

            $memo->orig_id = empty($orig_id) ? $memo->id : $orig_id;
            $memo->update();

        } catch (\Exception $ex) {
            Helper::log('####### CREDIT MEMO # create_memo # EXCEPTION #######', $ex->getTraceAsString());
        }
    }

    public static function use_credit($user_id, $amt, $appointment_id) {
        try {
            if ($amt <= 0 ) return;

            $credit = CreditMemo::where('user_id', $user_id)
              ->whereRaw('(expire_date >= \'' . \Carbon\Carbon::today() . '\' or expire_date is null)')
              ->groupBy('orig_id')
              ->orderBy('expire_date', 'asc')
              ->first([
                'orig_id',
                DB::raw('max(expire_date) as expire_date'),
                DB::raw('max(ref_type) as ref_type'),
                DB::raw('max(ref) as ref'),
                DB::raw('sum(amt) as amt'),
              ]);

            if (empty($credit) || $credit->amt <= 0) {
                return;
            }

            if ($credit->amt <= $amt) {
                ### create_memo($user_id, $type, $amt, $expire_date, $ref_type, $ref, $created_by, $orig_id = null)
                self::create_memo($user_id, 'D', $credit->amt, $credit->expire_date, $credit->reftype, $credit->ref, $appointment_id, 'system', $credit->orig_id);

                if ($credit->amt < $amt) {
                    self::use_credit($user_id, $amt - $credit->amt, $appointment_id);
                }

                return;
            } else {
                ### create_memo($user_id, $type, $amt, $expire_date, $ref_type, $ref, $created_by, $orig_id = null)
                self::create_memo($user_id, 'D', $amt, $credit->expire_date, $credit->reftype, $credit->ref, $appointment_id, 'system', $credit->orig_id);
                return;
            }

        } catch (\Exception $ex) {
            Helper::log('####### CREDIT MEMO # use_credit # EXCEPTION #######', $ex->getTraceAsString());
        }

    }

    public static function unuse_credit($app) {
        try {
            if (empty($app)) {
                return;
            }

            $credits = CreditMemo::where('appointment_id', $app->appointment_id)
                ->groupBy('orig_id')
                ->get([
                    'orig_id',
                    DB::raw('max(expire_date) as expire_date'),
                    DB::raw('max(ref_type) as ref_type'),
                    DB::raw('max(ref) as ref'),
                    DB::raw('sum(amt) as amt')
                ]);

            if (empty($credits) || count($credits) < 1) {
                return;
            }

            foreach ($credits as $credit) {
                if ($credit->amt != 0) {
                    ### create_memo($user_id, $type, $amt, $expire_date, $ref_type, $ref, $created_by, $orig_id = null)
                    self::create_memo($app->user_id, 'C', -$credit->amt, $credit->expire_date, $credit->reftype, $credit->ref, $app->appointment_id, 'system', $credit->orig_id);
                }
            }

        } catch (\Exception $ex) {
            Helper::log('####### CREDIT MEMO # unuse_credit # EXCEPTION #######', $ex->getTraceAsString());
        }
    }
}
