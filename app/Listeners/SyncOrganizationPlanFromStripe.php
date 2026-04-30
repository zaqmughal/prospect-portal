<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Organization;
use Laravel\Cashier\Events\WebhookReceived;

class SyncOrganizationPlanFromStripe
{
    public function handle(WebhookReceived $event): void
    {
        $type = $event->payload['type'] ?? '';

        if (! in_array($type, [
            'customer.subscription.created',
            'customer.subscription.updated',
            'customer.subscription.deleted',
        ])) {
            return;
        }

        $stripeCustomerId = $event->payload['data']['object']['customer'] ?? null;

        if (! $stripeCustomerId) {
            return;
        }

        $organization = Organization::where('stripe_id', $stripeCustomerId)->first();

        if (! $organization) {
            return;
        }

        $status = $event->payload['data']['object']['status'] ?? null;
        $stripePriceId = $event->payload['data']['object']['items']['data'][0]['price']['id'] ?? null;

        if ($type === 'customer.subscription.deleted' || $status === 'canceled') {
            $organization->update(['plan' => 'free']);

            return;
        }

        if (! in_array($status, ['active', 'trialing'])) {
            return;
        }

        $plans = config('plans', []);
        $matchedPlan = 'free';

        foreach ($plans as $planKey => $planConfig) {
            if (isset($planConfig['stripe_price_id']) && $planConfig['stripe_price_id'] === $stripePriceId) {
                $matchedPlan = $planKey;
                break;
            }
        }

        $organization->update(['plan' => $matchedPlan]);
    }
}
