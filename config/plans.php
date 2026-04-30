<?php

return [

    'free' => [
        'label' => 'Free',
        'price_monthly' => 0,
        'stripe_price_id' => null,
        'accounts_limit' => 10,
        'lead_sources_limit' => 1,
        'research_runs_per_month' => 20,
        'ai_daily_spend_limit' => 1.00,
        'team_members_limit' => 1,
        'ai_generated_icps' => false,
        'ai_generated_playbooks' => false,
    ],

    'starter' => [
        'label' => 'Starter',
        'price_monthly' => 29,
        'stripe_price_id' => env('STRIPE_STARTER_PRICE_ID'),
        'accounts_limit' => 100,
        'lead_sources_limit' => 5,
        'research_runs_per_month' => 200,
        'ai_daily_spend_limit' => 10.00,
        'team_members_limit' => 3,
        'ai_generated_icps' => true,
        'ai_generated_playbooks' => true,
    ],

    'pro' => [
        'label' => 'Professional',
        'price_monthly' => 79,
        'stripe_price_id' => env('STRIPE_PRO_PRICE_ID'),
        'accounts_limit' => null,
        'lead_sources_limit' => null,
        'research_runs_per_month' => null,
        'ai_daily_spend_limit' => 50.00,
        'team_members_limit' => 10,
        'ai_generated_icps' => true,
        'ai_generated_playbooks' => true,
    ],

];
