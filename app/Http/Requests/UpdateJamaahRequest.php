<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Auth\Authenticatable;

class UpdateJamaahRequest extends FormRequest
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
        $jamaahId = $this->route('jamaah');

        return [
            'nama' => [
                'required',
                'string',
                'max:255',
            ],
            'no_handphone' => 'required|string|max:15',
            'alamat' => 'required|string',
            'umur' => 'required|integer|min:1|max:150',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'aktivitas_jamaah' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama jamaah wajib diisi.',
            'nama.max' => 'Nama jamaah maksimal 255 karakter.',
            'no_handphone.required' => 'Nomor handphone wajib diisi.',
            'no_handphone.max' => 'Nomor handphone maksimal 15 karakter.',
            'alamat.required' => 'Alamat wajib diisi.',
            'umur.required' => 'Umur wajib diisi.',
            'umur.integer' => 'Umur harus berupa angka.',
            'umur.min' => 'Umur minimal 1 tahun.',
            'umur.max' => 'Umur maksimal 150 tahun.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib diisi.',
            'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'aktivitas_jamaah.max' => 'Aktivitas jamaah maksimal 255 karakter.',
        ];
    }
}
