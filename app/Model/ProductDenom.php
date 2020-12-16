<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProductDenom extends Model
{
    protected $table = 'product_denom';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'denom_id';
}
