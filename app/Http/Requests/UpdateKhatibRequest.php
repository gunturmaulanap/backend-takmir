<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class UpdateKhatibRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User|Authenticatable|null $user */
        $user = auth()->guard('api')->user();

        // Keamanan: user harus login dan punya profil masjid.
        return $user && method_exists($user, 'getMasjidProfile') && $user->getMasjidProfile();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama'            => 'sometimes|required|string|max:255',
            'no_handphone'    => 'sometimes|nullable|string|max:15',
            'alamat'          => 'sometimes|nullable|string',
            'tanggal_khutbah' => 'sometimes|required|date',
            'judul_khutbah'   => 'sometimes|required|string|max:255',
        ];
    }
}
