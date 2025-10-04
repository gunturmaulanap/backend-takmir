<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class StoreKhatibRequest extends FormRequest
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
            'nama'            => 'required|string|max:255',
            'no_handphone'    => 'nullable|string|max:15',
            'alamat'          => 'nullable|string',
            'tanggal_khutbah' => 'required|date|after_or_equal:today', // Pastikan tanggal valid
            'judul_khutbah'   => 'required|string|max:255',
            'profile_masjid_id' => 'nullable|exists:profile_masjids,id', // Untuk superadmin
        ];
    }
}
