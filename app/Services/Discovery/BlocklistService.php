<?php

declare(strict_types=1);

namespace App\Services\Discovery;

use Illuminate\Support\Facades\Config;

/**
 * Blocklist: exact domain + suffix (*.domain.com) matching for MVP.
 */
class BlocklistService
{
    /** @var array<int, string>|null */
    private ?array $exact = null;

    /** @var array<int, string>|null */
    private ?array $suffixes = null;

    private function load(): void
    {
        if ($this->exact !== null) {
            return;
        }

        $domains = Config::get('discovery.blocklist_domains', []);
        $this->exact = [];
        $this->suffixes = [];

        foreach ($domains as $entry) {
            $entry = strtolower(trim((string) $entry));
            if ($entry === '') {
                continue;
            }
            if (str_starts_with($entry, '*.')) {
                $this->suffixes[] = substr($entry, 2);
            } else {
                $this->exact[] = $entry;
            }
        }
    }

    /**
     * Check if domain is blocklisted (exact or suffix match).
     */
    public function isBlocked(string $domain): bool
    {
        $this->load();
        $domain = strtolower(trim($domain));

        if (in_array($domain, $this->exact, true)) {
            return true;
        }

        foreach ($this->suffixes as $suffix) {
            if ($domain === $suffix || str_ends_with('.'.$domain, '.'.$suffix)) {
                return true;
            }
        }

        return false;
    }
}
