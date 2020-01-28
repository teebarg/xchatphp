<?php

use Illuminate\Http\Request;

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

//health check port
Route::any('/health-check', function() {
    //redis connection is checked by default cause it's used for session storage
    //and all requests to the server creates a session by default
    //check if the database is connected
    \Illuminate\Support\Facades\DB::getDefaultConnection();
    return response()->json(['data' => 'Working']);
});

//Route::get('/', 'ChatsController@index');
//Route::get('messages', 'ChatsController@fetchMessages');
//Route::post('messages', 'ChatsController@sendMessage');

Route::get('private-messages/{user}', 'ChatsController@getMessages');
Route::post('private-messages', 'ChatsController@sendMessage');

Route::get('social', 'SocialController@index');
Route::post('social/manage/{user}', 'SocialController@manage');

//Route::apiResource('roles', 'RolesController');
//Route::apiResource('permissions', 'PermissionsController');
Route::apiResource('country', 'CountryController');
Route::apiResource('state', 'StateController');
//Route::apiResource('user', 'UserController');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::get('me', 'AuthController@me');
    Route::post('register', 'AuthController@register');
    Route::post('social','AuthController@socialite');
});
