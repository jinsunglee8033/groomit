<?php

namespace App\Console\Commands;

use App\Lib\AppointmentProcessor;
use App\Lib\Helper;
use App\Model\AppointmentList;
use App\Model\CCTrans;
use App\Model\Message;
use App\Model\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class HoldCC extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hold:cc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hold Credit Card for appointment of status D - Groomer assigned & time accepted before 2 hours.';

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

            $msg = '### Hold CC for appointments ###';
            $this->info($msg);
            $email_msg = $msg. "\n";

            $data = AppointmentList::where('status', 'D')
                ->where('accepted_date', '<=', Carbon::now()->addHours(2))
                ->where('accepted_date', '>', Carbon::now()->addHours(2)->addMinutes(-5)) //; Carbon::today())
                ->where('total', '>', 0)
                ->get();

            $msg = ' - total records : ' . count($data);
            $this->info($msg);
            $email_msg .= $msg. "\n";

            $cnt = 0;
            foreach ($data as $ap) {
                $msg = ' - holding CC of 1 cent for ' . $ap->appointment_id;
                $this->info($msg);
                $email_msg .= $msg. "\n";


                $total_trans = CCTrans::where('appointment_id', $ap->appointment_id)
                    ->whereIn('type', ['A', 'S', 'V'])
                    ->where('category', 'S')
                    ->where('result', 0)
                    //->whereNull('void_date')
                    ->where('amt', '!=', 0.01)
                    ->where( DB::raw('IfNull(error_name,"")'), '!=', 'cccomplete' )
                    ->sum( DB::raw("case type when 'S' then amt when 'A' then amt else -amt end") );

                if ( !empty($total_trans) ) {
                    if($total_trans == $ap->total) {
                        $msg = ' - holding CC already done, so skip holding.';
                        $this->info($msg);
                        $email_msg .= $msg. "\n";
                        continue;
                    }
                }
                if( $ap->total == 0 ){
                    $msg = ' - $0 Appointment Amount, so skip holding.';
                    $this->info($msg);
                    $email_msg .= $msg. "\n";
                    continue;
                }

                $proc = new AppointmentProcessor();
                //$ret = $proc->hold_appointment($ap);
                $ret = $proc->holdvoid_appointment($ap);
                if (!empty($ret['error_msg'])) {
                    $ap->status = 'R';
                    $ap->note = $ret['error_msg'] . ' [' . $ret['error_code'] . ']';
                    $ap->save();

                    $msg = ' - error : ' . $ret['error_msg'] . ' [' . $ret['error_code'] . ']';
                    $this->error($msg);
                    $email_msg .= $msg. "\n";

                    ### send SMS to user ###
                    $user = User::find($ap->user_id);
                    if (!empty($user->phone)) {
                        $message = 'We were unable to charge your credit card. Please update your payment method as soon as possible.';
                        $ret = Helper::send_sms($user->phone, $message);
                        Message::save_sms_to_user($message, $user, $ap->appointment_id);

                        if (!empty($ret)) {
                            //throw new \Exception($ret);
                            Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $message . '/ Appointment ID:' . $ap->appointment_id);
                        }
                    }

                    ### send alert to C/S ####
                    $sms_msg = "Fail to hold appointment amount. Please ask customers to check credit card. [Appointment ID :" . $ap->appointment_id . "]";
                    $ret_sms = Helper::send_sms_to_cs($sms_msg);
                    if (!empty($ret_sms)) {
                        $this->error($ret_sms);
                        $email_msg .= $ret_sms . "\n";
                    }
                }

                $cnt++;
            }

            $msg = ' - completed';
            $this->info($msg);
            $email_msg .= $msg. "\n";

            if ($cnt > 0) {
                Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . env('APP_ENV') . '] HoldCC completed', $email_msg);
            }

//
//            //Now refund holding amount, if exists.
//            $cnt_refund = 0;
//            foreach ($data as $ap) {
//                $msg = ' - refunds holding amount  for ' . $ap->appointment_id;
//                $this->info($msg);
//                $email_msg .= $msg. "\n";
//
//                $proc = new AppointmentProcessor();
//                $ret = $proc->refund_holding_amounts($ap);
//                if (!empty($ret['error_msg'])) {
//                    $msg = ' - error : ' . $ret['error_msg'] . ' [' . $ret['error_code'] . ']';
//                    $this->error($msg);
//                    $email_msg .= $msg. "\n";
//
//                    ### send alert to C/S ####
//                    $sms_msg = "Fail to refund holding appointment amount. [Appointment ID :" . $ap->appointment_id . "]";
//                    Helper::send_mail('jun@jjonbp.com', '[GROOMIT][' . env('APP_ENV') . '] Fail to refund HoldCC ', $sms_msg);
////                    $ret_sms = Helper::send_sms_to_cs($sms_msg);
////                    if (!empty($ret_sms)) {
////                        $this->error($ret_sms);
////                        $email_msg .= $ret_sms . "\n";
////                    }
//                }
//                $cnt_refund++;
//            }
//
//            $msg = ' - completed';
//            $this->info($msg);
//            $email_msg .= $msg. "\n";
//
//            if ($cnt_refund > 0) {
//                Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . env('APP_ENV') . '] Refunds of HoldCC completed', $email_msg);
//            }




        } catch(\Exception $ex) {
            $msg = ' - error : ' . $ex->getMessage() . ' [' . $ex->getCode() . ']';
            $this->error($msg);
            Helper::send_mail('tech@groomit.me', '[GROOMIT][' . env('APP_ENV') . '] Failed to hold credit card', $msg);
        }

    }
}
