<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AmoCrmTaskStatistic extends Model
{
    public $timestamps = false;

    protected $table = 'amocrm_tasks';

    protected $fillable = [
        'operator_id',
        'new',
        'closed',
        'not_closed',
        'date',
    ];

    protected $hidden = array('id');

    public function operator()
    {
        return $this->belongsTo(AmoCrmOperator::class);
    }
}
