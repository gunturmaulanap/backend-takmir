<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Event;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\EventResource;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;

class EventController extends Controller implements HasMiddleware
{
    // HAPUS CONSTRUCTOR DAN PROPERTI $user & $masjidProfile

    public static function middleware(): array
    {
        return [
            new Middleware(['permission:events.index'], only: ['index']),
            new Middleware(['permission:events.create'], only: ['store']),
            new Middleware(['permission:events.edit'], only: ['update']),
            new Middleware(['permission:events.delete'], only: ['destroy']),
        ];
    }


    /**
     * Menampilkan list event.
     * Trait HasMasjid akan otomatis memfilter berdasarkan masjid yang login.
     */
    public function index()
    {
        $query = Event::with('category');


        $events = $query->latest()->paginate(4);
        return new EventResource(true, 'List Data Events', $events);
    }

    /**
     * Menyimpan event baru.
     */
    public function store(StoreEventRequest $request)
    {
        $validated = $request->validated();

        // Ambil user dan profil masjid langsung dari request
        $user = $request->user();
        $masjidProfile = $user->getMasjidProfile();
        $imageName = null;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('photos', $imageName, 'public');
        }

        $event = Event::create(array_merge($validated, [
            'profile_masjid_id' => $masjidProfile->id,
            'created_by'        => $user->id,
            'slug'              => Str::slug($validated['nama']),
            'image'             => $imageName,
        ]));

        return new EventResource(true, 'Data event berhasil disimpan.', $event);
    }

    /**
     * Menampilkan detail satu event.
     * Route-Model Binding + Global Scope otomatis akan menangani keamanan.
     */
    public function show(Event $event)
    {
        return new EventResource(true, 'Detail data event berhasil dimuat.', $event->load('category'));
    }

    /**
     * Menampilkan detail event berdasarkan slug.
     */



    /**
     * Mendapatkan saran event yang mirip
     */
    // private function getEventSuggestions($slug)
    // {
    //     $slugWords = array_filter(explode('-', $slug), function ($word) {
    //         return strlen($word) > 2;
    //     });

    //     if (empty($slugWords)) {
    //         return [];
    //     }

    //     $suggestions = Event::select('nama', 'slug')
    //         ->where(function ($query) use ($slugWords) {
    //             foreach ($slugWords as $word) {
    //                 $query->orWhere('nama', 'like', '%' . $word . '%')
    //                     ->orWhere('slug', 'like', '%' . $word . '%');
    //             }
    //         })
    //         ->limit(5)
    //         ->get();

    //     return $suggestions->map(function ($event) {
    //         return [
    //             'nama' => $event->nama,
    //             'slug' => $event->slug
    //         ];
    //     });
    // }

    /**
     * Memperbarui event.
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        $validated = $request->validated();

        // Prepare data untuk update
        $updateData = [
            'slug'       => Str::slug($validated['nama']),
            'updated_by' => $request->user()->id,
        ];

        // ✅ HANYA update image jika ada file upload baru
        if ($request->hasFile('image')) {
            // Hapus image lama
            if ($event->image) {
                $oldImageName = basename($event->image); // Extract filename dari URL
                Storage::disk('public')->delete('photos/' . $oldImageName);
            }

            // Upload image baru
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('photos', $imageName, 'public');

            // Simpan filename saja, bukan full URL
            $updateData['image'] = $imageName;
        }
        // ❌ Jika tidak ada file upload, JANGAN tambahkan 'image' ke $updateData

        // Merge dengan validated data
        $event->update(array_merge($validated, $updateData));

        // Reload event untuk mendapatkan data terbaru termasuk slug baru
        $event->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Data event berhasil diperbarui.',
            'data' => $event->load('category'),
            'meta' => [
                'new_slug' => $event->slug,
                'old_slug_updated' => true
            ]
        ]);
    }

    /**
     * Menghapus event.
     */
    public function destroy(Event $event)
    {
        if ($event->image) {
            Storage::disk('public')->delete('photos/' . $event->image);
        }
        $event->delete();
        return new EventResource(true, 'Data event berhasil dihapus.', null);
    }
}
