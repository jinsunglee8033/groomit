<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 11/1/18
 * Time: 3:41 AM
 */

namespace App\Console\Commands;

use App\Lib\Helper;
use App\Model\AppointmentProduct;
use App\Model\PromoCode;
use App\Model\PromoCodeUsers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Model\AppointmentList;
use App\Model\CreditMemo;
use App\Model\Credit;

class SpecialPromotion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'special:promotion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Special promotion';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {

            Helper::send_mail('it@jjonbp.com', '[GROOMIT]SPOOKY promo.', 'started...');

//            $apps = DB::select("
//                            select  distinct a.appointment_id, a.user_id, b.email, b.first_name, b.last_name, b.phone, b.device_token
//                            from appointment_list a inner join user b on a.user_id = b.user_id
//                            where a.accepted_date >= curdate() - interval 1 day
//                            and a.accepted_date < curdate()
//                            and a.status ='P'
//                            and a.promo_code = 'SPOOKY'
//                            and not exists ( select code from promo_code where user_id = a.user_id and type ='N' and valid_user_ids = a.user_id and note like 'SPOOKY%')
//                        ");

            $apps = DB::select("  select  distinct 112 appointment_id , b.user_id, b.email, b.first_name, b.last_name, b.phone, b.device_token
                            from user b 
                            where b.email = 'jun@jjonbp.com' ");

            if (!empty($apps) && count($apps) > 0) {
                $expire_date = Carbon::today()->addDays(365);

                foreach ($apps as $app) {

                    $new_promo_code = 'S' . $app->user_id  . mt_rand(111, 999);;

                    try {
                        $promo_code = new PromoCode;
                        $promo_code->code = $new_promo_code;
                        $promo_code->type = 'N';
                        $promo_code->amt_type = 'A';
                        $promo_code->amt = 20; //$20
                        $promo_code->status = 'A';
                        $promo_code->first_only = 'N';
                        $promo_code->expire_date = $expire_date;
                        $promo_code->valid_user_ids = $app->user_id;
                        $promo_code->no_insurance = 'N';
                        $promo_code->include_tax = 'N';
                        $promo_code->cdate = Carbon::now();
                        $promo_code->created_by = 19; //SystemAdmin
                        $promo_code->note = 'SPOOKY Promotion[' . $app->appointment_id . "]";
                        $promo_code->save();

                        $pu = new PromoCodeUsers();
                        $pu->promo_code = $new_promo_code;
                        $pu->user_id = $app->user_id;
                        $pu->cdate = Carbon::now();
                        $pu->save();

                        //Emails.
                        $data = [];
                        $data['promo_code'] = $new_promo_code;
                        $data['name']   = $app->first_name . ' ' . $app->last_name ;

                        $data['email']  = $app->email;
                        $data['bcc']  = 'tech@groomit.me';

                        $data['subject'] = 'Here is your Groomit Gift Card.';

//                            Helper::log('##### EMAIL DATA #####', [
//                                'data' => $data
//                            ]);

                        Helper::send_html_mail('vouchers.spooky', $data);

                    }catch (\Exception $ex) {
                       $msg = "[" .$app->appointment_id . "]" . $ex->getCode() ;

                       Helper::send_mail('jun@jjonbp.com', '[GROOMIT.ME][' . getenv('APP_ENV') . '] SPOOKY Job Error2', $msg);
                    }

                }
            }
        } catch (\Exception $ex) {
            $msg = ' - code : ' . $ex->getCode() . "<br/>";
            $msg .= ' - error : ' . $ex->getMessage() . '<br/>';
            $msg .= ' - trace : ' . $ex->getTraceAsString();
            Helper::send_mail('it@jjonbp.com', '[GROOMIT.ME][' . getenv('APP_ENV') . '] SPOOKY Job Error2', $msg);
        }
    }
}
//            $apps = AppointmentList::where('status', 'P')
//              ->whereRaw("promo_code = 'HALLOWEEN'")
//              ->whereRaw("appointment_id not in (select appointment_id from credit_memo where ref_type = 'P' and ref = 'HALLOWEEN')")
//              ->get();
//
//            if (!empty($apps) && count($apps) > 0) {
//                $expire_date = Carbon::today()->addDays(365);
//
//                foreach ($apps as $app) {
//                    $credit = new Credit();
//                    $credit->user_id        = $app->user_id;
//                    $credit->appointment_id = $app->appointment_id;
//                    $credit->type           = 'C';
//                    $credit->category       = 'P';
//                    $credit->amt            = 25;
//                    $credit->expire_date    = $expire_date;
//                    $credit->status         = 'A';
//                    $credit->cdate          = Carbon::now();
//                    $credit->save();
//
//                    CreditMemo::create_memo($app->user_id, 'C', 25, $expire_date,'P', 'HALLOWEEN', $app->appointment_id, 'system');
//                }
//            }
//
//            $apps = DB::select("
//                select a.appointment_id, a.user_id, p.pet_id, az.group_id, pd.size_id, pd.prod_id, pd.denom
//                  from appointment_list a
//                  join appointment_product p on a.appointment_id = p.appointment_id
//                  join appointment_pet e on p.pet_id = e.pet_id
//                  join address ad on a.address_id = ad.address_id
//                  join allowed_zip az on ad.zip = az.zip
//                  join product_denom pd on pd.group_id = az.group_id and pd.prod_id = 2 and pd.size_id = e.size_id
//                 where a.cdate > :sdate
//                   and a.cdate < :edate
//                   and p.prod_id = 1
//                   and a.status = 'P'
//                   and a.appointment_id not in (select appointment_id from credit_memo where ref = 'FREESILVER')
//                 group by a.appointment_id, a.user_id, p.pet_id, az.group_id, pd.size_id
//            ", [
//                'sdate' => '2018-11-26',
//                'edate' => '2018-11-27 03:01'
//            ]);
//
//            if (!empty($apps) && count($apps) > 0) {
//                $expire_date = Carbon::today()->addDays(365);
//
//                foreach ($apps as $app) {
//                    $credit = new Credit();
//                    $credit->user_id        = $app->user_id;
//                    $credit->appointment_id = $app->appointment_id;
//                    $credit->type           = 'C';
//                    $credit->category       = 'P';
//                    $credit->amt            = $app->denom;
//                    $credit->expire_date    = $expire_date;
//                    $credit->status         = 'A';
//                    $credit->cdate          = Carbon::now();
//                    $credit->save();
//
//                    CreditMemo::create_memo($app->user_id, 'C', $app->denom, $expire_date,'P', 'FREESILVER', $app->appointment_id, 'system');
//                }
//            }
//
//
//            $bo_apps = AppointmentList::where('status', 'P')
//              ->whereRaw("promo_code = 'BOGONYC'")
//              ->whereRaw("appointment_id not in (select appointment_id from credit_memo where ref_type = 'P' and ref = 'BOGONYC')")
//              ->get();
//
//            if (!empty($bo_apps) && count($bo_apps) > 0) {
//                $expire_date = Carbon::today()->addDays(90);
//
//                foreach ($bo_apps as $app) {
//                    $pamt = AppointmentProduct::where('appointment_id', $app->appointment_id)->whereIn(prod_id, [1,2,16,27,28,29])->sum('amt');
//
//                    $credit = new Credit();
//                    $credit->user_id        = $app->user_id;
//                    $credit->appointment_id = $app->appointment_id;
//                    $credit->type           = 'C';
//                    $credit->category       = 'P';
//                    $credit->amt            = $pamt;
//                    $credit->expire_date    = $expire_date;
//                    $credit->status         = 'A';
//                    $credit->cdate          = Carbon::now();
//                    $credit->save();
//
//                    CreditMemo::create_memo($app->user_id, 'C', $pamt, $expire_date,'P', 'BOGONYC', $app->appointment_id, 'system');
//                }
//            }




