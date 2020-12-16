<?php

namespace App\Http\Controllers\Admin;

use App\Lib\Helper;
use App\Model\GroomerApplicationDocument;
use App\Model\ProfitShare;
use App\Model\ProfitSharing;
use App\Model\UserFavoriteGroomer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Application;
use App\Model\ApplicationAvailability;
use App\Model\ApplicationPetPhoto;
use App\Model\ApplicationTool;
use App\Model\ApplicationHistory;
use App\Model\Groomer;
use App\Model\GroomerAvailability;
use App\Model\GroomerPetPhoto;
use App\Model\GroomerTool;
use App\Model\Tool;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Log;
use Validator;
use Redirect;
use Illuminate\Support\Facades\Input;
use Excel;


class GroomerApplicationController extends Controller
{

    public function applications(Request $request) {
        try {
            $sdate = Carbon::today()->subDays(14);
            $edate = Carbon::today()->addHours(23)->addMinutes(59);

            if (!empty($request->sdate) && empty($request->id)) {
                $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
            }

            if (!empty($request->edate) && empty($request->id)) {
                $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
            }

            $query = Application::select('id',
                'status',
                'first_name',
                'last_name',
                'email',
                'phone',
                'street',
                'city',
                'state',
                'zip',
                'cdate');
            if (!empty($sdate) && empty($request->id)) {
                $query = $query->where('cdate', '>=', $sdate);
            }

            if (!empty($edate) && empty($request->id)) {
                $query = $query->where('cdate', '<=', $edate);
            }

            if (!empty($request->status)) {
                $query = $query->where('status', $request->status);
            }

            if (!empty($request->name)) {
                $query = $query->whereRaw('LOWER(first_name) like \'%' . strtolower($request->name) . '%\' or LOWER(last_name) like \'%' . strtolower($request->name) . '%\'');
            }

            if (!empty($request->phone)) {
                $query = $query->where('phone', 'like', '%' . $request->phone . '%');
            }

            if (!empty($request->email)) {
                $query = $query->where('email', 'like', '%' .$request->email . '%');
            }

            if (!empty($request->location)) {
                $query = $query->whereRaw('LOWER(city) like \'%' . strtolower($request->location) . '%\' or LOWER(state) like \'%' . strtolower($request->location) . '%\'');
            }

            if (!empty($request->state)) {
                $query = $query->whereRaw('LOWER(state) like \'%' . strtolower($request->state) . '%\'');
            }

            $query = $query->whereNull('hide');

            if ($request->excel == 'Y') {
                $applications = $query->orderBy('cdate', 'desc')->get();
                Excel::create('applications', function($excel) use($applications) {

                    $excel->sheet('reports', function($sheet) use($applications) {

                        $data = [];
                        foreach ($applications as $a) {
                            $row = [

                                'Application ID' => $a->id,
                                'Status' => $a->status,
                                'Name' => $a->first_name . ' ' . $a->last_name,
                                'email' => $a->email,
                                'Phone' => $a->phone,
                                'Address' => $a->street . ', ' . $a->city . ', ' . $a->state . ', ' . $a->zip,
                                'Date' => $a->cdate
                            ];

                            $data[] = $row;

                        }

                        $sheet->fromArray($data);

                    });

                })->export('xlsx');

            }

            $total = $query->count();

            $applications = $query->orderBy('cdate', 'desc')
                ->paginate(20);


            $states = Helper::get_states();

            return view('admin.applications', [
                'msg' => '',
                'applications' => $applications,
                'sdate' => $sdate->format('Y-m-d'),
                'edate' => $edate->format('Y-m-d'),
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'status' => $request->status,
                'location' => $request->location,
                'states' => $states,
                'state' => $request->state,
                'total' => $total
            ]);

        } catch (\Exception $ex) {
            return back()->withErrors([
                'exception' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ])->withInput();
        }
    }

