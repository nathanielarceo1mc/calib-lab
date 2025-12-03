<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class JobOrderController extends Controller
{
    /**
     * Display a listing of the jobs.
     */
    public function index()
    {
        // You can tune this later – for now we just show all jobs
        $jobs = Job::orderBy('order_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('jobs.index', compact('jobs'));
    }

    /**
     * Show the form for creating a new job.
     */
    public function create()
    {
        // Later we can add customer selection, UUTs, etc.
        return view('jobs.create');
    }

    /**
     * Store a newly created job in storage.
     */
    public function store(Request $request)
    {
        // Basic validation – extend this with your fields
        $data = $request->validate([
            'order_number'   => 'nullable|string|max:255',
            'order_date'     => 'nullable|date',
            'customer_name'  => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'status'         => 'nullable|string|max:50',
        ]);

        // Default status – receiving pipeline starts here
        if (empty($data['status'])) {
            $data['status'] = 'receiving';
        }

        $job = Job::create($data);

        return redirect()
            ->route('jobs.edit', $job)
            ->with('success', 'Job order created successfully.');
    }

    /**
     * Display the specified job.
     */
    public function show(Job $job)
    {
        return view('jobs.show', compact('job'));
    }

    /**
     * Show the form for editing the specified job.
     */
    public function edit(Job $job)
    {
        return view('jobs.edit', compact('job'));
    }

    /**
     * Update the specified job in storage.
     */
    public function update(Request $request, Job $job)
    {
        $data = $request->validate([
            'order_number'   => 'nullable|string|max:255',
            'order_date'     => 'nullable|date',
            'customer_name'  => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'status'         => 'nullable|string|max:50',
        ]);

        $job->update($data);

        return redirect()
            ->route('jobs.edit', $job)
            ->with('success', 'Job order updated successfully.');
    }

    /**
     * Remove the specified job from storage.
     */
    public function destroy(Job $job)
    {
        $job->delete();

        return redirect()
            ->route('jobs.index')
            ->with('success', 'Job order deleted successfully.');
    }
}
