<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! $user->current_organization_id) {
            $firstOrg = $user->organizations()->first();

            if ($firstOrg) {
                $user->update(['current_organization_id' => $firstOrg->id]);
                $user->refresh();
            } else {
                if (! $request->routeIs('organizations.create', 'organizations.store', 'logout')) {
                    return redirect()->route('organizations.create');
                }

                return $next($request);
            }
        }

        $organization = $user->currentOrganization;

        if (! $organization || ! $organization->isMember($user)) {
            $user->update(['current_organization_id' => null]);

            return redirect()->route('dashboard');
        }

        View::share('currentOrganization', $organization);

        return $next($request);
    }
}
