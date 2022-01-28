<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiCatchAllController;
use App\Http\Controllers\AngularController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Any URL that doesn't go to our backend API should just see the Angular
// frontend. Note that any files in the public folder will be served first if
// the URL matches any of their names. So the rules are:
// 1. Look for public file, if it exists then return that.
// 2. Look for an API Route in api.php
// 3. If URL starts with /api then it was supposed to use our API so return "NOT FOUND" (404) error
//   - For info about matching any route see: https://laravel.com/docs/8.x/routing#parameters-encoded-forward-slashes
// 4. Otherwise this URL is probably used by the Angular frontend so return that and leave the error handling to JavaScript.
//
// Note: the below controllers are "Single Action Controllers", see: https://laravel.com/docs/8.x/controllers#single-action-controllers

Route::get('/api{any}', ApiCatchAllController::class)->where('any', '.*');

Route::fallback(AngularController::class);
