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
        $events = Event::with('category')->latest()->paginate(10);
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
            $image->storeAs('public/photos', $imageName);
        }

        $event = Event::create(array_merge($validated, [
            'profile_masjid_id' => $masjidProfile->id,
            'user_id'           => $user->id,
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
     * Memperbarui event.
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        $validated = $request->validated();
        $imageName = $event->image;

        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::delete('public/photos/' . $event->image);
            }
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/photos', $imageName);
        }

        $event->update(array_merge($validated, [
            'slug'  => Str::slug($validated['nama']),
            'image' => $imageName,
        ]));

        return new EventResource(true, 'Data event berhasil diperbarui.', $event);
    }

    /**
     * Menghapus event.
     */
    public function destroy(Event $event)
    {
        if ($event->image) {
            Storage::delete('public/photos/' . $event->image);
        }
        $event->delete();
        return new EventResource(true, 'Data event berhasil dihapus.', null);
    }
}
