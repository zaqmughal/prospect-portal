<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('briefs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_run_id')->nullable()->constrained('ai_runs')->nullOnDelete();
            $table->integer('version')->default(1);
            $table->longText('content_md');
            $table->json('facts')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('briefs');
    }
};
