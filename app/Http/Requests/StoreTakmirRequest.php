<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTakmirRequest extends FormRequest
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
            // Takmir fields
            'nama' => 'required|string|max:255|unique:takmirs,nama,NULL,id,profile_masjid_id,' . $this->user()->getMasjidProfile()->id,
            'no_handphone' => 'nullable|string|max:15',
            'umur' => 'nullable|integer|min:17|max:120',
            'jabatan' => 'required|string|max:255',
            'deskripsi_tugas' => 'nullable|string',

            // User fields
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6',
            'email' => 'nullable|string|email|max:255|unique:users,email',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama takmir harus diisi.',
            'nama.string' => 'Nama takmir harus berupa teks.',
            'nama.max' => 'Nama takmir maksimal 255 karakter.',
            'nama.unique' => 'Nama takmir sudah digunakan.',
            'no_handphone.string' => 'No handphone harus berupa teks.',
            'no_handphone.max' => 'No handphone maksimal 15 karakter.',
            'umur.integer' => 'Umur harus berupa angka.',
            'umur.min' => 'Umur minimal 17 tahun.',
            'umur.max' => 'Umur maksimal 120 tahun.',
            'jabatan.required' => 'Jabatan harus diisi.',
            'jabatan.string' => 'Jabatan harus berupa teks.',
            'jabatan.max' => 'Jabatan maksimal 255 karakter.',
            'deskripsi_tugas.string' => 'Deskripsi tugas harus berupa teks.',
            'username.required' => 'Username harus diisi.',
            'username.string' => 'Username harus berupa teks.',
            'username.max' => 'Username maksimal 255 karakter.',
            'username.unique' => 'Username sudah digunakan.',
            'password.required' => 'Password harus diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 6 karakter.',
            'email.string' => 'Email harus berupa teks.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah digunakan.',
        ];
    }
}
