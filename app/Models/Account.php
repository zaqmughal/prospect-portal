<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PipelineStage;
use App\Enums\ResearchStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'lead_source_id',
        'name',
        'url',
        'domain',
        'sector',
        'size_band',
        'location',
        'notes',
        'pipeline_stage',
        'research_status',
        'lead_score',
        'score_breakdown',
        'last_researched_at',
        'discovered_at',
        'discovery_metadata',
        'research_blocked_reason',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'pipeline_stage' => PipelineStage::class,
            'research_status' => ResearchStatus::class,
            'score_breakdown' => 'array',
            'last_researched_at' => 'datetime',
            'discovered_at' => 'datetime',
            'discovery_metadata' => 'array',
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
     * @return BelongsTo<LeadSource, $this>
     */
    public function leadSource(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class);
    }

    /**
     * @return HasMany<ResearchRun, $this>
     */
    public function researchRuns(): HasMany
    {
        return $this->hasMany(ResearchRun::class);
    }

    /**
     * @return HasOne<ResearchRun, $this>
     */
    public function latestResearchRun(): HasOne
    {
        return $this->hasOne(ResearchRun::class)->latestOfMany();
    }

    /**
     * @return HasMany<OutreachAsset, $this>
     */
    public function outreachAssets(): HasMany
    {
        return $this->hasMany(OutreachAsset::class);
    }

    /**
     * @return HasMany<SignalEvent, $this>
     */
    public function signalEvents(): HasMany
    {
        return $this->hasMany(SignalEvent::class);
    }

    /**
     * @return HasMany<Brief, $this>
     */
    public function briefs(): HasMany
    {
        return $this->hasMany(Brief::class);
    }

    /**
     * @return HasOne<Brief, $this>
     */
    public function latestBrief(): HasOne
    {
        return $this->hasOne(Brief::class)->latestOfMany();
    }

    public static function normalizeDomain(string $url): string
    {
        $parsed = parse_url(strtolower(trim($url)));
        $host = $parsed['host'] ?? $parsed['path'] ?? $url;

        // Remove www. prefix
        $host = preg_replace('/^www\./', '', $host);

        // Remove trailing slashes and paths
        $host = explode('/', $host)[0];

        return $host;
    }
}
