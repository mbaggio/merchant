<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
*/

use Illuminate\Support\Facades\Route;

# Sitemap Categories CREATE . READ . UPDATE . DELETE
### CREATE
$router->post('/sitemap_categories/{name}[/{parent_id}]', ['uses' => 'SitemapCategoriesController@create']);
### READ
$router->get('/sitemap_categories', function () use ($router) {
    return response()->json(['collection' => \App\Models\SitemapCategory::where('id', '>', 1)->get()]);
});
### UPDATE
$router->patch('/sitemap_categories/{id}/{new_name}', ['uses' => 'SitemapCategoriesController@update']);
### DELETE
$router->delete('/sitemap_categories/{id}', ['uses' => 'SitemapCategoriesController@delete']);


# Sitemap Categories get merchants
### READ
$router->get('/sitemap_categories/{sitemap_category_id}/merchants[/{page_number}]', ['uses' => 'SitemapCategoriesController@getMerchants']);


# Merchants CREATE . READ . UPDATE . DELETE
### CREATE
$router->post('/merchants/{name}/{url}/{description}/{sitemap_category_id}', ['uses' => 'MerchantsController@create']);
### READ
$router->get('/merchants[/{name}/{page_number}]', ['uses' => 'MerchantsController@getMerchants']);
$router->get('/merchants/{name}', ['uses' => 'MerchantsController@getMerchants']);
### UPDATE
$router->patch('/merchants/{id}/{new_name}/{new_url}/{new_description}/{new_sitemap_category_id}', ['uses' => 'MerchantsController@update']);
### DELETE
$router->delete('/merchants/{id}', ['uses' => 'MerchantsController@delete']);


# Ad Campaigns CREATE . READ . UPDATE . DELETE
### CREATE
$router->post('/adcampaigns/{name}/{cash_back_rate}/{date_from}/{date_to}', ['uses' => 'AdcampaignsController@create']);
### READ
$router->get('/adcampaigns[/{name}/{page_number}]', ['uses' => 'AdcampaignsController@getAdcampaigns']);
$router->get('/adcampaigns/{name}', ['uses' => 'AdcampaignsController@getAdcampaigns']);
$router->get('/adcampaigns/{adcampaign_id}/merchants[/{page_number}]', ['uses' => 'AdcampaignsController@getAdcampaignMerchants']);
### UPDATE
$router->patch('/adcampaigns/{id}/{new_name}/{new_cash_back_rate}/{new_date_from}/{new_date_to}', ['uses' => 'AdcampaignsController@update']);
### DELETE
$router->delete('/adcampaigns/{id}', ['uses' => 'AdcampaignsController@delete']);

# Ad Campaigns Merchants CREATE . READ . DELETE
# READ
$router->get('/adcampaigns/{adcampaign_id}/merchants[/{page_number}]', ['uses' => 'AdcampaignsController@getAdcampaignMerchants']);
# CREATE
$router->post('/adcampaigns/{adcampaign_id}/merchants/{merchant_id}', ['uses' => 'AdcampaignsController@createAdcampaignMerchants']);
### DELETE
$router->delete('/adcampaigns/{adcampaign_id}/merchants/{merchant_id}', ['uses' => 'AdcampaignsController@deleteAdcampaignMerchants']);
