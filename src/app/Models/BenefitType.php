<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BenefitType extends Model
{
    protected $fillable = ['key', 'label', 'icon', 'sort_order'];

    public function spotBenefits()
    {
        return $this->hasMany(SpotBenefit::class);
    }
}
