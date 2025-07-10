@extends('layouts.dashboard')

@section('page-title', 'Assessment Form')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="space-y-6" id="assessmentContainer">
    {{-- Success/Error Messages --}}
    <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline" id="successText"></span>
    </div>

    <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline" id="errorText"></span>
    </div>

    {{-- Navigation Tabs --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <nav class="flex flex-wrap gap-2 text-sm font-medium" id="factorTabs">
            @foreach($factors as $factor)
                <a href="#" 
                   data-factor="{{ $factor->slug }}" 
                   class="factor-tab py-2 px-4 rounded-lg whitespace-nowrap transition-all duration-200 {{ $currentFactor->slug === $factor->slug ? 'text-white bg-blue-800 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}"
                   aria-current="{{ $currentFactor->slug === $factor->slug ? 'page' : 'false' }}">
                    {{ $factor->name }}
                    <span class="factor-progress ml-1 text-xs opacity-75" id="progress-{{ $factor->slug }}"></span>
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Assessment Questions Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Table Header --}}
        <div class="assessment-form-table-header px-6 py-4">
            <div class="grid grid-cols-12 gap-4 text-xs font-medium text-white uppercase tracking-wider">
                <div class="col-span-1">No.</div>
                <div class="col-span-5">Questions</div>
                <div class="col-span-2">Response</div>
                <div class="col-span-1">Weight</div>
                <div class="col-span-2">Weighted Score</div>
                <div class="col-span-1">Comments</div>
            </div>
        </div>
        
        {{-- Table Body --}}
        <div class="divide-y divide-gray-200" id="questionsContainer">
            @foreach($questions as $index => $question)
            <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center text-sm bg-white hover:bg-gray-50 question-row" data-question-id="{{ $question->id }}">
                {{-- Question Number --}}
                <div class="col-span-1 text-center">
                    <span class="text-gray-700 font-medium">{{ $index + 1 }}</span>
                </div>
                
                {{-- Question Text --}}
                <div class="col-span-5">
                    <span class="text-gray-900">{{ $question->question }}</span>
                    @if($question->description)
                        <p class="text-xs text-gray-500 mt-1">{{ $question->description }}</p>
                    @endif
                </div>
                
                {{-- Response Dropdown --}}
                <div class="col-span-2">
                    <select class="response-select w-full border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                            data-question-id="{{ $question->id }}"
                            onchange="handleResponseChange(this)">
                        <option value="">Not Yet Answered</option>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" 
                                {{ isset($existingResponses[$question->id]) && $existingResponses[$question->id]->score == $i ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>
                
                {{-- Weight --}}
                <div class="col-span-1 text-center">
                    <span class="text-gray-700 font-medium">X{{ $question->weight ?? 1 }}</span>
                </div>
                
                {{-- Weighted Score --}}
                <div class="col-span-2 text-center">
                    <span class="weighted-score text-gray-700 font-medium" id="weighted-{{ $question->id }}">
                        {{ isset($existingResponses[$question->id]) ? ($existingResponses[$question->id]->score * ($question->weight ?? 1)) : '-' }}
                    </span>
                </div>
                
                {{-- Comments --}}
                <div class="col-span-1">
                    <button type="button" 
                            class="text-blue-600 hover:text-blue-800 text-xs"
                            onclick="openCommentModal({{ $question->id }}, '{{ $question->question }}')">
                        <i class="fas fa-comment"></i>
                        <span class="comment-indicator {{ isset($existingResponses[$question->id]) && $existingResponses[$question->id]->comment ? 'text-blue-600' : 'text-gray-400' }}" id="comment-indicator-{{ $question->id }}">
                            {{ isset($existingResponses[$question->id]) && $existingResponses[$question->id]->comment ? '✓' : '+' }}
                        </span>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Navigation Buttons --}}
    <div class="flex justify-between items-center">
        <button type="button" 
                id="prevButton" 
                onclick="navigateToFactor('prev')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-arrow-left mr-2"></i>
            Previous
        </button>

        <div class="flex space-x-3">
            <button type="button" 
                    onclick="saveProgress()"
                    class="inline-flex items-center px-4 py-2 border border-blue-300 shadow-sm text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-save mr-2"></i>
                Save Progress
            </button>
            
            <button type="button" 
                    id="nextButton" 
                    onclick="navigateToFactor('next')"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Next
                <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </div>

    {{-- Form Progress --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div class="text-gray-700 font-semibold">Assessment Progress</div>
            <div class="flex items-center space-x-4">
                <div class="w-64 bg-gray-200 rounded-full h-3">
                    <div id="progressBar" class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: {{ $progressPercentage }}%;"></div>
                </div>
                <span class="text-sm font-medium text-gray-700 min-w-[4rem]" id="progressText">
                    {{ $progressPercentage }}%
                </span>
            </div>
        </div>
        <div class="mt-2 text-sm text-gray-600">
            <span id="progressDetails">{{ $answeredQuestions }} of {{ $totalQuestions }} questions completed</span>
        </div>
    </div>
    
    {{-- Submit Button (shown when all completed) --}}
    <div class="flex justify-center" id="submitSection" style="display: {{ $progressPercentage >= 100 ? 'block' : 'none' }};">
        <button type="button" 
                onclick="submitAssessment()" 
                class="inline-flex justify-center py-3 px-8 border border-transparent shadow-sm text-lg font-semibold rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150">
            <i class="fas fa-check-circle mr-2"></i>
            Complete Assessment
        </button>
    </div>
</div>

{{-- Comment Modal --}}
<div id="commentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4" id="commentModalTitle">Add Comment</h3>
            <form id="commentForm">
                <input type="hidden" id="commentQuestionId" value="">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Comment (Optional)</label>
                    <textarea id="commentText" rows="4" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Add any additional notes or comments..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeCommentModal()" class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Save Comment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.assessment-form-table-header {
    background-color: #25408f !important;
    color: white !important;
}

.factor-tab {
    position: relative;
}

.factor-tab.completed::after {
    content: '✓';
    position: absolute;
    top: -2px;
    right: -2px;
    background: #3ec516;
    color: white;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.question-row.answered {
    background-color: #f0f9ff !important;
}

select:focus, textarea:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.hover\:bg-gray-50:hover {
    background-color: #f9fafb;
}

#progressBar {
    transition: width 0.5s ease-in-out;
}

.comment-indicator {
    transition: all 0.2s ease;
}

.loading {
    opacity: 0.6;
    pointer-events: none;
}
</style>
@endpush

@push('scripts')
<script>
let currentFactorIndex = {{ $factors->pluck('slug')->search($currentFactor->slug) }};
let factors = @json($factors->pluck('slug'));
let currentAssessmentId = {{ $currentAssessment->id ?? 'null' }};
let isLoading = false;
let questionComments = {};

document.addEventListener('DOMContentLoaded', function() {
    updateNavigationButtons();
    loadFactorProgress();
});

// Handle response changes
function handleResponseChange(selectElement) {
    const questionId = selectElement.dataset.questionId;
    const score = selectElement.value;
    
    if (score) {
        saveResponse(questionId, score);
    }
}

// Save individual response
function saveResponse(questionId, score, comment = null) {
    if (isLoading) return;
    
    isLoading = true;
    const row = document.querySelector(`[data-question-id="${questionId}"]`);
    row.classList.add('loading');
    
    fetch('{{ route("assessment.save-response") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            question_id: questionId,
            score: score,
            comment: comment || questionComments[questionId] || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update weighted score
            document.getElementById(`weighted-${questionId}`).textContent = data.weighted_score;
            
            // Mark row as answered
            row.classList.add('answered');
            
            // Update progress
            updateProgress();
            
            // Show success message briefly
            showMessage('Response saved!', 'success', 2000);
            
            currentAssessmentId = data.assessment_id;
        } else {
            showMessage(data.message || 'Error saving response', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error saving response', 'error');
    })
    .finally(() => {
        isLoading = false;
        row.classList.remove('loading');
    });
}

// Navigate between factors
function navigateToFactor(direction) {
    let nextIndex;
    
    if (direction === 'next') {
        nextIndex = currentFactorIndex + 1;
    } else if (direction === 'prev') {
        nextIndex = currentFactorIndex - 1;
    }
    
    if (nextIndex >= 0 && nextIndex < factors.length) {
        const nextFactorSlug = factors[nextIndex];
        window.location.href = `{{ route('assessment.form') }}?factor=${nextFactorSlug}`;
    }
}

// Update navigation button states
function updateNavigationButtons() {
    const prevButton = document.getElementById('prevButton');
    const nextButton = document.getElementById('nextButton');
    
    prevButton.disabled = currentFactorIndex === 0;
    
    if (currentFactorIndex === factors.length - 1) {
        nextButton.innerHTML = '<i class="fas fa-flag-checkered mr-2"></i>Finish';
        nextButton.onclick = () => {
            // Check if current factor is complete before finishing
            updateProgress().then(() => {
                const progressPercentage = parseInt(document.getElementById('progressText').textContent);
                if (progressPercentage >= 100) {
                    document.getElementById('submitSection').style.display = 'block';
                    document.getElementById('submitSection').scrollIntoView({ behavior: 'smooth' });
                } else {
                    showMessage('Please complete all questions before finishing the assessment.', 'warning');
                }
            });
        };
    }
}

// Update progress
function updateProgress() {
    return fetch('{{ route("assessment.progress") }}', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const progressDetails = document.getElementById('progressDetails');
        const submitSection = document.getElementById('submitSection');
        
        progressBar.style.width = data.progress_percentage + '%';
        progressText.textContent = data.progress_percentage + '%';
        progressDetails.textContent = `${data.answered_questions} of ${data.total_questions} questions completed`;
        
        // Show submit section if 100% complete
        if (data.progress_percentage >= 100) {
            submitSection.style.display = 'block';
        } else {
            submitSection.style.display = 'none';
        }
        
        return data;
    })
    .catch(error => {
        console.error('Error updating progress:', error);
    });
}

