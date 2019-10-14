<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AcuityAppointment extends Model
{
    public $timestamps = false;

    public static $HOME_SESSION_LOCATION = 'home_sessions';
    public static $TRIAL_SESSION_TYPE = 'trial';
    public static $MEMBERSHIP_SESSION_TYPE = 'membership';

    protected $fillable = [
        'type',
        'location',
        'completed_count',
        'no_show_count',
        'cancelled_count',
        'date',
    ];
}
