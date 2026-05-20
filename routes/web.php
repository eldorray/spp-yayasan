<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\MonthlyBillController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Institutions
    Route::get('institutions', [InstitutionController::class, 'index'])->name('institutions.index');
    Route::post('institutions', [InstitutionController::class, 'store'])->name('institutions.store');
    Route::patch('institutions/{institution}', [InstitutionController::class, 'update'])->name('institutions.update');
    Route::delete('institutions/{institution}', [InstitutionController::class, 'destroy'])->name('institutions.destroy');

    // Academic Years
    Route::get('academic-years', [AcademicYearController::class, 'index'])->name('academic-years.index');
    Route::post('academic-years', [AcademicYearController::class, 'store'])->name('academic-years.store');
    Route::patch('academic-years/{academicYear}', [AcademicYearController::class, 'update'])->name('academic-years.update');
    Route::post('academic-years/{academicYear}/activate', [AcademicYearController::class, 'activate'])->name('academic-years.activate');
    Route::delete('academic-years/{academicYear}', [AcademicYearController::class, 'destroy'])->name('academic-years.destroy');

    // Classrooms
    Route::get('classrooms', [ClassroomController::class, 'index'])->name('classrooms.index');
    Route::post('classrooms', [ClassroomController::class, 'store'])->name('classrooms.store');
    Route::post('classrooms/bulk-delete', [ClassroomController::class, 'bulkDestroy'])->name('classrooms.bulk-destroy');
    Route::patch('classrooms/{classroom}', [ClassroomController::class, 'update'])->name('classrooms.update');
    Route::delete('classrooms/{classroom}', [ClassroomController::class, 'destroy'])->name('classrooms.destroy');

    // Students
    Route::get('students', [StudentController::class, 'index'])->name('students.index');
    Route::post('students', [StudentController::class, 'store'])->name('students.store');
    Route::patch('students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
    Route::post('students/sync', [StudentController::class, 'syncFromApi'])->name('students.sync');
    Route::post('students/bulk-delete', [StudentController::class, 'bulkDestroy'])->name('students.bulk-destroy');

    // Monthly Bills
    Route::get('monthly-bills', [MonthlyBillController::class, 'index'])->name('monthly-bills.index');
    Route::post('monthly-bills/generate', [MonthlyBillController::class, 'generate'])->name('monthly-bills.generate');
    Route::post('monthly-bills/bulk-delete', [MonthlyBillController::class, 'bulkDestroy'])->name('monthly-bills.bulk-destroy');

    // Fee Rates
    Route::post('fee-rates', [MonthlyBillController::class, 'storeFeeRate'])->name('fee-rates.store');
    Route::patch('fee-rates/{feeRate}', [MonthlyBillController::class, 'updateFeeRate'])->name('fee-rates.update');
    Route::delete('fee-rates/{feeRate}', [MonthlyBillController::class, 'destroyFeeRate'])->name('fee-rates.destroy');

    // Activities
    Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::post('activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::post('activities/bills/bulk-delete', [ActivityController::class, 'bulkDestroyBills'])->name('activities.bills.bulk-destroy');
    Route::get('activities/{activity}', [ActivityController::class, 'show'])->name('activities.show');
    Route::patch('activities/{activity}', [ActivityController::class, 'update'])->name('activities.update');
    Route::delete('activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');

    // Payments
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('api/students/search', [PaymentController::class, 'searchStudent'])->name('api.students.search');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('reports/ask', [ReportController::class, 'ask'])->name('reports.ask');
});

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
});


require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
