<?php

namespace App\Http\Controllers\Admin;

use App\Lib\AppointmentProcessor;
use App\Lib\CreditProcessor;
use App\Lib\ProfitSharingProcessor;
use App\Lib\PromoCodeProcessor;
use App\Lib\UserProcessor;
use App\Model\Adjust;
use App\Model\AllowedZip;
use App\Model\Appointment;
use App\Model\AppointmentLog;
use App\Model\AppointmentPet;
use App\Model\AppointmentProduct;
use App\Model\GroomerAvailability;
use App\Model\GroomerServiceArea;
use App\Model\GroomerServicePackage;
use App\Model\Product;
use App\Model\ProductDenom;
use App\Model\ProfitShare;
use App\Model\PromoCode;
use App\Model\UserBilling;
use App\Model\UserBlockedGroomer;
use App\Model\UserFavoriteGroomer;
use App\Model\VWAppointmentSchedule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\AppointmentList;
use App\Model\AppointmentPhoto;
use App\Model\User;
use App\Model\Groomer;
use App\Model\Address;
use App\Model\PetPhoto;
use App\Model\Constants;
use App\Model\Message;
use App\Model\Pet;
use App\Model\Breed;
use App\Model\Size;
use App\Model\TaxZip;
use App\Model\Credit;
use App\Model\CCTrans;
use App\Model\Times;
use App\Model\VWGroomerAssignLog;

use Carbon\Carbon;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use DateTime;
use stdClass;
use App\Lib\Helper;
use Illuminate\Support\Facades\Auth;
use App\Lib\Converge;


class AppointmentController extends Controller
{
    public function show(Request $request) {

        $sdate = Carbon::today()->subDays(30);
        $edate = Carbon::today()->addDays(334);

        if (!empty($request->sdate)) {
            $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
        }

        if (!empty($request->edate)) {
            $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
        }

        $sort_by = $request->sort_by;
        $sort_asdc = $request->sort_asdc;
        if (empty($sort_by)) {
            $sort_by = 'reserved_date';
        }
        if (empty($sort_asdc)) {
            $sort_asdc = 'desc';
        }

        $query = AppointmentList::leftJoin('user', 'user.user_id', '=', 'appointment_list.user_id')
            ->leftJoin('user_stat', function($join){  $join->on('user.user_id', '=', 'user_stat.user_id')   ; })
            ->leftJoin('address', 'address.address_id', '=', 'appointment_list.address_id')
            ->leftJoin('groomer', 'groomer.groomer_id', '=', 'appointment_list.groomer_id')
            ->leftJoin('promo_code', DB::raw('promo_code.code'), '=', DB::raw('appointment_list.promo_code'))
            ->where('reserved_date', '>=', $sdate)
            ->where('reserved_date', '<=', $edate);

        if (!empty($request->sdate2)) {
            $query = $query->where('appointment_list.accepted_date', '>=', Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate2 . ' 00:00:00'));
        }

        if (!empty($request->edate2)) {
            $query = $query->where('appointment_list.accepted_date', '<=', Carbon::createFromFormat('Y-m-d H:i:s', $request->edate2 . ' 23:59:59'));
        }

        if (!empty($request->sdate3)) {
            $query = $query->where('appointment_list.cdate', '>=', Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate3 . ' 00:00:00'));
        }

        if (!empty($request->edate3)) {
            $query = $query->where('appointment_list.cdate', '<=', Carbon::createFromFormat('Y-m-d H:i:s', $request->edate3 . ' 23:59:59'));
        }

        $status = $request->get('status', 'N');

        if ($status != 'all') {
            $query = $query->where('appointment_list.status', $status);
        }

        if (!empty($request->groomer)) {
            $query = $query->where('appointment_list.groomer_id', $request->groomer);
        }

        if (!empty($request->name)) {
            $query = $query->whereRaw("lower(concat(user.first_name, ' ', user.last_name)) like ?", ['%' . strtolower($request->name) . '%']);
        }

        if (!empty($request->phone)) {
            $query = $query->whereRaw('user.phone like ?', ['%' . $request->phone . '%']);
        }

        if (!empty($request->email)) {
            $query = $query->whereRaw("lower(user.email) like ?", ['%' . strtolower($request->email) . '%']);
        }

        if (!empty($request->appointment_id)) {
            $query = $query->where('appointment_list.appointment_id', $request->appointment_id);
        }

        if (!empty($request->pet_type)) {
            $query = $query->whereRaw("f_check_pet_type(appointment_list.appointment_id, ?) > 0", [strtolower($request->pet_type)]);
        }

        if (!empty($request->promo_type)) {
            $query = $query->where('promo_code.type', $request->promo_type);
        }

        switch ($request->booked) {
            case 'B':
                //$query = $query->whereRaw("f_get_appointment_booked_cnt(appointment_list.user_id) > 0");
                $query = $query->whereRaw("user_stat.book_cnt > 0");
                break;
            case 'O':
                //$query = $query->whereRaw("f_get_appointment_booked_cnt(appointment_list.user_id) = 1");
                $query = $query->whereRaw("user_stat.book_cnt = 1 ");
                break;
            case 'M':
                //$query = $query->whereRaw("f_get_appointment_booked_cnt(appointment_list.user_id) > 1");
                $query = $query->whereRaw("user_stat.book_cnt > 1 ");
                break;
        }

        if (!empty($request->package_id)) {
            $query = $query->whereRaw("appointment_list.appointment_id in (select appointment_id from appointment_product where prod_id = ? group by appointment_id)", [$request->package_id]);
        }

        if (!empty($request->order_from)) {
            $query = $query->where('appointment_list.order_from', $request->order_from);
        }

        if (!empty($request->county)) {
            $query = $query->whereRaw("address.zip in (select zip 
                   from allowed_zip
                   where concat(county_name, '/', state_abbr) = '" . $request->county . "'
                   and lower(available) = 'x')");
        }

        if (!empty($request->state)) {
            $query = $query->whereRaw("address.zip in (select zip 
                   from allowed_zip
                   where state_abbr = '" . $request->state . "'
                   and lower(available) = 'x')");
        }

        if ($request->excel == 'Y') {

            if ($sort_by == 'accepted_date') {
                $query->orderBy(DB::raw("if(appointment_list.accepted_date is null, '2999-12-31', appointment_list.accepted_date)"), $sort_asdc)
                    ->orderBy('cdate', 'asc');
            }else if ($sort_by == 'groomer_name') {
                $query->orderBy('groomer.first_name', $sort_asdc, 'groomer.last_name', $sort_asdc );
            } else {
                $query->orderBy('appointment_list.' . $sort_by, $sort_asdc);
            }

            $appointments = $query->selectRaw("
                    appointment_list.*,
                    concat(user.first_name, ' ', user.last_name) as user_name,
                    user.phone as user_phone,
                    user.email,
                    f_get_pet_type(appointment_list.appointment_id) as pet_type,
                    f_get_package_name(appointment_list.appointment_id) as package,
                    user_stat.book_cnt as booked_cnt  
                ")
                ->get();
                  //f_get_appointment_booked_cnt(appointment_list.user_id) as booked_cnt

            foreach ($appointments as $a) {
                $a->fav_groomers = Groomer::whereRaw(
                  'groomer_id in (select groomer_id from user_favorite_groomer where user_id = ' . $a->user_id . ')'
                )->get();

                $a->fav_gs = '';
                if (!empty($a->fav_groomers)) {
                    foreach($a->fav_groomers as $g) {
                        if ($a->fav_gs == '') {
                            $a->fav_gs = $g->first_name . ' ' . $g->last_name;
                        } else {
                            $a->fav_gs .= ',' . $g->first_name . ' ' . $g->last_name;
                        }
                    }
                }

                $vgl = VWGroomerAssignLog::where('appointment_id', $a->appointment_id)
                  ->where('groomer_id', $a->groomer_id)
                  ->first([
                    'groomer_assign_date',
                    DB::raw('TIMESTAMPDIFF(MINUTE, cdate, groomer_assign_date) as assign_diff')
                  ]);
                if (!empty($vgl)) {
                    $a->groomer_assign_date = $vgl->groomer_assign_date;
                    $a->assign_diff = $vgl->assign_diff;
                }
            }

            Excel::create('appointments', function($excel) use($appointments) {

                $excel->sheet('reports', function($sheet) use($appointments) {

                    $data = [];
                    foreach ($appointments as $a) {

                        $user_id = $a->user_id;

                        $row = [
                            'ID' => $a->appointment_id,
                            'Order.Date' => $a->cdate,
                            'Service.Date' => $a->accepted_date,
                            'Requested.Date' => $a->reserved_date,
                            'Assigned.Date' => $a->groomer_assign_date,
                            'Assigned Min' => $a->assign_diff,
                            'Status' => $a->status_name,
                            'Pet.Type' => $a->pet_type,
                            'Package' => $a->package,
                            'Promo.type' => $a->promo_type,
                            'Sub.Total' => '$' . number_format($a->sub_total, 2),
                            'Promo.Amount' => '$' . number_format($a->promo_amt, 2),
                            'Charged.Amount' => '$' . number_format($a->total, 2),
                            'UserName' => $a->user_name ,
                            'UserID' => $a->user_id,
                            'Phone' => $a->user_phone,
                            'Email' => $a->email,
                            'Address' => $a->address,
                            'Groomer' => $a->groomer_name,
                            'Favorite' => $a->fav_gs,
                            'Assigned' => $a->assigned_by,
                            'Rating' => $a->rating,
                            'Booked.Count' => $a->booked_cnt,
                            'Booked.Total($)' => number_format($a->sum_total,2),
                            'Booked.Avg($)'=> ($a->booked_cnt>0) ? number_format($a->sum_total/$a->booked_cnt,2) : 0
                        ];

                        $data[] = $row;

                    }

                    $sheet->fromArray($data);

                });

            })->export('xlsx');

        }

        $total = $query->count();

        if ($sort_by == 'accepted_date') {
            $query->orderBy(DB::raw("if(appointment_list.accepted_date is null, '2999-12-31', appointment_list.accepted_date)"), $sort_asdc)
              ->orderBy('cdate', 'asc');
        }else if ($sort_by == 'groomer_name') {
            $query->orderBy('groomer.first_name', $sort_asdc, 'groomer.last_name', $sort_asdc );
        } else {
            $query->orderBy('appointment_list.' . $sort_by, $sort_asdc);
        }
        $appointments = $query->selectRaw("
                appointment_list.*,
                concat(user.first_name, ' ', user.last_name) as user_name,
                user.phone as user_phone,
                f_get_pet_type(appointment_list.appointment_id) as pet_type,
                f_get_package_name(appointment_list.appointment_id) as package,
                user_stat.book_cnt as booked_cnt   ,
                user_stat.sum_total as sum_total 
            ")->paginate(50);
             //f_get_appointment_booked_cnt(appointment_list.user_id) as booked_cnt
        foreach ($appointments as $a) {
            $a->fav_groomers = Groomer::whereRaw(
              'groomer_id in (select groomer_id from user_favorite_groomer where user_id = ' . $a->user_id . ')'
            )->get();

            $vgl = VWGroomerAssignLog::where('appointment_id', $a->appointment_id)
                ->where('groomer_id', $a->groomer_id)
                ->first([
                    'groomer_assign_date',
                    DB::raw('TIMESTAMPDIFF(MINUTE, cdate, groomer_assign_date) as assign_diff')
                ]);
            if (!empty($vgl)) {
                $a->groomer_assign_date = $vgl->groomer_assign_date;
                $a->assign_diff = $vgl->assign_diff;
            }
        }

        $groomers = //Groomer::whereNotIn('status',['I','D'])->orderBy('first_name', 'asc')->get();
                    Groomer::orderBy('first_name', 'asc','last_name','asc')->get();
        $states = Helper::get_states();

        $counties = DB::select("
            select distinct county_name, state_abbr
            from allowed_zip
            where lower(available) = 'x' 
            order by 2, 1
        ");

        return view('admin.appointments', [
            'msg' => '',
            'appointments' => $appointments,
            'date' => '',
            'sdate' => $sdate->format('Y-m-d'),
            'edate' => $edate->format('Y-m-d'),
            'sdate2' => $request->sdate2,
            'edate2' => $request->edate2,
            'sdate3' => $request->sdate3,
            'edate3' => $request->edate3,
            'today' => Carbon::today()->format('Y-m-d'),
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'appointment_id' => $request->appointment_id,
            'groomer' => $request->groomer,
            'status' => $status,
            'location' => $request->location,
            'total' => $total,
            'appointment_status' => Constants::$appointment_status,
            'pet_type' => $request->pet_type,
            'promo_type' => $request->promo_type,
            'booked' => $request->booked,
            'rescheduled' => $request->rescheduled,
            'county' => $request->county,
            'state' => $request->state,
            'groomers' => $groomers,
            'alert' => session('alert'),
            'package_id' => $request->package_id,
            'states' => $states,
            'counties' => $counties,
            'order_from' => $request->order_from,
            'sort_by' => $sort_by,
            'sort_asdc' => $sort_asdc
        ]);

    }

    public function appointment($id) {

        try {

//            $ap = AppointmentList::where('appointment_id', $id)
//                ->first();

            $ap = AppointmentList::leftJoin('groomer', function($join) {
                 $join->on( 'appointment_list.my_favorite_groomer', '=', 'groomer.groomer_id');
                 })
                ->leftJoin('user_stat', function($join){
                    $join->on('appointment_list.user_id', '=', 'user_stat.user_id')   ;  })
                ->where('appointment_id', $id)
                  ->select( 'appointment_list.*', 'groomer.first_name', 'groomer.last_name','user_stat.book_cnt as booked_cnt'  ,'user_stat.sum_total'  )
                  ->first();

            $ap_first = DB::select("
                            select appointment_id, min(cdate) min_cdate
                            from appointment_list_log
                            where appointment_id = :appointment_id
                            group by 1
                        ", [
                'appointment_id' => $id
            ]);

            $ret = DB::select("
                            select a.place_id, a.other_name, b.place_name
                            from appointment_place a left join place b on a.place_id = b.place_id
                            where a.appointment_id = :appointment_id
                        ", [
                'appointment_id' => $id
            ]);

            if ( count($ret) > 0) {
                $ap->place_id = $ret[0]->place_id;
                $ap->place_name = $ret[0]->place_name;
                $ap->other_place_name = $ret[0]->other_name;
            }


            $ap->status_name = '';
            if (array_key_exists($ap->status, Constants::$appointment_status)) {
                $ap->status_name = Constants::$appointment_status[$ap->status];
            }

//            $reserved_at= Carbon::parse($ap->reserved_at);
//            $ap->reserved_at = $reserved_at->format('m/d/Y g:i a');

            $cdate = Carbon::parse($ap->cdate);
            if( is_array($ap_first) && count($ap_first)>0){ //if exist appointment_list
                $ap_first = $ap_first[0];
                $cdate = Carbon::parse($ap_first->min_cdate);
            }

            $ap->cdate = $cdate->format('m/d/Y g:i a');
            $org_accepted_date = '';
            if ($ap->accepted_date) {
                $org_accepted_date = $ap->accepted_date;
                $accepted_date = Carbon::parse($ap->accepted_date);
                $weekday = $accepted_date->dayOfWeek;
                $hour = $accepted_date->hour;
                $hours = array($hour, $hour+1, $hour+2, $hour+3);

                $ga_date = $accepted_date->format('Y-m-d');
                $ap->accepted_date = $accepted_date->format('m/d/Y g:i a');
                $sql_date = $accepted_date;

            } else {
                $sql_date = $ap->reserved_date;
                $reserved_date = Carbon::parse($ap->reserved_date);

                $ga_date = $reserved_date->format('Y-m-d');
                $weekday = $reserved_date->dayOfWeek;
                $hour = $reserved_date->hour;
                $hours = array($hour, $hour+1, $hour+2, $hour+3);
            }

            # because the availability week's format starts from Monday ot Sunday #
            if ($weekday == 0) {
                $weekday = 6;
            } else {
                $weekday = $weekday - 1;
            }


            $ap->groomer = Groomer::where('groomer_id', $ap->groomer_id)->select('groomer_id', 'first_name', 'last_name', 'profile_photo', 'phone')->first();
            if (!empty($ap->groomer)) {
                if (!empty($ap->groomer->photo)) {
                    $ap->groomer->photo = base64_encode($ap->groomer->photo);
                }

//                $ret = DB::select("
//                            select avg(rating) avg_rating
//                            from appointment_list
//                            where groomer_id = :groomer_id
//                            and status = 'P'
//                        ", [
//                    'groomer_id' => $ap->groomer_id
//                ]);
//
//                $ap->groomer->overall_rating = 0;
//                if (count($ret) > 0) {
//                    $ap->groomer->overall_rating = $ret[0]->avg_rating;
//                }

                $ap->groomer->average_rating = AppointmentList::where('groomer_id', $ap->groomer_id)
                    ->where('status', 'P')
                    //->whereNotNull('rating')
                    ->avg('rating');

                $ap->groomer->rating_qty = AppointmentList::where('groomer_id', $ap->groomer_id)
                    ->where('status', 'P')
                    ->whereNotNull('rating')
                    ->count();

                $ap->groomer->total_appts = AppointmentList::where('groomer_id', $ap->groomer_id)
                    ->where('status', 'P')
                    ->count();
                $ap->groomer->overall_rating = $ap->groomer->average_rating;

            }

            $ap->user = User::where('user_id', $ap->user_id)->first();


            $addr = Address::find($ap->address_id);
            if (!empty($addr)) {
                if( !empty($addr->address2) && ($addr->address2 != '' ) ){
                    $ap->address = $addr->address1 . ' # ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
                }else {
                    $ap->address = $addr->address1 . ', ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
                }

            }

            $pet_type = 'dog';

            $ap->pets = DB::select("
                    select 
                        a.pet_id, 
                        p.size_id, 
                        p.sub_total, 
                        p.tax, 
                        p.total, 
                        p.groomer_note,
                        c.name as pet_name,
                        c.dob as pet_dob,
                        c.age as pet_age,
                        c.breed,
                        c.special_note,
                        c.type,
                        timestampdiff(month, c.dob, curdate()) as age,
                        b.prod_id as package_id,
                        b.prod_name as package_name,
                        a.amt as price
                    from appointment_pet p 
                        inner join appointment_product a on p.appointment_id = a.appointment_id
                        inner join product b on a.prod_id = b.prod_id
                        inner join pet c on p.pet_id = c.pet_id
                    where a.appointment_id = :appointment_id
                    and a.pet_id = p.pet_id
                    and b.prod_type = 'P'
                    and b.pet_type = c.type
                    and b.pet_type = :pet_type
                    ", [
                'appointment_id' => $ap->appointment_id,
                'pet_type' => $pet_type
            ]);

            if (empty($ap->pets)) {

                $pet_type = 'cat';

                $ap->pets = DB::select("
                    select 
                        a.pet_id, 
                        p.sub_total, 
                        p.tax, 
                        p.total, 
                        c.name as pet_name,
                        c.dob as pet_dob,
                        c.age as pet_age,
                        c.type,
                        c.special_note,
                        timestampdiff(month, c.dob, curdate()) as age,
                        b.prod_id as package_id,
                        b.prod_name as package_name,
                        a.amt as price,
                        p.groomer_note
                    from appointment_pet p 
                        inner join appointment_product a on p.appointment_id = a.appointment_id
                        inner join product b on a.prod_id = b.prod_id
                        inner join pet c on p.pet_id = c.pet_id
                    where a.appointment_id = :appointment_id
                    and a.pet_id = p.pet_id
                    and b.prod_type = 'P'
                    and b.pet_type = c.type
                    and b.pet_type = :pet_type
                    ", [
                    'appointment_id' => $ap->appointment_id,
                    'pet_type' => $pet_type
                ]);
            }

            foreach ($ap->pets as $p) {

                $ap->package = $p->package_id;

                if (!empty($p->pet_age)) {
                    $p->age = ($p->pet_age > 0 ? $p->pet_age . ' year' : ''). ($p->pet_age > 1 ? 's old' : ' old');
                } else {
                    $year = intval($p->age / 12);
                    $month = intval($p->age % 12);
                    $p->age = ($year > 0 ? $year . ' year' : ''). ($year > 1 ? 's ' : ' ') . $month . ' month' . ($month > 1 ? 's' : '');
                }

                if ($p->type != 'cat') {
                    $p->addons = DB::select("
                            select
                                b.prod_id,
                                b.prod_name,
                                a.amt as price,
                                a.created_by,
                                a.cdate
                            from appointment_product a
                                inner join product b on a.prod_id = b.prod_id
                            where a.appointment_id = :appointment_id
                            and a.pet_id = :pet_id
                            and b.prod_type = 'A'
                            and b.pet_type = :pet_type
                        ", [
                        'appointment_id' => $ap->appointment_id,
                        'pet_id' => $p->pet_id,
                        'pet_type' => $p->type
                    ]);

                    $p->addon_array = [];
                    foreach($p->addons as $a) {
                        $p->addon_array[] = $a->prod_id;
                    }

                    $p->shampoo = DB::select("
                        select 
                            b.prod_id,
                            b.prod_name,
                            a.amt as price
                        from appointment_product a
                            inner join product b on a.prod_id = b.prod_id
                        where a.appointment_id = :appointment_id
                        and a.pet_id = :pet_id
                        and b.prod_type = 'S'
                        and b.pet_type = :pet_type
                    ", [
                        'appointment_id' => $ap->appointment_id,
                        'pet_id' => $p->pet_id,
                        'pet_type' => $p->type
                    ]);

                    $p->breed = Breed::find($p->breed);
                    $p->size = Size::find($p->size_id);
                } else {
                    $p->addons = DB::select("
                            select
                                b.prod_id,
                                b.prod_name,
                                a.amt as price,
                                a.created_by,
                                a.cdate
                            from appointment_product a
                                inner join product b on a.prod_id = b.prod_id
                            where a.appointment_id = :appointment_id
                            and a.pet_id = :pet_id
                            and b.prod_type = 'A'
                            and b.pet_type = :pet_type
                        ", [
                        'appointment_id' => $ap->appointment_id,
                        'pet_id' => $p->pet_id,
                        'pet_type' => $p->type
                    ]);

                    $p->addon_array = [];
                    foreach($p->addons as $a) {
                        $p->addon_array[] = $a->prod_id;
                    }
                }

                $p->photos = PetPhoto::where('pet_id', $p->pet_id)->get();

                foreach ($p->photos as $photo) {
                    $photo->photo = base64_encode($photo->photo);
                }

                if (count($p->photos) > 0) {
                    $p->photo = $p->photos[0]->photo;
                }

                $p->before_image = AppointmentPhoto::where('appointment_id', $ap->appointment_id)
                    ->where('pet_id', $p->pet_id)
                    ->where('type', 'B')
                    ->select('image')
                    ->first();

                if (!empty($p->before_image)) {
                    $p->before_image = base64_encode($p->before_image->image);
                }

                $p->after_image = AppointmentPhoto::where('appointment_id', $ap->appointment_id)
                    ->where('pet_id', $p->pet_id)
                    ->where('type', 'A')
                    ->select('image')
                    ->first();

                if (!empty($p->after_image)) {
                    $p->after_image = base64_encode($p->after_image->image);
                }
            }

            ## get available groomers ##

