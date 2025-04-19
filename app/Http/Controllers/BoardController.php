<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class BoardController extends Controller
{
    /**
     * Display a listing of the boards
     */
    public function index()
    {
        // This will eventually fetch boards from the database
        // For now, we're just rendering the view
        return Inertia::render('Boards/Index');
    }

    /**
     * Display the specified board
     */
    public function show($id)
    {
        // This will eventually fetch a specific board from the database
        // For now, we're just rendering the view with a boardId parameter
        return Inertia::render('Boards/Show', [
            'boardId' => $id
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
        // This will eventually store a new board in the database
        // For now, we'll just redirect to the boards index
        return redirect()->route('boards.index');
    }

    /**
     * Show the form for editing the specified board
     */
    public function edit($id)
    {
        // This will eventually fetch a specific board for editing
        return Inertia::render('Boards/Edit', [
            'boardId' => $id
        ]);
    }

    /**
     * Update the specified board
     */
    public function update(Request $request, $id)
    {
        // This will eventually update a specific board in the database
        // For now, we'll just redirect to the board's show page
        return redirect()->route('boards.show', $id);
    }

    /**
     * Remove the specified board
     */
    public function destroy($id)
    {
        // This will eventually delete a specific board from the database
        // For now, we'll just redirect to the boards index
        return redirect()->route('boards.index');
    }
}
