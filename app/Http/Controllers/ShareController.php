<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    /**
     * Generate preview page for event sharing
     */
    public function generatePreviewPage($slug)
    {
        try {
            $event = Event::with('category')->where('slug', $slug)->first();

            if (!$event) {
                return abort(404, 'Event tidak ditemukan');
            }

            // Return simple preview page
            return view('share.event-preview', compact('event'));
        } catch (\Exception $e) {
            return abort(500, 'Terjadi kesalahan');
        }
    }
}