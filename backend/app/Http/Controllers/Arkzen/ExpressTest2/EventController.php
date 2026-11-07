<?php

// ============================================================
// ARKZEN GENERATED CONTROLLER — EventController
// Tatemono: express-test-2
// DO NOT EDIT DIRECTLY. Edit the tatemono file instead.
// Generated: 2026-11-07T08:55:03.776279Z
// ============================================================

namespace App\Http\Controllers\Arkzen\ExpressTest2;

use App\Http\Controllers\Controller;
use App\Models\Arkzen\ExpressTest2\EventLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
         * Fire an event
         */
        public function store(Request $request): JsonResponse
        {
            $validated = $request->validate([
                'event' => 'required|string',
                'payload' => 'sometimes|array'
            ]);
    
            $eventClass = match($validated['event']) {
                'user-signed-up' => \App\Events\Arkzen\EventsTest\UserSignedUp::class,
                'order-placed' => \App\Events\Arkzen\EventsTest\OrderPlaced::class,
                'data-exported' => \App\Events\Arkzen\EventsTest\DataExported::class,
                default => throw new \Exception('Unknown event: ' . $validated['event']),
            };
    
            event(new $eventClass($validated['payload'] ?? []));
    
            return response()->json(['message' => 'Event fired'], 202);
        }
}
