<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    protected $fillable = [
        'user_id',
        'number',
        'block',
        'valve_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function device()
    {
        return $this->hasOne(Device::class);
    }

    public function readings()
    {
        return $this->hasMany(Reading::class);
    }

    public function valveLogs()
    {
        return $this->hasMany(ValveLog::class);
    }
}
