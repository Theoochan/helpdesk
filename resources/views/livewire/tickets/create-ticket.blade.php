<div class="max-w-2xl mx-auto">
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h1 class="card-title text-xl mb-2">Abrir Novo Chamado</h1>

            <form wire:submit="save" class="space-y-5">
                <div class="form-control">
                    <label class="label" for="title">
                        <span class="label-text font-medium">Título <span class="text-error">*</span></span>
                    </label>
                    <input wire:model="title" id="title" type="text"
                           class="input input-bordered w-full"
                           placeholder="Ex: Computador não liga">
                    @error('title')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label" for="category_id">
                            <span class="label-text font-medium">Categoria</span>
                        </label>
                        <select wire:model="category_id" id="category_id" class="select select-bordered w-full">
                            <option value="">— Selecione —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label" for="priority">
                            <span class="label-text font-medium">Prioridade</span>
                        </label>
                        <select wire:model="priority" id="priority" class="select select-bordered w-full">
                            <option value="low">Baixa</option>
                            <option value="medium">Média</option>
                            <option value="high">Alta</option>
                        </select>
                    </div>
                </div>

                <div class="form-control">
                    <label class="label" for="description">
                        <span class="label-text font-medium">Descrição detalhada <span class="text-error">*</span></span>
                    </label>
                    <textarea wire:model="description" id="description" rows="6"
                              class="textarea textarea-bordered w-full"
                              placeholder="Descreva o problema com o máximo de detalhes possível..."></textarea>
                    @error('description')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-ghost btn-sm">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>Abrir Chamado</span>
                        <span wire:loading class="loading loading-spinner loading-sm"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
