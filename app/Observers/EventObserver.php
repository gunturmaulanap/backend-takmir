<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\EventView;
use Illuminate\Support\Facades\Log;

class EventObserver
{
    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        try {
            // Create EventView record untuk Event baru
            EventView::create([
                'profile_masjid_id' => $event->profile_masjid_id,
                'event_id' => $event->id,
                'jadwal_khutbah_id' => null,
                'title' => $event->nama,
                'tanggal' => $event->tanggal_event,
                'waktu' => $event->waktu_event,
                'type' => 'event',
                'description' => $event->deskripsi,
                'created_by' => $event->created_by,
                'updated_by' => $event->updated_by,
            ]);

            Log::info('EventView created for Event', [
                'event_id' => $event->id,
                'title' => $event->nama
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create EventView for Event', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        try {
            // Update existing EventView record
            $eventView = EventView::where('event_id', $event->id)
                ->where('type', 'event')
                ->first();

            if ($eventView) {
                $eventView->update([
                    'title' => $event->nama,
                    'tanggal' => $event->tanggal_event,
                    'waktu' => $event->waktu_event,
                    'description' => $event->deskripsi,
                    'updated_by' => $event->updated_by,
                ]);

                Log::info('EventView updated for Event', [
                    'event_id' => $event->id,
                    'title' => $event->nama
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update EventView for Event', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        try {
            // Delete corresponding EventView record
            EventView::where('event_id', $event->id)
                ->where('type', 'event')
                ->delete();

            Log::info('EventView deleted for Event', [
                'event_id' => $event->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete EventView for Event', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}