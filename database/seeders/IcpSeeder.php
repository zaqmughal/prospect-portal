<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Icp;
use Illuminate\Database\Seeder;

class IcpSeeder extends Seeder
{
    public function run(): void
    {
        Icp::updateOrCreate(
            ['name' => 'UK SMEs & Organisations'],
            [
                'description' => 'Default ICP targeting UK small-to-medium businesses, education institutions, and charities that need web development and accessibility services.',
                'sectors' => [
                    'Education',
                    'Charity',
                    'Professional Services',
                    'Healthcare',
                    'Legal',
                    'Finance',
                    'Technology',
                ],
                'size_bands' => [
                    '1-10',
                    '11-50',
                    '51-200',
                ],
                'signals' => [
                    'outdated_content' => ['weight' => 8, 'description' => 'Copyright dates more than 1 year old'],
                    'stale_blog' => ['weight' => 6, 'description' => 'No blog posts in 6+ months'],
                    'ux_issues' => ['weight' => 10, 'description' => 'Poor navigation or unclear user journeys'],
                    'tech_debt' => ['weight' => 8, 'description' => 'Outdated technology stack indicators'],
                    'hiring_signals' => ['weight' => 12, 'description' => 'Hiring for digital/web roles'],
                    'accessibility_mentions' => ['weight' => 10, 'description' => 'WCAG or accessibility referenced'],
                ],
                'scoring_weights' => [
                    'icp_fit' => 40,
                    'signal_strength' => 40,
                    'reachability' => 20,
                ],
                'is_default' => true,
            ]
        );
    }
}
