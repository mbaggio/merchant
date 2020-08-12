<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Merchant extends Model 
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
        'name', 'url', 'description', 'sitemap_category_id'
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
