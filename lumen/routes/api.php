<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
*/

use Illuminate\Support\Facades\Route;

# Categories CREATE . READ . UPDATE . DELETE
# READ
$router->get('/sitemap_categories', function () use ($router) {
    return response()->json(\App\Models\SitemapCategory::all());
});
# CREATE
$router->post('/sitemap_categories/{name}[/{parent_id}]', ['uses' => 'SitemapCategoriesController@create']);