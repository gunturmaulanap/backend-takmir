<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMuadzinRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'slug' => 'required|string|max:255|unique:muadzins,slug',
            'no_handphone' => 'required|string|max:20',
            'alamat' => 'required|string',
            'tugas' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'profile_masjid_id' => 'nullable|exists:profile_masjids,id', // Untuk superadmin
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
            'nama.required' => 'Nama muadzin wajib diisi.',
            'nama.max' => 'Nama muadzin maksimal 255 karakter.',
            'slug.required' => 'Slug wajib diisi.',
            'slug.unique' => 'Slug sudah digunakan.',
            'slug.max' => 'Slug maksimal 255 karakter.',
            'no_handphone.required' => 'No handphone wajib diisi.',
            'no_handphone.max' => 'No handphone maksimal 20 karakter.',
            'alamat.required' => 'Alamat wajib diisi.',
            'tugas.required' => 'Tugas wajib diisi.',
            'tugas.max' => 'Tugas maksimal 255 karakter.',
            'is_active.boolean' => 'Status aktif harus berupa boolean.',
            'profile_masjid_id.exists' => 'Profile masjid tidak ditemukan.',
        ];
    }
}
