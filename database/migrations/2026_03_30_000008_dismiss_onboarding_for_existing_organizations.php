<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pre-mark every organisation that already exists as having dismissed
     * the onboarding checklist. The new dashboard widget will only appear
     * for organisations created from now onward, so existing users don't
     * suddenly see a "Get started" checklist they don't need.
     */
    public function up(): void
    {
        $organizations = DB::table('organizations')->get(['id', 'settings']);

        foreach ($organizations as $organization) {
            $settings = $this->decodeSettings($organization->settings);
            $settings['onboarding'] = array_merge(
                $settings['onboarding'] ?? [],
                ['dismissed' => true]
            );

            DB::table('organizations')
                ->where('id', $organization->id)
                ->update(['settings' => json_encode($settings)]);
        }
    }

    public function down(): void
    {
        $organizations = DB::table('organizations')->get(['id', 'settings']);

        foreach ($organizations as $organization) {
            $settings = $this->decodeSettings($organization->settings);
            unset($settings['onboarding']);

            $payload = $settings === [] ? null : json_encode($settings);

            DB::table('organizations')
                ->where('id', $organization->id)
                ->update(['settings' => $payload]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeSettings(?string $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
};
