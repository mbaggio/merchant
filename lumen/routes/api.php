<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
*/

use Illuminate\Support\Facades\Route;

# Categories CREATE . READ . UPDATE . DELETE
### CREATE
$router->post('/sitemap_categories/{name}[/{parent_id}]', ['uses' => 'SitemapCategoriesController@create']);
### READ
$router->get('/sitemap_categories', function () use ($router) {
    return response()->json(\App\Models\SitemapCategory::all());
});
### UPDATE
$router->patch('/sitemap_categories/{id}/{new_name}', ['uses' => 'SitemapCategoriesController@update']);
### DELETE
$router->delete('/sitemap_categories/{id}', ['uses' => 'SitemapCategoriesController@delete']);

# Merchants CREATE . READ . UPDATE . DELETE
### CREATE
$router->post('/merchants/{name}/{url}/{description}/{sitemap_category_id}', ['uses' => 'MerchantsController@create']);
### READ
$router->get('/merchants', function () use ($router) {
    return response()->json(\App\Models\Merchant::all());
});