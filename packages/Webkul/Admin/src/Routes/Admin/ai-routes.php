<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\AIController;

Route::controller(AIController::class)->prefix('ai')->group(function () {
    // API endpoints
    Route::post('improve', 'improve')->name('ai.improve');

    Route::post('answer', 'answer')->name('ai.answer');
});
