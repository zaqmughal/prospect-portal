<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $users = DB::table('users')->get();

        foreach ($users as $user) {
            $slug = Str::slug($user->name ?: 'org-'.$user->id);

            $existing = DB::table('organizations')->where('slug', $slug)->exists();
            if ($existing) {
                $slug = $slug.'-'.$user->id;
            }

            $orgId = DB::table('organizations')->insertGetId([
                'name' => ($user->name ?: 'My Organisation')."'s Organisation",
                'slug' => $slug,
                'owner_id' => $user->id,
                'plan' => 'free',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('organization_user')->insert([
                'organization_id' => $orgId,
                'user_id' => $user->id,
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')
                ->where('id', $user->id)
                ->update(['current_organization_id' => $orgId]);

            DB::table('accounts')
                ->where('user_id', $user->id)
                ->update(['organization_id' => $orgId]);

            DB::table('lead_sources')
                ->where('user_id', $user->id)
                ->update(['organization_id' => $orgId]);
        }

        // Assign orphan ICPs and playbooks to the first organization
        $firstOrg = DB::table('organizations')->orderBy('id')->first();

        if ($firstOrg) {
            DB::table('icps')
                ->whereNull('organization_id')
                ->update(['organization_id' => $firstOrg->id]);

            DB::table('playbooks')
                ->whereNull('organization_id')
                ->update(['organization_id' => $firstOrg->id]);
        }
    }

    public function down(): void
    {
        DB::table('accounts')->update(['organization_id' => null]);
        DB::table('lead_sources')->update(['organization_id' => null]);
        DB::table('icps')->update(['organization_id' => null]);
        DB::table('playbooks')->update(['organization_id' => null]);
        DB::table('users')->update(['current_organization_id' => null]);
        DB::table('organization_user')->truncate();
        DB::table('organizations')->truncate();
    }
};
