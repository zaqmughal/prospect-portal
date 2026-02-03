<?php

declare(strict_types=1);

namespace App\Services\Crawler;

use App\Models\Account;
use App\Models\ResearchRun;
use Illuminate\Support\Facades\Storage;

class SnapshotStorage
{
    private const STORAGE_PATH = 'snapshots';

    /**
     * Store HTML and text content for a research run.
     *
     * @return array{html_path: string, text_path: string}
     */
    public function store(
        Account $account,
        ResearchRun $researchRun,
        string $type,
        string $html,
        string $text
    ): array {
        $basePath = $this->getBasePath($account, $researchRun);

        $htmlPath = "{$basePath}/{$type}.html";
        $textPath = "{$basePath}/{$type}.txt";

        Storage::disk('local')->put($htmlPath, $html);
        Storage::disk('local')->put($textPath, $text);

        return [
            'html_path' => $htmlPath,
            'text_path' => $textPath,
        ];
    }

    /**
     * Get HTML content for a source.
     */
    public function getHtml(string $path): ?string
    {
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->get($path);
        }

        return null;
    }

    /**
     * Get text content for a source.
     */
    public function getText(string $path): ?string
    {
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->get($path);
        }

        return null;
    }

    /**
     * Delete all snapshots for a research run.
     */
    public function deleteForRun(Account $account, ResearchRun $researchRun): bool
    {
        $basePath = $this->getBasePath($account, $researchRun);

        return Storage::disk('local')->deleteDirectory($basePath);
    }

    /**
     * Delete all snapshots for an account.
     */
    public function deleteForAccount(Account $account): bool
    {
        $basePath = self::STORAGE_PATH."/{$account->id}";

        return Storage::disk('local')->deleteDirectory($basePath);
    }

    /**
     * Get the storage size for an account in bytes.
     */
    public function getAccountStorageSize(Account $account): int
    {
        $basePath = self::STORAGE_PATH."/{$account->id}";
        $size = 0;

        $files = Storage::disk('local')->allFiles($basePath);
        foreach ($files as $file) {
            $size += Storage::disk('local')->size($file);
        }

        return $size;
    }

    private function getBasePath(Account $account, ResearchRun $researchRun): string
    {
        return self::STORAGE_PATH."/{$account->id}/{$researchRun->id}";
    }
}
