<?php

declare(strict_types=1);

namespace App\Livewire\Billing;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class ManageSubscription extends Component
{
    public function checkout(string $plan): void
    {
        $user = Auth::user();
        $organization = $user->currentOrganization;

        if (! $organization || ! $organization->isOwner($user)) {
            abort(403, 'Only the organisation owner can manage billing.');
        }

        $planConfig = config("plans.{$plan}");

        if (! $planConfig || ! $planConfig['stripe_price_id']) {
            session()->flash('error', 'Invalid plan selected.');

            return;
        }

        // NOTE: We deliberately don't call Cashier's ->redirect() here. Inside
        // a Livewire request the `redirect` container binding is swapped for
        // Livewire's Redirector, which violates Cashier's RedirectResponse
        // return type. We grab the Stripe Checkout Session URL and let
        // Livewire perform a full-page browser redirect instead.
        $checkout = $organization
            ->newSubscription('default', $planConfig['stripe_price_id'])
            ->checkout([
                'success_url' => route('settings.billing').'?checkout=success',
                'cancel_url' => route('settings.billing').'?checkout=cancelled',
            ]);

        $this->redirect($checkout->url);
    }

    public function redirectToBillingPortal(): void
    {
        $user = Auth::user();
        $organization = $user->currentOrganization;

        if (! $organization || ! $organization->isOwner($user)) {
            abort(403);
        }

        $this->redirect(
            $organization->billingPortalUrl(route('settings.billing'))
        );
    }

    public function render(): View
    {
        $user = Auth::user();
        $organization = $user->currentOrganization;
        $currentPlan = $organization?->plan ?? 'free';
        $plans = config('plans');
        $isOwner = $organization?->isOwner($user) ?? false;

        $subscription = $organization?->subscription('default');
        $onTrial = $organization?->trial_ends_at && $organization->trial_ends_at->isFuture();

        return view('livewire.billing.manage-subscription', [
            'currentPlan' => $currentPlan,
            'plans' => $plans,
            'isOwner' => $isOwner,
            'subscription' => $subscription,
            'onTrial' => $onTrial,
            'hasStripeId' => (bool) $organization?->stripe_id,
        ]);
    }
}
