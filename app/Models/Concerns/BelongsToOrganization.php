<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait for models that are scoped to an organization.
 *
 * Automatically filters queries by the current user's organization
 * and sets organization_id when creating new records.
 */
trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope('organization', function (Builder $query): void {
            if (auth()->check() && auth()->user()->current_organization_id) {
                $query->where(
                    $query->getModel()->getTable().'.organization_id',
                    auth()->user()->current_organization_id
                );
            }
        });

        static::creating(function (Model $model): void {
            if (
                ! $model->organization_id
                && auth()->check()
                && auth()->user()->current_organization_id
            ) {
                $model->organization_id = auth()->user()->current_organization_id;
            }
        });
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
