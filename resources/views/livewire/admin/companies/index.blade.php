<div class="p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold">Empresas</h1>
        <a href="{{ route('admin.empresas.create') }}" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700">
            Nueva empresa
        </a>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-2">
            {{ session('status') }}
        </div>
    @endif

    <input
        type="search"
        wire:model.live.debounce.300ms="search"
        placeholder="Buscar por nombre, razón social o identificación fiscal"
        class="mb-4 w-full max-w-md rounded border-gray-300 text-sm"
    >

    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Empresa</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Identificación fiscal</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Estado</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Usuarios</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Casos</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($companies as $company)
                <tr wire:key="company-{{ $company->id }}">
                    <td class="px-4 py-2 text-sm">
                        {{ $company->name }}
                        @if ($company->legal_name)
                            <div class="text-xs text-gray-400">{{ $company->legal_name }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-sm">{{ $company->tax_id ?? '—' }}</td>
                    <td class="px-4 py-2 text-sm">
                        @php
                            $badge = match ($company->status) {
                                'active' => 'bg-green-100 text-green-700',
                                'suspended' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="inline-block rounded px-2 py-1 text-xs {{ $badge }}">{{ $company->statusLabel() }}</span>
                    </td>
                    <td class="px-4 py-2 text-sm">{{ $company->users_count }}</td>
                    <td class="px-4 py-2 text-sm">{{ $company->cases_count }}</td>
                    <td class="px-4 py-2 text-sm">
                        <a href="{{ route('admin.empresas.edit', $company) }}" class="text-gray-600 hover:text-gray-800 underline">Editar</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-400">No hay empresas que coincidan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $companies->links() }}
    </div>
</div>