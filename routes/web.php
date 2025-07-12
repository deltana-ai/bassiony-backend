<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});
Route::get('/test', function () {

    return view('text');
});

require __DIR__.'/auth.php';
