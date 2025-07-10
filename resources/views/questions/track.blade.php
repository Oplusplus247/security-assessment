@extends('layouts.dashboard')

@section('page-title', 'Track Questions')

@section('content')
<div class="space-y-6">
    {{-- Header with Stats --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Question Tracking</h2>
            <div class="flex space-x-4 text-sm">
                @php
                    $statusCounts = [
                        'sent' => $tracking->where('status', 'sent')->count(),
                        'pending' => $tracking->where('status', 'pending')->count(), 
                        'completed' => $tracking->where('status', 'completed')->count()
                    ];
                @endphp
                <div class="flex items-center space-x-1">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <span class="text-gray-600">Sent: {{ $statusCounts['sent'] }}</span>
                </div>
                <div class="flex items-center space-x-1">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <span class="text-gray-600">Pending: {{ $statusCounts['pending'] }}</span>
                </div>
                <div class="flex items-center space-x-1">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-gray-600">Completed: {{ $statusCounts['completed'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Track Questions Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Table Header --}}
        <div class="assessment-form-table-header px-6 py-4">
            <div class="grid grid-cols-12 gap-4 text-xs font-medium text-white uppercase tracking-wider">
                <div class="col-span-1">No</div>
                <div class="col-span-2">Date</div>
                <div class="col-span-3">Assessment Title</div>
                <div class="col-span-2">Status</div>
                <div class="col-span-4">Recipient</div>
            </div>
        </div>
        
        {{-- Table Body --}}
        <div class="divide-y divide-gray-200">
            @forelse($tracking as $index => $track)
            <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center text-sm bg-white hover:bg-gray-50">
                {{-- No Column --}}
                <div class="col-span-1">
                    <span class="text-gray-700 font-medium">{{ $tracking->firstItem() + $index }}</span>
                </div>
                
                {{-- Date Column --}}
                <div class="col-span-2">
                    <span class="text-gray-900">{{ $track->date->format('d/m/Y') }}</span>
                </div>
                
                {{-- Assessment Title Column --}}
                <div class="col-span-3">
                    <span class="text-gray-900">{{ $track->assessment_type }}</span>
                </div>
                
                {{-- Status Column --}}
                <div class="col-span-2">
                    @php
                        $statusClasses = [
                            'sent' => 'bg-blue-100 text-blue-800',
                            'pending' => 'bg-yellow-100 text-yellow-800', 
                            'completed' => 'bg-green-100 text-green-800',
                            'declined' => 'bg-red-100 text-red-800'
                        ];
                        $statusClass = $statusClasses[strtolower($track->status)] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                        {{ ucfirst($track->status) }}
                    </span>
                </div>
                
                {{-- Recipient Column --}}
                <div class="col-span-4">
                    <span class="text-gray-900">{{ $track->email }}</span>
                </div>
            </div>
            @empty
            <div class="grid grid-cols-12 gap-4 px-6 py-12 items-center text-sm bg-white">
                <div class="col-span-12 text-center">
                    <div class="text-gray-500 mb-4">
                        <i class="fas fa-paper-plane text-4xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No tracking data available</h3>
                    <p class="text-gray-500 mb-4">Questions haven't been sent to any recipients yet.</p>
                    <a href="{{ route('questions.send') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Send Questions
                    </a>
                </div>
            </div>
            @endforelse
        </div>
        
        {{-- Pagination Footer --}}
        @if($tracking->hasPages())
        <div class="bg-white px-6 py-4 flex items-center justify-between border-t border-gray-200">
            <div class="flex-1 flex justify-between sm:hidden">
                @if($tracking->previousPageUrl())
                <a href="{{ $tracking->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Previous
                </a>
                @endif
                
                @if($tracking->nextPageUrl())
                <a href="{{ $tracking->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Next
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
                @endif
            </div>
            
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">{{ $tracking->firstItem() ?? 0 }}</span> to <span class="font-medium">{{ $tracking->lastItem() ?? 0 }}</span> of <span class="font-medium">{{ $tracking->total() }}</span> results
                    </p>
                </div>
                <div>
                    {{ $tracking->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Action Buttons --}}
    <div class="flex justify-end space-x-3">
        <button onclick="refreshTracking()" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i class="fas fa-sync-alt mr-2"></i>
            Refresh
        </button>
    </div>
</div>

@push('styles')
<style>
/* Custom styles for Track Questions */
.assessment-form-table-header {
    background-color: #25408f !important;
    color: white !important;
}

/* Status badge enhancements */
.bg-blue-100 {
    background-color: #dbeafe;
}

.text-blue-800 {
    color: #1e40af;
}

.bg-yellow-100 {
    background-color: #fef3c7;
}

.text-yellow-800 {
    color: #92400e;
}

.bg-green-100 {
    background-color: #dcfce7;
}

.text-green-800 {
    color: #166534;
}

.bg-red-100 {
    background-color: #fee2e2;
}

.text-red-800 {
    color: #991b1b;
}

/* Hover effects */
.hover\:bg-gray-50:hover {
    background-color: #f9fafb;
}

/* Table container enhancements */
.bg-white.rounded-xl.shadow-sm.overflow-hidden {
    border: none;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

/* Button transitions */
button, a {
    transition: all 0.15s ease-in-out;
}

/* Stats styling */
.stats-item {
    display: flex;
    align-items: center;
    padding: 0.5rem 1rem;
    background: #f8fafc;
    border-radius: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
function refreshTracking() {
    location.reload();
}

// Auto-refresh every 30 seconds
setInterval(function() {
    // You can implement AJAX refresh here if needed
}, 30000);
</script>
@endpush
@endsection