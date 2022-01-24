<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

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
// 4. Otherwise this URL is probably used by the Angular frontend so return that and leave the error handling to JavaScript.

Route::any('/api', function() {
    abort(404);
});
Route::any('/api/{any}', function() {
    abort(404);
});

Route::fallback(function () {
    return File::get(public_path() . '/angular-assets/index.html');
});
