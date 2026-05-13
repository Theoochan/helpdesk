<div>
    <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">Criar Conta</h2>

    <form wire:submit="register" class="space-y-5">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome completo</label>
            <input wire:model="name" id="name" type="text" autocomplete="name"
                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"
                   placeholder="João da Silva">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

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
            <input wire:model="password" id="password" type="password"
                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha</label>
            <input wire:model="password_confirmation" id="password_confirmation" type="password"
                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
        </div>

        <button type="submit"
                class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 transition-colors"
                wire:loading.attr="disabled" wire:loading.class="opacity-75">
            <span wire:loading.remove>Criar Conta</span>
            <span wire:loading>Criando...</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
        Já tem conta?
        <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-500">
            Entrar
        </a>
    </p>
</div>
