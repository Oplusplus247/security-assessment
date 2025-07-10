{{-- Create new file: resources/views/admin/corrective-actions.blade.php --}}
@extends('layouts.dashboard')

@section('page-title', 'Manage Corrective Actions')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Manage Corrective Actions</h2>
            <button onclick="showAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>
                Add Corrective Action
            </button>
        </div>
    </div>

    {{-- Corrective Actions by Factor --}}
    @foreach($factors as $factor)
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Factor Header --}}
        <div class="assessment-form-table-header px-6 py-4">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-white">{{ $factor->name }}</h3>
                <span class="text-sm text-white opacity-90">{{ $factor->corrective_actions_count }} actions</span>
            </div>
        </div>
        
        {{-- Actions Table --}}
        <div class="divide-y divide-gray-200">
            @forelse($factor->correctiveActions as $action)
            <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center text-sm bg-white hover:bg-gray-50">
                {{-- Question --}}
                <div class="col-span-5">
                    <span class="text-gray-900 font-medium">{{ $action->question->question }}</span>
                    <div class="text-xs text-gray-500">Question ID: {{ $action->question_id }}</div>
                </div>
                
                {{-- Action --}}
                <div class="col-span-4">
                    <span class="text-gray-900">{{ $action->action }}</span>
                </div>
                
                {{-- Department --}}
                <div class="col-span-2">
                    <span class="inline-flex px-2 py-1 text-xs rounded-full {{ getDepartmentBadgeClass($action->department) }}">
                        {{ $action->department ?? 'All Departments' }}
                    </span>
                </div>
                
                {{-- Actions --}}
                <div class="col-span-1">
                    <div class="flex items-center space-x-2">
                        <button onclick="editAction({{ $action->id }})" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                        <button onclick="deleteAction({{ $action->id }})" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center">
                <div class="text-gray-500 mb-2">
                    <i class="fas fa-clipboard text-2xl"></i>
                </div>
                <p class="text-gray-500">No corrective actions defined for this factor.</p>
                <button onclick="showAddModal('{{ $factor->id }}')" class="mt-2 text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-plus mr-1"></i>Add Action
                </button>
            </div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

{{-- Add/Edit Modal --}}
<div id="actionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Add Corrective Action</h3>
                <button onclick="closeActionModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="actionForm">
                <input type="hidden" id="actionId" name="action_id">
                
                <div class="mb-4">
                    <label for="factorSelect" class="block text-sm font-medium text-gray-700 mb-2">Factor</label>
                    <select id="factorSelect" name="factor_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="loadQuestions()">
                        <option value="">Select Factor</option>
                        @foreach($factors as $factor)
                        <option value="{{ $factor->id }}">{{ $factor->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="questionSelect" class="block text-sm font-medium text-gray-700 mb-2">Question</label>
                    <select id="questionSelect" name="question_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Question</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="actionText" class="block text-sm font-medium text-gray-700 mb-2">Corrective Action</label>
                    <textarea id="actionText" name="action" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Describe the corrective action..."></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="departmentSelect" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <select id="departmentSelect" name="department" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Departments</option>
                        <option value="IT Department">IT Department</option>
                        <option value="IR Team">IR Team</option>
                        <option value="Management Support">Management Support</option>
                        <option value="Security Team">Security Team</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeActionModal()" class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>
                        Save Action
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Helper function for department badge colors --}}
@php
function getDepartmentBadgeClass($department) {
    switch($department) {
        case 'IT Department': return 'bg-blue-100 text-blue-800';
        case 'IR Team': return 'bg-green-100 text-green-800';
        case 'Management Support': return 'bg-purple-100 text-purple-800';
        case 'Security Team': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
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
</style>
@endpush

@push('scripts')
<script>
function showAddModal(factorId = '') {
    document.getElementById('modalTitle').textContent = 'Add Corrective Action';
    document.getElementById('actionForm').reset();
    document.getElementById('actionId').value = '';
    
    if (factorId) {
        document.getElementById('factorSelect').value = factorId;
        loadQuestions();
    }
    
    document.getElementById('actionModal').classList.remove('hidden');
}

function editAction(actionId) {
    fetch(`/admin/corrective-actions/${actionId}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        document.getElementById('modalTitle').textContent = 'Edit Corrective Action';
        document.getElementById('actionId').value = data.id;
        document.getElementById('factorSelect').value = data.question.factor_id;
        loadQuestions(() => {
            document.getElementById('questionSelect').value = data.question_id;
        });
        document.getElementById('actionText').value = data.action;
        document.getElementById('departmentSelect').value = data.department || '';
        document.getElementById('actionModal').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error loading action:', error);
        alert('Error loading action details: ' + error.message);
    });
}

function loadQuestions(callback = null) {
    const factorId = document.getElementById('factorSelect').value;
    const questionSelect = document.getElementById('questionSelect');
    
    questionSelect.innerHTML = '<option value="">Loading questions...</option>';
    
    if (factorId) {
        fetch(`/admin/questions/by-factor/${factorId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(questions => {
            questionSelect.innerHTML = '<option value="">Select Question</option>';
            questions.forEach(question => {
                const option = document.createElement('option');
                option.value = question.id;
                option.textContent = question.question.substring(0, 80) + (question.question.length > 80 ? '...' : '');
                questionSelect.appendChild(option);
            });
            
            if (callback) callback();
        })
        .catch(error => {
            console.error('Error loading questions:', error);
            questionSelect.innerHTML = '<option value="">Error loading questions</option>';
        });
    } else {
        questionSelect.innerHTML = '<option value="">Select Question</option>';
    }
}

function deleteAction(actionId) {
    if (confirm('Are you sure you want to delete this corrective action?')) {
        fetch(`/admin/corrective-actions/${actionId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.message || 'Action deleted successfully!');
                location.reload();
            } else {
                alert(data.message || 'Error deleting action');
            }
        })
        .catch(error => {
            console.error('Error deleting action:', error);
            alert('Error deleting action: ' + error.message);
        });
    }
}

function closeActionModal() {
    document.getElementById('actionModal').classList.add('hidden');
}

// Form submission
document.getElementById('actionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data as JSON instead of FormData
    const actionId = document.getElementById('actionId').value;
    const factorId = document.getElementById('factorSelect').value;
    const questionId = document.getElementById('questionSelect').value;
    const actionText = document.getElementById('actionText').value;
    const department = document.getElementById('departmentSelect').value;
    
    // Validation
    if (!factorId) {
        alert('Please select a factor');
        return;
    }
    
    if (!questionId) {
        alert('Please select a question');
        return;
    }
    
    if (!actionText.trim()) {
        alert('Please enter a corrective action');
        return;
    }
    
    const data = {
        question_id: questionId,
        action: actionText.trim(),
        department: (department && department !== 'All Departments') ? department : null
    };
    
    const url = actionId ? `/admin/corrective-actions/${actionId}` : '/admin/corrective-actions';
    const method = actionId ? 'PUT' : 'POST';
    
    // Show loading state
    const submitBtn = document.querySelector('#actionForm button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    submitBtn.disabled = true;
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message || 'Corrective action saved successfully!');
            closeActionModal();
            location.reload();
        } else {
            alert(data.message || 'Error saving action');
            console.error('Server response:', data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error: ' + error.message);
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>
@endpush