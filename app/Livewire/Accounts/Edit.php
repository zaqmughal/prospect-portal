<?php

declare(strict_types=1);

namespace App\Livewire\Accounts;

use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Edit extends Component
{
    #[Locked]
    public int $accountId;

    public string $name = '';

    public string $url = '';

    public string $sector = '';

    public string $size_band = '';

    public string $location = '';

    public string $notes = '';

    public function mount(int $accountId): void
    {
        $this->accountId = $accountId;
        $account = Account::where('user_id', Auth::id())->findOrFail($accountId);

        $this->name = $account->name;
        $this->url = $account->url;
        $this->sector = $account->sector ?? '';
        $this->size_band = $account->size_band ?? '';
        $this->location = $account->location ?? '';
        $this->notes = $account->notes ?? '';
    }

    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:255'],
            'sector' => ['nullable', 'string', 'max:255'],
            'size_band' => ['nullable', 'string', 'in:1-10,11-50,51-200,201-500,500+'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $account = Account::where('user_id', Auth::id())->findOrFail($this->accountId);

        $account->update([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'domain' => Account::normalizeDomain($validated['url']),
            'sector' => $validated['sector'] ?: null,
            'size_band' => $validated['size_band'] ?: null,
            'location' => $validated['location'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ]);

        $this->redirect(route('accounts.show', $account), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.accounts.edit');
    }
}