// Load factor progress indicators
function loadFactorProgress() {
    factors.forEach(factorSlug => {
        // This would require an additional endpoint to get per-factor progress
        // For now, we'll mark completed factors based on current progress
    });
}

// Save current progress
function saveProgress() {
    showMessage('Progress saved successfully!', 'success', 3000);
    updateProgress();
}

// Submit final assessment
function submitAssessment() {
    if (isLoading) return;
    
    if (!confirm('Are you sure you want to submit your assessment? This action cannot be undone.')) {
        return;
    }
    
    isLoading = true;
    document.getElementById('assessmentContainer').classList.add('loading');
    
    fetch('{{ route("assessment.submit") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            
            // Redirect to results or historical assessments
            setTimeout(() => {
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    window.location.href = '{{ route("dashboard.historical") }}';
                }
            }, 2000);
        } else {
            showMessage(data.message || 'Error submitting assessment', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error submitting assessment', 'error');
    })
    .finally(() => {
        isLoading = false;
        document.getElementById('assessmentContainer').classList.remove('loading');
    });
}

// Comment modal functions
function openCommentModal(questionId, questionText) {
    document.getElementById('commentQuestionId').value = questionId;
    document.getElementById('commentModalTitle').textContent = `Comment for Question ${questionId}`;
    document.getElementById('commentText').value = questionComments[questionId] || '';
    document.getElementById('commentModal').classList.remove('hidden');
}

