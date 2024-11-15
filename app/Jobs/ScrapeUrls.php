<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeUrls implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Task $task,
        private readonly array $urls,
        private readonly array $selectors
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $results = [];

        $this->task->status  = 'running';
        $this->task->save();

        foreach($this->urls as $url)
        {
            $response = Http::get($url);

            if($response->failed())
            {
                $results[] = [
                    'url'           => $url,
                    'http_status'   => $response->status(),
                    'data'          => [],
                ];
                continue;
            }

            $crawler = new Crawler($response->body());
            $scrapedData = [];
            foreach($this->selectors as $selector)
                $crawler->filter($selector)->each(function (Crawler $node) use (&$scrapedData, $selector){
                    $scrapedData[] = [
                        'selector'  => $selector,
                        'text'      => $node->text(),
                    ];
                });

            $results[] = [
                'url'           => $url,
                'http_status'   => $response->status(),
                'data'          => $scrapedData,
            ];

        }

        $this->task->results = $results;
        $this->task->status = 'complete';
        $this->task->save();
    }
}
