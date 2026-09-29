<div class="p-6 max-w-xl">
    <h1 class="text-xl font-semibold mb-4">Nuevo usuario</h1>

    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
            <input type="text" wire:model="name" class="w-full rounded border-gray-300">
            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
            <input type="email" wire:model="email" class="w-full rounded border-gray-300">
            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
            <input type="password" wire:model="password" class="w-full rounded border-gray-300">
            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
            <input type="password" wire:model="password_confirmation" class="w-full rounded border-gray-300">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de usuario</label>
            <select wire:model.live="type" class="w-full rounded border-gray-300">
                <option value="company_user">Empresa</option>
                <option value="specialist">Freelancer</option>
                <option value="ecosystem_admin">Administrador</option>
            </select>
        </div>

        @if ($type !== 'ecosystem_admin')
            <div>
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
                <div>
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
                <p class="text-sm text-gray-500">Esta empresa aún no tiene roles configurados.</p>
            @endif
        @endif

        <div class="flex justify-end gap-2 pt-4">
            <a href="{{ route('admin.usuarios.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700">
                Crear usuario
            </button>
        </div>
    </form>
</div>