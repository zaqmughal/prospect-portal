<?php

declare(strict_types=1);

namespace App\Services\AI;

use JsonSchema\Validator;

class OutputValidator
{
    private PromptLoader $promptLoader;

    public function __construct(PromptLoader $promptLoader)
    {
        $this->promptLoader = $promptLoader;
    }

    /**
     * Normalize AI output to match the expected schema (handles common key/format mismatches).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalizeForValidation(string $runType, array $data): array
    {
        return match ($runType) {
            'extractor' => $this->normalizeExtractorOutput($data),
            'signal_detector' => $this->normalizeSignalDetectorOutput($data),
            'brief_generator' => $this->normalizeBriefGeneratorOutput($data),
            'outreach_writer' => $this->normalizeOutreachWriterOutput($data),
            default => $data,
        };
    }

    /**
     * Validate AI output against its schema.
     *
     * @param  array<string, mixed>  $data
     * @return array{valid: bool, errors: array<string>}
     */
    public function validate(string $runType, array $data): array
    {
        try {
            $schema = $this->promptLoader->getSchema($runType);
        } catch (\RuntimeException $e) {
            return [
                'valid' => false,
                'errors' => ["Schema not found for run type: {$runType}"],
            ];
        }

        $validator = new Validator;
        $dataObject = json_decode(json_encode($data) ?: '{}');

        $validator->validate($dataObject, $schema);

        if ($validator->isValid()) {
            return [
                'valid' => true,
                'errors' => [],
            ];
        }

        $errors = [];
        foreach ($validator->getErrors() as $error) {
            $property = $error['property'] ?? 'root';
            $message = $error['message'] ?? 'Unknown error';
            $errors[] = "[{$property}] {$message}";
        }

        return [
            'valid' => false,
            'errors' => $errors,
        ];
    }

