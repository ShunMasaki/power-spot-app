<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpotBenefit extends Model
{
    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }

    public function benefitType()
    {
        return $this->belongsTo(BenefitType::class);
    }
}
