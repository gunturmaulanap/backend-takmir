<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKhatibRequest extends FormRequest
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
            'nama' => 'required|string|max:255|unique:khatibs,nama,NULL,id,profile_masjid_id,' . $this->user()->getMasjidProfile()->id,
            'no_handphone' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama khatib harus diisi.',
            'nama.string' => 'Nama khatib harus berupa teks.',
            'nama.max' => 'Nama khatib maksimal 255 karakter.',
            'nama.unique' => 'Nama khatib sudah digunakan.',
            'no_handphone.string' => 'No handphone harus berupa teks.',
            'no_handphone.max' => 'No handphone maksimal 15 karakter.',
            'alamat.string' => 'Alamat harus berupa teks.',
            'is_active.boolean' => 'Status active harus berupa boolean (true/false).',
        ];
    }
}
