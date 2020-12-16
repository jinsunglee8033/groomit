<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Groomer extends Model
{
    protected $table = 'groomer';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'groomer_id';

    public function status_name() {
        switch ($this->status) {
            case 'I':
                return 'Inactive';
            case 'N':
                return 'New';
            case 'A':
                return 'Active';
            case 'D':
                return 'Deleted';
            case 'P':
                return 'PreApproval';
            default:
                return $this->status;
        }
    }

    public function background_status_name() {
        switch ($this->background_check_status) {
            case 'G':
                return 'Background Checks Progress';
            case 'R':
                return 'Background Checks Rejected';
            case 'A':
                return 'Background Checks Approved';
            case 'P':
                return 'Background Checks Pending';
            case 'V':
                return 'Video Trial Scheduled ';
            case 'I':
                return 'InPerson Trial Scheduled';
            default:
                return $this->background_check_status;
        }
    }

    public function getForgotPwdKeyExpiredAttribute() {
        $set_date = $this->attributes['forgot_pwd_set_date'];
        $set_date = Carbon::parse($set_date);

        if ($set_date->copy()->addHour()->lt(Carbon::now())) {
            return true;
        }

        return false;
    }

    public static function get_num_of_appointment($groomer_id, $year, $month) {
        $vw = VWAppointmentGroomerMonthly::where('groomer_id', $groomer_id)->where('y', $year)->where('m', $month)->first();

        if (empty($vw)) return '';

        return $vw->qty;
    }

    public static function get_document_status($groomer_id, $type) {
        $doc = GroomerDocument::where('groomer_id', $groomer_id)->where('type', $type)->first();

        if (empty($doc)) return 'N';

        return 'Y';
    }

    public static function get_last_groom_date($groomer_id) {
        $app = AppointmentList::where('status', 'P')->where('groomer_id', $groomer_id)->orderBy('accepted_date', 'desc')->first();

        if (empty($app)) return '';

        return $app->accepted_date;
    }
}
