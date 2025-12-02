<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJadwalKhutbahRequest extends FormRequest
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
            'tanggal' => 'required|date|after_or_equal:today|unique:jadwal_khutbahs,tanggal,NULL,id,profile_masjid_id,' . $this->user()->getMasjidProfile()->id,
            'imam_id' => 'required|exists:imams,id',
            'khatib_id' => 'required|exists:khatibs,id',
            'muadzin_id' => 'required|exists:muadzins,id',
            'tema_khutbah' => 'required|string',
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
            'imam_id.required' => 'Imam harus dipilih.',
            'khatib_id.required' => 'Khatib harus dipilih.',
            'muadzin_id.required' => 'Muadzin harus dipilih.',
        ];
    }
}
