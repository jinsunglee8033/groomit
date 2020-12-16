<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 9/6/18
 * Time: 10:53 AM
 */

namespace App\Http\Controllers\Admin\Reports;


use App\Http\Controllers\Controller;
use App\Lib\Helper;
use App\Model\AppointmentList;
use App\Model\AppointmentPet;
use App\Model\Groomer;
use Carbon\Carbon;
use Carbon\Laravel\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CheckInOutTrendController extends Controller
{

    public function show(Request $request) {

        $sdate = Carbon::today()->subDays(1);
        $edate = Carbon::today();

        if (!empty($request->sdate)) {
            $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
        }

        if (!empty($request->edate)) {
            $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
        }

        $pet_type = '';
        $sql_pet_type ='' ;
        if (!empty($request->pet_type)) {
            $pet_type = $request->pet_type;
            $sql_pet_type = " and c.type = '$pet_type' ";
        }

        $groomers = Groomer::orderBy('first_name', 'asc','last_name','asc')->get();

        $query = "            
            select
                a.appointment_id,
                a.groomer_id,
                concat(f.first_name, ' ', f.last_name) as groomer_name,
                a.cdate,
                gal.groomer_assign_date,
                TIMESTAMPDIFF(MINUTE, a.cdate, gal.groomer_assign_date) as assign_diff,
                a.accepted_date,
                d.size_name,
                e.breed_name, 
                a.check_in,
                g.image as check_in_photo,
                a.check_out,
                h.image as check_out_photo,
                TIMESTAMPDIFF(MINUTE, a.check_in, a.check_out) as diff,
                TIMESTAMPDIFF(MINUTE, a.check_in, a.accepted_date) as service_time_diff,
                ps.groomer_profit_amt,
                ps.app_package_type as package,
                IfNull(ga.distance,0) * 5280.0 as distance,
                ga.cdate as ga_cdate,
                IfNull(ga.distance_comp_app,0) * 5280.0 as distance_comp_app,
                IfNull(ga.distance_comp_google,0) * 5280.0 as distance_comp_google
            from appointment_list a 
                inner join appointment_pet b on a.appointment_id = b.appointment_id
                inner join pet c on b.pet_id = c.pet_id
                inner join groomer f on a.groomer_id = f.groomer_id
                left join size d on c.size = d.size_id 
                left join breed e on c.breed = e.breed_id
                left join appointment_photo g on b.appointment_id = g.appointment_id
                    and b.pet_id = g.pet_id
                    and g.type = 'B'
                left join appointment_photo h on b.appointment_id = h.appointment_id
                    and b.pet_id = h.pet_id
                    and h.type = 'A' 
                inner join profit_share ps on a.appointment_id = ps.appointment_id and ps.type = 'A' and ps.id not in (select original_id from profit_share where type ='V')
                left join vw_groomer_assign_log gal on a.appointment_id = gal.appointment_id and a.groomer_id = gal.groomer_id 
                left join groomer_arrived ga on a.appointment_id = ga.appointment_id
            where a.status = 'P'
            and a.accepted_date >= :sdate
            and a.accepted_date <= :edate
        " . $sql_pet_type ;

        $params = compact('sdate', 'edate');
        if (!empty($request->groomer_id)) {
            $query .= " and a.groomer_id = :groomer_id ";
            $groomer_id = $request->groomer_id;
            $params = array_merge($params, compact('groomer_id'));
        }

        $query .= "order by a.accepted_date desc ";

        $data = DB::select($query, $params);
        if ($request->excel == 'Y') {
            Excel::create('appointments', function($excel) use($data) {

                $excel->sheet('reports', function($sheet) use($data) {

                    $new_data = [];
                    foreach ($data as $a) {

                        $row = [
                            'Appointment.ID' => $a->appointment_id,
                            'Groomer.ID' => $a->groomer_id,
                            'Groomer.Name' => $a->groomer_name,
                            'C.Date' => $a->cdate,
                            'Groomer.Assigned' => $a->groomer_assign_date,
                            'Groomer.Assigned.Diff' => $a->assign_diff,
                            'Service.Date' => $a->accepted_date,
                            'Size.Name' => $a->size_name,
                            'Package.Name' => $a->package,
                            'Breed.Name' => $a->breed_name,
                            'Check-In.Date' => $a->check_in,
                            'Check-Out.Date' => $a->check_out,
                            'Grooming Time(MIN)' => $a->diff,
                            'Delay Time(MIN)' => $a->service_time_diff,
                            'Groomer Earning ($)' => $a->service_time_diff
                        ];

                        $new_data[] = $row;

                    }

                    $sheet->fromArray($new_data);

                });

            })->export('xlsx');
        }

        foreach ($data as $d) {
            $d->pet_qty = AppointmentPet::where('appointment_id', $d->appointment_id)->count();
        }

        $data = Helper::arrayPaginator($data, $request);

        return view('admin.reports.check-in-out-trend', [
            'groomers' => $groomers,
            'data' => $data,
            'sdate' => $sdate,
            'edate' => $edate,
            'groomer_id' => $request->groomer_id,
            'pet_type' => $pet_type
        ]);
    }

}