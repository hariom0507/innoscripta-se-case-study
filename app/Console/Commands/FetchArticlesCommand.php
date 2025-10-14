<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Fetch\{NewsApiService, GuardianService, BbcNewsService, NewYorkTimesService};

class FetchArticlesCommand extends Command
{
    protected $signature = 'articles:fetch';
    protected $description = 'Fetch articles from NewsAPI, Guardian, BBC';

    public function handle(): void
    {
        $services = [
            app(NewsApiService::class),
            app(GuardianService::class),
            // app(BbcNewsService::class),
            app(NewYorkTimesService::class)
        ];

        foreach ($services as $service) {
            $this->info('Fetching from ' . get_class($service));
            $service->fetchArticles();
        }

        $this->info('Fetching completed.');
    }
}
