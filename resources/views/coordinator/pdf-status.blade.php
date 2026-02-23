<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            PDF Generation — {{ ucfirst(str_replace('-', ' ', $status['type'])) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="mb-6">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <span>{{ $status['completed'] }} of {{ $status['total_batches'] }} batches complete</span>
                        <span>{{ $status['total'] }} total items</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        @php $pct = $status['total_batches'] > 0 ? round(($status['completed'] / $status['total_batches']) * 100) : 0; @endphp
                        <div class="bg-red-600 h-3 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach($status['batches'] as $num => $batch)
                        <div class="flex items-center justify-between p-3 rounded-lg {{ $batch['status'] === 'completed' ? 'bg-green-50 dark:bg-green-900/20' : 'bg-gray-50 dark:bg-gray-700' }}">
                            <div class="flex items-center space-x-3">
                                @if($batch['status'] === 'completed')
                                    <span class="text-green-600 dark:text-green-400 font-bold">&#10003;</span>
                                @else
                                    <span class="text-gray-400 animate-pulse">&#9679;</span>
                                @endif
                                <span class="text-sm text-gray-900 dark:text-gray-100">Batch {{ $num }}</span>
                            </div>
                            @if($batch['status'] === 'completed')
                                <a href="{{ route('coordinator.pdfDownload', [$batchId, $num]) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-red-700 text-white rounded-md hover:bg-red-600 text-xs font-medium transition">
                                    Download PDF
                                </a>
                            @else
                                <span class="text-xs text-gray-500 dark:text-gray-400">Processing...</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($status['completed'] < $status['total_batches'])
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                        Page refreshes automatically every 5 seconds.
                    </p>
                @else
                    <p class="mt-4 text-sm text-green-600 dark:text-green-400 text-center font-medium">
                        All batches complete! Download above.
                    </p>
                @endif

                <div class="mt-6 text-center">
                    <a href="{{ route('coordinator.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                        &larr; Back to Coordinator Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($status['completed'] < $status['total_batches'])
        <script>
            setTimeout(function() { window.location.reload(); }, 5000);
        </script>
    @endif
</x-app-layout>
