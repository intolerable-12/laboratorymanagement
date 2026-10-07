<?php

namespace App\Providers;

use App\Models\Chemical;
use App\Models\Equipment;
use App\Models\ForumPost;
use App\Models\FeedbackQuestionnaire;
use App\Models\FeedbackQuestionnaireResponse;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Observers\EquipmentObserver;
use App\Observers\ChemicalObserver;
use App\Observers\SchoolYearObserver;
use App\Observers\SemesterObserver;
use App\Services\RequestNotificationService;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Relation::morphMap([
            'Equipment' => Equipment::class,
            'Chemical' => Chemical::class,
            'ForumPost' => ForumPost::class,
            'FeedbackQuestionnaire' => FeedbackQuestionnaire::class,
            'FeedbackQuestionnaireResponse' => FeedbackQuestionnaireResponse::class,
        ]);

        Equipment::observe(EquipmentObserver::class);
        Chemical::observe(ChemicalObserver::class);
        SchoolYear::observe(SchoolYearObserver::class);
        Semester::observe(SemesterObserver::class);

        View::composer('partials.notification-bell', function ($view) {
            $user = auth()->user();

            $view->with('notificationSummary', $user
                ? app(RequestNotificationService::class)->summaryFor($user)
                : [
                    'unreadCount' => 0,
                    'items' => collect(),
                ]);
        });
    }
}
