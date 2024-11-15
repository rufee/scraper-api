<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScrapeRequest;
use App\Http\Resources\ScrapeTaskResource;
use App\Jobs\ScrapeUrls;
use App\Models\Task;
use Illuminate\Http\Response;

class ScrapeJobController extends Controller
{
    /**
     * Gets job details from database
     *
     * @param string $id
     * @return ScrapeTaskResource
     */
    public function show(string $id) : ScrapeTaskResource
    {
        $task = Task::find($id);

        if(!$task)
            abort(response([
                'status' => 404, 'message' => 'Job not found.'
            ], 404));

        return ScrapeTaskResource::make($task);
    }

    /**
     * Creates and dispatches a new web scrape job
     *
     * @param StoreScrapeRequest $request
     * @return ScrapeTaskResource
     */
    public function store(StoreScrapeRequest $request) : ScrapeTaskResource
    {
        $task = new Task([
            'status'    => 'queued',
            'urls'      => $request->input('urls'),
            'selectors' => $request->input('selectors')
        ]);

        $task->save();

        dispatch(new ScrapeUrls(
            task: $task,
            urls: $request->input('urls'),
            selectors: $request->input('selectors')
        ));

        return ScrapeTaskResource::make($task);
    }

    /**
     * Deletes job from database
     *
     * @param string $id
     * @return Response
     */
    public function destroy(string $id) : Response
    {
        $task = Task::find($id);

        if(!$task)
            abort(response([
                'status' => 404, 'message' => 'Job not found.'
            ], 404));


        $task->delete();

        return response([
            'status'    => 200,
            'message'   => 'Job has been deleted.'
        ]);
    }
}
