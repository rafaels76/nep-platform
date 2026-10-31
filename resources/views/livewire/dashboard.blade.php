@php
$statusStyles = [
'nuevo' => ['card' => 'bg-sky-50 border-sky-200 text-sky-800', 'bar' => 'bg-sky-500'],
'en_analisis' => ['card' => 'bg-amber-50 border-amber-200 text-amber-800', 'bar' => 'bg-amber-500'],
'en_ejecucion' => ['card' => 'bg-indigo-50 border-indigo-200 text-indigo-800', 'bar' => 'bg-indigo-500'],
'cerrado' => ['card' => 'bg-green-50 border-green-200 text-green-800', 'bar' => 'bg-green-500'],
];
$fmt = fn ($date) => $date?->timezone('America/Caracas')->format('d/m/Y H:i');
@endphp

<div class="p-6 space-y-10">
    <div>
        <h1 class="text-xl font-semibold">Panel de inicio</h1>
        <p class="text-sm text-gray-500">
            Hola, {{ auth()->user()->name }}.
            @if ($company)
            Empresa activa: <span class="font-medium">{{ $company->name }}</span>
            @endif
        </p>
    </div>

    @if (! $company && ! $isAdmin)
    <div class="rounded border border-amber-200 bg-amber-50 text-amber-800 text-sm px-4 py-3">
        Tu usuario aún no está vinculado a ninguna empresa. Contacta al administrador para que te asigne una.
    </div>
    @endif

    {{-- Vista global (solo administradores) --}}
    @if ($isAdmin)
    <section>
        <h2 class="text-lg font-semibold mb-3">Vista global del ecosistema</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded border bg-white p-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Empresas</span>
                    <a href="{{ route('admin.empresas.index') }}" class="text-xs text-indigo-600 hover:underline">Ver todas</a>
                </div>
                <div class="mt-1 text-3xl font-semibold">{{ $global['companies']['total'] }}</div>
                <div class="mt-2 text-xs text-gray-500">
                    {{ $global['companies']['active'] }} activas ·
                    {{ $global['companies']['inactive'] }} inactivas ·
                    {{ $global['companies']['suspended'] }} suspendidas
                </div>
            </div>

            <div class="rounded border bg-white p-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Usuarios activos</span>
                    <a href="{{ route('admin.usuarios.index') }}" class="text-xs text-indigo-600 hover:underline">Ver todos</a>
                </div>
                <div class="mt-1 text-3xl font-semibold">{{ $global['users']['active'] }}</div>
                <div class="mt-2 text-xs text-gray-500">
                    {{ $global['users']['admins'] }} admin ·
                    {{ $global['users']['companyUsers'] }} empresa ·
                    {{ $global['users']['specialists'] }} freelancers ·
                    {{ $global['users']['disabled'] }} deshabilitados
                </div>
            </div>

            <div class="rounded border bg-white p-4">
                <span class="text-sm text-gray-500">Casos abiertos</span>
                <div class="mt-1 text-3xl font-semibold">{{ $global['cases']['open'] }}</div>
                <div class="mt-2 text-xs text-gray-500">
                    {{ $global['cases']['total'] }} en total ·
                    <span class="{{ $global['cases']['unassigned'] > 0 ? 'text-red-600 font-medium' : '' }}">
                        {{ $global['cases']['unassigned'] }} sin asignar
                    </span>
                </div>
            </div>
        </div>

        <div class="mt-4 rounded border bg-white">
            <div class="px-4 py-3 border-b text-sm font-medium text-gray-700">Empresas con más casos abiertos</div>
            <table class="min-w-full text-sm">
                <tbody class="divide-y">
                    @forelse ($global['topCompanies'] as $top)
                    <tr>
                        <td class="px-4 py-2">{{ $top->name }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $top->statusLabel() }}</td>
                        <td class="px-4 py-2 text-right font-medium">{{ $top->open_cases_count }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td class="px-4 py-6 text-center text-gray-400">No hay empresas registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @endif

    {{-- Métricas de la empresa activa --}}
    @if ($company)
    <section>
        <h2 class="text-lg font-semibold mb-3">
            {{ $isSpecialist ? 'Mis casos en '.$company->name : 'Casos de '.$company->name }}
        </h2>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($statusCounts as $item)
            <a href="{{ route('casos.index', ['estado' => $item['status']->value]) }}"
                class="{{ $statusStyles[$item['status']->value]['bar'] }}"
                style="width: {{ round($item['total'] / $total * 100, 1) }}%"
                title="{{ $item['status']->label() }}: {{ $item['total'] }}"></a>
            @endforeach
        </div>

        @if ($total > 0)
        <div class="mt-4">
            <div class="flex h-3 w-full overflow-hidden rounded bg-gray-100">
                @foreach ($statusCounts as $item)
                @if ($item['total'] > 0)
                <div
                    class="{{ $statusStyles[$item['status']->value]['bar'] }}"
                    style="width: {{ round($item['total'] / $total * 100, 1) }}%"
                    title="{{ $item['status']->label() }}: {{ $item['total'] }}"></div>
                @endif
                @endforeach
            </div>
            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                @foreach ($statusCounts as $item)
                <span class="inline-flex items-center gap-1">
                    <span class="inline-block h-2 w-2 rounded-full {{ $statusStyles[$item['status']->value]['bar'] }}"></span>
                    {{ $item['status']->label() }}
                </span>
                @endforeach
            </div>
        </div>
        @endif

        <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('casos.index', ['estado' => 'abiertos']) }}" class="block rounded border bg-white p-4 transition hover:shadow-sm">
                <div class="text-sm text-gray-500">{{ $isSpecialist ? 'Mis casos abiertos' : 'Casos abiertos' }}</div>
                <div class="mt-1 text-2xl font-semibold">{{ $openCount }}</div>
            </a>

            @unless ($isSpecialist)
            <a href="{{ route('casos.index', ['estado' => 'abiertos', 'asignado' => 'none']) }}" class="block rounded border bg-white p-4 transition hover:shadow-sm">
                <div class="text-sm text-gray-500">Sin asignar</div>
                <div class="mt-1 text-2xl font-semibold {{ $unassigned > 0 ? 'text-red-600' : '' }}">{{ $unassigned }}</div>
            </a>
            @endunless

            <a href="{{ route('casos.index', ['estado' => 'cerrado', 'cierre' => 30]) }}" class="block rounded border bg-white p-4 transition hover:shadow-sm">
                <div class="text-sm text-gray-500">Cerrados (últimos 30 días)</div>
                <div class="mt-1 text-2xl font-semibold">{{ $closedRecently }}</div>
            </a>

            <div class="rounded border bg-white p-4">
                <div class="text-sm text-gray-500">Tiempo medio de cierre</div>
                <div class="mt-1 text-2xl font-semibold">
                    {{ $avgDays === null ? '—' : number_format($avgDays, 1, ',', '.').' días' }}
                </div>
                <div class="text-xs text-gray-400">Casos cerrados en los últimos 90 días</div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="rounded border bg-white {{ $isSpecialist ? 'lg:col-span-3' : 'lg:col-span-2' }}">
                <div class="px-4 py-3 border-b flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">{{ $recentTitle }}</span>
                    <a href="{{ route('casos.index') }}" class="text-xs text-indigo-600 hover:underline">Ver todos los casos</a>
                </div>
                <ul class="divide-y">
                    @forelse ($recent as $case)
                    <li class="px-4 py-3 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <a href="{{ route('casos.show', $case->id) }}" class="block truncate text-sm text-indigo-600 hover:underline">
                                {{ $case->title }}
                            </a>
                            <div class="text-xs text-gray-400">
                                {{ $case->assignee?->name ?? 'Sin asignar' }} · {{ $fmt($case->updated_at) }}
                            </div>
                        </div>
                        <span class="shrink-0 rounded px-2 py-1 text-xs {{ $statusStyles[$case->status->value]['card'] }}">
                            {{ $case->status->label() }}
                        </span>
                    </li>
                    @empty
                    <li class="px-4 py-6 text-center text-sm text-gray-400">No hay casos para mostrar.</li>
                    @endforelse
                </ul>
            </div>

            @unless ($isSpecialist)
            @php $maxLoad = max(1, $workload->max('total')); @endphp
            <div class="rounded border bg-white">
                <div class="px-4 py-3 border-b text-sm font-medium text-gray-700">Casos abiertos por freelancer</div>
                <ul class="divide-y">
                    @forelse ($workload as $person)
                    <li>
                        <a href="{{ route('casos.index', ['estado' => 'abiertos', 'asignado' => $person['id']]) }}" class="block px-4 py-3 hover:bg-gray-50">
                            <div class="flex items-center justify-between text-sm">
                                <span>
                                    {{ $person['name'] }}
                                    @if ($person['disabled'])
                                    <span class="ml-1 rounded bg-red-100 px-1.5 py-0.5 text-xs text-red-700">Deshabilitado</span>
                                    @endif
                                </span>
                                <span class="text-gray-500">{{ $person['total'] }}</span>
                            </div>
                            <div class="mt-1 h-1.5 w-full rounded bg-gray-100">
                                <div class="h-1.5 rounded bg-indigo-500" style="width: {{ round($person['total'] / $maxLoad * 100) }}%"></div>
                            </div>
                        </a>
                    </li>
                    @empty
                    <li class="px-4 py-6 text-center text-sm text-gray-400">Esta empresa no tiene freelancers vinculados.</li>
                    @endforelse
                </ul>
            </div>
            @endunless
        </div>
    </section>
    @endif
</div>