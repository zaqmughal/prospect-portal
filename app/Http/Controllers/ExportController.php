<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ExportController extends Controller
{
    /**
     * Export weekly shortlist as CSV.
     */
    public function shortlistCsv(): StreamedResponse
    {
        $accounts = Account::where('user_id', Auth::id())
            ->where('lead_score', '>', 0)
            ->orderByDesc('lead_score')
            ->limit(50)
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="shortlist-'.date('Y-m-d').'.csv"',
        ];

        $callback = function () use ($accounts): void {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Name',
                'Domain',
                'URL',
                'Sector',
                'Size',
                'Location',
                'Score',
                'Pipeline Stage',
                'Research Status',
                'Last Researched',
                'Signals Count',
            ]);

            foreach ($accounts as $account) {
                fputcsv($file, [
                    $account->name,
                    $account->domain,
                    $account->url,
                    $account->sector ?? '',
                    $account->size_band ?? '',
                    $account->location ?? '',
                    $account->lead_score,
                    $account->pipeline_stage->label(),
                    $account->research_status->label(),
                    $account->last_researched_at?->format('Y-m-d H:i') ?? '',
                    $account->signalEvents()->count(),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export outreach bundle as ZIP.
     */
    public function outreachZip(): Response
    {
        $accounts = Account::where('user_id', Auth::id())
            ->where('lead_score', '>', 0)
            ->whereHas('outreachAssets')
            ->orderByDesc('lead_score')
            ->limit(20)
            ->with(['latestBrief', 'outreachAssets'])
            ->get();

        $tempPath = storage_path('app/temp');
        if (! is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        $zipPath = $tempPath.'/outreach-bundle-'.date('Y-m-d-His').'.zip';
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            abort(500, 'Could not create ZIP file');
        }

        foreach ($accounts as $account) {
            $folderName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $account->domain);

            // Add brief
            if ($account->latestBrief) {
                $zip->addFromString(
                    "{$folderName}/brief.md",
                    $account->latestBrief->content_md
                );
            }

            // Add outreach assets
            foreach ($account->outreachAssets as $asset) {
                $filename = match ($asset->channel->value) {
                    'linkedin' => 'linkedin-dm.txt',
                    'email' => 'email.txt',
                    default => $asset->channel->value.'.txt',
                };

                $zip->addFromString(
                    "{$folderName}/{$filename}",
                    $asset->content
                );
            }

            // Add account summary
            $summary = "# {$account->name}\n\n";
            $summary .= "**URL:** {$account->url}\n";
            $summary .= '**Sector:** '.($account->sector ?? 'Unknown')."\n";
            $summary .= "**Score:** {$account->lead_score}\n";
            $summary .= "**Pipeline:** {$account->pipeline_stage->label()}\n";

            $zip->addFromString("{$folderName}/summary.md", $summary);
        }

        $zip->close();

        $content = file_get_contents($zipPath);
        unlink($zipPath);

        return response($content, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="outreach-bundle-'.date('Y-m-d').'.zip"',
        ]);
    }
}
