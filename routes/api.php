<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\SignUpController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RefreshTokenController;
use App\Http\Controllers\Api\Admin\TakmirController;
use App\Http\Controllers\Api\Superadmin\DashboardController;
use App\Http\Controllers\Api\Superadmin\PermissionController;
use App\Http\Controllers\Api\Superadmin\RoleController;
use App\Http\Controllers\Api\Superadmin\UserController;
use App\Http\Controllers\Api\Superadmin\ProfileMasjidController;
use App\Http\Controllers\Api\Superadmin\AdminController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\JamaahController;
use App\Http\Controllers\Api\Admin\AsatidzController;
use App\Http\Controllers\Api\Admin\EventController;
use App\Http\Controllers\Api\Admin\EventViewController;
use App\Http\Controllers\Api\Admin\KhatibController;
use App\Http\Controllers\Api\Admin\ImamController;
use App\Http\Controllers\Api\Admin\MuadzinController;
use App\Http\Controllers\Api\Admin\JadwalKhutbahController;
use App\Http\Controllers\Api\Admin\TransaksiKeuanganController;
use App\Http\Controllers\Api\MeController;

// Test endpoint (no auth required)
Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working!',
        'timestamp' => now(),
        'cors_enabled' => true
    ]);
});

// Route untuk sign up (register)
Route::post('/signup', [SignUpController::class, '__invoke']);

// route login dengan rate limiting (20 attempts per minute)
// Increased for development and proactive refresh scenarios
Route::post('/login', [LoginController::class, 'index'])->middleware('throttle:20,1');

// refresh token endpoints (dengan rate limiting) - tetap di luar auth untuk refresh expired token
// Increased limit for proactive refresh scenarios
Route::post('/refresh', [RefreshTokenController::class, 'refresh'])->middleware('throttle:60,1');

// group route with middleware "auth"
Route::group(['middleware' => 'auth:api'], function () {

    // Get current authenticated user
    Route::get('/me', MeController::class);

    // logout (hapus semua refresh tokens - forced logout dari semua devices)
    Route::post('/logout', [LoginController::class, 'logout']);

    // revoke current refresh token (logout dari device saat ini saja)
    Route::post('/revoke-token', [RefreshTokenController::class, 'revoke']);



    // Grup rute untuk Admin (Hanya admin dan takmir)
    Route::prefix('admin')->as('admin.')->middleware('custom.role:admin,takmir')->group(function () {

        // Categories API - specific routes HARUS sebelum apiResource
        Route::apiResource('/categories', AdminCategoryController::class);
        // Transaksi Keuangan API - specific routes HARUS sebelum apiResource
        Route::get('/transactions/dashboard', [TransaksiKeuanganController::class, 'dashboard']);
        Route::get('/transactions/chart-data', [TransaksiKeuanganController::class, 'chartData']);
        Route::get('/transactions/monthly-summary', [TransaksiKeuanganController::class, 'monthlySummary']);
        Route::apiResource('/transactions', TransaksiKeuanganController::class);

        // Takmir API
        Route::apiResource('/takmirs', TakmirController::class);
        Route::patch('/takmirs/{takmir}/status', [TakmirController::class, 'updateStatus']);

        // Khatib API
        Route::apiResource('/khatibs', KhatibController::class);
        Route::patch('/khatibs/{khatib}/status', [KhatibController::class, 'updateStatus']);

        // Imam API
        Route::apiResource('/imams', ImamController::class);
        Route::patch('/imams/{imam}/status', [ImamController::class, 'updateStatus']);

        // Muadzin API
        Route::apiResource('/muadzins', MuadzinController::class);
        Route::patch('/muadzins/{muadzin}/status', [MuadzinController::class, 'updateStatus']);

        // Jamaah API
        Route::apiResource('/jamaahs', JamaahController::class);

        // Asatidz API
        Route::apiResource('/asatidzs', AsatidzController::class);
        Route::get('/asatidzs/murid-tpq/available', [AsatidzController::class, 'getAvailableMuridTPQ']);

        // Events API
        Route::apiResource('/events', EventController::class);

        // Event Views (Kalender) API
        Route::get('/event-views', [EventViewController::class, 'index']);

        // Jadwal Khutbah API
        Route::apiResource('/jadwal-khutbahs', JadwalKhutbahController::class);
    });
});

//group route with prefix "superadmin" (Hanya superadmin)
Route::prefix('superadmin')->as('superadmin.')->group(function () {
    //group route with middleware "auth:api" dan role superadmin
    Route::group(['middleware' => ['auth:api', 'custom.role:superadmin']], function () {
        //dashboard
        Route::get('/dashboard', DashboardController::class);

        //permissions
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::get('/permissions/all', [PermissionController::class, 'all']);

        //roles
        Route::resource('roles', RoleController::class);


        //categories - specific routes HARUS sebelum apiResource

        //users
        Route::apiResource('/users', UserController::class);
        Route::put('/users/{id}/toggle-active', [UserController::class, 'toggleActive'])
            ->middleware(['auth:api', 'permission:users.edit']);

        // Profile Masjid API
        Route::apiResource('/profile-masjids', ProfileMasjidController::class);
        Route::patch('/profile-masjids/{id}/status', [ProfileMasjidController::class, 'updateStatus'])
            ->middleware(['auth:api', 'permission:profile_masjids.edit']);
        Route::put('/profile-masjids/{id}/toggle-active', [ProfileMasjidController::class, 'toggleActive'])
            ->middleware(['auth:api', 'permission:profile_masjids.edit']);

        // Admin API (Pengurus Masjid)
        Route::apiResource('/admins', AdminController::class);
        Route::patch('/admins/{id}/status', [AdminController::class, 'updateStatus'])
            ->middleware(['auth:api', 'permission:admins.edit']);
        Route::put('/admins/{id}/toggle-active', [AdminController::class, 'toggleActive'])
            ->middleware(['auth:api', 'permission:admins.edit']);
        Route::get('/admins/unassigned', [AdminController::class, 'unassigned'])
            ->middleware(['auth:api', 'permission:admins.index']);
    });
});

// Debug route to check current authenticated user (outside of groups for easy access)
Route::get('/debug/current-user', function (\Illuminate\Http\Request $request) {
    $user = $request->user();
    $profileMasjidId = null;

    if ($user->roles->contains('name', 'superadmin')) {
        $profileMasjidId = $request->get('profile_masjid_id');
    } else {
        $profileMasjid = $user->getMasjidProfile();
        $profileMasjidId = $profileMasjid ? $profileMasjid->id : null;
    }

    return response()->json([
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_roles' => $user->roles->pluck('name'),
        'profile_masjid_id' => $profileMasjidId,
        'takmir_relation' => $user->takmir ? [
            'id' => $user->takmir->id,
            'profile_masjid_id' => $user->takmir->profile_masjid_id
        ] : null,
        'getMasjidProfile_result' => $user->getMasjidProfile() ? $user->getMasjidProfile()->id : null
    ]);
})->middleware(['auth:api']);

// Routes for generating shareable event cards (public access)
