<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
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
        // Dapatkan ID event dari route (karena Route Model Binding, ini bisa berupa object)
        $event = $this->route('event');
        $eventId = is_object($event) ? $event->id : $event;

        return [
            'nama' => 'required|string|max:255|unique:events,nama,' . $eventId . ',id,profile_masjid_id,' . $this->user()->getMasjidProfile()->id,

            'deskripsi'       => 'required|string',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tanggal_event'   => 'required|date',
            'waktu_event'     => 'required|date_format:H:i',
            'tempat_event'    => 'required|string|max:255',
            'category_id'     => 'required|exists:categories,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama event harus diisi.',
            'nama.string' => 'Nama event harus berupa teks.',
            'nama.max' => 'Nama event maksimal 255 karakter.',
            'nama.unique' => 'Nama event sudah digunakan.',
            'deskripsi.required' => 'Deskripsi event harus diisi.',
            'deskripsi.string' => 'Deskripsi event harus berupa teks.',
            'image.image' => 'File yang diupload harus berupa gambar.',
            'image.mimes' => 'Gambar harus berformat jpeg, png, atau jpg.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
            'tanggal_event.required' => 'Tanggal event harus diisi.',
            'tanggal_event.date' => 'Format tanggal tidak valid.',
            'waktu_event.required' => 'Waktu event harus diisi.',
            'waktu_event.date_format' => 'Format waktu harus HH:MM (contoh: 14:30).',
            'tempat_event.required' => 'Tempat event harus diisi.',
            'tempat_event.string' => 'Tempat event harus berupa teks.',
            'tempat_event.max' => 'Tempat event maksimal 255 karakter.',
            'category_id.required' => 'Kategori event harus dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
        ];
    }
}
