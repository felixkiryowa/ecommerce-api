<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('v1/register', 'User\UserController@register');
Route::post('v1/authenticate', 'User\UserController@authenticate');
Route::get('v1/open', 'TestData\DataController@open');
// Get all users 
Route::get('v1/users', 'User\UserController@get_all_users');
// List a products
Route::get('v1/products', 'Product\ProductController@index');
// List single product
Route::get('v1/product/{id}', 'Product\ProductController@show');

// Create new product
Route::post('v1/product', 'Product\ProductController@store');

// Update product
Route::post('v1/edit/product', 'Product\ProductController@edit');

// Delete product
Route::delete('v1/product/{id}', 'Product\ProductController@destroy');

Route::group(['middleware' => ['jwt.verify']], function() {
    // Delete user
    Route::delete('v1/user/{id}', 'User\UserController@delete_user');
    Route::get('v1/user', 'User\UserController@getAuthenticatedUser');
    Route::get('v1/closed', 'TestData\DataController@closed');
});
