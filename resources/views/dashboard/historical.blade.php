@extends('layouts.dashboard')

@section('page-title', 'Historical Assessment')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
{{-- Add jsPDF library for PDF generation --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
@endpush

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Historical Assessments</h2>
            <a href="{{ route('assessment.form') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-plus mr-2"></i>
                New Assessment
            </a>
        </div>
    </div>

    {{-- Assessments Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Table Header --}}
        <div class="assessment-form-table-header px-6 py-4">
            <div class="grid grid-cols-10 gap-4 text-sm font-semibold text-white">
                <div class="col-span-1">NO</div>
                <div class="col-span-2">DATE</div>
                <div class="col-span-3">ASSESSMENT TITLE</div>
                <div class="col-span-2">READINESS LEVEL</div>
                <div class="col-span-2">ACTIONS</div>
            </div>
        </div>
        
        {{-- Table Body --}}
        <div class="divide-y divide-gray-200">
            @if(isset($assessments) && $assessments->count() > 0)
                @foreach($assessments as $index => $assessment)
                <div class="grid grid-cols-10 gap-4 px-6 py-4 items-center text-sm bg-white hover:bg-gray-50">
                    {{-- Number --}}
                    <div class="col-span-1">
                        <span class="text-gray-700 font-medium">{{ ($assessments->currentPage() - 1) * $assessments->perPage() + $index + 1 }}</span>
                    </div>
                    
                    {{-- Date --}}
                    <div class="col-span-2">
                        <span class="text-gray-900">{{ $assessment->assessment_date->format('d/m/Y') }}</span>
                    </div>
                    
                    {{-- Assessment Title --}}
                    <div class="col-span-3">
                        <span class="text-gray-900 font-medium">
                            Assessment {{ $assessment->user->name ?? 'User' }}
                        </span>
                        <div class="text-xs text-gray-500">
                            {{ $assessment->responses()->count() }} questions answered
                        </div>
                    </div>
                    
                    {{-- Readiness Level with Updated Color Logic --}}
                    <div class="col-span-2">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">{{ number_format($assessment->readiness_level, 1) }}/4.0</div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="h-2 rounded-full" 
                                         style="width: {{ ($assessment->readiness_level / 4) * 100 }}%; background-color: {{ getReadinessBarColor($assessment->readiness_level) }}"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">{{ getReadinessStage($assessment->readiness_level) }}</div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Actions - Updated: Added share/PDF button --}}
                    <div class="col-span-2">
                        <div class="flex items-center space-x-2">
                            {{-- View Button --}}
                            <button onclick="viewAssessment({{ $assessment->id }})" 
                                    class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-50 transition duration-150"
                                    title="View Details">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                            
                            {{-- Share/PDF Button --}}
                            <button onclick="shareAssessmentPDF({{ $assessment->id }})" 
                                    class="text-green-600 hover:text-green-800 p-2 rounded-full hover:bg-green-50 transition duration-150"
                                    title="Download PDF Report">
                                <i class="fas fa-share-alt text-sm"></i>
                            </button>
                            
                            {{-- Delete Button --}}
                            <button onclick="deleteAssessment({{ $assessment->id }})" 
                                    class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-50 transition duration-150"
                                    title="Delete">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
            <div class="px-6 py-12 text-center">
                <div class="text-gray-500 mb-4">
                    <i class="fas fa-clipboard-list text-4xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No assessments completed yet</h3>
                <p class="text-gray-500 mb-4">Complete your first assessment to see it here.</p>
                <a href="{{ route('assessment.form') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>
                    Start Assessment
                </a>
            </div>
            @endif
        </div>

        {{-- Pagination --}}
        @if(isset($assessments) && $assessments->hasPages())
        <div class="bg-white px-6 py-4 flex items-center justify-between border-t border-gray-200">
            <div class="flex-1 flex justify-between sm:hidden">
                @if($assessments->previousPageUrl())
                <a href="{{ $assessments->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Previous
                </a>
                @endif
                
                @if($assessments->nextPageUrl())
                <a href="{{ $assessments->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Next
                </a>
                @endif
            </div>
            
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">{{ $assessments->firstItem() ?? 0 }}</span> to <span class="font-medium">{{ $assessments->lastItem() ?? 0 }}</span> of <span class="font-medium">{{ $assessments->total() }}</span> results
                    </p>
                </div>
                <div>
                    {{ $assessments->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Assessment Details Modal --}}
<div id="assessmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Assessment Details</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="modalContent" class="max-h-96 overflow-y-auto">
                <!-- Content will be loaded here -->
            </div>
            
            <div class="flex justify-end mt-4 space-x-2">
                <button onclick="printModalContent()" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                    <i class="fas fa-download mr-2"></i>
                    Download PDF
                </button>
                <button onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Hidden div for PDF generation --}}
