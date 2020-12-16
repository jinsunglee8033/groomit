<?php

namespace App\Console\Commands;

use App\Lib\Helper;
use App\Lib\AppointmentProcessor;
use App\Model\AppointmentList;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyGroomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'groomer:notify';

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
        try {

            ### get non-notified appointments with new status and created within 20 minutes ###
            //Actually, it's for Level 2 only, because Level 1 groomers will get notifications at the time of appointments, if non Fav.
            //In case of Fav. Groomer exist, that's it. no more notifications to the others.
            $apps = AppointmentList::where('status', 'N')
                ->where('cdate', '>=', Carbon::now()->subMinutes(25))
                ->where('cdate', '<', Carbon::now()->subMinutes(20))
                ->get();

            if (count($apps) > 0) {
                foreach ($apps as $app) {
                    AppointmentProcessor::send_groomer_notification($app, 1);
                }
            }

            //It's stopped at 12/30/2019, becasue it's supposed to be sent to groomer Level 3 only, but it's not wanted by Lars.
//            ### get non-notified appointments with new status and created within 35 minutes ###
//            $apps_l2 = AppointmentList::where('status', 'N')
//              ->where('cdate', '>=', Carbon::now()->subMinutes(40))
//              ->where('cdate', '<', Carbon::now()->subMinutes(35))
//              ->get();
//
//            if (count($apps_l2) > 0) {
//                foreach ($apps_l2 as $app) {
//                    AppointmentProcessor::send_groomer_notification($app, 2);
//                }
//            }

        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString();
            Helper::send_mail('tech@groomit.me', '[GROOMIT.ME][' . getenv('APP_ENV') . '] Groomer Notification Script Failed', $msg);
        }
    }
}
