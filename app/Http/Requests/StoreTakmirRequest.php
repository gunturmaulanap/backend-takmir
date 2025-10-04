<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Auth\Authenticatable;

class StoreTakmirRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User|Authenticatable|null $user */
        $user = auth()->guard('api')->user();

        // User harus terautentikasi
        return $user !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8',
            'category_id' => 'required|exists:categories,id',
            'no_handphone' => 'nullable|string|max:15',
            'umur' => 'nullable|integer|min:1',
            'deskripsi_tugas' => 'nullable|string',
            'profile_masjid_id' => 'nullable|exists:profile_masjids,id', // Untuk superadmin
        ];
    }
}