function closeCommentModal() {
    document.getElementById('commentModal').classList.add('hidden');
    document.getElementById('commentForm').reset();
}

// Handle comment form submission
document.getElementById('commentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const questionId = document.getElementById('commentQuestionId').value;
    const comment = document.getElementById('commentText').value;
    
    questionComments[questionId] = comment;
    
    // Update comment indicator
    const indicator = document.getElementById(`comment-indicator-${questionId}`);
    if (comment.trim()) {
        indicator.textContent = '✓';
        indicator.classList.remove('text-gray-400');
        indicator.classList.add('text-blue-600');
    } else {
        indicator.textContent = '+';
        indicator.classList.remove('text-blue-600');
        indicator.classList.add('text-gray-400');
    }
    
    // Save the response if there's a score selected
    const scoreSelect = document.querySelector(`[data-question-id="${questionId}"]`);
    if (scoreSelect && scoreSelect.value) {
        saveResponse(questionId, scoreSelect.value, comment);
    }
    
    closeCommentModal();
    showMessage('Comment saved!', 'success', 2000);
});

// Factor tab navigation
document.querySelectorAll('.factor-tab').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        const factorSlug = this.dataset.factor;
        if (factorSlug !== '{{ $currentFactor->slug }}') {
            window.location.href = `{{ route('assessment.form') }}?factor=${factorSlug}`;
        }
    });
});

// Message functions
function showMessage(message, type = 'success', duration = 5000) {
    const messageDiv = document.getElementById(type === 'success' ? 'successMessage' : 'errorMessage');
    const textElement = document.getElementById(type === 'success' ? 'successText' : 'errorText');
    
    textElement.textContent = message;
    messageDiv.classList.remove('hidden');
    
    if (duration > 0) {
        setTimeout(() => {
            messageDiv.classList.add('hidden');
        }, duration);
    }
}

// Auto-save functionality (save responses periodically)
setInterval(() => {
    if (!isLoading && currentAssessmentId) {
        // Auto-save logic could go here
    }
}, 30000); // Auto-save every 30 seconds
</script>
@endpush