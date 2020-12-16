<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 4/22/19
 * Time: 9:31 AM
 */

namespace App\Http\Controllers\Admin\Reports;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Model\AppointmentPet;
use App\Model\Groomer;
use App\Model\ProfitShare;
use App\Model\VWGroomerEvaluation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

//use DB;

class BreedBookedController extends Controller
{

    public function show(Request $request) {

        $sdate = Carbon::today()->addDays(-30);
        $edate = Carbon::today()->addDays(1)->addSeconds(-1);

        if (!empty($request->sdate)) {
            $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
        }

        if (!empty($request->edate)) {
            $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
        }

        $package_query = "";

        $data = DB::select("select a.breed_name, count(*) cnt,
                                        sum(case b.app_qty when 0 then 1 else 0 end) as a, 
                                        sum(case b.app_qty when 1 then 1 else 0 end) as b,
                                        sum(case b.app_qty when 2 then 1 else 0 end) as c,
                                        sum(case  when b.app_qty >= 3 then 1 else 0 end) as d     
                                    FROM breed a inner join (
                                    select p.pet_id, p.breed, count(*) app_qty
                                    from pet p join appointment_pet app_pet on p.pet_id = app_pet.pet_id
                                               join appointment_list app on app_pet.appointment_id = app.appointment_id and app.status ='P'
                                    where p.cdate >= :sdate
                                    and p.cdate <= :edate 
                                    and p.type='dog'          
                                    group by 1,2
                                    having app_qty = 1
                                    UNION
                                    select p.pet_id, p.breed, count(*) 
                                    from pet p join appointment_pet app_pet on p.pet_id = app_pet.pet_id
                                               join appointment_list app on app_pet.appointment_id = app.appointment_id and app.status ='P'
                                    where p.cdate >= :sdate
                                    and p.cdate <= :edate 
                                    and p.type='dog'         
                                    group by 1,2
                                    having count(*) = 2
                                    UNION
                                    select p.pet_id, p.breed, count(*) 
                                    from pet p join appointment_pet app_pet on p.pet_id = app_pet.pet_id
                                               join appointment_list app on app_pet.appointment_id = app.appointment_id and app.status ='P'
                                    where p.cdate >= :sdate
                                    and p.cdate <= :edate 
                                    and p.type='dog'        
                                    group by 1,2
                                    having count(*) >= 3
                                    UNION
                                    select p.pet_id, p.breed, 0
                                    from pet p
                                    where p.cdate >= :sdate
                                    and p.cdate <= :edate  
                                    and p.type='dog'       
                                    and not exists ( select app.appointment_id 
                                                     from appointment_pet app_pet 
                                                     inner join appointment_list app on app_pet.appointment_id = app.appointment_id and app.status ='P'     
                                                     where app_pet.pet_id =  p.pet_id      
                                                   ) 
                                    ) b on a.breed_id = b.breed
                                    group by 1
                                    UNION ALL
                                    select 'CAT', count(*) cnt,
                                        sum(case b.app_qty when 0 then 1 else 0 end) , 
                                        sum(case b.app_qty when 1 then 1 else 0 end) ,
                                        sum(case b.app_qty when 2 then 1 else 0 end) ,
                                        sum(case  when b.app_qty >= 3 then 1 else 0 end)     
                                    FROM  (
                                    select p.pet_id, count(*) app_qty
                                    from pet p join appointment_pet app_pet on p.pet_id = app_pet.pet_id
                                               join appointment_list app on app_pet.appointment_id = app.appointment_id and app.status ='P'
                                    where p.cdate >= :sdate
                                    and p.cdate <= :edate 
                                    and p.type='cat'          
                                    group by 1
                                    having app_qty = 1
                                    UNION
                                    select p.pet_id, count(*) 
                                    from pet p join appointment_pet app_pet on p.pet_id = app_pet.pet_id
                                               join appointment_list app on app_pet.appointment_id = app.appointment_id and app.status ='P'
                                    where p.cdate >= :sdate
                                    and p.cdate <= :edate 
                                    and p.type='cat'         
                                    group by 1
                                    having count(*) = 2
                                    UNION
                                    select p.pet_id, count(*) 
                                    from pet p join appointment_pet app_pet on p.pet_id = app_pet.pet_id
                                               join appointment_list app on app_pet.appointment_id = app.appointment_id and app.status ='P'
                                    where p.cdate >= :sdate
                                    and p.cdate <= :edate 
                                    and p.type='cat'        
                                    group by 1
                                    having count(*) >= 3
                                    UNION
                                    select p.pet_id, 0
                                    from pet p
                                    where p.cdate >= :sdate
                                    and p.cdate <= :edate  
                                    and p.type='cat'       
                                    and not exists ( select app.appointment_id 
                                                     from appointment_pet app_pet 
                                                     inner join appointment_list app on app_pet.appointment_id = app.appointment_id and app.status ='P'     
                                                     where app_pet.pet_id =  p.pet_id      
                                                   ) 
                                    ) b
                                    group by 1
                                    order by 2 desc
                ". $package_query. "
        ", [
          'sdate' => $sdate,
          'edate' => $edate
        ]);

        if ($request->excel == 'Y') {
            Helper::log('########### EXCEL #############');

            Excel::create('breed-booked', function($excel) use($data) {

                $excel->sheet('reports', function($sheet) use($data) {

                    $reports = [];
                    foreach ($data as $o) {
                        $reports[] = [
                          'Breed' => $o->breed_name,
                          'No Orders' => $o->a,
                          'One.Order' => $o->b,
                          'Two.Orders' => $o->c,
                          'Multiple.Orders' => $o->d,
                          'Total ' => $o->cnt
                        ];
                    }
                    $sheet->fromArray($reports);
                });
            })->export('xlsx');
        }

        $total = new \stdClass();
        $total->a_total = 0;
        $total->b_total = 0;
        $total->c_total = 0;
        $total->d_total = 0;

        foreach ($data as $o) {
            $total->a_total += $o->a;
            $total->b_total += $o->b;
            $total->c_total += $o->c;
            $total->d_total += $o->d;
        }

//        $data = Helper::arrayPaginator($data, $request);

        return view('admin.reports.breed_booked', [
            'sdate' => $sdate,
            'edate' => $edate,
            'data'  => $data,
            'total' => $total
        ]);

    }

}