<?php

declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Support\Facades\File;

class PromptLoader
{
    private const PROMPTS_PATH = 'resources/prompts';

    /**
     * Load a prompt template.
     */
    public function load(string $type, string $version = 'v1'): string
    {
        $path = base_path(self::PROMPTS_PATH."/{$type}/{$version}.txt");

        if (! File::exists($path)) {
            throw new \RuntimeException("Prompt not found: {$type}/{$version}");
        }

        return File::get($path);
    }

    /**
     * Load a prompt template with variable substitution.
     *
     * @param  array<string, string>  $variables
     */
    public function loadWithVariables(string $type, array $variables, string $version = 'v1'): string
    {
        $template = $this->load($type, $version);

        foreach ($variables as $key => $value) {
            $template = str_replace("{{$key}}", $value, $template);
        }

        return $template;
    }

    /**
     * Get the JSON schema for a prompt type.
     *
     * @return array<string, mixed>
     */
    public function getSchema(string $type): array
    {
        $path = base_path(self::PROMPTS_PATH."/{$type}/schemas/output.json");

        if (! File::exists($path)) {
            throw new \RuntimeException("Schema not found: {$type}");
        }

        $content = File::get($path);
        $schema = json_decode($content, true);

        if (! is_array($schema)) {
            throw new \RuntimeException("Invalid schema: {$type}");
        }

        return $schema;
    }

    /**
     * Get available versions for a prompt type.
     *
     * @return array<string>
     */
    public function getVersions(string $type): array
    {
        $path = base_path(self::PROMPTS_PATH."/{$type}");

        if (! File::isDirectory($path)) {
            return [];
        }

        $files = File::files($path);
        $versions = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'txt') {
                $versions[] = $file->getFilenameWithoutExtension();
            }
        }

        sort($versions);

        return $versions;
    }
}
