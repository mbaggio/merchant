<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdCampaign extends Model 
{   
    /**
     * The attributes that are autoincremental
     *
     * @var array
     */
    protected $guarded = [
        'id'
    ];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'cash_back_rate', 'date_from', 'date_to'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'deleted'
    ];
}
