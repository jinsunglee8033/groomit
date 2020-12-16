<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 8/28/18
 * Time: 10:16 AM
 */

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Model\AppointmentList;
use App\Model\Groomer;
use App\Model\UserFavoriteGroomer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

class GroomerFavController extends Controller
{
    public function show(Request $request, $id=null) {

        $query = "
           select f.user_id, f.groomer_id, f.cdate, u.first_name, u.last_name, g.first_name as g_name,
                    us.last_appt_date as last_date, us.last_groomer_fname as last_groomer_f, us.last_groomer_lname as last_groomer_l
            from user_favorite_groomer f
                left join user u on u.user_id = f.user_id
                left join groomer g on g.groomer_id = f.groomer_id
                left join user_stat us on us.user_id = u.user_id
            where 1=1
        ";

        if($id != null){
            $query .= " and f.groomer_id = " . $id . " ";
        } else {
            if (!empty($request->groomer_id)) {
                $query .= " and f.groomer_id = " . $request->groomer_id . " ";
            }
        }

        $data = DB::select($query, []);

        if ($request->excel == 'Y') {
            Excel::create('Favorite Users ', function ($excel) use ($data) {
                $excel->sheet('reports', function ($sheet) use ($data) {
                    $new_data = [];
                    foreach ($data as $a) {
                        $row = [
                            'Groomer.ID' => $a->groomer_id,
                            'Groomer.Name' => $a->g_name,
                            'User.ID' => $a->user_id,
                            'User.Name' => $a->first_name . ' ' . $a->last_name,
                            'Cdate' => $a->cdate,
                            'Last.Groomed.Date' => $a->last_date,
                            'Last.Groomer.Name' => $a->last_groomer_f . ' ' . $a->last_groomer_l
                        ];
                        $new_data[] = $row;
                    }
                    $sheet->fromArray($new_data);
                });
            })->export('xlsx');
        }

        $data = $this->arrayPaginator($data, $request);
        $groomers = Groomer::orderBy('first_name', 'asc','last_name','asc')->get();

        return view('admin.reports.groomer-fav', [
            'groomer_id' => $request->groomer_id,
            'groomers' => $groomers,
            'link_g_id' => !is_null($id) ? $id : '',
            'data' => $data
        ]);
    }

    public function delete(Request $request) {
        try {

            $ret = UserFavoriteGroomer::where('groomer_id', $request->groomer_id)
                                        ->where('user_id', $request->user_id)
                                        ->delete();

            if ($ret < 0) {
                return response()->json([
                    'msg' => 'Failed to delete'
                ]);
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

    public function delete_all(Request $request) {
        try{

            $ret = UserFavoriteGroomer::where('groomer_id', $request->groomer_id)
                ->delete();

            if ($ret < 0) {
                return response()->json([
                    'msg' => 'Failed to delete'
                ]);
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

    public function arrayPaginator($array, $request) {

        $page = Input::get('page', 1);
        $perPage = 10;
        $offset = ($page * $perPage) - $perPage;

        return new LengthAwarePaginator(array_slice($array, $offset, $perPage, true), count($array), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]);
    }
}