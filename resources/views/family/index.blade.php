<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            Family Entry Dashboard
            <x-hint key="family-dashboard" text="Add families and their children here. Mark a family 'Done' when all info is complete. Santa users can see all families across coordinators." />
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card bg-base-100 shadow-sm border border-base-300">
                <div class="card-body">
                    <h3 class="text-lg font-medium mb-4">Welcome, {{ auth()->user()->first_name }}!</h3>

                    @if($families->count() > 0)
                        <div x-data="{ ...sortTable(), search: '' }">
                            <div class="flex items-center justify-between mb-6 gap-4">
                                <a href="{{ route('family.create') }}" class="btn btn-primary whitespace-nowrap">
                                    Add New Family
                                </a>
                                <input type="text" x-model="search" placeholder="Search by number, name, or phone..."
                                    class="input input-bordered w-full sm:w-80">
                            </div>

                            <h4 class="font-medium text-base-content/80 mb-2">My Families</h4>
                            <div class="overflow-x-auto">
                                <table class="table table-zebra w-full">
                                    <thead>
                                        <tr>
                                            <x-sort-th key="family_number">Family #</x-sort-th>
                                            <x-sort-th key="family_name">Family Name</x-sort-th>
                                            <x-sort-th key="address">Address</x-sort-th>
                                            <x-sort-th key="phone">Phone</x-sort-th>
                                            @if((auth()->user()->isCoordinator() || auth()->user()->isSanta()) && \App\Models\Setting::get('packing_system_enabled', '1') === '1')
                                                <th>Packing</th>
                                            @endif
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $viewer = auth()->user();
                                            $viewerCanSeePii = $viewer?->canSeePii() ?? false;
                                        @endphp
                                        @foreach($families as $family)
                                            @php
                                                // Advisor exception: see names for families they entered themselves.
                                                $showPii = $viewerCanSeePii || ($viewer && $viewer->isAdvisor() && $family->user_id === $viewer->id);
                                            @endphp
                                            <tr x-show="!search || '{{ $family->family_number }}'.includes(search) @if($showPii) || '{{ strtolower(addslashes($family->family_name)) }}'.includes(search.toLowerCase()) || '{{ $family->phone1 }}'.includes(search) @endif">
                                                <td data-sort-value="{{ $family->family_number ?? 0 }}">{{ $family->family_number ?? '-' }}</td>
                                                <td class="font-medium">{{ $showPii ? $family->family_name : '—' }}</td>
                                                <td>{{ $showPii ? $family->address : '—' }}</td>
                                                <td>{{ $showPii ? $family->phone1 : '—' }}</td>
                                                @if((auth()->user()->isCoordinator() || auth()->user()->isSanta()) && \App\Models\Setting::get('packing_system_enabled', '1') === '1')
                                                    <td>
                                                        @if($family->packingList)
                                                            @php
                                                                $packingProgress = $family->packingList->progressSummary();
                                                                $packingBadgeType = match($family->packingList->status->value) {
                                                                    'in_progress' => 'warning',
                                                                    'complete' => 'info',
                                                                    'verified' => 'success',
                                                                    default => 'ghost',
                                                                };
                                                            @endphp
                                                            <a href="{{ route('packing.show', $family->packingList) }}" class="inline-flex items-center gap-1">
                                                                <x-badge :type="$packingBadgeType" size="sm">{{ $family->packingList->status->label() }}</x-badge>
                                                                <span class="text-xs text-base-content/50">{{ $packingProgress['packed'] }}/{{ $packingProgress['total'] }}</span>
                                                            </a>
                                                        @else
                                                            <span class="text-xs text-base-content/50">-</span>
                                                        @endif
                                                    </td>
                                                @endif
                                                <td>
                                                    <a href="{{ route('family.show', $family) }}" class="link link-primary">View</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="flex space-x-4 mb-6">
                            <a href="{{ route('family.create') }}" class="btn btn-primary">
                                Add New Family
                            </a>
                        </div>
                        <p class="text-base-content/60">No families added yet. Click "Add New Family" to get started.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
