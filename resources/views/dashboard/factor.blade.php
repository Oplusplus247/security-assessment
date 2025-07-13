{{-- resources/views/dashboard/factor.blade.php --}}
@extends('layouts.dashboard')

@section('page-title', 'Factor Dashboard')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="space-y-6">
    {{-- Top Charts Section --}}
    <div class="grid grid-cols-2 gap-6">
        {{-- Current Readiness Level --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative">
            <h3 class="text-lg font-semibold text-gray-800">Current Readiness Level</h3>
            <div class="-mx-6 h-0.5 bg-gray-200 my-4"></div><br>
            <div class="flex items-center justify-center">
                <div class="relative w-48 h-48">
                    <canvas id="factorGaugeChart" width="192" height="192"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span id="factorGaugeValue" class="text-4xl font-bold text-gray-800">{{ number_format($currentAssessment->readiness_level ?? 0, 1) }}</span>
                        <span id="factorGaugeStage" class="text-lg font-semibold mt-2" style="color: {{ getReadinessStageColor(getReadinessStage($currentAssessment->readiness_level ?? 0)) }}">
                            {{ getReadinessStage($currentAssessment->readiness_level ?? 0) }}
                        </span>
                    </div>
                </div>
            </div>
            
            {{-- Loading indicator for Gauge --}}
            <div id="gaugeLoading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center hidden rounded-xl">
                <div class="flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                    <span class="text-gray-600">Loading...</span>
                </div>
            </div>
        </div>

        {{-- Historical Assessment --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative">
            <h3 class="text-lg font-semibold text-gray-800">Historical Assessment</h3>
            <div class="-mx-6 h-0.5 bg-gray-200 my-4"></div><br>
            <div class="h-64">
                <canvas id="factorHistoricalChart"></canvas>
            </div>
            <div class="flex items-center justify-center mt-4 space-x-6 text-sm">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-blue-300 rounded-full"></div>
                    <span class="text-gray-600">Readiness level</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-blue-600 rounded-full"></div>
                    <span class="text-gray-600">Target level</span>
                </div>
            </div>
            
            {{-- Loading indicator for Historical Chart --}}
            <div id="historicalLoading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center hidden rounded-xl">
                <div class="flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                    <span class="text-gray-600">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Assessment Questions Section --}}
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-800">Assessment Questions</h3>
        
        {{-- Assessment Questions Table --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden relative">
            {{-- Table Header --}}
            <div class="assessment-form-table-header px-6 py-4">
                <div class="grid grid-cols-12 gap-4 text-sm font-semibold text-white">
                    <div class="col-span-2">Question ID</div>
                    <div class="col-span-6">Question</div>
                    <div class="col-span-2 text-center">Current Score</div>
                    <div class="col-span-1 text-center">Weight</div>
                    <div class="col-span-1 text-center">Status</div>
                </div>
            </div>
            
            {{-- Table Body --}}
            <div id="questionsTableBody" class="divide-y divide-gray-200">
                @forelse($questions as $question)
                <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center text-sm bg-white hover:bg-gray-50">
                    <div class="col-span-2 text-center">
                        <span class="text-gray-700 font-medium">{{ $question->id }}</span>
                    </div>
                    <div class="col-span-6">
                        <span class="text-gray-900">{{ $question->question }}</span>
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="text-gray-700 font-medium">{{ $question->current_score }}</span>
                    </div>
                    <div class="col-span-1 text-center">
                        <span class="text-blue-600 font-medium">×{{ $question->weight ?? 1 }}</span>
                    </div>
                    {{-- Status indicator with colors --}}
                    <div class="col-span-1 text-center">
                        <span class="inline-block w-3 h-3 rounded-full" 
                              style="background-color: {{ getScoreStatusColor($question->current_score) }}"
                              title="Score: {{ $question->current_score }}/4"></span>
                    </div>
                </div>
                @empty
                <div class="grid grid-cols-12 gap-4 px-6 py-8 items-center text-sm bg-white">
                    <div class="col-span-12 text-center text-gray-500">
                        No questions found for this factor.
                    </div>
                </div>
                @endforelse
            </div>
            
            {{-- Loading indicator for Questions --}}
            <div id="questionsLoading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center hidden">
                <div class="flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                    <span class="text-gray-600">Loading questions...</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Corrective Actions Section --}}
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-800">Corrective Actions</h3>
        
        {{-- Corrective Actions Table --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden relative">
            {{-- Table Header --}}
            <div class="assessment-form-table-header px-6 py-4">
                <div class="grid grid-cols-12 gap-4 text-sm font-semibold text-white">
                    <div class="col-span-2">Question ID</div>
                    <div class="col-span-6">Corrective Action</div>
                    <div class="col-span-4">Department</div>
                </div>
            </div>
            
            {{-- Table Body --}}
            <div id="actionsTableBody" class="divide-y divide-gray-200">
                @php
                    $correctiveActions = \App\Models\CorrectiveAction::whereHas('question', function($q) use ($factor) {
                        $q->where('factor_id', $factor->id);
                    })->with('question')->get();
                @endphp
                
                @forelse($correctiveActions as $action)
                <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center text-sm bg-white hover:bg-gray-50">
                    <div class="col-span-2 text-center">
                        <span class="text-gray-700 font-medium">{{ $action->question_id }}</span>
                    </div>
                    <div class="col-span-6">
                        <span class="text-gray-900">{{ $action->action }}</span>
                    </div>
                    <div class="col-span-4">
                        <span class="text-gray-900">{{ $action->department ?? 'All Departments' }}</span>
                    </div>
                </div>
                @empty
                <div class="grid grid-cols-12 gap-4 px-6 py-8 items-center text-sm bg-white">
                    <div class="col-span-12 text-center text-gray-500">
                        No corrective actions defined for this factor.
                    </div>
                </div>
                @endforelse
            </div>
            
            {{-- Loading indicator for Actions --}}
            <div id="actionsLoading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center hidden">
                <div class="flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                    <span class="text-gray-600">Loading actions...</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Helper functions for colors --}}
@php
function getScoreStatusColor($score) {
    if ($score >= 3.0) {
        return '#3ec516'; // Green for >= 3
    } elseif ($score >= 2.0) {
        return '#e4ab16'; // Orange for >= 2 but < 3
    } else {
        return '#f34b26'; // Red for < 2
    }
}
@endphp

@push('scripts')
<script>
// Factor Dashboard JavaScript - Simplified to match Figma
let factorGaugeChart = null;
let factorHistoricalChart = null;
const currentFactor = @json($factor);

console.log('🚀 Factor Dashboard loaded for factor:', currentFactor);

// Readiness stage functions
function getReadinessStage(score) {
    if (score >= 0.00 && score <= 1.25) {
        return 'Beginner';
    } else if (score >= 1.26 && score <= 2.50) {
        return 'Formative';
    } else if (score >= 2.51 && score <= 3.75) {
        return 'Progressive';
    } else if (score >= 3.76 && score <= 4.00) {
        return 'Mature';
    } else {
        return 'Unknown';
    }
}

function getReadinessStageColor(stage) {
    switch (stage) {
        case 'Beginner':
            return '#ef4444'; // red-500
        case 'Formative':
            return '#f97316'; // orange-500
        case 'Progressive':
            return '#eab308'; // yellow-500
        case 'Mature':
            return '#10b981'; // green-500
        default:
            return '#6b7280'; // gray-500
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('📱 DOM Content Loaded');
    
    // Initialize charts
    initializeFactorCharts();
    
    // Attach factor dropdown listener (will be handled by header)
    const factorDropdown = document.getElementById('factorFilter');
    if (factorDropdown) {
        factorDropdown.addEventListener('change', function() {
            console.log('🔄 Factor changed to:', this.value);
            // Navigate to new factor
            window.location.href = `{{ route('dashboard.factor') }}/${this.value}`;
        });
    }
});

function initializeFactorCharts() {
    const currentAssessment = @json($currentAssessment);
    const historicalData = @json($historicalData);

    console.log('📊 Initializing charts with data:', { currentAssessment, historicalData });

    createFactorGaugeChart(currentAssessment.readiness_level || 0);
    createFactorHistoricalChart(historicalData);
}

function createFactorGaugeChart(readinessValue) {
    const factorGaugeCtx = document.getElementById('factorGaugeChart');
    if (factorGaugeCtx) {
        if (factorGaugeChart) {
            factorGaugeChart.destroy();
        }
        
        const remaining = 4 - readinessValue; // Updated for 0-4 scale
        
        // Dynamic gauge color based on readiness stage
        const stage = getReadinessStage(readinessValue);
        const gaugeColor = getReadinessStageColor(stage);
        
        factorGaugeChart = new Chart(factorGaugeCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [readinessValue, remaining],
                    backgroundColor: [gaugeColor, '#E5E7EB'],
                    borderWidth: 0,
                    cutout: '75%'
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
        
        // Update value and stage display
        document.getElementById('factorGaugeValue').textContent = readinessValue.toFixed(1);
        
        const gaugeStageElement = document.getElementById('factorGaugeStage');
        if (gaugeStageElement) {
            gaugeStageElement.textContent = stage;
            gaugeStageElement.style.color = gaugeColor;
        }
    }
}

function createFactorHistoricalChart(data) {
    const factorHistoricalCtx = document.getElementById('factorHistoricalChart');
    if (factorHistoricalCtx) {
        if (factorHistoricalChart) {
            factorHistoricalChart.destroy();
        }
        
        factorHistoricalChart = new Chart(factorHistoricalCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.map(item => item.date),
                datasets: [{
                    label: 'Readiness level',
                    data: data.map(item => item.readiness_level),
                    backgroundColor: '#93C5FD',
                    borderRadius: 6,
                    barThickness: 14
                }, {
                    label: 'Target level',
                    data: data.map(item => item.target_level),
                    backgroundColor: '#3B82F6',
                    borderRadius: 6,
                    barThickness: 14
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 }, color: '#6B7280' }
                    },
                    y: {
                        beginAtZero: true,
                        max: 4, // Updated for 0-4 scale
                        ticks: { stepSize: 1, font: { size: 10 }, color: '#6B7280' },
                        grid: { color: '#F3F4F6' }
                    }
                },
                plugins: { legend: { display: false } },
                elements: { bar: { borderSkipped: false } }
            }
        });
    }
}

// Functions for handling factor switching (will be called from header dropdown)
window.switchFactor = function(factorSlug) {
    console.log('🔄 Switching to factor:', factorSlug);
    window.location.href = `{{ route('dashboard.factor') }}/${factorSlug}`;
};

// Update data functions (if needed for AJAX updates)
function updateFactorData() {
    // Show loading indicators
    showLoadingIndicators();
    
    // This would be called when data needs to be refreshed
    // For now, just hide loading after a short delay
    setTimeout(() => {
        hideLoadingIndicators();
    }, 1000);
}

function showLoadingIndicators() {
    const indicators = ['gaugeLoading', 'historicalLoading', 'questionsLoading', 'actionsLoading'];
    indicators.forEach(id => {
        const element = document.getElementById(id);
        if (element) element.classList.remove('hidden');
    });
}

function hideLoadingIndicators() {
    const indicators = ['gaugeLoading', 'historicalLoading', 'questionsLoading', 'actionsLoading'];
    indicators.forEach(id => {
        const element = document.getElementById(id);
        if (element) element.classList.add('hidden');
    });
}
</script>
@endpush

@push('styles')
<style>
.assessment-form-table-header {
    background-color: #25408f !important;
    color: white !important;
}

.hover\:bg-gray-50:hover {
    background-color: #f9fafb;
}
</style>
@endpush
@endsection