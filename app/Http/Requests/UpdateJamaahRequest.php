<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJamaahRequest extends FormRequest
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
        // Convert aktivitas_jamaah array to comma-separated string
        if ($this->has('aktivitas_jamaah') && is_array($this->input('aktivitas_jamaah'))) {
            $this->merge([
                'aktivitas_jamaah' => implode(', ', array_filter($this->input('aktivitas_jamaah'))),
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
        // Dapatkan ID jamaah dari route (karena Route Model Binding, ini bisa berupa object)
        $jamaah = $this->route('jamaah');
        $jamaahId = is_object($jamaah) ? $jamaah->id : $jamaah;

        return [
            'nama' => 'required|string|max:255|unique:jamaahs,nama,' . $jamaahId . ',id,profile_masjid_id,' . $this->user()->getMasjidProfile()->id,
            'no_handphone' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
            'umur' => 'nullable|integer|min:1|max:150',
            'jenis_kelamin' => 'nullable|string|in:Laki-laki,Perempuan',
            'aktivitas_jamaah' => 'nullable|array|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama jamaah harus diisi.',
            'nama.string' => 'Nama jamaah harus berupa teks.',
            'nama.unique' => 'Nama jamaah sudah digunakan.',
            'nama.max' => 'Nama jamaah maksimal 255 karakter.',
            'nama.unique' => 'Nama jamaah sudah digunakan.',
            'no_handphone.string' => 'No handphone harus berupa teks.',
            'no_handphone.max' => 'No handphone maksimal 15 karakter.',
            'alamat.string' => 'Alamat harus berupa teks.',
            'umur.integer' => 'Umur harus berupa angka.',
            'umur.min' => 'Umur minimal 1 tahun.',
            'umur.max' => 'Umur maksimal 150 tahun.',
            'jenis_kelamin.string' => 'Jenis kelamin harus berupa teks.',
            'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'aktivitas_jamaah.string' => 'Aktivitas jamaah harus berupa teks.',
            'aktivitas_jamaah.max' => 'Aktivitas jamaah maksimal 255 karakter.',
        ];
    }
}
