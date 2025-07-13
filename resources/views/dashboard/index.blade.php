{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.dashboard')

@section('page-title', 'Dashboard')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 h-full auto-rows-fr">
    {{-- Current Readiness Level --}}
<div class="bg-white rounded-xl shadow-sm p-6 min-h-[350px]">
    <h3 class="text-lg font-semibold text-gray-800">Current Readiness Level</h3>
    <div class="-mx-6 h-0.5 bg-gray-200 my-4"></div><br><br>

    <div class="flex items-center justify-center">
        <div class="relative w-56 h-56 flex items-center justify-center">
            <canvas id="gaugeChart"></canvas>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span id="gaugeValue" class="text-4xl font-bold text-gray-800">{{ $currentReadiness->readiness_level ?? 0 }}</span>
                <span id="gaugeStage" class="text-lg font-semibold mt-2" style="color: {{ getReadinessStageColor(getReadinessStage($currentReadiness->readiness_level ?? 0)) }}">
                    {{ getReadinessStage($currentReadiness->readiness_level ?? 0) }}
                </span>
            </div>
        </div>
    </div>
</div>


    {{-- Aggregated Readiness Level --}}
    <div class="bg-white rounded-xl shadow-sm p-6 min-h-[350px]">
        <h3 class="text-lg font-semibold text-gray-800">Aggregated Readiness Level</h3>
        <div class="-mx-6 h-0.5 bg-gray-200 my-4"></div>
        
        <div class="flex items-center justify-center">
            <div class="w-100 h-100 flex items-center justify-center">
                <canvas id="radarChart"></canvas>
            </div>
        </div>
    </div>

    



    {{-- Readiness Level By Factor --}}
    <div class="bg-white rounded-xl shadow-sm p-6 min-h-[350px]">
        <h3 class="text-lg font-semibold text-gray-800">Readiness Level By Factor</h3>
        <div class="-mx-6 h-0.5 bg-gray-200 my-4"></div><br>
        <div class="grid grid-cols-3 gap-2" id="factorGrid">
            @foreach($factorReadiness as $factor)
                <a href="{{ route('dashboard.factor', ['factor' => $factor['slug']]) }}" 
                   class="text-white p-3 rounded text-center font-medium text-xs sm:text-sm py-4 hover:opacity-90 transition-all duration-200 cursor-pointer hover:transform hover:-translate-y-1"
                   style="background-color: {{ getFactorBackgroundColor($factor['level']) }}">
                    {{ $factor['name'] }}
                    <div class="text-xs mt-1 opacity-90">{{ number_format($factor['level'], 1) }}</div>
                </a>
            @endforeach
            
            {{-- Add empty div if needed for grid alignment --}}
            @if(count($factorReadiness) % 3 !== 0)
                @for($i = 0; $i < (3 - (count($factorReadiness) % 3)); $i++)
                    <div class="bg-gray-200 p-3 rounded text-center font-medium text-xs opacity-0"></div>
                @endfor
            @endif
        </div>
    </div>

    {{-- Historical Assessment --}}
    <div class="bg-white rounded-xl shadow-sm p-6 flex flex-col min-h-[350px] relative">
        <div class="flex justify-between items-center">
            <h3 class="text-xl font-semibold text-gray-800">Historical Assessment</h3>
            <select id="departmentSelect" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @foreach($departments as $department)
                    <option value="{{ $department->slug }}">{{ $department->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Full-width divider below title and dropdown --}}
        <div class="-mx-6 h-0.5 bg-gray-200 my-4"></div>

        <div class="flex-grow h-64 flex items-center justify-center">
            <canvas id="historicalChart"></canvas>
        </div>

        <div class="flex items-center justify-center mt-4 space-x-6 text-base">
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-blue-300 rounded"></div>
                <span class="text-gray-600">Readiness level</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-blue-600 rounded"></div>
                <span class="text-gray-600">Target level</span>
            </div>
        </div>

        <div id="historicalLoading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center hidden rounded-xl">
            <div class="flex items-center space-x-2">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <span class="text-gray-600">Loading...</span>
            </div>
        </div>
    </div>

</div>

{{-- Updated color helper functions with new mapping --}}
@php
function getFactorBackgroundColor($level) {
    if ($level >= 3.76) {
        return '#10b981'; // Green for Mature (3.76-5.00)
    } elseif ($level >= 2.51) {
        return '#eab308'; // Yellow for Progressive (2.51-3.75)
    } elseif ($level >= 1.26) {
        return '#f97316'; // Orange for Formative (1.26-2.50)
    } else {
        return '#ef4444'; // Red for Beginner (0.00-1.25)
    }
}
@endphp

@push('scripts')
<script>
let gaugeChart = null;
let radarChart = null;
let historicalChart = null;

// Updated readiness stage functions
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
    initializeCharts();
    
    document.getElementById('departmentSelect').addEventListener('change', function() {
        const selectedDepartment = this.value;
        updateHistoricalChart(selectedDepartment);
    });
});

