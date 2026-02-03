<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OutreachChannel;
use App\Enums\OutreachStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutreachAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'research_run_id',
        'playbook_id',
        'ai_run_id',
        'channel',
        'content',
        'status',
        'sent_at',
        'notes',
        'outcome',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'channel' => OutreachChannel::class,
            'status' => OutreachStatus::class,
            'sent_at' => 'datetime',
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
     * @return BelongsTo<ResearchRun, $this>
     */
    public function researchRun(): BelongsTo
    {
        return $this->belongsTo(ResearchRun::class);
    }

    /**
     * @return BelongsTo<Playbook, $this>
     */
    public function playbook(): BelongsTo
    {
        return $this->belongsTo(Playbook::class);
    }

    /**
     * @return BelongsTo<AiRun, $this>
     */
    public function aiRun(): BelongsTo
    {
        return $this->belongsTo(AiRun::class);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => OutreachStatus::Sent,
            'sent_at' => now(),
        ]);
    }
}
