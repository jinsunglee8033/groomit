<?php

namespace App\Console\Commands;

use App\Lib\Helper;
use App\Model\AppointmentList;
use App\Model\Message;
use App\Model\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AssignCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $half_hours = AppointmentList::where('status', 'N')
            ->where('cdate', '<=', Carbon::now()->subMinutes(30))
            ->where('cdate', '>=', Carbon::now()->subMinutes(35))
            ->get();

        if (count($half_hours) > 0) {
            foreach ($half_hours as $o) {
                $msg = '30 minutes passed without groomer assigned for the appointment' . "\n";
                $msg .= ' - apponitment ID: ' . $o->appointment_id;

                if (getenv('APP_ENV') == 'production') {
                    Helper::send_sms_to_cs($msg);
                } else {
                    Helper::send_sms('2015675555', $msg);
                }
            }
        }

        $one_hours = AppointmentList::where('status', 'N')
            ->where('cdate', '<=', Carbon::now()->subMinutes(60))
            ->where('cdate', '>=', Carbon::now()->subMinutes(65))
            ->get();
        if (count($one_hours) > 0) {
            foreach ($one_hours as $o) {
                $msg = 'We’re still looking to assign a groomer, we will update you ASAP. Thank you for your patience.';

                if (getenv('APP_ENV') == 'production') {
                    $user = User::find($o->user_id);

                    Message::save_sms_to_user($msg, $user, $o->appointment_id);

                    ### SMS to user
                    if (!empty($user->phone)) {
                        $ret = Helper::send_sms($user->phone, $msg);

                        if (!empty($ret)) {
                            Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $msg);
                        }
                    }

                    if (!empty($user->device_token) && ($user->device_token != '') ) {
                        Helper::send_notification("", $msg, $user->device_token, 'Notice', "");
                    }
                }

            }
        }
//        if (count($one_hours) > 0) {
//            foreach ($one_hours as $o) {
//                $msg = '60 minutes passed without groomer assigned for the appointment' . "\n";
//                $msg .= ' - apponitment ID: ' . $o->appointment_id;
//
//                if (getenv('APP_ENV') == 'production') {
//                    $ret = Helper::send_sms_to_cs($msg);
//                } else {
//                    $ret = Helper::send_sms('2015675555', $msg);
//                    if (!empty($ret)) {
//                        $this->error(' - failed to send SMS : ' . $ret);
//                        exit;
//                    }
//                }
//            }
//        }


        ##
        $two_hours = AppointmentList::where('status', 'N')
          ->where('cdate', '<=', Carbon::now()->subMinutes(120))
          ->where('cdate', '>=', Carbon::now()->subMinutes(125))
          ->get();

        if (count($two_hours) > 0) {
            foreach ($two_hours as $o) {
                $msg = 'We are still attempting to locate the closest available Groomit groomer for your requested date and time. Thank you for your patience.';


                if (getenv('APP_ENV') == 'production') {
                    $user = User::find($o->user_id);

                    Message::save_sms_to_user($msg, $user, $o->appointment_id);

                    ### SMS to user
                    if (!empty($user->phone)) {
                        $ret = Helper::send_sms($user->phone, $msg);

                        if (!empty($ret)) {
                            Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $msg);
                        }
                    }
                }
            }
        }

        if (count($half_hours) > 0 || count($one_hours) > 0 || count($two_hours) > 0) {
            $msg = ' - total half hours : ' . count($half_hours) . ' <br/>';
            $msg .= ' - total one hours : ' . count($one_hours) . ' <br/>';
            $msg .= ' - total two hours : ' . count($two_hours);
            Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Assign Check Result', $msg);

        }

        $this->info(' - total half hours: ' . count($half_hours));
        $this->info(' - total one hours: ' . count($one_hours));
        $this->info(' - total two hours: ' . count($two_hours));
    }
}
