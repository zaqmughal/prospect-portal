<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SourceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AccountSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'research_run_id',
        'account_id',
        'type',
        'url',
        'fetched_at',
        'html_path',
        'text_path',
        'html_hash',
        'byte_size',
        'status',
        'error_message',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'type' => SourceType::class,
            'fetched_at' => 'datetime',
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

    public function getHtmlContent(): ?string
    {
        if (! $this->html_path) {
            return null;
        }

        return Storage::disk('local')->get($this->html_path);
    }

    public function getTextContent(): ?string
    {
        if (! $this->text_path) {
            return null;
        }

        return Storage::disk('local')->get($this->text_path);
    }
}
