<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['accounts', 'lead_sources', 'icps', 'playbooks'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('organization_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('organizations')
                    ->cascadeOnDelete();
            });
        }

        // Change accounts.domain from globally unique to unique per organization
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique(['domain']);
            $table->unique(['domain', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique(['domain', 'organization_id']);
            $table->unique('domain');
        });

        $tables = ['accounts', 'lead_sources', 'icps', 'playbooks'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('organization_id');
            });
        }
    }
};
