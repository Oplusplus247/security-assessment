@extends('layouts.dashboard')

@section('page-title', 'Edit Questions')

@section('content')
{{-- Add CSRF meta tag --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="space-y-6">
    {{-- Header with title and dropdown --}}
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">Edit Questions</h2>
        <select id="factorSelect" class="border border-gray-300 rounded-md px-4 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            @foreach($factors as $factor)
                <option value="{{ $factor->slug }}" {{ $currentFactor->slug === $factor->slug ? 'selected' : '' }}>
                    {{ $factor->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Success Message --}}
    <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline" id="successText"></span>
    </div>

    {{-- Error Message --}}
    <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline" id="errorText"></span>
    </div>

    {{-- Main Table Container --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Table Header --}}
        <div class="assessment-form-table-header px-6 py-4">
            <div class="grid grid-cols-12 gap-4 text-sm font-semibold text-white">
                <div class="col-span-1">No.</div>
                <div class="col-span-7">Question</div>
                <div class="col-span-2">Weight</div>
                <div class="col-span-2 text-center">Actions</div>
            </div>
        </div>
        
        {{-- Table Body --}}
        <div id="questionsContainer" class="divide-y divide-gray-200">
            @forelse ($questions as $index => $question)
            <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center text-sm bg-white hover:bg-gray-50 question-row" data-question-id="{{ $question->id }}">
                {{-- No. Column --}}
                <div class="col-span-1">
                    <span class="text-gray-700 font-medium">{{ $questions->firstItem() + $index }}</span>
                </div>
                
                {{-- Question Column --}}
                <div class="col-span-7">
                    <input type="text" 
                           value="{{ $question->question }}" 
                           class="question-input w-full border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           data-original="{{ $question->question }}"
                           onchange="markAsChanged(this)" />
                </div>
                
                {{-- Weight Column --}}
                <div class="col-span-2">
                    <select class="weight-select w-full border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                            data-original="{{ $question->weight ?? 1 }}"
                            onchange="markAsChanged(this)">
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ ($question->weight ?? 1) == $i ? 'selected' : '' }}>
                                X{{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>
                
                {{-- Action Buttons Column --}}
                <div class="col-span-2 flex justify-center space-x-2">
                    <button onclick="updateQuestion({{ $question->id }})" 
                            class="update-btn text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-50 transition duration-150 hidden">
                        <i class="fas fa-save text-lg"></i>
                    </button>
                    <button onclick="cancelEdit({{ $question->id }})" 
                            class="cancel-btn text-gray-600 hover:text-gray-800 p-2 rounded-full hover:bg-gray-50 transition duration-150 hidden">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                    <button onclick="duplicateQuestion({{ $question->id }})" 
                            class="text-green-600 hover:text-green-800 p-2 rounded-full hover:bg-green-50 transition duration-150">
                        <i class="fas fa-copy text-lg"></i>
                    </button>
                    <button onclick="deleteQuestion({{ $question->id }})" 
                            class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-50 transition duration-150">
                        <i class="fas fa-trash text-lg"></i>
                    </button>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-gray-500">
                No questions found for {{ $currentFactor->name }}. 
                <button onclick="openAddQuestionModal()" class="text-blue-600 hover:text-blue-800 font-medium">
                    Add the first question
                </button>
            </div>
            @endforelse
        </div>

        {{-- Pagination Footer --}}
        @if($questions->hasPages())
        <div class="bg-white px-6 py-4 flex items-center justify-between border-t border-gray-200">
            <div class="flex-1 flex justify-between sm:hidden">
                @if($questions->previousPageUrl())
                <a href="{{ $questions->previousPageUrl() }}&factor={{ $currentFactor->slug }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Previous
                </a>
                @endif
                
                @if($questions->nextPageUrl())
                <a href="{{ $questions->nextPageUrl() }}&factor={{ $currentFactor->slug }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Next
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
                @endif
            </div>
            
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">{{ $questions->firstItem() }}</span> to <span class="font-medium">{{ $questions->lastItem() }}</span> of <span class="font-medium">{{ $questions->total() }}</span> results
                    </p>
                </div>
                <div>
                    {{ $questions->appends(['factor' => $currentFactor->slug])->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Add Question Button --}}
    <div class="flex justify-end space-x-3">
        <button onclick="bulkSaveQuestions()" id="bulkSaveBtn" class="hidden bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-md shadow-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-150">
            <i class="fas fa-save mr-2"></i>
            Save All Changes
        </button>
        <button onclick="openAddQuestionModal()" type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-md shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150">
            <i class="fas fa-plus mr-2"></i>
            Add a Question
        </button>
    </div>
</div>

{{-- Add Question Modal --}}
<div id="addQuestionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Question</h3>
            <form id="addQuestionForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Factor</label>
                    <select id="modalFactorSelect" name="factor_id" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($factors as $factor)
                            <option value="{{ $factor->id }}" {{ $currentFactor->id == $factor->id ? 'selected' : '' }}>
                                {{ $factor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Question</label>
                    <textarea id="modalQuestion" name="question" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your question here..."></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Weight</label>
                    <select id="modalWeight" name="weight" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>X{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeAddQuestionModal()" class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Add Question
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

.question-row.changed {
    background-color: #fef3c7 !important;
}

.question-row.changed .update-btn,
.question-row.changed .cancel-btn {
    display: inline-flex !important;
}

input[type="text"]:focus,
select:focus,
textarea:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.hover\:bg-gray-50:hover {
    background-color: #f9fafb;
}

.hover\:text-red-600:hover {
    color: #dc2626;
}

.hover\:bg-red-50:hover {
    background-color: #fef2f2;
}
</style>
@endpush

@push('scripts')
<script>
let changedQuestions = new Set();

// Factor selector change
document.getElementById('factorSelect').addEventListener('change', function() {
    const selectedFactor = this.value;
    window.location.href = `{{ route('questions.edit') }}?factor=${selectedFactor}`;
});

// Mark question as changed
function markAsChanged(element) {
    const row = element.closest('.question-row');
    const questionId = row.dataset.questionId;
    
    row.classList.add('changed');
    changedQuestions.add(questionId);
    
    // Show bulk save button
    document.getElementById('bulkSaveBtn').classList.remove('hidden');
}

// Update single question
function updateQuestion(questionId) {
    const row = document.querySelector(`[data-question-id="${questionId}"]`);
    const questionText = row.querySelector('.question-input').value;
    const weight = row.querySelector('.weight-select').value;
    
    fetch(`/questions/${questionId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            question: questionText,
            factor_id: {{ $currentFactor->id }},
            weight: weight
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage(data.message);
            row.classList.remove('changed');
            changedQuestions.delete(questionId);
            
            if (changedQuestions.size === 0) {
                document.getElementById('bulkSaveBtn').classList.add('hidden');
            }
        } else {
            showErrorMessage('Failed to update question');
        }
    })
    .catch(error => {
        showErrorMessage('Error updating question');
    });
}

// Cancel edit
function cancelEdit(questionId) {
    const row = document.querySelector(`[data-question-id="${questionId}"]`);
    const questionInput = row.querySelector('.question-input');
    const weightSelect = row.querySelector('.weight-select');
    
    questionInput.value = questionInput.dataset.original;
    weightSelect.value = weightSelect.dataset.original;
    
    row.classList.remove('changed');
    changedQuestions.delete(questionId);
    
    if (changedQuestions.size === 0) {
        document.getElementById('bulkSaveBtn').classList.add('hidden');
    }
}

// Delete question
function deleteQuestion(questionId) {
    if (confirm('Are you sure you want to delete this question?')) {
        fetch(`/questions/${questionId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage(data.message);
                document.querySelector(`[data-question-id="${questionId}"]`).remove();
            } else {
                showErrorMessage('Failed to delete question');
            }
        })
        .catch(error => {
            showErrorMessage('Error deleting question');
        });
    }
}

// Duplicate question
function duplicateQuestion(questionId) {
    fetch(`/questions/${questionId}/duplicate`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage(data.message);
            location.reload(); // Refresh to show duplicated question
        } else {
            showErrorMessage('Failed to duplicate question');
        }
    })
    .catch(error => {
        showErrorMessage('Error duplicating question');
    });
}

// Bulk save questions
function bulkSaveQuestions() {
    const questions = [];
    
    changedQuestions.forEach(questionId => {
        const row = document.querySelector(`[data-question-id="${questionId}"]`);
        questions.push({
            id: questionId,
            question: row.querySelector('.question-input').value,
            weight: row.querySelector('.weight-select').value
        });
    });
    
    fetch('/questions/bulk-update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ questions: questions })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage(data.message);
            
            // Reset all changed states
            changedQuestions.clear();
            document.querySelectorAll('.question-row.changed').forEach(row => {
                row.classList.remove('changed');
            });
            document.getElementById('bulkSaveBtn').classList.add('hidden');
        } else {
            showErrorMessage('Failed to update questions');
        }
    })
    .catch(error => {
        showErrorMessage('Error updating questions');
    });
}

