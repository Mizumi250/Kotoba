<?php

// ============================================================
// ARKZEN GENERATED CONTROLLER — DispatchController
// Tatemono: express-test-2
// DO NOT EDIT DIRECTLY. Edit the tatemono file instead.
// Generated: 2026-11-07T08:55:03.775798Z
// ============================================================

namespace App\Http\Controllers\Arkzen\ExpressTest2;

use App\Http\Controllers\Controller;
use App\Models\Arkzen\ExpressTest2\JobResult;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DispatchController extends Controller
{
    /**
         * Dispatch a queued job
         */
        public function store(Request $request): JsonResponse
        {
            $validated = $request->validate([
                'job' => 'required|string'
            ]);
    
            $jobClass = match($validated['job']) {
                'process-data' => \App\Jobs\Arkzen\JobTest\ProcessDataJob::class,
                'heavy-computation' => \App\Jobs\Arkzen\JobTest\HeavyComputationJob::class,
                'always-fails' => \App\Jobs\Arkzen\JobTest\AlwaysFailsJob::class,
                default => throw new \Exception('Unknown job: ' . $validated['job']),
            };
    
            dispatch(new $jobClass());
    
            return response()->json(['message' => 'Job dispatched'], 202);
        }
}
