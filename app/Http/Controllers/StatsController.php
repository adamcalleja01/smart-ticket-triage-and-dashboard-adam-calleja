<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Enums\Category;
use App\Enums\TicketStatus;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $status = Ticket::pluck('status')
            ->countBy()
            ->sortKeys();

        $category = Ticket::pluck('category')
            ->map(function ($v) {
                return $v === null ? 'no category' : $v;
            })
            ->countBy()
            ->sortKeys();

        return response()->json([
            'total_tickets' => Ticket::count(),
            'status' => $status,
            'category' => $category,
        ]); 
    }
}