<div id="pdfContent" style="display: none; background: white; padding: 40px; font-family: Arial, sans-serif;">
    <!-- PDF content will be generated here -->
</div>

{{-- Updated helper functions with new mapping --}}
@php
function getReadinessBarColor($level) {
    if ($level >= 3.76) {
        return '#10b981'; // Green for Mature (3.76-4.00)
    } elseif ($level >= 2.51) {
        return '#eab308'; // Yellow for Progressive (2.51-3.75)
    } elseif ($level >= 1.26) {
        return '#f97316'; // Orange for Formative (1.26-2.50)
    } else {
        return '#ef4444'; // Red for Beginner (0.00-1.25)
    }
}

@endphp

@endsection

@push('styles')
<style>
.assessment-form-table-header {
    background-color: #25408f !important;
    color: white !important;
}

.hover\:bg-gray-50:hover {
    background-color: #f9fafb;
}

.assessment-summary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.factor-score {
    background: #f8fafc;
    border-left: 4px solid #3b82f6;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
}

/* PDF Print Styles */
.pdf-header {
    text-align: center;
    border-bottom: 2px solid #25408f;
    padding-bottom: 20px;
    margin-bottom: 30px;
}

.pdf-section {
    margin-bottom: 25px;
    page-break-inside: avoid;
}

.pdf-factor {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.pdf-score-bar {
    width: 100%;
    height: 20px;
    background-color: #f3f4f6;
    border-radius: 10px;
    overflow: hidden;
}

@media print {
    body * {
        visibility: hidden;
    }
    #pdfContent, #pdfContent * {
        visibility: visible;
    }
    #pdfContent {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        display: block !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
let currentAssessmentData = null;

function viewAssessment(assessmentId) {
    fetch(`/assessment/${assessmentId}/details`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentAssessmentData = data;
            document.getElementById('modalTitle').textContent = `Assessment Details - ${data.assessment.assessment_date}`;
            document.getElementById('modalContent').innerHTML = generateAssessmentHTML(data.assessment, data.responses_by_factor);
            document.getElementById('assessmentModal').classList.remove('hidden');
        } else {
            alert('Error loading assessment details');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading assessment details');
    });
}

function shareAssessmentPDF(assessmentId) {
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i>';
    button.disabled = true;
    
    fetch(`/assessment/${assessmentId}/details`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            generatePDFReport(data.assessment, data.responses_by_factor);
        } else {
            alert('Error loading assessment details');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error generating PDF');
    })
    .finally(() => {
        button.innerHTML = originalHTML;
        button.disabled = false;
    });
}

