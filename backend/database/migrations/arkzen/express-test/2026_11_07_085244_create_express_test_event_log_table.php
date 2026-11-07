<?php

// ============================================================
// ARKZEN GENERATED MIGRATION — express_test_event_log
// Connection: express_test
// DO NOT EDIT DIRECTLY. Edit the tatemono file instead.
// Generated: 2026-11-07T08:52:44.494136Z
// ============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'express_test';

    public function up(): void
    {
        Schema::connection('express_test')->create('express_test_event_log', function (Blueprint $table) {
            $table->string('event_name');
            $table->string('listener');
            $table->text('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('express_test')->dropIfExists('express_test_event_log');
    }
};
