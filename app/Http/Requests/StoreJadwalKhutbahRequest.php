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
        return [
            'tanggal' => 'required|date|after_or_equal:today',
            'hari' => 'nullable|string|max:20',
            'imam_id' => 'nullable|exists:imams,id',
            'khatib_id' => 'nullable|exists:khatibs,id',
            'muadzin_id' => 'nullable|exists:muadzins,id',
            'tema_khutbah' => 'nullable|string',
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
            'tanggal.required' => 'Tanggal khutbah wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tanggal.after_or_equal' => 'Tanggal khutbah tidak boleh di masa lalu.',
            'hari.max' => 'Hari maksimal 20 karakter.',
            'imam_id.exists' => 'Imam tidak ditemukan.',
            'khatib_id.exists' => 'Khatib tidak ditemukan.',
            'muadzin_id.exists' => 'Muadzin tidak ditemukan.',
            'profile_masjid_id.exists' => 'Profile masjid tidak ditemukan.',
        ];
    }
}
