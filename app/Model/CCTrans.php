<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CCTrans extends Model
{
    protected $table = 'cc_trans';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

    public static function get_category_name ($category) {
        switch ($category) {
            case 'S':
                return 'Sales';
            case 'A':
                return 'Holding';
            case 'T':
                return 'Tip';
            case 'W':
                return 'Cancel with Fee';
            case 'R':
                return 'Rescheduling Fee';
            default:
                return $category;
        }
    }

    public static function get_type_name ($type) {
        switch ($type) {
            case 'S':
                return 'Sales';
            case 'A':
                return 'Holding';
            case 'V':
                return 'Void';
            default:
                return $type;
        }
    }
}
