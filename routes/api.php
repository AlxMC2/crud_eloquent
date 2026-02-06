<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::post('/users',[UserController::class, 'store']);
    Route::get('/users',[UserController::class,'index']);
    Route::get('/users/{user}',[UserController::class,'show']);
    Route::patch('/users/{user}',[UserController::class,'update']);
});
