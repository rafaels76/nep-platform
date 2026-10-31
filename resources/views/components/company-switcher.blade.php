@php
    $user = auth()->user();
    $companies = $user->availableCompanies()->orderBy('companies.name')->get();
    $active = $user->activeCompany();
@endphp

@if ($companies->count() > 1)
    <form method="POST" action="{{ route('empresa-activa.update') }}" class="flex items-center">
        @csrf
        <label for="company_id" class="sr-only">Empresa activa</label>
        <select
            id="company_id"
            name="company_id"
            onchange="this.form.submit()"
            class="rounded-md border-gray-300 text-sm text-gray-600 focus:border-indigo-500 focus:ring-indigo-500"
        >
            @foreach ($companies as $company)
                <option value="{{ $company->id }}" @selected($active?->id === $company->id)>
                    {{ $company->name }}{{ $company->status !== 'active' ? ' (suspendida)' : '' }}
                </option>
            @endforeach
        </select>
    </form>
@elseif ($active)
    <span class="text-sm text-gray-500">{{ $active->name }}</span>
@endif