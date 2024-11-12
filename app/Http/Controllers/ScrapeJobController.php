<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScrapeRequest;
use Illuminate\Http\Request;
use Symfony\Component\CssSelector\CssSelectorConverter;

class ScrapeJobController extends Controller
{
    public function show(Request $request)
    {
        //TODO: Get job or status
        abort(404);
    }

    public function store(StoreScrapeRequest $request)
    {
        //TODO: Dispatch job
        dd('yeah');
        return response($request->all());
    }

    public function destroy(Request $request)
    {
        //TODO: Delete job
    }
}
