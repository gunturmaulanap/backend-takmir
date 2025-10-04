<?php

namespace App\Http\Controllers\Api\Superadmin;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class RoleController extends Controller implements HasMiddleware
{
    /**
     * middleware
     *
     * @return array
     */
    public static function middleware(): array
    {
        return [
            new Middleware(['permission:roles.index'], only: ['index', 'all']),
            new Middleware(['permission:roles.create'], only: ['store']),
            new Middleware(['permission:roles.edit'], only: ['update']),
            new Middleware(['permission:roles.delete'], only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResource
     */
    public function index(): JsonResource
    {
        $roles = Role::when(request()->search, function ($roles) {
            $roles = $roles->where('name', 'like', '%' . request()->search . '%');
        })->with('permissions')->latest()->paginate(5);
        
        $roles->appends(['search' => request()->search]);

        return new RoleResource(true, 'List Data Roles', $roles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResource
     */
    public function store(Request $request): JsonResource
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|unique:roles,name',
            'permissions'   => 'required|array',
        ]);

        if ($validator->fails()) {
            return new RoleResource(false, 'Validasi Gagal!', $validator->errors());
        }

        $permissions = Permission::whereIn('name', $request->permissions)->pluck('name')->toArray();
        if (count($permissions) !== count($request->permissions)) {
            return new RoleResource(false, 'Beberapa izin yang dimasukkan tidak ditemukan.', ['permissions' => ['Satu atau lebih izin yang Anda berikan tidak valid.']]);
        }

        $role = Role::create(['name' => $request->name]);
        $role->givePermissionTo($permissions);

        if ($role) {
            return new RoleResource(true, 'Data Role Berhasil Disimpan!', $role);
        }

        return new RoleResource(false, 'Data Role Gagal Disimpan!', null);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return JsonResource
     */
    public function show($id): JsonResource
    {
        try {
            $role = Role::with('permissions')->findOrFail($id);
            return new RoleResource(true, 'Detail Data Role!', $role);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new RoleResource(false, 'Detail Data Role Tidak Ditemukan!', null);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Spatie\Permission\Models\Role  $role
     * @return JsonResource
     */
    public function update(Request $request, Role $role): JsonResource
    {
        $validator = Validator::make($request->all(), [
            // Perbaikan ada di baris ini:
                'name'          => ['required', 'string', Rule::unique('roles')->ignore($role->id)],
            'permissions'   => 'required|array',
        ]);
    
        if ($validator->fails()) {
            return new RoleResource(false, 'Validasi Gagal!', $validator->errors());
        }
    
        $permissions = Permission::whereIn('name', $request->permissions)->pluck('name')->toArray();
        if (count($permissions) !== count($request->permissions)) {
            return new RoleResource(false, 'Beberapa izin yang dimasukkan tidak ditemukan.', ['permissions' => ['Satu atau lebih izin yang Anda berikan tidak valid.']]);
        }
    
        $role->update(['name' => $request->name]);
        $role->syncPermissions($permissions);
    
        if ($role) {
            return new RoleResource(true, 'Data Role Berhasil Diupdate!', $role);
        }
    
        return new RoleResource(false, 'Data Role Gagal Diupdate!', null);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResource
     */
    public function destroy($id): JsonResource
    {
        try {
            $role = Role::findOrFail($id);
            if ($role->delete()) {
                return new RoleResource(true, 'Data Role Berhasil Dihapus!', null);
            }
            return new RoleResource(false, 'Data Role Gagal Dihapus!', null);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new RoleResource(false, 'Data Role Tidak Ditemukan!', null);
        }
    }

    /**
     * all
     *
     * @return JsonResource
     */
    public function all(): JsonResource
    {
        $roles = Role::latest()->get();
        return new RoleResource(true, 'List Data Roles', $roles);
    }
}