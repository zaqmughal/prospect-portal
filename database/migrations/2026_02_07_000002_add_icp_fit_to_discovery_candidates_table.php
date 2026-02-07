<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discovery_candidates', function (Blueprint $table) {
            $table->string('icp_fit', 20)->nullable()->after('position');
            $table->text('icp_fit_reason')->nullable()->after('icp_fit');
        });
    }

    public function down(): void
    {
        Schema::table('discovery_candidates', function (Blueprint $table) {
            $table->dropColumn(['icp_fit', 'icp_fit_reason']);
        });
    }
};
