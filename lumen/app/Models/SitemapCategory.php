<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

/**
 * @OA\Get(
 *     path="/sitemap_categories",
 *     description="Sitemap Categories list",
 *     tags={"Sitemap categories"},
 *     @OA\Response(response="200", description="Sitemap cateogories list")
 * )
 */

/**
 * @OA\Post(
 *     path="/sitemap_categories/{name}",
 *     description="New Sitemap Category",
 *     tags={"Sitemap categories"},
 *     @OA\Parameter(
 *        name="name",
 *        in="path",
 *        description="Category name",
 *        required=true,
 *        example="San Francisco News Papers"
 *     ),
 *     @OA\Response(response="200", description="New Sitemap cateogory")
 * )
 */

class SitemapCategory extends Model 
{
    use Searchable;
    
    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'sitemap_category_index';
    }
    
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
