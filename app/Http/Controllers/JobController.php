<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Comment out Gate temporarily to test
            // Gate::authorize('viewAny', Job::class);
            
            $filters = request()->only(
                'search',
                'min_salary',
                'max_salary',
                'experience',
                'category'
            );

            Log::info('Loading jobs with filters', $filters);

            // ✅ USE PAGINATE INSTEAD OF GET
            $jobs = Job::with('employer')
                ->latest()
                ->filter($filters)
                ->paginate(15); // Load only 15 jobs per page

            Log::info('Jobs loaded successfully: ' . $jobs->count());

            return view('job.index', ['jobs' => $jobs]);
            
        } catch (\Exception $e) {
            Log::error('JobController index error: ' . $e->getMessage());
            // Return a simple error page instead of timing out
            return response()->view('errors.500', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Job $job)
    {
        try {
            // Comment out Gate temporarily to test
            // Gate::authorize('view', $job);
            
            return view('job.show', [
                'job' => $job->load('employer.jobs')
            ]);
            
        } catch (\Exception $e) {
            Log::error('JobController show error: ' . $e->getMessage());
            return response()->view('errors.500', ['error' => $e->getMessage()], 500);
        }
    }
}