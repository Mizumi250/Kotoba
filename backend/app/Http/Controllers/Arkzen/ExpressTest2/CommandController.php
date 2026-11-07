<?php

// ============================================================
// ARKZEN GENERATED CONTROLLER — CommandController
// Tatemono: express-test-2
// DO NOT EDIT DIRECTLY. Edit the tatemono file instead.
// Generated: 2026-11-07T08:55:03.775306Z
// ============================================================

namespace App\Http\Controllers\Arkzen\ExpressTest2;

use App\Http\Controllers\Controller;
use App\Models\Arkzen\ExpressTest2\CommandRun;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class CommandController extends Controller
{
    /**
     * List command runs
     */
    public function index(Request $request): JsonResponse
    {
        $query = CommandRun::query();


        $perPage = $request->get('per_page', 15);
        $results = $query->paginate($perPage);

        return response()->json($results);
    }

    /**
     * Run a command
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'command' => 'required|string',
            'triggered_by' => 'sometimes|string'
        ]);

        $commandName = $validated['command'];
        $triggeredBy = $validated['triggered_by'] ?? 'manual';

        // Map command name to signature (extracted from tatemono)
        $signatures = [
            'cleanup-temp' => 'scheduler-test:cleanup-temp',
            'generate-report' => 'scheduler-test:generate-report',
            'ping-health' => 'scheduler-test:ping-health',
            'sync-data' => 'scheduler-test:sync-data',
        ];

        $signature = $signatures[$commandName] ?? null;
        if (!$signature) {
            return response()->json(['message' => 'Invalid command'], 400);
        }

        $start = microtime(true);
        
        try {
            $exitCode = \Artisan::call($signature);
            $output = \Artisan::output();
        } catch (\Exception $e) {
            $exitCode = 1;
            $output = $e->getMessage();
        }

        $durationMs = (int) ((microtime(true) - $start) * 1000);

        $commandRun = CommandRun::create([
            'command_name' => $commandName,
            'signature' => $signature,
            'exit_code' => $exitCode,
            'output' => $output,
            'triggered_by' => $triggeredBy,
            'duration_ms' => $durationMs,
        ]);

        return response()->json($commandRun, 201);
    }
}
