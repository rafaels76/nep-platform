<div class="p-6">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-xl font-semibold">Casos operacionales</h1>
            <p class="text-sm text-gray-500">Empresa activa: {{ $company?->name ?? 'Ninguna' }}</p>
        </div>

        @can('casos.crear')
        <button wire:click="$toggle('showForm')" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700">
            Nuevo caso
        </button>
        @endcan
    </div>

    @if (session('status'))
    <div class="mb-4 rounded bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-2">
        {{ session('status') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="mb-4 rounded bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2">
        {{ $errors->first() }}
    </div>
    @endif

    @if ($showForm)
    <form wire:submit="create" class="mb-6 p-4 border rounded space-y-3 max-w-xl">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
            <input type="text" wire:model="title" class="w-full rounded border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
            <textarea wire:model="description" rows="3" class="w-full rounded border-gray-300"></textarea>
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 text-sm text-gray-600">Cancelar</button>
            <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700">Crear caso</button>
        </div>
    </form>
    @endif

    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Título</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Estado</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Creado por</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Asignado a</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($cases as $case)
            <tr wire:key="case-{{ $case->id }}">
                <td class="px-4 py-2 text-sm">
                    <a href="{{ route('casos.show', $case->id) }}" class="text-indigo-600 hover:text-indigo-800 underline">
                        {{ $case->title }}
                    </a>
                </td>
                <td class="px-4 py-2 text-sm">
                    <span class="inline-block bg-gray-100 rounded px-2 py-1 text-xs">{{ $case->status->label() }}</span>
                </td>
                <td class="px-4 py-2 text-sm">{{ $case->creator->name }}</td>
                <td class="px-4 py-2 text-sm">
                    @if ($canAssign && $case->status->value !== 'cerrado')
                    <select wire:change="assign('{{ $case->id }}', $event.target.value)" class="rounded border-gray-300 text-sm">
                        <option value="">Sin asignar</option>
                        @foreach ($specialists as $specialist)
                        <option value="{{ $specialist->id }}" @selected($case->assigned_to === $specialist->id)>
                            {{ $specialist->name }}
                        </option>
                        @endforeach
                    </select>
                    @else
                    {{ $case->assignee?->name ?? 'Sin asignar' }}
                    @endif
                </td>
                <td class="px-4 py-2 text-sm">
                    @can('casos.gestionar')
                    @foreach ($case->status->allowedTransitions() as $next)
                    <button
                        wire:click="transition('{{ $case->id }}', '{{ $next->value }}')"
                        @if ($next->value === 'cerrado') wire:confirm="¿Cerrar este caso?" @endif
                        class="text-indigo-600 hover:text-indigo-800 text-xs underline mr-2"
                        >
                        {{ $next->label() }}
                    </button>
                    @endforeach
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">No hay casos para mostrar.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $cases->links() }}
    </div>
</div>