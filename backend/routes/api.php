<?php

use App\Http\Controllers\PostCommentController;
use App\Http\Controllers\PostController;
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

// Useful info when setting up routes:
//
// - `apiResource` is similar to `resource` except it doesn't use any routes
//   that present HTML templates such as `create` and `edit`.
//   - Use `php artisan make:controller PostController --api` to create a
//     controller that will be used via `apiResource`
//   - See: https://laravel.com/docs/8.x/controllers#api-resource-routes
// - "Implicit Binding" is when Laravel translates a raw id in the URL to a
//   Eloquent model specified via a type hint for a variable in the controller.
//   - See: https://laravel.com/docs/8.x/routing#implicit-binding
//   - Note that variable names are very important for this feature.
// - "Nested Resources" is when a route has multiple ids in it, such as
//   "/posts/{post}/comments/{comment}".
//   - See: https://laravel.com/docs/8.x/controllers#restful-nested-resources
//   - For `apiResource` and `resource` these are specified using dots (.), for
//     example: "posts.comments".
//   - If the controller for such a route is using implicit bindings then you
//     should use the `scoped` method to enforce that the later model belongs to
//     the earlier one. This requires that the relationship between the models
//     is defined in the model itself.
//     - For enforcing scopes see:
//       https://laravel.com/docs/8.x/controllers#scoping-nested-resources
//     - For model relationships see:
//       https://laravel.com/docs/8.x/eloquent-relationships
//     - For scoping's reliance on model relationships see:
//       https://laravel.com/docs/8.x/routing#implicit-model-binding-scoping
//   - Nested routes can also be flattened using the `shallow` method to allow
//     access to the inner resource directly. For example as
//     "/comments/{comment}". Creation and listing (many items) is still done
//     via the parent however.
//     - For more info see:
//       https://laravel.com/docs/8.x/controllers#shallow-nesting
//     - One drawback of this is that the child resource's name becomes global
//       so this can't be done if there are multiple routes with the same child
//       resource name. For example "/images/{image}/comments/{comment}" and
//       "/posts/{post}/comments/{comment}" can't both be flattened.

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('posts', PostController::class);
Route::apiResource('posts.comments', PostCommentController::class)->scoped();
