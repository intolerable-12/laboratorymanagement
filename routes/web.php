<?php



use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Coordinator\Chemical\ChemicalBarcodePrintController;
use App\Http\Controllers\Coordinator\Chemical\ChemicalCategoryController;
use App\Http\Controllers\Coordinator\Chemical\ChemicalController;
use App\Http\Controllers\Coordinator\Announcement\AnnouncementController as CoordinatorAnnouncementController;
use App\Http\Controllers\Coordinator\DashboardController as CoordinatorDashboardController;
use App\Http\Controllers\Coordinator\DepartmentManagementController;
use App\Http\Controllers\Coordinator\Borrow\CoordinatorBorrowController;
use App\Http\Controllers\Coordinator\Borrow\CoordinatorBorrowEmailController;
use App\Http\Controllers\Coordinator\EquipmentCategoryController;
use App\Http\Controllers\Coordinator\EquipmentController;
use App\Http\Controllers\Coordinator\EquipmentBarcodePrintController;
use App\Http\Controllers\Coordinator\LaboratoryController;
use App\Http\Controllers\Coordinator\Reservation\CoordinatorReservationCalendarController;
use App\Http\Controllers\Coordinator\Reservation\CoordinatorBorrowCalendarController;
use App\Http\Controllers\Coordinator\Reservation\CoordinatorReservationController;
use App\Http\Controllers\Coordinator\UserManagementController;
use App\Http\Controllers\Coordinator\UserAccountRequestController;
use App\Http\Controllers\Facilitator\DashboardController as FacilitatorDashboardController;
use App\Http\Controllers\Facilitator\Account\Reservation\FacilitatorReservationController;
use App\Http\Controllers\Facilitator\Account\Reservation\FacilitatorReservationCalendarController;
use App\Http\Controllers\Facilitator\Account\Reservation\FacilitatorBorrowCalendarController;
use App\Http\Controllers\Facilitator\Borrow\FacilitatorBorrowController;
use App\Http\Controllers\Facilitator\Borrow\FacilitatorBorrowEmailController;
use App\Http\Controllers\Facilitator\Checkout\FacilitatorCheckoutController;
use App\Http\Controllers\Facilitator\Checkout\FacilitatorCheckinController;
use App\Http\Controllers\Facilitator\Forum\LaboratoryInchargeForumController;
use App\Http\Controllers\Instructor\DashboardController as InstructorDashboardController;
use App\Http\Controllers\Instructor\Reservation\ReservationController as InstructorReservationController;
use App\Http\Controllers\Instructor\Borrow\InstructorBorrowController;
use App\Http\Controllers\Instructor\Borrow\InstructorBorrowEmailController;
use App\Http\Controllers\Instructor\Inventory\ChemicalController as InstructorChemicalInventoryController;
use App\Http\Controllers\Instructor\Inventory\EquipmentController as InstructorEquipmentInventoryController;
use App\Http\Controllers\Instructor\Forum\InstructorForumController;
use App\Http\Controllers\Instructor\Account\MyAccountController as InstructorMyAccountController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\Inventory\ChemicalController as StudentChemicalInventoryController;
use App\Http\Controllers\Student\Inventory\LabEquipmentController as StudentLabEquipmentController;
use App\Http\Controllers\Student\Reservation\ReservationController as StudentReservationController;
use App\Http\Controllers\Student\Borrow\StudentBorrowController;
use App\Http\Controllers\Student\Borrow\StudentBorrowEmailController;
use App\Http\Controllers\Student\Account\MyAccountController as StudentMyAccountController;
use App\Http\Controllers\Student\Forum\ForumController as StudentForumController;
use App\Http\Controllers\Student\Forum\ForumCommentController as StudentForumCommentController;
use App\Http\Controllers\Student\Feedback\FeedbackController as StudentFeedbackController;
use App\Http\Controllers\Student\Feedback\FeedbackQuestionnaireController as StudentFeedbackQuestionnaireController;
use App\Http\Controllers\Coordinator\Forum\ForumController as CoordinatorForumController;
use App\Http\Controllers\Coordinator\Forum\ForumCommentController as CoordinatorForumCommentController;
use App\Http\Controllers\Coordinator\Feedback\FeedbackController as CoordinatorFeedbackController;
use App\Http\Controllers\Coordinator\Feedback\FeedbackQuestionnaireController as CoordinatorFeedbackQuestionnaireController;
use App\Models\Chemical;
use App\Models\ChemicalCategory;
use App\Models\Equipment;
use App\Models\EquipmentCategory;

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
        Route::get('/dashboard', [CoordinatorDashboardController::class, 'index'])->name('dashboard');

        Route::get('/users/archived', [UserManagementController::class, 'archived'])->name('users.archived');
        Route::get('/users/requests', [UserAccountRequestController::class, 'index'])->name('users.requests.index');
        Route::get('/users/requests/{accountRequest}', [UserAccountRequestController::class, 'show'])->name('users.requests.show');
        Route::post('/users/requests/{accountRequest}/approve', [UserAccountRequestController::class, 'approve'])->name('users.requests.approve');
        Route::post('/users/requests/{accountRequest}/reject', [UserAccountRequestController::class, 'reject'])->name('users.requests.reject');
        Route::post('/users/{user}/restore', [UserManagementController::class, 'restore'])->withTrashed()->name('users.restore');
        Route::resource('users', UserManagementController::class)->withTrashed(['show']);

        Route::resource('departments', DepartmentManagementController::class);

        Route::prefix('equipment')
            ->name('equipment.')
            ->group(function () {
                Route::get('/', [EquipmentController::class, 'index'])->name('index');
                Route::get('/archived', [EquipmentController::class, 'archived'])->name('archived');
                Route::get('/create', [EquipmentController::class, 'create'])->name('create');
                Route::post('/', [EquipmentController::class, 'store'])->name('store');
                Route::get('/{equipment}', [EquipmentController::class, 'show'])->withTrashed()->name('show');
                Route::get('/{equipment}/barcode-print', EquipmentBarcodePrintController::class)->withTrashed()->name('barcode-print');
                Route::post('/{equipment}/restore', [EquipmentController::class, 'restore'])->withTrashed()->name('restore');
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
                Route::get('/archived', [ChemicalController::class, 'archived'])->name('archived');
                Route::get('/create', [ChemicalController::class, 'create'])->name('create');
                Route::post('/', [ChemicalController::class, 'store'])->name('store');
                Route::get('/{chemical}', [ChemicalController::class, 'show'])->withTrashed()->name('show');
                Route::get('/{chemical}/barcode-print', ChemicalBarcodePrintController::class)->withTrashed()->name('barcode-print');
                Route::post('/{chemical}/restore', [ChemicalController::class, 'restore'])->withTrashed()->name('restore');
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
                Route::get('/calendar', [CoordinatorReservationCalendarController::class, 'index'])->name('calendar');
                Route::get('/', [CoordinatorReservationController::class, 'index'])->name('index');
                Route::get('/{reservation}', [CoordinatorReservationController::class, 'show'])->name('show');
                Route::post('/{reservation}/approve', [CoordinatorReservationController::class, 'approve'])->name('approve');
                Route::post('/{reservation}/reject', [CoordinatorReservationController::class, 'reject'])->name('reject');
            });

        Route::resource('announcements', CoordinatorAnnouncementController::class);

        Route::prefix('borrow')
            ->name('borrow.')
            ->group(function () {
                Route::get('/calendar', [CoordinatorBorrowCalendarController::class, 'index'])->name('calendar');
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
                Route::prefix('questionnaires')
                    ->name('questionnaires.')
                    ->group(function () {
                        Route::get('/', [CoordinatorFeedbackQuestionnaireController::class, 'index'])->name('index');
                        Route::get('/create', [CoordinatorFeedbackQuestionnaireController::class, 'create'])->name('create');
                        Route::post('/', [CoordinatorFeedbackQuestionnaireController::class, 'store'])->name('store');
                        Route::get('/{feedbackQuestionnaire}', [CoordinatorFeedbackQuestionnaireController::class, 'show'])->whereNumber('feedbackQuestionnaire')->name('show');
                        Route::get('/{feedbackQuestionnaire}/responses/{feedbackQuestionnaireResponse}', [CoordinatorFeedbackQuestionnaireController::class, 'showResponse'])->whereNumber(['feedbackQuestionnaire', 'feedbackQuestionnaireResponse'])->name('responses.show');
                        Route::get('/{feedbackQuestionnaire}/edit', [CoordinatorFeedbackQuestionnaireController::class, 'edit'])->whereNumber('feedbackQuestionnaire')->name('edit');
                        Route::put('/{feedbackQuestionnaire}', [CoordinatorFeedbackQuestionnaireController::class, 'update'])->whereNumber('feedbackQuestionnaire')->name('update');
                        Route::delete('/{feedbackQuestionnaire}', [CoordinatorFeedbackQuestionnaireController::class, 'destroy'])->whereNumber('feedbackQuestionnaire')->name('destroy');
                    });

                Route::get('/{feedback}', [CoordinatorFeedbackController::class, 'show'])->whereNumber('feedback')->name('show');
                Route::post('/{feedback}/toggle-visibility', [CoordinatorFeedbackController::class, 'toggleVisibility'])->whereNumber('feedback')->name('toggle-visibility');
            });

    });


