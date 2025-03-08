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
Route::middleware(['web', 'auth'])->get('/user', function (Request $request) {
    return response()->json([
        'user_id' => $request->user()->id,
        'user_name' => $request->user()->name,
        'user_email' => $request->user()->email,
    ]);
});