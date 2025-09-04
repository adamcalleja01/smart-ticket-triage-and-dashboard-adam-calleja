<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\TicketClassifier;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClassifyTicket implements ShouldQueue
{
    use Queueable, InteractsWithQueue, Queueable, SerializesModels;

    protected Ticket $ticket;


    /**
     * Create a new job instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ticket = $this->ticket->fresh();

        if (! $ticket) {
            return;
        }

        $classification = (new TicketClassifier())->classify($ticket) ?? [];

        $updateData = [
            'explanation' => $classification['explanation'] ?? null,
            'confidence'  => $classification['confidence'] ?? null,
        ];

        // Only set category if none exists (preserve manual choice)
        if (empty($ticket->category)) {
            $updateData['category'] = $classification['category'] ?? null;
        }

        $ticket->update($updateData);
    }
}
