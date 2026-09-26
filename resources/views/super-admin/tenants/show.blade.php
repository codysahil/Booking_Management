@extends('layouts.super-admin')

@section('content')
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-gray-900">{{ $tenant->name }}</h1>
            <p class="text-gray-500 text-sm mt-1">{{ $tenant->slug }}</p>
        </div>
        <a href="{{ route('super-admin.tenants.edit', $tenant) }}"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm">
            Edit
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="font-bold text-gray-900 mb-2">Admin login link</h2>
        @if (config('app.tenant_domain'))
            <p class="text-sm text-gray-700">
                <a href="http://{{ $tenant->slug }}.{{ config('app.tenant_domain') }}/admin/login" class="text-slate-900 underline" target="_blank">
                    {{ $tenant->slug }}.{{ config('app.tenant_domain') }}/admin/login
                </a>
            </p>
            <p class="text-xs text-gray-500 mt-1">Share this exact link with the owner — logging in anywhere else won't work for their account.</p>
        @else
            <p class="text-sm text-gray-700">
                <a href="{{ route('admin.login') }}" class="text-slate-900 underline" target="_blank">{{ route('admin.login') }}</a>
            </p>
            <p class="text-xs text-gray-500 mt-1">Every hostel shares this same link for now — set <code class="bg-gray-100 px-1 rounded">TENANT_DOMAIN</code> once you have a domain to give each hostel its own URL instead.</p>
        @endif
    </div>

    @php($subscription = $tenant->subscription)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex justify-between items-start mb-4">
            <h2 class="font-bold text-gray-900">Billing</h2>
            @if ($subscription)
                <span @class([
                    'text-xs px-2 py-1 rounded-full font-medium',
                    'bg-green-100 text-green-800' => in_array($subscription->status, ['active', 'internal', 'trialing']),
                    'bg-amber-100 text-amber-800' => $subscription->status === 'past_due',
                    'bg-gray-100 text-gray-600' => in_array($subscription->status, ['expired', 'cancelled']),
                ])>{{ $subscription->statusLabel() }}</span>
            @endif
        </div>

        @if ($subscription && $subscription->status === 'past_due' && $subscription->grace_ends_at)
            <p class="text-sm text-amber-700 mb-4">A charge failed — writes stay allowed until {{ $subscription->grace_ends_at->format('d M, Y H:i') }} while Razorpay retries.</p>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <form method="POST" action="{{ route('super-admin.tenants.subscription.assign-plan', $tenant) }}" class="space-y-2">
                @csrf
                @method('PUT')
                <label class="block text-sm font-medium text-gray-700">Plan</label>
                <div class="flex gap-2">
                    <select name="plan_id" class="flex-1 border-gray-300 rounded-lg text-sm focus:ring-slate-500 focus:border-slate-500">
                        <option value="">No plan</option>
                        @foreach (\App\Models\Plan::where('is_active', true)->orderBy('price')->get() as $plan)
                            <option value="{{ $plan->id }}" @selected($subscription?->plan_id === $plan->id)>
                                {{ $plan->name }} — ₹{{ number_format($plan->price) }}/{{ $plan->billing_interval === 'yearly' ? 'yr' : 'mo' }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">Save</button>
                </div>
            </form>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Razorpay mandate</label>
                @if ($subscription?->razorpay_subscription_id)
                    <p class="text-sm text-gray-700">Subscription created on Razorpay.</p>
                    @if ($subscription->meta['short_url'] ?? null)
                        <a href="{{ $subscription->meta['short_url'] }}" target="_blank" class="text-sm text-slate-900 underline break-all">
                            {{ $subscription->meta['short_url'] }}
                        </a>
                        <p class="text-xs text-gray-500">Share this link with the owner to authorize the recurring mandate, if they haven't already.</p>
                    @endif
                @else
                    <form method="POST" action="{{ route('super-admin.tenants.subscription.start-razorpay', $tenant) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">
                            Start Razorpay subscription
                        </button>
                    </form>
                    <p class="text-xs text-gray-500">Assign a plan with a linked Razorpay plan first.</p>
                @endif
            </div>
        </div>

        <details class="mt-6 pt-4 border-t border-gray-100">
            <summary class="text-sm text-gray-500 cursor-pointer">Manual override (bank transfer, emergency)</summary>
            <form method="POST" action="{{ route('super-admin.tenants.subscription.update', $tenant) }}" class="flex flex-wrap items-end gap-3 mt-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="border-gray-300 rounded-lg text-sm focus:ring-slate-500 focus:border-slate-500">
                        @foreach (\App\Models\Subscription::STATUSES as $value => $label)
                            <option value="{{ $value }}" @selected($subscription?->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Paid through (optional)</label>
                    <input type="date" name="current_period_end" value="{{ $subscription?->current_period_end?->format('Y-m-d') }}"
                        class="border-gray-300 rounded-lg text-sm focus:ring-slate-500 focus:border-slate-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm font-medium hover:bg-slate-800 transition">
                    Apply
                </button>
            </form>
        </details>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="font-bold text-gray-900 mb-4">Account</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Status</dt>
                    <dd class="font-medium">
                        <span class="text-xs px-2 py-1 rounded-full {{ $tenant->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($tenant->status) }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Owner</dt>
                    <dd class="font-medium text-gray-900">{{ $tenant->owner_name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Owner email</dt>
                    <dd class="font-medium text-gray-900">{{ $tenant->owner_email ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Owner phone</dt>
                    <dd class="font-medium text-gray-900">{{ $tenant->owner_phone ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Trial ends</dt>
                    <dd class="font-medium text-gray-900">{{ $tenant->trial_ends_at?->format('d M, Y') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Onboarded</dt>
                    <dd class="font-medium text-gray-900">{{ $tenant->created_at->format('d M, Y') }}</dd>
                </div>
            </dl>
            @if ($tenant->notes)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-gray-500 text-sm mb-1">Notes</p>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $tenant->notes }}</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="font-bold text-gray-900 mb-4">Staff logins</h2>
            <div class="divide-y divide-gray-100">
                @forelse ($tenant->users as $user)
                    <div class="py-3 flex justify-between items-center">
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">{{ $user->role_label }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No staff yet.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
