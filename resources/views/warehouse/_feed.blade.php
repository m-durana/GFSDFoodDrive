@if($transactions->isEmpty())
    <p class="text-sm text-gray-500 dark:text-gray-400">No transactions recorded yet.</p>
@else
    <div class="space-y-2">
        @foreach($transactions as $txn)
            <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700/50 text-sm">
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                        {{ $txn->transaction_type->color() === 'green' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : '' }}
                        {{ $txn->transaction_type->color() === 'red' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                        {{ $txn->transaction_type->color() === 'blue' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                        {{ $txn->transaction_type->color() === 'yellow' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}
                    ">{{ $txn->transaction_type->label() }}</span>
                    <span class="text-gray-900 dark:text-gray-100">
                        {{ $txn->quantity }}x {{ $txn->category->name }}
                        @if($txn->item) ({{ $txn->item->name }}) @endif
                    </span>
                    @if($txn->source)
                        <span class="text-gray-400 dark:text-gray-500">via {{ $txn->source }}</span>
                    @endif
                </div>
                <div class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                    {{ $txn->scanned_at?->diffForHumans() ?? $txn->created_at->diffForHumans() }}
                    @if($txn->scanner) &middot; {{ $txn->scanner->first_name }} @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
