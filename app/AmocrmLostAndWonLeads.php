<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AmocrmLostAndWonLeads extends Model
{
    public $timestamps = false;

    protected $table = 'amocrm_lost_and_won_leads';

    protected $fillable = [
        'won',
        'lost',
        'month',
    ];
}
