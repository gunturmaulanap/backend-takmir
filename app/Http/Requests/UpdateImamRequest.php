<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateImamRequest extends FormRequest
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
        $imam = $this->route('imam'); // Mendapatkan imam dari route
        $profileMasjidId = $this->user()->getMasjidProfile()->id;

        return [
            'nama' => [
                'required',
                'string',
                'max:255',
                // Unique validation, tapi exclude record yang sedang diupdate
                "unique:imams,nama,{$imam->id},id,profile_masjid_id,{$profileMasjidId}"
            ],
            'no_handphone' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama imam harus diisi.',
            'nama.string' => 'Nama imam harus berupa teks.',
            'nama.max' => 'Nama imam maksimal 255 karakter.',
            'nama.unique' => 'Nama imam sudah digunakan.',
            'no_handphone.string' => 'No handphone harus berupa teks.',
            'no_handphone.max' => 'No handphone maksimal 20 karakter.',
            'alamat.string' => 'Alamat harus berupa teks.',
            'is_active.boolean' => 'Status active harus berupa boolean (true/false).',
        ];
    }
}
