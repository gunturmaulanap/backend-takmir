<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAsatidzRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert murid_ids array to ensure it's an array
        if ($this->has('murid_ids') && is_string($this->input('murid_ids'))) {
            $muridIds = explode(',', $this->input('murid_ids'));
            $this->merge([
                'murid_ids' => array_filter(array_map('trim', $muridIds), 'strlen')
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Dapatkan ID asatidz dari route
        $asatidz = $this->route('asatidz');
        $asatidzId = is_object($asatidz) ? $asatidz->id : $asatidz;

        return [
            'nama' => 'required|string|max:255|unique:asatidzs,nama,' . $asatidzId . ',id,profile_masjid_id,' . $this->user()->getMasjidProfile()->id,
            'no_handphone' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
            'umur' => 'nullable|integer|min:1|max:150',
            'jenis_kelamin' => 'nullable|string|in:Laki-laki,Perempuan',
            'keahlian' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'murid_ids' => 'nullable|array',
            'murid_ids.*' => 'exists:jamaahs,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama asatidz harus diisi.',
            'nama.string' => 'Nama asatidz harus berupa teks.',
            'nama.max' => 'Nama asatidz maksimal 255 karakter.',
            'nama.unique' => 'Nama asatidz sudah digunakan.',
            'no_handphone.string' => 'No handphone harus berupa teks.',
            'no_handphone.max' => 'No handphone maksimal 15 karakter.',
            'alamat.string' => 'Alamat harus berupa teks.',
            'umur.integer' => 'Umur harus berupa angka.',
            'umur.min' => 'Umur minimal 1 tahun.',
            'umur.max' => 'Umur maksimal 150 tahun.',
            'jenis_kelamin.string' => 'Jenis kelamin harus berupa teks.',
            'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'keahlian.string' => 'Keahlian harus berupa teks.',
            'keahlian.max' => 'Keahlian maksimal 255 karakter.',
            'keterangan.string' => 'Keterangan harus berupa teks.',
            'murid_ids.array' => 'Murid harus berupa array.',
            'murid_ids.*.exists' => 'Murid tidak valid.',
        ];
    }
}
