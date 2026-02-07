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
            'lead_source_generator' => $this->normalizeLeadSourceGeneratorOutput($data),
            'candidate_icp_fit' => $this->normalizeCandidateIcpFitOutput($data),
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
            'technology' => 'tech',
        ];

        $normalized = [];
        foreach ($signals as $signal) {
            if (! is_array($signal)) {
                continue;
            }
            $categoryOrType = $signal['category'] ?? $signal['type'] ?? '';
            $typeLower = is_string($categoryOrType) ? strtolower(trim($categoryOrType)) : '';
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
            $evidenceText = $signal['evidence_snippet'] ?? $signal['evidence'] ?? '';
            $normalized[] = [
                'type' => $mappedType,
                'severity' => $severity,
                'title' => $this->truncateString($signal['title'] ?? $description ?: 'Unknown Signal', 100),
                'description' => $description,
                'evidence' => $this->truncateString($evidenceText, 300),
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
        $briefMarkdown = $data['brief_markdown'] ?? $data['CompanyOverview'] ?? $data['Company Overview'] ?? $data['overview'] ?? $data['brief'] ?? $data['content'] ?? $data['company_overview'] ?? $data['full_brief'] ?? '';
        $recommendedAngle = $data['recommended_angle'] ?? $data['RecommendedApproach'] ?? $data['Recommended Approach'] ?? $data['recommended_approach'] ?? '';
        $talkingPoints = $data['talking_points'] ?? $data['TalkingPoints'] ?? $data['Talking Points'] ?? [];
        if (! is_array($talkingPoints)) {
            $talkingPoints = [];
        }
        while (count($talkingPoints) < 3) {
            $talkingPoints[] = '';
        }
        $talkingPoints = array_slice(array_values($talkingPoints), 0, 5);

        $opportunitiesRaw = $data['opportunities_identified'] ?? $data['Opportunities Identified'] ?? $data['OpportunitiesIdentified'] ?? '';
        $opportunitiesIdentified = is_string($opportunitiesRaw)
            ? $this->parseOpportunitiesString($opportunitiesRaw)
            : (is_array($opportunitiesRaw) ? implode("\n\n", array_map(fn ($v) => $this->formatOpportunityAsMarkdown($this->parseOpportunityItem($v)), $opportunitiesRaw)) : '');

        $facts = $data['facts'] ?? [];
        if (! is_array($facts)) {
            $facts = [];
        }
        if (empty($facts) && isset($data['KeyFacts']) && is_array($data['KeyFacts'])) {
            foreach ($data['KeyFacts'] as $label => $value) {
                $facts[] = [
                    'label' => is_string($label) ? $label : 'Item',
                    'value' => $this->factValueToString($value),
                ];
            }
        }
        if (empty($facts) && isset($data['Key Facts']) && is_array($data['Key Facts'])) {
            $this->appendFactsFromSource($facts, $data['Key Facts']);
        }
        if (empty($facts) && isset($data['key_facts']) && is_array($data['key_facts'])) {
            if (isset($data['key_facts'][0]) && is_array($data['key_facts'][0])) {
                foreach ($data['key_facts'] as $item) {
                    $label = $item['label'] ?? $item['name'] ?? 'Item';
                    $value = $item['value'] ?? $item['content'] ?? '';
                    $facts[] = ['label' => is_string($label) ? $label : 'Item', 'value' => $this->factValueToString($value)];
                }
            } else {
                foreach ($data['key_facts'] as $label => $value) {
                    $facts[] = [
                        'label' => is_string($label) ? $label : 'Item',
                        'value' => $this->factValueToString($value),
                    ];
                }
            }
        }

        return [
            'brief_markdown' => is_string($briefMarkdown) ? $briefMarkdown : '',
            'facts' => $facts,
            'opportunities_identified' => $opportunitiesIdentified,
            'recommended_angle' => is_string($recommendedAngle) ? $recommendedAngle : '',
            'talking_points' => $talkingPoints,
        ];
    }

    /**
     * Append facts from a source array (numeric keys = list of items, string keys = label=>value).
     *
     * @param  array<int, array{label: string, value: string}>  $facts
     * @param  array<int|string, mixed>  $source
     */
    private function appendFactsFromSource(array &$facts, array $source): void
    {
        $isList = array_keys($source) === range(0, count($source) - 1);
        if ($isList) {
            foreach ($source as $item) {
                if (is_array($item) && isset($item['label'], $item['value'])) {
                    $facts[] = [
                        'label' => is_string($item['label']) ? $item['label'] : 'Item',
                        'value' => $this->factValueToString($item['value']),
                    ];
                } elseif (is_string($item)) {
                    $facts[] = ['label' => 'Item', 'value' => $item];
                }
            }
        } else {
            foreach ($source as $label => $value) {
                $facts[] = [
                    'label' => is_string($label) ? $label : 'Item',
                    'value' => $this->factValueToString($value),
                ];
            }
        }
    }

    /**
     * Parse opportunities when stored as JSON string (array or newline-separated objects) and return formatted markdown.
     */
    private function parseOpportunitiesString(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }
        if (str_starts_with($raw, '[')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return implode("\n\n", array_map(fn ($v) => $this->formatOpportunityAsMarkdown($v), $decoded));
            }
        }
        if (str_starts_with($raw, '{')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $this->formatOpportunityAsMarkdown($decoded);
            }
            // Newline-separated JSON objects: split and parse each
            $blocks = preg_split('/\}\s*\n+\s*\{/', $raw);
            if ($blocks !== false && count($blocks) > 1) {
                $formatted = [];
                foreach ($blocks as $i => $block) {
                    $block = ($i > 0 ? '{' : '').$block.($i < count($blocks) - 1 ? '}' : '');
                    $decoded = json_decode($block, true);
                    if (is_array($decoded)) {
                        $formatted[] = $this->formatOpportunityAsMarkdown($decoded);
                    }
                }
                if ($formatted !== []) {
                    return implode("\n\n", $formatted);
                }
            }
        }

        return $raw;
    }

    /**
     * If the item is a JSON string, decode it to an array; otherwise return as-is (for formatOpportunityAsMarkdown).
     *
     * @return array<string, mixed>|mixed
     */
    private function parseOpportunityItem(mixed $item): mixed
    {
        if (! is_string($item) || trim($item) === '') {
            return $item;
        }
        $trimmed = trim($item);
        if (! str_starts_with($trimmed, '{') && ! str_starts_with($trimmed, '[')) {
            return $item;
        }
        $decoded = json_decode($item, true);

        return is_array($decoded) ? $decoded : $item;
    }

    /**
     * Format a single opportunity object (e.g. Signal, Description, Importance, Potential Impact) as markdown.
     *
     * @param  mixed  $item  Array or string; arrays are formatted as readable bullets.
     */
    private function formatOpportunityAsMarkdown(mixed $item): string
    {
        if (is_string($item) && trim($item) !== '') {
            return $item;
        }
        if (! is_array($item)) {
            return (string) $item;
        }
        $signal = $item['Signal'] ?? $item['signal'] ?? '';
        $description = $item['Description'] ?? $item['description'] ?? '';
        $importance = $item['Importance'] ?? $item['importance'] ?? '';
        $impact = $item['Potential Impact'] ?? $item['PotentialImpact'] ?? $item['potential_impact'] ?? '';
        $lines = [];
        if ((string) $signal !== '') {
            $lines[] = '**'.trim((string) $signal).'**';
        }
        if ((string) $description !== '') {
            $lines[] = '- **Description:** '.trim((string) $description);
        }
        if ((string) $importance !== '') {
            $lines[] = '- **Importance:** '.trim((string) $importance);
        }
        if ((string) $impact !== '') {
            $lines[] = '- **Potential Impact:** '.trim((string) $impact);
        }

        return $lines === [] ? (json_encode($item, JSON_UNESCAPED_SLASHES) ?: '') : implode("\n", $lines);
    }

    private function factValueToString(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES) ?: '';
        }
        if (is_scalar($value)) {
            return (string) $value;
        }

        return '';
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

        $subject = $email['subject'] ?? $email['Subject'] ?? '';
        $body = $email['body'] ?? $email['Body'] ?? '';

        return [
            'linkedin_dm' => $linkedinDm,
            'email' => [
                'subject' => $subject,
                'body' => $body,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeLeadSourceGeneratorOutput(array $data): array
    {
        $name = $data['name'] ?? '';
        $queries = $data['queries'] ?? [];
        if (! is_array($queries)) {
            $queries = [];
        }
        $queries = array_values(array_filter(
            array_map(fn ($q) => is_string($q) ? $this->truncateString(trim($q), 500) : null, $queries)
        ));

        return [
            'name' => is_string($name) ? trim($name) : '',
            'queries' => $queries,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeCandidateIcpFitOutput(array $data): array
    {
        $fit = $data['fit'] ?? '';
        $fit = is_string($fit) ? strtolower(trim($fit)) : '';
        if (! in_array($fit, ['high', 'medium', 'low'], true)) {
            $fit = 'low';
        }
        $reason = $data['reason'] ?? '';
        $reason = is_string($reason) ? $this->truncateString(trim($reason), 500) : '';

        return [
            'fit' => $fit,
            'reason' => $reason,
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
