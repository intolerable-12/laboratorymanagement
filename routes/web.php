<?php



use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Coordinator\Chemical\ChemicalBarcodePrintController;
use App\Http\Controllers\Coordinator\Chemical\ChemicalCategoryController;
use App\Http\Controllers\Coordinator\Chemical\ChemicalController;
use App\Http\Controllers\Coordinator\Borrow\CoordinatorBorrowController;
use App\Http\Controllers\Coordinator\Borrow\CoordinatorBorrowEmailController;
use App\Http\Controllers\Coordinator\EquipmentCategoryController;
use App\Http\Controllers\Coordinator\EquipmentController;
use App\Http\Controllers\Coordinator\EquipmentBarcodePrintController;
use App\Http\Controllers\Coordinator\LaboratoryController;
use App\Http\Controllers\Coordinator\Reservation\CoordinatorReservationController;
use App\Http\Controllers\Coordinator\UserManagementController;
use App\Http\Controllers\Facilitator\Account\Reservation\FacilitatorReservationController;
use App\Http\Controllers\Facilitator\Borrow\FacilitatorBorrowController;
use App\Http\Controllers\Facilitator\Borrow\FacilitatorBorrowEmailController;
use App\Http\Controllers\Instructor\Reservation\ReservationController as InstructorReservationController;
use App\Http\Controllers\Instructor\Borrow\InstructorBorrowController;
use App\Http\Controllers\Instructor\Borrow\InstructorBorrowEmailController;
use App\Http\Controllers\Instructor\Account\MyAccountController as InstructorMyAccountController;
use App\Http\Controllers\Student\Reservation\ReservationController as StudentReservationController;
use App\Http\Controllers\Student\Borrow\StudentBorrowController;
use App\Http\Controllers\Student\Borrow\StudentBorrowEmailController;
use App\Http\Controllers\Student\Account\MyAccountController as StudentMyAccountController;
use App\Http\Controllers\Student\Forum\ForumController as StudentForumController;
use App\Http\Controllers\Student\Forum\ForumCommentController as StudentForumCommentController;
use App\Http\Controllers\Student\Feedback\FeedbackController as StudentFeedbackController;
use App\Http\Controllers\Coordinator\Forum\ForumController as CoordinatorForumController;
use App\Http\Controllers\Coordinator\Forum\ForumCommentController as CoordinatorForumCommentController;
use App\Http\Controllers\Coordinator\Feedback\FeedbackController as CoordinatorFeedbackController;

Route::get('/', [LoginController::class, 'create'])->name('login');

Route::post('/login', [LoginController::class, 'store'])
    ->name('login.store');

Route::get('/register', [RegistrationController::class, 'create'])->name('register');

Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');

Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::middleware(['auth'])
    ->prefix('notifications')
    ->name('notifications.')
    ->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::get('/{notification}', [NotificationController::class, 'show'])->whereNumber('notification')->name('show');
    });