Route::middleware(['auth', 'role:Student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

        Route::get('/inventory', function () {
            return view('users.student.inventory.index', [
                'stats' => [
                    'equipment_available' => Equipment::withoutTrashed()->where('status', 'Available')->count(),
                    'chemicals_available' => Chemical::withoutTrashed()->where('status', 'Available')->count(),
                    'equipment_categories' => EquipmentCategory::count(),
                    'chemical_categories' => ChemicalCategory::count(),
                ],
            ]);
        })->name('inventory.index');

        Route::prefix('inventory')
            ->name('inventory.')
            ->group(function () {
                Route::prefix('equipment')
                    ->name('equipment.')
                    ->group(function () {
                        Route::get('/', [StudentLabEquipmentController::class, 'index'])->name('index');
                        Route::get('/categories/{equipmentCategory}', [StudentLabEquipmentController::class, 'category'])->name('categories.show');
                        Route::get('/items/{equipment}', [StudentLabEquipmentController::class, 'show'])->name('show');
                    });

                Route::prefix('chemicals')
                    ->name('chemicals.')
                    ->group(function () {
                        Route::get('/', [StudentChemicalInventoryController::class, 'index'])->name('index');
                        Route::get('/categories/{chemicalCategory}', [StudentChemicalInventoryController::class, 'category'])->name('categories.show');
                        Route::get('/items/{chemical}', [StudentChemicalInventoryController::class, 'show'])->name('show');
                    });
            });

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
                Route::prefix('questionnaires')
                    ->name('questionnaires.')
                    ->group(function () {
                        Route::get('/', [StudentFeedbackQuestionnaireController::class, 'index'])->name('index');
                        Route::get('/{feedbackQuestionnaire}', [StudentFeedbackQuestionnaireController::class, 'show'])->whereNumber('feedbackQuestionnaire')->name('show');
                        Route::post('/{feedbackQuestionnaire}', [StudentFeedbackQuestionnaireController::class, 'store'])->whereNumber('feedbackQuestionnaire')->name('store');
                    });

                Route::get('/{feedback}', [StudentFeedbackController::class, 'show'])->whereNumber('feedback')->name('show');
            });

        Route::get('/my-account', [StudentMyAccountController::class, 'index'])->name('myaccount');
        Route::put('/my-account', [StudentMyAccountController::class, 'update'])->name('myaccount.update');
    });

