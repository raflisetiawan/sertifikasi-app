<?php

namespace App\Providers;

use App\Models\Assignment;
use App\Models\File;
use App\Models\Practice;
use App\Models\Quiz;
use App\Models\Text;
use App\Models\Video;
use App\Services\EnrollmentService;
use App\Services\PaymentService;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Services\CourseService;
use App\Services\LiveSessionService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EnrollmentService::class, function ($app) {
            return new EnrollmentService();
        });

        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService(
                $app->make(EnrollmentService::class)
            );
        });

        $this->app->singleton(CourseService::class, function ($app) {
            return new CourseService(
                $app->make(LiveSessionService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'text' => Text::class,
            'video' => Video::class,
            'quiz' => Quiz::class,
            'assignment' => Assignment::class,
            'file' => File::class,
            'practice' => Practice::class,
        ]);
    }
}
