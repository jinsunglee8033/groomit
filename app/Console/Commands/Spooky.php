<?php

namespace App\Console\Commands;

use App\Lib\Helper;
use App\Model\PromoCode;
use App\Model\PromoCodeUsers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Spooky extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spooky:promotion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Halloween SPOOKY promotion 2020';

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

            $apps = DB::select("
                            select  distinct a.appointment_id, a.user_id, b.email, b.first_name, b.last_name, b.phone, b.device_token
                            from appointment_list a inner join user b on a.user_id = b.user_id
                            where a.accepted_date >= curdate() - interval 1 day
                            and a.accepted_date < curdate()
                            and a.status ='P'
                            and a.promo_code = 'SPOOKY'
                            and not exists ( select code from promo_code where user_id = a.user_id and type ='N' and valid_user_ids = a.user_id and note like 'SPOOKY%')
                        ");

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

                        Helper::send_html_mail('vouchers.spooky', $data);

                    }catch (\Exception $ex) {
                        $msg = "[" .$app->appointment_id . "]" . $ex->getCode() ;

                        Helper::send_mail('jun@jjonbp.com', '[GROOMIT.ME][' . getenv('APP_ENV') . '] SPOOKY Job Error', $msg);
                    }

                }
            }
        } catch (\Exception $ex) {
            $msg = ' - code : ' . $ex->getCode() . "<br/>";
            $msg .= ' - error : ' . $ex->getMessage() . '<br/>';
            $msg .= ' - trace : ' . $ex->getTraceAsString();
            Helper::send_mail('it@jjonbp.com', '[GROOMIT.ME][' . getenv('APP_ENV') . '] SPOOKY Job Error', $msg);
        }

    }
}