//            # available Groomers based on the available schedule #
//
//            if ($pet_type == 'cat') {
//                $groom_pet = " and g.cat = 'Y' ";
//            } else {
//                $groom_pet = " and g.dog = 'Y' ";
//            }
//
//            if ($ap->status == 'N') {
//                $groomers = DB::select("
//                    select g.*
//                    from groomer as g
//                        inner join groomer_availability as ga on ga.groomer_id = g.groomer_id
//                    where g.status IN ('N', 'A')
//                    and g.groomer_id not in (select groomer_id from user_blocked_groomer where user_id = ". $ap->user_id .")
//                    and ga.weekday = ". $weekday ."
//                    and ga.hour in (". implode(',', $hours) .")
//                    and ga.date = '". $ga_date. "'
//                    " . $groom_pet . "
//                    group by g.groomer_id
//                    ");
//            }
//
//            if ($ap->status == 'D' || $ap->status == 'O' || $ap->status == 'W' || $ap->status == 'R') {
//                $groomers = DB::select("
//                    select g.*
//                    from groomer as g
//                        inner join groomer_availability as ga on ga.groomer_id = g.groomer_id
//                    where g.status IN ('N', 'A')
//                    and g.groomer_id not in (select groomer_id from user_blocked_groomer where user_id = ". $ap->user_id .")
//                    and ga.weekday = ". $weekday ."
//                    and ga.hour = ". $hour . "
//                    and ga.date = '". $ga_date. "'
//                    " . $groom_pet . "
//                    group by g.groomer_id
//                    ");
//
//            }
//
//            # unavailable Groomers based on the appointment #
//
//            $unavailable_groomers = DB::select("
//                    select g.*, DATE_FORMAT(a.accepted_date,'%c/%e/%Y %l:%i %p') as accepted_date
//                    from groomer as g
//                        inner join appointment_list as a on a.groomer_id = g.groomer_id
//                    where
//                    (
//                    (a.status in ('D', 'O', 'W') and ABS(TIMESTAMPDIFF(HOUR, a.accepted_date, '" . $sql_date . "')) < 1)
//                    or
//                    (a.status = 'N' and ABS(TIMESTAMPDIFF(HOUR, a.reserved_date, '" . $ap->reserved_date . "')) < 1)
//                    )
//                    and a.appointment_id <> :appointment_id
//                ", [
//                'appointment_id' => $ap->appointment_id
//            ]);
//
//            $unavailable_ids = array();
//            $groomer_ids = array();
//
//            if (!empty($groomers)) {
//
//                // remove unavailable groomers from the groomers list
//                foreach ($groomers as $k=>$v) {
//
//                    $groomer_ids[] = $v->groomer_id;
//
//
//                    $v->groom_pet = ($v->dog == 'Y') ? 'Dog' : '';
//
//                    if ($v->cat == 'Y') {
//                        $v->groom_pet = (!empty($v->groom_pet)) ? $v->groom_pet . ', ' : $v->groom_pet ;
//                        $v->groom_pet .= 'Cat';
//                    }
//                }
//
//                if (!empty($unavailable_groomers)) {
//
//                    foreach ($unavailable_groomers as $g) {
//                        $unavailable_ids[] = $g->groomer_id;
//                    }
//
//                    // remove unavailable groomers from the groomers list
//                    foreach ($groomers as $k=>$v) {
//                        if (in_array($v->groomer_id, $unavailable_ids)) {
//                            unset($groomers[$k]);
//                        }
//                    }
//
//                    // remove unavailable groomers from the unavailable groomers list
//                    // when unavailable groomer is not in the groomer list
//                    foreach ($unavailable_groomers as $k=>$v) {
//                        if (!in_array($v->groomer_id, $groomer_ids)) {
//                            unset($unavailable_groomers[$k]);
//                        }
//
//                        $v->groom_pet = ($v->dog == 'Y') ? 'Dog' : '';
//
//                        if ($v->cat == 'Y') {
//                            $v->groom_pet = (!empty($v->groom_pet)) ? $v->groom_pet . ', ' : $v->groom_pet ;
//                            $v->groom_pet .= 'Cat';
//                        }
//                    }
//                }
//
//            } else {
//                if (!empty($unavailable_groomers)) {
//                    foreach ($unavailable_groomers as $k=>$v) {
//                        if (!in_array($v->groomer_id, $groomer_ids)) {
//                            unset($unavailable_groomers[$k]);
//                        }
//
//                        $v->groom_pet = ($v->dog == 'Y') ? 'Dog' : '';
//
//                        if ($v->cat == 'Y') {
//                            $v->groom_pet = (!empty($v->groom_pet)) ? $v->groom_pet . ', ' : $v->groom_pet ;
//                            $v->groom_pet .= 'Cat';
//                        }
//                    }
//                }
//            }

            ### New Start ###

            ## get available groomers ##
            $pet_type = 'dog';

            $pets = DB::select("
                    select 
                        a.pet_id
                    from appointment_pet p 
                        inner join appointment_product a on p.appointment_id = a.appointment_id
                        inner join product b on a.prod_id = b.prod_id
                    where a.appointment_id = :appointment_id
                    and b.prod_type = 'P'
                    and b.pet_type = 'cat'
                    ", [
                'appointment_id' => $ap->appointment_id
            ]);

            if (!empty($pets)) {
                $pet_type = 'cat';
            }
            # available Groomers based on the available schedule #

            if ($pet_type == 'cat') {
                $groom_pet = " and g.cat = 'Y' ";
            } else {
                $groom_pet = " and g.dog = 'Y' ";
            }

            ## calculate groomer schedule availability

            $groomers = null;

            if ($ap->status == 'N') {
                $groomers = DB::select("
                    select g.*
                    from groomer as g 
                        inner join groomer_availability as ga on ga.groomer_id = g.groomer_id
                    where g.status IN ('N', 'A')
                    and ga.weekday = ". $weekday ." 
                    and ga.hour in (". implode(',', $hours) .")
                    and ga.date = '". $ga_date. "'
                    " . $groom_pet . "
                    group by g.groomer_id
                    ");
            }

            if ($ap->status == 'D' || $ap->status == 'O' || $ap->status == 'W' || $ap->status == 'R') {
                $groomers = DB::select("
                    select g.*
                    from groomer as g 
                        inner join groomer_availability as ga on ga.groomer_id = g.groomer_id
                    where g.status IN ('N', 'A')
                    and ga.weekday = ". $weekday ." 
                    and ga.hour = ". $hour . "
                    and ga.date = '". $ga_date. "'
                    " . $groom_pet . "
                    group by g.groomer_id
                    ");

            }

            ## unavailable Groomers based on the appointment ##

            # 1. today's appointments
            $today_appointments = DB::select("
                    select 
                    g.groomer_id, 
                    g.first_name, 
                    g.last_name, 
                    g.level,
                    g.cat,
                    g.dog,
                    DATE_FORMAT(a.accepted_date,'%c/%e/%Y %l:%i %p') as accepted_date,
                    TIMESTAMPDIFF(MINUTE, '" . $sql_date . "', a.accepted_date) as time_diff,
                    a.appointment_id
                    from groomer as g 
                        inner join appointment_list as a on a.groomer_id = g.groomer_id
                    where a.status in ('D', 'O', 'W') 
                    and DATE(a.accepted_date) = '" . $ga_date . "'
                    " . $groom_pet . "
                    order by 2,3, 7
                ");

            $unavailable_ids = [];
            $groomer_appointments = [];
            $current_total_minutes = 0;

            # 2. calculate total service time(minutes) for each appointment
            if (!empty($today_appointments)) {
                foreach($today_appointments as $k=>$a) {

                    $app_pets = DB::select("
                    select
                        a.pet_id,
                        c.type as pet_type,
                        c.size as pet_size,
                        b.prod_id as package_id,
                        b.prod_name as package_name
                    from appointment_pet p
                        inner join appointment_product a on p.appointment_id = a.appointment_id
                        inner join product b on a.prod_id = b.prod_id
                        inner join pet c on p.pet_id = c.pet_id
                    where a.appointment_id = :appointment_id
                    and a.pet_id = p.pet_id
                    and b.prod_type = 'P'
                    and b.pet_type = c.type
                    ", [
                        'appointment_id' => $a->appointment_id
                    ]);

                    // Calculate total minutes to takes depends on the pet and size of pet
                    if (!empty($app_pets)) {
                        $total_minutes = 0;
                        foreach ($app_pets as $o) {

                            if ($o->pet_type == 'cat') { // 1.5 hr for each cat grooming
                                # cat
                                $total_minutes += 90;
                            } else {
                                #dog
                                if ($o->package_id == '2') {
                                    // silver  : 1 hr, regardless of size
                                    $total_minutes += 60;
                                } else {
                                    // gold : different by size
                                    if ($o->pet_size == 4 || $o->pet_size == 5) { // large, extra large : 2 hr
                                        $total_minutes += 120;
                                    } else { // other size : 1.5 hr
                                        $total_minutes += 90;
                                    }
                                }
                            }

                            if ($a->appointment_id == $ap->appointment_id) {
                                $current_total_minutes = $total_minutes;
                            }
                        }

                        if ($a->appointment_id == $ap->appointment_id) {
                            // remove current appointment
                            unset($today_appointments[$k]);
                        } else {
                            $groomer_appointments[$a->appointment_id]['total_minutes'] = $total_minutes;
                            $groomer_appointments[$a->appointment_id]['time_diff'] = $a->time_diff;
                            $groomer_appointments[$a->appointment_id]['groomer_id'] = $a->groomer_id;
                        }


                    }

                }

                # 3. take out unavailable groomers
                foreach($groomer_appointments as $o) {
                    if (($o['time_diff'] < 0 && ($o['time_diff'] + $o['total_minutes']) > 0)
                        || ($o['time_diff'] > 0 && $o['time_diff'] < $current_total_minutes)
                        || $o['time_diff'] == 0)
                    {
                        $unavailable_ids[] = $o['groomer_id'];
                    }
                }
            }


            $groomer_ids = [];
            $unavailable_groomers = [];

            if (!empty($groomers)) {

                // remove unavailable groomers from the groomers list
                foreach ($groomers as $k=>$v) {

                    $groomer_ids[] = $v->groomer_id;

                    $v->groom_pet = ($v->dog == 'Y') ? 'Dog' : '';

                    if ($v->cat == 'Y') {
                        $v->groom_pet = (!empty($v->groom_pet)) ? $v->groom_pet . ', ' : $v->groom_pet ;
                        $v->groom_pet .= 'Cat';
                    }
                }

                // remove unavailable groomers from the groomers list
                foreach ($groomers as $k=>$v) {
                    if (in_array($v->groomer_id, $unavailable_ids)) {
                        unset($groomers[$k]);
                    }
                }

                if (!empty($today_appointments)) {

                    // get unavailable groomers
                    foreach ($today_appointments as $k=>$v) {
                        if (!in_array($v->groomer_id, $unavailable_ids)) {
                            unset($today_appointments[$k]);
                        }

                        $unavailable_groomers = $today_appointments;

                        $v->groom_pet = ($v->dog == 'Y') ? 'Dog' : '';

                        if ($v->cat == 'Y') {
                            $v->groom_pet = (!empty($v->groom_pet)) ? $v->groom_pet . ', ' : $v->groom_pet ;
                            $v->groom_pet .= 'Cat';
                        }
                    }
                }
            }

            ### New End ###

            $status = Constants::$appointment_status;
            $selectable_status = [];
            foreach ($status as $key => $val) {

                Helper::log('### KEY ###', $key);
                Helper::log('### VAL ###', $val);

                if (!in_array($key, ['P', 'F'])) {
                    $selectable_status[$key] = $val;
                    /*$selectable_status[] = [
                        "$key" => "$val"
                    ];*/
                }
            }

            if (empty($groomers)) {
                $groomers = [];
            }

            $payments = UserBilling::where('user_id', $ap->user_id)
                ->orderBy('status', 'asc')
                ->get();
            if (empty($payments)) {
                $payments = '';
            }

            $sizes = Size::all();
            $packages = Product::where('prod_type', 'P')
                ->where('pet_type', $pet_type)
                ->where('status','A')
                ->get(); // show only dog package for now

            //::join('product_denom', 'product.prod_id' ,  '=', 'product_denom.prod_id' )
            $shampoos = Product::Join('product_denom', function($join){  $join->on('product.prod_id', '=', 'product_denom.prod_id')
                                                                           ->where('product_denom.status', '=', 'A')
                                                                           ->where('product_denom.group_id','=', 1);
                                                                           //->whereRaw('product_denom.size_id is null');
                                                                           //->whereIsNull('product_denom.size_id')  ;
                                                                     })
                ->where('prod_type', 'S')
                ->where('pet_type', $pet_type)
                ->whereIn('product.status',['A'])
                ->orderBy('product.prod_id', 'asc')
                ->select( 'product.prod_id','product.pet_type' ,'product.prod_type','product.prod_name','product.prod_desc','product.status','product_denom.denom')
                ->get();

            $add_ons = DB::select("
                select
                    a.prod_id,
                    a.prod_type,
                    a.prod_name,
                    a.prod_desc,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom,
                    b.min_denom,
                    b.max_denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where a.prod_type = 'A'
                and a.status = 'A'
                and b.status = 'A'
                and a.pet_type = :pet_type
                group by a.prod_id
                order by a.seq
            ", [
                'zip' => isset($addr) ? $addr->zip : '',
                'pet_type' => $pet_type
            ]);


            $allowed_admin = in_array(Auth::guard('admin')->user()->email, ['jin@jjonbp.com','jun@jjonbp.com']);

            # get reschedule history
            $update_history = DB::select("
                select 
                    status, 
                    reserved_at,
                    DATE_FORMAT(accepted_date, '%m/%d/%Y %h:%i %p') as accepted_date, 
                    groomer_id, 
                    modified_by, 
                    DATE_FORMAT(min(mdate), '%m/%d/%Y %h:%i %p') as mdate,
                    adate
                from appointment_list_log
                where appointment_id = :appointment_id
                group by 1,2,3,4,5               
                order by 7
            ", [
            'appointment_id' => $ap->appointment_id
            ]);

            $ap_log = AppointmentLog::where('appointment_id', $ap->appointment_id)
                ->orderBy('adate', 'desc')
                ->first();



            foreach($update_history as $o) {
                $o->status_name = Constants::$appointment_status[$o->status];
                $o->groomer_name = null;
                if (!empty($o->groomer_id)) {
                    $groomer = Groomer::find($o->groomer_id);
                    if (!empty($groomer)) {
                        $o->groomer_name = $groomer->fist_name . ' ' . $groomer->last_name;
                    }
                }
            }

            $groomer = Groomer::find($ap->groomer_id);
            $groomer_name = '';
            if (!empty($groomer)) {
                $groomer_name = $groomer->first_name . ' ' . $groomer->last_name;
            }

            $current_log = new \stdClass();
            $current_log->status = $ap->status;
            $current_log->reserved_at = $ap->reserved_at;
            $current_log->accepted_date = empty($ap->accepted_date) ? '' : Carbon::parse($ap->accepted_date)->format('m/d/Y h:i A');
            $current_log->groomer_id = $ap->groomer_id;
            $current_log->modified_by = $ap->modified_by;
            $current_log->status_name = Constants::$appointment_status[$ap->status];
            $current_log->groomer_name = $groomer_name;
            $current_log->mdate = empty($ap->mdate) ? '' : Carbon::parse($ap->mdate)->format('m/d/Y h:i:s A');
            $current_log->adate = isset($ap_log) ? Carbon::parse($ap_log->adate)->format('m/d/Y h:i:s A') : '';

            $update_history[] = $current_log;

            $op_notes = AppointmentList::where('user_id', $ap->user_id)
                ->where('appointment_id', '!=', $ap->appointment_id)
                ->whereRaw("trim(ifnull(op_note, '')) != ''")
                ->select('appointment_id', 'op_note', 'cdate')
                ->orderBy('appointment_id', 'desc')
                ->get();

            $allowed_zip = AllowedZip::where('zip', $addr->zip)->first();
            $county = empty($allowed_zip) ? null : ($allowed_zip->county_name . '.' . $allowed_zip->state_abbr);

            $ap->fav_groomers = Groomer::whereRaw('groomer_id in (select groomer_id from user_favorite_groomer where user_id = ' . $ap->user_id . ')')->get();
            if (!empty($ap->fav_groomers) && count($ap->fav_groomers) > 0) {
                foreach ($ap->fav_groomers as $fav) {
                    $fav->service_package = GroomerServicePackage::where('groomer_id', $fav->groomer_id)->where('status','A')
                        ->whereRaw('prod_id in (select prod_id from appointment_product where appointment_id = ' . $ap->appointment_id . ')')
                        ->first();
                    $fav->service_area = GroomerServiceArea::where('groomer_id', $fav->groomer_id)->where('county', $county)->first();
                }
            }

            $ap->blocked_groomers = Groomer::whereRaw('groomer_id in (select groomer_id from user_blocked_groomer where user_id = ' . $ap->user_id . ')')->get();

            $ap->cc_trans = CCTrans::where('appointment_id', $ap->appointment_id)
                ->where('amt', '!=', '0.01')
                //->where('type', 'S')
                ->orderBy('id', 'desc')->get();

            $ap->estimated_earning = ProfitSharingProcessor::getEstimatedProfit($ap->appointment_id);
            $ap->estimated_bonus = ProfitSharingProcessor::getEstimatedBonus($ap->appointment_id);

            $times = Helper::get_time_windows();

            //New Cancel fee
