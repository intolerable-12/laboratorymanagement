<?php

namespace App\Providers;

use App\Models\Chemical;
use App\Models\Equipment;
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
        ]);

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
