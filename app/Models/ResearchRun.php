<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ResearchRunStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ResearchRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'status',
        'triggered_by',
        'started_at',
        'completed_at',
        'error_message',
        'total_cost',
        'pages_fetched',
        'signals_found',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => ResearchRunStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'total_cost' => 'decimal:6',
        ];
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return HasMany<AccountSource, $this>
     */
    public function sources(): HasMany
    {
        return $this->hasMany(AccountSource::class);
    }

    /**
     * @return HasMany<SignalEvent, $this>
     */
    public function signalEvents(): HasMany
    {
        return $this->hasMany(SignalEvent::class);
    }

    /**
     * @return HasMany<AiRun, $this>
     */
    public function aiRuns(): HasMany
    {
        return $this->hasMany(AiRun::class);
    }

    /**
     * @return HasOne<Brief, $this>
     */
    public function brief(): HasOne
    {
        return $this->hasOne(Brief::class);
    }

    public function markAsRunning(): void
    {
        $this->update([
            'status' => ResearchRunStatus::Running,
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => ResearchRunStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => ResearchRunStatus::Failed,
            'completed_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }

    public function addCost(float $cost): void
    {
        $this->increment('total_cost', $cost);
    }
}
