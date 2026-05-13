<div class="max-w-3xl mx-auto space-y-6">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-400 mb-1">OS #{{ $order->id }} • {{ $order->created_at->format('d/m/Y H:i') }}</p>
                <h1 class="text-xl font-semibold text-gray-900">{{ $order->title }}</h1>
                <div class="flex flex-wrap gap-2 mt-2">
                    <x-badge :color="$order->statusColor()">{{ $order->statusLabel() }}</x-badge>
                    <x-badge :color="$order->priorityColor()">{{ $order->priorityLabel() }}</x-badge>
                </div>
            </div>

            {{-- Ações --}}
            <div class="flex flex-col gap-2 shrink-0">
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
            </div>
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

        @if($order->status !== 'done' && auth()->user()->can('comment', $order))
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
