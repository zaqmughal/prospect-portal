<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadSourceRunStatus;
use App\Enums\LeadSourceRunTrigger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadSourceRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_source_id',
        'trigger',
        'provider',
        'config_snapshot',
        'started_at',
        'completed_at',
        'status',
        'queries_executed',
        'throttled_count',
        'domains_found',
        'domains_created',
        'domains_skipped',
        'domains_blocked',
        'error_message',
        'query_errors',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'trigger' => LeadSourceRunTrigger::class,
            'status' => LeadSourceRunStatus::class,
            'config_snapshot' => 'array',
            'query_errors' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<LeadSource, $this>
     */
    public function leadSource(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class);
    }

    /**
     * @return HasMany<DiscoveryCandidate, $this>
     */
    public function candidates(): HasMany
    {
        return $this->hasMany(DiscoveryCandidate::class);
    }

    public function markAsSuccess(): void
    {
        $this->update([
            'status' => LeadSourceRunStatus::Success,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => LeadSourceRunStatus::Failed,
            'completed_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }

    public function markAsPartial(): void
    {
        $this->update([
            'status' => LeadSourceRunStatus::Partial,
            'completed_at' => now(),
        ]);
    }
}
