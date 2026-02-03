<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outreach_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('research_run_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('playbook_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ai_run_id')->nullable()->constrained('ai_runs')->nullOnDelete();
            $table->string('channel');
            $table->text('content');
            $table->string('status')->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('outcome')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'channel', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_assets');
    }
};
