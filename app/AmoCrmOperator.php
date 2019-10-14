<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AmoCrmOperator extends Model
{
    public $timestamps = false;

    protected $table = 'operators';

    protected $fillable = [
        'new',
        'closed',
        'not_closed',
        'date',
    ];

    public function tasks()
    {
        return $this->hasMany(AmoCrmTaskStatistic::class, 'operator_id', 'id');
    }

    public function getTasks($startDay, $lastDay)
    {
        return $this->tasks()->where('date', '>=', $startDay)->where('date', '<=', $lastDay)->get();
    }
}
