<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Get(
 *     path="/sitemap_categories",
 *     description="Sitemap Categories list",
 *     tags={"Sitemap categories"},
 *     @OA\Response(response="200", description="Sitemap cateogories list")
 * )
 */

class SitemapCategory extends Model 
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
        'name', 'parent_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];
}
