<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventViewResource;
use App\Models\EventView;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Carbon\Carbon;

class EventViewController extends Controller implements HasMiddleware
{
    /**
     * Hanya user dengan izin 'events.index' yang bisa melihat kalender ini.
     */
    public static function middleware(): array
    {
        return [
            new Middleware(['permission:events.index'], only: ['index']),
        ];
    }

    /**
     * Menampilkan daftar event views sebagai kalender.
     * Menerima filter ?month=9&year=2025&type=event|jadwal_khutbah
     */
    public function index(Request $request)
    {
        // Validasi input filter bulan, tahun, dan tipe
        $request->validate([
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2000',
            'type' => 'nullable|in:event,jadwal_khutbah',
        ]);

        // Tentukan bulan dan tahun, jika tidak ada, gunakan bulan dan tahun saat ini
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        $type = $request->input('type');

        $user = $request->user();
        $profileMasjidId = $this->getProfileMasjidId($user, $request);

        if (!$profileMasjidId) {
            return response()->json([
                'success' => false,
                'message' => 'Profile masjid tidak ditemukan.'
            ], 400);
        }

        // Query data EventView dengan relasi
        $query = EventView::with(['event.category', 'jadwalKhutbah.khatib', 'jadwalKhutbah.imam', 'jadwalKhutbah.muadzin'])
            ->where('profile_masjid_id', $profileMasjidId)
            ->byMonth($year, $month);

        // Filter berdasarkan tipe jika ada
        if ($type) {
            $query->byType($type);
        }

        $eventViews = $query->orderBy('tanggal', 'asc')
            ->orderBy('waktu', 'asc')
            ->get();

        // Format data untuk kalender
        $calendarData = $this->formatForCalendar($eventViews, $year, $month);

        return response()->json([
            'success' => true,
            'message' => 'Data kalender berhasil dimuat',
            'data' => [
                'calendar' => $calendarData,
                'summary' => [
                    'year' => $year,
                    'month' => $month,
                    'month_name' => Carbon::createFromDate($year, $month, 1)->format('F Y'),
                    'total_events' => $eventViews->where('type', 'event')->count(),
                    'total_jadwal_khutbah' => $eventViews->where('type', 'jadwal_khutbah')->count(),
                    'total_items' => $eventViews->count(),
                ]
            ]
        ]);
    }

    /**
     * Format data untuk tampilan kalender
     */
    private function formatForCalendar($eventViews, $year, $month)
    {
        $calendar = [];

        foreach ($eventViews as $eventView) {
            $date = $eventView->tanggal->format('Y-m-d');
            $day = $eventView->tanggal->format('d');

            if (!isset($calendar[$date])) {
                $calendar[$date] = [
                    'date' => $date,
                    'day' => (int) $day,
                    'events' => []
                ];
            }

            $calendar[$date]['events'][] = [
                'id' => $eventView->id,
                'title' => $eventView->title,
                'time' => $eventView->waktu ? Carbon::parse($eventView->waktu)->format('H:i') : null,
                'type' => $eventView->type,
                'description' => $eventView->description,
                'related_data' => $this->getRelatedData($eventView)
            ];
        }

        return array_values($calendar);
    }

    /**
     * Mendapatkan data terkait berdasarkan tipe
     */
    private function getRelatedData($eventView)
    {
        if ($eventView->type === 'event' && $eventView->event) {
            return [
                'category' => $eventView->event->category->name ?? null,
                'image' => $eventView->event->image ?? null,
            ];
        }

        if ($eventView->type === 'jadwal_khutbah' && $eventView->jadwalKhutbah) {
            return [
                'khatib' => $eventView->jadwalKhutbah->khatib->nama ?? null,
                'imam' => $eventView->jadwalKhutbah->imam->nama ?? null,
                'muadzin' => $eventView->jadwalKhutbah->muadzin->nama ?? null,
                'tema_khutbah' => $eventView->jadwalKhutbah->tema_khutbah ?? null,
            ];
        }

        return null;
    }

    /**
     * Get profile masjid ID berdasarkan role user
     */
    private function getProfileMasjidId($user, $request)
    {
        if ($user->roles->contains('name', 'superadmin')) {
            return $request->get('profile_masjid_id');
        }

        // Untuk admin dan takmir, gunakan method getMasjidProfile untuk konsistensi
        $profileMasjid = $user->getMasjidProfile();
        return $profileMasjid ? $profileMasjid->id : null;
    }
}
