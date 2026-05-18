<div>
    <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">Entrar no HelpDesk</h2>

    <form wire:submit="login" class="space-y-5">
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
            <input wire:model="email" id="email" type="email" autocomplete="email"
                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"
                   placeholder="voce@empresa.com">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
            <input wire:model="password" id="password" type="password" autocomplete="current-password"
                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center">
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input wire:model="remember" type="checkbox"
                       class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                Lembrar de mim
            </label>
        </div>

        <button type="submit"
                class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors"
                wire:loading.attr="disabled" wire:loading.class="opacity-75">
            <span wire:loading.remove>Entrar</span>
            <span wire:loading>Entrando...</span>
        </button>
    </form>
</div>
