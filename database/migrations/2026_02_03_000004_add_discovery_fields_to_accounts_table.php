<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->foreignId('lead_source_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->timestamp('discovered_at')->nullable()->after('last_researched_at');
            $table->json('discovery_metadata')->nullable()->after('discovered_at');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['lead_source_id']);
            $table->dropColumn(['lead_source_id', 'discovered_at', 'discovery_metadata']);
        });
    }
};
