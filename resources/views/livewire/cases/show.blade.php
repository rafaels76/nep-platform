<div class="p-6 max-w-4xl">
    <a href="{{ route('casos.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Volver a casos</a>

    @if (session('status'))
        <div class="mt-4 rounded bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-2">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-4 rounded bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mt-4 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold">{{ $case->title }}</h1>
            <p class="text-sm text-gray-500">{{ $case->company->name }}</p>
        </div>
        <span class="inline-block bg-gray-100 rounded px-3 py-1 text-sm">{{ $case->status->label() }}</span>
    </div>

    <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
        <div>
            <dt class="text-gray-500">Creado por</dt>
            <dd>{{ $case->creator->name }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Fecha de creación</dt>
            <dd>{{ $case->created_at->timezone('America/Caracas')->format('d/m/Y H:i') }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Asignado a</dt>
            <dd>
                @if ($canAssign && $case->status->value !== 'cerrado')
                    <select wire:change="assign($event.target.value)" class="rounded border-gray-300 text-sm">
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
            </dd>
        </div>
        <div>
            <dt class="text-gray-500">Tipo</dt>
            <dd>{{ $case->type }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Canal de origen</dt>
            <dd>{{ $case->metadata['channel'] ?? '—' }}</dd>
        </div>
        @if ($case->closed_at)
            <div>
                <dt class="text-gray-500">Fecha de cierre</dt>
                <dd>{{ $case->closed_at->timezone('America/Caracas')->format('d/m/Y H:i') }}</dd>
            </div>
        @endif
    </dl>

    @if ($case->description)
        <div class="mt-6">
            <h2 class="text-sm font-medium text-gray-500">Descripción</h2>
            <p class="mt-1 text-sm whitespace-pre-line">{{ $case->description }}</p>
        </div>
    @endif

    @can('casos.gestionar')
        @if (count($case->status->allowedTransitions()) > 0)
            <div class="mt-6 flex flex-wrap items-center gap-2">
                <span class="text-sm text-gray-500">Mover a:</span>
                @foreach ($case->status->allowedTransitions() as $next)
                    <button
                        wire:click="transition('{{ $next->value }}')"
                        @if ($next->value === 'cerrado') wire:confirm="¿Cerrar este caso? Esta acción no se puede revertir." @endif
                        class="px-3 py-1 text-sm border border-indigo-300 text-indigo-700 rounded hover:bg-indigo-50"
                    >
                        {{ $next->label() }}
                    </button>
                @endforeach
            </div>
        @endif
    @endcan

    <div class="mt-10">
        <h2 class="text-lg font-semibold mb-4">Historial</h2>

        @forelse ($timeline as $entry)
            <div class="relative pl-6 pb-6 border-l border-gray-200 last:pb-0 ml-2">
                <span class="absolute -left-1.5 top-1 h-3 w-3 rounded-full bg-indigo-500"></span>
                <div class="text-sm font-medium">{{ $entry['title'] }}</div>
                @if ($entry['detail'])
                    <div class="text-sm text-gray-600">{{ $entry['detail'] }}</div>
                @endif
                <div class="text-xs text-gray-400">
                    {{ $entry['who'] }} · {{ $entry['at']->timezone('America/Caracas')->format('d/m/Y H:i') }}
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400">Este caso aún no tiene eventos registrados.</p>
        @endforelse
    </div>
</div>