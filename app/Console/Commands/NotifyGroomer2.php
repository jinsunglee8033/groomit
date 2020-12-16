<?php

namespace App\Console\Commands;

use App\Lib\Helper;
use App\Lib\AppointmentProcessor;
use App\Model\AppointmentList;
use Carbon\Carbon;
use Illuminate\Console\Command;
use DB;

class NotifyGroomer2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'groomer:notify2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'New Notifications';

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

            //Notify at every 3 minutes
            $apps = AppointmentList::where('status', 'N')
                ->where('cdate', '>=', Carbon::now()->subMinutes(6))
                ->where('cdate', '<', Carbon::now()->subMinutes(3))
                ->whereRaw('IfNull(prefer_groomer_id,0) = 0 ')
                ->get();

            if (count($apps) > 0) {
                foreach ($apps as $app) {
                    AppointmentProcessor::send_groomer_notification2($app);
                }
            }

            $apps = AppointmentList::where('status', 'N')
                ->where('cdate', '>=', Carbon::now()->subMinutes(27))
                ->where('cdate', '<', Carbon::now()->subMinutes(24))
                ->whereRaw('IfNull(prefer_groomer_id,0) = 0 ')
                ->get();

            if (count($apps) > 0) {
                foreach ($apps as $app) {
                    AppointmentProcessor::send_groomer_notification2($app);
                }
            }

//Argument 1 passed to App\Lib\AppointmentProcessor::send_groomer_notification2() must be an instance of App\Model\AppointmentList, instance of stdClass given,
//            $apps = DB::select(" SELECT a.*, b.cdate as notify_date
//                         FROM appointment_list a
//                             inner join groomer_opens b on a.appointment_id = b.appt_id
//                             and b.stage = 700
//                             and b.cdate >=  '" .  Carbon::now()->subMinutes(15) . "' " .
//                           " and b.cdate < '" . Carbon::now()->subMinutes(12) . "' " .
//                         " WHERE a.status = 'N'
//                         AND IfNull(a.prefer_groomer_id,0) > 0
//                         order by a.appointment_id asc, b.cdate desc ",
//                            [ ]);
            $apps = AppointmentList::join('groomer_opens', 'groomer_opens.appt_id', '=', 'appointment_list.appointment_id')
                ->where('appointment_list.status', 'N')
                ->where('groomer_opens.stage', 700)
                ->where('groomer_opens.cdate', '>=', Carbon::now()->subMinutes(15))
                ->where('groomer_opens.cdate', '<', Carbon::now()->subMinutes(12))
                ->where('appointment_list.status', 'N')
                ->whereRaw('IfNull(appointment_list.prefer_groomer_id,0) > 0 ')
                ->get();

            if (count($apps) > 0) {
                //Helper::send_mail('tech@groomit.me', '[GROOMIT.ME][' . getenv('APP_ENV') . ']cnt', count($apps) );
                $appt_id = -1;
                foreach ($apps as $app) {
                    if( $appt_id != $app->appointment_id ) {
                        AppointmentProcessor::send_groomer_notification2($app);
                        $appt_id = $app->appointment_id ;
                    } //Do not send notification when earlier 700 transactions.
                }
            }
        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString();
            Helper::send_mail('tech@groomit.me', '[GROOMIT.ME][' . getenv('APP_ENV') . '] Groomer Notification Script Failed', $msg);
        }
    }
}
