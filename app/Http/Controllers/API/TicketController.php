<?php

namespace App\Http\Controllers\API;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Symfony\Component\Uid\Ulid;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $search = request()->input('search');
        $filter = request()->input('filter');

        return response()->json(
            Ticket::when($search, function ($query, $search) {
                $query->where('subject', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            })
                ->when($filter, function ($query, $filter) {
                    $query->where('status', $filter);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(9)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'subject' => ['required', 'string', 'max:255'],
            'body'    => ['required', 'string'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $ticket = Ticket::create([
            'id'      => Ulid::generate(),
            'subject' => $validated['subject'],
            'body'    => $validated['body'],
        ]);

        return response()->json($ticket, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
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
        abort(404);
    }
}
