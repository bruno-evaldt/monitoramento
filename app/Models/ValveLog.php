<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValveLog extends Model
{
    protected $fillable = [
        'apartment_id',
        'user_id',
        'action',
    ];

    protected $casts = [
        'action' => \App\Enums\ValveActionEnum::class,
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
