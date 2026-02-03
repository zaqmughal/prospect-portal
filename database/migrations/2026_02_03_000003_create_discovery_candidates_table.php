<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discovery_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_source_run_id')->constrained()->cascadeOnDelete();
            $table->string('domain');
            $table->string('url');
            $table->string('title')->nullable();
            $table->text('snippet')->nullable();
            $table->string('status'); // new, approved, rejected, blocked, duplicate, unreachable
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index('lead_source_run_id');
            $table->index('domain');
            $table->unique(['lead_source_run_id', 'domain']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discovery_candidates');
    }
};
