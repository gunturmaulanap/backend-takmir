<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Auth\Authenticatable;

class UpdateTakmirRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User|Authenticatable|null $user */
        $user = auth()->guard('api')->user();
        return $user && method_exists($user, 'getMasjidProfile') && $user->getMasjidProfile();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Validasi untuk data takmir dan user terkait
        return [
            // Takmir fields
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'no_handphone' => 'required|string|max:15',
            'umur' => 'required|integer|min:1|max:120',
            'deskripsi_tugas' => 'nullable|string',
            'is_active' => 'boolean',

            // User fields
            'username' => 'nullable|string|max:255|unique:users,username,' . $this->route('takmir')?->user_id,
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $this->route('takmir')?->user_id,
            'password' => 'nullable|string|min:6|confirmed',
        ];
    }
}
