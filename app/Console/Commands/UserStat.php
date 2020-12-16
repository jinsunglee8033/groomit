<?php

namespace App\Console\Commands;

use App\Lib\Helper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserStat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:stat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create UserStat table.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Helper::send_mail('it@jjonbp.com', '[GROOMIT]User Statistics/Order CNT started.', 'user_stat table');

        //Live
        DB::delete("DELETE from user_stat");

        $ret = DB::insert("
                    INSERT INTO user_stat( user_id, book_cnt, sum_total, last_appt_id, last_appt_date )
                    select a.user_id,  count(*), sum(total), max(a.appointment_id), max(a.accepted_date)
                    from appointment_list a
                    where status not in ('C','L')
                    and a.cdate < current_timestamp
                    group by 1 ",
            [ ]);
        $ret = DB::update( "
                   UPDATE user_stat a, appointment_list b, groomer c
                   SET a.last_groomer_id = b.groomer_id,
                       a.last_groomer_fname = c.first_name,
                       a.last_groomer_lname = c.last_name
                   WHERE  a.last_appt_id = b.appointment_id
                   AND  b.groomer_id = c.groomer_id ",
            [ ]);

        DB::delete("DELETE from appointment_cnt");
        $ret = DB::insert("
                    INSERT INTO appointment_cnt(appointment_id ,  cdate, book_cnt)
                    select a.appointment_id, current_timestamp, count(b.appointment_id) +1
                    from appointment_list a 
                    left join appointment_list b on a.user_id = b.user_id and a.appointment_id > b.appointment_id  and b.status not in ('C','L')
                    where a.status not in ( 'C', 'L')
                    group by 1 , 2 ",
            [ ]);
        $ret = DB::update( "
                  UPDATE appointment_cnt a inner join (
                           SELECT  a.appointment_id, count(b.appointment_id) cnt
                           FROM appointment_list a 
                                left join appointment_list b on a.user_id = b.user_id and a.appointment_id > b.appointment_id  and b.status = 'P'
                           WHERE a.status = 'P'
                           GROUP BY 1 
                         ) z on a.appointment_id = z.appointment_id
                  SET a.paid_cnt = z.cnt + 1 ",
            [ ]);

        DB::delete("DELETE from groomer_stat");

        $ret = DB::insert("
                    INSERT INTO groomer_stat( groomer_id, book_cnt, revenue_total, rating_qty, rating_avg)
                    SELECT groomer_id, count(*), sum(total), avg(rating), sum( case IfNull(rating,0) when 0 then 0 else  1 end  )
                    FROM appointment_list
                    WHERE status = 'P'
                    GROUP BY 1",
           [ ]);

        //Demo
        DB::delete("DELETE from groomit_demo.user_stat");

        $ret = DB::insert("
                    INSERT INTO groomit_demo.user_stat( user_id, book_cnt,sum_total, last_appt_id, last_appt_date )
                    select a.user_id, count(*),sum(total), max(a.appointment_id), max(a.accepted_date)
                    from groomit_demo.appointment_list a
                    where status not in ('C','L')
                    and a.cdate < current_timestamp
                    group by 1 ",
                [ ]);
        $ret = DB::update( "
                   UPDATE groomit_demo.user_stat a, groomit_demo.appointment_list b, groomit_demo.groomer c
                   SET a.last_groomer_id = b.groomer_id,
                       a.last_groomer_fname = c.first_name,
                       a.last_groomer_lname = c.last_name
                   WHERE  a.last_appt_id = b.appointment_id
                   AND  b.groomer_id = c.groomer_id",
                [ ]);

        DB::delete("DELETE from groomit_demo.groomer_stat");

        $ret = DB::insert("
                    INSERT INTO groomit_demo.groomer_stat( groomer_id, book_cnt, revenue_total, rating_qty, rating_avg)
                    SELECT groomer_id, count(*), sum(total), avg(rating), sum( case IfNull(rating,0) when 0 then 0 else  1 end  )
                    FROM groomit_demo.appointment_list
                    WHERE status = 'P'
                    GROUP BY 1",
            [ ]);


        DB::delete("DELETE from groomit_demo.appointment_cnt");
        $ret = DB::insert("
                    INSERT INTO groomit_demo.appointment_cnt(appointment_id ,  cdate, book_cnt)
                    select a.appointment_id, current_timestamp, count(b.appointment_id) +1
                    from groomit_demo.appointment_list a 
                    left join groomit_demo.appointment_list b on a.user_id = b.user_id and a.appointment_id > b.appointment_id  and b.status not in ('C','L')
                    where a.status not in ( 'C', 'L')
                    group by 1 , 2 ",
            [ ]);

        $ret = DB::update( "
                  UPDATE groomit_demo.appointment_cnt a inner join (
                           SELECT  a.appointment_id, count(b.appointment_id) cnt
                           FROM groomit_demo.appointment_list a 
                                left join groomit_demo.appointment_list b on a.user_id = b.user_id and a.appointment_id > b.appointment_id  and b.status = 'P'
                           WHERE a.status = 'P'
                           GROUP BY 1 
                         ) z on a.appointment_id = z.appointment_id
                  SET a.paid_cnt = z.cnt + 1 ",
            [ ]);

        Helper::send_mail('it@jjonbp.com', '[GROOMIT]User Statistics data/Order CNT ended', 'user_stat table');


        //Remove OLD data for performance.
        DB::delete("DELETE from groomer_opens WHERE cdate < curdate() - interval 7 day");


        // Photo update from After photo to Pet photo
        $p_list = DB::select("
                            select ap.image as image, ap.pet_id as pet_id from appointment_list al 
                            join appointment_photo ap on ap.appointment_id = al.appointment_id and ap.type = 'A' 
                            where al.accepted_date >= curdate() - interval 5 day
                            and al.accepted_date < curdate()
                            and al.status ='P' 
                            and ap.pet_id not in ( select pet_id from pet_photo )
                        ");

        if (!empty($p_list)) {
            foreach ($p_list as $p) {
                DB::insert("
                    insert into pet_photo ( pet_id, photo, cdate ) value ( :pet_id, :image, current_timestamp ) ",
                    [
                    'pet_id'    => $p->pet_id,
                    'image'     => $p->image
                    ]);
            }
        }


        DB::delete("DELETE from request_log WHERE cdate < curdate() - interval 14 day");
        DB::delete("DELETE from groomit_demo.request_log WHERE cdate < curdate() - interval 14 day");


        DB::delete("DELETE from service_area
                           where area_name not in (
                               select distinct concat(county_name, '.', state_abbr)
                               from allowed_zip
                               where available = 'x')"
        );

        $ret = DB::insert("
                    INSERT into service_area(area_name, sort )
                    select distinct concat(county_name, '.', state_abbr), 5000
                    from allowed_zip
                    where available = 'x'
                    and concat(county_name, '.', state_abbr) not in ( select area_name from groomit_demo.service_area) ",
            [ ]);
        DB::delete("DELETE from groomit_demo.service_area
                           where area_name not in (
                               select distinct concat(county_name, '.', state_abbr)
                               from groomit_demo.allowed_zip
                               where available = 'x')"
        );

        $ret = DB::insert("
                    INSERT into groomit_demo.service_area(area_name, sort )
                    select distinct concat(county_name, '.', state_abbr), 5000
                    from groomit_demo.allowed_zip
                    where available = 'x'
                    and concat(county_name, '.', state_abbr) not in ( select area_name from groomit_demo.service_area) ",
            [ ]);



//        //Groomer points by Accepted by Favorite Groomer : + 5 points
//        $ret = DB::insert("
//                    INSERT INTO groomer_point(groomer_id,  point, reason_code, appointment_id, modified_by, cdate )
//                     select a.groomer_id, 5, 10, a.appointment_id, 'SYSTEM', a.accepted_date
//                    from appointment_list a
//                    where a.status in ('P')
//                    and a.groomer_id = my_favorite_groomer
//                    and a.fav_type ='F'
//                    and a.accepted_date >=  curdate() - interval 1 day
//                    and a.accepted_date <  curdate()
//                     ",
//            [ ]);
//
//        //Groomer points by Arrived on Time +/- 5 points
//        $ret = DB::insert("
//                    INSERT INTO groomer_point(groomer_id, point, reason_code, appointment_id, modified_by, cdate )
//                    select a.groomer_id, CASE WHEN a.accepted_date > b.cdate THEN 5 ELSE -5 END, 20, a.appointment_id, 'SYSTEM', a.accepted_date
//                    from appointment_list a left join groomer_arrived b
//                         on a.appointment_id = b.appointment_id and a.groomer_id = b.groomer_id and b.result='Y'
//                    where a.status in ('P')
//                    and a.accepted_date >=  curdate() - interval 1 day
//                    and a.accepted_date <  curdate()
//                     ",
//            [ ]);
//      //  -5 point if no 'Arrived' transaction exist => ignore them for time being.
//
//        //5 points when over 20 appts for 1 week.
//        $today = new Carbon('today', 'America/New_York');
//        $first_monday = $today->startOfWeek();
//        if( $first_monday->format('Y-m-d') == $today->format('Y-m-d')) {
//            $ret = DB::insert("
//                    INSERT INTO groomer_point(groomer_id, point, reason_code, modified_by, cdate )
//                    select a.groomer_id,  20, 30, 'SYSTEM', current_timestamp , count(*)
//                    from appointment_list a
//                    where a.status in ('P')
//                    and a.accepted_date >=  curdate() - interval 7 day
//                    and a.accepted_date <  curdate()
//                    group by 1,2,3,4,5,6
//                    having count(*) >= 20
//                     ",
//                [ ]);
//        }
    }
}