function initializeCharts() {
    const currentReadiness = @json($currentReadiness);
    const aggregatedReadiness = @json($aggregatedReadiness);
    const historicalData = @json($historicalData);

    // Current Readiness Gauge Chart
    const gaugeCtx = document.getElementById('gaugeChart');
    if (gaugeCtx) {
        const readinessValue = currentReadiness.readiness_level || 0;
        const remaining = 4 - readinessValue; // Updated for 0-4 scale
        
        const stage = getReadinessStage(readinessValue);
        const gaugeColor = getReadinessStageColor(stage);
        
        gaugeChart = new Chart(gaugeCtx.getContext('2d'), {
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
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });

        const gaugeStageElement = document.getElementById('gaugeStage');
        if (gaugeStageElement) {
            gaugeStageElement.textContent = stage;
            gaugeStageElement.style.color = gaugeColor;
        }
    }

    // Aggregated Readiness Radar Chart
    const radarCtx = document.getElementById('radarChart');
    if (radarCtx) {
        const labels = Object.keys(aggregatedReadiness);
        const data = Object.values(aggregatedReadiness);
        
        radarChart = new Chart(radarCtx.getContext('2d'), {
            type: 'radar',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderColor: '#3B82F6',
                    borderWidth: 3,
                    pointBackgroundColor: '#3B82F6',
                    pointBorderColor: '#3B82F6',
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: 10
                },
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 4, // Updated for 0-4 scale
                        ticks: {
                            stepSize: 1,
                            display: true,
                            color: '#9CA3AF',
                            padding: 20,
                            font: {
                                size: 10
                            }
                        },
                        grid: {
                            color: '#E5E7EB',
                            lineWidth: 1
                        },
                        angleLines: {
                            color: '#E5E7EB',
                            lineWidth: 1
                        },
                        pointLabels: {
                            font: {
                                size: 14,
                                weight: '500'
                            },
                            color: '#374151',
                            padding: 25 
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        titleColor: '#F9FAFB',
                        bodyColor: '#F9FAFB',
                        borderColor: '#374151',
                        borderWidth: 1
                    }
                }
            }
        });
    }

    createHistoricalChart(historicalData);
}

function createHistoricalChart(data) {
    const historicalCtx = document.getElementById('historicalChart');
    if (historicalCtx) {
        if (historicalChart) {
            historicalChart.destroy();
        }
        
        // Filter out duplicate dates and keep latest entry for each date
        const uniqueData = [];
        const seenDates = new Set();
        
        data.reverse().forEach(item => {
            if (!seenDates.has(item.date)) {
                seenDates.add(item.date);
                uniqueData.push(item);
            }
        });
        
        historicalChart = new Chart(historicalCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: uniqueData.map(item => item.date),
                datasets: [{
                    label: 'Readiness level',
                    data: uniqueData.map(item => item.readiness_level),
                    backgroundColor: '#93C5FD',
                    borderRadius: 4,
                    barThickness: 16
                }, {
                    label: 'Target level',
                    data: uniqueData.map(item => item.target_level),
                    backgroundColor: '#3B82F6',
                    borderRadius: 4,
                    barThickness: 16
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 10 },
                            color: '#6B7280'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 4, // Updated for 0-4 scale
                        ticks: {
                            stepSize: 1,
                            font: { size: 10 },
                            color: '#6B7280'
                        },
                        grid: {
                            color: '#F3F4F6'
                        }
                    }
                },
                plugins: {
                    legend: { display: false }
                },
                elements: {
                    bar: {
                        borderSkipped: false
                    }
                }
            }
        });
    }
}

function updateHistoricalChart(departmentSlug) {
    document.getElementById('historicalLoading').classList.remove('hidden');
    
    fetch(`{{ route('dashboard.historical.ajax') }}?department=${departmentSlug}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            createHistoricalChart(data.data);
        } else {
            console.error('Error loading historical data');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    })
    .finally(() => {
        document.getElementById('historicalLoading').classList.add('hidden');
    });
}
</script>
@endpush
@endsection