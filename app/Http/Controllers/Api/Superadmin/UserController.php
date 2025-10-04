<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Spatie\Permission\Models\Role; // Tambahkan ini

class UserController extends Controller implements HasMiddleware
{
    /**
     * middleware
     *
     * @return array
     */
    public static function middleware(): array
    {
        return [
            new Middleware(['permission:users.index'], only: ['index']),
            new Middleware(['permission:users.create'], only: ['store']),
            new Middleware(['permission:users.edit'], only: ['update']),
            new Middleware(['permission:users.delete'], only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //get users
        $users = User::when(request()->search, function ($users) {
            $users = $users->where('name', 'like', '%' . request()->search . '%');
        })->with('roles')->latest()->paginate(5);

        //append query string to pagination links
        $users->appends(['search' => request()->search]);

        //return with Api Resource
        return new UserResource(true, 'List Data Users', $users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'roles'     => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi Gagal!',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Validasi tambahan: Pastikan semua roles ada di database
        $roles = Role::whereIn('name', $request->roles)->pluck('name')->toArray();
        if (count($roles) !== count($request->roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Beberapa role yang dimasukkan tidak ditemukan.',
                'errors'  => ['roles' => ['Satu atau lebih role yang Anda berikan tidak valid.']]
            ], 422);
        }

        //create user
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => bcrypt($request->password)
        ]);

        //assign roles to user
        $user->assignRole($roles);

        if ($user) {
            //return success with Api Resource
            return new UserResource(true, 'Data User Berhasil Disimpan!', $user);
        }

        //return failed with Api Resource
        return new UserResource(false, 'Data User Gagal Disimpan!', null);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::with('roles')->whereId($id)->first();

        if ($user) {
            //return success with Api Resource
            return new UserResource(true, 'Detail Data User!', $user);
        }

        //return failed with Api Resource
        return new UserResource(false, 'Detail Data User Tidak Ditemukan!', null);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|string|min:8|confirmed',
            'roles'     => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi Gagal!',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Validasi tambahan: Pastikan semua roles ada di database
        $roles = Role::whereIn('name', $request->roles)->pluck('name')->toArray();
        if (count($roles) !== count($request->roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Beberapa role yang dimasukkan tidak ditemukan.',
                'errors'  => ['roles' => ['Satu atau lebih role yang Anda berikan tidak valid.']]
            ], 422);
        }

        if ($request->password == "") {
            //update user without password
            $user->update([
                'name'      => $request->name,
                'email'     => $request->email,
            ]);
        } else {
            //update user with new password
            $user->update([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => bcrypt($request->password)
            ]);
        }

        //assign roles to user
        $user->syncRoles($roles);

        if ($user) {
            //return success with Api Resource
            return new UserResource(true, 'Data User Berhasil Diupdate!', $user);
        }

        //return failed with Api Resource
        return new UserResource(false, 'Data User Gagal Diupdate!', null);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        if ($user->delete()) {
            //return success with Api Resource
            return new UserResource(true, 'Data User Berhasil Dihapus!', null);
        }

        //return failed with Api Resource
        return new UserResource(false, 'Data User Gagal Dihapus!', null);
    }

    /**
     * Toggle the active status of a user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleActive($id)
    {
        $user = User::find($id);

        if (!$user) {
            return new UserResource(false, 'User tidak ditemukan.', null);
        }

        // Toggle the is_active status
        $user->is_active = !$user->is_active;
        $user->save();

        // Check if the user is active or not
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return new UserResource(true, "User berhasil {$status}!", $user);
    }
}
