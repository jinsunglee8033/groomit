<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProfitSharingExceptionUser extends Model
{
    protected $table = 'profit_sharing_exception_user';

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

    public function getLastUpdatedAttribute() {
        if (empty($this->attributes['cdate'])) {
            return $this->attributes['mdate'] . ' (' . $this->attributes['modified_by'] . ')';
        }

        return $this->attributes['cdate'] . ' (' . $this->attributes['created_by'] . ')';
    }

    public $appends = ['package'];
}
