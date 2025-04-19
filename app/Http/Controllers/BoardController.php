<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Services\AIAssistantService;

class BoardController extends Controller
{
    /**
     * Display a listing of the boards
     */
    public function index()
    {
        $boards = Board::latest()->get();
        
        return Inertia::render('Boards/Index', [
            'boards' => $boards
        ]);
    }

    /**
     * Display the specified board
     */
    public function show($id, AIAssistantService $aiService)
    {
        $board = Board::with(['tasks', 'tasks.user'])->findOrFail($id);
        
        // Generate AI insights if the board has tasks
        $aiInsights = null;
        if ($board->tasks->count() > 0) {
            $aiInsights = $aiService->generateBoardInsights($board);
        }
        
        return Inertia::render('Boards/Show', [
            'board' => $board,
            'aiInsights' => $aiInsights,
        ]);
    }

    /**
     * Show the form for creating a new board
     */
    public function create()
    {
        return Inertia::render('Boards/Create');
    }

    /**
     * Store a newly created board
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);
        
        Board::create($validated);
        
        return redirect()->route('boards.index');
    }

    /**
     * Show the form for editing the specified board
     */
    public function edit($id)
    {
        $board = Board::findOrFail($id);
        
        return Inertia::render('Boards/Edit', [
            'board' => $board
        ]);
    }

    /**
     * Update the specified board
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);
        
        // Use direct SQL update to ensure the update works
        DB::table('boards')->where('id', $id)->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'user_id' => $validated['user_id'],
            'updated_at' => now(),
        ]);
        
        return redirect()->route('boards.show', $id);
    }

    /**
     * Remove the specified board
     */
    public function destroy($id)
    {
        $board = Board::findOrFail($id);
        $board->delete();
        
        return redirect()->route('boards.index');
    }
}