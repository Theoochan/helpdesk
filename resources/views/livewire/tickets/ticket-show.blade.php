<div class="max-w-3xl mx-auto space-y-6">

    {{-- Cabeçalho do chamado --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-400 mb-1">#{{ $ticket->id }} • {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                <h1 class="text-xl font-semibold text-gray-900">{{ $ticket->title }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <x-badge :color="$ticket->statusColor()">{{ $ticket->statusLabel() }}</x-badge>
                    <x-badge :color="$ticket->priorityColor()">{{ $ticket->priorityLabel() }}</x-badge>
                    @if($ticket->category)
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
                            {{ $ticket->category->name }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Ações do TÉCNICO: assumir e resolver --}}
            @if(auth()->user()->isTechnician() && !$ticket->isTerminal())
                <div class="flex flex-col gap-2 shrink-0" x-data="{ showDue: false }">
                    @if($ticket->status === 'open')
                        <div x-show="!showDue">
                            <button @click="showDue = true"
                                    class="px-3 py-1.5 text-xs font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition-colors w-full">
                                Assumir Chamado
                            </button>
                        </div>
                        <div x-show="showDue" x-cloak class="border border-brand-200 bg-brand-50 rounded-lg p-3 text-xs space-y-2 min-w-[200px]">
                            <p class="text-brand-700 font-medium">Prazo (opcional)</p>
                            <input wire:model="dueDate" type="date"
                                   min="{{ now()->toDateString() }}"
                                   class="block w-full rounded border-gray-300 text-xs focus:border-brand-500 focus:ring-brand-500">
                            <div class="flex gap-2">
                                <button wire:click="assign" wire:loading.attr="disabled"
                                        class="flex-1 py-1.5 text-white bg-brand-600 hover:bg-brand-700 rounded font-medium transition-colors">
                                    Confirmar
                                </button>
                                <button @click="showDue = false; $wire.dueDate = ''"
                                        class="flex-1 py-1.5 text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 rounded font-medium transition-colors">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    @endif
                    @if($ticket->status === 'in_progress')
                        <button wire:click="resolve" wire:loading.attr="disabled"
                                class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
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
                                class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
                            ✓ Confirmar e Fechar
                        </button>
                    @endif

                    {{-- Cancelar: de qualquer status ativo, com confirmação Alpine --}}
                    <div>
                        <button @click="confirmCancel = true" x-show="!confirmCancel"
                                class="px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition-colors w-full">
                            Cancelar Chamado
                        </button>
                        <div x-show="confirmCancel" x-cloak class="border border-red-300 bg-red-50 rounded-lg p-3 text-xs space-y-2">
                            <p class="text-red-700 font-medium">Confirmar cancelamento?</p>
                            <div class="flex gap-2">
                                <button wire:click="cancel" wire:loading.attr="disabled"
                                        class="flex-1 py-1.5 text-white bg-red-600 hover:bg-red-700 rounded font-medium transition-colors">
                                    Sim, cancelar
                                </button>
                                <button @click="confirmCancel = false"
                                        class="flex-1 py-1.5 text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 rounded font-medium transition-colors">
                                    Não
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Metadados --}}
        <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 gap-3 text-sm">
            <div>
                <span class="text-gray-500">Solicitante:</span>
                <span class="font-medium text-gray-800 ml-1">{{ $ticket->user->name }}</span>
            </div>
            <div>
                <span class="text-gray-500">Técnico:</span>
                <span class="font-medium text-gray-800 ml-1">
                    {{ $ticket->technician ? $ticket->technician->name : '—' }}
                </span>
            </div>
            @php $dueBadge = $ticket->dueBadge(); @endphp
            @if($ticket->due_date || $ticket->technician_id)
                <div class="flex items-center gap-2">
                    <span class="text-gray-500">Prazo:</span>
                    @if($ticket->due_date)
                        <span class="font-medium text-gray-800">{{ $ticket->due_date->format('d/m/Y') }}</span>
                        @if($dueBadge)
                            <x-badge :color="$dueBadge['color']">{{ $dueBadge['label'] }}</x-badge>
                        @endif
                    @else
                        <span class="text-gray-400 text-xs">Sem prazo</span>
                    @endif
                    @if(auth()->user()->isTechnician() && !$ticket->isTerminal())
                        <span x-data="{ editing: false }" class="ml-1">
                            <button @click="editing = !editing" class="text-xs text-brand-500 hover:text-brand-700 underline">editar</button>
                            <span x-show="editing" x-cloak class="inline-flex items-center gap-1 ml-1">
                                <input wire:model="dueDate" type="date"
                                       class="rounded border-gray-300 text-xs py-0.5 focus:border-brand-500 focus:ring-brand-500">
                                <button wire:click="setDueDate" @click="editing = false"
                                        class="text-xs text-white bg-brand-600 hover:bg-brand-700 px-2 py-1 rounded">✓</button>
                                <button @click="editing = false; $wire.dueDate = '{{ $ticket->due_date?->format('Y-m-d') ?? '' }}'"
                                        class="text-xs text-gray-500 hover:text-gray-700 px-1 py-1">✕</button>
                            </span>
                        </span>
                    @endif
                </div>
            @endif
            @if($ticket->resolved_at)
                <div>
                    <span class="text-gray-500">Resolvido em:</span>
                    <span class="font-medium text-gray-800 ml-1">{{ $ticket->resolved_at->format('d/m/Y H:i') }}</span>
                </div>
            @endif
            @if($ticket->closed_at)
                <div>
                    <span class="text-gray-500">
                        {{ $ticket->status === 'cancelled' ? 'Cancelado em:' : 'Fechado em:' }}
                    </span>
                    <span class="font-medium text-gray-800 ml-1">{{ $ticket->closed_at->format('d/m/Y H:i') }}</span>
                </div>
            @endif
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-sm font-medium text-gray-700 mb-2">Descrição</p>
            <div class="prose prose-sm text-gray-600 max-w-none">
                {!! nl2br(e($ticket->description)) !!}
            </div>
        </div>
    </div>

    {{-- Banner informativo para status terminais --}}
    @if($ticket->isTerminal())
        <div class="rounded-xl px-5 py-4 text-sm font-medium flex items-center gap-3
            {{ $ticket->status === 'cancelled' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-gray-100 border border-gray-200 text-gray-600' }}">
            <span class="text-lg">{{ $ticket->status === 'cancelled' ? '🚫' : '🔒' }}</span>
            {{ $ticket->status === 'cancelled'
                ? 'Este chamado foi cancelado. Nenhuma ação adicional é possível.'
                : 'Este chamado está fechado. Nenhuma ação adicional é possível.' }}
        </div>
    @endif

    {{-- Comentários --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">
            Comentários ({{ $comments->count() }})
        </h2>

        @if($comments->isEmpty())
            <p class="text-sm text-gray-400 py-4 text-center">Nenhum comentário ainda.</p>
        @else
            <div class="space-y-4">
                @foreach($comments as $comment)
                    @if($comment->is_internal && auth()->user()->isCollaborator())
                        @continue
                    @endif
                    <div class="flex gap-3 {{ $comment->is_internal ? 'bg-amber-50 border border-amber-200 rounded-lg p-3' : '' }}">
                        <div class="h-8 w-8 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</span>
                                @if($comment->is_internal)
                                    <span class="text-xs text-amber-600 font-medium">[Nota Interna]</span>
                                @endif
                                <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-700">{!! nl2br(e($comment->body)) !!}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Formulário de comentário — bloqueado em status terminais --}}
        @if(!$ticket->isTerminal())
            <form wire:submit="addComment" class="mt-5 pt-4 border-t border-gray-100 space-y-3">
                <textarea wire:model="commentBody" rows="3"
                          class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"
                          placeholder="Escreva um comentário..."></textarea>
                @error('commentBody')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror

                @if(auth()->user()->isTechnician())
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input wire:model="isInternal" type="checkbox"
                               class="rounded border-gray-300 text-amber-500 focus:ring-amber-400">
                        Nota interna (somente técnicos veem)
                    </label>
                @endif

                <div class="flex justify-end">
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition-colors"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>Comentar</span>
                        <span wire:loading>Enviando...</span>
                    </button>
                </div>
            </form>
        @endif
    </div>

    <div class="text-center">
        <a href="{{ route('tickets.index') }}" class="text-sm text-brand-600 hover:text-brand-700">
            ← Voltar para lista
        </a>
    </div>
</div>
