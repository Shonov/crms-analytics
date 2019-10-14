<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AmoCrmLeadStatistic extends Model
{
    public $timestamps = false;

    protected $table = 'amocrm_leads';

    protected $fillable = [
        'type',
        'count',
        'date',
    ];
}
