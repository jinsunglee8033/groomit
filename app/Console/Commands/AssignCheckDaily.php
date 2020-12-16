<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 5/29/19
 * Time: 6:05 AM
 */

namespace App\Console\Commands;

use App\Lib\Helper;
use App\Model\AppointmentList;
use App\Model\Message;
use App\Model\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AssignCheckDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:check:daily';

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
        $new_apps = AppointmentList::where('status', 'N')->get();

        if (count($new_apps) > 0) {
            foreach ($new_apps as $o) {
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
    }
}
