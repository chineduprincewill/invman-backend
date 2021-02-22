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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// User authentication route
Route::post('login', 'UserController@authenticate');
Route::get('inventory', 'InventoryController@index');
Route::get('history/{id}', 'HistoryController@show');


Route::group(['middleware' => ['jwt.verify']], function(){
    // Inventory routes
    Route::post('inventory', 'InventoryController@store');
    Route::get('inventory/{id}', 'InventoryController@show');
    Route::put('inventory/{id}', 'InventoryController@update');

    // History routes
    Route::post('history/{id}', 'HistoryController@disburse');
});
