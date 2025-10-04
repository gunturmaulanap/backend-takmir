<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Otorisasi sudah ditangani oleh middleware permission, jadi kita bisa set ke true.
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
            'nama' => 'required|string|max:255|unique:events,nama',
            'deskripsi'       => 'required|string',
            'image'           => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tanggal_event'   => 'required|date',
            'waktu_event'     => 'required|date_format:H:i',
            'lokasi'          => 'required|string|max:255',
            'category_id'     => 'required|exists:categories,id',
        ];
    }
}