Route::middleware(['auth', 'role:Coordinator'])->prefix('coordinator')->name('coordinator.')->group(function () {
        Route::get('/dashboard', function () {
            return view('users.coordinator.dashboard');
        })->name('dashboard');

        Route::resource('users', UserManagementController::class);

        Route::prefix('equipment')
            ->name('equipment.')
            ->group(function () {
                Route::get('/', [EquipmentController::class, 'index'])->name('index');
                Route::get('/create', [EquipmentController::class, 'create'])->name('create');
                Route::post('/', [EquipmentController::class, 'store'])->name('store');
                Route::get('/{equipment}', [EquipmentController::class, 'show'])->name('show');
                Route::get('/{equipment}/barcode-print', EquipmentBarcodePrintController::class)->name('barcode-print');
                Route::get('/{equipment}/edit', [EquipmentController::class, 'edit'])->name('edit');
                Route::put('/{equipment}', [EquipmentController::class, 'update'])->name('update');
                Route::delete('/{equipment}', [EquipmentController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('equipment-categories')
            ->name('equipment.categories.')
            ->group(function () {
                Route::get('/', [EquipmentCategoryController::class, 'index'])->name('index');
                Route::get('/create', [EquipmentCategoryController::class, 'create'])->name('create');
                Route::post('/', [EquipmentCategoryController::class, 'store'])->name('store');
                Route::get('/{equipmentCategory}', [EquipmentCategoryController::class, 'show'])->name('show');
                Route::get('/{equipmentCategory}/edit', [EquipmentCategoryController::class, 'edit'])->name('edit');
                Route::put('/{equipmentCategory}', [EquipmentCategoryController::class, 'update'])->name('update');
                Route::delete('/{equipmentCategory}', [EquipmentCategoryController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('laboratories')
            ->name('laboratories.')
            ->group(function () {
                Route::get('/', [LaboratoryController::class, 'index'])->name('index');
                Route::get('/create', [LaboratoryController::class, 'create'])->name('create');
                Route::post('/', [LaboratoryController::class, 'store'])->name('store');
                Route::get('/{laboratory}', [LaboratoryController::class, 'show'])->name('show');
                Route::get('/{laboratory}/edit', [LaboratoryController::class, 'edit'])->name('edit');
                Route::put('/{laboratory}', [LaboratoryController::class, 'update'])->name('update');
                Route::delete('/{laboratory}', [LaboratoryController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('chemicals')
            ->name('chemicals.')
            ->group(function () {
                Route::get('/', [ChemicalController::class, 'index'])->name('index');
                Route::get('/create', [ChemicalController::class, 'create'])->name('create');
                Route::post('/', [ChemicalController::class, 'store'])->name('store');
                Route::get('/{chemical}', [ChemicalController::class, 'show'])->name('show');
                Route::get('/{chemical}/barcode-print', ChemicalBarcodePrintController::class)->name('barcode-print');
                Route::get('/{chemical}/edit', [ChemicalController::class, 'edit'])->name('edit');
                Route::put('/{chemical}', [ChemicalController::class, 'update'])->name('update');
                Route::delete('/{chemical}', [ChemicalController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('chemical-categories')
            ->name('chemical.categories.')
            ->group(function () {
                Route::get('/', [ChemicalCategoryController::class, 'index'])->name('index');
                Route::get('/create', [ChemicalCategoryController::class, 'create'])->name('create');
                Route::post('/', [ChemicalCategoryController::class, 'store'])->name('store');
                Route::get('/{chemicalCategory}', [ChemicalCategoryController::class, 'show'])->name('show');
                Route::get('/{chemicalCategory}/edit', [ChemicalCategoryController::class, 'edit'])->name('edit');
                Route::put('/{chemicalCategory}', [ChemicalCategoryController::class, 'update'])->name('update');
                Route::delete('/{chemicalCategory}', [ChemicalCategoryController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('reservations')
            ->name('reservations.')
            ->group(function () {
                Route::get('/', [CoordinatorReservationController::class, 'index'])->name('index');
                Route::get('/{reservation}', [CoordinatorReservationController::class, 'show'])->name('show');
                Route::post('/{reservation}/approve', [CoordinatorReservationController::class, 'approve'])->name('approve');
                Route::post('/{reservation}/reject', [CoordinatorReservationController::class, 'reject'])->name('reject');
            });

        Route::prefix('borrow')
            ->name('borrow.')
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\Coordinator\Borrow\CoordinatorBorrowController::class, 'index'])->name('index');
                Route::get('/{borrowTransaction}', [\App\Http\Controllers\Coordinator\Borrow\CoordinatorBorrowController::class, 'show'])->name('show');
                Route::post('/{borrowTransaction}/approve', [\App\Http\Controllers\Coordinator\Borrow\CoordinatorBorrowController::class, 'approve'])->name('approve');
                Route::post('/{borrowTransaction}/reject', [\App\Http\Controllers\Coordinator\Borrow\CoordinatorBorrowController::class, 'reject'])->name('reject');
            });

        Route::prefix('forum')
            ->name('forum.')
            ->group(function () {
                Route::get('/', [CoordinatorForumController::class, 'index'])->name('index');
                Route::get('/{forumPost}', [CoordinatorForumController::class, 'show'])->name('show');
                Route::put('/{forumPost}', [CoordinatorForumController::class, 'update'])->name('update');
                Route::post('/comments/{forumComment}/toggle-visibility', [CoordinatorForumCommentController::class, 'toggleVisibility'])->name('comments.toggle-visibility');
            });

        Route::prefix('feedback')
            ->name('feedback.')
            ->group(function () {
                Route::get('/', [CoordinatorFeedbackController::class, 'index'])->name('index');
                Route::get('/{feedback}', [CoordinatorFeedbackController::class, 'show'])->name('show');
                Route::post('/{feedback}/toggle-visibility', [CoordinatorFeedbackController::class, 'toggleVisibility'])->name('toggle-visibility');
            });

    });


Route::middleware(['auth', 'role:Student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('users.student.dashboard');
        })->name('dashboard');

        Route::prefix('reservations')
            ->name('reservations.')
            ->group(function () {
                Route::get('/', [StudentReservationController::class, 'index'])->name('index');
                Route::get('/create', [StudentReservationController::class, 'create'])->name('create');
                Route::post('/', [StudentReservationController::class, 'store'])->name('store');
                Route::get('/{reservation}', [StudentReservationController::class, 'show'])->name('show');
            });

        Route::prefix('borrow')
            ->name('borrow.')
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\Student\Borrow\StudentBorrowController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Student\Borrow\StudentBorrowController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\Student\Borrow\StudentBorrowController::class, 'store'])->name('store');
                Route::get('/{borrowTransaction}', [\App\Http\Controllers\Student\Borrow\StudentBorrowController::class, 'show'])->name('show');
            });

        Route::prefix('forum')
            ->name('forum.')
            ->group(function () {
                Route::get('/', [StudentForumController::class, 'index'])->name('index');
                Route::get('/create', [StudentForumController::class, 'create'])->name('create');
                Route::post('/', [StudentForumController::class, 'store'])->name('store');
                Route::get('/{forumPost}', [StudentForumController::class, 'show'])->name('show');
                Route::post('/{forumPost}/comments', [StudentForumCommentController::class, 'store'])->name('comments.store');
            });

        Route::prefix('feedback')
            ->name('feedback.')
            ->group(function () {
                Route::get('/', [StudentFeedbackController::class, 'index'])->name('index');
                Route::get('/create', [StudentFeedbackController::class, 'create'])->name('create');
                Route::post('/', [StudentFeedbackController::class, 'store'])->name('store');
                Route::get('/{feedback}', [StudentFeedbackController::class, 'show'])->name('show');
            });

        Route::get('/my-account', [StudentMyAccountController::class, 'index'])->name('myaccount');
        Route::put('/my-account', [StudentMyAccountController::class, 'update'])->name('myaccount.update');
    });

Route::middleware(['auth', 'role:Facilitator'])
    ->prefix('facilitator')
    ->name('facilitator.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('users.facilitator.dashboard');
        })->name('dashboard');

        Route::prefix('reservations')
            ->name('reservations.')
            ->group(function () {
                Route::get('/', [FacilitatorReservationController::class, 'index'])->name('index');
                Route::get('/{reservation}', [FacilitatorReservationController::class, 'show'])->name('show');
                Route::post('/{reservation}/approve', [FacilitatorReservationController::class, 'approve'])->name('approve');
                Route::post('/{reservation}/reject', [FacilitatorReservationController::class, 'reject'])->name('reject');
            });

        Route::prefix('borrow')
            ->name('borrow.')
            ->group(function () {
                Route::get('/', [FacilitatorBorrowController::class, 'index'])->name('index');
                Route::get('/{borrowTransaction}', [FacilitatorBorrowController::class, 'show'])->name('show');
                Route::post('/{borrowTransaction}/approve', [FacilitatorBorrowController::class, 'approve'])->name('approve');
                Route::post('/{borrowTransaction}/reject', [FacilitatorBorrowController::class, 'reject'])->name('reject');
            });

        Route::get('/my-account', [\App\Http\Controllers\Facilitator\Account\MyAccountController::class, 'index'])->name('myaccount');
        Route::put('/my-account', [\App\Http\Controllers\Facilitator\Account\MyAccountController::class, 'update'])->name('myaccount.update');
    });

Route::middleware(['auth', 'role:Instructor'])
    ->prefix('instructor')
    ->name('instructor.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('users.instructor.dashboard');
        })->name('dashboard');

        Route::prefix('reservations')
            ->name('reservations.')
            ->group(function () {
                Route::get('/', [InstructorReservationController::class, 'index'])->name('index');
                Route::get('/{reservation}', [InstructorReservationController::class, 'show'])->name('show');
                Route::post('/{reservation}/approve', [InstructorReservationController::class, 'approve'])->name('approve');
                Route::post('/{reservation}/reject', [InstructorReservationController::class, 'reject'])->name('reject');
            });

        Route::prefix('borrow')
            ->name('borrow.')
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\Instructor\Borrow\InstructorBorrowController::class, 'index'])->name('index');
                Route::get('/{borrowTransaction}', [\App\Http\Controllers\Instructor\Borrow\InstructorBorrowController::class, 'show'])->name('show');
                Route::post('/{borrowTransaction}/approve', [\App\Http\Controllers\Instructor\Borrow\InstructorBorrowController::class, 'approve'])->name('approve');
                Route::post('/{borrowTransaction}/reject', [\App\Http\Controllers\Instructor\Borrow\InstructorBorrowController::class, 'reject'])->name('reject');
            });

        Route::get('/my-account', [\App\Http\Controllers\Instructor\Account\MyAccountController::class, 'index'])->name('myaccount');
        Route::put('/my-account', [\App\Http\Controllers\Instructor\Account\MyAccountController::class, 'update'])->name('myaccount.update');
    });