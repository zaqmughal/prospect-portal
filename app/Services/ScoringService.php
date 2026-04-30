<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SignalSeverity;
use App\Models\Account;
use App\Models\Icp;

class ScoringService
{
    private const MAX_ICP_SCORE = 25;

    private const MAX_SIGNAL_SCORE = 40;

    private const MAX_REACHABILITY_SCORE = 20;

    /**
     * Calculate the lead score for an account.
     *
     * @return array{score: int, breakdown: array<string, mixed>}
     */
    public function calculate(Account $account, ?Icp $icp = null): array
    {
        $icp = $icp ?? Icp::getDefault();

        $icpScore = $this->calculateIcpFit($account, $icp);
        $signalScore = $this->calculateSignalStrength($account, $icp);
        $reachabilityScore = $this->calculateReachability($account);

        $totalScore = $icpScore + $signalScore + $reachabilityScore;

        return [
            'score' => $totalScore,
            'breakdown' => [
                'icp_fit' => [
                    'score' => $icpScore,
                    'max' => self::MAX_ICP_SCORE,
                    'factors' => $this->getIcpFactors($account, $icp),
                ],
                'signal_strength' => [
                    'score' => $signalScore,
                    'max' => self::MAX_SIGNAL_SCORE,
                    'signals' => $this->getSignalFactors($account),
                ],
                'reachability' => [
                    'score' => $reachabilityScore,
                    'max' => self::MAX_REACHABILITY_SCORE,
                    'factors' => $this->getReachabilityFactors($account),
                ],
            ],
        ];
    }

    /**
     * Calculate ICP fit score (0-25): sector (0-20) + location (0-5).
     */
    private function calculateIcpFit(Account $account, ?Icp $icp): int
    {
        if ($icp === null) {
            return 13; // Default middle score if no ICP
        }

        $score = 0;

        // Sector match (0-20)
        $sectors = $icp->sectors ?? [];
        if (is_array($sectors) && $this->accountSectorMatchesIcpSectors($account->sector, $sectors)) {
            $score += 20;
        } elseif ($account->sector !== null) {
            $score += 5; // Some credit for having a sector
        }

        // Location match (0-5) - UK gets bonus
        if ($account->location !== null) {
            $location = strtolower($account->location);
            if (str_contains($location, 'uk') || str_contains($location, 'united kingdom') || str_contains($location, 'london') || str_contains($location, 'england')) {
                $score += 5;
            } else {
                $score += 2;
            }
        }

        return min($score, self::MAX_ICP_SCORE);
    }

    /**
     * Calculate signal strength score (0-40).
     */
    private function calculateSignalStrength(Account $account, ?Icp $icp): int
    {
        $signals = $account->signalEvents()->get();

        if ($signals->isEmpty()) {
            return 0;
        }

        $score = 0;
        $signalWeights = $icp !== null ? ($icp->signals ?? []) : [];

        foreach ($signals as $signal) {
            // Base score from severity
            $baseScore = match ($signal->severity) {
                SignalSeverity::High => 12,
                SignalSeverity::Medium => 8,
                SignalSeverity::Low => 4,
                default => 4,
            };

            // Apply ICP weight if configured
            if (is_array($signalWeights) && isset($signalWeights[$signal->type->value]['weight'])) {
                $weight = (int) $signalWeights[$signal->type->value]['weight'];
                $baseScore = (int) (($baseScore * $weight) / 10);
            }

            $score += $baseScore;
        }

        return min($score, self::MAX_SIGNAL_SCORE);
    }

    /**
     * Calculate reachability score (0-20).
     */
    private function calculateReachability(Account $account): int
    {
        $score = 0;

        // Has website (5 points)
        if ($account->url !== null) {
            $score += 5;
        }

        // Has been researched successfully (10 points)
        $latestRun = $account->latestResearchRun;
        if ($latestRun !== null && $latestRun->status->value === 'completed') {
            $score += 10;

            // Has brief (5 points)
            if ($latestRun->brief !== null) {
                $score += 5;
            }
        }

        return min($score, self::MAX_REACHABILITY_SCORE);
    }

    /**
     * Get ICP fit factors for breakdown.
     *
     * @return array<string, mixed>
     */
    private function getIcpFactors(Account $account, ?Icp $icp): array
    {
        $factors = [];

        if ($icp !== null) {
            $sectors = $icp->sectors ?? [];
            $factors['sector_match'] = is_array($sectors) && $this->accountSectorMatchesIcpSectors($account->sector, $sectors);
            $factors['sector'] = $account->sector;
        }

        $factors['location'] = $account->location;

        return $factors;
    }

    /**
     * Get signal factors for breakdown.
     *
     * @return array<array<string, mixed>>
     */
    private function getSignalFactors(Account $account): array
    {
        return $account->signalEvents()
            ->get()
            ->map(fn ($signal) => [
                'type' => $signal->type->value,
                'severity' => $signal->severity->value,
                'title' => $signal->title,
                'impact' => $signal->score_impact,
            ])
            ->toArray();
    }

    /**
     * Get reachability factors for breakdown.
     *
     * @return array<string, mixed>
     */
    private function getReachabilityFactors(Account $account): array
    {
        $latestRun = $account->latestResearchRun;

        return [
            'has_url' => $account->url !== null,
            'research_complete' => $latestRun?->status->value === 'completed',
            'has_brief' => $latestRun?->brief !== null,
        ];
    }

    /**
     * Check whether the account sector matches any ICP sector (exact, segment, or token/prefix).
     */
    private function accountSectorMatchesIcpSectors(?string $accountSector, array $icpSectors): bool
    {
        $accountSector = $accountSector !== null ? trim($accountSector) : '';
        if ($accountSector === '' || $icpSectors === []) {
            return false;
        }

        // Exact match
        if (in_array($accountSector, $icpSectors, true)) {
            return true;
        }

        // Segment match: split on slashes, trim, check if any segment equals an ICP sector
        $segments = array_map('trim', preg_split('/\s*\/\s*/', $accountSector, -1, PREG_SPLIT_NO_EMPTY));
        foreach ($segments as $segment) {
            if ($segment !== '' && in_array($segment, $icpSectors, true)) {
                return true;
            }
        }

        // Intelligent match: tokens (slash + space split) and ICP sectors, lowercase; match if equal or one prefix of other
        $accountTokens = [];
        foreach ($segments as $segment) {
            $words = array_map('trim', explode(' ', $segment));
            foreach ($words as $word) {
                if ($word !== '') {
                    $accountTokens[] = mb_strtolower($word, 'UTF-8');
                }
            }
        }
        foreach ($icpSectors as $icpSector) {
            if (! is_string($icpSector) || trim($icpSector) === '') {
                continue;
            }
            $icpLower = mb_strtolower(trim($icpSector), 'UTF-8');
            foreach ($accountTokens as $token) {
                if ($token === $icpLower) {
                    return true;
                }
                $longer = strlen($token) >= strlen($icpLower) ? $token : $icpLower;
                $shorter = strlen($token) < strlen($icpLower) ? $token : $icpLower;
                if (str_starts_with($longer, $shorter)) {
                    return true;
                }
                if (mb_strlen($shorter, 'UTF-8') >= 4 && str_starts_with($longer, mb_substr($shorter, 0, -1, 'UTF-8'))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Update an account's score.
     */
    public function updateScore(Account $account, ?Icp $icp = null): Account
    {
        $result = $this->calculate($account, $icp);

        $account->update([
            'lead_score' => $result['score'],
            'score_breakdown' => $result['breakdown'],
        ]);

        return $account;
    }
}
