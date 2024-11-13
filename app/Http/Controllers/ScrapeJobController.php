<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScrapeRequest;
use App\Http\Resources\ScrapeJobResource;
use App\Jobs\ScrapeUrls;
use App\Models\Job;
use Illuminate\Bus\Dispatcher;
use Illuminate\Http\Response;

class ScrapeJobController extends Controller
{
    /**
     * Gets job details from database
     *
     * @param string $id
     * @return ScrapeJobResource
     */
    public function show(string $id) : ScrapeJobResource
    {
        $job = Job::find($id);

        if(!$job)
            abort(response([
                'status' => 404, 'message' => 'Job not found.'
            ], 404));

        return ScrapeJobResource::make($job);
    }

    /**
     * Creates and dispatches a new web scrape job
     *
     * @param StoreScrapeRequest $request
     * @return ScrapeJobResource
     */
    public function store(StoreScrapeRequest $request) : ScrapeJobResource
    {
        $jobId = app(Dispatcher::class)
            ->dispatch(new ScrapeUrls(
                urls: $request->input('urls'),
                selectors: $request->input('selectors')
            ));

        $job = new Job([
            'id' => $jobId,
            'status' => 'queued',
            'urls' => $request->input('urls'),
            'selectors' => $request->input('selectors')
        ]);

        $job->save();

        return ScrapeJobResource::make($job);
    }

    /**
     * Deletes job from database
     *
     * @param string $id
     * @return Response
     */
    public function destroy(string $id) : Response
    {
        $job = Job::find($id);

        if(!$job)
            abort(response([
                'status' => 404, 'message' => 'Job not found.'
            ], 404));

        $job->delete();

        return response([
            'status'    => 200,
            'message'   => 'Job has been deleted.'
        ]);
    }
}
