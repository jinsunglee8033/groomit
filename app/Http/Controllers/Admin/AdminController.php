<?php

namespace App\Http\Controllers\Admin;

use App\Model\AdminPrivilege;
use App\Model\AdminPrivilegeAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Model\Admin;
use Carbon\Carbon;
use DB;
use Log;
use Validator;
use Redirect;


class AdminController extends Controller
{
    public function index() {

        if (Auth::guard('admin')->check()) {
            return Redirect::route('admin.appointments');
        } else {
            return Redirect::route('admin.login');
        }

    }

    public function admins() {
        try {

            $admins = Admin::select([
                'admin_id',
                'name',
                'email',
                'group',
                'status',
                'last_login_date'
            ])->orderBy('admin_id', 'desc')
                ->get();

            $groups = DB::table('admin_privilege')->distinct()->get();

            $total = $admins->count();

            foreach ($admins as $ad) {
                $this->admin_detail($ad);
            }

            return view('admin.admins', [
                'msg' => '',
                'admins' => $admins,
                'groups' => $groups,
                'total' => $total
            ]);



        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function admin($id) {
        try {
            $admin = Admin::findOrFail($id);

            $this->admin_detail($admin);

            $groups = DB::table('admin_privilege')->groupBy('group')->get();

            return view('admin.admin', [
                'msg' => '',
                'admin' => $admin,
                'groups' => $groups
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    private function admin_detail ($admin)
    {

        switch ($admin->status) {
            case 'A':
                $admin->status_name = 'Active';
                break;
            case 'I':
                $admin->status_name = 'Inactive';
                break;
        }
    }

    public function change_admin_status ($id) {

        try {
            $admin = Admin::where('admin_id', strtolower($id))->first();
            if (empty($admin)) {
                return response()->json([
                    'msg' => 'Invalid admin information'
                ]);
            }

            if ($admin->status == 'A') {
                $admin->status = 'I';
            } else {
                $admin->status = 'A';
            }
            $admin->save();

            return Redirect::route('admin.admin', ['id' => $id]);


        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function update(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'id' => 'required',
                'name' => 'required',
                'group' => 'required'
//                'email' => 'required|email|max:255'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : " | ") . $v[0];
                }

                return Redirect::route('admin.admin', array('id' => $request->id))->with('alert', $msg);
            }

//            $check_email = Admin::where('email', '=', $request->email)
//                                ->where('admin_id', '<>', $request->id)
//                                ->get();
//
//            if (!empty($check_email)) {
//                $msg = 'Duplicated email address was provided';
//                return Redirect::route('admin.admin', array('id' => $request->id))->with('alert', $msg);
//            }

            $admin = Admin::findOrFail($request->id);
            if (empty($admin)) {
                $msg = 'Invalid admin ID provided';
                return Redirect::route('admin.admin', array('id' => $request->id))->with('alert', $msg);
            }

            $admin->name = $request->name;
            $admin->group = $request->group;
//            $admin->email = $request->email;

            $admin->save();

            $msg = "Success";

            return Redirect::route('admin.admin', array('id' => $request->id))->with('alert', $msg);

        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . ']';
            return Redirect::route('admin.admin', array('id' => $request->id))->with('alert', $msg);
        }
    }

    public function reset_password(Request $request) {

        try {

            $v = Validator::make($request->all(), [
                'id' => 'required',
                'password' => 'same:confirm_password|required|min:6'
            ]);

            if ($v->fails()) {
                DB::rollback();
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return Redirect::route('admin.admin', array('id' => $request->id))->with('alert', $msg);
            }

            ### update user password###
            $u = Admin::findOrFail($request->id);

            $u->password = bcrypt($request->password);
            $u->save();

            $msg = "Success";

            return Redirect::route('admin.admin', array('id' => $request->id))->with('alert', $msg);

        } catch (\Exception $ex) {
            DB::rollback();

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.admin', array('id' => $request->id))->with('alert', $msg);
        }
    }

    public function privilege(Request $request) {

        $query = AdminPrivilege::query();

        if (!empty($request->group)) {
            $query->where('group', $request->group);
        }

        $type = empty($request->type) ? 'B' : $request->type;

        $privileges = DB::select("
            select  p.id,
                  p.type, p.label, p.url, 
                  cs1.type as cs1_type, 
                  cs2.type as cs2_type, 
                  mg1.type as mg1_type, 
                  mg2.type as mg2_type, 
                  pt1.type as pt1_type, 
                  pt2.type as pt2_type, 
                  ship1.type as ship1_type, 
                  ship2.type as ship2_type, 
                  acct1.type as acct1_type, 
                  acct2.type as acct2_type
              from admin_privilege_action p
              left join admin_privilege cs1 on p.url = cs1.url and cs1.group = 'CS1'
              left join admin_privilege cs2 on p.url = cs2.url and cs2.group = 'CS2'
              left join admin_privilege mg1 on p.url = mg1.url and mg1.group = 'MG1'
              left join admin_privilege mg2 on p.url = mg2.url and mg2.group = 'MG2'
              left join admin_privilege pt1 on p.url = pt1.url and pt1.group = 'PT1'
              left join admin_privilege pt2 on p.url = pt2.url and pt2.group = 'PT2'
              left join admin_privilege ship1 on p.url = ship1.url and ship1.group = 'SHIP1'
              left join admin_privilege ship2 on p.url = ship2.url and ship2.group = 'SHIP2'
              left join admin_privilege acct1 on p.url = acct1.url and acct1.group = 'ACCT1'
              left join admin_privilege acct2 on p.url = acct2.url and acct2.group = 'ACCT2'
             where p.type = :type
             order by p.type asc, p.label asc
        ", [
            'type' => $type
        ]);

        return view('admin.privilege')->with([
            'privileges' => $privileges,
            'type'  => $type
        ]);
    }

    public function privilege_setup(Request $request) {

        $privilege = AdminPrivilege::where('group', $request->group)->where('url', $request->url)->first();

        if (empty($privilege)) {
            $privilege = new AdminPrivilege();
            $privilege->group = $request->group;
            $privilege->url = $request->url;
            $privilege->cdate = Carbon::now();
            $privilege->save();

        } else {
            $privilege->delete();
        }

        return response()->json([
            'code' => '0',
            'msg'  => 'updated'
        ]);
    }

    public function privilege_action_add(Request $request) {

        $privilege = new AdminPrivilegeAction();
        $privilege->type = $request->type;
        $privilege->label = $request->label;
        $privilege->url = $request->url;
        $privilege->cdate = Carbon::now();
        $privilege->status = 'A';
        $privilege->save();

        return back();
    }

    public function privilege_delete(Request $request, $id) {

        AdminPrivilege::where('id', $id)->delete();

        return back();
    }
}
