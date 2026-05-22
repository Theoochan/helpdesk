<div class="max-w-3xl mx-auto space-y-6">

    {{-- Cabeçalho do chamado --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-base-content/40 mb-1">#{{ $ticket->id }} • {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                    <h1 class="text-xl font-semibold">{{ $ticket->title }}</h1>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <x-badge :color="$ticket->statusColor()">{{ $ticket->statusLabel() }}</x-badge>
                        <x-badge :color="$ticket->priorityColor()">{{ $ticket->priorityLabel() }}</x-badge>
                        @if($ticket->category)
                            <span class="badge badge-sm badge-ghost">{{ $ticket->category->name }}</span>
                        @endif
                    </div>
                </div>

                {{-- Ações do TÉCNICO: assumir e resolver --}}
                @if(auth()->user()->isTechnician() && !$ticket->isTerminal())
                    <div class="flex flex-col gap-2 shrink-0" x-data="{ showDue: false }">
                        @if($ticket->status === 'open')
                            <div x-show="!showDue">
                                <button @click="showDue = true" class="btn btn-primary btn-sm">
                                    Assumir Chamado
                                </button>
                            </div>
                            <div x-show="showDue" x-cloak
                                 class="border border-primary/30 bg-primary/5 rounded-lg p-3 text-xs space-y-2 min-w-[200px]">
                                <p class="text-primary font-medium">Prazo (opcional)</p>
                                <input wire:model="dueDate" type="date"
                                       min="{{ now()->toDateString() }}"
                                       class="input input-bordered input-xs w-full">
                                <div class="flex gap-2">
                                    <button wire:click="assign" wire:loading.attr="disabled"
                                            class="btn btn-primary btn-xs flex-1">
                                        Confirmar
                                    </button>
                                    <button @click="showDue = false; $wire.dueDate = ''"
                                            class="btn btn-ghost btn-xs flex-1">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        @endif
                        @if($ticket->status === 'in_progress')
                            <button wire:click="resolve" wire:loading.attr="disabled"
                                    class="btn btn-success btn-sm">
                                Marcar Resolvido
                            </button>
                        @endif
                    </div>
                @endif

                {{-- Ações do COLABORADOR dono --}}
                @if(auth()->user()->isCollaborator() && $ticket->user_id === auth()->id() && !$ticket->isTerminal())
                    <div class="flex flex-col gap-2 shrink-0" x-data="{ confirmCancel: false }">

                        {{-- Fechar: apenas quando resolvido --}}
                        @if($ticket->status === 'resolved')
                            <button wire:click="close" wire:loading.attr="disabled"
                                    class="btn btn-success btn-sm">
                                ✓ Confirmar e Fechar
                            </button>
                        @endif

                        {{-- Cancelar: com confirmação Alpine --}}
                        <div>
                            <button @click="confirmCancel = true" x-show="!confirmCancel"
                                    class="btn btn-error btn-outline btn-sm w-full">
                                Cancelar Chamado
                            </button>
                            <div x-show="confirmCancel" x-cloak
                                 class="border border-error/30 bg-error/5 rounded-lg p-3 text-xs space-y-2">
                                <p class="text-error font-medium">Confirmar cancelamento?</p>
                                <div class="flex gap-2">
                                    <button wire:click="cancel" wire:loading.attr="disabled"
                                            class="btn btn-error btn-xs flex-1">
                                        Sim, cancelar
                                    </button>
                                    <button @click="confirmCancel = false"
                                            class="btn btn-ghost btn-xs flex-1">
                                        Não
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Metadados --}}
            <div class="mt-4 pt-4 border-t border-base-200 grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-base-content/50">Solicitante:</span>
                    <span class="font-medium ml-1">{{ $ticket->user->name }}</span>
                </div>
                <div>
                    <span class="text-base-content/50">Técnico:</span>
                    <span class="font-medium ml-1">{{ $ticket->technician?->name ?? '—' }}</span>
                </div>
                @php $dueBadge = $ticket->dueBadge(); @endphp
                @if($ticket->due_date || $ticket->technician_id)
                    <div class="flex items-center gap-2">
                        <span class="text-base-content/50">Prazo:</span>
                        @if($ticket->due_date)
                            <span class="font-medium">{{ $ticket->due_date->format('d/m/Y') }}</span>
                            @if($dueBadge)
                                <x-badge :color="$dueBadge['color']">{{ $dueBadge['label'] }}</x-badge>
                            @endif
                        @else
                            <span class="text-base-content/30 text-xs">Sem prazo</span>
                        @endif
                        @if(auth()->user()->isTechnician() && !$ticket->isTerminal())
                            <span x-data="{ editing: false }" class="ml-1">
                                <button @click="editing = !editing"
                                        class="text-xs text-primary hover:underline">editar</button>
                                <span x-show="editing" x-cloak class="inline-flex items-center gap-1 ml-1">
                                    <input wire:model="dueDate" type="date"
                                           class="input input-bordered input-xs">
                                    <button wire:click="setDueDate" @click="editing = false"
                                            class="btn btn-primary btn-xs">✓</button>
                                    <button @click="editing = false; $wire.dueDate = '{{ $ticket->due_date?->format('Y-m-d') ?? '' }}'"
                                            class="btn btn-ghost btn-xs">✕</button>
                                </span>
                            </span>
                        @endif
                    </div>
                @endif
                @if($ticket->resolved_at)
                    <div>
                        <span class="text-base-content/50">Resolvido em:</span>
                        <span class="font-medium ml-1">{{ $ticket->resolved_at->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
                @if($ticket->closed_at)
                    <div>
                        <span class="text-base-content/50">
                            {{ $ticket->status === 'cancelled' ? 'Cancelado em:' : 'Fechado em:' }}
                        </span>
                        <span class="font-medium ml-1">{{ $ticket->closed_at->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
            </div>

            <div class="mt-4 pt-4 border-t border-base-200">
                <p class="text-sm font-medium mb-2">Descrição</p>
                <div class="text-sm text-base-content/70 leading-relaxed">
                    {!! nl2br(e($ticket->description)) !!}
                </div>
            </div>
        </div>
    </div>

    {{-- Banner informativo para status terminais --}}
    @if($ticket->isTerminal())
        <div role="alert"
             class="alert {{ $ticket->status === 'cancelled' ? 'alert-error' : 'alert-neutral' }} text-sm">
            <span>{{ $ticket->status === 'cancelled' ? '🚫' : '🔒' }}</span>
            <span>{{ $ticket->status === 'cancelled'
                ? 'Este chamado foi cancelado. Nenhuma ação adicional é possível.'
                : 'Este chamado está fechado. Nenhuma ação adicional é possível.' }}</span>
        </div>
    @endif

    {{-- Comentários --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-base">
                Comentários ({{ $comments->count() }})
            </h2>

            @if($comments->isEmpty())
                <p class="text-sm text-base-content/40 py-4 text-center">Nenhum comentário ainda.</p>
            @else
                <div class="space-y-4">
                    @foreach($comments as $comment)
                        @if($comment->is_internal && auth()->user()->isCollaborator())
                            @continue
                        @endif
                        <div class="flex gap-3 {{ $comment->is_internal ? 'bg-warning/10 border border-warning/30 rounded-lg p-3' : '' }}">
                            <div class="avatar placeholder shrink-0">
                                <div class="bg-primary text-primary-content rounded-full w-8 h-8 text-xs font-bold">
                                    <span>{{ strtoupper(substr($comment->user->name, 0, 1)) }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm font-medium">{{ $comment->user->name }}</span>
                                    @if($comment->is_internal)
                                        <span class="badge badge-sm badge-warning">Nota Interna</span>
                                    @endif
                                    <span class="text-xs text-base-content/40">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-base-content/80">{!! nl2br(e($comment->body)) !!}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Formulário de comentário --}}
            @if(!$ticket->isTerminal())
                <form wire:submit="addComment" class="mt-5 pt-4 border-t border-base-200 space-y-3">
                    <div class="form-control">
                        <textarea wire:model="commentBody" rows="3"
                                  class="textarea textarea-bordered w-full"
                                  placeholder="Escreva um comentário..."></textarea>
                        @error('commentBody')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    @if(auth()->user()->isTechnician())
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input wire:model="isInternal" type="checkbox"
                                   class="checkbox checkbox-warning checkbox-sm">
                            <span class="label-text">Nota interna (somente técnicos veem)</span>
                        </label>
                    @endif

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary btn-sm"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove>Comentar</span>
                            <span wire:loading class="loading loading-spinner loading-sm"></span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div class="text-center">
        <a href="{{ route('tickets.index') }}" class="btn btn-ghost btn-sm">
            ← Voltar para lista
        </a>
    </div>
</div>
