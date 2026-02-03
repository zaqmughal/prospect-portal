<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_source_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_source_id')->constrained()->cascadeOnDelete();
            $table->string('trigger'); // manual, scheduled
            $table->string('provider')->nullable(); // bing, google_cse
            $table->json('config_snapshot')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->string('status')->default('running'); // running, success, failed, partial
            $table->integer('queries_executed')->default(0);
            $table->integer('throttled_count')->default(0);
            $table->integer('domains_found')->default(0);
            $table->integer('domains_created')->default(0);
            $table->integer('domains_skipped')->default(0);
            $table->integer('domains_blocked')->default(0);
            $table->text('error_message')->nullable();
            $table->json('query_errors')->nullable();
            $table->timestamps();

            $table->index('lead_source_id');
            $table->index('status');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_source_runs');
    }
};
