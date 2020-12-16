<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Model\Contact;
use App\Model\Reply;
use Carbon\Carbon;
use DB;
use Log;
use Validator;
use Redirect;
use App\Lib\Helper;


class ContactController extends Controller
{

    public function contacts(Request $request) {
        try {

            $sdate = Carbon::today()->subMonths(2);
            $edate = Carbon::today()->addHours(23)->addMinutes(59);

            if (!empty($request->sdate) && empty($request->id)) {
                $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
            }

            if (!empty($request->edate) && empty($request->id)) {
                $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
            }

            $query = Contact::select('contact_id',
                'user_id',
                'first_name',
                'last_name',
                'email',
                'phone',
                'subject',
                'status',
                'cdate',
                'type');
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

            if ($request->excel == 'Y') {
                $contacts = $query->orderBy('cdate', 'desc')->get();
                Excel::create('contacts', function($excel) use($contacts) {

                    $excel->sheet('reports', function($sheet) use($contacts) {

                        $data = [];
                        foreach ($contacts as $a) {
                            $row = [

                                'Contact ID' => $a->contact_id,
                                'User ID' => $a->user_id,
                                'Name' => $a->first_name . ' ' . $a->last_name,
                                'email' => $a->email,
                                'Phone' => $a->phone,
                                'Subject' => $a->subject,
                                'Message' => $a->message,
                                'Status' => $a->status,
                                'Date' => $a->cdate
                            ];

                            $data[] = $row;

                        }

                        $sheet->fromArray($data);

                    });

                })->export('xlsx');

            }

            $total = $query->count();

            $contacts = $query->orderBy('cdate', 'desc')
                ->paginate(20);


            foreach ($contacts as $c) {

                switch ($c->status) {
                    case 'N':
                        $c->status_name = 'New';
                        break;
                    case 'A':
                        $c->status_name = 'Answered';
                        break;
                    case 'C':
                        $c->status_name = 'Closed';
                        break;
                }
            }

            return view('admin.contacts', [
                'msg' => '',
                'contacts' => $contacts,
                'sdate' => $sdate->format('Y-m-d'),
                'edate' => $edate->format('Y-m-d'),
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'status' => $request->status,
                'subject' => $request->subject,
                'total' => $total
            ]);



        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function contact($id) {
        try {
            $contact = Contact::findOrFail($id);

            switch ($contact->status) {
                case 'N':
                    $contact->status_name = 'New';
                    break;
                case 'A':
                    $contact->status_name = 'Answered';
                    break;
                case 'C':
                    $contact->status_name = 'Closed';
                    break;
            }

            $reply = Reply::select([
                'admin.name',
                'reply.message',
                'reply.cdate'])
                ->where('reply.contact_id', $id)
                ->leftJoin('admin', 'admin.admin_id', '=', 'reply.admin_id')
                ->orderBy('reply.cdate', 'desc')
                ->get();

            return view('admin.contact', [
                'msg' => '',
                'c' => $contact,
                'reply' => $reply
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }

    }

    public function reply(Request $request) {

        try {
            $v = Validator::make($request->all(), [
                'id' => 'required',
                'message' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return Redirect::route('admin.contact', array('id' => $request->id))->with('alert', $msg);
            }


            if ($auth = Auth::guard('admin')->user()) {
                $admin_id = $auth->admin_id;
            } else {
                $msg = 'Admin auth required';
                return Redirect::route('admin.contact', array('id' => $request->id))->with('alert', $msg);
            }

            DB::beginTransaction();

            $c = Contact::findOrFail($request->id);
            $c->status = 'A'; ## N: new | A: answered | C: closed
            $c->save();

            $r = New Reply;
            $r->contact_id = $request->id;
            $r->message = $request->message;
            $r->admin_id = $admin_id;
            $r->cdate = Carbon::now();
            $r->save();

            ## send email
//            $subject = "Re: " . $c->subject;
//            $msg = $request->message;
//            $ret = Helper::send_mail($c->email, $subject, $msg);
//            if (!empty($ret)) {
//                DB::rollback();
//                $msg = 'Email was not sent';
//                return Redirect::route('admin.contact', array('id' => $request->id))->with('alert', $msg);
//            }

            $data['email'] = $c->email;
            $data['name'] = $c->first_name . ' ' . $c->last_name;
            $data['subject'] = "Re: " . $c->subject;
            $data['message'] = $request->message;

            $ret = Helper::send_html_mail('contact_reply', $data);

            if (!empty($ret)) {
                DB::rollback();
                $msg = 'Failed to send reply email';
                return Redirect::route('admin.contact', array('id' => $request->id))->with('alert', $msg);
            }


            DB::commit();

            $msg = "Success";

            return Redirect::route('admin.contact', array('id' => $request->id))->with('alert', $msg);

        } catch (\Exception $ex) {
            DB::rollback();

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.contact', array('id' => $request->id))->with('alert', $msg);
        }
    }

    public function close(Request $request) {

        try {
            $v = Validator::make($request->all(), [
                'id' => 'required',
                'note' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return Redirect::route('admin.contact', array('id' => $request->id))->with('alert', $msg);
            }

            if ($auth = Auth::guard('admin')->user()) {
                $admin_id = $auth->admin_id;
            } else {
                $msg = 'Admin auth required';
                return Redirect::route('admin.contact', array('id' => $request->id))->with('alert', $msg);
            }

            $c = Contact::findOrFail($request->id);
            $c->note = $request->note;
            $c->status = 'C'; // N: new | A: answered | C: closed
            $c->mdate = Carbon::now();
            $c->modified_by = $admin_id;
            $c->save();

            $msg = "Success";

            return Redirect::route('admin.contact', array('id' => $request->id))->with('alert', $msg);

        } catch (\Exception $ex) {

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.contact', array('id' => $request->id))->with('alert', $msg);
        }
    }

    public function update(Request $request) {

        try {
            $v = Validator::make($request->all(), [
                'id' => 'required',
                'status' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                return Redirect::route('admin.contact', array('id' => $request->id))->with('alert', $msg);
            }

            $c = Contact::findOrFail($request->id);
            $c->status = $request->status; // N: new | A: answered | C: closed
            $c->save();

            $msg = "Success";

            return Redirect::route('admin.contact', array('id' => $request->id))->with('alert', $msg);

        } catch (\Exception $ex) {

            $msg = $ex->getMessage() . ' (' . $ex->getCode() . ') : ' . $ex->getTraceAsString();

            return Redirect::route('admin.contact', array('id' => $request->id))->with('alert', $msg);
        }
    }
}
