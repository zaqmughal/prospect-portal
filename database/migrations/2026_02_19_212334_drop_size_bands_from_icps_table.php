<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('icps', function (Blueprint $table) {
            $table->dropColumn('size_bands');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('icps', function (Blueprint $table) {
            $table->json('size_bands')->nullable()->after('sectors');
        });
    }
};
