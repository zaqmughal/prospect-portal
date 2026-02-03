<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Brief extends Model
{
    use HasFactory;

    protected $fillable = [
        'research_run_id',
        'account_id',
        'ai_run_id',
        'version',
        'content_md',
        'facts',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'facts' => 'array',
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

    /**
     * @return BelongsTo<AiRun, $this>
     */
    public function aiRun(): BelongsTo
    {
        return $this->belongsTo(AiRun::class);
    }
}
