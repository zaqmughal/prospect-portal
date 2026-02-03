<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DiscoveryCandidateStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscoveryCandidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_source_run_id',
        'domain',
        'url',
        'title',
        'snippet',
        'status',
        'reason',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => DiscoveryCandidateStatus::class,
        ];
    }

    /**
     * @return BelongsTo<LeadSourceRun, $this>
     */
    public function leadSourceRun(): BelongsTo
    {
        return $this->belongsTo(LeadSourceRun::class);
    }

    public function approve(): void
    {
        $this->update(['status' => DiscoveryCandidateStatus::Approved]);
    }

    public function reject(): void
    {
        $this->update(['status' => DiscoveryCandidateStatus::Rejected]);
    }
}
