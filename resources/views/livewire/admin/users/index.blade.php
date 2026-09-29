<div class="p-6">
    <h1 class="text-xl font-semibold mb-4">Usuarios del sistema</h1>

    @if (session('status'))
        <div class="mb-4 rounded bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-2">
            {{ session('status') }}
        </div>
    @endif

    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Nombre</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Correo</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Tipo</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Empresas / Roles</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Acciones</th>
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
                            <span class="inline-flex items-center bg-gray-100 rounded px-2 py-1 text-xs mr-1 mb-1">
                                {{ $company->name }} ({{ $company->pivot->role }})
                                <button
                                    wire:click="removeAccess('{{ $user->id }}', '{{ $company->id }}')"
                                    wire:confirm="¿Quitar el acceso de este usuario a {{ $company->name }}?"
                                    class="ml-2 text-red-500 hover:text-red-700"
                                    title="Quitar acceso"
                                >
                                    &times;
                                </button>
                            </span>
                        @empty
                            <span class="text-gray-400 text-xs">Sin empresa asignada</span>
                        @endforelse
                    </td>
                    <td class="px-4 py-2 text-sm">
                        <button
                            wire:click="openRoleModal('{{ $user->id }}')"
                            class="text-indigo-600 hover:text-indigo-800 text-sm underline"
                        >
                            Asignar rol
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4">Asignar rol a usuario</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Empresa</label>
                    <select wire:model.live="selectedCompanyId" class="w-full rounded border-gray-300">
                        <option value="">Selecciona una empresa</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                    @error('selectedCompanyId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                @if (count($availableRoles) > 0)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                        <select wire:model="selectedRole" class="w-full rounded border-gray-300">
                            <option value="">Selecciona un rol</option>
                            @foreach ($availableRoles as $role)
                                <option value="{{ $role }}">{{ $role }}</option>
                            @endforeach
                        </select>
                        @error('selectedRole') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                @elseif ($selectedCompanyId)
                    <p class="text-sm text-gray-500 mb-4">Esta empresa aún no tiene roles configurados.</p>
                @endif

                <div class="flex justify-end gap-2 mt-6">
                    <button wire:click="closeModal" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                        Cancelar
                    </button>
                    <button wire:click="assignRole" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>