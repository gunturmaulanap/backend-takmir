<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use App\Http\Resources\PermissionResource;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class PermissionController extends Controller implements HasMiddleware
{
    /**
     * middleware
     *
     * @return array
     */
    public static function middleware(): array
    {
        return [
            new Middleware(['permission:permissions.index'], only: ['index', 'all']),
        ];
    }

    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        //get permissions
        $permissions = Permission::when(request()->search, function ($permissions) {
            $permissions = $permissions->where('name', 'like', '%' . request()->search . '%');
        })->latest()->paginate(100);

        //append query string to pagination links
        $permissions->appends(['search' => request()->search]);

        //return with Api Resource
        return new PermissionResource(true, 'List Data Permissions', $permissions);
    }

    /**
     * all
     *
     * @return void
     */
    public function all()
    {
        //get permissions
        $permissions = Permission::latest()->get();

        //return with Api Resource
        return new PermissionResource(true, 'List Data Permissions', $permissions);
    }
}
