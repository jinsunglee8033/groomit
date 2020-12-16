<?php

namespace App\Console\Commands;

use App\Model\Message;
use Illuminate\Console\Command;
use App\Model\AppointmentList;
use App\Model\User;
use App\Model\Groomer;
use App\Model\Address;
use App\Model\Breed;
use App\Model\Size;
use Carbon\Carbon;
use DB;
use Log;
use Validator;
use App\Lib\Helper;
use App\Lib\UserProcessor;

class SendReminderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:survey';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Survey Email/Push';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tech_email = 'tech@groomit.me';

        try {

            $today = Carbon::now()->format('Y-m-d');

            $apps = DB::select("
                            select a.appointment_id, a.user_id, a.groomer_id, IfNull(b.device_token,'') device_token, b.email,  c.first_name, c.last_name
                            from appointment_list a inner join user b on a.user_id = b.user_id
                                                    inner join groomer c on a.groomer_id = c.groomer_id
                            where DATE(accepted_date) = '" . $today . "'
                            and a.status = 'P'
                            and IfNull(b.device_token,'') != ''
                        ");
            if (!empty($apps)) {

                foreach ($apps as $app) {
                    $send_mail = true;
                    $today_reminder = false;
                    $groomer_name = '';
                    $groomer_email = '';
                    $subject = '';
                    $address = '';
                    $app_date = '';
                    $app_time = '';
                    $message = '';

                    if ($app->accepted_date) {
                        $app_date = Carbon::parse($app->accepted_date);
                        $app_time = $app_date->format('g:i A');
                    }



                    $data = [];

                    $subject = "You have an upcoming Groomit Appointment today.";
                    $data['reminder_type'] = "today";
                    $message = 'Just a reminder of your upcoming Groomit Appointment today ' . $app_time;

                    ### send push ###
                    if (!empty($app->device_token) && (trim($app->device_token) != '') )  {

                            $payload = [
                                'type' => 'A',
                                'id' => $app->appointment_id
                            ];

                            $error = Helper::send_notification('groomit', $message, $app->device_token, $subject, $payload);
                            if (!empty($error)) {
                                Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Push Notification to User[Appt ID:' .$app->appointment_id .'] failed - Survey request', 'Push Notification Error: ' . $error . '[' . $app->device_token . ']' );
                            }
                        } else {
                            Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Push Notification to User[Appt ID:' .$app->appointment_id .'] failed - Survey today', 'Push Notification Error: No device token found');
                        }

                        ### end send push

                }
            }

//            $bar->finish();
//            $this->info(" - Done...");

        } catch (\Exception $ex) {
            $msg = ' - error : ' . $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString();
            $this->error($msg);
            Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Send Survey failed', $msg);
        }
    }
}