function generatePDFReport(assessment, responsesByFactor) {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('p', 'mm', 'a4');
    
    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();
    const margin = 20;
    let yPosition = margin;
    
    function checkNewPage(height) {
        if (yPosition + height > pageHeight - margin) {
            pdf.addPage();
            yPosition = margin;
        }
    }
    
    function getScoreColor(score) {
        if (score >= 3.76) return [16, 185, 129]; // Green
        if (score >= 2.51) return [234, 179, 8]; // Yellow
        if (score >= 1.26) return [249, 115, 22]; // Orange
        return [239, 68, 68]; // Red
    }
    
    // HEADER
    pdf.setFontSize(24);
    pdf.setFont(undefined, 'bold');
    pdf.setTextColor(37, 64, 143);
    pdf.text('Assessment Report', pageWidth / 2, yPosition, { align: 'center' });
    yPosition += 15;
    
    pdf.setDrawColor(37, 64, 143);
    pdf.setLineWidth(0.5);
    pdf.line(margin, yPosition, pageWidth - margin, yPosition);
    yPosition += 20;
    
    // ASSESSMENT SUMMARY
    pdf.setFontSize(16);
    pdf.setFont(undefined, 'bold');
    pdf.setTextColor(0, 0, 0);
    pdf.text('Assessment Summary', margin, yPosition);
    yPosition += 10;
    
    pdf.setFontSize(11);
    pdf.setFont(undefined, 'normal');
    
    const leftCol = margin;
    const rightCol = pageWidth / 2;
    
    pdf.text(`Date: ${assessment.assessment_date}`, leftCol, yPosition);
    pdf.text(`Readiness Level: ${assessment.readiness_level}/4.0`, rightCol, yPosition);
    yPosition += 6;
    
    pdf.text(`Department: ${assessment.department.name}`, leftCol, yPosition);
    pdf.text(`Status: ${assessment.status}`, rightCol, yPosition);
    yPosition += 10;
    
    // Overall readiness bar
    const barWidth = pageWidth - (2 * margin);
    const barHeight = 8;
    const fillWidth = (assessment.readiness_level / 4) * barWidth; // Updated for 0-4 scale
    const scoreColor = getScoreColor(assessment.readiness_level);
    
    pdf.setFillColor(243, 244, 246);
    pdf.rect(margin, yPosition, barWidth, barHeight, 'F');
    
    pdf.setFillColor(scoreColor[0], scoreColor[1], scoreColor[2]);
    pdf.rect(margin, yPosition, fillWidth, barHeight, 'F');
    yPosition += 20;
    
    // FACTORS BREAKDOWN
    checkNewPage(20);
    pdf.setFontSize(16);
    pdf.setFont(undefined, 'bold');
    pdf.text('Detailed Results by Factor', margin, yPosition);
    yPosition += 15;
    
    for (const [factorName, responses] of Object.entries(responsesByFactor)) {
        checkNewPage(30);
        
        pdf.setFontSize(14);
        pdf.setFont(undefined, 'bold');
        pdf.setTextColor(37, 64, 143);
        pdf.text(factorName, margin, yPosition);
        yPosition += 10;
        
        // Calculate factor average with weights
        let totalWeightedScore = 0;
        let totalWeight = 0;
        
        responses.forEach(response => {
            const weight = response.question.weight || 1;
            totalWeightedScore += response.score * weight;
            totalWeight += weight;
        });
        
        const factorAverage = totalWeight > 0 ? totalWeightedScore / totalWeight : 0;
        const factorColor = getScoreColor(factorAverage);
        
        pdf.setFontSize(10);
        pdf.setFont(undefined, 'normal');
        pdf.setTextColor(0, 0, 0);
        pdf.text(`Average Score: ${factorAverage.toFixed(1)}/4.0`, margin, yPosition);
        yPosition += 8;
        
        const factorBarWidth = (pageWidth - (2 * margin)) * 0.6;
        const factorFillWidth = (factorAverage / 4) * factorBarWidth;
        
        pdf.setFillColor(243, 244, 246);
        pdf.rect(margin, yPosition, factorBarWidth, 6, 'F');
        pdf.setFillColor(factorColor[0], factorColor[1], factorColor[2]);
        pdf.rect(margin, yPosition, factorFillWidth, 6, 'F');
        yPosition += 15;
        
        // Individual questions
        responses.forEach((response, index) => {
            checkNewPage(12);
            
            const questionColor = getScoreColor(response.score);
            
            let questionText = response.question.question;
            if (questionText.length > 80) {
                questionText = questionText.substring(0, 80) + '...';
            }
            
            pdf.setFontSize(9);
            pdf.setTextColor(0, 0, 0);
            pdf.text(`${index + 1}. ${questionText}`, margin + 5, yPosition);
            
            const scoreX = pageWidth - margin - 25;
            pdf.setFillColor(questionColor[0], questionColor[1], questionColor[2]);
            pdf.roundedRect(scoreX, yPosition - 4, 20, 8, 2, 2, 'F');
            
            pdf.setFontSize(8);
            pdf.setTextColor(255, 255, 255);
            pdf.text(`${response.score}/4`, scoreX + 10, yPosition, { align: 'center' });
            
            yPosition += 10;
        });
        
        yPosition += 5;
    }
    
    // FOOTER
    const now = new Date();
    pdf.setFontSize(8);
    pdf.setTextColor(128, 128, 128);
    pdf.text(`Generated on ${now.toLocaleDateString()} at ${now.toLocaleTimeString()}`, margin, pageHeight - 10);
    pdf.text('Company Assessment System', pageWidth - margin, pageHeight - 10, { align: 'right' });
    
    const fileName = `Assessment_Report_${assessment.assessment_date.replace(/\//g, '-')}.pdf`;
    pdf.save(fileName);
}

