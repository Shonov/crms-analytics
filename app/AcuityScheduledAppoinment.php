<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AcuityScheduledAppoinment extends Model
{
    protected $table = 'acuity_scheduled_appointments';
    public $timestamps = false;

    protected $fillable = [
        'month',
        'count',
    ];
}
