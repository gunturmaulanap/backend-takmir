<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EventView;
use App\Models\Event;
use App\Models\JadwalKhutbah;
use App\Models\ProfileMasjid;

class EventViewTableSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua profile masjid
        $masjids = ProfileMasjid::all();

        foreach ($masjids as $masjid) {
            $userId = $masjid->user_id;

            // 1. Masukkan data dari Events untuk masjid ini
            $events = Event::where('profile_masjid_id', $masjid->id)->get();

            foreach ($events as $event) {
                EventView::create([
                    'profile_masjid_id' => $masjid->id,
                    'event_id' => $event->id,
                    'jadwal_khutbah_id' => null,
                    'title' => $event->nama,
                    'tanggal' => $event->tanggal_event,
                    'waktu' => $event->waktu_event,
                    'type' => 'event',
                    'description' => $event->deskripsi,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            // 2. Masukkan data dari JadwalKhutbah untuk masjid ini
            $jadwalKhutbahs = JadwalKhutbah::where('profile_masjid_id', $masjid->id)->get();

            foreach ($jadwalKhutbahs as $jadwal) {
                $title = 'Khatib: ' . ($jadwal->khatib->nama ?? 'TBA');

                EventView::create([
                    'profile_masjid_id' => $masjid->id,
                    'event_id' => null,
                    'jadwal_khutbah_id' => $jadwal->id,
                    'title' => $title,
                    'tanggal' => $jadwal->tanggal,
                    'waktu' => '12:00', // Default waktu jumat
                    'type' => 'jadwal_khutbah',
                    'description' => $jadwal->tema_khutbah ?? 'Khutbah Jumat',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
        }
    }
}
