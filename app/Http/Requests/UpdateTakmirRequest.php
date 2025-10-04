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
        // Validasi hanya untuk data yang boleh diubah.
        // Data user seperti email/password tidak diubah di sini.
        return [
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'required|exists:categories,id',
            'no_handphone' => 'nullable|string|max:15',
            'umur' => 'nullable|integer|min:1',
            'deskripsi_tugas' => 'nullable|string',
        ];
    }
}
