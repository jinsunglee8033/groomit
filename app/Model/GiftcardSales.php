<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GiftcardSales extends Model
{
    protected $table = 'giftcard_sales';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';
}
