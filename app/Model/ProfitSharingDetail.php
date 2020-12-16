<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProfitSharingDetail extends Model
{
    protected $table = 'profit_sharing_detail';

    public $timestamps = false;

    protected $dateFormat = 'U';

    protected $primaryKey = 'id';

    public function getPackageAttribute() {
        $product = Product::find($this->attributes['package_id']);
        if (!empty($product)) {
            return $product->prod_name;
        }

        return '-';
    }

    public $appends = ['package'];
}
