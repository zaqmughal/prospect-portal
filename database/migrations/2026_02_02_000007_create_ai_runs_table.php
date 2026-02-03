<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('run_type');
            $table->string('model');
            $table->string('prompt_version')->default('v1');
            $table->json('inputs')->nullable();
            $table->json('outputs')->nullable();
            $table->string('validation_status')->default('pending');
            $table->text('validation_errors')->nullable();
            $table->integer('tokens_input')->default(0);
            $table->integer('tokens_output')->default(0);
            $table->decimal('cost_estimate', 10, 6)->default(0);
            $table->integer('duration_ms')->nullable();
            $table->timestamps();

            $table->index('research_run_id');
            $table->index(['account_id', 'run_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_runs');
    }
};
