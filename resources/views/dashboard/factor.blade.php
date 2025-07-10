{{-- resources/views/dashboard/factor.blade.php --}}
@extends('layouts.dashboard')

@section('page-title', 'Factor Dashboard - ' . ($factor->name ?? 'Unknown'))

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="space-y-6">
    {{-- Top Charts Section --}}
    <div class="grid grid-cols-2 gap-6">
        {{-- Current Readiness Level --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">Current Readiness Level</h3>
            <div class="flex items-center justify-center">
                <div class="relative w-48 h-48">
                    <canvas id="factorGaugeChart" width="192" height="192"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span id="factorGaugeValue" class="text-4xl font-bold text-gray-800">{{ $currentAssessment->readiness_level ?? 0 }}</span>
                        {{-- ADDED: Readiness Stage Label --}}
                        <span id="factorGaugeStage" class="text-lg font-semibold mt-2 {{ getReadinessStageColor(getReadinessStage($currentAssessment->readiness_level ?? 0)) }}">
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
            <h3 class="text-lg font-semibold text-gray-800 mb-6">Historical Assessment</h3>
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
                    <div class="col-span-2">S/N</div>
                    <div class="col-span-6">Question</div>
                    <div class="col-span-2 text-center">Current Score</div>
                    <div class="col-span-1 text-center">Weight</div>
                    <div class="col-span-1 text-center">Status</div>
                </div>
            </div>
            
            {{-- Table Body - Now Dynamic with Status Colors! --}}
            <div id="questionsTableBody" class="divide-y divide-gray-200">
                @forelse($questions as $question)
                <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center text-sm bg-white hover:bg-gray-50">
                    <div class="col-span-2 text-center">
                        <span class="text-gray-700 font-medium">{{ $loop->iteration }}</span>
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
                    {{-- ADDED: Status indicator with colors --}}
                    <div class="col-span-1 text-center">
                        <span class="inline-block w-3 h-3 rounded-full" 
                              style="background-color: {{ getScoreStatusColor($question->current_score) }}"
                              title="Score: {{ $question->current_score }}/5"></span>
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
                    <div class="col-span-2">S/N</div>
                    <div class="col-span-6">Corrective Action</div>
                    <div class="col-span-4">Department</div>
                </div>
            </div>
            
            {{-- Table Body - Now Dynamic! --}}
            <div id="actionsTableBody" class="divide-y divide-gray-200">
                @php
                    $correctiveActions = \App\Models\CorrectiveAction::whereHas('question', function($q) use ($factor) {
                        $q->where('factor_id', $factor->id);
                    })->with('question')->get();
                @endphp
                
                @forelse($correctiveActions as $action)
                <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center text-sm bg-white hover:bg-gray-50">
                    <div class="col-span-2 text-center">
                        <span class="text-gray-700 font-medium">{{ $loop->iteration }}</span>
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

    {{-- Debug Panel (Remove this after testing) --}}
    <div id="debugPanel" class="bg-gray-100 rounded-xl p-4 text-xs" style="display: none;">
        <h4 class="font-bold mb-2">Debug Info:</h4>
        <div id="debugContent"></div>
    </div>
</div>

{{-- FIXED: Added consistent color helper function --}}
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
// Simple, working JavaScript for Factor Dashboard
let factorGaugeChart = null;
let factorHistoricalChart = null;
const currentFactor = @json($factor);

console.log('🚀 Factor Dashboard loaded for factor:', currentFactor);

// ADDED: JavaScript function to get readiness stage
function getReadinessStage(score) {
    if (score >= 0.00 && score <= 1.25) {
        return 'Beginner';
    } else if (score >= 1.26 && score <= 2.50) {
        return 'Formative';
    } else if (score >= 2.51 && score <= 3.75) {
        return 'Progressive';
    } else if (score >= 3.76 && score <= 5.00) {
        return 'Mature';
    } else {
        return 'Unknown';
    }
}

// ADDED: JavaScript function to get stage color
function getReadinessStageColor(stage) {
    switch (stage) {
        case 'Beginner':
            return '#ef4444'; // red-500
        case 'Formative':
            return '#f97316'; // orange-500
        case 'Progressive':
            return '#3b82f6'; // blue-500
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
    
    // Wait a bit for header to load, then attach dropdown listener
    setTimeout(function() {
        attachDropdownListener();
    }, 500);
});

function attachDropdownListener() {
    const dropdown = document.getElementById('departmentFilter');
    console.log('🔍 Looking for dropdown...', dropdown);
    
    if (dropdown) {
        console.log('✅ Dropdown found! Current value:', dropdown.value);
        
        // Remove any existing listeners and add fresh one
        dropdown.replaceWith(dropdown.cloneNode(true));
        const newDropdown = document.getElementById('departmentFilter');
        
        newDropdown.addEventListener('change', function() {
            console.log('🔄 Department changed to:', this.value);
            updateFactorData(this.value);
        });
        
        console.log('✅ Event listener attached successfully');
    } else {
        console.error('❌ Dropdown not found!');
        
        // Try again in 1 second
        setTimeout(attachDropdownListener, 1000);
    }
}

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
        
        const remaining = 5 - readinessValue;
        
        // UPDATED: Dynamic gauge color based on readiness stage
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
        
        // UPDATED: Set both value and stage
        document.getElementById('factorGaugeValue').textContent = readinessValue;
        
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
                        max: 5,
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

function updateFactorData(departmentSlug) {
    console.log('🔄 Updating factor data for department:', departmentSlug);
    
    // Show loading indicators
    showLoadingIndicators();
    
    // Update URL
    const url = new URL(window.location);
    url.searchParams.set('department', departmentSlug);
    window.history.pushState({}, '', url);
    
    // Fetch data
    Promise.all([
        updateFactorQuestions(departmentSlug),
        updateFactorHistoricalData(departmentSlug),
        updateCorrectiveActions(departmentSlug)
    ]).finally(() => {
        hideLoadingIndicators();
    });
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

function updateFactorQuestions(departmentSlug) {
    const url = `/dashboard/factor/questions?factor=${currentFactor.slug}&department=${departmentSlug}`;
    console.log('📋 Fetching questions from:', url);
    
    return fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    })
    .then(data => {
        console.log('✅ Questions received:', data);
        
        if (data.success) {
            const tableBody = document.getElementById('questionsTableBody');
            tableBody.innerHTML = generateQuestionsHTML(data.questions);
            
            // UPDATED: Update gauge chart with stage label
            if (data.questions.length > 0) {
                const totalScore = data.questions.reduce((sum, q) => sum + (q.current_score * q.weight), 0);
                const totalWeight = data.questions.reduce((sum, q) => sum + q.weight, 0);
                const averageScore = totalWeight > 0 ? totalScore / totalWeight : 0;
                createFactorGaugeChart(Math.round(averageScore * 10) / 10);
            }
        }
    })
    .catch(error => {
        console.error('❌ Error loading questions:', error);
    });
}

function updateFactorHistoricalData(departmentSlug) {
    const url = `/dashboard/factor/historical?factor=${currentFactor.slug}&department=${departmentSlug}`;
    
    return fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            createFactorHistoricalChart(data.data);
        }
    })
    .catch(error => console.error('Error loading historical data:', error));
}

