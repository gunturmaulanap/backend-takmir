<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShareController;

// Route for preview page with Open Graph tags for social sharing
Route::get('/share/preview/event/{slug}', [ShareController::class, 'generatePreviewPage'])->name('share.preview');

Route::get('/', function () {
    return view('welcome');
});

// Sanctum CSRF Cookie endpoint
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});
