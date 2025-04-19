<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets
     */
    public function index()
    {
        // This will eventually fetch tickets from the database
        // For now, we're just rendering the view
        return Inertia::render('Tickets/Index');
    }

    /**
     * Display the specified ticket
     */
    public function show($id)
    {
        // This will eventually fetch a specific ticket from the database
        // For now, we're just rendering the view with a ticketId parameter
        return Inertia::render('Tickets/Show', [
            'ticketId' => $id
        ]);
    }

    /**
     * Show the form for creating a new ticket
     */
    public function create()
    {
        return Inertia::render('Tickets/Create');
    }

    /**
     * Store a newly created ticket
     */
    public function store(Request $request)
    {
        // This will eventually store a new ticket in the database
        // For now, we'll just redirect to the tickets index
        return redirect()->route('tickets.index');
    }

    /**
     * Show the form for editing the specified ticket
     */
    public function edit($id)
    {
        // This will eventually fetch a specific ticket for editing
        return Inertia::render('Tickets/Edit', [
            'ticketId' => $id
        ]);
    }

    /**
     * Update the specified ticket
     */
    public function update(Request $request, $id)
    {
        // This will eventually update a specific ticket in the database
        // For now, we'll just redirect to the ticket's show page
        return redirect()->route('tickets.show', $id);
    }

    /**
     * Remove the specified ticket
     */
    public function destroy($id)
    {
        // This will eventually delete a specific ticket from the database
        // For now, we'll just redirect to the tickets index
        return redirect()->route('tickets.index');
    }
}
