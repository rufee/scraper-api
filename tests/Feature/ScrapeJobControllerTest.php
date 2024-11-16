<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class ScrapeJobControllerTest extends TestCase
{
    public string $completedJobId = '01JCRSTM12460PC3BZCOMPLETE';
    public string $nonExistentJobId = '01JCRSTM12460PC404NOTFOUND';

    public Task $completedTask;

    protected function setUp(): void
    {
        parent::setUp();

        Redis::command('FLUSHALL');

        $this->completedTask = new Task([
            'id' => $this->completedJobId,
            'status' => 'complete',
            'urls' => [
                'https://example.com',
                'https://my-domain.tech'
            ],
            'selectors' => [
                'title',
                'h1'
            ],
            'results' => [
                [
                    'url' => 'https://example.com',
                    'http_status' => 200,
                    'data' => [
                        [
                            'selector' => 'title',
                            'text' => 'Example website',
                        ],
                        [
                            'selector' => 'h1',
                            'text' => 'Heading 1',
                        ],
                    ],
                ],
                [
                    'url' => 'https://my-domain.tech',
                    'http_status' => 404,
                    'data' => [],
                ],
            ]
        ]);

        $this->completedTask->save();
    }

    public function test_store_creates_scrape_job_successfully(): void
    {
        Http::fake([
            'https://example.com' => Http::response(),
            'https://my-domain.tech' => Http::response(status: 404)
        ]);

        $response = $this->postJson('/api/jobs', [
            'urls' => [
                'https://example.com',
                'https://my-domain.tech'
            ],
            'selectors' => [
                'title',
                'h1'
            ]
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'status',
                'urls',
                'selectors',
                'results'
            ])
            ->assertJson([
                'status' => 'queued',
                'urls' => [
                    'https://example.com',
                    'https://my-domain.tech'
                ],
                'selectors' => [
                    'title',
                    'h1'
                ],
                'results' => []
            ]);
    }

    public function test_store_returns_validation_error_for_invalid_selector(): void
    {
        $response = $this->postJson('/api/jobs', [
            'urls' => ['https://example.com'],
            'selectors' => ['!invalid-selector']
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['selectors.0']);
    }

    public function test_store_returns_validation_error_for_missing_fields(): void
    {
        $response = $this->postJson('/api/jobs', [
            'urls' => []
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['selectors']);
    }

    public function test_show_returns_job_details_successfully(): void
    {
        $response = $this->getJson('/api/jobs/' . $this->completedJobId);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'status',
                'urls',
                'selectors',
                'results'
            ]);
    }

    public function test_show_returns_not_found_for_invalid_id(): void
    {
        $response = $this->getJson('/api/jobs/' . $this->nonExistentJobId);

        $response->assertStatus(404)
            ->assertJsonFragment([
                'status' => 404
            ])
            ->assertJsonStructure([
                'status',
                'message'
            ]);
    }

    public function test_delete_removes_job_successfully(): void
    {
        $response = $this->deleteJson('/api/jobs/' . $this->completedJobId);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 200
            ])
            ->assertJsonStructure([
                'status',
                'message'
            ]);

        $this->assertNull(Redis::get((new Task())->getPrefix() . ':' . $this->completedJobId));
    }

    public function test_delete_returns_not_found_for_invalid_id(): void
    {
        $response = $this->deleteJson('/api/jobs/' . $this->nonExistentJobId);

        $response->assertStatus(404)
            ->assertJsonFragment([
                'status' => 404
            ])
            ->assertJsonStructure([
                'status',
                'message'
            ]);
    }
}