    public function application($id) {
        try {
            $application = Application::findOrFail($id);

            $application->pet_photo = ApplicationPetPhoto::where('application_id',$id)->first();

            $application->tools = DB::table('tools')
                ->select('tools.name')
                ->join('groomer_application_tools', 'tools.id', '=', 'groomer_application_tools.tool_id')
                ->where('groomer_application_tools.application_id',$id)
                ->get();

            $application->availability = ApplicationAvailability::where('application_id',$id)->get();

            $aa_all = '';
            foreach ($application->availability as $aa) {
                $aa_all .= 'wd' . $aa->weekday . '_h' . str_pad($aa->hour, 2, '0', STR_PAD_LEFT);
            }
            $application->availability = $aa_all;

            $histories = ApplicationHistory::where('application_id', $id)->orderBy('id', 'desc')->get();

            $groomers = Groomer::where('status', 'A')->where('level', '1')->orderBy('first_name', 'asc','last_name','asc')->get();

            $documents = DB::select("
                select g.type
                     , g.name as type_name
                     , d.id
                     , d.application_id
                     , d.file_name
                     , d.data
                     , d.created_by
                     , d.cdate
                  from groomer_application_document_type g 
                  left join groomer_application_document d on g.type = d.type and d.application_id = :application_id
            ", [
              'application_id' => $application->id
            ]);

            return view('admin.application', [
                'msg' => '',
                'a' => $application,
                'groomers'  => $groomers,
                'documents' => $documents,
                'histories' => $histories
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function document_upload(Request $request, $application_id) {
        try {
            $application = Application::findOrFail($application_id);

            if (empty($application)) {
                return redirect('/admin/application/' . $application_id)->with('alert', 'The application is not available.');
            }

            $admin = Auth::guard('admin')->user();
            if (empty($admin)) {
                return redirect('/admin/application/' . $application_id)->with('alert', 'Session expired!');
            }

            $modified_by = $admin->name . ' (' . $admin->admin_id . ')';

            $key = 'document_file';
            if (Input::hasFile($key)){
                if (Input::file($key)->isValid()) {
                    $path = Input::file($key)->getRealPath();

                    $contents = file_get_contents($path);
                    $name = Input::file($key)->getClientOriginalName();

                    $document_file = base64_encode($contents);

                    $gd = GroomerApplicationDocument::where('application_id', $application->id)->where('type', $request->type)->first();
                    if (empty($gd)) {
                        $gd = new GroomerApplicationDocument();
                        $gd->application_id = $application->id;
                        $gd->type = $request->type;
                    }
                    $gd->file_name  = $name;
                    $gd->data       = $document_file;
                    $gd->created_by = $modified_by;
                    $gd->cdate = Carbon::now();
                    $gd->save();

                } else {
                    DB::rollback();
                    $msg = 'Invalid document file provided';
                    return Redirect::route('admin.groomer', array('id' => $application_id))->with('alert', $msg);
                }
            }

            $msg = 'Document upload successfully !!';
            return redirect('/admin/application/' . $application_id)->with('alert', $msg);

        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . ']';

            return redirect('/admin/application/' . $application_id)->with('alert', $msg);
        }
    }

    public function application_preapprove(Request $request, $id)
    {

        DB::beginTransaction();

        try {

            $app = Application::findOrFail($id);

            ### copy application info to groomer table ###
            $gr = Groomer::where('application_id', $app->id)->first();

            if (empty($gr)) {
                $gr = new Groomer;

                $app_array = $app->toArray();
                foreach ($app_array as $k=>$v) {
                    if ($k != 'id') $gr->$k = $v;
                }
            }

            $gr->level = 2;
            $gr->status = 'P';
            $gr->cdate = Carbon::now();
            $gr->application_id = $app->id;
            $gr->save();


            DB::commit();

            return Redirect::route('admin.application', array('id' => $request->id));;

        } catch (\Exception $ex) {
            DB::rollback();

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);
        }
    }

    public function application_approve(Request $request)
    {

        DB::beginTransaction();

        try {

            $v = Validator::make($request->all(), [
                'level' => 'required',
                'id' => 'required'
            ]);

            if ($v->fails()) {
                DB::rollback();
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);
            }

            $app = Application::findOrFail($request->id);

            $gr = Groomer::where('application_id', $app->id)->first();

            if (empty($gr)) {
            ### copy application info to groomer table ###
            $gr = new Groomer;

            $app_array = $app->toArray();
            foreach ($app_array as $k=>$v) {
                if ($k != 'id') $gr->$k = $v;
            }
                $gr->cdate = Carbon::now();
            }

            $gr->level = $request->level;
            $gr->status = 'A';
            $gr->mdate = Carbon::now();
            $gr->save();

            ### change application status ###
            $app->status = 'A';
            $app->mdate = Carbon::now();
            $app->save();


            ### Schedule ###
            $aa = ApplicationAvailability::where('application_id', $request->id)
                ->orderBy('weekday','asc')
                ->orderBy('hour','asc')
                ->get();

            if ($aa) {
                foreach ($aa as $a) {
                    $a_array = $a->toArray();
                    $ga = new GroomerAvailability;

                    foreach ($a_array as $k=>$v) {

                        if ($k == 'application_id') {
                            $ga->groomer_id = $gr->groomer_id;
                        } else {
                            $ga->$k = $v;
                        }
                    }

                    $ga->save();
                }
            }


            ### groomed photo ###
            $ap = ApplicationPetPhoto::where('application_id', $request->id)->first();
            if ($ap) {
                $gp = new GroomerPetPhoto;

                $gp->groomer_id = $gr->groomer_id;
                $gp->data = $ap->data;
                $gp->save();
            }


            ### tools ###
            $at = ApplicationTool::where('application_id', $request->id)
                ->orderBy('tool_id','asc')
                ->get();

            if ($at) {
                foreach ($at as $a) {
                    $a_array = $a->toArray();
                    $gt = new GroomerTool;

                    foreach ($a_array as $k=>$v) {

                        if ($k == 'application_id') {
                            $gt->groomer_id = $gr->groomer_id;
                        } else {
                            $gt->$k = $v;
                        }
                    }

                    $gt->save();
                }
            }

            DB::commit();

            $msg = "Success";

            return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);

        } catch (\Exception $ex) {
            DB::rollback();

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);
        }
    }

    public function application_reject(Request $request)
    {

        try {

            $v = Validator::make($request->all(), [
                'reject_reason' => 'required',
                'id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);
            }

            $app = Application::find($request->id);

            ### change application status ###
            $app->status = 'R';
            $app->reject_reason = $request->reject_reason;
            $app->reject_notes = $request->reject_notes;
            $app->mdate = Carbon::now();
            $app->save();

            $msg = "Rejected";

            return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);

        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);
        }
    }

    public function application_maybe(Request $request)
    {

        try {

            $v = Validator::make($request->all(), [
                'reject_reason' => 'required',
                'id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);
            }

            $app = Application::find($request->id);

            ### change application status ###
            $app->status = 'M';
            $app->reject_reason = $request->reject_reason;
            $app->reject_notes = $request->reject_notes;
            $app->mdate = Carbon::now();
            $app->save();

            $msg = "Maybe applied !!";

            return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);

        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);
        }
    }

    public function remove(Request $request)
    {

        try {

            $v = Validator::make($request->all(), [
                'id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }


            $app = Application::findOrFail($request->id);
            $app->hide = 'Y';
            $app->mdate = Carbon::now();
            $app->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return response()->json([
                'msg' => $msg
            ]);
        }
    }


    public function status(Request $request)
    {

        try {

            $v = Validator::make($request->all(), [
              'status' => 'required',
              'id' => 'required'
            ]);

            if ($v->fails()) {
                DB::rollback();
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);
            }

            $admin = Auth::guard('admin')->user();
            if (empty($admin)) {
                return response()->json([
                  'msg' => 'Session expired!'
                ]);
            }

            $application = Application::find($request->id);

            if (!empty($application)) {
                $application->status = $request->status;
                $application->modified_by = $admin->admin_id;
                $application->mdate = Carbon::now();
                $application->update();
            }

            return Redirect::route('admin.application', array('id' => $request->id));

        } catch (\Exception $ex) {

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);
        }
    }


    public function update_status(Request $request)
    {

        try {

            $v = Validator::make($request->all(), [
              'status' => 'required',
              'id' => 'required'
            ]);

            if ($v->fails()) {
                DB::rollback();
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);
            }

            $admin = Auth::guard('admin')->user();
            if (empty($admin)) {
                return response()->json([
                  'msg' => 'Session expired!'
                ]);
            }

            $created_by = $admin->name . ' (' . $admin->admin_id . ')';

            ### copy application info to groomer table ###
            if ($request->status == '2') {
                $his = ApplicationHistory::where('application_id', $request->id)->where('status', '2')->first();
                if (empty($his)) {
                    $his = new ApplicationHistory();
                    $his->application_id = $request->id;
                    $his->status = $request->status;
                }
                $his->notes = $request->notes;
                $his->pdate = $request->pdate;
                $groomer = Groomer::find($request->groomer_id);
                if (!empty($groomer)) {
                    $his->groomer_id = $request->groomer_id;
                    $his->groomer_name = $groomer->first_name . ' ' . $groomer->last_name;
                }
                $his->amt   = $request->amt;
                $his->cdate = Carbon::now();
                $his->created_by = $created_by;
                $his->save();


                $credit_amt = ProfitSharing::where('type', 'C')->where('application_id', $request->id)->sum('groomer_profit_amt');
                if (empty($credit_amt)) $credit_amt = 0;

                $ps = new ProfitSharing();
                $ps->type = 'C';
                $ps->groomer_id = $request->groomer_id;
                $ps->groomer_profit_amt = $request->amt - $credit_amt;
                $ps->comments = 'Trial Commission. Application ID: ' . $request->id;
                $ps->application_id = $request->id;
                $ps->cdate = Carbon::now();
                $ps->created_by = $admin->admin_id;
                $ps->save();

                ProfitShare::create_credit('C'
                    , $request->groomer_id
                    , $request->amt - $credit_amt
                    , 'Trial Commission. Application ID: ' . $request->id
                    , $admin->admin_id
                );

            } else {
                $his = new ApplicationHistory();
                $his->application_id = $request->id;
                $his->status = $request->status;
                $his->notes = $request->notes;
                $his->pdate = $request->pdate;
                $his->cdate = Carbon::now();
                $his->created_by = $created_by;
                $his->save();
            }

            return Redirect::route('admin.application', array('id' => $request->id));

        } catch (\Exception $ex) {

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);
        }
    }

    public function update(Request $request) {

        DB::beginTransaction();

        try {

            $v = Validator::make($request->all(), [
                'id' => 'required',
                'first_name' => 'required',
                'mobile_phone' => 'required|regex:/^\d{10}$/',
                'email' => 'required|email'
            ]);

            if ($v->fails()) {
                DB::rollback();
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }
                return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);
            }

            ### update basic groomer information ###
            $gr = Application::findOrFail($request->id);

            $gr->dog = isset($request->dog) ? 'Y': 'N';
            $gr->cat = isset($request->cat) ? 'Y': 'N';
            $gr->first_name = $request->first_name;
            $gr->last_name = $request->last_name;
            $gr->mobile_phone = $request->mobile_phone;
            $gr->email = $request->email;
            $gr->street = $request->street;
            $gr->city = $request->city;
            $gr->state = $request->state;
            $gr->zip = $request->zip;

            $gr->groomer_exp = isset($request->groomer_exp) ? 'Y': 'N';
            $gr->groomer_exp_note = $request->groomer_exp_note;
            $gr->bather_exp = isset($request->bather_exp) ? 'Y': 'N';

            $gr->groomer_exp_years = $request->groomer_exp_years;

            $gr->mdate = Carbon::now();

            $key = 'profile_photo';
            if (Input::hasFile($key)){
                if (Input::file($key)->isValid()) {
                    $path = Input::file($key)->getRealPath();

                    Log::info('### FILE ###', [
                        'key' => $key,
                        'path' => $path
                    ]);

                    $contents = file_get_contents($path);
                    $name = Input::file($key)->getClientOriginalName();

                    $gr->profile_photo = base64_encode($contents);

                } else {
                    DB::rollback();
                    $msg = 'Invalid profile photo provided';
                    return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);
                }
            }

            $gr->save();

            DB::commit();

            $msg = "Success";

            return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);

        } catch (\Exception $ex) {
            DB::rollback();

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.application', array('id' => $request->id))->with('alert', $msg);
        }
    }

}