// Modal functions
function openAddQuestionModal() {
    document.getElementById('addQuestionModal').classList.remove('hidden');
}

function closeAddQuestionModal() {
    document.getElementById('addQuestionModal').classList.add('hidden');
    document.getElementById('addQuestionForm').reset();
}

// Add question form submission
document.getElementById('addQuestionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Debug: Log the form data
    console.log('Form data:');
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }
    
    fetch('{{ route("questions.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        // Check if response is HTML (error page) instead of JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('text/html')) {
            throw new Error('Server returned HTML instead of JSON. Check for validation errors or redirects.');
        }
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showSuccessMessage(data.message);
            closeAddQuestionModal();
            location.reload(); // Refresh to show new question
        } else {
            showErrorMessage(data.message || 'Failed to add question');
            console.log('Validation errors:', data.errors);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('Error adding question: ' + error.message);
    });
});

// Message functions
function showSuccessMessage(message) {
    const successDiv = document.getElementById('successMessage');
    document.getElementById('successText').textContent = message;
    successDiv.classList.remove('hidden');
    
    setTimeout(() => {
        successDiv.classList.add('hidden');
    }, 5000);
}

function showErrorMessage(message) {
    const errorDiv = document.getElementById('errorMessage');
    document.getElementById('errorText').textContent = message;
    errorDiv.classList.remove('hidden');
    
    setTimeout(() => {
        errorDiv.classList.add('hidden');
    }, 5000);
}
</script>
@endpush