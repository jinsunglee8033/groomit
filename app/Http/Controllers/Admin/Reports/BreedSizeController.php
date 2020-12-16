<?php
/**
 * Created by PhpStorm.
 * User: royce
 * Date: 6/7/19
 * Time: 4:45 AM
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

class BreedSizeController extends Controller
{

    public function show(Request $request) {

        $sdate = Carbon::today()->subDays(6);
        $edate = Carbon::today();

        if (!empty($request->sdate)) {
            $sdate = Carbon::createFromFormat('Y-m-d H:i:s', $request->sdate . ' 00:00:00');
        }

        if (!empty($request->edate)) {
            $edate = Carbon::createFromFormat('Y-m-d H:i:s', $request->edate . ' 23:59:59');
        }

        $groomers = //Groomer::where('status', 'A')->orderBy('first_name', 'asc','last_name','asc')->get();
            Groomer::orderBy('first_name', 'asc','last_name','asc')->get();

        $query_breed = '';
        if (!empty($request->breed)) {
            $query_breed = " and lower(e.breed_name) like '%" . strtolower($request->breed) . "%' ";
        }

        $query_size = '';
        if (!empty($request->size)) {
            $query_size = " and c.size = " . $request->size;
        }

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
                a.sub_total,
                a.check_in,
                a.check_out,
                TIMESTAMPDIFF(MINUTE, a.check_in, a.check_out) as diff
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
                left join vw_groomer_assign_log gal on a.appointment_id = gal.appointment_id and a.groomer_id = gal.groomer_id 
            where a.status = 'P'
            and a.accepted_date >= :sdate
            and a.accepted_date <= :edate
            $query_breed
            $query_size
            
        ";

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
                          'App #' => $a->appointment_id,
                          'Service.Date' => $a->accepted_date,
                          'Groomer.ID' => $a->groomer_id,
                          'Groomer.Name' => $a->groomer_name,
                          'Breed.Name' => $a->breed_name,
                          'Size.Name' => $a->size_name,
                          'Amount' => $a->sub_total,
                          'Check-In.Date' => $a->check_in,
                          'Check-Out.Date' => $a->check_out,
                          'Grooming Time(MIN)' => $a->diff
                        ];

                        $new_data[] = $row;

                    }

                    $sheet->fromArray($new_data);

                });

            })->export('xlsx');
        }

        $total = new \stdClass();
        $total->apps = 0;
        $total->amount = 0;
        $total->pet_qty = 0;
        $total->times = 0;

        foreach ($data as $d) {
            $d->pet_qty = AppointmentPet::where('appointment_id', $d->appointment_id)->count();
            $total->apps += 1;
            $total->amount += $d->sub_total;
            $total->pet_qty += $d->pet_qty;
            $total->times += $d->diff;
        }

        $data = Helper::arrayPaginator($data, $request);

        return view('admin.reports.breed_size', [
          'groomers' => $groomers,
          'data' => $data,
          'sdate' => $sdate,
          'edate' => $edate,
          'groomer_id' => $request->groomer_id,
            'breed' => $request->breed,
            'size'  => $request->size,
            'total' => $total
        ]);
    }

}