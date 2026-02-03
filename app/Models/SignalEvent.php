<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SignalSeverity;
use App\Enums\SignalType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignalEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'research_run_id',
        'account_id',
        'type',
        'severity',
        'title',
        'description',
        'evidence_url',
        'evidence_snippet',
        'score_impact',
        'detected_at',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'type' => SignalType::class,
            'severity' => SignalSeverity::class,
            'detected_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ResearchRun, $this>
     */
    public function researchRun(): BelongsTo
    {
        return $this->belongsTo(ResearchRun::class);
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
