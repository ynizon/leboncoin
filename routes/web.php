<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/graph');
});

Route::get('/json', [Controller::class,'json']);
Route::get('/csv', [Controller::class,'csv']);
Route::get('/graph', [Controller::class,'index']);
Route::post('/graph', [Controller::class,'postCars']);
Route::post('/save', [Controller::class,'save']);

require __DIR__.'/auth.php';