function updateCorrectiveActions(departmentSlug) {
    const url = `/dashboard/factor/actions?factor=${currentFactor.slug}&department=${departmentSlug}`;
    
    return fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('actionsTableBody').innerHTML = generateActionsHTML(data.corrective_actions);
        }
    })
    .catch(error => console.error('Error loading actions:', error));
}

// FIXED: Updated with status colors
function generateQuestionsHTML(questions) {
    if (questions.length === 0) {
        return `<div class="grid grid-cols-12 gap-4 px-6 py-8 items-center text-sm bg-white">
                    <div class="col-span-12 text-center text-gray-500">No questions found for this factor.</div>
                </div>`;
    }
    
    return questions.map(question => {
        // Determine color based on score
        let statusColor = '#f34b26'; // Red default
        if (question.current_score >= 3) {
            statusColor = '#3ec516'; // Green
        } else if (question.current_score >= 2) {
            statusColor = '#e4ab16'; // Orange
        }
        
        return `
        <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center text-sm bg-white hover:bg-gray-50">
            <div class="col-span-2 text-center">
                <span class="text-gray-700 font-medium">${question.id}</span>
            </div>
            <div class="col-span-6">
                <span class="text-gray-900">${question.question}</span>
            </div>
            <div class="col-span-2 text-center">
                <span class="text-gray-700 font-medium">${question.current_score}</span>
            </div>
            <div class="col-span-1 text-center">
                <span class="text-blue-600 font-medium">×${question.weight}</span>
            </div>
            <div class="col-span-1 text-center">
                <span class="inline-block w-3 h-3 rounded-full" 
                      style="background-color: ${statusColor}"
                      title="Score: ${question.current_score}/5"></span>
            </div>
        </div>
        `;
    }).join('');
}

function generateActionsHTML(actions) {
    if (actions.length === 0) {
        return `<div class="grid grid-cols-12 gap-4 px-6 py-8 items-center text-sm bg-white">
                    <div class="col-span-12 text-center text-gray-500">No corrective actions defined for this factor.</div>
                </div>`;
    }
    
    return actions.map(action => `
        <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center text-sm bg-white hover:bg-gray-50">
            <div class="col-span-2 text-center">
                <span class="text-gray-700 font-medium">${action.question_id}</span>
            </div>
            <div class="col-span-6">
                <span class="text-gray-900">${action.action}</span>
            </div>
            <div class="col-span-4">
                <span class="text-gray-900">${action.department}</span>
            </div>
        </div>
    `).join('');
}

// Test functions for debugging
window.testTableUpdate = function() {
    console.log('🧪 Testing table update...');
    const sampleQuestions = [
        {id: 999, question: 'Test Question 1', current_score: 3, weight: 1},
        {id: 998, question: 'Test Question 2', current_score: 5, weight: 2}
    ];
    document.getElementById('questionsTableBody').innerHTML = generateQuestionsHTML(sampleQuestions);
    console.log('✅ Table updated with test data');
};

window.testDepartmentChange = function(departmentSlug) {
    console.log('🧪 Testing department change to:', departmentSlug);
    updateFactorData(departmentSlug);
};
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