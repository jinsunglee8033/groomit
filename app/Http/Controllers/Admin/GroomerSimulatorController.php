<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 12/21/18
 * Time: 4:15 AM
 */

namespace App\Http\Controllers\Admin;

use App\Model\GroomerServiceArea;
use App\Model\GroomerServicePackage;
use App\Model\ProfitShare;
use App\Model\ProfitSharing;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Groomer;
use App\Model\GroomerAvailability;
use App\Model\GroomerPetPhoto;
use App\Model\GroomerTool;
use App\Model\AppointmentList;
use App\Model\User;
use App\Model\Address;
use App\Model\Constants;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Log;
use Validator;
use Redirect;
use Excel;
use Illuminate\Support\Facades\Input;


class GroomerSimulatorController extends Controller
{
    public function index($groomer_id) {
        $groomer = Groomer::find($groomer_id);

        $email_token = \Crypt::encrypt($groomer->email);
        return view('admin.groomer-simulator')->with([
            'groomer_id' => $groomer_id,
            'groomer' => $groomer,
            'email' => $groomer->email,
            'token' => $email_token
        ]);
    }
}