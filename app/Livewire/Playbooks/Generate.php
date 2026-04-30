<?php

declare(strict_types=1);

namespace App\Livewire\Playbooks;

use App\Models\Playbook;
use App\Services\AI\OpenAIService;
use App\Services\PlanLimitService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Generate extends Component
{
    public string $businessDescription = '';

    public string $icpContext = '';

    public string $valuePropositions = '';

    public bool $generating = false;

    public function generate(): void
    {
        $this->validate([
            'businessDescription' => ['required', 'string', 'max:2000'],
            'icpContext' => ['nullable', 'string', 'max:1000'],
            'valuePropositions' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();
        $organization = $user->currentOrganization;

        if (! $organization) {
            $this->addError('businessDescription', 'No organisation selected.');

            return;
        }

        $planLimits = app(PlanLimitService::class);

        if (! $planLimits->canGenerateAiPlaybook($organization)) {
            $this->addError('businessDescription', 'AI-generated Playbooks require a Starter plan or above.');

            return;
        }

        $this->generating = true;

        /** @var OpenAIService $ai */
        $ai = app(OpenAIService::class);
        $result = $ai->runStandalone('playbook_generator', [
            'business_description' => trim($this->businessDescription),
            'icp_context' => trim($this->icpContext) ?: 'Not specified',
            'value_propositions' => trim($this->valuePropositions) ?: 'Not specified',
        ]);

        $this->generating = false;

        if (! $result['success'] || ! is_array($result['data'])) {
            $this->addError('businessDescription', $result['error'] ?? 'AI generation failed. Please try again.');

            return;
        }

        $data = $result['data'];

        Playbook::create([
            'organization_id' => $organization->id,
            'name' => is_string($data['name'] ?? null) ? $data['name'] : 'AI-Generated Playbook',
            'angle' => is_string($data['angle'] ?? null) ? $data['angle'] : '',
            'dm_template' => is_string($data['dm_template'] ?? null) ? $data['dm_template'] : '',
            'email_template' => is_string($data['email_template'] ?? null) ? $data['email_template'] : '',
            'constraints' => is_array($data['constraints'] ?? null)
                ? array_values(array_filter(
                    array_map(fn ($c) => is_string($c) ? trim($c) : null, $data['constraints']),
                    fn ($c) => $c !== null && $c !== ''
                ))
                : [],
            'is_active' => false,
        ]);

        session()->flash('message', 'Playbook generated and saved successfully.');
        $this->redirect(route('playbooks.index'), navigate: true);
    }

    public function render(): View
    {
        $canGenerate = false;
        $organization = Auth::user()->currentOrganization;

        if ($organization) {
            $canGenerate = app(PlanLimitService::class)->canGenerateAiPlaybook($organization);
        }

        return view('livewire.playbooks.generate', [
            'canGenerate' => $canGenerate,
        ]);
    }
}
