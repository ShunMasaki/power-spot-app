<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OmikujiImage extends Model
{
    protected $fillable = [
        'user_id',
        'spot_id',
        'image_path',
        'original_filename',
        'taken_at',
    ];

    protected $casts = [
        'taken_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }
}
