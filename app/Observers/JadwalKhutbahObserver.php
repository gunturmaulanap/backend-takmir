<?php

namespace App\Observers;

use App\Models\JadwalKhutbah;
use App\Models\EventView;
use Illuminate\Support\Facades\Log;

class JadwalKhutbahObserver
{
    /**
     * Handle the JadwalKhutbah "created" event.
     */
    public function created(JadwalKhutbah $jadwalKhutbah): void
    {
        try {
            // Create or update EventView record untuk JadwalKhutbah baru
            EventView::updateOrCreate(
                [
                    'profile_masjid_id' => $jadwalKhutbah->profile_masjid_id,
                    'jadwal_khutbah_id' => $jadwalKhutbah->id,
                    'type' => 'jadwal_khutbah',
                ],
                [
                    'event_id' => null,
                    'title' => 'Khutbah: ' . ($jadwalKhutbah->khatib->nama ?? 'TBA'),
                    'tanggal' => $jadwalKhutbah->tanggal,
                    'waktu' => '12:00', // Default waktu untuk khutbah jumat
                    'description' => $jadwalKhutbah->tema_khutbah ?? 'Khutbah Jumat',
                    'created_by' => $jadwalKhutbah->created_by,
                    'updated_by' => $jadwalKhutbah->updated_by,
                ]
            );

            Log::info('EventView created for JadwalKhutbah', [
                'jadwal_khutbah_id' => $jadwalKhutbah->id,
                'title' => $jadwalKhutbah->tema_khutbah
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create EventView for JadwalKhutbah', [
                'jadwal_khutbah_id' => $jadwalKhutbah->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the JadwalKhutbah "updated" event.
     */
    public function updated(JadwalKhutbah $jadwalKhutbah): void
    {
        try {
            // Update existing EventView record
            $eventView = EventView::where('jadwal_khutbah_id', $jadwalKhutbah->id)
                ->where('type', 'jadwal_khutbah')
                ->first();

            if ($eventView) {
                $eventView->update([
                    'title' => 'Khutbah: ' . ($jadwalKhutbah->khatib->nama ?? 'TBA'),
                    'tanggal' => $jadwalKhutbah->tanggal,
                    'description' => $jadwalKhutbah->tema_khutbah ?? 'Khutbah Jumat',
                    'updated_by' => $jadwalKhutbah->updated_by,
                ]);

                Log::info('EventView updated for JadwalKhutbah', [
                    'jadwal_khutbah_id' => $jadwalKhutbah->id,
                    'title' => $jadwalKhutbah->tema_khutbah
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update EventView for JadwalKhutbah', [
                'jadwal_khutbah_id' => $jadwalKhutbah->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the JadwalKhutbah "deleted" event.
     */
    public function deleted(JadwalKhutbah $jadwalKhutbah): void
    {
        try {
            // Delete corresponding EventView record
            EventView::where('jadwal_khutbah_id', $jadwalKhutbah->id)
                ->where('type', 'jadwal_khutbah')
                ->delete();

            Log::info('EventView deleted for JadwalKhutbah', [
                'jadwal_khutbah_id' => $jadwalKhutbah->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete EventView for JadwalKhutbah', [
                'jadwal_khutbah_id' => $jadwalKhutbah->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}