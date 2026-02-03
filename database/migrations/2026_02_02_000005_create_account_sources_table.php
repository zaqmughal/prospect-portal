<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('url');
            $table->timestamp('fetched_at')->nullable();
            $table->string('html_path')->nullable();
            $table->string('text_path')->nullable();
            $table->string('html_hash', 64)->nullable();
            $table->integer('byte_size')->default(0);
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['research_run_id', 'type']);
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_sources');
    }
};
