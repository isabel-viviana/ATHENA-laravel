<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\AiTutor\AiChatController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\MockExam\MockExamController;
use App\Http\Controllers\Practice\PracticeController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Ranking\RankingController;
use App\Http\Controllers\Statistic\StatisticController;
use App\Http\Controllers\Subscription\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Módulo Administrador
Route::get('/admin/dashboard-admin', [AdminDashboardController::class, 'dashboardAdmin']);
Route::get('/admin/dashboard-super', [AdminDashboardController::class, 'dashboardSuper']);
Route::get('/admin/dashboard-support', [AdminDashboardController::class, 'dashboardSupport']);
Route::get('/admin/exams', [ExamController::class, 'exams']);

// Módulo Tutor IA
Route::get('/ai-tutor/chat', [AiChatController::class, 'chat']);

// Módulo Autenticación
Route::get('/auth/me', [AuthController::class, 'me']);

// Módulo Dashboard
Route::get('/dashboard', [DashboardController::class, 'dashboard']);

// Módulo Simulacros (Mock Exams)
Route::get('/mock/config', [MockExamController::class, 'mockConfig']);
Route::get('/mock/quick', [MockExamController::class, 'mockQuick']);
Route::get('/mock/history', [MockExamController::class, 'mockHistory']);
Route::get('/mock/results/{id}', [MockExamController::class, 'mockResults']);
Route::get('/mock/review/{id}', [MockExamController::class, 'mockReview']);
Route::get('/mock/{id}', [MockExamController::class, 'mockExam']);

// Módulo Prácticas
Route::get('/practice/config', [PracticeController::class, 'practiceConfig']);
Route::get('/practice/results/{id}', [PracticeController::class, 'practiceResults']);
Route::get('/practice/review/{id}', [PracticeController::class, 'practiceReview']);

// Módulo Perfil
Route::get('/profile', [ProfileController::class, 'show']);

// Módulo Escalafón (Ranking)
Route::get('/ranking', [RankingController::class, 'ranking']);

// Módulo Estadísticas
Route::get('/statistics/analysis', [StatisticController::class, 'analysis']);
Route::get('/statistics', [StatisticController::class, 'statistics']);

// Módulo Suscripciones y Pagos
Route::get('/subscription', [SubscriptionController::class, 'subscription']);
Route::get('/payments', [SubscriptionController::class, 'payments']);