//            if ($ap->accepted_date) {
//                $app_date = $ap->accepted_date;
//            } else {
//                $app_date = $ap->reserved_date;
//            }
//            $cancelfee_rates = 100;
//            if ($app_date < Carbon::now()->addHours(12)) {
//                $cancelfee_rates = 100;
//            }else if ($app_date < Carbon::now()->addHours(24)) {
//                $cancelfee_rates = 50;
//            }else {
//                $cancelfee_rates = 0;
//            }
//            $cwf_charge_amt =  round( ($ap->sub_total - $ap->promo_amt ) * ($cancelfee_rates/100), 2) ;
//            $cwf_groomer_commission_amt = round($cwf_charge_amt * 0.65, 2 ); //
            $cwf_charge_amt =  35.00 ;
            $cwf_groomer_commission_amt = 30.00 ; //

            return view('admin.appointment', [
                'msg' => '',
                'ap' => $ap,
                'time_windows' => $times,
                'status' => $selectable_status,
                'groomers' => $groomers,
                'unavailable_groomers' => $unavailable_groomers,
                'payments' => $payments,
                'sizes' => $sizes,
                'packages' => $packages,
                'shampoos' => $shampoos,
                'addons' => $add_ons,
                'update_history' => $update_history,
                'op_notes' => $op_notes,
                'allowed_admin' => $allowed_admin , // to show Send Service Completion Email
                'cwf_charge_amt' => $cwf_charge_amt,
                'cwf_groomer_commission_amt' => $cwf_groomer_commission_amt
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);
        }

    }

    public function appointment_invoice($id) {

        try {

            $ap = AppointmentList::where('appointment_id', $id)
              ->first();


            $ap->status_name = '';
            if (array_key_exists($ap->status, Constants::$appointment_status)) {
                $ap->status_name = Constants::$appointment_status[$ap->status];
            }

//            $reserved_at= Carbon::parse($ap->reserved_at);
//            $ap->reserved_at = $reserved_at->format('m/d/Y g:i a');

            $cdate = Carbon::parse($ap->cdate);
            $ap->cdate = $cdate->format('m/d/Y g:i a');
            $org_accepted_date = '';
            if ($ap->accepted_date) {
                $org_accepted_date = $ap->accepted_date;
                $accepted_date = Carbon::parse($ap->accepted_date);
                $weekday = $accepted_date->dayOfWeek;
                $hour = $accepted_date->hour;
                $hours = array($hour, $hour+1, $hour+2, $hour+3);

                $ga_date = $accepted_date->format('Y-m-d');
                $ap->accepted_date = $accepted_date->format('m/d/Y g:i a');
                $sql_date = $accepted_date;

            } else {
                $sql_date = $ap->reserved_date;
                $reserved_date = Carbon::parse($ap->reserved_date);

                $ga_date = $reserved_date->format('Y-m-d');
                $weekday = $reserved_date->dayOfWeek;
                $hour = $reserved_date->hour;
                $hours = array($hour, $hour+1, $hour+2, $hour+3);
            }

            # because the availability week's format starts from Monday ot Sunday #
            if ($weekday == 0) {
                $weekday = 6;
            } else {
                $weekday = $weekday - 1;
            }


            $ap->groomer = Groomer::where('groomer_id', $ap->groomer_id)->select('groomer_id', 'first_name', 'last_name', 'profile_photo', 'phone')->first();
            if (!empty($ap->groomer)) {
                if (!empty($ap->groomer->photo)) {
                    $ap->groomer->photo = base64_encode($ap->groomer->photo);
                }

                $ret = DB::select("
                            select avg(rating) avg_rating, count(*) total_appts
                            from appointment_list
                            where groomer_id = :groomer_id
                            and status = 'P'
                        ", [
                  'groomer_id' => $ap->groomer_id
                ]);

                $ap->groomer->overall_rating = 0;
                $ap->groomer->total_appts = 0;
                if (count($ret) > 0) {
                    $ap->groomer->overall_rating = $ret[0]->avg_rating;
                    $ap->groomer->total_appts = $ret[0]->total_appts;
                }
            }

            $ap->user = User::where('user_id', $ap->user_id)->first();


            $addr = Address::find($ap->address_id);
            if (!empty($addr)) {
                if(!empty( $addr->address2 ) && ( $addr->address2  != '')) {
                    $ap->address = $addr->address1 . ' # ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
                }else {
                    $ap->address = $addr->address1 . ', ' .  $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
                }

            }

            $ap->billing = UserBilling::find($ap->payment_id);

            $pet_type = 'dog';

            $ap->pets = DB::select("
                    select 
                        a.pet_id, 
                        p.size_id, 
                        p.sub_total, 
                        p.tax, 
                        p.total, 
                        c.name as pet_name,
                        c.dob as pet_dob,
                        c.age as pet_age,
                        c.breed,
                        c.special_note,
                        c.type,
                        timestampdiff(month, c.dob, curdate()) as age,
                        b.prod_id as package_id,
                        b.prod_name as package_name,
                        a.amt as price
                    from appointment_pet p 
                        inner join appointment_product a on p.appointment_id = a.appointment_id
                        inner join product b on a.prod_id = b.prod_id
                        inner join pet c on p.pet_id = c.pet_id
                    where a.appointment_id = :appointment_id
                    and a.pet_id = p.pet_id
                    and b.prod_type = 'P'
                    and b.pet_type = c.type
                    and b.pet_type = :pet_type
                    ", [
              'appointment_id' => $ap->appointment_id,
              'pet_type' => $pet_type
            ]);

            if (empty($ap->pets)) {

                $pet_type = 'cat';

                $ap->pets = DB::select("
                    select 
                        a.pet_id, 
                        p.sub_total, 
                        p.tax, 
                        p.total, 
                        c.name as pet_name,
                        c.dob as pet_dob,
                        c.age as pet_age,
                        c.type,
                        c.special_note,
                        timestampdiff(month, c.dob, curdate()) as age,
                        b.prod_id as package_id,
                        b.prod_name as package_name,
                        a.amt as price,
                        p.groomer_note
                    from appointment_pet p 
                        inner join appointment_product a on p.appointment_id = a.appointment_id
                        inner join product b on a.prod_id = b.prod_id
                        inner join pet c on p.pet_id = c.pet_id
                    where a.appointment_id = :appointment_id
                    and a.pet_id = p.pet_id
                    and b.prod_type = 'P'
                    and b.pet_type = c.type
                    and b.pet_type = :pet_type
                    ", [
                  'appointment_id' => $ap->appointment_id,
                  'pet_type' => $pet_type
                ]);
            }

            foreach ($ap->pets as $p) {

                $ap->package = $p->package_id;

                if (!empty($p->pet_age)) {
                    $p->age = ($p->pet_age > 0 ? $p->pet_age . ' year' : ''). ($p->pet_age > 1 ? 's old' : ' old');
                } else {
                    $year = intval($p->age / 12);
                    $month = intval($p->age % 12);
                    $p->age = ($year > 0 ? $year . ' year' : ''). ($year > 1 ? 's ' : ' ') . $month . ' month' . ($month > 1 ? 's' : '');
                }

                if ($p->type != 'cat') {
                    $p->addons = DB::select("
                            select
                                b.prod_id,
                                b.prod_name,
                                a.amt as price
                            from appointment_product a
                                inner join product b on a.prod_id = b.prod_id
                            where a.appointment_id = :appointment_id
                            and a.pet_id = :pet_id
                            and b.prod_type = 'A'
                            and b.pet_type = :pet_type
                        ", [
                      'appointment_id' => $ap->appointment_id,
                      'pet_id' => $p->pet_id,
                      'pet_type' => $p->type
                    ]);

                    $p->addon_array = [];
                    foreach($p->addons as $a) {
                        $p->addon_array[] = $a->prod_id;
                    }

                    $p->shampoo = DB::select("
                        select 
                            b.prod_id,
                            b.prod_name,
                            a.amt as price
                        from appointment_product a
                            inner join product b on a.prod_id = b.prod_id
                        where a.appointment_id = :appointment_id
                        and a.pet_id = :pet_id
                        and b.prod_type = 'S'
                        and b.pet_type = :pet_type
                    ", [
                      'appointment_id' => $ap->appointment_id,
                      'pet_id' => $p->pet_id,
                      'pet_type' => $p->type
                    ]);

                    $p->breed = Breed::find($p->breed);
                    $p->size = Size::find($p->size_id);
                } else {
                    $p->addons = DB::select("
                            select
                                b.prod_id,
                                b.prod_name,
                                a.amt as price
                            from appointment_product a
                                inner join product b on a.prod_id = b.prod_id
                            where a.appointment_id = :appointment_id
                            and a.pet_id = :pet_id
                            and b.prod_type = 'A'
                            and b.pet_type = :pet_type
                        ", [
                      'appointment_id' => $ap->appointment_id,
                      'pet_id' => $p->pet_id,
                      'pet_type' => $p->type
                    ]);

                    $p->addon_array = [];
                    foreach($p->addons as $a) {
                        $p->addon_array[] = $a->prod_id;
                    }
                }

                $p->photos = PetPhoto::where('pet_id', $p->pet_id)->get();

                foreach ($p->photos as $photo) {
                    $photo->photo = base64_encode($photo->photo);
                }

                if (count($p->photos) > 0) {
                    $p->photo = $p->photos[0]->photo;
                }

                $p->before_image = AppointmentPhoto::where('appointment_id', $ap->appointment_id)
                  ->where('pet_id', $p->pet_id)
                  ->where('type', 'B')
                  ->select('image')
                  ->first();

                if (!empty($p->before_image)) {
                    $p->before_image = base64_encode($p->before_image->image);
                }

                $p->after_image = AppointmentPhoto::where('appointment_id', $ap->appointment_id)
                  ->where('pet_id', $p->pet_id)
                  ->where('type', 'A')
                  ->select('image')
                  ->first();

                if (!empty($p->after_image)) {
                    $p->after_image = base64_encode($p->after_image->image);
                }
            }

            ## get available groomers ##

            # available Groomers based on the available schedule #

            if ($pet_type == 'cat') {
                $groom_pet = " and g.cat = 'Y' ";
            } else {
                $groom_pet = " and g.dog = 'Y' ";
            }

            if ($ap->status == 'N') {
                $groomers = DB::select("
                    select g.*
                    from groomer as g 
                        inner join groomer_availability as ga on ga.groomer_id = g.groomer_id
                    where g.status IN ('N', 'A')
                    and ga.weekday = ". $weekday ." 
                    and ga.hour in (". implode(',', $hours) .")
                    and ga.date = '". $ga_date. "'
                    " . $groom_pet . "
                    group by g.groomer_id
                    ");
            }

            if ($ap->status == 'D' || $ap->status == 'O' || $ap->status == 'W' || $ap->status == 'R') {
                $groomers = DB::select("
                    select g.*
                    from groomer as g 
                        inner join groomer_availability as ga on ga.groomer_id = g.groomer_id
                    where g.status IN ('N', 'A')
                    and ga.weekday = ". $weekday ." 
                    and ga.hour = ". $hour . "
                    and ga.date = '". $ga_date. "'
                    " . $groom_pet . "
                    group by g.groomer_id
                    ");

            }

            # unavailable Groomers based on the appointment #

            $unavailable_groomers = DB::select("
                    select g.*, DATE_FORMAT(a.accepted_date,'%c/%e/%Y %l:%i %p') as accepted_date
                    from groomer as g 
                        inner join appointment_list as a on a.groomer_id = g.groomer_id
                    where 
                    (
                    (a.status in ('D', 'O', 'W') and ABS(TIMESTAMPDIFF(HOUR, a.accepted_date, '" . $sql_date . "')) < 1)
                    or 
                    (a.status = 'N' and ABS(TIMESTAMPDIFF(HOUR, a.reserved_date, '" . $ap->reserved_date . "')) < 1)
                    )
                    and a.appointment_id <> :appointment_id
                ", [
              'appointment_id' => $ap->appointment_id
            ]);

            $unavailable_ids = array();
            $groomer_ids = array();

            if (!empty($groomers)) {

                // remove unavailable groomers from the groomers list
                foreach ($groomers as $k=>$v) {

                    $groomer_ids[] = $v->groomer_id;


                    $v->groom_pet = ($v->dog == 'Y') ? 'Dog' : '';

                    if ($v->cat == 'Y') {
                        $v->groom_pet = (!empty($v->groom_pet)) ? $v->groom_pet . ', ' : $v->groom_pet ;
                        $v->groom_pet .= 'Cat';
                    }
                }

                if (!empty($unavailable_groomers)) {

                    foreach ($unavailable_groomers as $g) {
                        $unavailable_ids[] = $g->groomer_id;
                    }

                    // remove unavailable groomers from the groomers list
                    foreach ($groomers as $k=>$v) {
                        if (in_array($v->groomer_id, $unavailable_ids)) {
                            unset($groomers[$k]);
                        }
                    }

                    // remove unavailable groomers from the unavailable groomers list
                    // when unavailable groomer is not in the groomer list
                    foreach ($unavailable_groomers as $k=>$v) {
                        if (!in_array($v->groomer_id, $groomer_ids)) {
                            unset($unavailable_groomers[$k]);
                        }

                        $v->groom_pet = ($v->dog == 'Y') ? 'Dog' : '';

                        if ($v->cat == 'Y') {
                            $v->groom_pet = (!empty($v->groom_pet)) ? $v->groom_pet . ', ' : $v->groom_pet ;
                            $v->groom_pet .= 'Cat';
                        }
                    }
                }

            } else {
                if (!empty($unavailable_groomers)) {
                    foreach ($unavailable_groomers as $k=>$v) {
                        if (!in_array($v->groomer_id, $groomer_ids)) {
                            unset($unavailable_groomers[$k]);
                        }

                        $v->groom_pet = ($v->dog == 'Y') ? 'Dog' : '';

                        if ($v->cat == 'Y') {
                            $v->groom_pet = (!empty($v->groom_pet)) ? $v->groom_pet . ', ' : $v->groom_pet ;
                            $v->groom_pet .= 'Cat';
                        }
                    }
                }
            }



            $status = Constants::$appointment_status;
            $selectable_status = [];
            foreach ($status as $key => $val) {

                Helper::log('### KEY ###', $key);
                Helper::log('### VAL ###', $val);

                if (!in_array($key, ['P', 'F'])) {
                    $selectable_status[$key] = $val;
                    /*$selectable_status[] = [
                        "$key" => "$val"
                    ];*/
                }
            }

            if (empty($groomers)) {
                $groomers = [];
            }

            $payments = UserBilling::where('user_id', $ap->user_id)
              ->orderBy('status', 'asc')
              ->get();
            if (empty($payments)) {
                $payments = '';
            }

            $sizes = Size::all();
            $packages = Product::where('prod_type', 'P')
              ->where('pet_type', $pet_type)
              ->where('status','A')
              ->get(); // show only dog package for now
            $shampoos = Product::where('prod_type', 'S')
              ->where('pet_type', $pet_type)
              ->whereIn('status',['A', 'C'])
              ->orderBy('status', 'asc')
              ->get();

            $add_ons = DB::select("
                select
                    a.prod_id,
                    a.prod_type,
                    a.prod_name,
                    a.prod_desc,
                    f_get_product_price(b.prod_id, b.size_id, :zip) as denom,
                    b.min_denom,
                    b.max_denom
                from product a 
                    inner join product_denom b on a.prod_id = b.prod_id
                where a.prod_type = 'A'
                and a.status = 'A'
                and b.status = 'A'
                and a.pet_type = :pet_type
                group by a.prod_id
                order by a.seq
            ", [
              'zip' => isset($addr) ? $addr->zip : '',
              'pet_type' => $pet_type
            ]);


            $allowed_admin = in_array(Auth::guard('admin')->user()->email, ['it@jjonbp.com','jun@jjonbp.com']);

            # get reschedule history
            $update_history = DB::select("
                select 
                    status, 
                    reserved_at,
                    DATE_FORMAT(accepted_date, '%m/%d/%Y %h:%i %p') as accepted_date, 
                    groomer_id, 
                    modified_by, 
                    DATE_FORMAT(min(mdate), '%m/%d/%Y %h:%i %p') as mdate,
                    adate
                from appointment_list_log
                where appointment_id = :appointment_id
                group by 1,2,3,4,5               
                order by 7
            ", [
              'appointment_id' => $ap->appointment_id
            ]);

            $ap_log = AppointmentLog::where('appointment_id', $ap->appointment_id)
              ->orderBy('adate', 'desc')
              ->first();



            foreach($update_history as $o) {
                $o->status_name = Constants::$appointment_status[$o->status];
                $o->groomer_name = null;
                if (!empty($o->groomer_id)) {
                    $groomer = Groomer::find($o->groomer_id);
                    if (!empty($groomer)) {
                        $o->groomer_name = $groomer->fist_name . ' ' . $groomer->last_name;
                    }
                }
            }

            $groomer = Groomer::find($ap->groomer_id);
            $groomer_name = '';
            if (!empty($groomer)) {
                $groomer_name = $groomer->first_name . ' ' . $groomer->last_name;
            }

            $current_log = new \stdClass();
            $current_log->status = $ap->status;
            $current_log->reserved_at = $ap->reserved_at;
            $current_log->accepted_date = empty($ap->accepted_date) ? '' : Carbon::parse($ap->accepted_date)->format('m/d/Y h:i A');
            $current_log->groomer_id = $ap->groomer_id;
            $current_log->modified_by = $ap->modified_by;
            $current_log->status_name = Constants::$appointment_status[$ap->status];
            $current_log->groomer_name = $groomer_name;
            $current_log->mdate = empty($ap->mdate) ? '' : Carbon::parse($ap->mdate)->format('m/d/Y h:i:s A');
            $current_log->adate = isset($ap_log) ? Carbon::parse($ap_log->adate)->format('m/d/Y h:i:s A') : '';

            $update_history[] = $current_log;

            $op_notes = AppointmentList::where('user_id', $ap->user_id)
              ->where('appointment_id', '!=', $ap->appointment_id)
              ->whereRaw("trim(ifnull(op_note, '')) != ''")
              ->select('appointment_id', 'op_note', 'cdate')
              ->orderBy('appointment_id', 'desc')
              ->get();

            $ap->cc_trans_appt = CCTrans::where('appointment_id', $ap->appointment_id)
                ->where('type', 'S')
            ->whereIn('category', ['S'])
            ->where('result', '0')
            ->whereNull('void_date')
            ->orderBy('id','asc')
             ->get() ;

            $ap->cc_trans_tip = CCTrans::where('appointment_id', $ap->appointment_id)
                ->where('type', 'S')
                ->whereIn('category', ['T'])
                ->where('result', '0')
                ->whereNull('void_date')
                ->orderBy('id','asc')
                ->get() ;

            $ap->cc_trans_resc = CCTrans::where('appointment_id', $ap->appointment_id)
                ->where('type', 'S')
                ->whereIn('category', ['R'])
                ->where('result', '0')
                ->whereNull('void_date')
                ->orderBy('id','asc')
                ->get() ;

            $ap->cc_trans_cancel = CCTrans::where('appointment_id', $ap->appointment_id)
                ->where('type', 'S')
                ->whereIn('category', ['W'])
                ->where('result', '0')
                ->whereNull('void_date')
                ->orderBy('id','asc')
                ->get() ;

            return view('admin.appointment-invoice', [
              'msg' => '',
              'ap' => $ap,
              'status' => $selectable_status,
              'groomers' => $groomers,
              'unavailable_groomers' => $unavailable_groomers,
              'payments' => $payments,
              'sizes' => $sizes,
              'packages' => $packages,
              'shampoos' => $shampoos,
              'addons' => $add_ons,
              'update_history' => $update_history,
              'op_notes' => $op_notes,
              'allowed_admin' => $allowed_admin// to show Send Service Completion Email
            ]);

        } catch (\Exception $ex) {
            return response()->json([
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);
        }

    }

    public function update_service(Request $request) {

        try {
            $v = Validator::make($request->all(), [
                'id' => 'required',
                'pet_id' => 'required',
                'size_id' => '',
                'package_id' => 'required',
                'shampoo_id' => ''
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }
                return response()->json([
                    'msg' =>  $msg
                ]);
            }

            $ap = AppointmentList::findOrFail($request->id);
            if (empty($ap)) {
                return response()->json([
                    'msg' =>  'Wrong appointment!'
                ]);
            }

//Could be comment out, I think.
            $address = Address::findOrFail($ap->address_id);
            if (empty($address)) {
                return response()->json([
                    'msg' =>  'Wrong address!'
                ]);
            }

            $admin = Auth::guard('admin')->user();
            if (empty($admin)) {
                return response()->json([
                    'msg' => 'Session expired!'
                ]);
            }

            $modified_by = $admin->name . ' (' . $admin->admin_id . ')';

            # allow only for 'Groomer Not Assigned Yet','Groomer Assigned','Groomer On The Way','Work In Progress'
            $allowed_status = array("N", "D", "O", "W", "P");
            if (!in_array($ap->status, $allowed_status)) {
                return response()->json([
                    'msg' =>  'Not allowed status.'
                ]);
            }

            DB::beginTransaction();

            AppointmentProcessor::update_service($request->id, $request->pet_id, $request->size_id, $request->package_id, $request->shampoo_id, $request->addon, $modified_by);

            DB::commit();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                'msg' =>  $ex->getMessage() . ' [' . $ex->getCode() . ']' . ' [' . $ex->getFile() . ']' . ' [' . $ex->getLine() . ']'
            ]);
        }
    }

    public function cancel_fav_groomer(Request $request) {

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
                    'msg' =>  $msg
                ]);
            }

            $ap = AppointmentList::findOrFail($request->id);
            if (empty($ap)) {
                return response()->json([
                    'msg' =>  'Wrong appointment!'
                ]);
            }

            $admin = Auth::guard('admin')->user();
            if (empty($admin)) {
                return response()->json([
                    'msg' => 'Session expired!'
                ]);
            }

            $modified_by = $admin->name . ' (' . $admin->admin_id . ')';

            # allow only for 'Groomer Not Assigned Yet','Groomer Assigned','Groomer On The Way','Work In Progress'
            $allowed_status = array("N", "D", "O", "W", "P");
            if (!in_array($ap->status, $allowed_status)) {
                return response()->json([
                    'msg' =>  'Not allowed status.'
                ]);
            }

            $ap->my_favorite_groomer = null;
            $ap->fav_type = 'N';
            $ap->fav_groomer_fee = 0;
//            $ap->total = $ap->total - 10;
            $ap->modified_by = $modified_by;

            $ap->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                'msg' =>  $ex->getMessage() . ' [' . $ex->getCode() . ']' . ' [' . $ex->getFile() . ']' . ' [' . $ex->getLine() . ']'
            ]);
        }
    }

    public function update_fav_groomer(Request $request) {

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
                    'msg' =>  $msg
                ]);
            }

            $ap = AppointmentList::findOrFail($request->id);
            if (empty($ap)) {
                return response()->json([
                    'msg' =>  'Wrong appointment!'
                ]);
            }

            $admin = Auth::guard('admin')->user();
            if (empty($admin)) {
                return response()->json([
                    'msg' => 'Session expired!'
                ]);
            }

            $modified_by = $admin->name . ' (' . $admin->admin_id . ')';

            # allow only for 'Groomer Not Assigned Yet','Groomer Assigned','Groomer On The Way','Work In Progress'
            $allowed_status = array("N", "D", "O", "W", "P");
            if (!in_array($ap->status, $allowed_status)) {
                return response()->json([
                    'msg' =>  'Not allowed status.'
                ]);
            }

            $ap->my_favorite_groomer = $request->my_favorite_groomer;
            $ap->fav_type = 'F';
            $ap->fav_groomer_fee = 10;
