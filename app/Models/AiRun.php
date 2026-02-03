<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ValidationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'research_run_id',
        'account_id',
        'run_type',
        'model',
        'prompt_version',
        'inputs',
        'outputs',
        'validation_status',
        'validation_errors',
        'tokens_input',
        'tokens_output',
        'cost_estimate',
        'duration_ms',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'inputs' => 'array',
            'outputs' => 'array',
            'validation_status' => ValidationStatus::class,
            'cost_estimate' => 'decimal:6',
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

    public function getTotalTokens(): int
    {
        return $this->tokens_input + $this->tokens_output;
    }
}
