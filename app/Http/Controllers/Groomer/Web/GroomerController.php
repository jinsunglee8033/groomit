<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 3/20/19
 * Time: 2:07 PM
 */

namespace App\Http\Controllers\Groomer\Web;

use App\Lib\eversign;
use App\Model\Groomer;
use App\Model\GroomerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Model\Admin;
use Carbon\Carbon;
use DB;
use Log;
use Validator;
use Redirect;
use Illuminate\Support\Facades\Input;


class GroomerController extends Controller
{
    public function index() {

//        if (Auth::guard('groomer')->check()) {
            $groomer = Auth::guard('groomer')->user();

            $documents = DB::select("
                select g.type
                     , g.name as type_name
                     , d.id
                     , d.groomer_id
                     , d.file_name
                     , d.data
                     , d.signed
                     , d.locked
                     , d.e_doc_id
                     , d.esign_url
                     , d.created_by
                     , d.cdate
                  from groomer_document_type g 
                  left join groomer_document d on g.type = d.type and d.status = 'A' and d.groomer_id = :groomer_id
                 where g.type in ('A', 'G', 'J')
                 order by g.type
            ", [
              'groomer_id' => $groomer->groomer_id
            ]);

            $document_others = DB::select("
                select g.type
                     , g.name as type_name
                     , d.id
                     , d.groomer_id
                     , d.file_name
                     , d.data
                     , d.signed
                     , d.locked
                     , d.created_by
                     , d.cdate
                  from groomer_document_type g 
                  left join groomer_document d on g.type = d.type and d.groomer_id = :groomer_id
                 where g.type in ('U')
            ", [
              'groomer_id' => $groomer->groomer_id
            ]);

            return view('groomer.index')->with([
                'groomer' => $groomer,
                'documents' => $documents,
                'document_others' => $document_others
            ]);
//        } else {
//            return Redirect::route('groomer.login');
//        }
    }

    public function esign(Request $request, $type) {

        $groomer = Auth::guard('groomer')->user();

        $result = null;

//        $groomer = Groomer::find(34);
        switch ($type) {
            case 'A':
                $result = eversign::post_ach($request, $groomer->groomer_id);
                break;
            case 'G':
                $result = eversign::post_agreement($groomer->groomer_id);
                break;
            case 'J':
                $result = eversign::post_w9($groomer->groomer_id);
                break;
        }

        if (empty($result) || (isset($result->success) &&  $result->success == false)) {
            return response()->json([
              'code'      => '-1',
              'msg'       => 'Your request has not processed properly. Please try it again. [SUC:FALSE]'
            ]);
        }

        return response()->json([
            'code'      => '0',
            'esign_url' => $result->esign_url,
            'doc_id'    => $result->doc_id,
            'msg'       => ''
        ]);
    }

    public function complete($doc_id) {
        $doc = GroomerDocument::find($doc_id);

        if (!empty($doc)) {
            $doc->file_name = 'complete . ' . \Carbon\Carbon::now();
            $doc->signed = 'Y';
            $doc->update();
        }

        return Redirect::route('groomer');
    }

    public function declined($doc_id) {
        $doc = GroomerDocument::find($doc_id);

        if (!empty($doc)) {
            $doc->status = 'C';
            $doc->update();
        }

        return Redirect::route('groomer');
    }

    public function document_upload(Request $request) {
        try {
            $groomer_id = $request->groomer_id;

            $groomer = Groomer::findOrFail($groomer_id);

            if (empty($groomer)) {
                return Redirect::route('groomer')->with('alert', 'The groomer is not available.');
            }

            $key = 'document_file';
            if (Input::hasFile($key)){
                if (Input::file($key)->isValid()) {
                    $path = Input::file($key)->getRealPath();

                    $contents = file_get_contents($path);
                    $name = Input::file($key)->getClientOriginalName();

                    $document_file = base64_encode($contents);

                    $gd = GroomerDocument::where('groomer_id', $groomer->groomer_id)->where('type', $request->type)->first();
                    if (empty($gd)) {
                        $gd = new GroomerDocument();
                        $gd->groomer_id = $groomer->groomer_id;
                        $gd->type = $request->type;
                    }
                    $gd->file_name = $name;
                    $gd->data = $document_file;
                    $gd->signed = 'N';
                    $gd->locked = 'N';
                    $gd->cdate = Carbon::now();
                    $gd->save();

                } else {
                    DB::rollback();
                    $msg = 'Invalid document file provided';
                    return Redirect::route('groomer')->with('alert', $msg);
                }
            }

            $msg = 'Document upload successfully !!';
            return redirect('/groomer')->with('alert', $msg);

        } catch (\Exception $ex) {
            $msg = $ex->getMessage() . ' [' . $ex->getCode() . ']';

            return Redirect::route('groomer')->with('alert', $msg);
        }
    }
}
