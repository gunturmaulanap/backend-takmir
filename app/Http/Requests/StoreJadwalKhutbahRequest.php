<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJadwalKhutbahRequest extends FormRequest
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
        $profileMasjidId = $this->user()->getMasjidProfile()->id;

        return [
            'tanggal' => [
                'required',
                'date',
                'after_or_equal:today',
                // Unique validation untuk tanggal di masjid yang sama
                "unique:jadwal_khutbahs,tanggal,NULL,id,profile_masjid_id,{$profileMasjidId}"
            ],
            'imam_id' => 'required|exists:imams,id',
            'khatib_id' => 'required|exists:khatibs,id',
            'muadzin_id' => 'required|exists:muadzins,id',
            'tema_khutbah' => 'required|string|min:3|max:255',
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
            'tanggal.required' => 'Tanggal khutbah wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tanggal.after_or_equal' => 'Tanggal khutbah tidak boleh di masa lalu.',
            'tanggal.unique' => 'Tanggal jadwal khutbah untuk masjid ini sudah digunakan.',
            'imam_id.required' => 'Imam harus dipilih.',
            'imam_id.exists' => 'Imam tidak ditemukan.',
            'khatib_id.required' => 'Khatib harus dipilih.',
            'khatib_id.exists' => 'Khatib tidak ditemukan.',
            'muadzin_id.required' => 'Muadzin harus dipilih.',
            'muadzin_id.exists' => 'Muadzin tidak ditemukan.',
            'tema_khutbah.required' => 'Tema khutbah wajib diisi.',
            'tema_khutbah.min' => 'Tema khutbah minimal 3 karakter.',
            'tema_khutbah.max' => 'Tema khutbah maksimal 255 karakter.',
        ];
    }
}