Route::middleware(['auth', 'role:Laboratory In-charge'])
    ->prefix('facilitator')
    ->name('facilitator.')
    ->group(function () {
        Route::get('/dashboard', [FacilitatorDashboardController::class, 'index'])->name('dashboard');

        Route::prefix('reservations')
            ->name('reservations.')
            ->group(function () {
                Route::get('/calendar', [FacilitatorReservationCalendarController::class, 'index'])->name('calendar');
                Route::get('/', [FacilitatorReservationController::class, 'index'])->name('index');
                Route::get('/{reservation}', [FacilitatorReservationController::class, 'show'])->name('show');
                Route::post('/{reservation}/approve', [FacilitatorReservationController::class, 'approve'])->name('approve');
                Route::post('/{reservation}/reject', [FacilitatorReservationController::class, 'reject'])->name('reject');
            });

        Route::prefix('borrow')
            ->name('borrow.')
            ->group(function () {
                Route::get('/calendar', [FacilitatorBorrowCalendarController::class, 'index'])->name('calendar');
                Route::get('/', [FacilitatorBorrowController::class, 'index'])->name('index');
                Route::get('/{borrowTransaction}', [FacilitatorBorrowController::class, 'show'])->name('show');
                Route::post('/{borrowTransaction}/approve', [FacilitatorBorrowController::class, 'approve'])->name('approve');
                Route::post('/{borrowTransaction}/reject', [FacilitatorBorrowController::class, 'reject'])->name('reject');
            });

        Route::prefix('checkout')
            ->name('checkout.')
            ->group(function () {
                Route::get('/', [FacilitatorCheckoutController::class, 'index'])->name('index');
                Route::get('/{borrowTransaction}', [FacilitatorCheckoutController::class, 'show'])->name('show');
                Route::post('/{borrowTransaction}/scan', [FacilitatorCheckoutController::class, 'scan'])->name('scan');
                Route::post('/{borrowTransaction}/scan/{barcodeLog}/remove', [FacilitatorCheckoutController::class, 'remove'])->name('remove');
            });

        Route::prefix('checkin')
            ->name('checkin.')
            ->group(function () {
                Route::get('/', [FacilitatorCheckinController::class, 'index'])->name('index');
                Route::get('/{borrowTransaction}', [FacilitatorCheckinController::class, 'show'])->name('show');
                Route::post('/{borrowTransaction}/scan', [FacilitatorCheckinController::class, 'scan'])->name('scan');
                Route::post('/{borrowTransaction}/scan/{barcodeLog}/remove', [FacilitatorCheckinController::class, 'remove'])->name('remove');
            });

    Route::prefix('forum')
            ->name('forum.')
            ->group(function () {
                Route::get('/', [LaboratoryInchargeForumController::class, 'index'])->name('index');
                Route::get('/create', [LaboratoryInchargeForumController::class, 'create'])->name('create');
                Route::post('/', [LaboratoryInchargeForumController::class, 'store'])->name('store');
                Route::get('/{forumPost}', [LaboratoryInchargeForumController::class, 'show'])->whereNumber('forumPost')->name('show');
                Route::post('/{forumPost}/comments', [LaboratoryInchargeForumController::class, 'storeComment'])->whereNumber('forumPost')->name('comments.store');
            });

        Route::get('/my-account', [\App\Http\Controllers\Facilitator\Account\MyAccountController::class, 'index'])->name('myaccount');
        Route::put('/my-account', [\App\Http\Controllers\Facilitator\Account\MyAccountController::class, 'update'])->name('myaccount.update');
    });

