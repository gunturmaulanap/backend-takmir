<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\SignUpController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Admin\TakmirController;
use App\Http\Controllers\Api\Superadmin\DashboardController;
use App\Http\Controllers\Api\Superadmin\PermissionController;
use App\Http\Controllers\Api\Superadmin\RoleController;
use App\Http\Controllers\Api\Superadmin\UserController;
use App\Http\Controllers\Api\Superadmin\CategoryController as SuperadminCategoryController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\JamaahController;
use App\Http\Controllers\Api\Admin\EventController;
use App\Http\Controllers\Api\Admin\EventViewController;
use App\Http\Controllers\Api\Admin\KhatibController;
use App\Http\Controllers\Api\Admin\ImamController;
use App\Http\Controllers\Api\Admin\MuadzinController;
use App\Http\Controllers\Api\Admin\JadwalKhutbahController;
use App\Http\Controllers\Api\Admin\TransaksiKeuanganController;

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

// route login
Route::post('/login', [LoginController::class, 'index']);

// group route with middleware "auth"
Route::group(['middleware' => 'auth:api'], function () {

    // logout
    Route::post('/logout', [LoginController::class, 'logout']);

    // Grup rute untuk Admin (Hanya admin dan takmir)
    Route::prefix('admin')->as('admin.')->middleware('custom.role:admin,takmir')->group(function () {

        // Categories API - specific routes HARUS sebelum apiResource
        Route::get('/categories/all', [AdminCategoryController::class, 'all']);
        Route::apiResource('/categories', AdminCategoryController::class)->names([
            'index' => 'admin.categories.index',
            'store' => 'admin.categories.store',
            'show' => 'admin.categories.show',
            'update' => 'admin.categories.update',
            'destroy' => 'admin.categories.destroy',
        ]);

        // Transaksi Keuangan API - specific routes HARUS sebelum apiResource
        Route::get('/transactions/dashboard', [TransaksiKeuanganController::class, 'dashboard']);
        Route::get('/transactions/chart-data', [TransaksiKeuanganController::class, 'chartData']);
        Route::get('/transactions/monthly-summary', [TransaksiKeuanganController::class, 'monthlySummary']);
        Route::apiResource('/transactions', TransaksiKeuanganController::class);

        // Takmir API
        Route::apiResource('/takmirs', TakmirController::class);

        // Jamaah API
        Route::apiResource('/jamaahs', JamaahController::class);

        // Events API
        Route::apiResource('/events', EventController::class);

        // Event Views (Kalender) API
        Route::get('/event-views', [EventViewController::class, 'index']);

        // Khatib API
        Route::apiResource('/khatibs', KhatibController::class);

        // Imam API
        Route::apiResource('/imams', ImamController::class);

        // Muadzin API
        Route::apiResource('/muadzins', MuadzinController::class);

        // Jadwal Khutbah API
        Route::apiResource('/jadwal-khutbahs', JadwalKhutbahController::class);

        // Additional routes for specific functionalities
        // Dashboard untuk admin/takmir (jika diperlukan)
        // Route::get('/dashboard', [AdminDashboardController::class, 'index']);

        // Reports routes (jika diperlukan)
        // Route::get('/reports/financial', [TransaksiKeuanganController::class, 'generateReport']);
        // Route::get('/reports/attendance', [JamaahController::class, 'attendanceReport']);
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
