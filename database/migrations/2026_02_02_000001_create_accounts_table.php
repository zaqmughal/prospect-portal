<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('url');
            $table->string('domain')->unique();
            $table->string('sector')->nullable();
            $table->string('size_band')->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->string('pipeline_stage')->default('new');
            $table->string('research_status')->default('pending');
            $table->integer('lead_score')->default(0);
            $table->json('score_breakdown')->nullable();
            $table->timestamp('last_researched_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pipeline_stage', 'lead_score']);
            $table->index('research_status');
            $table->index('sector');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
