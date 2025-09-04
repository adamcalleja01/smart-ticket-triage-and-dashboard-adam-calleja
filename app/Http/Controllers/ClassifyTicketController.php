<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\TicketClassifier;
use Illuminate\Http\Request;

class ClassifyTicketController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Ticket $ticket)
    {
        $classification = (new TicketClassifier())->classify($ticket);

        $ticket->update([
            'category'   => $classification['category'] ?? null,
            'explanation'=> $classification['explanation'] ?? null,
            'confidence' => $classification['confidence'] ?? null,
        ]);

        return redirect()->back();
    }
}
