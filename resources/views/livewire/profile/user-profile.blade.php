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
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Senha atual</span>
                            </label>
                            <input wire:model="currentPassword" type="password"
                                   class="input input-bordered w-full"
                                   placeholder="Digite sua senha atual"
                                   autofocus>
                            @error('currentPassword')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Nova senha</span>
                            </label>
                            <input wire:model="newPassword" type="password"
                                   class="input input-bordered w-full"
                                   placeholder="Mínimo 8 caracteres">
                            @error('newPassword')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Confirmar nova senha</span>
                            </label>
                            <input wire:model="newPasswordConfirmation" type="password"
                                   class="input input-bordered w-full"
                                   placeholder="Repita a nova senha">
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
