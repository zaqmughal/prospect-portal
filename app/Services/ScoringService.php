<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SignalSeverity;
use App\Models\Account;
use App\Models\Icp;

class ScoringService
{
    private const MAX_ICP_SCORE = 40;

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
     * Calculate ICP fit score (0-40).
     */
    private function calculateIcpFit(Account $account, ?Icp $icp): int
    {
        if ($icp === null) {
            return 20; // Default middle score if no ICP
        }

        $score = 0;

        // Sector match (0-20)
        $sectors = $icp->sectors ?? [];
        if (is_array($sectors) && in_array($account->sector, $sectors, true)) {
            $score += 20;
        } elseif ($account->sector !== null) {
            $score += 5; // Some credit for having a sector
        }

        // Size band match (0-15)
        $sizeBands = $icp->size_bands ?? [];
        if (is_array($sizeBands) && in_array($account->size_band, $sizeBands, true)) {
            $score += 15;
        } elseif ($account->size_band !== null) {
            $score += 5; // Some credit for having size info
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
            $factors['sector_match'] = is_array($sectors) && in_array($account->sector, $sectors, true);
            $factors['sector'] = $account->sector;

            $sizeBands = $icp->size_bands ?? [];
            $factors['size_match'] = is_array($sizeBands) && in_array($account->size_band, $sizeBands, true);
            $factors['size_band'] = $account->size_band;
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
