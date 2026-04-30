<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Icp;
use Illuminate\Database\Seeder;

class IcpSeeder extends Seeder
{
    public function run(): void
    {
        // Hare & Tortoise target audience (from marketing site: mission-driven orgs, SMEs, education, healthcare, social impact, B2B SaaS)
        Icp::updateOrCreate(
            ['name' => 'Hare & Tortoise target audience'],
            [
                'description' => 'ICP aligned with Hare & Tortoise marketing: founders, SME leaders and decision-makers at mission-driven organisations who need a professional digital product but cannot justify 6-figure agency budgets or 6-month timelines. Budget-conscious, quality-aware, value accessibility and security.',
                'sectors' => [
                    'Education',
                    'Healthcare',
                    'Charity',
                    'Social Impact',
                    'B2B SaaS',
                    'Professional Services',
                    'Alternative Education',
                    'Legal',
                    'Technology',
                ],
                'signals' => [
                    'content' => ['weight' => 10, 'description' => 'Content & freshness: outdated copyright, stale blog, placeholder content — indicates need for refresh'],
                    'ux' => ['weight' => 10, 'description' => 'UX & journey clarity: competing CTAs, unclear purpose, fragmented messaging — opportunity for improvement'],
                    'tech' => ['weight' => 9, 'description' => 'Technology & platform health: legacy stack, incomplete migrations — fits legacy modernisation offer'],
                    'opportunity' => ['weight' => 12, 'description' => 'Organisational & timing: hiring digital roles, accessibility/WCAG mentions, redesign intent — strong fit'],
                ],
                'scoring_weights' => [
                    'icp_fit' => 40,
                    'signal_strength' => 40,
                    'reachability' => 20,
                ],
                'is_default' => true,
            ]
        );

        // Legacy ICP: UK SMEs & Organisations (no longer default)
        Icp::updateOrCreate(
            ['name' => 'UK SMEs & Organisations'],
            [
                'description' => 'UK small-to-medium businesses, education institutions, and charities that need web development and accessibility services.',
                'sectors' => [
                    'Education',
                    'Charity',
                    'Professional Services',
                    'Healthcare',
                    'Legal',
                    'Finance',
                    'Technology',
                ],
                'signals' => [
                    'content' => ['weight' => 8, 'description' => 'Copyright dates more than 1 year old, stale content'],
                    'ux' => ['weight' => 10, 'description' => 'Poor navigation or unclear user journeys'],
                    'tech' => ['weight' => 8, 'description' => 'Outdated technology stack indicators'],
                    'opportunity' => ['weight' => 12, 'description' => 'Hiring for digital/web roles, WCAG or accessibility referenced'],
                ],
                'scoring_weights' => [
                    'icp_fit' => 40,
                    'signal_strength' => 40,
                    'reachability' => 20,
                ],
                'is_default' => false,
            ]
        );
    }
}
