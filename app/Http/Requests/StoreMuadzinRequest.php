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
            'nama' => 'required|string|max:255|unique:muadzins,nama,NULL,id,profile_masjid_id,' . $this->user()->getMasjidProfile()->id,
            'no_handphone' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'tugas' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama muadzin harus diisi.',
            'nama.string' => 'Nama muadzin harus berupa teks.',
            'nama.max' => 'Nama muadzin maksimal 255 karakter.',
            'nama.unique' => 'Nama muadzin sudah digunakan.',
            'no_handphone.string' => 'No handphone harus berupa teks.',
            'no_handphone.max' => 'No handphone maksimal 20 karakter.',
            'alamat.string' => 'Alamat harus berupa teks.',
            'tugas.string' => 'Tugas harus berupa teks.',
            'tugas.max' => 'Tugas maksimal 255 karakter.',
            'is_active.boolean' => 'Status active harus berupa boolean (true/false).',
        ];
    }
}
