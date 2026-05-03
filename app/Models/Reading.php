<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    protected $fillable = [
        'apartment_id',
        'volume',
        'reading_type',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'reading_type' => \App\Enums\ReadingTypeEnum::class,
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
