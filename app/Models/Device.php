<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'apartment_id',
        'mac_address',
        'relay_pin',
        'sensor_pin',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
