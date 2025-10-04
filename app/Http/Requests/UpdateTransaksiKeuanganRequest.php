<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransaksiKeuanganRequest extends FormRequest
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
            'type' => 'required|in:income,expense',
            'kategori' => 'required|string|max:100',
            'jumlah' => 'required|numeric|min:0',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:1000',
            'bukti_transaksi' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            'type.required' => 'Tipe transaksi wajib diisi.',
            'type.in' => 'Tipe transaksi harus income atau expense.',
            'kategori.required' => 'Kategori transaksi wajib diisi.',
            'kategori.max' => 'Kategori maksimal 100 karakter.',
            'jumlah.required' => 'Jumlah transaksi wajib diisi.',
            'jumlah.numeric' => 'Jumlah harus berupa angka.',
            'jumlah.min' => 'Jumlah tidak boleh negatif.',
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'keterangan.max' => 'Keterangan maksimal 1000 karakter.',
            'bukti_transaksi.image' => 'Bukti transaksi harus berupa gambar.',
            'bukti_transaksi.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif.',
            'bukti_transaksi.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
