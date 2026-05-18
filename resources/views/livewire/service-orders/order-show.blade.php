<div class="max-w-3xl mx-auto space-y-6">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-400 mb-1">OS #{{ $order->id }} • {{ $order->created_at->format('d/m/Y H:i') }}</p>
                <h1 class="text-xl font-semibold text-gray-900">{{ $order->title }}</h1>
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
                                class="px-3 py-1.5 text-xs font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition-colors">
                            Iniciar OS
                        </button>
                    @endif
                    @if($order->status === 'in_progress' && auth()->user()->can('finish', $order))
                        <button wire:click="finish" wire:loading.attr="disabled"
                                class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
                            Finalizar OS
                        </button>
                    @endif
                    @if(auth()->user()->can('cancel', $order))
                        <div>
                            <button @click="confirmCancel = true" x-show="!confirmCancel"
                                    class="px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition-colors w-full">
                                Cancelar OS
                            </button>
                            <div x-show="confirmCancel" x-cloak class="border border-red-300 bg-red-50 rounded-lg p-3 text-xs space-y-2">
                                <p class="text-red-700 font-medium">Confirmar cancelamento?</p>
                                <div class="flex gap-2">
                                    <button wire:click="cancel" wire:loading.attr="disabled"
                                            class="flex-1 py-1.5 text-white bg-red-600 hover:bg-red-700 rounded font-medium transition-colors">
                                        Sim
                                    </button>
                                    <button @click="confirmCancel = false"
                                            class="flex-1 py-1.5 text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 rounded font-medium transition-colors">
                                        Não
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 gap-3 text-sm">
            <div>
                <span class="text-gray-500">Solicitante:</span>
                <span class="font-medium text-gray-800 ml-1">{{ $order->requester->name }}</span>
            </div>
            <div>
                <span class="text-gray-500">Responsável:</span>
                <span class="font-medium text-gray-800 ml-1">{{ $order->assignedTo->name }}</span>
            </div>
            @if($order->due_date)
                <div>
                    <span class="text-gray-500">Prazo:</span>
                    <span class="font-medium text-gray-800 ml-1">{{ $order->due_date->format('d/m/Y') }}</span>
                </div>
            @endif
            @if($order->done_at)
                <div>
                    <span class="text-gray-500">Finalizada em:</span>
                    <span class="font-medium text-gray-800 ml-1">{{ $order->done_at->format('d/m/Y H:i') }}</span>
                </div>
            @endif
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-sm font-medium text-gray-700 mb-2">Descrição</p>
            <div class="text-sm text-gray-600">{!! nl2br(e($order->description)) !!}</div>
        </div>
    </div>

    {{-- Banner status terminal --}}
    @if($order->isTerminal())
        <div class="rounded-xl px-5 py-4 text-sm font-medium flex items-center gap-3
            {{ $order->status === 'cancelled' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-gray-100 border border-gray-200 text-gray-600' }}">
            <span class="text-lg">{{ $order->status === 'cancelled' ? '🚫' : '🔒' }}</span>
            {{ $order->status === 'cancelled' ? 'Esta OS foi cancelada.' : 'Esta OS foi finalizada.' }}
        </div>
    @endif

    {{-- ─── Transferência de responsabilidade ─────────────────────────────── --}}
    @if(!$order->isTerminal())

        {{-- Solicitação pendente → admin aprova/rejeita --}}
        @if($order->hasPendingTransfer())
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-5 space-y-3">
                <p class="text-sm font-semibold text-purple-800">Solicitação de transferência pendente</p>
                <p class="text-sm text-purple-700">
                    <span class="font-medium">{{ $order->assignedTo->name }}</span>
                    solicitou transferir para
                    <span class="font-medium">{{ $order->transferRequestedTo->name }}</span>.
                </p>
                @if($order->transfer_note)
                    <p class="text-xs text-purple-600 italic">"{{ $order->transfer_note }}"</p>
                @endif
                @if(auth()->user()->can('manageTransfer', $order))
                    <div class="flex gap-2 pt-1">
                        <button wire:click="approveTransfer"
                                class="px-4 py-1.5 text-xs font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors">
                            ✓ Aprovar transferência
                        </button>
                        <button wire:click="rejectTransfer"
                                class="px-4 py-1.5 text-xs font-medium text-purple-700 bg-white border border-purple-300 hover:bg-purple-50 rounded-lg transition-colors">
                            ✕ Rejeitar
                        </button>
                    </div>
                @else
                    <p class="text-xs text-purple-500">Aguardando aprovação do admin.</p>
                @endif
            </div>

        {{-- Botão de solicitar transferência → aparece para o responsável --}}
        @elseif(auth()->user()->can('requestTransfer', $order))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5"
                 x-data="{ open: @entangle('showTransferForm') }">
                <button @click="open = !open"
                        class="text-sm font-medium text-gray-600 hover:text-brand-700 flex items-center gap-2">
                    <span>↔ Solicitar transferência de responsabilidade</span>
                    <svg class="h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" x-cloak class="mt-4 space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Transferir para</label>
                        <select wire:model="transferTo"
                                class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Selecionar técnico...</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }} ({{ $tech->isAdmin() ? 'Admin' : 'Técnico' }})</option>
                            @endforeach
                        </select>
                        @error('transferTo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Motivo <span class="text-gray-400">(opcional)</span></label>
                        <textarea wire:model="transferNote" rows="2"
                                  class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500"
                                  placeholder="Ex: estarei de férias, especialidade necessária..."></textarea>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="requestTransfer"
                                class="px-4 py-2 text-xs font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition-colors">
                            Enviar solicitação
                        </button>
                        <button @click="open = false"
                                class="px-4 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- Comentários --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Comentários ({{ $comments->count() }})</h2>

        @if($comments->isEmpty())
            <p class="text-sm text-gray-400 py-4 text-center">Nenhum comentário ainda.</p>
        @else
            <div class="space-y-4">
                @foreach($comments as $comment)
                    <div class="flex gap-3">
                        <div class="h-8 w-8 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</span>
                                <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-700">{!! nl2br(e($comment->body)) !!}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if(!$order->isTerminal() && auth()->user()->can('comment', $order))
            <form wire:submit="addComment" class="mt-5 pt-4 border-t border-gray-100 space-y-3">
                <textarea wire:model="commentBody" rows="3"
                          class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"
                          placeholder="Adicionar comentário..."></textarea>
                @error('commentBody') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
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
        <a href="{{ route('orders.index') }}" class="text-sm text-brand-600 hover:text-brand-700">
            ← Voltar para OS Internas
        </a>
    </div>
</div>
