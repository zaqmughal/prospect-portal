<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->string('triggered_by')->default('manual');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->decimal('total_cost', 10, 6)->default(0);
            $table->integer('pages_fetched')->default(0);
            $table->integer('signals_found')->default(0);
            $table->timestamps();

            $table->index(['account_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_runs');
    }
};
