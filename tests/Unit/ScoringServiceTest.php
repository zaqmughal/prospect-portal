<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Account;
use App\Models\Icp;
use App\Models\User;
use App\Services\ScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoringServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_icp_fit_breakdown_does_not_include_size_band_or_size_match(): void
    {
        $user = User::factory()->create();
        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'Test Co',
            'url' => 'https://example.com',
            'domain' => 'example.com',
            'sector' => 'Education',
            'location' => 'London, UK',
        ]);

        Icp::create([
            'name' => 'Test ICP',
            'sectors' => ['Education'],
            'signals' => [],
            'is_default' => true,
        ]);

        $service = new ScoringService;
        $result = $service->calculate($account);

        $this->assertArrayHasKey('icp_fit', $result['breakdown']);
        $factors = $result['breakdown']['icp_fit']['factors'];
        $this->assertArrayNotHasKey('size_band', $factors);
        $this->assertArrayNotHasKey('size_match', $factors);
        $this->assertArrayHasKey('sector', $factors);
        $this->assertArrayHasKey('location', $factors);
    }

    public function test_icp_fit_max_is_25(): void
    {
        $account = new Account;
        $account->sector = null;
        $account->location = null;

        $result = (new ScoringService)->calculate($account, null);

        $this->assertSame(25, $result['breakdown']['icp_fit']['max']);
    }

    public function test_sector_match_slash_separated(): void
    {
        $user = User::factory()->create();
        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'Test Co',
            'url' => 'https://example.com',
            'domain' => 'example.com',
            'sector' => 'Charity / Non-profit',
            'location' => 'London, UK',
        ]);

        Icp::create([
            'name' => 'Test ICP',
            'sectors' => ['Charity'],
            'signals' => [],
            'is_default' => true,
        ]);

        $result = (new ScoringService)->calculate($account);

        $this->assertTrue($result['breakdown']['icp_fit']['factors']['sector_match']);
        $this->assertSame(25, $result['breakdown']['icp_fit']['score']);
    }

    public function test_sector_match_exact(): void
    {
        $user = User::factory()->create();
        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'Test Co',
            'url' => 'https://example.com',
            'domain' => 'example.com',
            'sector' => 'Education',
            'location' => 'London, UK',
        ]);

        Icp::create([
            'name' => 'Test ICP',
            'sectors' => ['Education'],
            'signals' => [],
            'is_default' => true,
        ]);

        $result = (new ScoringService)->calculate($account);

        $this->assertTrue($result['breakdown']['icp_fit']['factors']['sector_match']);
        $this->assertSame(25, $result['breakdown']['icp_fit']['score']);
    }

    public function test_sector_match_intelligent_prefix(): void
    {
        $user = User::factory()->create();
        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'Test Co',
            'url' => 'https://example.com',
            'domain' => 'example.com',
            'sector' => 'Charitable Trust',
            'location' => 'London, UK',
        ]);

        $icp = Icp::create([
            'name' => 'Test ICP',
            'sectors' => ['Charity'],
            'signals' => [],
            'is_default' => true,
        ]);

        $result = (new ScoringService)->calculate($account, $icp);

        $this->assertTrue($result['breakdown']['icp_fit']['factors']['sector_match']);
        $this->assertSame(25, $result['breakdown']['icp_fit']['score']);
    }

    public function test_sector_no_match(): void
    {
        $user = User::factory()->create();
        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'Test Co',
            'url' => 'https://example.com',
            'domain' => 'example.com',
            'sector' => 'Other',
            'location' => 'London, UK',
        ]);

        Icp::create([
            'name' => 'Test ICP',
            'sectors' => ['Charity'],
            'signals' => [],
            'is_default' => true,
        ]);

        $result = (new ScoringService)->calculate($account);

        $this->assertFalse($result['breakdown']['icp_fit']['factors']['sector_match']);
        $this->assertSame(10, $result['breakdown']['icp_fit']['score']);
    }
}
