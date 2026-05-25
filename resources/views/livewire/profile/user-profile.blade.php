<div>
    {{-- ─── Modal: Editar Nome ──────────────────────────────────────────────── --}}
    @if($modal === 'name')
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             x-data x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="card bg-base-100 shadow-xl w-full max-w-sm" @click.stop>
                <div class="card-body">
                    <h3 class="card-title text-base">Editar nome</h3>

                    <form wire:submit="saveName" class="space-y-4 mt-1">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Nome</span>
                            </label>
                            <input wire:model="newName" type="text"
                                   class="input input-bordered w-full"
                                   placeholder="Seu nome completo"
                                   autofocus>
                            @error('newName')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" wire:click="closeModal"
                                    class="btn btn-ghost btn-sm">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove>Salvar</span>
                                <span wire:loading class="loading loading-spinner loading-sm"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            {{-- Fechar ao clicar fora --}}
            <div class="absolute inset-0 -z-10" wire:click="closeModal"></div>
        </div>
    @endif

    {{-- ─── Modal: Alterar Senha ────────────────────────────────────────────── --}}
    @if($modal === 'password')
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             x-data x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="card bg-base-100 shadow-xl w-full max-w-sm" @click.stop>
                <div class="card-body">
                    <h3 class="card-title text-base">Alterar senha</h3>

                    <form wire:submit="savePassword" class="space-y-4 mt-1">

                        {{-- Senha atual --}}
                        <div class="form-control" x-data="{ show: false }">
                            <label class="label">
                                <span class="label-text font-medium">Senha atual</span>
                            </label>
                            <div class="relative">
                                <input wire:model="currentPassword"
                                       :type="show ? 'text' : 'password'"
                                       class="input input-bordered w-full pr-10"
                                       placeholder="Digite sua senha atual"
                                       autofocus>
                                <button type="button" tabindex="-1" @click="show = !show"
                                        class="absolute inset-y-0 right-0 flex items-center px-3 text-base-content/40 hover:text-base-content transition-colors">
                                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            @error('currentPassword')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        {{-- Nova senha --}}
                        <div class="form-control" x-data="{ show: false }">
                            <label class="label">
                                <span class="label-text font-medium">Nova senha</span>
                            </label>
                            <div class="relative">
                                <input wire:model="newPassword"
                                       :type="show ? 'text' : 'password'"
                                       class="input input-bordered w-full pr-10"
                                       placeholder="Mínimo 8 caracteres">
                                <button type="button" tabindex="-1" @click="show = !show"
                                        class="absolute inset-y-0 right-0 flex items-center px-3 text-base-content/40 hover:text-base-content transition-colors">
                                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            @error('newPassword')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        {{-- Confirmar nova senha --}}
                        <div class="form-control" x-data="{ show: false }">
                            <label class="label">
                                <span class="label-text font-medium">Confirmar nova senha</span>
                            </label>
                            <div class="relative">
                                <input wire:model="newPasswordConfirmation"
                                       :type="show ? 'text' : 'password'"
                                       class="input input-bordered w-full pr-10"
                                       placeholder="Repita a nova senha">
                                <button type="button" tabindex="-1" @click="show = !show"
                                        class="absolute inset-y-0 right-0 flex items-center px-3 text-base-content/40 hover:text-base-content transition-colors">
                                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            @error('newPasswordConfirmation')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" wire:click="closeModal"
                                    class="btn btn-ghost btn-sm">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove>Alterar senha</span>
                                <span wire:loading class="loading loading-spinner loading-sm"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="absolute inset-0 -z-10" wire:click="closeModal"></div>
        </div>
    @endif
</div>
