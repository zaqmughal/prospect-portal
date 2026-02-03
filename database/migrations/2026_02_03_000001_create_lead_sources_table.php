<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type'); // search_query_pack, registry, directory, rss
            $table->json('config')->nullable();
            $table->string('cadence')->default('manual'); // manual, daily, weekly
            $table->string('status')->default('enabled'); // enabled, disabled
            $table->timestamp('last_run_at')->nullable();
            $table->string('last_run_status')->nullable(); // success, failed, partial
            $table->integer('last_run_domains_found')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('cadence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_sources');
    }
};