//            $ap->total = $ap->total + 10;
            $ap->modified_by = $modified_by;

            $ap->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                'msg' =>  $ex->getMessage() . ' [' . $ex->getCode() . ']' . ' [' . $ex->getFile() . ']' . ' [' . $ex->getLine() . ']'
            ]);
        }
    }

    public function update_promo_code(Request $request) {

        try {
            $v = Validator::make($request->all(), [
                'id' => 'required',
                //'promo_code' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }
                return response()->json([
                    'msg' =>  $msg
                ]);
            }

            $admin = Auth::guard('admin')->user();
            if (empty($admin)) {
                return response()->json([
                  'msg' => 'Session expired!'
                ]);
            }

            $ap = AppointmentList::findOrFail($request->id);
            if (empty($ap)) {
                return response()->json([
                    'msg' =>  'Wrong appointment!'
                ]);
            }

            # allow only for 'Groomer Not Assigned Yet','Groomer Assigned','Groomer On The Way','Work In Progress'
//            $allowed_status = array("N", "D", "O", "W", "P");
//            if (!in_array($ap->status, $allowed_status)) {
//                return response()->json([
//                    'msg' =>  'Not allowed status.' . $ap->status
//                ]);
//            }

            $promo = PromoCode::whereRaw("code = '" . strtoupper($request->promo_code) . "'")->first();
            if (!empty($promo)) {
                $app_products = AppointmentProduct::join('product', 'appointment_product.prod_id', '=', 'product.prod_id')
                  ->where('appointment_product.appointment_id', $ap->appointment_id)
                  ->where('product.prod_type', 'P')
                  ->select('appointment_product.*')
                  ->get();

                $package_list = [];
                foreach ($app_products as $product) {
                    $package_list[] = $product->prod_id;
                }

                $msg = PromoCodeProcessor::checkIfUsed($ap->user_id, $promo, $ap->appointment_id, $package_list);
                if (!empty($msg)) {
                    return response()->json([
                      'msg' => $msg
                    ]);
                }

                if ($promo->type == 'R' && $ap->status == 'P') {
                    return response()->json([
                      'msg' =>  'You entered referral promo code!'
                    ]);
                }
            } else if (!empty($request->promo_code)) {
                return response()->json([
                  'msg' =>  'Wrong promo code!'
                ]);
            }

            $res = AppointmentProcessor::apply_promo_code($ap, $promo);

            return response()->json(
                $res
            );

        } catch (\Exception $ex) {
            return response()->json([
                'msg' =>  $ex->getMessage() . ' [' . $ex->getCode() . ']' . ' [' . $ex->getFile() . ']' . ' [' . $ex->getLine() . ']'
            ]);
        }
    }

    public function confirm_available_groomer(Request $request) {

        try {
            $v = Validator::make($request->all(), [
                'groomer_id' => 'required',
                'accepted_date' => 'required',
                'id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }
                return response()->json([
                    'msg' =>  $msg
                ]);
            }

            $accepted_date = Carbon::parse($request->accepted_date);
            if ($accepted_date < Carbon::now()) {
                return response()->json([
                  'msg' =>  'Please enter valid date. Current: ' . Carbon::now()
                ]);
            }

            # Double check for User Blocked Groomer!
            $ap = AppointmentList::find($request->id);
            if (empty($ap)) {
                throw new \Exception('Invalid appointment ID provided');
            }

            $user_id = $ap->user_id;
            $groomer_id = $request->groomer_id;

            $ret = UserBlockedGroomer::where('user_id', $user_id)->where('groomer_id', $groomer_id)->first();

            if(!empty($ret)){
                return response()->json([
                    'msg' =>  'This Groomer is Blocked'
                ]);
            }

            $weekday = $accepted_date->dayOfWeek;
            $hour = $accepted_date->hour;

            # because the availability week's format starts from Monday ot Sunday #
            if ($weekday == 0) {
                $weekday = 6;
            } else {
                $weekday = $weekday - 1;
            }

            $check_available = GroomerAvailability::where('weekday', $weekday)
                ->where('hour', $hour)
                ->where('groomer_id', $request->groomer_id)
                ->first();

            if (empty($check_available)) {
                $msg = 'unchecked';
                return response()->json([
                    'msg' =>  $msg
                ]);
            } else {
                return response()->json([
                    'msg' =>  ''
                ]);
            }

        } catch (\Exception $ex) {
            return response()->json([
                'msg' =>  $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function pay_bonus(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'amt' => 'required|numeric'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "<br/>") . $v[0];
                }

                return response()->json([
                    'msg' => $msg
                ]);
            }

            $user = Auth::guard('admin')->user();
            if (empty($user)) {
                return response()->json([
                    'msg' => 'Session has been expired. Please login again!'
                ]);
            }

            $ap = AppointmentList::findOrFail($request->id);

            $ps = new ProfitShare();
            $ps->appointment_id     = $request->id;
            $ps->type               = 'C';
            $ps->groomer_id         = $ap->groomer_id;
            $ps->groomer_profit_amt = $request->amt;
            $ps->category           = 'B';
            $ps->remaining_amt      = 0;
            $ps->comments           = $request->comments;
            $ps->cdate              = !empty($request->cdate) ? $request->cdate : Carbon::now();
            $ps->created_by         = $user->admin_id;
            $ps->save();

            return response()->json([
                'msg' =>  ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' =>  $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function update_requested_time(Request $request) {
        try {

            //required_date means requested_date in UI, or reserved_at in DB.
            $admin = Auth::guard('admin')->user();

            $required_date = new DateTime($request->required_date);
            $required_date = $required_date->format('Y-m-d');

            $required_time = Helper::get_time_by_id($request->required_time);

            if (empty($required_time)) {
                throw new \Exception('Invalid time provided');
            }

            $ap = AppointmentList::findOrFail($request->id);

            $ap_first = DB::select("
                            select appointment_id, min(cdate) min_cdate
                            from appointment_list_log
                            where appointment_id = :appointment_id
                            group by 1
                        ", [
                'appointment_id' => $request->id
            ]);
            $cdate = $ap->cdate;

            if( is_array($ap_first) && count($ap_first)>0){ //if exist appointment_list
                $ap_first = $ap_first[0];
                $cdate = Carbon::parse($ap_first->min_cdate);
            }

            $today = Carbon::now()->format('Y-m-d');
            $price_change = 'N';
            //Change from today into future.

            if( $ap->sameday_booking > 0 &&
                $today ==  Carbon::parse($cdate)->format('Y-m-d')  &&
                $required_date > $today
            ){
                $sameday_booking = 0;
                $price_change = 'Y';
            }else if( $ap->sameday_booking == 0 &&
                $today ==  Carbon::parse($cdate)->format('Y-m-d')  &&
                $required_date == $today
            ){
                $sameday_booking = env('SAMEDAY_BOOKING');
                $price_change = 'Y';
            }

            if($price_change == 'Y') {
                $sub_total = $ap->sub_total;
                $promo_amt = $ap->promo_amt;
                $safety_insurance = $ap->safety_insurance;
                $fav_fee = $ap->fav_groomer_fee;
                $credit_amt = $ap->credit_amt;

                $zip = '';
                $address = Address::where('user_id', $ap->user_id)
                    ->where('status', 'A')
                    ->first();
                if (!empty($address)) {
                    $zip = $address->zip;
                }

//                if ($promo_amt > ($sub_total + $safety_insurance + $sameday_booking)) {
//                    $taxable_promo_amt = $sub_total + $safety_insurance + $sameday_booking + $fav_fee ;
                if ($promo_amt > ($sub_total + $safety_insurance )) {
                    $taxable_promo_amt = $sub_total + $safety_insurance ;
                }else {
                    $taxable_promo_amt = $promo_amt;
                }

                ### tax ###
                if( !empty($ap->promo_code) && $ap->promo_code != '' ){ //recalculate
                    $promo = PromoCode::whereRaw("code = '" . $ap->promo_code . "'")->first();
                    $taxable_promo_amt = empty($promo) ? 0 : ($promo->include_tax == 'N' ? 0 : $taxable_promo_amt);
                }
                $tax = AppointmentProcessor::get_tax($zip, $sub_total, $safety_insurance, $taxable_promo_amt, 0, $sameday_booking, $fav_fee ); //Do not consider credit amount,

                $total = $sub_total + $safety_insurance  + $sameday_booking + $fav_fee + $tax - $promo_amt - $credit_amt ;

                $ap->sameday_booking = $sameday_booking;
                //$ap->fav_groomer_fee = $fav_fee; //Don't have to update here because no changes.
                $ap->tax = $tax;
                $ap->total = $total;
            }

            $ap->reserved_at    = $required_date . ' ' . $required_time->time;
            $reserved_date = new DateTime($required_date .' ' . substr($required_time->start,0, 5) . ':00');
            $reserved_date = $reserved_date->format('Y-m-d H:i:s');
            $ap->reserved_date  = $reserved_date;

            $ap->cdate          = Carbon::now();
            $ap->mdate          = Carbon::now();
            $ap->modified_by = isset($admin) ? $admin->name . '(' . $admin->admin_id . ')' : null;
            $ap->save();

            //Delete all old notifications, so start from the beginning.
            //This is because Fav. groomers need to get notified again.
            DB::update("
                DELETE from groomer_opens
                 where appt_id = :appointment_id
            ", [
                'appointment_id'    => $ap->appointment_id
            ]);
            //Send new notifications.
            AppointmentProcessor::send_groomer_notification2($ap);

            return response()->json([
                'msg' =>  ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' =>  $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function assign_groomer(Request $request) {

        try {

            if (!empty($request->for_unassign) && $request->for_unassign == 'unassign') {
                //print_r('is form un assign groomer');
                if (!empty($request->id)) {
                    //print_r('is form un assign groomer 2');
                    return $this->unassign_groomer($request->id);
                }
            }

            $v = Validator::make($request->all(), [
                'groomer_id' => 'required',
                'accepted_date' => 'required',
                'id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }
                return response()->json([
                    'msg' =>  $msg
                ]);
            }

            $gr = Groomer::where('groomer_id', $request->groomer_id)
                ->whereIn('status',['A', 'N'])
                ->where('level', '>', '0')
                ->first();

            if (empty($gr)) {
                $msg = 'Please select other groomer';
                return response()->json([
                    'msg' =>  $msg
                ]);
            }

            $ap = AppointmentList::findOrFail($request->id);

            $pre_groomer_id = $ap->groomer_id;

            $ap->status = 'D';
            $ap->groomer_id = $request->groomer_id;
            $ap->accepted_date = new DateTime($request->accepted_date);
            $ap->mdate = Carbon::now();

            $u = Auth::guard('admin')->user();
            $ap->modified_by = $u->name . '(' . $u->admin_id . ')';
            $ap->groomer_assigned_by = 'A';
            $ap->groomer_assigned_by_id = $u->admin_id;

            $ap->save();

            $user = User::findOrFail($ap->user_id);

            # new assigned groomer
            $groomer = Groomer::where('groomer_id', $ap->groomer_id)->first();
            if (empty($groomer)) {
                $msg = 'Please assign existing groomer.';
                return response()->json([
                    'msg' =>  $msg
                ]);
            }
            $groomer_name = $groomer->first_name . ' ' . $groomer->last_name;

            # previous groomer
            $same_groomer = false;
            $pre_groomer = Groomer::where('groomer_id', $pre_groomer_id)->first();
            $pre_groomer_name = '';
            if (!empty($pre_groomer)) {
                if ($ap->groomer_id == $pre_groomer_id) {
                    $same_groomer = true;
                }
                $pre_groomer_name = $pre_groomer->first_name . ' ' . $pre_groomer->last_name;
            }

            # address
            $addr = Address::find($ap->address_id);
            $address = '';
            if (!empty($addr)) {
                if(!empty( $addr->address2 ) && ( $addr->address2  != '')) {
                    $address  =$addr->address1 . ' # ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
                }else {
                    $address  =$addr->address1 .  ', ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
                }

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
                'appointment_id' => $ap->appointment_id
            ]);


            ### send email ###

            ## send email to user ##

            $subject = "Your Groomit Appointment has been confirmed.";

            $data = [];
            $data['email'] = $user->email;
            $data['name'] = $user->first_name;
            $data['subject'] = $subject;
            $data['groomer'] = $groomer_name;
            $data['address'] = $address;
//            $data['referral_code'] = $user->referral_code;

            foreach ($pets as $k=>$v) {
                $data['pet'][$k]['pet_name'] = $v->pet_name;
                $data['pet'][$k]['package_name'] = $v->package_name;
                $data['pet'][$k]['breed_name'] = empty($v->breed) ? '' : Breed::findOrFail($v->breed)->breed_name;
                $data['pet'][$k]['size_name'] = empty($v->size) ? '' : Size::findOrFail($v->size)->size_name;

                if (!empty($v->pet_age)) {
                    $data['pet'][$k]['age'] = ($v->pet_age > 0 ? $v->pet_age . ' year' : ''). ($v->pet_age > 1 ? 's old' : ' old');
                } else {
                    $dob = Carbon::parse($v->pet_dob);
                    $data['pet'][$k]['age'] = $dob->age < 2 ? $dob->diffInMonths(Carbon::now()) . " months old" : $dob->age . " years old";
                }

                $data['pet'][$k]['note'] = $v->note;
                $data['pet'][$k]['shampoo'] = '';
                $data['pet'][$k]['addon'] = '';

                $shampoo = DB::select("
                            select
                                b.prod_name
                            from appointment_product a
                                inner join product b on a.prod_id = b.prod_id
                            where a.appointment_id = :appointment_id
                            and a.pet_id = :pet_id
                            and b.prod_type = 'S'
                            and b.pet_type = :pet_type
                        ", [
                    'appointment_id' => $ap->appointment_id,
                    'pet_id' => $v->pet_id,
                    'pet_type' => $v->type
                ]);

                if (!empty($shampoo)) {
                    foreach ($shampoo as $a) {
                        $data['pet'][$k]['shampoo'] .= $a->prod_name;
                    }
                }

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
                    'appointment_id' => $ap->appointment_id,
                    'pet_id' => $v->pet_id,
                    'pet_type' => $v->type
                ]);

                if (!empty($addon)) {
                    foreach ($addon as $a) {
                        $data['pet'][$k]['addon'] .= '[' . $a->prod_name . '] ';
                    }
                }
            }

            $data['accepted_date'] = $ap->accepted_date->format('l, F j Y, h:i A');

            $referral_arr = UserProcessor::get_referral_code($user->user_id);
            $data['referral_code'] = $referral_arr['referral_code'];
            $data['referral_amount'] = $referral_arr['referral_amount'];

            $ret = Helper::send_html_mail('appointment_confirmation', $data);

            if (!empty($ret)) {
                $msg = 'Failed to send appointment confirmation email: ' . $ret;
                throw new \Exception($msg);
            }

            ## end send email to user ##

            ###########################

            ## send email to groomer ##

            # to new assigned groomer
            $data['email'] = $groomer->email;
            $data['name'] = $groomer->first_name;
            $data['subject'] = "You have an assigned Groomit appointment.";
            $data['groomer'] = $user->first_name . ' ' . $user->last_name; // temp.

            $data['accepted_date'] = $ap->accepted_date->format('l, F j Y, h:i A');

            $ret = Helper::send_html_mail('groomer_assigned_for_groomer', $data);

            if (!empty($ret)) {
                $msg = 'Failed to send appointment confirmation email';
                throw new \Exception($msg);
            }

            # to previous groomer : if exist, send cancel email
            if (!empty($pre_groomer) && !$same_groomer) {
                $data['subject'] = "Your scheduled appointment has been cancelled.";
                $data['address'] = $address;
                $data['groomer'] = $pre_groomer_name;
                $data['email'] = $pre_groomer->email;
                $data['name'] = $pre_groomer->first_name;
                $data['accepted_date'] = $ap->accepted_date->format('l, F j Y, h:i A');
                $data['user'] = $user->first_name . ' ' . $user->last_name;

                foreach ($pets as $k=>$v) {
                    $data['pet'][$k]['pet_name'] = $v->pet_name;
                    $data['pet'][$k]['package_name'] = $v->package_name;
                }

                $ret = Helper::send_html_mail('appointment_cancelled_for_groomer', $data);

                if (!empty($ret)) {
                    $msg = 'Failed to send appointment cancel email';
                    throw new \Exception($msg);
                }
            }
            ## end send email to groomer ##


            ### send SMS to groomer ###

            # to new assigned groomer #

            if (!empty($groomer->mobile_phone)) {
                $phone = $groomer->mobile_phone;

                $message = "You have a new Groomit appointment. \n\n";
                $message .= "Appt. ID: " . $ap->appointment_id . "\n";
                $message .= $user->first_name . ' ' . $user->last_name . "\n";
                $message .= $data['accepted_date'] . "\n";
                $message .= $data['address'] . "\n\n";


                foreach ($data['pet'] as $p) {
                    $message .= "\n" . "Pet Name: " . $p['pet_name'] . "\n";
                    $message .= "Pet Age: " . $p['age'] . " \n";

                    if ($p['breed_name'] != '' && $p['size_name'] != '') {
                        $message .= $p['breed_name'] . " / " . $p['size_name'] . "\n";
                    }

                    $message .= "Package: " . $p['package_name'] . "\n";
                    $message .= "Shampoo: " . $p['shampoo'] . "\n";

                    if ($p['addon'] != '') {
                        $message .= "Add-ons: " . $p['addon'] . "\n";
                    }

                    if ($p['note'] != '') {
                        $message .= "Special Note: " . $p['note'] . "\n";
                    }
                }

                ## Save message ##
                $r = New Message;
                $r->send_method = 'S';
                $r->sender_type = 'A'; // admin user
                $r->sender_id = Auth::guard('admin')->user()->admin_id;
                $r->receiver_type = 'B'; // groomer
                $r->receiver_id = $ap->groomer_id;
                $r->message_type = 'D';
                $r->appointment_id = $ap->appointment_id;
                $r->subject = '';
                $r->message = $message;
                $r->cdate = Carbon::now();
                $r->save();

                ## send SMS ##
                $ret = Helper::send_sms($phone, $message);
                if (!empty($ret)) {
                    //throw new \Exception('Groomer SMS Error: ' . $ret);
                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $message . '/ Appointment ID:' . $ap->appointment_id);
                }
            }


            # to previous groomer : if exist, send cancel sms #

            if (!empty($pre_groomer) && !$same_groomer) {
                if (!empty($pre_groomer->mobile_phone)) {
                    $phone = $pre_groomer->mobile_phone;

                    $cancel_message = "Your scheduled appointment has been cancelled. \n\n";
                    $cancel_message .= "Appt. ID: " . $ap->appointment_id . "\n";
                    $cancel_message .= $user->first_name . ' ' . $user->last_name . "\n";
                    $cancel_message .= $data['accepted_date'] . "\n";
                    $cancel_message .= $data['address'] . "\n\n";

                    foreach ($data['pet'] as $p) {
                        $cancel_message .= "\n" . "Pet Name: " . $p['pet_name'] . "\n";
                        $cancel_message .= "Pet Age: " . $p['age'] . " \n";

                        if ($p['breed_name'] != '' && $p['size_name'] != '') {
                            $cancel_message .= $p['breed_name'] . " / " . $p['size_name'] . "\n";
                        }

                        $cancel_message .= "Package: " . $p['package_name'] . "\n";
                        $cancel_message .= "Shampoo: " . $p['shampoo'] . "\n";

                        if ($p['addon'] != '') {
                            $cancel_message .= "Add-ons: " . $p['addon'] . "\n";
                        }

                        if ($p['note'] != '') {
                            $cancel_message .= "Special Note: " . $p['note'] . "\n";
                        }
                    }

                    ## Save message ##
                    $r = New Message;
                    $r->send_method = 'S';
                    $r->sender_type = 'A'; // admin user
                    $r->sender_id = Auth::guard('admin')->user()->admin_id;
                    $r->receiver_type = 'B'; // groomer
                    $r->receiver_id = $pre_groomer_id;
                    $r->message_type = 'C';
                    $r->appointment_id = $ap->appointment_id;
                    $r->subject = '';
                    $r->message = $cancel_message;
                    $r->cdate = Carbon::now();
                    $r->save();

                    ## send SMS ##
                    $ret = Helper::send_sms($phone, $cancel_message);
                    if (!empty($ret)) {
                        //throw new \Exception('Groomer SMS Error: ' . $ret);
                        Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $cancel_message . '/ Appointment ID:' . $ap->appointment_id);
                    }
                }
            }

            ### end send SMS to groomer ###


            ### send SMS to end user ###
            $message = Constants::$message_app['Confirmation'];
            $message = str_replace('GROOMER_NAME', $groomer_name, $message);
            $message = str_replace('DATETIME', $data['accepted_date'], $message);
            $message = str_replace('ADDRESS', $address, $message );

            ## Save message ##
            $r = New Message;
            $r->send_method = 'B'; // for now both
            $r->sender_type = 'A'; // admin user
            $r->sender_id = Auth::guard('admin')->user()->admin_id;
            $r->receiver_type = 'A'; // end user
            $r->receiver_id = $ap->user_id;
            $r->message_type = 'D';
            $r->appointment_id = $ap->appointment_id;
            $r->subject = '';
            $r->message = $message;
            $r->cdate = Carbon::now();
            $r->save();

            ## send SMS ##
            if (!empty($user->phone)) {
                $phone = $user->phone;
                $ret = Helper::send_sms($phone, $message);
                if (!empty($ret)) {
                    //throw new \Exception('End-User SMS Error: ' . $ret);
                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $message . '/ Appointment ID:' . $ap->appointment_id);
                }
            }

            ### end send SMS to end user ###

            ### send push ###

            if (!empty($user->device_token)) {
                /*
                $payload = [
                    'type' => 'G',
                    'id' => $ap->groomer_id
                ];
                */
                $payload = [
                    'type' => 'A',
                    'id' => $ap->appointment_id
                ];

                $error = Helper::send_notification('groomit', $r->message, $user->device_token, $r->subject, $payload);
                if (!empty($error)) {
                    //throw new \Exception('Push Notfication Error: ' . $error);
                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] Push Notfication Error:' . $error, 'Appointment ID:' . $ap->appointment_id);
                }
            } else {
                //throw new \Exception('Push Notfication Error: No device token found');
                Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] Push Notfication Error: No device token found', 'Appointment ID:' . $ap->appointment_id);
            }


            $ret = DB::delete(" delete from groomer_accept_history where appointment_id = :appointment_id"
                , [ 'appointment_id' =>  $ap->appointment_id ] );


            $ret = DB::insert("
                insert into groomer_accept_history (appointment_id, groomer_id, accepted_date, cdate, by_type, by_name , by_id )
                values (:appointment_id, :groomer_id, :accepted_date, :cdate ,'C', :by_name, :by_id )
            ", [
                'appointment_id' =>  $ap->appointment_id,
                'groomer_id' =>  $groomer->groomer_id,
                'accepted_date' => $ap->accepted_date,
                'cdate' => Carbon::now(),
                'by_name' =>  $u->name,
                'by_id' =>  $u->admin_id
            ]);

            ### end send push
            return response()->json([
                'msg' => '',
                'address_match' => $ap->address_match ? 'Y' : 'N'
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'msg' =>  $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }



    public function unassign_groomer($appointment_id) {
        $ap = AppointmentList::findOrFail($appointment_id);
        $groomer = Groomer::find($ap->groomer_id);
        $addr = Address::find($ap->address_id);
        $address = '';
        if (!empty($addr)) {
            if( !empty($addr->address2) && ($addr->address2 != '' ) ) {
                $address  =$addr->address1 . ' # ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
            }else {
                $address  =$addr->address1 . ', ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
            }

        }

        if (!empty($ap)) {
            $ap->status = 'N';
            $ap->groomer_id = null;
            $ap->accepted_date = null;
            $ap->mdate = Carbon::now();

            $u = Auth::guard('admin')->user();
            $ap->modified_by = $u->name . '(' . $u->admin_id . ')';
            $ap->groomer_assigned_by = null;
            $ap->groomer_assigned_by_id = null;

            $ap->save();

            $user = User::findOrFail($ap->user_id);

            if (!empty($groomer)) {

                $cancel_message = "Your scheduled appointment has been cancelled. \n\n";
                $cancel_message .= 'Appt. ID:' . $ap->appointment_id . "\n";
                $cancel_message .= $user->first_name . ' ' . $user->last_name . "\n";
                $cancel_message .= $ap->accepted_date . "\n";
                $cancel_message .= $address . "\n";
                //$cancel_message .= "Appointment ID: " . $ap->appointment_id . "\n";

                ## Save message ##
                $r = New Message;
                $r->send_method = 'S';
                $r->sender_type = 'A'; // admin user
                $r->sender_id = $u->admin_id;
                $r->receiver_type = 'B'; // groomer
                $r->receiver_id = $groomer->groomer_id;
                $r->message_type = 'C';
                $r->appointment_id = $ap->appointment_id;
                $r->subject = '';
                $r->message = $cancel_message;
                $r->cdate = Carbon::now();
                $r->save();

                ## send SMS ##
                $ret = Helper::send_sms($groomer->phone, $cancel_message);
                if (!empty($ret)) {
                    //throw new \Exception('Groomer SMS Error: ' . $ret);
                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $cancel_message . '/ Appointment ID:' . $ap->appointment_id);
                }
            }
            
            $cancel_message = "Your upcoming Groomit appointment has been changed. We are now attempting to locate the closest available groomer. Thank you for your patience. \n\n";

            ## Save message ##
            $r = New Message;
            $r->send_method = 'S';
            $r->sender_type = 'A'; // admin user
            $r->sender_id = $u->admin_id;
            $r->receiver_type = 'A'; // user
            $r->receiver_id = $user->user_id;
            $r->message_type = 'C';
            $r->appointment_id = $ap->appointment_id;
            $r->subject = '';
            $r->message = $cancel_message;
            $r->cdate = Carbon::now();
            $r->save();

            ## send SMS ##
            $ret = Helper::send_sms($user->phone, $cancel_message);
            if (!empty($ret)) {
                //throw new \Exception('Groomer SMS Error: ' . $ret);
                Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $cancel_message . '/ Appointment ID:' . $ap->appointment_id);
            }

            //Do not send notifications when unassigned by CS, requested by CS.
            //Invalidate existing notifications.
//            DB::update("
//                update groomer_opens
//                   set removed = 'Y'
//                 where appt_id = :appointment_id
//            ", [
//                'appointment_id'    => $appointment_id
//            ]);
//            //Send new notifications.
//            AppointmentProcessor::send_groomer_notification2($ap);

            return redirect('/admin/appointment/' . $appointment_id);
        } else {
            return redirect('/admin/appointment');
        }
    }

    public function change_status(Request $request) {
        try {
            $v = Validator::make($request->all(), [
                'status' => 'required',
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

            $ap = AppointmentList::findOrFail($request->id);
            $status_msg = '';

            $old_status = $ap->status;

            switch($request->status) {
                case 'S': //Work Completed
                    if (!$ap->groomer_id) {
                        $status_msg = 'Please assign Groomer first';
                    } else if (!$ap->accepted_date) {
                        $status_msg = 'Please set up Accept Date first';
                    }

                    if (!in_array($ap->status, ['O', 'W', 'F'])) { // O? W:Work In Progress F:Failed payment
                        $status_msg = 'Invalid previous status for work completed';
                    }

                    if ($ap->status == 'P') {
                        $status_msg = 'This appointment was already completed! Please refresh your browser.';
                    }

                    break;
                case 'D':
                    if (!$ap->accepted_date || !$ap->groomer_id) {
                        $status_msg = 'Please Assign Groomer first';
                    }
                    break;
                case 'W':
                    if (!$ap->accepted_date) {
                        $status_msg = 'Please set up Accept Date first';
                    }
                    break;
            }

            if ($status_msg != '') {
                return response()->json([
                    'msg' => $status_msg
                ]);
            }

            # update payment #
            if ($old_status != 'C' && $old_status != 'P' && $request->payment_id) {
                $ap->payment_id = $request->payment_id;
            }

            $ap->status = $request->status;
            $ap->mdate = Carbon::now();

            $u = Auth::guard('admin')->user();
            $ap->modified_by = $u->name . '(' . $u->admin_id . ')';

            $ap->save();

            ### groomer on the way ###
            if ($ap->status == 'O' && $ap->total > 0) {
                ### if no holding CC found, try to do it ###
                $total_trans = null;
                $total_trans = CCTrans::where('appointment_id', $ap->appointment_id)
                    ->whereIn('type', ['A', 'S', 'V'])
                    ->where('category', 'S')
                    ->where('result', 0)
                    //->whereNull('void_date')
                    ->where('amt', '!=', 0.01)
                    ->where( DB::raw('IfNull(error_name,"")'), '!=', 'cccomplete' )
                    ->sum( DB::raw("case type when 'S' then amt when 'A' then amt else -amt end") );

                if( empty($total_trans) ) {
                    $total_trans = 0;
                }

                if ( $total_trans != $ap->total )  {
                    $hold_1cent_trans = CCTrans::where('appointment_id', $ap->appointment_id)
                        ->where('type', 'A')
                        ->where('category', 'A')
                        ->where('result', 0)
                        ->where('amt', '=', 0.01)
                        ->where('cdate', '>=',  Carbon::today()->subDays(1) )
                        ->first();

                    if( empty($hold_1cent_trans)){
                        $proc = new AppointmentProcessor();
                        $ret = $proc->holdvoid_appointment($ap);
                        if (!empty($ret['error_msg'])) {
                            $ap->status = 'R';
                            $ap->note = $ret['error_msg'] . ' [' . $ret['error_code'] . ']';
                            $ap->save();

                            return response()->json([
                                'msg' => $ret['error_msg'] . ' [' . $ret['error_code'] . ']'
                            ]);
                        }
                    }
                }
            }

//            if ($ap->status == 'O' && $ap->total > 0) {
//                ### if no holding CC found, try to do it ###
//                $total_auth_only_trans = CCTrans::where('appointment_id', $ap->appointment_id)
//                    ->whereIn('type', ['A', 'S', 'V'])
//                    ->where('category', 'S')
//                    ->where('result', 0)
//                    //->whereNull('void_date')
//                    ->where('amt', '!=', 0.01)
//                    ->where( DB::raw('IfNull(error_name,"")'), '!=', 'cccomplete' )
//                    ->sum( DB::raw("case type when 'S' then amt when 'A' then amt else -amt end") );
//                if ( $total_auth_only_trans != $ap->total ) {
//                    $proc = new AppointmentProcessor();
//                    //$ret = $proc->hold_appointment($ap);
//                    $ret = $proc->holdvoid_appointment($ap);
//                    if (!empty($ret['error_msg'])) {
//                        $ap->status = 'R';
//                        $ap->note = $ret['error_msg'] . ' [' . $ret['error_code'] . ']';
//                        $ap->save();
//
//                        return response()->json([
//                            'msg' => $ret['error_msg'] . ' [' . $ret['error_code'] . ']'
//                        ]);
//                    }
//                }
//            }


            #### Work Completed ####
            if ($ap->status == 'S') {
                $proc = new AppointmentProcessor();
                $ret = $proc->charge_appointment($ap);
                if (!empty($ret['error_msg'])) {
                    $ap->status = 'F';
                    $ap->note = $ret['error_msg'] . ' [' . $ret['error_code'] . ']';
                    $ap->save();

                    return response()->json([
                        'msg' => $ret['error_msg'] . ' [' . $ret['error_code'] . ']'
                    ]);
                }
            }


            if ($ap->status == 'C') {
                ### void auth/sales if exists any ###
                //Do not refund automatically. When CS cancel an appointment, just remain as of now, becasue store credit could be given.
                //So void Authentication only.
                $all_trans = CCTrans::where('appointment_id', $ap->appointment_id)
                     ->whereIn('type', ['A'])
                    ->where('category', 'S')
                    ->where('result', 0)
                    ->whereNull('void_date')
                    ->get();

                foreach( $all_trans as $all_tran) {
                        $ret = Converge::void($ap->appointment_id, 'S', $all_tran->void_ref, $all_tran->type ); //Full voids, not partial voids.
                        if (!empty($ret['error_msg'])) {
                            Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Failed to void CC trans when cancelling appointment : ' . $ap->appointment_id, $ret['error_code'] . ' [' . $ret['error_msg'] . ']');
                            /*return response()->json([
                                'msg' => 'Failed to void auth only credit card transaction'
                            ]);*/
                        }
                }

                //Voids of Partial Refunds needed, if exist. wait what'll happens in real mode. Expect if Refunds could be voided automatically, if the original amount is voided in Converge side.
//                $all_trans = CCTrans::where('appointment_id', $ap->appointment_id)
//                    ->whereIn('type', ['V'])
//                    ->where('category', 'S')
//                    ->where('result', 0)
//                    ->where('error_name', 'Partial Void')
//                    ->whereNull('void_date')
//                    ->get();
//
//                foreach( $all_trans as $all_tran) {
//                    $ret = Converge::void($ap->appointment_id, 'S', $all_tran->void_ref, $all_tran->type ); //Full voids, not partial voids.
//                    if (!empty($ret['error_msg'])) {
//                        Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Failed to void CC trans when cancelling appointment : ' . $ap->appointment_id, $ret['error_code'] . ' [' . $ret['error_msg'] . ']');
//                        /*return response()->json([
//                            'msg' => 'Failed to void auth only credit card transaction'
//                        ]);*/
//                    }
//                }

                
                ### Recycle Promo code
                if (!empty($ap->promo_code)) {
                    PromoCodeProcessor::recycle($ap->promo_code);
                }

                $msg = CreditProcessor::cancelCreditUsage($ap->appointment_id);
                if (!empty($msg)) {
                    return response()->json([
                        'msg' => $msg
                    ]);
                }

                # Deactivate credit of canceled appointment
                $credit = Credit::where('user_id', $ap->user_id)
                    ->where('appointment_id', $ap->appointment_id)
                    ->first();

                if (!empty($credit)) {
                    $credit->status = 'C';
                    $credit->save();
                }

                $send_msg = Helper::send_appointment_msg($ap);
                if (!empty($send_msg)) {
                    return response()->json([
                        'msg' => $send_msg
                    ]);
                }

                ### CHARGE FEE (50%)
                //if (!empty($ap->groomer_id) && $ap->accepted_date < Carbon::now()->addHours(1)) {
                    if (!empty($request->collect_fee) && $request->collect_fee == 'Y') {
                        //This will be 'W', not 'S'.
                        //With $0 tax.
                        $msg = AppointmentProcessor::cancel_appointment_with_fee($ap, $request->charge_amt, $request->groomer_commission_amt);

                        if (!empty($msg)) {
                            return response()->json([
                              'msg' => $msg
                            ]);
                        }

                        ### Refund, refund later
                        $this->appointment_refund($ap->appointment_id);

                    }
                //}
            }

            if ($ap->status == 'N') { //If an appointment status is New, send notifications to all again.
                //Invalidate existing notifications.
                DB::update("
                update groomer_opens
                   set removed = 'Y'
                 where appt_id = :appointment_id
            ", [
                    'appointment_id'    => $ap->appointment_id
                ]);
                //Send new notifications.
                AppointmentProcessor::send_groomer_notification2($ap);
            }

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            Helper::log('### EXCEPTION ###', $ex->getTraceAsString());

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function reminder(Request $request) {

        //DB::beginTransaction();

        try {

            $v = Validator::make($request->all(), [
                'id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                throw new \Exception($msg);
            }

            $ap = AppointmentList::find($request->id);
            if (empty($ap)) {
                throw new \Exception('Invalid appointment ID provided');
            }

            if ($ap->accepted_date) {
                // get date / hour diff.
                $now = Carbon::now();
                $app_date = Carbon::parse($ap->accepted_date);
                $day_diff = $app_date->diffInDays($now);
                $hour_diff = $app_date->diffInHours($now);
            } else {
                throw new \Exception('Please confirm date and time first.');
            }


            if ($ap->groomer_id && $ap->reserved_date) {
                $groomer = Groomer::findOrFail($ap->groomer_id);
                $groomer_name = $groomer->first_name . ' ' . $groomer->last_name;
            } else {
                throw new Exception('Please select a groomer and confirm the appointment date first.');
            }

            ### send email ###

            $user = User::find($ap->user_id);
            if (empty($user)) {
                throw new Exception('Invalid user ID assigned to the appointment');
            }


            $address = '';
            $addr = Address::find($ap->address_id);
            if (!empty($addr)) {
                if( !empty($addr->address2) && ($addr->address2 != '' ) ) {
                    $address  =$addr->address1 . ' # ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->state;
                }else {
                    $address  =$addr->address1 .  ', ' . $addr->city . ', ' . $addr->state;
                }

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
                'appointment_id' => $ap->appointment_id
            ]);


            $data = [];

            //return $day_diff . ' / ' . $hour_diff;
            if ($hour_diff < 48 && $day_diff == 1) {
                $subject = "You have an upcoming Groomit Appointment tomorrow.";
                $data['reminder_type'] = "tomorrow";
                $message = Constants::$message_app['ReminderTomorrow'];

            } elseif ($hour_diff < 24 && $hour_diff > 0 && $day_diff == 0) {
                $subject = "You have an upcoming Groomit Appointment today.";
                $data['reminder_type'] = "today";
                $message = Constants::$message_app['ReminderToday'];
            } else {
                throw new \Exception('You can send reminder a day before or same day.');
            }


            $data['email'] = $user->email;
            $data['name'] = $user->first_name;
            $data['subject'] = $subject;
            $data['groomer'] = $groomer_name;
            $data['address'] = $address;
            $data['referral_code'] = $user->referral_code;

            foreach ($pets as $k=>$v) {
                $data['pet'][$k]['pet_name'] = $v->pet_name;
                $data['pet'][$k]['package_name'] = $v->package_name;
                $data['pet'][$k]['breed_name'] = empty($v->breed) ? '' : Breed::findOrFail($v->breed)->breed_name;
                $data['pet'][$k]['size_name'] = empty($v->size) ? '' : Size::findOrFail($v->size)->size_name;

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
                    'appointment_id' => $ap->appointment_id,
                    'pet_id' => $v->pet_id,
                    'pet_type' => $v->type
                ]);

                if (!empty($addon)) {
                    foreach ($addon as $a) {
                        $data['pet'][$k]['addon'] .= '[' . $a->prod_name . '] ';
                    }
                }

            }

            $data['accepted_date'] = $app_date->format('l, F j Y, h:i A');

            $ret = Helper::send_html_mail('reminder', $data);

            if (!empty($ret)) {
                throw new \Exception('Failed to send reminder email: ' . $ret);
            }

            $ret_groomer = Helper::send_html_mail('reminder_for_groomer', $data);

            if (!empty($ret_groomer)) {
                throw new \Exception('Failed to send reminder email: ' . $ret_groomer);
            }

            ### end send email ###


            $message = str_replace('GROOMER_NAME', $groomer_name, $message);
            $message = str_replace('DATE_TIME', $data['accepted_date'], $message);
            $message = str_replace('ADDRESS', $address, $message);

            ## Send message ##
            $r = New Message;
            $r->send_method = 'B'; // for now both
            $r->sender_type = 'A'; // admin user
            $r->sender_id = Auth::guard('admin')->user()->admin_id;
            $r->receiver_type = 'A'; // an end user
            $r->receiver_id = $ap->user_id;
            $r->message_type = $ap->status;
            $r->appointment_id = $ap->appointment_id;
            $r->subject = '';
            $r->message = $message;
            $r->cdate = Carbon::now();
            $r->save();

            ### send text ###

            $user = User::findOrFail($ap->user_id);

            if (!empty($user->phone)) {
                $phone = $user->phone;

                $ret = Helper::send_sms($phone, $message);
                if (!empty($ret)) {
                    //throw new \Exception($ret);
                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] SMS Error:' . $ret, $message . '/ Appointment ID:' . $ap->appointment_id);
                }
            }

            ### end send text ###

            ### send push ###
            if (!empty($user->device_token)) {
                $payload = [
                    'type' => 'A',
                    'id' => $ap->appointment_id
                ];

                $error = Helper::send_notification('groomit', $r->message, $user->device_token, $r->subject, $payload);
                if (!empty($error)) {
                    //throw new \Exception('Push Notfication Error: ' . $error);
                    Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] Push Notfication Error:' . $error, 'Appointment ID:' . $ap->appointment_id);
                }
            } else {
                //throw new \Exception('Push Notfication Error: No device token found');
                Helper::send_mail('tech@groomit.me', '[groomit][' . getenv('APP_ENV') . '] Push Notfication Error: No device token found', 'Appointment ID:' . $ap->appointment_id);
            }

            //DB::commit();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            //DB::rollback();

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    //Invalidate existing notifications, and send again
    public function new_notification(Request $request) {

        try {

            $v = Validator::make($request->all(), [
                'id' => 'required'
            ]);

            if ($v->fails()) {
                $msg = '';
                foreach ($v->messages()->toArray() as $k => $v) {
                    $msg .= (empty($msg) ? '' : "|") . $v[0];
                }

                throw new \Exception($msg);
            }

            $ap = AppointmentList::find($request->id);
            if (empty($ap)) {
                throw new \Exception('Invalid appointment ID provided');
            }


            DB::update("
                update groomer_opens
                   set removed = 'Y'
                 where appt_id = :appointment_id
            ", [
                'appointment_id'    => $ap->appointment_id
            ]);
            //Send new notifications.
            AppointmentProcessor::send_groomer_notification2($ap);


            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            //DB::rollback();

            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }
    public function groomer_on_the_way(Request $request) {

        //DB::beginTransaction();

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

            $user = Auth::guard('admin')->user();

            $ret = AppointmentProcessor::groomer_on_the_way(
                $request->id,
                $user->name,
                $user->user_id,
                'A'
            );

            if (!empty($ret['error_msg'])) {
                throw new \Exception($ret['error_msg'], $ret['error_code']);
            }

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            //DB::rollback();
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function upcoming($id, $type)
    {
        try {

            if (!isset($id) || !isset($type)) {
                return response()->json([
                    'msg' => 'Id and type should be provided'
                ]);
            }
            switch($type) {
                case('groomer'):
                    $groomer = Groomer::findOrFail($id);
                    if (empty($groomer)) {
                        return response()->json([
                            'msg' => 'Invalid groomer id was provided'
                        ]);
                    }
                    $id_field = 'groomer_id';
                    break;
                case('pet'):
                    $pet = Pet::findOrFail($id);
                    if (empty($pet)) {
                        return response()->json([
                            'msg' => 'Invalid $pet id was provided'
                        ]);
                    }
                    $id_field = 'pet_id';
                    break;
                default:
                    $user = User::findOrFail($id);
                    if (empty($user)) {
                        return response()->json([
                            'msg' => 'Invalid user id was provided'
                        ]);
                    }
                    $id_field = 'user_id';
                    break;
            }

            ### list of information to be returned ###
            # - appointment general
            # - groomer information
            # - pet images ( before / after )

            $appointments = AppointmentList::where($id_field, $id)
                ->whereNotIn('status', ['C', 'F', 'S', 'P', 'L'])
                ->whereRaw("
                    (
                        accepted_date is null and reserved_date >= ? or 
                        accepted_date >= ?
                    )
                ", [Carbon::today(), Carbon::today()])
                //->where('reserved_date', '>=', Carbon::today()->toDateString())
                ->select([
                    'appointment_id',
                    'user_id',
                    'address_id',
                    'groomer_id',
                    'reserved_at',
                    'reserved_date',
                    'accepted_date',
                    'special_request',
                    'sub_total',
                    'promo_amt',
                    'credit_amt',
                    'new_credit',
                    'safety_insurance',
                    'tax',
                    'total',
                    'status',
                    'rating',
                    'rating_comments',
                    'order_from',
                    'cdate'
                ])->orderBy('reserved_date', 'asc')
                ->get();

            foreach ($appointments as $ap) {

                $ap->status_name = '';
                if (array_key_exists($ap->status, Constants::$appointment_status)) {
                    $ap->status_name = Constants::$appointment_status[$ap->status];
                }

                $ap->groomer = Groomer::where('groomer_id', $ap->groomer_id)->select('groomer_id', 'first_name', 'last_name')->first();

                if (!empty($ap->groomer)) {

                    $ap->groomer_name = $ap->groomer->first_name . ' ' . $ap->groomer->last_name;


                    $ret = DB::select("
                        select avg(rating) avg_rating, count(*) total_appts
                        from appointment_list
                        where groomer_id = :groomer_id
                        and status = 'P'
                    ", [
                        'groomer_id' => $ap->groomer_id
                    ]);

                    $ap->groomer->overall_rating = 0;
                    $ap->groomer->total_appts = 0;
                    if (count($ret) > 0) {
                        $ap->groomer->overall_rating = $ret[0]->avg_rating;
                        $ap->groomer->total_appts = $ret[0]->total_appts;
                    }
                }

                $ap->user = User::where('user_id', $ap->user_id)->first();
                $ap->name = $ap->user->first_name . ' ' . $ap->user->last_name;
                $ap->phone = $ap->user->phone;

                $addr = Address::find($ap->address_id);
                if (!empty($addr)) {
                    if( !empty($addr->address2) && ($addr->address2 != '' ) ) {
                        $ap->address = $addr->address1 . ' # ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
                    }else {
                        $ap->address = $addr->address1 . ', '  . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
                    }

                }

                $accepted_date = Carbon::parse($ap->accepted_date);
                $ap->accepted_date = $accepted_date->format('m/d/Y g:i a');

            }

            return view('admin.upcoming', [
                'msg' => '',
                'appointments' => $appointments
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function recent($id, $type)
    {
        try {

            if (!isset($id) || !isset($type)) {
                return response()->json([
                    'msg' => 'Id and type should be provided'
                ]);
            }
            switch($type) {
                case('groomer'):
                    $groomer = Groomer::findOrFail($id);
                    if (empty($groomer)) {
                        return response()->json([
                            'msg' => 'Invalid groomer id was provided'
                        ]);
                    }
                    $id_field = 'groomer_id';
                    break;
                case('pet'):
                    $pet = Pet::findOrFail($id);
                    if (empty($pet)) {
                        return response()->json([
                            'msg' => 'Invalid $pet id was provided'
                        ]);
                    }
                    $id_field = 'pet_id';
                    break;
                default:
                    $user = User::findOrFail($id);
                    if (empty($user)) {
                        return response()->json([
                            'msg' => 'Invalid user id was provided'
                        ]);
                    }
                    $id_field = 'user_id';
                    break;
            }

            ### list of information to be returned ###
            # - appointment general
            # - groomer information
            # - pet images ( before / after )

            $appointments = AppointmentList::where($id_field, $id)
                ->whereIn('status', ['P'])
                ->where('accepted_date', '<', Carbon::now()->toDateTimeString())
                ->where('accepted_date', '<>', 'null')
                ->select([
                    'appointment_id',
                    'user_id',
                    'address_id',
                    'groomer_id',
                    'reserved_at',
                    'reserved_date',
                    'accepted_date',
                    'special_request',
                    'sub_total',
                    'promo_amt',
                    'credit_amt',
                    'new_credit',
                    'safety_insurance',
                    'tax',
                    'total',
                    'status',
                    'rating',
                    'rating_comments',
                    'order_from',
                    'cdate'
                ])->orderBy('reserved_date', 'desc')
                ->get();

            foreach ($appointments as $ap) {

                $ap->status_name = '';
                if (array_key_exists($ap->status, Constants::$appointment_status)) {
                    $ap->status_name = Constants::$appointment_status[$ap->status];
                }

                $ap->groomer = Groomer::where('groomer_id', $ap->groomer_id)->select('groomer_id', 'first_name', 'last_name')->first();
                if (!empty($ap->groomer)) {
                    $ap->groomer_name = $ap->groomer->first_name . ' ' . $ap->groomer->last_name;
                }

                $ret = DB::select("
                    select avg(rating) avg_rating, count(*) total_appts
                    from appointment_list
                    where groomer_id = :groomer_id
                    and status = 'P'
                ", [
                    'groomer_id' => $ap->groomer_id
                ]);

                $ap->groomer->overall_rating = 0;
                $ap->groomer->total_appts = 0;
                if (count($ret) > 0) {
                    $ap->groomer->overall_rating = $ret[0]->avg_rating;
                    $ap->groomer->total_appts = $ret[0]->total_appts;
                }

                $ap->user = User::where('user_id', $ap->user_id)->first();
                $ap->name = $ap->user->first_name . ' ' . $ap->user->last_name;
                $ap->phone = $ap->user->phone;

                $addr = Address::find($ap->address_id);
                if (!empty($addr)) {
                    if( !empty($addr->address2) && ($addr->address2 != '' ) ) {
                        $ap->address = $addr->address1 . ' # ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
                    }else {
                        $ap->address = $addr->address1 . ', ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
                    }

                }

                $accepted_date = Carbon::parse($ap->accepted_date);
                $ap->accepted_date = $accepted_date->format('m/d/Y g:i a');
            }

            return view('admin.recent', [
                'msg' => '',
                'appointments' => $appointments
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function cancel_pop($id, $type)
    {
        try {

            if (!isset($id) || !isset($type)) {
                return response()->json([
                  'msg' => 'Id and type should be provided'
                ]);
            }

            switch($type) {
                case('groomer'):
                    $groomer = Groomer::findOrFail($id);
                    if (empty($groomer)) {
                        return response()->json([
                          'msg' => 'Invalid groomer id was provided'
                        ]);
                    }
                    $id_field = 'groomer_id';
                    break;
                case('pet'):
                    $pet = Pet::findOrFail($id);
                    if (empty($pet)) {
                        return response()->json([
                          'msg' => 'Invalid $pet id was provided'
                        ]);
                    }
                    $id_field = 'pet_id';
                    break;
                default:
                    $user = User::findOrFail($id);
                    if (empty($user)) {
                        return response()->json([
                          'msg' => 'Invalid user id was provided'
                        ]);
                    }
                    $id_field = 'user_id';
                    break;
            }

            ### list of information to be returned ###
            # - appointment general
            # - groomer information
            # - pet images ( before / after )

            $appointments = AppointmentList::where($id_field, $id)
              ->whereIn('status', ['C', 'L'])
              //->where('reserved_date', '>=', Carbon::today()->toDateString())
              ->select([
                'appointment_id',
                'user_id',
                'address_id',
                'groomer_id',
                'reserved_at',
                'reserved_date',
                'accepted_date',
                'special_request',
                'sub_total',
                'promo_amt',
                'credit_amt',
                'new_credit',
                'safety_insurance',
                'tax',
                'total',
                'status',
                'rating',
                'rating_comments',
                'order_from',
                'cdate'
              ])->orderBy('reserved_date', 'asc')
              ->get();

            foreach ($appointments as $ap) {

                $ap->status_name = '';
                if (array_key_exists($ap->status, Constants::$appointment_status)) {
                    $ap->status_name = Constants::$appointment_status[$ap->status];
                }

                $ap->groomer = Groomer::where('groomer_id', $ap->groomer_id)->select('groomer_id', 'first_name', 'last_name')->first();

                if (!empty($ap->groomer)) {

                    $ap->groomer_name = $ap->groomer->first_name . ' ' . $ap->groomer->last_name;


                    $ret = DB::select("
                        select avg(rating) avg_rating, count(*) total_appts
                        from appointment_list
                        where groomer_id = :groomer_id
                        and status = 'P'
                    ", [
                      'groomer_id' => $ap->groomer_id
                    ]);

                    $ap->groomer->overall_rating = 0;
                    $ap->groomer->total_appts = 0;
                    if (count($ret) > 0) {
                        $ap->groomer->overall_rating = $ret[0]->avg_rating;
                        $ap->groomer->total_appts = $ret[0]->total_appts;
                    }
                }

                $ap->user = User::where('user_id', $ap->user_id)->first();
                $ap->name = $ap->user->first_name . ' ' . $ap->user->last_name;
                $ap->phone = $ap->user->phone;

                $addr = Address::find($ap->address_id);
                if (!empty($addr)) {
                    if( !empty($addr->address2) && ($addr->address2 != '') ) {
                        $ap->address = $addr->address1 . ' # ' . $addr->address2 . ', ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
                    }else {
                        $ap->address = $addr->address1 . ', ' .  $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
                    }

                }

                $accepted_date = Carbon::parse($ap->accepted_date);
                $ap->accepted_date = $accepted_date->format('m/d/Y g:i a');

            }

            return view('admin.appointment_cancel_pop', [
              'msg' => '',
              'appointments' => $appointments
            ]);

        } catch (\Exception $ex) {
            return response()->json([
              'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }


    public function history($id, $type)
    {
        try {

            if (!isset($id) || !isset($type)) {
                return response()->json([
                    'msg' => 'Id and type should be provided'
                ]);
            }
            switch($type) {
                case('groomer'):
                    $groomer = Groomer::findOrFail($id);
                    if (empty($groomer)) {
                        return response()->json([
                            'msg' => 'Invalid groomer id was provided'
                        ]);
                    }
                    $id_field = 'groomer_id';
                    break;
                case('pet'):
                    $pet = Pet::findOrFail($id);
                    if (empty($pet)) {
                        return response()->json([
                            'msg' => 'Invalid $pet id was provided'
                        ]);
                    }
                    $id_field = 'pet_id';
                    break;
                default:
                    $user = User::findOrFail($id);
                    if (empty($user)) {
                        return response()->json([
                            'msg' => 'Invalid user id was provided'
                        ]);
                    }
                    $id_field = 'user_id';
                    break;
            }

            ### list of information to be returned ###
            # - appointment general
            # - groomer information
            # - pet images ( before / after )

            $appointments = AppointmentList::where($id_field, $id)
                ->select([
                    'appointment_id',
                    'user_id',
                    'address_id',
                    'groomer_id',
                    'reserved_at',
                    'reserved_date',
                    'accepted_date',
                    'special_request',
                    'sub_total',
                    'promo_amt',
                    'tax',
                    'total',
                    'status',
                    'rating',
                    'rating_comments',
                    'cdate'
                ])->orderBy('status', 'desc')
                ->orderBy('reserved_date', 'desc')
                ->get();


            foreach ($appointments as $ap) {

                $ap->status_name = '';
                if (array_key_exists($ap->status, Constants::$appointment_status)) {
                    $ap->status_name = Constants::$appointment_status[$ap->status];
                }

                if ($ap->groomer_id) {
                    $ap->groomer = Groomer::where('groomer_id', $ap->groomer_id)->select('groomer_id', 'first_name', 'last_name')->first();
                    if (!empty($ap->groomer)) {
                        $ap->groomer_name = $ap->groomer->first_name . ' ' . $ap->groomer->last_name;
                    }

                } else {
                    $ap->groomer_name = '';
                }


                $ap->user = User::where('user_id', $ap->user_id)->first();
                $ap->name = $ap->user->first_name . ' ' . $ap->user->last_name;
                $ap->phone = $ap->user->phone;

                $addr = Address::find($ap->address_id);
                if (!empty($addr)) {
                    $ap->address = $addr->address1 . ', ' . $addr->address2 . ' ' . $addr->city . ', ' . $addr->state . ' ' . $addr->zip;
                }

                if ($accepted_date = Carbon::parse($ap->accepted_date)) {
                    $ap->accepted_date = $accepted_date->format('m/d/Y g:i a');
                } else {
                    $ap->accepted_date = '';
                }
            }


            return view('admin.history', [
                'msg' => '',
                'appointments' => $appointments
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function getAvailableGroomers(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'id' => 'required',
                'accepted_date' => 'required'
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

            $ap = AppointmentList::find($request->id);
            if (empty($ap)) {
                return response()->json([
                    'msg' => 'Invalid ID provided'
                ]);
            }

            if ($request->accepted_date) {
                $accepted_date = Carbon::parse($request->accepted_date);
                $weekday = $accepted_date->dayOfWeek;
                $hour = $accepted_date->hour;
                $hours = array($hour, $hour+1, $hour+2, $hour+3);

                $ga_date = $accepted_date->format('Y-m-d');
                $ap->accepted_date = $accepted_date->format('m/d/Y g:i a');
                $sql_date = $accepted_date;

            } else {
                $sql_date = $ap->reserved_date;
                $reserved_date = Carbon::parse($ap->reserved_date);

                $ga_date = $reserved_date->format('Y-m-d');

                $weekday = $reserved_date->dayOfWeek;
                $hour = $reserved_date->hour;
                $hours = array($hour, $hour+1, $hour+2, $hour+3);
            }

            # because the availability week's format starts from Monday ot Sunday #
            if ($weekday == 0) {
                $weekday = 6;
            } else {
                $weekday = $weekday - 1;
            }

            ## get available groomers ##
            $pet_type = 'dog';

            $pets = DB::select("
                    select 
                        a.pet_id
                    from appointment_pet p 
                        inner join appointment_product a on p.appointment_id = a.appointment_id
                        inner join product b on a.prod_id = b.prod_id
                    where a.appointment_id = :appointment_id
                    and b.prod_type = 'P'
                    and b.pet_type = 'cat'
                    ", [
                'appointment_id' => $ap->appointment_id
            ]);

            if (!empty($pets)) {
                $pet_type = 'cat';
            }
            # available Groomers based on the available schedule #

            if ($pet_type == 'cat') {
                $groom_pet = " and g.cat = 'Y' ";
            } else {
                $groom_pet = " and g.dog = 'Y' ";
            }

            ## calculate groomer schedule availability

            $groomers = null;

            if ($ap->status == 'N') {
                $groomers = DB::select("
                    select g.*
                    from groomer as g 
                        inner join groomer_availability as ga on ga.groomer_id = g.groomer_id
                    where g.status IN ('N', 'A')
                    and ga.weekday = ". $weekday ." 
                    and ga.hour in (". implode(',', $hours) .")
                    and ga.date = '". $ga_date. "'
                    " . $groom_pet . "
                    group by g.groomer_id
                    ");
            }

            if ($ap->status == 'D' || $ap->status == 'O' || $ap->status == 'W' || $ap->status == 'R') {
                $groomers = DB::select("
                    select g.*
                    from groomer as g 
                        inner join groomer_availability as ga on ga.groomer_id = g.groomer_id
                    where g.status IN ('N', 'A')
                    and ga.weekday = ". $weekday ." 
                    and ga.hour = ". $hour . "
                    and ga.date = '". $ga_date. "'
                    " . $groom_pet . "
                    group by g.groomer_id
                    ");

            }

            ## unavailable Groomers based on the appointment ##

            # 1. today's appointments
            $today_appointments = DB::select("
                    select 
                    g.groomer_id, 
                    g.first_name, 
                    g.last_name, 
                    g.level,
                    g.cat,
                    g.dog,
                    DATE_FORMAT(a.accepted_date,'%c/%e/%Y %l:%i %p') as accepted_date,
                    TIMESTAMPDIFF(MINUTE, '" . $sql_date . "', a.accepted_date) as time_diff,
                    a.appointment_id
                    from groomer as g 
                        inner join appointment_list as a on a.groomer_id = g.groomer_id
                    where a.status in ('D', 'O', 'W') 
                    and DATE(a.accepted_date) = '" . $ga_date . "'
                    " . $groom_pet . "
                    order by 2,3, 7
                ");

            $unavailable_ids = [];
            $groomer_appointments = [];
            $current_total_minutes = 0;

            # 2. calculate total service time(minutes) for each appointment
            if (!empty($today_appointments)) {
                foreach($today_appointments as $k=>$a) {

                    $app_pets = DB::select("
                    select
                        a.pet_id,
                        c.type as pet_type,
                        c.size as pet_size,
                        b.prod_id as package_id,
                        b.prod_name as package_name
                    from appointment_pet p
                        inner join appointment_product a on p.appointment_id = a.appointment_id
                        inner join product b on a.prod_id = b.prod_id
                        inner join pet c on p.pet_id = c.pet_id
                    where a.appointment_id = :appointment_id
                    and a.pet_id = p.pet_id
                    and b.prod_type = 'P'
                    and b.pet_type = c.type
                    ", [
                        'appointment_id' => $a->appointment_id
                    ]);

                    // Calculate total minutes to takes depends on the pet and size of pet
                    if (!empty($app_pets)) {
                        $total_minutes = 0;
                        foreach ($app_pets as $o) {

                            if ($o->pet_type == 'cat') { // 1.5 hr for each cat grooming
                                # cat
                                $total_minutes += 90;
                            } else {
                                #dog
                                if ($o->package_id == '2') {
                                    // silver  : 1 hr, regardless of size
                                    $total_minutes += 60;
                                } else {
                                    // gold : different by size
                                    if ($o->pet_size == 4 || $o->pet_size == 5) { // large, extra large : 2 hr
                                        $total_minutes += 120;
                                    } else { // other size : 1.5 hr
                                        $total_minutes += 90;
                                    }
                                }
                            }

                            if ($a->appointment_id == $ap->appointment_id) {
                                $current_total_minutes = $total_minutes;
                            }
                        }

                        if ($a->appointment_id == $ap->appointment_id) {
                            // remove current appointment
                            unset($today_appointments[$k]);
                        } else {
                            $groomer_appointments[$a->appointment_id]['total_minutes'] = $total_minutes;
                            $groomer_appointments[$a->appointment_id]['time_diff'] = $a->time_diff;
                            $groomer_appointments[$a->appointment_id]['groomer_id'] = $a->groomer_id;
                        }


                    }

                }

                # 3. take out unavailable groomers
                foreach($groomer_appointments as $o) {
                    if (($o['time_diff'] < 0 && ($o['time_diff'] + $o['total_minutes']) > 0)
                        || ($o['time_diff'] > 0 && $o['time_diff'] < $current_total_minutes)
                        || $o['time_diff'] == 0)
                    {
                        $unavailable_ids[] = $o['groomer_id'];
                    }
                }
            }

            $groomer_ids = [];
            $unavailable_groomers = [];

            if (!empty($groomers)) {

                // remove unavailable groomers from the groomers list
                foreach ($groomers as $k=>$v) {

                    $groomer_ids[] = $v->groomer_id;

                    $v->groom_pet = ($v->dog == 'Y') ? 'Dog' : '';

                    if ($v->cat == 'Y') {
                        $v->groom_pet = (!empty($v->groom_pet)) ? $v->groom_pet . ', ' : $v->groom_pet ;
                        $v->groom_pet .= 'Cat';
                    }
                }

                // remove unavailable groomers from the groomers list
                foreach ($groomers as $k=>$v) {
                    if (in_array($v->groomer_id, $unavailable_ids)) {
                        unset($groomers[$k]);
                    }
                }

                if (!empty($today_appointments)) {

                    // get unavailable groomers
                    foreach ($today_appointments as $k=>$v) {
                        if (!in_array($v->groomer_id, $unavailable_ids)) {
                            unset($today_appointments[$k]);
                        }

                        $unavailable_groomers = $today_appointments;

                        $v->groom_pet = ($v->dog == 'Y') ? 'Dog' : '';

                        if ($v->cat == 'Y') {
                            $v->groom_pet = (!empty($v->groom_pet)) ? $v->groom_pet . ', ' : $v->groom_pet ;
                            $v->groom_pet .= 'Cat';
                        }
                    }
                }

            }


            return response()->json([
                'msg' => '',
                'groomers' => $groomers,
                'unavailable_groomers' => $unavailable_groomers
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);
        }
    }

    public function getSchedule(Request $request) {
        return $this->fulfillment_schedule($request);

        try {

            if (!empty($request->date)) {
                $d = Carbon::parse($request->date);
            } else {
                $d = Carbon::today();
            }

            $date = Carbon::parse($d)->format('Y-m-d');
            $week = Carbon::parse($d)->dayOfWeek;

            # because the availability week's format starts from Monday not Sunday
            switch ($week) {
                case 0: // Sunday
                    $w = 6;
                    break;
                default:
                    $w = $week - 1;
                    break;
            }

            $groomers = Groomer::whereIn('status',['A', 'N'])
                ->orderBy('level','desc')
                ->orderBy('first_name','asc')
                ->get();

            if (empty($groomers)) {
                return response()->json([
                    'msg' => 'No active groomer was found.'
                ]);
            }

            foreach ($groomers as $g) {
                $available_or_scheduled = false;

                ## get Groomer's appointment ##
                $appointment_time = array();
                $appointment = AppointmentList::whereNotIn('status', ['C', 'L'])
                    ->whereRaw("DATE(accepted_date) = '" . $date . "'")
                    ->select(['accepted_date', 'appointment_id'])
                    ->where('groomer_id', $g->groomer_id)
                    ->get();

                $groomer_appointment = array();
                $groomer_appointment_css = array();
                $groomer_appointment_id = array();

                if (!empty($appointment)) {
                    foreach ($appointment as $ap) {
                        $appointment_hour =  Carbon::parse($ap->accepted_date)->format('G');
                        $appointment_minute = Carbon::parse($ap->accepted_date)->format('i');

                        $appointment_time[$appointment_hour] = array();

                        $appointment_time[$appointment_hour]['minute'] = $appointment_minute;
                        $appointment_time[$appointment_hour]['id'] = $ap->appointment_id;
                        $appointment_time[$appointment_hour]['time'] = '';

                        $pets = DB::select("
                    select 
                        a.pet_id, 
                        c.type as pet_type,
                        c.size as pet_size,
                        b.prod_id as package_id,
                        b.prod_name as package_name
                    from appointment_pet p 
                        inner join appointment_product a on p.appointment_id = a.appointment_id
                        inner join product b on a.prod_id = b.prod_id
                        inner join pet c on p.pet_id = c.pet_id
                    where a.appointment_id = :appointment_id
                    and a.pet_id = p.pet_id
                    and b.prod_type = 'P'
                    and b.pet_type = c.type
                    ", [
                            'appointment_id' => $ap->appointment_id
                        ]);

                        # Calculate total minutes to takes depends on the pet and size of pet #
                        if (!empty($pets)) {
                            $total_minutes = 0;
                            foreach($pets as $o) {
                                if ($o->pet_type == 'cat') { // 1.5 hr for each cat grooming
                                    # cat
                                    $total_minutes += 90;
                                } else {
                                    #dog
                                    if ($o->package_id == '2') {
                                        // silver  : 1 hr, regardless of size
                                        $total_minutes +=  60;
                                    } else {
                                        // gold : different by size
                                        if ($o->pet_size == 4 || $o->pet_size == 5) { // large, extra large : 2 hr
                                            $total_minutes += 120;
                                        } else { // other size : 1.5 hr
                                            $total_minutes += 90;
                                        }
                                    }

                                }
                            }

                            $final_minutes = $total_minutes + $appointment_minute;

                            $add_hr = intval($final_minutes/60);
                            $rest = $final_minutes%60;

                            # check every 30 minutes #
                            for($i=0;$i<$add_hr;$i++) {
                                if ($appointment_minute < 30) {
                                    $appointment_time[$appointment_hour + $i]['0'] = true;
                                    $appointment_time[$appointment_hour + $i]['30'] = true;
                                } else {
                                    if ($i == 0) {
                                        $appointment_time[$appointment_hour + $i]['0'] = false;
                                    }

                                    if ($i == $add_hr - 1) {
                                        $appointment_time[$appointment_hour + $i + 1]['30'] = false;
                                    }

                                    $appointment_time[$appointment_hour + $i]['30'] = true;
                                    $appointment_time[$appointment_hour + $i + 1]['0'] = true;
                                }
                            }

                            if ($rest == 0) {
                                $appointment_time[$appointment_hour + $add_hr]['0'] = false;
                                $appointment_time[$appointment_hour + $add_hr]['30'] = false;
                            } elseif ($rest > 0 && $rest <=30) {
                                $appointment_time[$appointment_hour + $add_hr]['0'] = true;
                                $appointment_time[$appointment_hour + $add_hr]['30'] = false;
                            } elseif ($rest > 30) {
                                $appointment_time[$appointment_hour + $add_hr]['0'] = true;
                                $appointment_time[$appointment_hour + $add_hr]['30'] = true;
                            }

                            $appointment_time[$appointment_hour]['time'] = round($total_minutes/60, 2);
                        }
                    }
                }


                # check assigned appointment evey 30 minutes #
                for ($i = 8; $i < 23; $i++) {
                    if (array_key_exists($i, $appointment_time)) {
                        $available_or_scheduled = true;

                        $h = ($i > 12) ? $i - 12 : $i;

                        $groomer_appointment['h' . $i]['start'] = !empty($appointment_time[$i]['minute']) ? $h . ":". $appointment_time[$i]['minute'] : '';
                        $groomer_appointment['h' . $i]['start_minute'] = !empty($appointment_time[$i]['minute']) ? $appointment_time[$i]['minute'] : '';
                        $groomer_appointment['h' . $i]['time'] = !empty($appointment_time[$i]['time']) ? $appointment_time[$i]['time'] : '';
                        $groomer_appointment_id[$i] = !empty($appointment_time[$i]['id']) ? $appointment_time[$i]['id'] : '';

                        // change background color when this time frame has appointment
                        $groomer_appointment_css[$i]['0'] = !empty($appointment_time[$i]['0']) ? 'class=danger' : '';
                        $groomer_appointment_css[$i]['30'] = !empty($appointment_time[$i]['30']) ? 'class=danger' : '';

                    } else {
                        $groomer_appointment['h' . $i]['start'] = '';
                        $groomer_appointment['h' . $i]['time'] = '';
                        $groomer_appointment_id[$i] = '';

                        if (empty($groomer_appointment_css[$i])) {
                            $groomer_appointment_css[$i]['0'] = '';
                            $groomer_appointment_css[$i]['30'] = '';
                        }
                    }
                }
                $g->appointment = $groomer_appointment;
                $g->appointment_css = $groomer_appointment_css;


                ## get Groomer's availability ##
                $availability_hours = array();
                $availability = GroomerAvailability::where('groomer_id', $g->groomer_id)
                    ->where('date', $date)->get();

                foreach ($availability as $a) {
                    $availability_hours[] = $a->hour;
                }

                $groomer_availability = array();
                for ($i = 8; $i < 23; $i++) {
                    if (in_array($i, $availability_hours)) {
                        $available_or_scheduled = true;

                        # 1. Groomer available time frame
                        if (empty($groomer_appointment_css[$i]['0'])) {
                            $groomer_availability['h' . $i]['0'] = 'X';
                        } else {
                            if (!empty($groomer_appointment['h' . $i]['start'])
                                && !empty($groomer_appointment['h' . $i]['time'])
                                && $groomer_appointment['h' . $i]['start_minute'] < 30
                            ) {
                                $groomer_availability['h' . $i]['0'] = '<a href="/admin/appointment/' . $groomer_appointment_id[$i] . '" target="_blank">' . $groomer_appointment['h' . $i]['start'] . '<br>(' . $groomer_appointment['h' . $i]['time'] . 'hr)</a>';
                            } else{
                                $groomer_availability['h' . $i]['0'] = '';
                            }

                        }

                        if (empty($groomer_appointment_css[$i]['30'])) {
                            $groomer_availability['h' . $i]['30'] = 'X';
                        } else {
                            if (!empty($groomer_appointment['h' . $i]['start'])
                                && !empty($groomer_appointment['h' . $i]['time'])
                                && $groomer_appointment['h' . $i]['start_minute'] >= 30
                            ) {
                                $groomer_availability['h' . $i]['30'] = '<a href="/admin/appointment/' . $groomer_appointment_id[$i] . '" target="_blank">' . $groomer_appointment['h' . $i]['start'] . '<br>(' . $groomer_appointment['h' . $i]['time'] . 'hr)</a>';
                            } else{
                                $groomer_availability['h' . $i]['30'] = '';
                            }

                        }

                    } else {

                        # 2. Groomer not available time frame

                        if (empty($groomer_appointment_css[$i]['0'])) {
                            $groomer_availability['h' . $i]['0'] = '';
                        } else {
                            if (!empty($groomer_appointment['h' . $i]['start'])
                                && !empty($groomer_appointment['h' . $i]['time'])
                                && $groomer_appointment['h' . $i]['start_minute'] < 30
                            ) {
                                $groomer_availability['h' . $i]['0'] = '<a href="/admin/appointment/' . $groomer_appointment_id[$i] . '" target="_blank">' . $groomer_appointment['h' . $i]['start'] . '<br>(' . $groomer_appointment['h' . $i]['time'] . 'hr)</a>';
                            } else {
                                $groomer_availability['h' . $i]['0'] = '';
                            }

                        }

                        if (empty($groomer_appointment_css[$i]['30'])) {
                            $groomer_availability['h' . $i]['30'] = '';
                        } else {
                            if (!empty($groomer_appointment['h' . $i]['start'])
                                && !empty($groomer_appointment['h' . $i]['time'])
                                && $groomer_appointment['h' . $i]['start_minute'] < 30
                            ) {
                                $groomer_availability['h' . $i]['30'] = '<a href="/admin/appointment/' . $groomer_appointment_id[$i] . '" target="_blank">' . $groomer_appointment['h' . $i]['start'] . '<br>(' . $groomer_appointment['h' . $i]['time'] . 'hr)</a>';
                            } else {
                                $groomer_availability['h' . $i]['30'] = '';
                            }

                        }
                    }
                }
                $g->availability = $groomer_availability;

                $g->show = $available_or_scheduled;
            }

            return view('admin.appointment_schedule', [
                'msg' => '',
                'groomers' => $groomers,
                'date' => $date
            ]);


        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);
        }
    }

    public function groomer_fulfillment(Request $request) {
        try {

            #####

            if (!empty($request->sdate)) {
                $sd = Carbon::parse($request->sdate);
            } else {
                $sd = Carbon::today();
            }
            $sdate = Carbon::parse($sd)->format('Y-m-d');

            if (!empty($request->edate)) {
                $ed = Carbon::parse($request->edate);
            } else {
                $ed = Carbon::today();
            }
            $edate = Carbon::parse($ed)->format('Y-m-d');

            $groomers = Groomer::whereIn('status',['A', 'N'])
                ->orderBy('level','desc')
                ->orderBy('first_name','asc')
                ->get();

            if (empty($groomers)) {
                return response()->json([
                    'msg' => 'No active groomer was found.'
                ]);
            }

            $summary_data = array();
            $summary_qty_by_hour = [
                'h8' => 0,
                'h830' => 0,
                'h9' => 0,
                'h930' => 0,
                'h10' => 0,
                'h1030' => 0,
                'h11' => 0,
                'h1130' => 0,
                'h12' => 0,
                'h1230' => 0,
                'h13' => 0,
                'h1330' => 0,
                'h14' => 0,
                'h1430' => 0,
                'h15' => 0,
                'h1530' => 0,
                'h16' => 0,
                'h1630' => 0,
                'h17' => 0,
                'h1730' => 0,
                'h18' => 0,
                'h1830' => 0,
                'h19' => 0,
                'h1930' => 0,
                'h20' => 0,
                'h2030' => 0
            ];

            for ($date=$sdate; $date <= $edate ; $date=Carbon::parse($date)->addDays(1)->format('Y-m-d')) { 

                $week = Carbon::parse($date)->dayOfWeek;

                if (!empty($request->weekday) &&  $request->weekday != $week ) continue;

                $week_name = '';
                # because the availability week's format starts from Monday not Sunday

                switch ($week) {
                    case 1: // Monday
                        $week_name = 'Mon';
                        break;
                    case 2: // Thue
                        $week_name = 'Tue';
                        break;
                    case 3: // Wedn
                        $week_name = 'Wed';
                        break;
                    case 4: // Thir
                        $week_name = 'Thu';
                        break;
                    case 5: // Fri
                        $week_name = 'Fri';
                        break;
                    case 6: // Sat
                        $week_name = 'Sat';
                        break;
                    case 0: // Sunday
                        $week_name = 'Sun';
                        break;
                }

                $groomer_ava_summary = array();
                $groomer_css_summary = array();
                $appoint_qty_summary = 0;

                foreach ($groomers as $g) {
                    $g->show_target = true;

                    if (!empty($request->groomer) && $request->groomer != $g->groomer_id) {
                        $g->show_target = false;
                        continue;
                    }

                    $available_or_scheduled = false;

                    ## get Groomer's appointment ##
                    $appointment_time = array();
                    $appointment = AppointmentList::whereIn('status', ['D', 'O', 'W','P', 'F'])
                        ->whereRaw("DATE(accepted_date) = '" . $date . "'")
                        ->select(['accepted_date', 'appointment_id'])
                        ->where('groomer_id', $g->groomer_id)
                        ->get();

                    $groomer_appointment = array();
                    $groomer_appointment_css = array();
                    $groomer_appointment_id = array();

                    if (!empty($appointment)) {
                        $appoint_qty_summary += count($appointment);

                        foreach ($appointment as $ap) {
                            $appointment_hour =  Carbon::parse($ap->accepted_date)->format('G');
                            $appointment_minute = Carbon::parse($ap->accepted_date)->format('i');

                            $appointment_time[$appointment_hour] = array();

                            $appointment_time[$appointment_hour]['minute'] = $appointment_minute;
                            $appointment_time[$appointment_hour]['id'] = $ap->appointment_id;
                            $appointment_time[$appointment_hour]['time'] = '';

                            $pets = DB::select("
                        select 
                            a.pet_id, 
                            c.type as pet_type,
                            c.size as pet_size,
                            b.prod_id as package_id,
                            b.prod_name as package_name
                        from appointment_pet p 
                            inner join appointment_product a on p.appointment_id = a.appointment_id
                            inner join product b on a.prod_id = b.prod_id
                            inner join pet c on p.pet_id = c.pet_id
                        where a.appointment_id = :appointment_id
                        and a.pet_id = p.pet_id
                        and b.prod_type = 'P'
                        and b.pet_type = c.type
                        ", [
                                'appointment_id' => $ap->appointment_id
                            ]);

                            # Calculate total minutes to takes depends on the pet and size of pet #
                            if (!empty($pets)) {
                                $total_minutes = 0;
                                foreach($pets as $o) {
                                    if ($o->pet_type == 'cat') { // 1.5 hr for each cat grooming
                                        # cat
                                        $total_minutes += 90;
                                    } else {
                                        #dog
                                        if ($o->package_id == '2') {
                                            // silver  : 1 hr, regardless of size
                                            $total_minutes +=  60;
                                        } else {
                                            // gold : different by size
                                            if ($o->pet_size == 4 || $o->pet_size == 5) { // large, extra large : 2 hr
                                                $total_minutes += 120;
                                            } else { // other size : 1.5 hr
                                                $total_minutes += 90;
                                            }
                                        }

                                    }
                                }

                                $final_minutes = $total_minutes + $appointment_minute;

                                $add_hr = intval($final_minutes/60);
                                $rest = $final_minutes%60;

                                # check every 30 minutes #
                                for($i=0;$i<$add_hr;$i++) {
                                    if ($appointment_minute < 30) {
                                        $appointment_time[$appointment_hour + $i]['0'] = true;
                                        $appointment_time[$appointment_hour + $i]['30'] = true;
                                    } else {
                                        if ($i == 0) {
                                            $appointment_time[$appointment_hour + $i]['0'] = false;
                                        }

                                        if ($i == $add_hr - 1) {
                                            $appointment_time[$appointment_hour + $i + 1]['30'] = false;
                                        }

                                        $appointment_time[$appointment_hour + $i]['30'] = true;
                                        $appointment_time[$appointment_hour + $i + 1]['0'] = true;
                                    }
                                }

                                if ($rest == 0) {
                                    $appointment_time[$appointment_hour + $add_hr]['0'] = false;
                                    $appointment_time[$appointment_hour + $add_hr]['30'] = false;
                                } elseif ($rest > 0 && $rest <=30) {
                                    $appointment_time[$appointment_hour + $add_hr]['0'] = true;
                                    $appointment_time[$appointment_hour + $add_hr]['30'] = false;
                                } elseif ($rest > 30) {
                                    $appointment_time[$appointment_hour + $add_hr]['0'] = true;
                                    $appointment_time[$appointment_hour + $add_hr]['30'] = true;
                                }

                                $appointment_time[$appointment_hour]['time'] = round($total_minutes/60, 2);
                            }
                        }
                    }


                    # check assigned appointment evey 30 minutes #
                    for ($i = 8; $i < 23; $i++) {
                        if (array_key_exists($i, $appointment_time)) {
                            $available_or_scheduled = true;

                            $h = ($i > 12) ? $i - 12 : $i;

                            $groomer_appointment['h' . $i]['start'] = !empty($appointment_time[$i]['minute']) ? $h . ":". $appointment_time[$i]['minute'] : '';
                            $groomer_appointment['h' . $i]['start_minute'] = !empty($appointment_time[$i]['minute']) ? $appointment_time[$i]['minute'] : '';
                            $groomer_appointment['h' . $i]['time'] = !empty($appointment_time[$i]['time']) ? $appointment_time[$i]['time'] : '';
                            $groomer_appointment_id[$i] = !empty($appointment_time[$i]['id']) ? $appointment_time[$i]['id'] : '';

                            // change background color when this time frame has appointment
                            $groomer_appointment_css[$i]['0'] = !empty($appointment_time[$i]['0']) ? 'class=danger' : '';
                            $groomer_appointment_css[$i]['30'] = !empty($appointment_time[$i]['30']) ? 'class=danger' : '';

                            if (empty($groomer_css_summary[$i]['0'])) {
                                $groomer_css_summary[$i]['0'] = !empty($appointment_time[$i]['0']) ? 'class=danger' : '';
                            }
                            if (empty($groomer_css_summary[$i]['30'])) {
                                $groomer_css_summary[$i]['30'] = !empty($appointment_time[$i]['30']) ? 'class=danger' : '';
                            }

                        } else {
                            $groomer_appointment['h' . $i]['start'] = '';
                            $groomer_appointment['h' . $i]['time'] = '';
                            $groomer_appointment_id[$i] = '';

                            if (empty($groomer_appointment_css[$i])) {
                                $groomer_appointment_css[$i]['0'] = '';
                                $groomer_appointment_css[$i]['30'] = '';
                            }

                            if (empty($groomer_css_summary[$i])) {
                                $groomer_css_summary[$i]['0'] = '';
                                $groomer_css_summary[$i]['30'] = '';
                            }
                        }
                    }
                    $g->appointment = $groomer_appointment;
                    $g->appointment_css = $groomer_appointment_css;


                    ## get Groomer's availability ##
                    $availability_hours = array();
                    $availability = GroomerAvailability::where('groomer_id', $g->groomer_id)
                        ->where('date', $date)->get();

                    foreach ($availability as $a) {
                        $availability_hours[] = $a->hour;
                    }

                    $groomer_availability = array();
                    for ($i = 8; $i < 23; $i++) {
                        if (in_array($i, $availability_hours)) {
                            $available_or_scheduled = true;

                            # 1. Groomer available time frame
                            if (empty($groomer_appointment_css[$i]['0'])) {
                                $groomer_availability['h' . $i]['0'] = 'X';

                                if (empty($groomer_ava_summary['h' . $i]['0'])) {
                                    $groomer_ava_summary['h' . $i]['0'] = 'X';
                                }
                            } else {
                                if (!empty($groomer_appointment['h' . $i]['start'])
                                    && !empty($groomer_appointment['h' . $i]['time'])
                                    && $groomer_appointment['h' . $i]['start_minute'] < 30
                                ) {
                                    $groomer_availability['h' . $i]['0'] = '<a href="/admin/appointment/' . $groomer_appointment_id[$i] . '" target="_blank">' . $groomer_appointment['h' . $i]['start'] . '<br>(' . $groomer_appointment['h' . $i]['time'] . 'hr)</a>';

                                    if (empty($groomer_ava_summary['h' . $i]['0'])) $groomer_ava_summary['h' . $i]['0'] = 0;
                                    $groomer_ava_summary['h' . $i]['0'] = $groomer_ava_summary['h' . $i]['0'] + 1;
                                    $summary_qty_by_hour['h' . $i] = $summary_qty_by_hour['h' . $i] + 1;
                                } else{
                                    $groomer_availability['h' . $i]['0'] = '';
                                }

                            }

                            if (empty($groomer_appointment_css[$i]['30'])) {
                                $groomer_availability['h' . $i]['30'] = 'X';

                                if (empty($groomer_ava_summary['h' . $i]['30'])) {
                                    $groomer_ava_summary['h' . $i]['30'] = 'X';
                                }
                            } else {
                                if (!empty($groomer_appointment['h' . $i]['start'])
                                    && !empty($groomer_appointment['h' . $i]['time'])
                                    && $groomer_appointment['h' . $i]['start_minute'] >= 30
                                ) {
                                    $groomer_availability['h' . $i]['30'] = '<a href="/admin/appointment/' . $groomer_appointment_id[$i] . '" target="_blank">' . $groomer_appointment['h' . $i]['start'] . '<br>(' . $groomer_appointment['h' . $i]['time'] . 'hr)</a>';

                                    if (empty($groomer_ava_summary['h' . $i]['30'])) $groomer_ava_summary['h' . $i]['30'] = 0;
                                    $groomer_ava_summary['h' . $i]['30'] = $groomer_ava_summary['h' . $i]['30'] + 1;
                                    $summary_qty_by_hour['h' . $i . '30'] = $summary_qty_by_hour['h' . $i. '30'] + 1;
                                } else{
                                    $groomer_availability['h' . $i]['30'] = '';

                                    if (empty($groomer_ava_summary['h' . $i]['30'])) {
                                        $groomer_ava_summary['h' . $i]['30'] = '';
                                    }
                                }

                            }

                        } else {

                            # 2. Groomer not available time frame

                            if (empty($groomer_appointment_css[$i]['0'])) {
                                $groomer_availability['h' . $i]['0'] = '';

                                if (empty($groomer_ava_summary['h' . $i]['0'])) {
                                    $groomer_ava_summary['h' . $i]['0'] = '';
                                }
                            } else {
                                if (!empty($groomer_appointment['h' . $i]['start'])
                                    && !empty($groomer_appointment['h' . $i]['time'])
                                    && $groomer_appointment['h' . $i]['start_minute'] < 30
                                ) {
                                    $groomer_availability['h' . $i]['0'] = '<a href="/admin/appointment/' . $groomer_appointment_id[$i] . '" target="_blank">' . $groomer_appointment['h' . $i]['start'] . '<br>(' . $groomer_appointment['h' . $i]['time'] . 'hr)</a>';

                                    if (empty($groomer_ava_summary['h' . $i]['0'])) $groomer_ava_summary['h' . $i]['0'] = 0;
                                    $groomer_ava_summary['h' . $i]['0'] = $groomer_ava_summary['h' . $i]['0'] + 1;
                                    $summary_qty_by_hour['h' . $i] = $summary_qty_by_hour['h' . $i] + 1;
                                } else {
                                    $groomer_availability['h' . $i]['0'] = '';

                                    if (empty($groomer_ava_summary['h' . $i]['0'])) {
                                        $groomer_ava_summary['h' . $i]['0'] = '';
                                    }
                                }

                            }

                            if (empty($groomer_appointment_css[$i]['30'])) {
                                $groomer_availability['h' . $i]['30'] = '';

                                if (empty($groomer_ava_summary['h' . $i]['30'])) {
                                    $groomer_ava_summary['h' . $i]['30'] = '';
                                }
                            } else {
                                if (!empty($groomer_appointment['h' . $i]['start'])
                                    && !empty($groomer_appointment['h' . $i]['time'])
                                    && $groomer_appointment['h' . $i]['start_minute'] < 30
                                ) {
                                    $groomer_availability['h' . $i]['30'] = '<a href="/admin/appointment/' . $groomer_appointment_id[$i] . '" target="_blank">' . $groomer_appointment['h' . $i]['start'] . '<br>(' . $groomer_appointment['h' . $i]['time'] . 'hr)</a>';

                                    if (empty($groomer_ava_summary['h' . $i]['30'])) $groomer_ava_summary['h' . $i]['30'] = 0;
                                    $groomer_ava_summary['h' . $i]['30'] = $groomer_ava_summary['h' . $i]['30'] + 1;
                                    $summary_qty_by_hour['h' . $i . '30'] = $summary_qty_by_hour['h' . $i . '30'] + 1;

                                } else {
                                    $groomer_availability['h' . $i]['30'] = '';

                                    if (empty($groomer_ava_summary['h' . $i]['30'])) {
                                        $groomer_ava_summary['h' . $i]['30'] = '';
                                    }
                                }

                            }
                        }
                    } // End for ($i = 8; $i < 23; $i++) {

                    $g->availability = $groomer_availability;
                    $g->show = $available_or_scheduled;

                    if (!empty($request->groomer)) {
                        $summary_data[] = [
                            'date'              => $date,
                            'week_name'         => $week_name,
                            'appointment'       => $groomer_appointment,
                            'appointment_css'   => $groomer_appointment_css,
                            'availability'      => $groomer_availability,
                            'appoint_qty_summary' => $appoint_qty_summary,
                            'gromer_name'       => $g->first_name . ' ' . $g->last_name,
                            'service_area'      => $g->service_area
                        ];
                    }
                }

                if (empty($request->groomer)) {
                    $summary_data[] = [
                        'date'              => $date,
                        'week_name'         => $week_name,
                        'availability'      => $groomer_ava_summary,
                        'appointment_css'   => $groomer_css_summary,
                        'appoint_qty_summary' => $appoint_qty_summary
                    ];
                }
            }

            return view('admin.groomer_fulfillment', [
                'msg' => '',
                'groomer'   => $request->groomer,
                'groomers'  => $groomers,
                'summary_data' => $summary_data,
                'summary_qty_by_hour' => $summary_qty_by_hour,
                'sdate'     => $sdate,
                'edate'     => $edate,
                'weekday'   => $request->weekday
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . '] : ' . $ex->getTraceAsString()
            ]);
        }
    }


    public function getGroomerSchedule(Request $request) {
        try {

            if (!empty($request->date)) {
                $d = Carbon::parse($request->date);
            } else {
                $d = Carbon::today();
            }

            $mon = Carbon::parse($d)->format('Y-m');

            $groomers = Groomer::whereIn('status',['A', 'N'])
                ->orderBy('level','desc')
                ->orderBy('first_name','asc')
                ->get();

            if (empty($groomers)) {
                return response()->json([
                    'msg' => 'No active groomer was found.'
                ]);
            }

            foreach ($groomers as $g) {
                ## get Groomer's availability ##
                $availability = GroomerAvailability::where('groomer_id', $g->groomer_id)
                    ->whereRaw("date like '".$mon."-%'")
                    ->groupBy('date')
                    ->get();

                $availability_days = array();
                foreach ($availability as $a) {
                    $availability_days[] = intval(substr($a->date, -2));
                }

                $groomer_availability = array();
                for ($i = 1; $i <= 31; $i++) {
                    if (in_array($i, $availability_days)) {
                        $hour_cnt = GroomerAvailability::where('groomer_id', $g->groomer_id)
                            ->whereRaw('date like ?', [$mon . '-' . str_pad($i, 2,'0', STR_PAD_LEFT)])
                            ->count();

                        $groomer_availability['d' . $i] = $hour_cnt;
                    } else {
                        $groomer_availability['d' . $i] = '';
                    }
                }
                $g->availability = $groomer_availability;

            }

            return view('admin.groomer_schedule', [
                'msg' => '',
                'groomers' => $groomers,
                'date' => $mon
            ]);


        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function updateOpNote(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'appointment_id' => 'required',
                'op_note' => 'required'
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

            $ap = AppointmentList::find($request->appointment_id);
            if (empty($ap)) {
                return response()->json([
                    'msg' => 'Invalid appointment ID provided'
                ]);
            }

            $ap->op_note = $request->op_note;
            $ap->save();

            return response()->json([
                'msg' => ''
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }


    public function sendServiceCompletionEmail(Request $request) {
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


            $ap = AppointmentList::where('appointment_id', $request->id)->first();

            if (empty($ap)) {
                return response()->json([
                    'msg' => 'Invalid appointment ID provided'
                ]);
            }

            $user = User::findOrFail($ap->user_id);

            if (empty($user)) {
                return response()->json([
                    'msg' => 'Invalid user ID provided'
                ]);
            }

            $groomer = Groomer::where('groomer_id', $ap->groomer_id)->select('first_name', 'last_name')->first();

            if (empty($groomer)) {
                return response()->json([
                    'msg' => 'Invalid groomer ID provided'
                ]);
            }
            $groomer_name = $groomer->first_name . ' ' . $groomer->last_name;;

            $payment = UserBilling::where('user_id', $ap->user_id)
                ->where('billing_id', $ap->payment_id)
                ->first();

            if (empty($payment)) {
                return response()->json([
                    'msg' => 'Invalid payment ID provided'
                ]);
            }

            $pets = DB::select("
            select 
                a.pet_id, 
                p.sub_total,
                p.tax, 
                p.total, 
                c.name as pet_name,
                c.dob as pet_dob,
                timestampdiff(month, c.dob, curdate()) as age,
                b.prod_id as package_id,
                b.prod_name as package_name,
                a.amt as price
            from appointment_pet p 
                inner join appointment_product a on p.appointment_id = a.appointment_id
                inner join product b on a.prod_id = b.prod_id
                inner join pet c on p.pet_id = c.pet_id
            where a.appointment_id = :appointment_id
            and a.pet_id = p.pet_id
            and b.prod_type = 'P'
            and b.pet_type = c.type
            ", [
                'appointment_id' => $ap->appointment_id
            ]);

            $subject = "Your Groomit Appointment has been completed";

            $data = [];
            $data['appointment_id'] = $ap->appointment_id;
            $data['email'] = $user->email;
            $data['name'] = $user->first_name;
            $data['subject'] = $subject;
            $data['groomer'] = $groomer_name;
            $data['card_holder'] = $payment->card_holder;
            $data['card_number'] = substr($payment->card_number, -4);
            $data['safety_insurance'] = $ap->safety_insurance;
            $data['sub_total'] = $ap->sub_total;
            $data['promo_code'] = $ap->promo_code;
            $data['promo_amt'] = $ap->promo_amt;
            $data['credit_amt'] = $ap->credit_amt;
            $data['tax'] = $ap->tax;
            $data['total'] = $ap->total;
            $data['payment_date'] = Carbon::now()->toDayDateTimeString();

            foreach ($pets as $k=>$v) {
                $data['pet'][$k]['pet_name'] = $v->pet_name;
                $data['pet'][$k]['package_name'] = $v->package_name;
                $data['pet'][$k]['sub_total'] = $v->sub_total;
            }

            $data['accepted_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $ap->accepted_date)->format('l, F j Y, h:i A');

            $data['bcc'] = 'tech@groomit.me';

            $ret = Helper::send_html_mail('service_completion', $data);

            if (!empty($ret)) {
                $msg = 'Failed to send service completion email: ' . $ret;
                return $msg;
            }


            return response()->json([
                'msg' => ''
            ]);


        } catch (\Exception $ex) {
            return response()->json([
                'msg' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function toggleDelayed(Request $request) {
        try {

            $v = Validator::make($request->all(), [
                'appointment_id' => 'required',
                'delayed' => 'required|in:Y,N'
            ]);

            if ($v->fails()) {
                return back()->withErrors($v);
            }

            $app = AppointmentList::find($request->appointment_id);
            if (empty($app)) {
                return back()->withErrors([
                    'exception' => 'Invalid apointment ID provided'
                ]);
            }

            $app->delayed = $request->delayed;
            $app->mdate = Carbon::now();
            $app->modified_by = Auth::guard('admin')->user()->admin_id;
            $app->save();

            return back()->with([
                'success' => 'Y'
            ]);

        } catch (\Exception $ex) {
            return back()->withErrors([
                'exception' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    public function fulfillment_schedule(Request $request)
    {
        try {
            if (!empty($request->date)) {
                $d = Carbon::parse($request->date);
            } else {
                $d = Carbon::today();
            }

            $date = Carbon::parse($d)->format('Y-m-d');
            $week = Carbon::parse($d)->dayOfWeek;

            # because the availability week's format starts from Monday not Sunday
            switch ($week) {
                case 0: // Sunday
                    $w = 6;
                    break;
                default:
                    $w = $week - 1;
                    break;
            }

            $query_county = '';
            if (!empty($request->county)) {
                $query_county = "and lower(concat(az.county_name, '/', az.state_abbr)) = '" . strtolower($request->county) . "'";
            }

            ### fulfillment ###
            $temp_fulfillments = DB::select("
                    select vas.*, concat(g.first_name, ' ', g.last_name) as groomer_name, concat(az.county_name, '/', az.state_abbr) county
                      from vw_appointment_schedule vas
                      join groomer g on vas.groomer_id = g.groomer_id
                      join address ad on ad.address_id = vas.address_id
                      join allowed_zip az on az.zip = ad.zip
                     where vas.adate = :adate
                     " . $query_county . "
                       and g.status in ('A', 'N')
                     order by vas.groomer_id asc, vas.adate desc, vas.appointment_id desc, vas.dtime asc
                    ", [
              'adate' => $date
            ]);

            $fulfillments = array();
            foreach ($temp_fulfillments as $ful) {
                if (empty($fulfillments[$ful->groomer_id])) {
                    $fulfillments[$ful->groomer_id] = array();
                }
                for($i = $ful->id_from; $i <= $ful->id_to; $i++) {
                    $ful->address = Address::find($ful->address_id);
                    $ful->allowed_zip = AllowedZip::where('zip', $ful->address->zip)->first();
                    $fulfillments[$ful->groomer_id][$i] = $ful;
                }
            }

            ### Availabilities ###
            $temp_availabilities = DB::select("
                    select ga.groomer_id, ga.hour, t.id
                      from groomer_availability ga
                      join times t on t.hour = ga.hour
                     where ga.date = :adate
                       and ga.groomer_id in (select groomer_id from groomer where status in ('A', 'N'))
                     order by t.id asc
                    ", [
              'adate' => $date
            ]);

            $availabilities = array();
            foreach($temp_availabilities as $avb) {
                if (empty($availabilities[$avb->groomer_id])) {
                    $availabilities[$avb->groomer_id] = array();
                }
                $availabilities[$avb->groomer_id][$avb->id] = $avb->hour;
            }

            $groomers = Groomer::whereIn('status',['A', 'N'])
              ->orderBy('level','desc')
              ->orderBy('first_name','asc')
              ->get();

//            foreach ($groomers as $g) {
//                $g->show = false;
//
//                $data = array();
//                foreach ($fulfillments as $ful) {
//                    $data[$ful->id] = $ful;
//                }
//                $g->fulfillment = $data;
//
//                $available_hours = GroomerAvailability::where('groomer_id', $g->groomer_id)->where('date', $date)->count();
//
//                if ($available_hours > 0) {
//                    $g->show = true;
//
//                    ### groomer availabilities ###
//                    $g->availabilities = DB::select("
//                    select t.*, ga.groomer_id
//                      from times t
//                      left join groomer_availability ga on t.hour = ga.hour and ga.date = :adate and ga.groomer_id = :groomer_id
//                     order by t.id asc
//                    ", [
//                      'adate' => $date,
//                      'groomer_id' => $g->groomer_id
//                    ]);
//                } else {
//                    if (!empty($fulfillments) && count($fulfillments) > 0) {
//                        $g->show = true;
//                    }
//                }
//
//            }

            $times = Times::where('status', 'A')->orderBy('id', 'asc')->get();

            $counties = DB::select("
                select distinct county_name, state_abbr
                from allowed_zip
                where lower(available) = 'x' 
                order by 2, 1
            ");

            return view('admin.fulfillment_schedule', [
                'times'         => $times,
                'groomers'      => $groomers,
                'date'          => $date,
                'county'        => $request->county,
                'counties'      => $counties,
                'fulfillments'  => $fulfillments,
                'availabilities' => $availabilities
            ]);

        } catch (\Exception $ex) {
            Helper::log('#### EXCEPTION ####', $ex->getTraceAsString());
            return back()->withErrors([
              'exception' => $ex->getMessage() . ' [' . $ex->getCode() . ']'
            ]);
        }
    }

    //Called after cancel w/ fee charges, or refund after Cancelled.
    //Need to refund difference amount only, if there exist 'Partial Void'.
    public function appointment_refund($appointment_id) {
         //S category only, not for 'W' or 'T'
        //Refund the all existing ones w/ 'S' only, not 'W','T', because it'll be called after charge w/ Cancel w/ fee first, or refund after cancelled.
//        $cctrans = CCTrans::where('appointment_id', $appointment_id)
//          ->whereIn('type', ['A', 'S'])
//          ->where('category', 'S')
//          ->where('result', 0)
//          ->whereNull('void_date')
//          ->orderBy('id','asc')
//          ->get();
//
//        foreach($cctrans as $cctran) {
//            $ret = Converge::void($appointment_id, 'S', $cctran->void_ref, $cctran->type ); //Full voids, not partial voids.
//            if (!empty($ret['error_msg'])) {
//                Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Failed to void CC trans at appointment_refund : ' . $appointment_id, $ret['error_code'] . ' [' . $ret['error_msg'] . ']');
//                /*return response()->json([
//                    'msg' => 'Failed to void auth only credit card transaction'
//                ]);*/
//            }
//        }
//
        //Called after cancel w/ fee charges, or refund after Cancelled.
        //Refund the all existing ones w/ 'S' only, not 'W','T', because it'll be called after charge w/ Cancel w/ fee first, or refund after cancelled.
        $cctrans = DB::select("
                select a.id, a.appointment_id, a.type, a.category, a.token, a.amt - IfNull(b.amt,0) as amt, a.cdate, a.void_ref, b.orig_sales_id 
                from cc_trans a left join cc_trans b on a.id = b.orig_sales_id and b.appointment_id = :appointment_id1 and b.type ='V' and b.result = 0 and b.error_name = 'Partial Void' 
                where a.appointment_id = :appointment_id2
                and a.type in ('A','S')
                and a.category = 'S'
                and a.result = 0
                and a.void_date is null 
            ", [
            'appointment_id1' => $appointment_id,
            'appointment_id2' => $appointment_id
        ]);

        foreach($cctrans as $cctran) {
            if (!empty($cctran->orig_sales_id) && $cctran->orig_sales_id > 0) {
                $ret = Converge::void($appointment_id, 'S', $cctran->void_ref, $cctran->type, $cctran->amt); //Partial voids, if there existed 'Partial Void
            } else {
                $ret = Converge::void($appointment_id, 'S', $cctran->void_ref, $cctran->type); //Full voids, not partial voids.
            }
            if (!empty($ret['error_msg'])) {
                Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Failed to void CC trans at appointment_refund : ' . $appointment_id, $ret['error_code'] . ' [' . $ret['error_msg'] . ']');
                /*return response()->json([
                    'msg' => 'Failed to void auth only credit card transaction'
                ]);*/
            }
        }
        return back();
    }

    //Do not use anuy longer.
//    public function appointment_repayment($appointment_id) {
//
//        $app = AppointmentList::find($appointment_id);
//
//
//        if ($app->total > 0) {
//            ### First : find auth only transaction .
//
//            $sum_auth_only_trans= CCTrans::where('appointment_id', $appointment_id)
//                ->where('type', 'A')
//                ->where('category', 'S')
//                ->where('result', 0)
//                ->whereNull('void_date')
//                ->where('amt', '!=', 0.01)
//                ->sum('amt');
//
//            if($sum_auth_only_trans > 0) {
//                if ( $sum_auth_only_trans == $app->total) {
//                    back();
//                }
//
//                $all_auth_only_trans = CCTrans::where('appointment_id', $appointment_id )
//                    ->where('type', 'A')
//                    ->where('category', 'S')
//                    ->where('result', 0)
//                    ->whereNull('void_date')
//                    ->where('amt', '!=', 0.01);
//
//                foreach( $all_auth_only_trans as $auth_only_trans) {
//                    //This create a new record of 'S' type at cc_trans
//                    $ret = Converge::complete($auth_only_trans->void_ref, $auth_only_trans->token, $auth_only_trans->amt, $appointment_id, 'S');
//                    if (!empty($ret['error_msg'])) {
//                        ### notify tech@groomit.me ###
//                        $msg = $ret['error_msg'] . ' [ ' . $ret['error_code'] . ']';
//                        Helper::send_mail('tech@groomit.me', '[GROOMIT][' . getenv('APP_ENV') . '] Auth-Complete Failed : ' . $app->appointment_id, $msg);
//                        Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Auth-Complete Failed : ' . $app->appointment_id, $msg);
//
//                        //throw new \Exception($ret['error_msg'], $ret['error_code']);
//                        ### auth complete has failed so we need sales ###
//                    }
//                }
//            }
//
//            $payment = UserBilling::find($app->payment_id);
//
//            $total_paid_trans = CCTrans::where('appointment_id', $appointment_id)
//                ->whereIn('type', ['S','V'])
//                ->where('category', 'S')
//                ->where('result', 0)
//                ->whereNull('void_date')
//                ->sum( DB::raw("case when type = 'S' then amt else -amt end") );
//                //->sum(\Illuminate\Support\Facades\DB::raw("case type when 'S' then amt else -amt end"));
//                //->sum('amt');
//
//            //Charge the difference amount only.
//            if ($total_paid_trans < $app->total ) {
//                $ret = Converge::sales($payment->card_token, $app->total - $total_paid_trans, $appointment_id, 'S');
//                if (!empty($ret['error_msg'])) {
//                    $app->status = 'F';
//                    $app->mdate = Carbon::now();
//                    $u = Auth::guard('admin')->user();
//                    $app->modified_by = isset($u) ? $u->name . '(' . $u->admin_id . ')' : null;
//                    $app->save();
//
//                    ### notify tech as well  ###
//                    Helper::send_mail('tech@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Re-Payment credit card processing charges failed', ' - appointment : ' . $app->appointment_id . '<br> - error : ' . $ret['error_msg']);
//                    Helper::send_mail('help@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Re-Payment credit card processing charges failed', ' - appointment : ' . $app->appointment_id . '<br> - error : ' . $ret['error_msg']);
//
//                    throw new \Exception($ret['error_msg'], $ret['error_code']);
//                }
//            }else if ($total_paid_trans > $app->total ) {
//                //refund partial amount.
//                $first_auth_only_trans = CCTrans::where('appointment_id', $appointment_id)
//                    ->whereIn('type', ['A', 'S'])
//                    ->where('category', 'S')
//                    ->where('result', 0)
//                    ->whereNull('void_date')
//                    ->where('amt', '!=', 0.01)
//                    ->orderBy('amt','desc')
//                    ->first();
//
//                $ret = Converge::void($appointment_id, 'S', $first_auth_only_trans->void_ref,$first_auth_only_trans->type,$app->total - $total_paid_trans );
//
//                if (!empty($ret['error_msg'])) {
//                    $app->status = 'F';
//                    $app->mdate = Carbon::now();
//                    $u = Auth::guard('admin')->user();
//                    $app->modified_by = isset($u) ? $u->name . '(' . $u->admin_id . ')' : null;
//                    $app->save();
//
//                    Helper::send_mail('tech@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Re-Payment credit card processing refunds failed', ' - appointment : ' . $app->appointment_id . '<br> - error : ' . $ret['error_msg']);
//                    Helper::send_mail('help@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Re-Payment credit card processing refunds failed', ' - appointment : ' . $app->appointment_id . '<br> - error : ' . $ret['error_msg']);
//                    Helper::send_mail('it@jjonbp.com', '[Groomit][' . getenv('APP_ENV') . '] Re-Payment credit card processing refunds failed', ' - appointment : ' . $app->appointment_id . '<br> - error : ' . $ret['error_msg']);
//
//                    throw new \Exception($ret['error_msg'], $ret['error_code']);
//                }
//            }
//        }
//        return back();
//    }

    //Called by CS only after updating prices of an appointment.
    //This will not be used any longer, because $0.01 holding/void only at 2hr before. No holding full amount any longer.
    public function appointment_reholding($appointment_id)
    {

        $app = AppointmentList::find($appointment_id);

        $payment = UserBilling::find($app->payment_id);

        ### First : find total amount of auth only & Sales together.
        $sum_all_trans = CCTrans::where('appointment_id', $appointment_id)
            ->whereIn('type', ['A', 'S','V'])
            ->where('category', 'S')
            ->where('result', 0)
            //->whereNull('void_date')
            ->where('amt', '!=', 0.01)
            ->where( DB::raw('IfNull(error_name,"")'), '!=', 'cccomplete' )
            ->sum( DB::raw("case type when 'S' then amt when 'A' then amt else -amt end") );

        if ($sum_all_trans == '') {
            $sum_all_trans = 0;
        }

        //Find sales only. This is not the full sales amount, because of Void. Voids could be for Auth_only or Sales.
        //Just to see if sales(not voided) exist or not.
        $sum_sales_trans = CCTrans::where('appointment_id', $appointment_id)
            ->where('type', 'S')
            ->where('category', 'S')
            ->where('result', 0)
            ->whereNull('void_date')
            ->sum('amt');
        if ($sum_sales_trans == '') {
            $sum_sales_trans = 0;
        }

        if ($sum_all_trans == $app->total) {
            back(); //Do nothing if auth_only/sales cover new total amount.
        }

        //If not match, need to charge more or refund, depending on sales amount.

        if ($sum_sales_trans == 0) { //When no 'Sales' transaction exist. This means all are Auth_only or no tx at all yetl

            if ($sum_all_trans < $app->total) { //Charge more when auth_only is less than new total amount.
                $ret = Converge::auth_only($payment->card_token,$app->total - $sum_all_trans, $appointment_id, 'S');
                if (!empty($ret['error_msg'])) {
                    $app->status = 'F';
                    $app->mdate = Carbon::now();
                    $u = Auth::guard('admin')->user();
                    $app->modified_by = isset($u) ? $u->name . '(' . $u->admin_id . ')' : null;
                    $app->save();

                    $msg = $ret['error_msg'] . ' [ ' . $ret['error_code'] . ']';
                    Helper::send_mail('tech@groomit.me', '[GROOMIT][' . getenv('APP_ENV') . '] Re-holding more Failed : ' . $app->appointment_id, $msg);
                    Helper::send_mail('help@groomit.me', '[GROOMIT][' . getenv('APP_ENV') . '] Re-holding more Failed : ' . $app->appointment_id, $msg);
                    Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Re-holding more Failed : ' . $app->appointment_id, $msg);

                    throw new \Exception($ret['error_msg'], $ret['error_code']);
                }
            }else if ($sum_all_trans > $app->total) { //refund extra amount. => make sales, then refund partials.

                $first_auth_only_trans = CCTrans::where('appointment_id', $appointment_id)
                    ->where('type', 'A')
                    ->where('category', 'S')
                    ->where('result', 0)
                    ->whereNull('void_date')
                    ->where('amt', '!=', 0.01)
                    ->orderBy('amt','desc')
                    ->first();

                $ret = Converge::void($appointment_id, 'S', $first_auth_only_trans->void_ref,$first_auth_only_trans->type,$sum_all_trans - $app->total  );

                if (!empty($ret['error_msg'])) {
                    $app->status = 'F';
                    $app->mdate = Carbon::now();
                    $u = Auth::guard('admin')->user();
                    $app->modified_by = isset($u) ? $u->name . '(' . $u->admin_id . ')' : null;
                    $app->save();

                    Helper::send_mail('tech@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Re-Payment credit card processing refunds failed', ' - appointment : ' . $app->appointment_id . '<br> - error : ' . $ret['error_msg']);
                    Helper::send_mail('help@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Re-Payment credit card processing refunds failed', ' - appointment : ' . $app->appointment_id . '<br> - error : ' . $ret['error_msg']);
                    Helper::send_mail('it@jjonbp.com', '[Groomit][' . getenv('APP_ENV') . '] Re-Payment credit card processing refunds failed', ' - appointment : ' . $app->appointment_id . '<br> - error : ' . $ret['error_msg']);

                    throw new \Exception($ret['error_msg'], $ret['error_code']);
                }
            }

        } else { //When sales transaction exist. auth_only could exist or not(ECO)
            if ($sum_all_trans < $app->total) { //Charge more when auth_only is less than new total amount.
                $ret = Converge::auth_only($payment->card_token,$app->total - $sum_all_trans, $appointment_id, 'S');
                if (!empty($ret['error_msg'])) {
                    $app->status = 'F';
                    $app->mdate = Carbon::now();
                    $u = Auth::guard('admin')->user();
                    $app->modified_by = isset($u) ? $u->name . '(' . $u->admin_id . ')' : null;
                    $app->save();

                    $msg = $ret['error_msg'] . ' [ ' . $ret['error_code'] . ']';
                    Helper::send_mail('tech@groomit.me', '[GROOMIT][' . getenv('APP_ENV') . '] Re-holding more Failed : ' . $app->appointment_id, $msg);
                    Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Re-holding more Failed : ' . $app->appointment_id, $msg);

                    throw new \Exception($ret['error_msg'], $ret['error_code']);
                }
            }else if ($sum_all_trans > $app->total) { //refund extra amount. => make sales for all auth_only, then refund partials.
                //Make all holdings to complete first.
                //Then, refund the difference.
                $all_auth_only_trans = CCTrans::where('appointment_id', $appointment_id)
                    ->where('type', 'A')
                    ->where('category', 'S')
                    ->where('result', 0)
                    ->whereNull('void_date')
                    ->where('amt', '!=', 0.01)
                    ->get();

                foreach ($all_auth_only_trans as $auth_only_trans) {
                    //This create a new record of 'S' type at cc_trans
                    $ret = Converge::complete($auth_only_trans->void_ref, $auth_only_trans->token, $auth_only_trans->amt, $appointment_id, 'S');
                    if (!empty($ret['error_msg'])) {
                        $app->status = 'F';
                        $app->mdate = Carbon::now();
                        $u = Auth::guard('admin')->user();
                        $app->modified_by = isset($u) ? $u->name . '(' . $u->admin_id . ')' : null;
                        $app->save();

                        ### notify tech@groomit.me ###
                        $msg = $ret['error_msg'] . ' [ ' . $ret['error_code'] . ']';
                        Helper::send_mail('tech@groomit.me', '[GROOMIT][' . getenv('APP_ENV') . '] Auth-Complete Failed while refunding : ' . $app->appointment_id, $msg);
                        Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Auth-Complete Failed  while refunding : ' . $app->appointment_id, $msg);
                        ### auth complete has failed so we need sales ###
                        throw new \Exception($ret['error_msg'], $ret['error_code']);
                    }
                }

                //Refunds the difference from the biggest amount.
                $all_completed_trans = CCTrans::where('appointment_id', $appointment_id)
                    ->whereIn('type', ['A', 'S'])
                    ->where('category', 'S')
                    ->where('result', 0)
                    ->whereNull('void_date')
                    ->where('amt', '!=', 0.01)
                    ->orderBy('amt', 'desc')
                    ->first();

                $ret = Converge::void($appointment_id, 'S', $all_completed_trans->void_ref, $all_completed_trans->type, $sum_all_trans - $app->total);

                if (!empty($ret['error_msg'])) {
                    $app->status = 'F';
                    $app->mdate = Carbon::now();
                    $u = Auth::guard('admin')->user();
                    $app->modified_by = isset($u) ? $u->name . '(' . $u->admin_id . ')' : null;
                    $app->save();

                    Helper::send_mail('tech@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Re-Payment credit card processing refunds failed', ' - appointment : ' . $app->appointment_id . '<br> - error : ' . $ret['error_msg']);
                    Helper::send_mail('help@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Re-Payment credit card processing refunds failed', ' - appointment : ' . $app->appointment_id . '<br> - error : ' . $ret['error_msg']);
                    Helper::send_mail('it@jjonbp.com', '[Groomit][' . getenv('APP_ENV') . '] Re-Payment credit card processing refunds failed', ' - appointment : ' . $app->appointment_id . '<br> - error : ' . $ret['error_msg']);

                    throw new \Exception($ret['error_msg'], $ret['error_code']);
                }
            }
        }

        return back();
    }

    //Will be used by CS only, when there exist difference between charged amount and new amount of appointment.
    //Same logic of charge()
    public function appointment_chargerefund($appointment_id)
    {
        $app = AppointmentList::find($appointment_id);

        $void_ref = '';

        if (empty($app)) {
            throw new \Exception('Invalid appointment ID provided', -1);
        }

        if ($app->status != 'P') { //This will be used only when Payment completed.
            throw new \Exception('Invalid appointment status provided: ' . $app->status, -2);
        }

        $user = User::findOrFail($app->user_id);

        ### charge credit card ###
        $payment = UserBilling::find($app->payment_id);
        $proc = new AppointmentProcessor();

        if (empty($payment)) {
            $app->status = 'F';
            $app->mdate = Carbon::now();

            $u = Auth::guard('admin')->user();
            $app->modified_by = isset($u) ? $u->name . '(' . $u->admin_id . ')' : null;
            $app->save();

            Helper::send_mail('tech@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - empty payment info of appointment : ' . $app->appointment_id);
            Helper::send_mail('help@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - empty payment info of appointment : ' . $app->appointment_id);
            Helper::send_mail('it@jjonbp.com', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - empty payment info of appointment : ' . $app->appointment_id);

            $proc = new AppointmentProcessor();
            $proc->send_failure_email($app, $user);

            $msg = 'Payment information cannot be found for the appointment';
                throw new \Exception($msg);
        }

        if (empty($payment->card_token)) {
            $app->status = 'F';

            $app->mdate = Carbon::now();

            $u = Auth::guard('admin')->user();
            $app->modified_by = isset($u) ? $u->name . '(' . $u->admin_id . ')' : null;

            $app->save();

            Helper::send_mail('tech@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - empty card token for appointment : ' . $app->appointment_id);
            Helper::send_mail('help@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - empty card token for appointment : ' . $app->appointment_id);
            Helper::send_mail('it@jjonbp.com', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - empty card token for appointment : ' . $app->appointment_id);

            $proc->send_failure_email($app, $user);

            $msg = 'Payment card token is empty.';
            throw new \Exception($msg);

        }

        Helper::log('### Before converge sales ###');
        $void_ref = '';
        //if ($app->total > 0) { total could be $0 when cs want to refund in full amount by applying promo code.
            ## First, complete auth only transactions
            $all_auth_only_trans = CCTrans::where('appointment_id', $app->appointment_id)
                ->where('type', 'A')
                ->where('category', 'S')
                ->where('result', 0)
                ->whereNull('void_date')
                ->where('amt', '!=', 0.01)
                ->get();

            foreach( $all_auth_only_trans as $auth_only_trans) {
                    //This create a new record of 'S' type at cc_trans
                $ret = Converge::complete($auth_only_trans->void_ref, $auth_only_trans->token, $auth_only_trans->amt, $app->appointment_id, 'S');
                if (!empty($ret['error_msg'])) {
                    ### notify tech@groomit.me ###
                    $msg = $ret['error_msg'] . ' [ ' . $ret['error_code'] . ']';
                    Helper::send_mail('tech@groomit.me', '[GROOMIT][' . getenv('APP_ENV') . '] Auth-Complete Failed : ' . $app->appointment_id, $msg);
                    Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Auth-Complete Failed : ' . $app->appointment_id, $msg);
                    ### auth complete has failed so we need sales ###
                    throw new \Exception($msg, -4);
                }
            }

            ### Second : find total paid(Sales) transaction.
            $total_paid_trans = 0;
            $total_paid_trans = CCTrans::where('appointment_id', $app->appointment_id)
                ->whereIn('type', ['S','V'])
                ->where('category', 'S')
                ->where('result', 0)
                ->whereNull('void_date')
                ->sum( DB::raw("case when type = 'S' then amt else -amt end") );
                //->sum('amt');

            //Charge the difference amount only.
            if ($total_paid_trans < $app->total ) {
                $ret = Converge::sales($payment->card_token, ($app->total - $total_paid_trans), $app->appointment_id, 'S');

                if (!empty($ret)) {
                    if (!empty($ret['error_msg'])) {
                        $app->status = 'F';
                        $app->mdate = Carbon::now();
                        $u = Auth::guard('admin')->user();
                        $app->modified_by = isset($u) ? $u->name . '(' . $u->admin_id . ')' : null;
                        $app->save();

                        Helper::send_mail('tech@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - appointment : ' . $app->appointment_id . '<br> - error : ' . $ret['error_msg']);
                        Helper::send_mail('help@groomit.me', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing failed', ' - appointment : ' . $app->appointment_id . '<br> - error : ' . $ret['error_msg']);
                        Helper::send_mail('it@jjonbp.com', '[Groomit][' . getenv('APP_ENV') . '] Credit card processing of Sales of difference from Auth failed', ' - appointment : ' . $app->appointment_id . '<br> - error : ' . $ret['error_msg']);

                        $proc->send_failure_email($app, $user);

                        $msg = 'Credit card processing failed : ' . $ret['error_msg'] . ' [' . $ret['error_code'] . ']';
                        throw new \Exception($msg, -5);

                    }

                    $void_ref = $ret['void_ref'];
                }
            }else if($total_paid_trans > $app->total){ //refund the difference.

                $first_paid_trans = CCTrans::where('appointment_id', $app->appointment_id)
                    ->where('type', 'S')
                    ->where('category', 'S')
                    ->where('result', 0)
                    ->whereNull('void_date')
                    ->orderBy('amt', 'desc')
                    ->first();
                //Partial refunds.
                $ret = Converge::void($app->appointment_id,'S', $first_paid_trans->void_ref,$first_paid_trans->type, $total_paid_trans - $app->total );
                if (!empty($ret['error_msg'])) {
                    Helper::send_mail('it@jjonbp.com', '[GROOMIT][' . getenv('APP_ENV') . '] Failed to refund CC trans when charge_appointment appointment : ' . $app->appointment_id, $ret['error_code'] . ' [' . $ret['error_msg'] . ']');
                    /*return response()->json([
                           'msg' => 'Failed to void auth only credit card transaction'
                       ]);*/
                }
            }
        //}

//        $app->status = 'P'; //Do not modify status. just keep the same as of now.
        $app->mdate = Carbon::now();

        $u = Auth::guard('admin')->user();
        $app->modified_by = isset($u) ? $u->name . '(' . $u->admin_id . ')' : null;
        $app->save();

        return back();
    }

    public function adjust(Request $request, $appointment_id) {

        $app = AppointmentList::find($appointment_id);
        if (empty($app) || !in_array($app->status , ['P','C','L'])) {
            return back();
        }

        $admin = Auth::guard('admin')->user();

        $adjust = new Adjust();
        $adjust->appointment_id = $appointment_id;
        $adjust->type = $request->type;
        $adjust->pdate = $request->pdate;
        $adjust->amt = $request->amt;
        $adjust->comments = $request->comments;
        $adjust->cdate = Carbon::now();
        $adjust->save();

        //## create_adjust($app, $type, $pdate, $amt, $comments, $created_by)
        ProfitShare::create_adjust(
            $app
          , $adjust->type
          , $adjust->pdate
          , $adjust->amt
          , $adjust->comments
          , $admin->admin_id
        );

        return back();
    }

    public function update_groomer_note(Request $request) {
        $app = AppointmentList::find($request->id);

        if (empty($app)) {
            return back();
        }

        $pet = AppointmentPet::where('appointment_id', $request->id)->where('pet_id', $request->pet_id)->first();
        if (!empty($pet)) {

            DB::update("
                update appointment_pet
                   set groomer_note = :groomer_note
                 where appointment_id = :appointment_id
                   and pet_id = :pet_id
            ", [
                'groomer_note' => $request->groomer_note,
                'appointment_id'    => $request->id,
                'pet_id'    => $request->pet_id
            ]);
        }

        return response()->json([
            'msg'   => ''
        ]);
    }
}
