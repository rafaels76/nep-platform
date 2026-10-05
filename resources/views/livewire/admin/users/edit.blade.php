<div class="p-6 max-w-xl">
    <h1 class="text-xl font-semibold mb-4">Editar usuario</h1>

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
            <label class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña (opcional)</label>
            <input type="password" wire:model="password" class="w-full rounded border-gray-300" placeholder="Dejar en blanco para no cambiarla">
            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar nueva contraseña</label>
            <input type="password" wire:model="password_confirmation" class="w-full rounded border-gray-300">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select wire:model="status" class="w-full rounded border-gray-300">
                <option value="active">Activo</option>
                <option value="disabled">Deshabilitado</option>
            </select>
            @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <a href="{{ route('admin.usuarios.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700">
                Guardar cambios
            </button>
        </div>
    </form>
</div>