<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $table = 'groomer_application';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

    public static function status_name($status) {
        switch($status) {
            case 'N':
                return 'New';
                break;
            case 'A':
                return 'Approved';
                break;
            case 'C':
                return 'Contacted';
                break;
            case 'T':
                return 'On Trial';
                break;
            case 'R':
                return 'Rejected';
                break;
            case 'M':
                return 'Maybe';
                break;
            default:
                return 'Rejected';
                break;

        }
    }
}
