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
                <div class="col-span-5">Question</div>
                <div class="col-span-1">Weight</div>
                <div class="col-span-2">Corrective Actions</div>
                <div class="col-span-3 text-center">Actions</div>
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
                <div class="col-span-5">
                    <input type="text" 
                           value="{{ $question->question }}" 
                           class="question-input w-full border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           data-original="{{ $question->question }}"
                           onchange="markAsChanged(this)" />
                </div>
                
                {{-- Weight Column --}}
                <div class="col-span-1">
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
                
                {{-- Corrective Actions Column --}}
                <div class="col-span-2">
                    <div class="flex items-center space-x-2">
                        @php
                            $actionsCount = $question->correctiveActions->count();
                        @endphp
                        <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full">
                            {{ $actionsCount }} {{ $actionsCount == 1 ? 'action' : 'actions' }}
                        </span>
                        <button onclick="manageCorrectiveActions({{ $question->id }}, '{{ addslashes($question->question) }}')" 
                                class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                            <i class="fas fa-cogs mr-1"></i>
                            {{ $actionsCount > 0 ? 'Edit' : 'Add' }}
                        </button>
                    </div>
                    
                    {{-- Show existing actions preview --}}
                    @if($question->correctiveActions->count() > 0)
                        <div class="mt-1">
                            @foreach($question->correctiveActions->take(2) as $action)
                                <div class="text-xs text-gray-500 truncate" title="{{ $action->action }}">
                                    • {{ Str::limit($action->action, 40) }}
                                </div>
                            @endforeach
                            @if($question->correctiveActions->count() > 2)
                                <div class="text-xs text-gray-400">
                                    +{{ $question->correctiveActions->count() - 2 }} more...
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
                
                {{-- Action Buttons Column --}}
                <div class="col-span-3 flex justify-center space-x-2">
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
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Question</h3>
            <form id="addQuestionForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Factor</label>
                        <select id="modalFactorSelect" name="factor_id" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($factors as $factor)
                                <option value="{{ $factor->id }}" {{ $currentFactor->id == $factor->id ? 'selected' : '' }}>
                                    {{ $factor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Question</label>
                        <textarea id="modalQuestion" name="question" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your question here..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Weight</label>
                        <select id="modalWeight" name="weight" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>X{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="addCorrectiveActions" class="mr-2">
                        <label class="text-sm text-gray-700">Add corrective actions now</label>
                    </div>
                </div>
                
                {{-- Corrective Actions Section (initially hidden) --}}
                <div id="correctiveActionsSection" class="hidden mt-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-md font-medium text-gray-800 mb-3">Corrective Actions</h4>
                    <div id="correctiveActionsList">
                        {{-- Dynamic action items will be added here --}}
                    </div>
                    <button type="button" onclick="addCorrectiveActionRow()" class="mt-2 text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-plus mr-1"></i>Add Action
                    </button>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
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

{{-- Manage Corrective Actions Modal --}}
<div id="manageActionsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="manageActionsTitle">Manage Corrective Actions</h3>
                <button onclick="closeManageActionsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-800" id="questionPreview"></p>
            </div>
            
            <div class="space-y-3" id="existingActionsList">
                {{-- Existing actions will be loaded here --}}
            </div>
            
            <div class="mt-4">
                <button type="button" onclick="addNewActionRow()" class="w-full py-2 px-4 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-400 hover:text-blue-600 transition duration-150">
                    <i class="fas fa-plus mr-2"></i>Add New Corrective Action
                </button>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button onclick="closeManageActionsModal()" class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400">
                    Close
                </button>
                <button onclick="saveAllActions()" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Save All Actions
                </button>
            </div>
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

.action-row {
    transition: all 0.3s ease;
}

.action-row.removing {
    opacity: 0;
    transform: translateX(-100%);
}
</style>
@endpush

@push('scripts')
<script>
let changedQuestions = new Set();
let currentQuestionId = null;
let actionCounter = 0;

// 🔥 Load departments from PHP variable passed by controller
const departments = @json($departments);

// 🔥 Helper function to generate department datalist options
function generateDepartmentOptions() {
    let options = '<option value="All Departments">';
    
    departments.forEach(dept => {
        options += `<option value="${dept.name}">`;
    });
    
    return options;
}

// 🔥 Helper function to create datalist element
function createDepartmentDatalist() {
    const datalistId = 'departmentSuggestions-' + Math.random().toString(36).substr(2, 9);
    return {
        datalistId: datalistId,
        datalistHtml: `
            <datalist id="${datalistId}">
                <option value="All Departments">
                ${departments.map(dept => `<option value="${dept.name}">`).join('')}
            </datalist>
        `
    };
}

// Factor selector change
document.getElementById('factorSelect').addEventListener('change', function() {
    const selectedFactor = this.value;
    window.location.href = `{{ route('questions.edit') }}?factor=${selectedFactor}`;
});

// Add corrective actions checkbox toggle
document.getElementById('addCorrectiveActions').addEventListener('change', function() {
    const section = document.getElementById('correctiveActionsSection');
    if (this.checked) {
        section.classList.remove('hidden');
        addCorrectiveActionRow();
    } else {
        section.classList.add('hidden');
        document.getElementById('correctiveActionsList').innerHTML = '';
    }
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

// Manage corrective actions for a question
function manageCorrectiveActions(questionId, questionText) {
    currentQuestionId = questionId;
    document.getElementById('manageActionsTitle').textContent = `Manage Corrective Actions`;
    document.getElementById('questionPreview').textContent = questionText;
    
    // Load existing actions
    loadExistingActions(questionId);
    
    document.getElementById('manageActionsModal').classList.remove('hidden');
}

function loadExistingActions(questionId) {
    fetch(`/questions/${questionId}/corrective-actions`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('existingActionsList');
        container.innerHTML = '';
        
        if (data.actions && data.actions.length > 0) {
            data.actions.forEach(action => {
                addExistingActionRow(action);
            });
        }
    })
    .catch(error => {
        console.error('Error loading actions:', error);
    });
}

function addExistingActionRow(action) {
    const container = document.getElementById('existingActionsList');
    const actionRow = document.createElement('div');
    actionRow.className = 'action-row flex items-center space-x-3 p-3 border border-gray-200 rounded-lg bg-white';
    actionRow.dataset.actionId = action.id;
    
    const { datalistId, datalistHtml } = createDepartmentDatalist();
    
    actionRow.innerHTML = `
        <div class="flex-1">
            <textarea class="action-text w-full border border-gray-300 rounded-md px-3 py-2 text-sm resize-none" 
                      rows="2" placeholder="Enter corrective action...">${action.action}</textarea>
        </div>
        <div class="w-40">
            <input type="text" 
                   class="action-department w-full border border-gray-300 rounded-md px-3 py-2 text-sm" 
                   placeholder="Department name..."
                   value="${action.department || ''}"
                   list="${datalistId}">
            ${datalistHtml}
        </div>
        <button type="button" onclick="removeActionRow(this)" class="text-red-600 hover:text-red-800 p-2">
            <i class="fas fa-trash"></i>
        </button>
    `;
    
    container.appendChild(actionRow);
}

function addNewActionRow() {
    const container = document.getElementById('existingActionsList');
    const actionRow = document.createElement('div');
    actionRow.className = 'action-row flex items-center space-x-3 p-3 border border-gray-200 rounded-lg bg-white';
    actionRow.dataset.actionId = 'new-' + (++actionCounter);
    
    const { datalistId, datalistHtml } = createDepartmentDatalist();
    
    actionRow.innerHTML = `
        <div class="flex-1">
            <textarea class="action-text w-full border border-gray-300 rounded-md px-3 py-2 text-sm resize-none" 
                      rows="2" placeholder="Enter corrective action..."></textarea>
        </div>
        <div class="w-40">
            <input type="text" 
                   class="action-department w-full border border-gray-300 rounded-md px-3 py-2 text-sm" 
                   placeholder="Department name..."
                   list="${datalistId}">
            ${datalistHtml}
        </div>
        <button type="button" onclick="removeActionRow(this)" class="text-red-600 hover:text-red-800 p-2">
            <i class="fas fa-trash"></i>
        </button>
    `;
    
    container.appendChild(actionRow);
    
    // Focus on the new textarea
    actionRow.querySelector('.action-text').focus();
}

function removeActionRow(button) {
    const row = button.closest('.action-row');
    row.classList.add('removing');
    setTimeout(() => {
        row.remove();
    }, 300);
}

function saveAllActions() {
    const actionRows = document.querySelectorAll('#existingActionsList .action-row');
    const actions = [];
    
    actionRows.forEach(row => {
        const actionText = row.querySelector('.action-text').value.trim();
        const department = row.querySelector('.action-department').value.trim();
        const actionId = row.dataset.actionId;
        
        if (actionText) {
            actions.push({
                id: actionId.startsWith('new-') ? null : actionId,
                action: actionText,
                department: department || null
            });
        }
    });
    
    fetch(`/questions/${currentQuestionId}/corrective-actions`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ actions: actions })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage(data.message || 'Corrective actions saved successfully!');
            closeManageActionsModal();
            // Refresh the page to show updated action counts
            location.reload();
        } else {
            showErrorMessage(data.message || 'Error saving corrective actions');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('Error saving corrective actions');
    });
}

function closeManageActionsModal() {
    document.getElementById('manageActionsModal').classList.add('hidden');
    currentQuestionId = null;
}

function addCorrectiveActionRow() {
    const container = document.getElementById('correctiveActionsList');
    const actionRow = document.createElement('div');
    actionRow.className = 'flex items-center space-x-3 mb-2';
    
    const { datalistId, datalistHtml } = createDepartmentDatalist();
    
    console.log(datalistId, datalistHtml);
    actionRow.innerHTML = `
        <div class="flex-1">
            <input type="text" class="new-action-text w-full border border-gray-300 rounded-md px-3 py-2 text-sm" 
                   placeholder="Enter corrective action...">
        </div>
        <div class="w-40">
            <input type="text" 
                   class="new-action-department w-full border border-gray-300 rounded-md px-3 py-2 text-sm" 
                   placeholder="Department name..."
                   list="${datalistId}">
            ${datalistHtml}
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 p-2">
            <i class="fas fa-trash"></i>
        </button>
    `;
    
    container.appendChild(actionRow);
}

// Rest of the existing functions remain the same...
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

function deleteQuestion(questionId) {
    if (confirm('Are you sure you want to delete this question? This will also delete all associated corrective actions.')) {
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
            location.reload();
        } else {
            showErrorMessage('Failed to duplicate question');
        }
    })
    .catch(error => {
        showErrorMessage('Error duplicating question');
    });
}

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

function openAddQuestionModal() {
    document.getElementById('addQuestionModal').classList.remove('hidden');
}

function closeAddQuestionModal() {
    document.getElementById('addQuestionModal').classList.add('hidden');
    document.getElementById('addQuestionForm').reset();
    document.getElementById('correctiveActionsSection').classList.add('hidden');
    document.getElementById('correctiveActionsList').innerHTML = '';
    document.getElementById('addCorrectiveActions').checked = false;
}

document.getElementById('addQuestionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    const correctiveActions = [];
    if (document.getElementById('addCorrectiveActions').checked) {
        document.querySelectorAll('#correctiveActionsList .flex').forEach(row => {
            const actionText = row.querySelector('.new-action-text').value.trim();
            const department = row.querySelector('.new-action-department').value;
            
            if (actionText) {
                correctiveActions.push({
                    action: actionText,
                    department: department || null
                });
            }
        });
    }
    
    formData.append('corrective_actions', JSON.stringify(correctiveActions));
    
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
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showSuccessMessage(data.message);
            closeAddQuestionModal();
            location.reload();
        } else {
            showErrorMessage(data.message || 'Failed to add question');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('Error adding question: ' + error.message);
    });
});

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