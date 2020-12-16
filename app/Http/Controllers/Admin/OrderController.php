<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 1/15/19
 * Time: 9:45 AM
 */

namespace App\Http\Controllers\Admin;

use App\Lib\Helper;
use App\Model\GroomerDocument;
use App\Model\GroomerServiceArea;
use App\Model\GroomerServicePackage;
use App\Model\Message;
use App\Model\Product;
use App\Model\ProductGroomer;
use App\Model\ProductGroomerOrder;
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


class OrderController extends Controller
{

    public function index(Request $request) {
        try {
            $sdate = Carbon::today()->addDays(-30);
            $edate = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59);

            if (!empty($request->sdate) && empty($request->id)) {
                $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
            }

            if (!empty($request->edate) && empty($request->id)) {
                $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
            }

            $where_query = '1=1';

            $request->status = isset($request->status) ? $request->status : 'N';

            if (!empty($request->status)) {
                $where_query .= ' and status = \'' . $request->status . '\'';
            }
            if (!empty($request->groomer_id)) {
                $where_query .= ' and groomer_id = ' . $request->groomer_id;
            }

            $new_orders = [];
            $orders = ProductGroomerOrder::whereRaw($where_query)
                ->where('cdate', '>=', $sdate)
                ->where('cdate', '<', $edate)
                ->groupBy('groomer_id', 'delivery_company', 'tracking_no')
                ->orderBy('status', 'asc')
                ->orderBy('cdate', 'asc')
                ->paginate(20, [
                    'groomer_id',
                    'delivery_company',
                    'tracking_no',
                    DB::raw('max(status) as status'),
                    DB::raw('sum(qty) as qty')
                ]);

            foreach ($orders as $o) {
                $o->groomer = Groomer::find($o->groomer_id);
                $o->details = ProductGroomerOrder::where('groomer_id', $o->groomer_id)->where('tracking_no', $o->tracking_no)->get();

                if ($o->status == 'N') {
                    $new_orders[] = $o;
                }
            }

            $groomers = Groomer::orderBy('first_name', 'asc','last_name','asc')->get();
            $products = ProductGroomer::where('status', 'A')->orderBy('prod_type')->get();

            return view('admin.order', [
              'msg'         => '',
              'orders'      => $orders,
              'new_orders'  => $new_orders,
              'groomers'    => $groomers,
              'products'    => $products,
              'sdate'       => $sdate->format('Y-m-d'),
              'edate'       => $edate->format('Y-m-d'),
              'name'        => $request->name,
              'status'      => $request->status,
              'groomer_id'  => $request->groomer_id
            ]);

        } catch (\Exception $ex) {
            return back()->withErrors([
              'exception' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ])->withInput();
        }
    }

    public function save(Request $request) {
        Helper::log('### ORDER ### SAVE ###', $request->data);

        $admin = Auth::guard('admin')->user();
        if (empty($admin)) {
            return response()->json([
              'msg' => 'Session has been expired. Please login again!'
            ]);
        }

        $groomer = Groomer::find($request->groomer_id);
        if (empty($groomer)) {
            return response()->json([
              'code' => '-1',
              'msg' => 'Groomer Not Available',
            ]);
        }

        foreach ($request->data as $d) {
            $product_groomer = ProductGroomer::find($d['prod_id']);
            ProductGroomerOrder::add_by_admin($groomer, $product_groomer, $d['qty'], $admin);
        }

        return response()->json([
            'code' => '0',
            'msg' => 'test',
        ]);
    }

    public function update(Request $request) {
        Helper::log('### ORDER ### UPDATE ###', $request->data);

        $admin = Auth::guard('admin')->user();
        if (empty($admin)) {
            return response()->json([
              'msg' => 'Session has been expired. Please login again!'
            ]);
        }

        $order = ProductGroomerOrder::find($request->order_id);
        if (empty($order)) {
            return response()->json([
              'code' => '-1',
              'msg' => 'Groomer Not Available',
            ]);
        }

        $order->qty = $request->qty;
        $order->update();

        return response()->json([
            'code' => '0',
            'msg' => 'Updated',
        ]);
    }

    public function shipping_save(Request $request) {
        Helper::log('### ORDER SHIPPING INFO ### SAVE ###', $request->data);


        $v = Validator::make($request->all(), [
          'delivery_company' => 'required',
          'tracking_no' => 'required'
        ]);

        if ($v->fails()) {
            $msg = '';
            foreach ($v->messages()->toArray() as $k => $v) {
                $msg .= (empty($msg) ? '' : "|") . $v[0];
            }

            return response()->json([
              'code' => '-1',
              'msg' => $msg
            ]);
        }

        $str = '';
        foreach ($request->data as $d) {
            $order = ProductGroomerOrder::find($d);
            if (!empty($order)) {
                $order->status = 'S';
                $order->delivery_company = $request->delivery_company;
                $order->tracking_no = $request->tracking_no;
                $order->ship_date = Carbon::now();
                $str .= 'QTY : '. $order->qty . ' - ' . $order->prod_name . ' (' . $order->size . ') <br>' ;
                $order->save();
            }
        }

        ### Send email ###
        $groomer = Groomer::find($request->groomer_id);

        $groomer_name   = $groomer->first_name . ' ' . $groomer->last_name;
        $groomer_email  = $groomer->email;
        $groomer_tracking_no = '['.$request->delivery_company . '] ' . $request->tracking_no;
        $groomer_address = $groomer->street . ', ' . $groomer->city . ' ' . $groomer->state . ' ' . $groomer->zip;

        $data['email'] = $groomer_email;
        $data['name'] = $groomer_name;
        $data['tracking_no'] = $groomer_tracking_no;
        $data['address'] = $groomer_address;
        $data['subject'] = "Your grooming supplies have been shipped.";
        $data['detail'] = $str;

        $ret = Helper::send_html_mail('groomer/groomer-supplies-shipped', $data);

        if (!empty($ret)) {
            $msg = 'Failed to send Grooming supplies have been shipped email to Groomer';
            Helper::send_mail('it@jjonbp.com', '[Groomit][' . getenv('APP_ENV') . ']' . $msg, ' - groomer supplies shipped -  : ' . '<br> - error : ' . $ret);
        }

        ### Send email ###

        ### Send TEXT ###

        $last_id = $order->id;

        $last_order = ProductGroomerOrder::find($last_id);
        $delivery_company = $last_order->delivery_company;

        $phone = $groomer->mobile_phone;

        if($delivery_company == 'Other'){
            $message = "Dear " . $groomer->last_name . " , Your requested order has been shipped.";
        } else {
            $message = "Dear " . $groomer->last_name . " , Your requested order has been shipped. Click https://www.groomit.me/tracking/" . $last_id . " to track your order.";
        }

        $ret = Helper::send_sms($phone, $message);
        if (!empty($ret)) {
            Helper::send_mail('it@jjonbp.com', '[Groomit][' . getenv('APP_ENV') . '] Send user SMS failed : Groomer Order - Groomer ID: ' . $groomer->groomer_id . ', Order ID : ' . $last_id, $ret);
        }

        $r = new Message();
        $r->send_method = 'S';
        $r->sender_type = 'A'; // admin user
        $r->sender_id = 19;
        $r->receiver_type = 'B';
        $r->receiver_id = $request->groomer_id;
        $r->message_type = 'N'; //Notification
        $r->subject ='';
        $r->message = $message;
        $r->cdate = Carbon::now();
        $r->save();
        ### Send TEXT ###

        return response()->json([
          'code' => '0',
          'msg' => 'test',
        ]);
    }

    public function history(Request $request) {
        try {
            $sdate = Carbon::today()->addDays(-30);
            $edate = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59);

            if (!empty($request->sdate) && empty($request->id)) {
                $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
            }

            if (!empty($request->edate) && empty($request->id)) {
                $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
            }

            $where_query = '1=1';

            if (!empty($request->status)) {
                $where_query .= ' and status = \'' . $request->status . '\'';
            }
            if (!empty($request->groomer_id)) {
                $where_query .= ' and groomer_id = ' . $request->groomer_id;
            }

            $orders = ProductGroomerOrder::whereRaw($where_query)
              ->where('cdate', '>=', $sdate)
              ->where('cdate', '<', $edate)
              ->where('status', '<>', 'N')
              ->groupBy('groomer_id', 'prod_type', 'prod_name')
              ->orderBy('groomer_id', 'asc')
              ->get([
                'groomer_id',
                'prod_type',
                'prod_name',
                DB::raw('max(status) as status'),
                DB::raw('sum(qty) as qty')
              ]);

            $current_groomer_id = 0;
            foreach ($orders as $o) {
                $o->groomer = Groomer::find($o->groomer_id);
                $o->pet_num = '';
                if ($current_groomer_id !== $o->groomer_id) {
                    $o->pet_num = ProfitShare::where('groomer_id', $o->groomer_id)
                      ->where('cdate', '>=', $sdate)
                      ->where('cdate', '<=', $edate)
                      ->sum('app_pet_qty');

                    $current_groomer_id = $o->groomer_id;
                }
            }

            $groomers = Groomer::where('status', 'A')->orderBy('first_name', 'asc','last_name','asc')->get();
            $products = ProductGroomer::where('status', 'A')->orderBy('prod_type')->get();

            return view('admin.order-history', [
              'msg'         => '',
              'orders'      => $orders,
              'groomers'    => $groomers,
              'products'    => $products,
              'sdate'       => $sdate->format('Y-m-d'),
              'edate'       => $edate->format('Y-m-d'),
              'name'        => $request->name,
              'groomer_id'  => $request->groomer_id
            ]);

        } catch (\Exception $ex) {
            return back()->withErrors([
              'exception' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ])->withInput();
        }
    }

    public function show_print(Request $request) {

        $order = json_decode($request->order);

        return view('admin.order-print', [
            'order'         => $order
        ]);
    }

    public function bind_orders(Request $request) {

        $sdate = Carbon::today()->addDays(-30);
        $edate = Carbon::today()->addHours(23)->addMinutes(59)->addSeconds(59);

        if (!empty($request->sdate)) {
            $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
        }

        if (!empty($request->edate)) {
            $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
        }

        $where_query = '1=1';

        $request->status = isset($request->status) ? $request->status : 'N';

        if (!empty($request->status)) {
            $where_query .= ' and status = \'' . $request->status . '\'';
        }
        if (!empty($request->groomer_id)) {
            $where_query .= ' and groomer_id = ' . $request->groomer_id;
        }

        $new_orders = [];
        $orders = ProductGroomerOrder::whereRaw($where_query)
            ->where('cdate', '>=', $sdate)
            ->where('cdate', '<', $edate)
            ->groupBy('groomer_id', 'delivery_company', 'tracking_no')
            ->orderBy('status', 'asc')
            ->orderBy('cdate', 'asc')
            ->paginate(20, [
                'groomer_id',
                'delivery_company',
                'tracking_no',
                DB::raw('max(status) as status'),
                DB::raw('sum(qty) as qty')
            ]);

        foreach ($orders as $o) {
            $o->groomer = Groomer::find($o->groomer_id);
            $o->details = ProductGroomerOrder::where('groomer_id', $o->groomer_id)->where('tracking_no', $o->tracking_no)->get();

            if ($o->status == 'N') {
                $new_orders[] = $o;
            }
        }

        return response()->json([
            'code'          => '0',
            'msg'           => 'test',
            'orders'        => $orders,
            'new_orders'    => $new_orders,
            'sdate'       => $sdate->format('Y-m-d'),
            'edate'       => $edate->format('Y-m-d'),
            'name'        => $request->name,
            'status'      => $request->status,
            'groomer_id'  => $request->groomer_id
        ]);

    }

    public function add(Request $request) {
        Helper::log('### ORDER ### ADD ###', $request->data);

        $admin = Auth::guard('admin')->user();
        if (empty($admin)) {
            return response()->json([
                'msg' => 'Session has been expired. Please login again!'
            ]);
        }
        $groomer = Groomer::find($request->groomer_id);
        if (empty($groomer)) {
            return response()->json([
                'code' => '-1',
                'msg' => 'Groomer Not Available',
            ]);
        }
        if (!empty($request->sdate)) {
            $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
        }
        if (!empty($request->edate)) {
            $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
        }
        foreach ($request->data as $d) {
            $order = ProductGroomerOrder::where('prod_gr_id', $d['prod_id'])
                ->where('groomer_id', $request->groomer_id)
                ->where('cdate', '>=', $sdate)
                ->where('cdate', '<', $edate)
                ->where('status', 'N')
                ->get();
            if (count($order)>0) {
                return response()->json([
                    'code' => '-1',
                    'msg' => 'Can not Add This item. Please update QTY',
                ]);
            }
            $product_groomer = ProductGroomer::find($d['prod_id']);
            ProductGroomerOrder::add_by_admin($groomer, $product_groomer, $d['qty'], $admin);
        }



        $new_orders = [];
        $orders = ProductGroomerOrder::where('cdate', '>=', $sdate)
            ->where('cdate', '<', $edate)
            ->where('status', 'N')
            ->where('groomer_id', $request->groomer_id)
            ->groupBy('groomer_id', 'delivery_company', 'tracking_no')
            ->orderBy('status', 'asc')
            ->orderBy('cdate', 'asc')
            ->paginate(20, [
                'groomer_id',
                'delivery_company',
                'tracking_no',
                DB::raw('max(status) as status'),
                DB::raw('sum(qty) as qty')
            ]);

        foreach ($orders as $o) {
            $o->groomer = Groomer::find($o->groomer_id);
            $o->details = ProductGroomerOrder::where('groomer_id', $o->groomer_id)->where('tracking_no', $o->tracking_no)->get();

            if ($o->status == 'N') {
                $new_orders[] = $o;
            }
        }

        return response()->json([
            'code' => '0',
            'msg' => 'test',
            'new_orders'    => $new_orders
        ]);
    }

    public function delete(Request $request) {
        Helper::log('### ORDER ### DELETE ###');

        $admin = Auth::guard('admin')->user();
        if (empty($admin)) {
            return response()->json([
                'msg' => 'Session has been expired. Please login again!'
            ]);
        }

        $ret = ProductGroomerOrder::where('id', $request->order_id)
            ->delete();

        if ($ret < 0) {
            \Illuminate\Support\Facades\DB::rollback();
            return 'Failed to clear old profit sharing detail record';
        }

        if (!empty($request->sdate)) {
            $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
        }
        if (!empty($request->edate)) {
            $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
        }

        $new_orders = [];
        $orders = ProductGroomerOrder::where('cdate', '>=', $sdate)
            ->where('cdate', '<', $edate)
            ->where('status', 'N')
            ->where('groomer_id', $request->groomer_id)
            ->groupBy('groomer_id', 'delivery_company', 'tracking_no')
            ->orderBy('status', 'asc')
            ->orderBy('cdate', 'asc')
            ->paginate(20, [
                'groomer_id',
                'delivery_company',
                'tracking_no',
                DB::raw('max(status) as status'),
                DB::raw('sum(qty) as qty')
            ]);

        foreach ($orders as $o) {
            $o->groomer = Groomer::find($o->groomer_id);
            $o->details = ProductGroomerOrder::where('groomer_id', $o->groomer_id)->where('tracking_no', $o->tracking_no)->get();

            if ($o->status == 'N') {
                $new_orders[] = $o;
            }
        }

        return response()->json([
            'code' => '0',
            'msg' => 'test',
            'new_orders'    => $new_orders
        ]);
    }

    public function unship(Request $request) {
        Helper::log('### ORDER ### UNSHIP ###');

        $admin = Auth::guard('admin')->user();
        if (empty($admin)) {
            return response()->json([
                'msg' => 'Session has been expired. Please login again!'
            ]);
        }

        $u = Auth::guard('admin')->user();

        ProductGroomerOrder::where('delivery_company', $request->delivery_company)
            ->where('tracking_no', $request->tracking_no)
            ->update([
                'status' => 'N',
                'delivery_company' => '',
                'tracking_no' => '',
                'modified_by' => $u->name,
                'mdate' => Carbon::now()
        ]);

        return response()->json([
            'code' => '0',
            'msg' => '0'
        ]);
    }

}
