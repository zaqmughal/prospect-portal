<?php

namespace App\Providers;

use App\Contracts\DiscoveryConnector;
use App\Listeners\SyncOrganizationPlanFromStripe;
use App\Models\Organization;
use App\Services\Discovery\BingSearchConnector;
use App\Services\Discovery\DomainNormaliser;
use App\Services\Discovery\SerpApiSearchConnector;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookReceived;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DiscoveryConnector::class, function ($app) {
            $provider = config('discovery.default_provider', 'serpapi');
            if ($provider === 'bing') {
                return new BingSearchConnector($app->make(DomainNormaliser::class));
            }

            return new SerpApiSearchConnector($app->make(DomainNormaliser::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Cashier::useCustomerModel(Organization::class);

        Event::listen(WebhookReceived::class, SyncOrganizationPlanFromStripe::class);
    }
}
