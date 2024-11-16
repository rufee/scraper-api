<?php

namespace Tests\Feature;

use App\Jobs\ScrapeUrls;
use App\Models\Task;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class ScrapeUrlsJobTest extends TestCase
{
    public string $queuedJobId = '01KCRSTM12460PC3BZQUEUED';
    public Task $queuedTask;
    protected function setUp(): void
    {
        parent::setUp();

        Redis::command('FLUSHALL');

        $this->queuedTask = new Task([
            'id' => $this->queuedJobId,
            'status' => 'queued',
            'urls' => [
                'https://example.com'
            ],
            'selectors' => [
                'title'
            ],
        ]);

        $this->queuedTask->save();
    }

    public function test_scrape_makes_http_request(): void
    {
        Http::fake([
            'https://example.com' => Http::response('<title>Example website</title>')
        ]);

        $job = new ScrapeUrls($this->queuedTask);
        $job->handle();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://example.com' && $request->method() === 'GET';
        });
    }

    public function test_scrape_returns_correct_data(): void
    {
        Http::fake([
            'https://example.com' => Http::response('<title>Example website</title>')
        ]);

        $job = new ScrapeUrls($this->queuedTask);
        $job->handle();

        $expectedResult = [
            [
                'url'           => 'https://example.com',
                'http_status'   => 200,
                'data' => [
                    [
                        'selector' => 'title',
                        'text'  => 'Example website'
                    ]
                ]
            ]
        ];

        $this->assertTrue($this->queuedTask->status === 'complete');
        $this->assertEquals($expectedResult, $this->queuedTask->results);
    }
}
