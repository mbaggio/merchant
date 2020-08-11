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

/*
Route::resource('users', 'UserController', ['only' => ['store']]);

Route::resource('cart_lotteries', 'CartSubscriptionController',
    ['parameters' => [
        'cart_lotteries' => 'cart_subscription',
        ],
    'names' => [
        'store' => 'cart_subscriptions.store',
        'show' => 'cart_subscriptions.show',
        'update' => 'cart_subscriptions.update',
        'destroy' => 'cart_subscriptions.destroy',
        ]
    ]
    );


Route::get('/lottery_syndicate_subscriptions', 'SyndicateCartSubscriptionController@index');
Route::put('/users', 'UserController@update_me');
Route::resource('cart_raffles', 'CartRaffleController', ['only' => ['show', 'store', 'destroy', 'update']]);
*/