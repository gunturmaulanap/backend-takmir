<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTakmirRequest extends FormRequest
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
        // Dapatkan ID takmir dari route (karena Route Model Binding, ini bisa berupa object)
        $takmir = $this->route('takmir');
        $takmirId = is_object($takmir) ? $takmir->id : $takmir;

        return [
            // Takmir fields
            'nama' => 'required|string|max:255|unique:takmirs,nama,' . $takmirId . ',id,profile_masjid_id,' . $this->user()->getMasjidProfile()->id,
            'no_handphone' => 'nullable|string|max:15',
            'umur' => 'required|integer|min:17|max:120',
            'jabatan' => 'required|string|max:255',
            'deskripsi_tugas' => 'nullable|string',

            // User fields (optional)
            'username' => 'nullable|string|max:255|unique:users,username,' . $this->route('takmir')?->user_id,
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $this->route('takmir')?->user_id,
            'password' => 'nullable|string|min:6|confirmed',
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
            'umur.required' => 'Umur harus diisi.',
            'umur.min' => 'Umur minimal 17 tahun.',
            'umur.max' => 'Umur maksimal 120 tahun.',
            'jabatan.required' => 'Jabatan harus diisi.',
            'jabatan.string' => 'Jabatan harus berupa teks.',
            'jabatan.max' => 'Jabatan maksimal 255 karakter.',
            'deskripsi_tugas.string' => 'Deskripsi tugas harus berupa teks.',
            'username.string' => 'Username harus berupa teks.',
            'username.max' => 'Username maksimal 255 karakter.',
            'username.unique' => 'Username sudah digunakan.',
            'email.string' => 'Email harus berupa teks.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah digunakan.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }
}
