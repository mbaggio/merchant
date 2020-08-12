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
 *     path="/sitemap_categories/{name}/{parent_id}",
 *     description="New Sitemap Category",
 *     tags={"Sitemap categories"},
 *     @OA\Parameter(
 *        name="name",
 *        in="path",
 *        description="Category name",
 *        required=true,
 *        example="NY News papers"
 *     ),
 *     @OA\Parameter(
 *        name="parent_id",
 *        in="path",
 *        description="Category parent id",
 *        required=false,
 *        allowEmptyValue=true,
 *        example=12
 *     ),
 *     @OA\Response(response="201", description="New Sitemap cateogory addedd"),
 *     @OA\Response(response="412", description="Already exists or an error")
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
