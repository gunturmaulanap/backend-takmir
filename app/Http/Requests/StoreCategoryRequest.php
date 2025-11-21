<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
            'nama' => 'required|string|max:255|unique:categories,nama,NULL,id,profile_masjid_id,' . $this->user()->getMasjidProfile()->id,
            'warna' => 'nullable|string|in:Blue,Green,Purple,Orange,Indigo',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama kategori harus diisi.',
            'nama.string' => 'Nama kategori harus berupa teks.',
            'nama.max' => 'Nama kategori maksimal 100 karakter.',
            'nama.unique' => 'Nama kategori sudah digunakaaaaan.',
            'warna.string' => 'Warna kategori harus berupa teks.',
            'warna.in' => 'Warna kategori harus salah satu dari: Blue, Green, Purple, Orange, Indigo.',
        ];
    }
}
