<?php

namespace App\Console\Commands;

use App\Lib\Helper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HourlyJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:hourly';

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

            ### 1. remove test appointment ###
            # - @jjonbp.com
            # - @groomit.me
            # - @black011.com

            $ret = DB::statement("
                delete a
                from appointment_list a 
                    inner join user b on a.user_id = b.user_id
                        and (
                            lower(b.email) like '%@jjonbp.com' or 
                            lower(b.email) like '%@groomit.me' or 
                            lower(b.email) like '%@black011.com' or 
                            lower(b.email) = 'lars.rissmann@gmail.com'
                        )
                where a.status in ('C', 'L')
            ");

            $msg = ' - remove test appointment: ' . $ret . '<br/>';

            ### 2. mark appointment as re-scheduled if there is new appointment from same user within 7 days ###
            DB::statement("
                update appointment_list a 
                    inner join appointment_list b on a.user_id = b.user_id 
                        and a.cdate < b.cdate
                        and a.appointment_id != b.appointment_id
                        and b.cdate < a.cdate + interval 7 day
                set a.status = 'L',
                    a.rescheduled_id = b.appointment_id
                where a.status = 'C'
                and a.rescheduled_id is null 
            ");

//            $msg = ' - mark rescheduled: ' . $ret;

//            Helper::send_mail('tech@groomit.me', '[GROOMIT.ME][' . getenv('APP_ENV') . '] Hourly Job Result', $msg);

        } catch (\Exception $ex) {
            $msg = ' - code : ' . $ex->getCode() . "<br/>";
            $msg .= ' - error : ' . $ex->getMessage() . '<br/>';
            $msg .= ' - trace : ' . $ex->getTraceAsString();
            Helper::send_mail('tech@groomit.me', '[GROOMIT.ME][' . getenv('APP_ENV') . '] Hourly Job Error', $msg);
        }

    }
}