Route::middleware(['auth', 'role:Instructor'])
    ->prefix('instructor')
    ->name('instructor.')
    ->group(function () {
        Route::get('/dashboard', [InstructorDashboardController::class, 'index'])->name('dashboard');

        Route::get('/inventory', function () {
            return view('users.instructor.inventory.index');
        })->name('inventory.index');

        Route::prefix('inventory')
            ->name('inventory.')
            ->group(function () {
                Route::prefix('equipment')
                    ->name('equipment.')
                    ->group(function () {
                        Route::get('/', [InstructorEquipmentInventoryController::class, 'index'])->name('index');
                        Route::get('/categories/{equipmentCategory}', [InstructorEquipmentInventoryController::class, 'category'])->name('categories.show');
                        Route::get('/items/{equipment}', [InstructorEquipmentInventoryController::class, 'show'])->name('show');
                    });

                Route::prefix('chemicals')
                    ->name('chemicals.')
                    ->group(function () {
                        Route::get('/', [InstructorChemicalInventoryController::class, 'index'])->name('index');
                        Route::get('/categories/{chemicalCategory}', [InstructorChemicalInventoryController::class, 'category'])->name('categories.show');
                        Route::get('/items/{chemical}', [InstructorChemicalInventoryController::class, 'show'])->name('show');
                    });
            });

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

        Route::prefix('forum')
            ->name('forum.')
            ->group(function () {
                Route::get('/', [InstructorForumController::class, 'index'])->name('index');
                Route::get('/create', [InstructorForumController::class, 'create'])->name('create');
                Route::post('/', [InstructorForumController::class, 'store'])->name('store');
                Route::get('/{forumPost}', [InstructorForumController::class, 'show'])->whereNumber('forumPost')->name('show');
                Route::post('/{forumPost}/comments', [InstructorForumController::class, 'storeComment'])->whereNumber('forumPost')->name('comments.store');
            });

        Route::get('/my-account', [\App\Http\Controllers\Instructor\Account\MyAccountController::class, 'index'])->name('myaccount');
        Route::put('/my-account', [\App\Http\Controllers\Instructor\Account\MyAccountController::class, 'update'])->name('myaccount.update');
    });
