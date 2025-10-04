<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Models\Aparatur;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AktivitasJamaah;
use App\Models\Event;
use App\Models\Takmir;
use App\Models\Jamaah;
use App\Models\Imam;
use App\Models\Muadzin;
use App\Models\Khatib;
use App\Models\Asatidz;
use App\Models\EventView;
use App\Models\User;
use App\Models\ProfileMasjid;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $categories = Category::count();
        $takmirs = Takmir::count();
        $events = Event::count();
        $aparaturs = Aparatur::count();
        $jamaahs = Jamaah::count();
        $imams = Imam::count();
        $muadzins = Muadzin::count();
        $khatibs = Khatib::count();
        $asatidzs = Asatidz::count();
        $users = User::count();
        $profile_masjids = ProfileMasjid::count();
        $aktivitas_jamaahs = AktivitasJamaah::count();
        $event_views = EventView::count();

        return response()->json([
            'success'   => true,
            'message'   => 'List Data on Dashboard',
            'data'      => [
                'categories'        => $categories,
                'takmirs'           => $takmirs,
                'events'            => $events,
                'aparaturs'         => $aparaturs,
                'jamaahs'           => $jamaahs,
                'imams'             => $imams,
                'muadzins'          => $muadzins,
                'khatibs'           => $khatibs,
                'asatidzs'          => $asatidzs,
                'users'             => $users,
                'aktivitas_jamaahs' => $aktivitas_jamaahs,
                'profile_masjids'   => $profile_masjids,
                'event_views'        => $event_views
            ]
        ]);
    }
}
