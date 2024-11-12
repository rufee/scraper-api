<?php

use Illuminate\Support\Facades\Route;

Route::apiResource('jobs', \App\Http\Controllers\ScrapeJobController::class, ['only' => ['show', 'store', 'destroy']]);
