<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_runs', function (Blueprint $table) {
            $table->dropForeign(['research_run_id']);
            $table->dropForeign(['account_id']);
        });

        Schema::table('ai_runs', function (Blueprint $table) {
            $table->unsignedBigInteger('research_run_id')->nullable()->change();
            $table->unsignedBigInteger('account_id')->nullable()->change();
        });

        Schema::table('ai_runs', function (Blueprint $table) {
            $table->foreign('research_run_id')->references('id')->on('research_runs')->cascadeOnDelete();
            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ai_runs', function (Blueprint $table) {
            $table->dropForeign(['research_run_id']);
            $table->dropForeign(['account_id']);
        });

        Schema::table('ai_runs', function (Blueprint $table) {
            $table->unsignedBigInteger('research_run_id')->nullable(false)->change();
            $table->unsignedBigInteger('account_id')->nullable(false)->change();
        });

        Schema::table('ai_runs', function (Blueprint $table) {
            $table->foreign('research_run_id')->references('id')->on('research_runs')->cascadeOnDelete();
            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
        });
    }
};
