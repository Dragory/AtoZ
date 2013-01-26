<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

// THE FRONT PAGE
Route::get('/', ['as' => 'index', 'uses' => 'IndexController@index']);

// LOGGING IN/OUT (STEAM OPENID)
Route::get('/login', ['as' => 'login', 'uses' => 'LoginController@login']);
Route::get('/login_after', ['as' => 'login_after', 'uses' => 'LoginController@login_after']);
Route::post('/logout', ['as' => 'logout', 'uses' => 'LoginController@logout', 'before' => 'csrf']);

// LISTS
Route::get('/list/(:num)', ['as' => 'list', 'uses' => 'ListController@list']);