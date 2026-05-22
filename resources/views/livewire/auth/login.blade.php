<div>
    <h2 class="card-title justify-center text-xl mb-6">Entrar no HelpDesk</h2>

    <form wire:submit="login" class="space-y-4">
        <div class="form-control">
            <label class="label" for="email">
                <span class="label-text font-medium">E-mail</span>
            </label>
            <input wire:model="email" id="email" type="email" autocomplete="email"
                   class="input input-bordered w-full"
                   placeholder="voce@empresa.com">
            @error('email')
                <label class="label">
                    <span class="label-text-alt text-error">{{ $message }}</span>
                </label>
            @enderror
        </div>

        <div class="form-control">
            <label class="label" for="password">
                <span class="label-text font-medium">Senha</span>
            </label>
            <input wire:model="password" id="password" type="password" autocomplete="current-password"
                   class="input input-bordered w-full">
            @error('password')
                <label class="label">
                    <span class="label-text-alt text-error">{{ $message }}</span>
                </label>
            @enderror
        </div>

        <div class="form-control">
            <label class="label cursor-pointer justify-start gap-3">
                <input wire:model="remember" type="checkbox" class="checkbox checkbox-primary checkbox-sm">
                <span class="label-text">Lembrar de mim</span>
            </label>
        </div>

        <div class="form-control mt-2">
            <button type="submit" class="btn btn-primary w-full"
                    wire:loading.attr="disabled">
                <span wire:loading.remove>Entrar</span>
                <span wire:loading class="loading loading-spinner loading-sm"></span>
            </button>
        </div>
    </form>
</div>
