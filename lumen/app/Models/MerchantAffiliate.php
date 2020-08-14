<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantAffiliate extends Model 
{   
    protected $primaryKey = 'merchant_id';
    
    /**
     * The attributes that are autoincremental
     *
     * @var array
     */
    protected $guarded = [
    ];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'merchant_id', 'logo1_url', 'logo2_url', 'logo3_url', 'shipping_address_first_name'
        , 'shipping_address_last_name', 'shipping_address_street', 'shipping_address_postalcode', 'shipping_address_state', 'shipping_address_country_code'
        , 'billing_address_first_name', 'billing_address_last_name', 'billing_address_street', 'billing_address_postalcode', 'billing_address_state'
        , 'billing_address_country_code', 'cash_back_rate'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];
}