function printModalContent() {
    if (currentAssessmentData) {
        generatePDFReport(currentAssessmentData.assessment, currentAssessmentData.responses_by_factor);
    } else {
        alert('No assessment data available for PDF generation');
    }
}

function generateAssessmentHTML(assessment, responsesByFactor) {
    const barColor = getReadinessBarColorJS(assessment.readiness_level);
    
    let html = `
        <div class="assessment-summary">
            <h4 class="text-lg font-semibold mb-2">Assessment Summary</h4>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p><strong>Date:</strong> ${assessment.assessment_date}</p>
                    <p><strong>Department:</strong> ${assessment.department.name}</p>
                </div>
                <div>
                    <p><strong>Readiness Level:</strong> ${assessment.readiness_level}/4.0</p>
                    <p><strong>Status:</strong> ${assessment.status}</p>
                    <div class="w-full bg-white bg-opacity-30 rounded-full h-2 mt-2">
                        <div class="h-2 rounded-full" style="width: ${(assessment.readiness_level / 4) * 100}%; background-color: ${barColor}"></div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    for (const [factorName, responses] of Object.entries(responsesByFactor)) {
        html += `
            <div class="factor-score">
                <h5 class="font-semibold text-gray-800 mb-2">${factorName}</h5>
                <div class="space-y-1">
        `;
        
        responses.forEach(response => {
            const scoreColor = getScoreColorJS(response.score);
            
            html += `
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600">${response.question.question.substring(0, 80)}...</span>
                    <span class="font-medium px-2 py-1 rounded text-white text-xs" style="background-color: ${scoreColor}">${response.score}/4</span>
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    }
    
    return html;
}

function getReadinessBarColorJS(level) {
    if (level >= 3.76) return '#10b981'; // Green
    if (level >= 2.51) return '#eab308'; // Yellow
    if (level >= 1.26) return '#f97316'; // Orange
    return '#ef4444'; // Red
}

function getScoreColorJS(score) {
    if (score >= 3.76) return '#10b981'; // Green
    if (score >= 2.51) return '#eab308'; // Yellow
    if (score >= 1.26) return '#f97316'; // Orange
    return '#ef4444'; // Red
}

function deleteAssessment(assessmentId) {
    if (confirm('Are you sure you want to delete this assessment? This action cannot be undone.')) {
        fetch(`/assessment/${assessmentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting assessment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting assessment');
        });
    }
}

function closeModal() {
    document.getElementById('assessmentModal').classList.add('hidden');
    currentAssessmentData = null;
}

document.getElementById('assessmentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endpush