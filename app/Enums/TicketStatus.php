<?php

namespace App\Enums;

enum TicketStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED = 'resolved';
    case PENDING = 'pending';
    case CLOSED = 'closed';
    case ESCALATED = 'escalated';
    case REOPENED = 'reopened';
    
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::OPEN => 'Open',
            self::IN_PROGRESS => 'In Progress',
            self::RESOLVED => 'Resolved',
            self::PENDING => 'Pending',
            self::CLOSED => 'Closed',
            self::ESCALATED => 'Escalated',
            self::REOPENED => 'Reopened',
        };
    }
}