    /**
     * Validate and sanitise extracted company data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function sanitiseExtractorOutput(array $data): array
    {
        return [
            'company_name' => $this->sanitiseString($data['company_name'] ?? null),
            'sector' => $this->sanitiseString($data['sector'] ?? null),
            'size_band' => $this->validateSizeBand($data['size_band'] ?? null),
            'services' => $this->sanitiseStringArray($data['services'] ?? []),
            'technologies' => $this->sanitiseStringArray($data['technologies'] ?? []),
            'location' => $this->sanitiseString($data['location'] ?? null),
            'copyright_year' => $this->sanitiseYear($data['copyright_year'] ?? null),
        ];
    }

    /**
     * Validate and sanitise signal detector output.
     *
     * @param  array<string, mixed>  $data
     * @return array<array<string, mixed>>
     */
    public function sanitiseSignalOutput(array $data): array
    {
        $signals = $data['signals'] ?? [];
        $validSignals = [];

        foreach ($signals as $signal) {
            if (! is_array($signal)) {
                continue;
            }

            $type = $signal['type'] ?? '';
            $severity = $signal['severity'] ?? '';

            if (! in_array($type, ['content', 'ux', 'tech', 'opportunity'], true)) {
                continue;
            }

            if (! in_array($severity, ['high', 'medium', 'low'], true)) {
                continue;
            }

            $validSignals[] = [
                'type' => $type,
                'severity' => $severity,
                'title' => $this->truncateString($signal['title'] ?? 'Unknown Signal', 100),
                'description' => $this->truncateString($signal['description'] ?? '', 500),
                'evidence' => $this->truncateString($signal['evidence'] ?? '', 300),
            ];
        }

        return $validSignals;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeExtractorOutput(array $data): array
    {
        $copyrightYear = $data['copyright_year'] ?? null;
        if (is_string($copyrightYear) && is_numeric(trim($copyrightYear))) {
            $copyrightYear = (int) trim($copyrightYear);
        }

        return [
            'company_name' => $data['company_name'] ?? null,
            'sector' => $data['sector'] ?? $data['primary_sector'] ?? null,
            'size_band' => $data['size_band'] ?? null,
            'services' => $data['services'] ?? $data['key_services'] ?? [],
            'technologies' => $data['technologies'] ?? $data['technology_mentions'] ?? [],
            'location' => $data['location'] ?? null,
            'copyright_year' => $copyrightYear,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeSignalDetectorOutput(array $data): array
    {
        $signals = $data['signals'] ?? [];
        if (! is_array($signals)) {
            return ['signals' => []];
        }

        $typeMap = [
            'content signal' => 'content',
            'ux signal' => 'ux',
            'tech signal' => 'tech',
            'opportunity signal' => 'opportunity',
        ];

        $normalized = [];
        foreach ($signals as $signal) {
            if (! is_array($signal)) {
                continue;
            }
            $type = $signal['type'] ?? '';
            $typeLower = is_string($type) ? strtolower(trim($type)) : '';
            $mappedType = $typeMap[$typeLower] ?? null;
            if ($mappedType === null && in_array($typeLower, ['content', 'ux', 'tech', 'opportunity'], true)) {
                $mappedType = $typeLower;
            }
            if ($mappedType === null) {
                continue;
            }
            $description = $this->truncateString($signal['description'] ?? '', 500);
            $severity = is_string($signal['severity'] ?? '') ? strtolower(trim($signal['severity'])) : 'medium';
            if (! in_array($severity, ['high', 'medium', 'low'], true)) {
                $severity = 'medium';
            }
            $normalized[] = [
                'type' => $mappedType,
                'severity' => $severity,
                'title' => $this->truncateString($signal['title'] ?? $description ?: 'Unknown Signal', 100),
                'description' => $description,
                'evidence' => $this->truncateString($signal['evidence'] ?? '', 300),
            ];
        }

        return ['signals' => $normalized];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeBriefGeneratorOutput(array $data): array
    {
        $briefMarkdown = $data['brief_markdown'] ?? $data['CompanyOverview'] ?? '';
        $recommendedAngle = $data['recommended_angle'] ?? $data['RecommendedApproach'] ?? '';
        $talkingPoints = $data['talking_points'] ?? $data['TalkingPoints'] ?? [];
        if (! is_array($talkingPoints)) {
            $talkingPoints = [];
        }
        while (count($talkingPoints) < 3) {
            $talkingPoints[] = '';
        }
        $talkingPoints = array_slice(array_values($talkingPoints), 0, 5);

        $facts = $data['facts'] ?? [];
        if (! is_array($facts)) {
            $facts = [];
        }
        if (empty($facts) && isset($data['KeyFacts']) && is_array($data['KeyFacts'])) {
            foreach ($data['KeyFacts'] as $label => $value) {
                $facts[] = [
                    'label' => is_string($label) ? $label : 'Item',
                    'value' => is_string($value) ? $value : (string) $value,
                ];
            }
        }

        return [
            'brief_markdown' => is_string($briefMarkdown) ? $briefMarkdown : '',
            'facts' => $facts,
            'recommended_angle' => is_string($recommendedAngle) ? $recommendedAngle : '',
            'talking_points' => $talkingPoints,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeOutreachWriterOutput(array $data): array
    {
        $email = $data['email'] ?? $data['Email'] ?? [];
        $email = is_array($email) ? $email : ['subject' => '', 'body' => ''];

        $linkedinDm = $data['linkedin_dm'] ?? $data['LinkedIn_DM'] ?? '';
        $linkedinDm = is_string($linkedinDm) ? $this->truncateString($linkedinDm, 300) : '';

        return [
            'linkedin_dm' => $linkedinDm,
            'email' => [
                'subject' => $email['subject'] ?? '',
                'body' => $email['body'] ?? '',
            ],
        ];
    }

    private function sanitiseString(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim(strip_tags($value));
    }

    /**
     * @param  array<mixed>  $array
     * @return array<string>
     */
    private function sanitiseStringArray(array $array): array
    {
        return array_values(array_filter(
            array_map(fn ($item) => is_string($item) ? trim(strip_tags($item)) : null, $array)
        ));
    }

    private function validateSizeBand(?string $value): ?string
    {
        $validBands = ['1-10', '11-50', '51-200', '201-500', '500+'];

        if ($value !== null && in_array($value, $validBands, true)) {
            return $value;
        }

        return null;
    }

    private function sanitiseYear(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $year = is_numeric($value) ? (int) $value : null;

        if ($year !== null && $year >= 1990 && $year <= (int) date('Y') + 1) {
            return $year;
        }

        return null;
    }

    private function truncateString(string $value, int $maxLength): string
    {
        if (strlen($value) <= $maxLength) {
            return $value;
        }

        return substr($value, 0, $maxLength - 3).'...';
    }
}
