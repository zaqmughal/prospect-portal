<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Icp extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sectors',
        'size_bands',
        'signals',
        'scoring_weights',
        'is_default',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'sectors' => 'array',
            'size_bands' => 'array',
            'signals' => 'array',
            'scoring_weights' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public static function getDefault(): ?self
    {
        return self::where('is_default', true)->first();
    }
}
