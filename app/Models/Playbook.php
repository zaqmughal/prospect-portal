<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Playbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'angle',
        'dm_template',
        'email_template',
        'constraints',
        'is_active',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'constraints' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<OutreachAsset, $this>
     */
    public function outreachAssets(): HasMany
    {
        return $this->hasMany(OutreachAsset::class);
    }
}
