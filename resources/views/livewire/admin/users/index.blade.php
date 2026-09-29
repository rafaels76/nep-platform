<div class="p-6">
    <h1 class="text-xl font-semibold mb-4">Usuarios del sistema</h1>

    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Nombre</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Correo</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Tipo</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Empresas / Roles</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach ($users as $user)
                <tr>
                    <td class="px-4 py-2 text-sm">{{ $user->name }}</td>
                    <td class="px-4 py-2 text-sm">{{ $user->email }}</td>
                    <td class="px-4 py-2 text-sm">{{ $user->type }}</td>
                    <td class="px-4 py-2 text-sm">
                        @forelse ($user->companies as $company)
                            <span class="inline-block bg-gray-100 rounded px-2 py-1 text-xs mr-1">
                                {{ $company->name }} ({{ $company->pivot->role }})
                            </span>
                        @empty
                            <span class="text-gray-400 text-xs">Sin empresa asignada</span>
                        @endforelse
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>