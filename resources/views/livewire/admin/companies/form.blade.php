<div class="p-6 max-w-xl">
    <h1 class="text-xl font-semibold mb-4">{{ $company ? 'Editar empresa' : 'Nueva empresa' }}</h1>

    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre comercial</label>
            <input type="text" wire:model="name" class="w-full rounded border-gray-300">
            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Razón social</label>
            <input type="text" wire:model="legal_name" class="w-full rounded border-gray-300">
            @error('legal_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
<label class="block text-sm font-medium text-gray-700 mb-1">Identificación fiscal (RIF / EIN) <span class="text-red-500">*</span></label>            <input type="text" wire:model="tax_id" class="w-full rounded border-gray-300">
            @error('tax_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select wire:model="status" class="w-full rounded border-gray-300">
                @foreach (\App\Models\Company::STATUSES as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            <p class="mt-1 text-xs text-gray-400">Una empresa inactiva o suspendida no puede recibir casos nuevos.</p>
        </div>

        @unless ($company)
            <p class="text-sm text-gray-500">
                Al crearla se generarán automáticamente los roles <strong>empresa</strong>,
                <strong>freelancer</strong> y <strong>freelancer-senior</strong>.
            </p>
        @endunless

        <div class="flex justify-end gap-2 pt-4">
            <a href="{{ route('admin.empresas.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</a>
            <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700">
                {{ $company ? 'Guardar cambios' : 'Crear empresa' }}
            </button>
        </div>
    </form>
</div>