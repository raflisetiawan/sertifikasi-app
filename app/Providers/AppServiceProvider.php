<?php

namespace App\Providers;

use App\Models\Assignment;
use App\Models\File;
use App\Models\Quiz;
use App\Models\Text;
use Illuminate\Database\Eloquent\Relations\Relation;
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
    public function boot(): void {
        Relation::morphMap([
            'text' => Text::class,
            // 'video' => Video::class,
            'quiz' => Quiz::class,
            'assignment' => Assignment::class,
            'file' => File::class,
        ]);
    }
}
