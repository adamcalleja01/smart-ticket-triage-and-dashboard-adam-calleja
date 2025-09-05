<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Ticket;
use App\Enums\Category;
use App\Enums\TicketStatus;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Tickets/Index', [
            'ticket_status' => array_map(function ($case) {
                return [
                    'value' => $case->value,
                    'label' => $case->label(),
                ];
            }, TicketStatus::cases()),
            'categories' => array_map(function ($case) {
                return [
                    'value' => $case->value,
                    'label' => $case->label(),
                ];
            }, Category::cases())
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
            'ticket_status' => array_map(function ($case) {
                return [
                    'value' => $case->value,
                    'label' => $case->label(),
                ];
            }, TicketStatus::cases()),
            'categories' => array_map(function ($case) {
                return [
                    'value' => $case->value,
                    'label' => $case->label(),
                ];
            }, Category::cases())

        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        //
    }
}
