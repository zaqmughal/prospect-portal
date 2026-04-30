<div class="max-w-md mx-auto mt-16">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
        @if ($invalidToken)
            <div class="text-red-600">
                <svg class="mx-auto h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <h3 class="text-lg font-medium">Invalid or Expired Invitation</h3>
                <p class="mt-2 text-sm text-gray-600">This invitation link is no longer valid. Please ask the organisation administrator to send a new invitation.</p>
            </div>
        @elseif ($invitation && !auth()->check())
            <h3 class="text-lg font-medium text-gray-900">You've Been Invited</h3>
            <p class="mt-2 text-sm text-gray-600">
                You've been invited to join <strong>{{ $invitation->organization->name }}</strong>.
            </p>
            <div class="mt-6 space-y-3">
                <a href="{{ route('login') }}" class="block w-full inline-flex justify-center items-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700">
                    {{ __('Log In to Accept') }}
                </a>
                <a href="{{ route('register', ['invitation' => $token]) }}" class="block w-full inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    {{ __('Create an Account') }}
                </a>
            </div>
        @else
            <div class="text-green-600">
                <svg class="mx-auto h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-lg font-medium">Invitation Accepted</h3>
                <p class="mt-2 text-sm text-gray-600">Redirecting to dashboard...</p>
            </div>
        @endif
    </div>
</div>
