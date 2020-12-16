<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\AppointmentList;
use App\Model\Groomer;
use Carbon\Carbon;
use DB;
use Log;
use Validator;
use App\Lib\Helper;

class CheckGroomerOnTheWay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:groomer_on_the_way';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS when Groomer Not On The Way within 1 hour before appointment time';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tech_email = 'tech@groomit.me';

        try {


            $apps = DB::select("
                            select
                                a.*
                            from appointment_list a
                                inner join address b on a.address_id = b.address_id
                            where
                            IF(b.state = 'CA',
                                a.accepted_date > '" . Carbon::now()->subHour(3) . "' and a.accepted_date <= '" . Carbon::now()->subHour(2) . "',
                                a.accepted_date > '" . Carbon::now() . "' and a.accepted_date <= '" . Carbon::now()->addHour() . "')
                            and a.status = 'D'
                        ");


//            $this->info("1. Total appointment groomer not on the way : " . count($apps));
//            $bar = $this->output->createProgressBar(count($apps));


            if (!empty($apps)) {

                foreach ($apps as $app) {

                    $app_date = Carbon::parse($app->accepted_date);
                    $app_date = $app_date->format('g:i A');

                    $groomer = Groomer::find($app->groomer_id);

                    $message = "Groomer is not on the way yet. \nAppointment ID: " . $app->appointment_id . " \nService time: " . $app_date . " \nGroomer: " . $groomer->first_name . " " . $groomer->last_name;

                    $err_msg = '[Groomit][' . getenv('APP_ENV') . '] Send SMS for "Check Groomer On The Way" failed. Appointment ID: ' . $app->appointment_id;

                    ### send text ###

                    if (getenv('APP_ENV') == 'production') {
                        $ret = Helper::send_sms($groomer->phone, 'Are you on your way to your next appointment? Please update status through Groomer App.');

                        if (!empty($ret)) {
                            Helper::send_mail($groomer->email, $message, $ret);
                        }

                        $ret = Helper::send_sms_to_admin($message);
                        if (!empty($ret)) {
                            Helper::send_mail($tech_email, $err_msg, $ret);
                        }
                    }
                    ### end send text ###

                    // save message
                    $r = Helper::save_message('groomer_not_on_the_way', '', '', $app->appointment_id, '', $message);
                    if (!empty($r)) {
                        Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Send reminder email - save message failed', 'Groomer ID: ' . $app->groomer_id . ', Appointment ID : ' . $app->appointment_id);
                    }
                    // end save message


                }
            }

//            $bar->finish();
//            $this->info(" - Done...");

        } catch (\Exception $ex) {
            $msg = ' - error : ' . $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString();
            $this->error($msg);
            Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Send SMS for "Check Groomer On The Way" failed', $msg);
        }
    }
}
