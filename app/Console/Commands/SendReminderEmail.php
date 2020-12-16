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
    protected $signature = 'send:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Reminder Emails';

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
            $tomorrow = Carbon::tomorrow()->format('Y-m-d');

//            $apps = AppointmentList::whereRaw("(DATE(accepted_date) = '" . $today . "' OR DATE(accepted_date) = '" . $tomorrow . "')")
//                ->where('status', 'D')
//                ->get();


            $apps = DB::select("
                            select
                                a.*
                            from appointment_list a
                                inner join address b on a.address_id = b.address_id
                            where
                              (DATE(accepted_date) = '" . $today . "' OR DATE(accepted_date) = '" . $tomorrow . "')
                              and b.state != 'CA'
                              and a.status = 'D'
                        ");
            //and (b.state = 'NY' or b.state = 'NJ')

//            $this->info("1. Total appointment reminder : " . count($apps));
//            $bar = $this->output->createProgressBar(count($apps));

            if (!empty($apps)) {

                foreach ($apps as $app) {

                    $send_mail = true;
                    $today_reminder = false;
                    $tomorrow_reminder = false;
                    $groomer_name = '';
                    $groomer_email = '';
                    $subject = '';
                    $address = '';
                    $app_date = '';
                    $app_time = '';
                    $message = '';

                    $user = User::find($app->user_id);
                    if (empty($user)) {
                        $send_mail = false;
                    }

                    $groomer = Groomer::find($app->groomer_id);
                    if (empty($user)) {
                        $send_mail = false;
                    }


                    if ($app->accepted_date) {
                        $app_date = Carbon::parse($app->accepted_date);
                        $app_time = $app_date->format('g:i A');

                        if ($app_date->format('Y-m-d') == $today) {
                            $today_reminder = true;
                        } else {
                            $tomorrow_reminder = true;
                        }
                    } else {
                        $send_mail = false;
                    }

                    if ($app->groomer_id && $send_mail == true) {
                        $groomer = Groomer::findOrFail($app->groomer_id);
                        if (!empty($groomer)) {
                            $groomer_name = $groomer->first_name . ' ' . $groomer->last_name;
                            $groomer_email = $groomer->email;
                        } else {
                            $send_mail = false;
                        }
                    }

                    $addr = Address::find($app->address_id);
                    if (!empty($addr) && $send_mail == true) {
                        if(!empty( $addr->address2 ) && ( $addr->address2  != '')) {
                            $address = $addr->address1 . ' # ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->state;
                        }else {
                            $address = $addr->address1 . ', ' . $addr->city . ', ' . $addr->state;
                        }

                    } else {
                        $send_mail = false;
                    }

                    $pets = DB::select("
                        select 
                            a.pet_id,
                            c.name as pet_name,
                            c.dob as pet_dob,
                            c.age as pet_age,
                            b.prod_name as package_name,
                            a.amt as price,
                            c.breed,
                            c.size,
                            c.dob,
                            c.special_note as note,
                            c.type
                        from appointment_pet p 
                            inner join appointment_product a on p.appointment_id = a.appointment_id
                            inner join product b on a.prod_id = b.prod_id
                            inner join pet c on p.pet_id = c.pet_id
                        where a.appointment_id = :appointment_id
                        and a.pet_id = p.pet_id
                        and b.prod_type = 'P'
                        and b.pet_type = c.type
                    ", [
                        'appointment_id' => $app->appointment_id
                    ]);

                    $data = [];

                    if ($tomorrow_reminder) {

                        $subject = "You have an upcoming Groomit Appointment tomorrow.";
                        $data['reminder_type'] = "tomorrow";
                        $message = 'Just a reminder of your upcoming Groomit Appointment tomorrow ' . $app_time;

                        ### send push ###
                        if (!empty($user->device_token) && (trim($user->device_token) != '') ) {
                            $payload = [
                                'type' => 'A',
                                'id' => $app->appointment_id
                            ];

                            $error = Helper::send_notification('groomit', $message, $user->device_token, $subject, $payload);
                            if (!empty($error)) {
                                Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Push Notification to User[Appt ID:' .$app->appointment_id .'] failed - reminder tomorrow', 'Push Notification Error: ' . $error . '[' . $user->device_token . ']' );
                            }

                        } else {
                            Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Push Notification to User[Appt ID:' .$app->appointment_id .'] failed - reminder tomorrow', 'Push Notification Error: No device token found');
                        }

                        ### end send push

                        ### send text ###
                        if (!empty($user->phone)) {
                            $phone = $user->phone;
                            $ret = Helper::send_sms($phone, $message);
                            if (!empty($ret)) {
                                Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Send  SMS to USER failed : reminder tomorrow - User ID: ' . $app->user_id . ', Appointment ID : ' . $app->appointment_id, $ret);
                            }

                            Message::save_sms_to_user($message, $user, $app->appointment_id);
                        }

                        if (!empty($groomer->mobile_phone)) {
                            $phone = $groomer->mobile_phone;
                            $ret = Helper::send_sms($phone, $message);
                            if (!empty($ret)) {
                                Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Send  SMS to GROOMER failed - reminder tomorrow - Groomer ID: ' . $app->groomer_id . ', Appointment ID : ' . $app->appointment_id, $ret);
                            }
                        }
                        ### end send text ###

                    } elseif ($today_reminder) {
                        $subject = "You have an upcoming Groomit Appointment today.";
                        $data['reminder_type'] = "today";
                        $message = 'Just a reminder of your upcoming Groomit Appointment today ' . $app_time;

                        ### send push ###
                        if (!empty($user->device_token) && (trim($user->device_token) != '') )  {

                            $payload = [
                                'type' => 'A',
                                'id' => $app->appointment_id
                            ];

                            $error = Helper::send_notification('groomit', $message, $user->device_token, $subject, $payload);
                            if (!empty($error)) {
                                Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Push Notification to User[Appt ID:' .$app->appointment_id .'] failed - reminder today', 'Push Notification Error: ' . $error . '[' . $user->device_token . ']' );
                            }
                        } else {
                            Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Push Notification to User[Appt ID:' .$app->appointment_id .'] failed - reminder today', 'Push Notification Error: No device token found');
                        }

                        ### end send push

                        ### send text ###
                        if (!empty($user->phone)) {
                            $phone = $user->phone;
                            $ret = Helper::send_sms($phone, $message);
                            if (!empty($ret)) {
                                Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Send SMS to USER failed : Covid19 Intro - User ID: ' . $app->user_id . ', Appointment ID : ' . $app->appointment_id, $ret);
                            }

                            Message::save_sms_to_user($message, $user, $app->appointment_id);

                            //Send Covid19 Intro TEXT.
                            $message2="Health is on all of our minds now more than ever. As stay-at-home orders begin to lift, we're accelerating efforts to help safeguard pet owners and groomers. Learn more https://bit.ly/2WlWMdg ";
                            $ret = Helper::send_sms($phone, $message2);
                            if (!empty($ret)) {
                                Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Send SMS to USER failed : Covid19 Intro - User ID: ' . $app->user_id . ', Appointment ID : ' . $app->appointment_id, $ret);
                            }

                            Message::save_sms_to_user($message2, $user, $app->appointment_id);

                            //$ret = Helper::send_sms('2015752308', $message2);
                        }

                        if (!empty($groomer->mobile_phone)) {
                            $phone = $groomer->mobile_phone;
                            $ret = Helper::send_sms($phone, $message);
                            if (!empty($ret)) {
                                Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Send SMS to GROOMER failed - reminder today - Groomer ID: ' . $app->groomer_id . ', Appointment ID : ' . $app->appointment_id, $ret);
                            }
                        }
                        ### end send text ###

                    } else {
                        $send_mail = false;
                    }


                    if ($send_mail) {
                        ### send email ###

                        $data['email'] = $user->email;
                        $data['name'] = $user->first_name;
                        $data['subject'] = $subject;
                        $data['groomer'] = $groomer_name;
                        $data['address'] = $address;
                        $data['referral_code'] = $user->referral_code;
                        $data['accepted_date'] = $app_date->format('l, F j Y, h:i A');

                        foreach ($pets as $k => $v) {
                            $data['pet'][$k]['pet_name'] = $v->pet_name;
                            $data['pet'][$k]['package_name'] = $v->package_name;


                            $data['pet'][$k]['breed_name'] = empty($v->size) ? '' : Size::findOrFail($v->size)->size_name;
                            $data['pet'][$k]['size_name'] = empty($v->breed) ? '' : Breed::findOrFail($v->breed)->breed_name;
                            $data['pet'][$k]['dob'] = $v->dob;

                            if (!empty($v->pet_age)) {
                                $data['pet'][$k]['age'] = ($v->pet_age > 0 ? $v->pet_age . ' year' : ''). ($v->pet_age > 1 ? 's old' : ' old');
                            } else {
                                $dob = Carbon::parse($v->pet_dob);
                                $data['pet'][$k]['age'] = $dob->age < 2 ? $dob->diffInMonths(Carbon::now()) . " months old" : $dob->age . " years old";
                            }

                            $data['pet'][$k]['note'] = $v->note;

                            $data['pet'][$k]['addon'] = '';
                            $addon = DB::select("
                                select
                                    b.prod_name
                                from appointment_product a
                                    inner join product b on a.prod_id = b.prod_id
                                where a.appointment_id = :appointment_id
                                and a.pet_id = :pet_id
                                and b.prod_type = 'A'
                                and b.pet_type = :pet_type
                            ", [
                                'appointment_id' => $app->appointment_id,
                                'pet_id' => $v->pet_id,
                                'pet_type' => $v->type
                            ]);

                            if (!empty($addon)) {
                                foreach ($addon as $a) {
                                    $data['pet'][$k]['addon'] .= '[' . $a->prod_name . '] ';
                                }
                            }
                        }

                        $referral_arr = UserProcessor::get_referral_code($user->user_id);
                        $data['referral_code'] = $referral_arr['referral_code'];
                        $data['referral_amount'] = $referral_arr['referral_amount'];

                        ### user ###########
                        $ret = Helper::send_html_mail('reminder', $data);

                        if (!empty($ret)) {
                            Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Send reminder email failed - User ID: ' . $app->user_id . ', Appointment ID : ' . $app->appointment_id, $ret);
                        }

                        // save message
                        $r = Helper::save_message('reminder_user', '', $app->user_id, $app->appointment_id, '', $message);
                        if (!empty($r)) {
                            Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Send reminder email - save message failed - User ID: ' . $app->user_id . ', Appointment ID : ' . $app->appointment_id, $r);
                        }
                        // end save message
                        #######################



                        ### groomer ###########
                        $data['email'] = $groomer_email;
                        $data['name'] = $groomer_name;
                        $ret_groomer = Helper::send_html_mail('reminder_for_groomer', $data);

                        if (!empty($ret_groomer)) {
                            Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Send reminder email failed - Groomer ID: ' . $app->groomer_id . ', Appointment ID : ' . $app->appointment_id, $ret_groomer);
                        }

                        // save message
                        $r = Helper::save_message('reminder_groomer', '', $app->groomer_id, $app->appointment_id, '', $message);
                        if (!empty($r)) {
                            Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Send reminder email - save message failed - Groomer ID: ' . $app->groomer_id . ', Appointment ID : ' . $app->appointment_id, $r);
                        }
                        // end save message

                        $data['email'] = 'tech@groomit.me';
                        $data['name'] = 'TECH';
                        $ret_admin = Helper::send_html_mail('reminder_for_admin', $data);

                        #######################


                        ### end send email ###
                    }
                }
            }

//            $bar->finish();
//            $this->info(" - Done...");

        } catch (\Exception $ex) {
            $msg = ' - error : ' . $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString();
            $this->error($msg);
            Helper::send_mail($tech_email, '[Groomit][' . getenv('APP_ENV') . '] Send reminder email failed', $msg);
        }
    }
}
