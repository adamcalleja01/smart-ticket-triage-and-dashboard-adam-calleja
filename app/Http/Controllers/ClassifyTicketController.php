<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Jobs\ClassifyTicket;
use Illuminate\Http\Request;
use App\Services\TicketClassifier;

class ClassifyTicketController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Ticket $ticket)
    {
        ClassifyTicket::dispatch($ticket);

        return response()->json(['message' => 'Ticket classification in progress'], 200);
    }
}
