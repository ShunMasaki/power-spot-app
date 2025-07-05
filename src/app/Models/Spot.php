<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Spot extends Model
{
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function spotBenefits()
    {
        return $this->hasMany(SpotBenefit::class);
    }

    public function goshuinImages()
    {
        return $this->hasMany(GoshuinImage::class);
    }

    public function omikujiImages()
    {
        return $this->hasMany(OmikujiImage::class);
    }
}
