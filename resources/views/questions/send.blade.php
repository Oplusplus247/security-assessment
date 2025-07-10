@extends('layouts.dashboard')

@section('page-title', 'Send Questions')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Send Assessment Questions</h2>
                <p class="text-gray-600 mt-1">Send assessment questions to recipients via email</p>
            </div>
            <a href="{{ route('questions.track') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                <i class="fas fa-list mr-2"></i>
                View Tracking
            </a>
        </div>
    </div>

    {{-- Send Form --}}
    <form id="sendQuestionsForm" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="space-y-6">
            {{-- Factor Selection --}}
            <div>
                <label for="factor_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Select Assessment Factor
                </label>
                <select id="factor_id" name="factor_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    <option value="">Choose a factor...</option>
                    @foreach($factors as $factor)
                        <option value="{{ $factor->id }}" data-questions="{{ $factor->questions->count() }}">
                            {{ $factor->name }} ({{ $factor->questions->count() }} questions)
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Questions Preview --}}
            <div id="questionsPreview" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Questions to be sent
                </label>
                <div class="border border-gray-200 rounded-md p-4 bg-gray-50 max-h-64 overflow-y-auto">
                    <div id="questionsList" class="space-y-2"></div>
                </div>
            </div>

            {{-- Email Recipients --}}
            <div>
                <label for="emailInput" class="block text-sm font-medium text-gray-700 mb-2">
                    Email Recipients
                </label>
                <div class="space-y-3">
                    <div class="flex space-x-2">
                        <input type="email" 
                               id="emailInput" 
                               placeholder="Enter email address and press Enter or click Add"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="button" 
                                id="addEmailBtn" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-plus mr-1"></i>
                            Add
                        </button>
                    </div>
                    
                    {{-- Email Tags Container --}}
                    <div id="emailTags" class="flex flex-wrap gap-2"></div>
                    
                    {{-- Quick Add Buttons --}}
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="quick-add-email px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200" data-email="admin@example.com">
                            admin@example.com
                        </button>
                        <button type="button" class="quick-add-email px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200" data-email="manager@example.com">
                            manager@example.com
                        </button>
                        <button type="button" class="quick-add-email px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200" data-email="supervisor@example.com">
                            supervisor@example.com
                        </button>
                    </div>
                </div>
            </div>

            {{-- Send Options --}}
            <div class="border-t pt-6">
                <h3 class="text-sm font-medium text-gray-700 mb-3">Send Options</h3>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" id="includeInstructions" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
                        <span class="ml-2 text-sm text-gray-600">Include assessment instructions</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" id="sendReminder" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-600">Send reminder after 3 days</span>
                    </label>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-end space-x-3 pt-6 border-t">
                <button type="button" 
                        onclick="resetForm()" 
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Reset
                </button>
                <button type="submit" 
                        id="sendBtn"
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Send Questions
                </button>
            </div>
        </div>
    </form>

    {{-- Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-paper-plane text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Sent</p>
                    <p class="text-2xl font-semibold text-gray-900" id="totalSent">{{ $statusCounts['sent'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending</p>
                    <p class="text-2xl font-semibold text-gray-900" id="totalPending">{{ $statusCounts['pending'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Completed</p>
                    <p class="text-2xl font-semibold text-gray-900" id="totalCompleted">{{ $statusCounts['completed'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Declined</p>
                    <p class="text-2xl font-semibold text-gray-900" id="totalDeclined">{{ $statusCounts['declined'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Send Questions Modal --}}
<div id="sendModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div id="modalIcon" class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                <i class="fas fa-paper-plane text-blue-600 text-xl"></i>
            </div>
            <h3 id="modalTitle" class="text-lg leading-6 font-medium text-gray-900">Send Questions</h3>
            <div class="mt-2 px-7 py-3">
                <p id="modalMessage" class="text-sm text-gray-500">
                    Are you sure you want to send the assessment questions to the selected recipients?
                </p>
                <div id="recipientsList" class="mt-3 text-left"></div>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmSendBtn" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    Send
                </button>
                <button id="cancelSendBtn" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Success Modal --}}
<div id="successModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <i class="fas fa-check text-green-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900">Congratulations!</h3>
            <div class="mt-2 px-7 py-3">
                <p id="successMessage" class="text-sm text-gray-500"></p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="okBtn" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-24 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.email-tag {
    display: inline-flex;
    align-items: center;
    background-color: #e0e7ff;
    color: #3730a3;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    border: 1px solid #c7d2fe;
}

.email-tag .remove-email {
    margin-left: 0.5rem;
    cursor: pointer;
    color: #6366f1;
    hover:color: #4f46e5;
}

.email-tag .remove-email:hover {
    color: #4f46e5;
}

.quick-add-email:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

#questionsPreview {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.question-item {
    padding: 0.5rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    font-size: 0.875rem;
}

.question-item .weight {
    background: #dbeafe;
    color: #1e40af;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}
</style>
@endpush

@push('scripts')
<script>
let emails = [];
let selectedQuestions = [];

// Questions data from backend
const questionsData = @json($questions);

document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
});

function initializeEventListeners() {
    // Factor selection change
    document.getElementById('factor_id').addEventListener('change', function() {
        const factorId = this.value;
        if (factorId) {
            loadQuestionsForFactor(factorId);
        } else {
            hideQuestionsPreview();
        }
    });

    // Email input handlers
    document.getElementById('emailInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addEmail();
        }
    });

    document.getElementById('addEmailBtn').addEventListener('click', addEmail);

    // Quick add email buttons
    document.querySelectorAll('.quick-add-email').forEach(btn => {
        btn.addEventListener('click', function() {
            const email = this.getAttribute('data-email');
            addEmailToList(email);
        });
    });

    // Form submission
    document.getElementById('sendQuestionsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        validateAndShowModal();
    });

    // Modal handlers
    document.getElementById('confirmSendBtn').addEventListener('click', sendQuestions);
    document.getElementById('cancelSendBtn').addEventListener('click', hideModal);
    document.getElementById('okBtn').addEventListener('click', hideSuccessModal);
}

function loadQuestionsForFactor(factorId) {
    // Find factor questions
    const factor = @json($factors).find(f => f.id == factorId);
    
    if (factor) {
        fetch(`/questions/factor/${factor.id}/questions`)
            .then(response => response.json())
            .then(data => {
                if (data.questions) {
                    selectedQuestions = data.questions.data || data.questions;
                    showQuestionsPreview(selectedQuestions);
                }
            })
            .catch(error => {
                console.error('Error loading questions:', error);
                // Fallback to questions from blade template if available
                const factorQuestions = questionsData[factor.name] || [];
                selectedQuestions = factorQuestions;
                showQuestionsPreview(factorQuestions);
            });
    }
}

function showQuestionsPreview(questions) {
    const preview = document.getElementById('questionsPreview');
    const questionsList = document.getElementById('questionsList');
    
    questionsList.innerHTML = '';
    
    questions.forEach((question, index) => {
        const questionItem = document.createElement('div');
        questionItem.className = 'question-item';
        questionItem.innerHTML = `
            <div class="flex justify-between items-start">
                <span class="flex-1">${index + 1}. ${question.question}</span>
                <span class="weight ml-2">×${question.weight || 1}</span>
            </div>
        `;
        questionsList.appendChild(questionItem);
    });
    
    preview.classList.remove('hidden');
}

function hideQuestionsPreview() {
    document.getElementById('questionsPreview').classList.add('hidden');
    selectedQuestions = [];
}

function addEmail() {
    const emailInput = document.getElementById('emailInput');
    const email = emailInput.value.trim();
    
    if (email) {
        addEmailToList(email);
        emailInput.value = '';
    }
}

function addEmailToList(email) {
    if (!isValidEmail(email)) {
        alert('Please enter a valid email address');
        return;
    }
    
    if (emails.includes(email)) {
        alert('Email already added');
        return;
    }
    
    emails.push(email);
    updateEmailTags();
}

function removeEmail(email) {
    emails = emails.filter(e => e !== email);
    updateEmailTags();
}

function updateEmailTags() {
    const container = document.getElementById('emailTags');
    container.innerHTML = '';
    
    emails.forEach(email => {
        const tag = document.createElement('div');
        tag.className = 'email-tag';
        tag.innerHTML = `
            ${email}
            <i class="fas fa-times remove-email" onclick="removeEmail('${email}')"></i>
        `;
        container.appendChild(tag);
    });
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validateAndShowModal() {
    const factorId = document.getElementById('factor_id').value;
    
    if (!factorId) {
        alert('Please select an assessment factor');
        return;
    }
    
    if (emails.length === 0) {
        alert('Please add at least one email recipient');
        return;
    }
    
    if (selectedQuestions.length === 0) {
        alert('No questions found for the selected factor');
        return;
    }
    
    showModal();
}

function showModal() {
    const factor = @json($factors).find(f => f.id == document.getElementById('factor_id').value);
    const recipientsList = document.getElementById('recipientsList');
    
    recipientsList.innerHTML = `
        <div class="text-sm">
            <p><strong>Factor:</strong> ${factor.name}</p>
            <p><strong>Questions:</strong> ${selectedQuestions.length}</p>
            <p><strong>Recipients:</strong></p>
            <ul class="mt-1 space-y-1">
                ${emails.map(email => `<li class="text-blue-600">• ${email}</li>`).join('')}
            </ul>
        </div>
    `;
    
    document.getElementById('sendModal').classList.remove('hidden');
}

function hideModal() {
    document.getElementById('sendModal').classList.add('hidden');
}

function showSuccessModal(message) {
    document.getElementById('successMessage').textContent = message;
    document.getElementById('successModal').classList.remove('hidden');
}

function hideSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
    resetForm();
}

async function sendQuestions() {
    const factorId = document.getElementById('factor_id').value;
    const includeInstructions = document.getElementById('includeInstructions').checked;
    const sendReminder = document.getElementById('sendReminder').checked;
    
    const sendBtn = document.getElementById('confirmSendBtn');
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
    
    try {
        const response = await fetch('{{ route("questions.send.emails") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                factor_id: factorId,
                emails: emails,
                questions: selectedQuestions.map(q => q.id),
                include_instructions: includeInstructions,
                send_reminder: sendReminder
            })
        });
        
        const data = await response.json();
        
        hideModal();
        
        if (data.success) {
            showSuccessModal(data.message);
            updateStatistics();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while sending questions');
        hideModal();
    } finally {
        sendBtn.disabled = false;
        sendBtn.innerHTML = 'Send';
    }
}

function updateStatistics() {
    // Update the statistics counters
    const totalSent = document.getElementById('totalSent');
    const currentCount = parseInt(totalSent.textContent);
    totalSent.textContent = currentCount + emails.length;
}

function resetForm() {
    emails = [];
    selectedQuestions = [];
    document.getElementById('sendQuestionsForm').reset();
    document.getElementById('emailTags').innerHTML = '';
    hideQuestionsPreview();
}
</script>
@endpush
@endsection