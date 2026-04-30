<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadSourceCadence;
use App\Enums\LeadSourceStatus;
use App\Enums\LeadSourceType;
use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LeadSource extends Model
{
    use BelongsToOrganization;
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'user_id',
        'name',
        'type',
        'config',
        'cadence',
        'status',
        'last_run_at',
        'last_run_status',
        'last_run_domains_found',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'type' => LeadSourceType::class,
            'cadence' => LeadSourceCadence::class,
            'status' => LeadSourceStatus::class,
            'config' => 'array',
            'last_run_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<LeadSourceRun, $this>
     */
    public function runs(): HasMany
    {
        return $this->hasMany(LeadSourceRun::class);
    }

    /**
     * @return HasMany<Account, $this>
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * @return HasOne<LeadSourceRun, $this>
     */
    public function latestRun(): HasOne
    {
        return $this->hasOne(LeadSourceRun::class)->latestOfMany();
    }
}
