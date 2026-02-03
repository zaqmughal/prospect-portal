<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signal_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('severity');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('evidence_url')->nullable();
            $table->text('evidence_snippet')->nullable();
            $table->integer('score_impact')->default(0);
            $table->timestamp('detected_at');
            $table->timestamps();

            $table->index(['account_id', 'severity']);
            $table->index('research_run_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signal_events');
    }
};
