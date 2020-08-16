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
$router->get('/sitemap_categories[/{id}]', ['uses' => 'SitemapCategoriesController@getSitemapCategories']);
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
$router->get('/merchants/{name}/{page_number}', ['uses' => 'MerchantsController@getMerchants']);
$router->get('/merchants//{page_number}', ['uses' => 'MerchantsController@getMerchants']);
$router->get('/merchants/{name}', ['uses' => 'MerchantsController@getMerchants']);
$router->get('/merchants//', ['uses' => 'MerchantsController@getMerchants']);
$router->get('/merchants-details/{id}', ['uses' => 'MerchantsController@getMerchantsInfo']);
### UPDATE
$router->patch('/merchants/{id}/{new_name}/{new_url}/{new_description}/{new_sitemap_category_id}', ['uses' => 'MerchantsController@update']);
### DELETE
$router->delete('/merchants/{id}', ['uses' => 'MerchantsController@delete']);

# Merchants Affiliate CREATE . UPDATE . DELETE
### CREATE
$txt_fields = implode('}/{', ['logo1_url', 'logo2_url', 'logo3_url', 'shipping_address_first_name', 'shipping_address_last_name', 'shipping_address_street', 'shipping_address_postalcode', 
            'shipping_address_state', 'shipping_address_country_code', 'billing_address_first_name', 'billing_address_last_name', 'billing_address_street', 
            'billing_address_postalcode', 'billing_address_state', 'billing_address_country_code']);
$router->post('/merchants-affiliate/{merchant_id}/{cash_back_rate}[/{'.$txt_fields.'}]', ['uses' => 'MerchantsAffiliatesController@createMerchantsAffiliation']);
### UPDATE
$router->patch('/merchants-affiliate/{merchant_id}/{cash_back_rate}[/{'.$txt_fields.'}]', ['uses' => 'MerchantsAffiliatesController@updateMerchantsAffiliation']);
# DELETE
$router->delete('/merchants-affiliate/{merchant_id}', ['uses' => 'MerchantsAffiliatesController@deleteMerchantsAffiliation']);

# Merchants Affiliates Order CREATE 
$router->post('/merchants-affiliate-order/{merchant_id}/{order_amount}', ['uses' => 'MerchantsAffiliatesController@createMerchantsAffiliateOrder']);

# Ad Campaigns CREATE . READ . UPDATE . DELETE
### CREATE
$router->post('/adcampaigns/{name}/{cash_back_rate}/{date_from}/{date_to}', ['uses' => 'AdcampaignsController@create']);
### READ
$router->get('/adcampaigns[/{name}/{page_number}]', ['uses' => 'AdcampaignsController@getAdcampaigns']);
$router->get('/adcampaigns/{name}', ['uses' => 'AdcampaignsController@getAdcampaigns']);
$router->get('/adcampaigns/{adcampaign_id}/merchants[/{page_number}]', ['uses' => 'AdcampaignsController@getAdcampaignMerchants']);
$router->get('/adcampaigns-details/{id}', ['uses' => 'AdcampaignsController@getAdcampaignsInfo']);
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
