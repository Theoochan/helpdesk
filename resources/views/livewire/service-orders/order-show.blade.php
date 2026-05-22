<div class="max-w-3xl mx-auto space-y-6">

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-base-content/40 mb-1">OS #{{ $order->id }} • {{ $order->created_at->format('d/m/Y H:i') }}</p>
                    <h1 class="text-xl font-semibold">{{ $order->title }}</h1>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <x-badge :color="$order->statusColor()">{{ $order->statusLabel() }}</x-badge>
                        <x-badge :color="$order->priorityColor()">{{ $order->priorityLabel() }}</x-badge>
                        @php $dueBadge = $order->dueBadge(); @endphp
                        @if($dueBadge)
                            <x-badge :color="$dueBadge['color']">{{ $dueBadge['label'] }}</x-badge>
                        @endif
                    </div>
                </div>

                {{-- Ações --}}
                @if(!$order->isTerminal())
                    <div class="flex flex-col gap-2 shrink-0" x-data="{ confirmCancel: false }">
                        @if($order->status === 'pending' && auth()->user()->can('start', $order))
                            <button wire:click="start" wire:loading.attr="disabled"
                                    class="btn btn-primary btn-sm">
                                Iniciar OS
                            </button>
                        @endif
                        @if($order->status === 'in_progress' && auth()->user()->can('finish', $order))
                            <button wire:click="finish" wire:loading.attr="disabled"
                                    class="btn btn-success btn-sm">
                                Finalizar OS
                            </button>
                        @endif
                        @if(auth()->user()->can('cancel', $order))
                            <div>
                                <button @click="confirmCancel = true" x-show="!confirmCancel"
                                        class="btn btn-error btn-outline btn-sm w-full">
                                    Cancelar OS
                                </button>
                                <div x-show="confirmCancel" x-cloak
                                     class="border border-error/30 bg-error/5 rounded-lg p-3 text-xs space-y-2">
                                    <p class="text-error font-medium">Confirmar cancelamento?</p>
                                    <div class="flex gap-2">
                                        <button wire:click="cancel" wire:loading.attr="disabled"
                                                class="btn btn-error btn-xs flex-1">
                                            Sim
                                        </button>
                                        <button @click="confirmCancel = false"
                                                class="btn btn-ghost btn-xs flex-1">
                                            Não
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="mt-4 pt-4 border-t border-base-200 grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-base-content/50">Solicitante:</span>
                    <span class="font-medium ml-1">{{ $order->requester->name }}</span>
                </div>
                <div>
                    <span class="text-base-content/50">Responsável:</span>
                    <span class="font-medium ml-1">{{ $order->assignedTo->name }}</span>
                </div>
                @if($order->due_date)
                    <div>
                        <span class="text-base-content/50">Prazo:</span>
                        <span class="font-medium ml-1">{{ $order->due_date->format('d/m/Y') }}</span>
                    </div>
                @endif
                @if($order->done_at)
                    <div>
                        <span class="text-base-content/50">Finalizada em:</span>
                        <span class="font-medium ml-1">{{ $order->done_at->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
            </div>

            <div class="mt-4 pt-4 border-t border-base-200">
                <p class="text-sm font-medium mb-2">Descrição</p>
                <div class="text-sm text-base-content/70 leading-relaxed">
                    {!! nl2br(e($order->description)) !!}
                </div>
            </div>
        </div>
    </div>

    {{-- Banner status terminal --}}
    @if($order->isTerminal())
        <div role="alert"
             class="alert {{ $order->status === 'cancelled' ? 'alert-error' : 'alert-neutral' }} text-sm">
            <span>{{ $order->status === 'cancelled' ? '🚫' : '🔒' }}</span>
            <span>{{ $order->status === 'cancelled' ? 'Esta OS foi cancelada.' : 'Esta OS foi finalizada.' }}</span>
        </div>
    @endif

    {{-- ─── Transferência de responsabilidade ─────────────────────────────── --}}
    @if(!$order->isTerminal())

        {{-- Solicitação pendente → admin aprova/rejeita --}}
        @if($order->hasPendingTransfer())
            <div class="card bg-secondary/10 border border-secondary/30 shadow-sm">
                <div class="card-body gap-3">
                    <p class="font-semibold text-secondary">Solicitação de transferência pendente</p>
                    <p class="text-sm">
                        <span class="font-medium">{{ $order->assignedTo->name }}</span>
                        solicitou transferir para
                        <span class="font-medium">{{ $order->transferRequestedTo->name }}</span>.
                    </p>
                    @if($order->transfer_note)
                        <p class="text-xs text-base-content/60 italic">"{{ $order->transfer_note }}"</p>
                    @endif
                    @if(auth()->user()->can('manageTransfer', $order))
                        <div class="flex gap-2 pt-1">
                            <button wire:click="approveTransfer"
                                    class="btn btn-secondary btn-sm">
                                ✓ Aprovar transferência
                            </button>
                            <button wire:click="rejectTransfer"
                                    class="btn btn-ghost btn-sm">
                                ✕ Rejeitar
                            </button>
                        </div>
                    @else
                        <p class="text-xs text-base-content/50">Aguardando aprovação do admin.</p>
                    @endif
                </div>
            </div>

        {{-- Botão de solicitar transferência → aparece para o responsável --}}
        @elseif(auth()->user()->can('requestTransfer', $order))
            <div class="card bg-base-100 shadow-sm"
                 x-data="{ open: @entangle('showTransferForm') }">
                <div class="card-body">
                    <button @click="open = !open"
                            class="flex items-center gap-2 text-sm font-medium text-base-content/70 hover:text-primary">
                        <span>↔ Solicitar transferência de responsabilidade</span>
                        <svg class="h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open" x-cloak class="mt-4 space-y-3">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text text-xs font-medium">Transferir para</span>
                            </label>
                            <select wire:model="transferTo" class="select select-bordered select-sm w-full">
                                <option value="">Selecionar técnico...</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">
                                        {{ $tech->name }} ({{ $tech->isAdmin() ? 'Admin' : 'Técnico' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('transferTo')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text text-xs font-medium">Motivo</span>
                                <span class="label-text-alt text-base-content/40">opcional</span>
                            </label>
                            <textarea wire:model="transferNote" rows="2"
                                      class="textarea textarea-bordered textarea-sm w-full"
                                      placeholder="Ex: estarei de férias, especialidade necessária..."></textarea>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="requestTransfer" class="btn btn-primary btn-sm">
                                Enviar solicitação
                            </button>
                            <button @click="open = false" class="btn btn-ghost btn-sm">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- Comentários --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-base">Comentários ({{ $comments->count() }})</h2>

            @if($comments->isEmpty())
                <p class="text-sm text-base-content/40 py-4 text-center">Nenhum comentário ainda.</p>
            @else
                <div class="space-y-4">
                    @foreach($comments as $comment)
                        <div class="flex gap-3">
                            <div class="avatar placeholder shrink-0">
                                <div class="bg-primary text-primary-content rounded-full w-8 h-8 text-xs font-bold">
                                    <span>{{ strtoupper(substr($comment->user->name, 0, 1)) }}</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm font-medium">{{ $comment->user->name }}</span>
                                    <span class="text-xs text-base-content/40">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-base-content/80">{!! nl2br(e($comment->body)) !!}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if(!$order->isTerminal() && auth()->user()->can('comment', $order))
                <form wire:submit="addComment" class="mt-5 pt-4 border-t border-base-200 space-y-3">
                    <div class="form-control">
                        <textarea wire:model="commentBody" rows="3"
                                  class="textarea textarea-bordered w-full"
                                  placeholder="Adicionar comentário..."></textarea>
                        @error('commentBody')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>
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
        <a href="{{ route('orders.index') }}" class="btn btn-ghost btn-sm">
            ← Voltar para OS Internas
        </a>
    </div>
</div>
