<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Fetch\{NewsApiService, GuardianService, BbcNewsService, NewYorkTimesService};
use App\Repositories\{ArticleRepository, SourceRepository, CategoryRepository};

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NewsApiService::class, function($app) {
            return new NewsApiService(
                env('NEWSAPI_KEY'),
                $app->make(ArticleRepository::class),
                $app->make(SourceRepository::class),
                $app->make(CategoryRepository::class)
            );
        });

        $this->app->singleton(GuardianService::class, function($app) {
            return new GuardianService(
                env('GUARDIAN_KEY'),
                $app->make(ArticleRepository::class),
                $app->make(SourceRepository::class),
                $app->make(CategoryRepository::class)
            );
        });

        $this->app->singleton(BbcNewsService::class, function($app) {
            return new BbcNewsService(
                $app->make(ArticleRepository::class),
                $app->make(SourceRepository::class),
                $app->make(CategoryRepository::class)
            );
        });

        $this->app->singleton(NewYorkTimesService::class, function($app) {
            return new NewYorkTimesService(
                env('NYTIMES_KEY'),
                $app->make(ArticleRepository::class),
                $app->make(SourceRepository::class),
                $app->make(CategoryRepository::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
