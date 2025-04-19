<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\AIAssistantService;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets
     */
    public function index()
    {
        $tickets = Task::latest()->get();
        
        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets
        ]);
    }

    /**
     * Display the specified ticket
     */
    public function show($id)
    {
        $ticket = Task::findOrFail($id);
        
        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket
        ]);
    }

    /**
     * Show the form for creating a new ticket
     */
    public function create()
    {
        return Inertia::render('Tickets/Create', [
            'aiAssistEnabled' => true
        ]);
    }
    
    /**
     * AI assistance for task details
     */
    public function aiAssist(Request $request, AIAssistantService $aiService)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assistType' => 'required|in:description,priority',
        ]);
        
        if ($request->assistType === 'description') {
            $enhancedDescription = $aiService->enhanceTaskDescription(
                $request->title,
                $request->description
            );
            
            return response()->json([
                'description' => $enhancedDescription
            ]);
        }
        
        if ($request->assistType === 'priority') {
            $suggestedPriority = $aiService->suggestTaskPriority(
                $request->title,
                $request->description
            );
            
            return response()->json([
                'priority' => $suggestedPriority
            ]);
        }
        
        return response()->json([
            'error' => 'Invalid assistance type'
        ], 400);
    }

    /**
     * Store a newly created ticket
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'priority' => 'required|string',
            'board_id' => 'required|exists:boards,id',
            'user_id' => 'required|exists:users,id',
        ]);
        
        Task::create($validated);
        
        return redirect()->route('tickets.index');
    }

    /**
     * Show the form for editing the specified ticket
     */
    public function edit($id)
    {
        $ticket = Task::findOrFail($id);
        
        return Inertia::render('Tickets/Edit', [
            'ticket' => $ticket
        ]);
    }

    /**
     * Update the specified ticket
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'priority' => 'required|string',
            'board_id' => 'required|exists:boards,id',
            'user_id' => 'required|exists:users,id',
        ]);
        
        // Use direct SQL update to ensure the update works
        DB::table('tasks')->where('id', $id)->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'board_id' => $validated['board_id'],
            'user_id' => $validated['user_id'],
            'updated_at' => now(),
        ]);
        
        return redirect()->route('tickets.show', $id);
    }

    /**
     * Remove the specified ticket
     */
    public function destroy($id)
    {
        $ticket = Task::findOrFail($id);
        $ticket->delete();
        
        return redirect()->route('tickets.index');
    }
}