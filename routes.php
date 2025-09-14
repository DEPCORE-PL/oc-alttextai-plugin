<?php
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function() {
    Route::post('altext/webhook', [\Depcore\AltTextAi\Controllers\AltTextWebhook::class, 'handle']);
});
