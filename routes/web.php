<?php
// routes/web.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\CorrectiveActionController;

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Root redirect
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect('/login');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    
    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/factor', [DashboardController::class, 'factorDashboard'])->name('dashboard.factor');
    Route::get('/dashboard/historical', [AssessmentController::class, 'assessmentHistory'])->name('dashboard.historical');

    // Dashboard AJAX Routes for Factor Dashboard
    Route::get('/dashboard/factor/questions', [DashboardController::class, 'getFactorQuestionsAjax'])->name('dashboard.factor.questions.ajax');
    Route::get('/dashboard/factor/actions', [DashboardController::class, 'getCorrectiveActionsAjax'])->name('dashboard.factor.actions.ajax');
    Route::get('/dashboard/factor/historical', [DashboardController::class, 'getFactorHistoricalDataAjax'])->name('dashboard.factor.historical.ajax');
    Route::get('/dashboard/historical/ajax', [DashboardController::class, 'getHistoricalDataAjax'])->name('dashboard.historical.ajax');
    
    // Debug routes (remove these in production)
    Route::get('/debug/questions', [DashboardController::class, 'debugQuestions']);
    Route::get('/debug/actions', [DashboardController::class, 'debugCorrectiveActions']);
    
    // Legacy route redirects (for backward compatibility)
    Route::get('/factor-dashboard', function() {
        return redirect()->route('dashboard.factor');
    });
    Route::get('/historical-assessment', function() {
        return redirect()->route('dashboard.historical');
    });
    
    // Question management routes
    Route::get('/track-questions', [QuestionController::class, 'trackQuestions'])->name('questions.track');
    Route::get('/edit-questions', [QuestionController::class, 'editQuestions'])->name('questions.edit');
    Route::get('/send-questions', [QuestionController::class, 'sendQuestions'])->name('questions.send');
    
    // CRUD routes for questions
    Route::post('/questions', [QuestionController::class, 'storeQuestion'])->name('questions.store');
    Route::put('/questions/{question}', [QuestionController::class, 'updateQuestion'])->name('questions.update');
    Route::delete('/questions/{question}', [QuestionController::class, 'destroyQuestion'])->name('questions.destroy');
    
    // Additional question management routes
    Route::post('/questions/bulk-update', [QuestionController::class, 'bulkUpdateQuestions'])->name('questions.bulk-update');
    Route::post('/questions/{question}/duplicate', [QuestionController::class, 'duplicateQuestion'])->name('questions.duplicate');
    Route::post('/questions/reorder', [QuestionController::class, 'reorderQuestions'])->name('questions.reorder');
    Route::get('/questions/factor/{factor}', [QuestionController::class, 'getQuestionsByFactor'])->name('questions.by-factor');
    
    // Send questions routes
    Route::post('/send-questions', [QuestionController::class, 'sendQuestionsToEmails'])->name('questions.send.emails');
    
    // Assessment routes
    Route::get('/assessment-form/{flow?}', [AssessmentController::class, 'showForm'])->name('assessment.form');
    Route::post('/assessment-form', [AssessmentController::class, 'submitForm'])->name('assessment.form.submit');
    
    // Enhanced assessment routes
    Route::post('/assessment/save-response', [AssessmentController::class, 'saveResponse'])->name('assessment.save-response');
    Route::post('/assessment/submit', [AssessmentController::class, 'submitAssessment'])->name('assessment.submit');
    Route::get('/assessment/progress', [AssessmentController::class, 'getProgress'])->name('assessment.progress');
    Route::get('/assessment/{assessment}/results', [AssessmentController::class, 'showResults'])->name('assessment.results');
    Route::get('/assessment/history', [AssessmentController::class, 'assessmentHistory'])->name('assessment.history');
    Route::get('/assessment/{assessment}/details', [AssessmentController::class, 'getAssessmentDetails'])->name('assessment.details');
    Route::get('/assessment/{assessment}/download', [AssessmentController::class, 'downloadAssessment'])->name('assessment.download');
    Route::get('/assessment/{assessment}/edit', [AssessmentController::class, 'editAssessment'])->name('assessment.edit');
    Route::delete('/assessment/{assessment}', [AssessmentController::class, 'deleteAssessment'])->name('assessment.delete');
    Route::get('/factor/{factor}/questions', [AssessmentController::class, 'getFactorQuestions'])->name('assessment.factor-questions');

    // CORRECTIVE ACTIONS MANAGEMENT ROUTES - FIXED
    Route::prefix('admin')->name('admin.')->group(function () {
        // Corrective Actions CRUD
        Route::get('/corrective-actions', [CorrectiveActionController::class, 'index'])->name('corrective-actions.index');
        Route::post('/corrective-actions', [CorrectiveActionController::class, 'store'])->name('corrective-actions.store');
        Route::get('/corrective-actions/{id}', [CorrectiveActionController::class, 'show'])->name('corrective-actions.show');
        Route::put('/corrective-actions/{id}', [CorrectiveActionController::class, 'update'])->name('corrective-actions.update');
        Route::delete('/corrective-actions/{id}', [CorrectiveActionController::class, 'destroy'])->name('corrective-actions.destroy');
        
        // Helper route for getting questions by factor (using ID instead of model binding)
        Route::get('/questions/by-factor/{id}', [QuestionController::class, 'getQuestionsByFactor'])->name('questions.by-factor');
    });
    
    // TEST ROUTE - Remove after testing
    Route::get('/test-corrective-actions', function() {
        try {
            $factors = App\Models\Factor::where('is_active', true)->get();
            return response()->json([
                'success' => true,
                'factors_count' => $factors->count(),
                'factors' => $factors->pluck('name', 'id')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });
    
});

// Public assessment routes (outside auth middleware)
Route::get('/assessment/public/{token}', [AssessmentController::class, 'showPublicAssessment'])->name('assessment.public');
Route::post('/assessment/public/{token}/submit', [AssessmentController::class, 'submitPublicAssessment'])->name('assessment.public.submit');