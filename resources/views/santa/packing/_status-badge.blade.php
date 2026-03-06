@php
    $badgeColors = [
        'pending' => 'bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300',
        'packed' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        'verified' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        'substituted' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
        'unfulfilled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    ];
@endphp
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $badgeColors[$status->value] ?? $badgeColors['pending'] }}">
    {{ $status->label() }}
</span>
