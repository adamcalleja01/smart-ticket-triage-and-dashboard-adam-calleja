<?php

namespace App\Enums;

enum Category: string
{
    case BUG = 'bug';
    case ACCESS = 'access';
    case BILLING = 'billing';
    case FEATURE_REQUEST = 'feature_request';
    case GENERAL_INQUIRY = 'general_inquiry';
    case HOW_TO = 'how_to';
    case INCIDENT = 'incident';
    case PERFORMANCE = 'performance';
    case OTHER = 'other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::BUG => 'Bug',
            self::ACCESS => 'Access',
            self::BILLING => 'Billing',
            self::FEATURE_REQUEST => 'Feature Request',
            self::GENERAL_INQUIRY => 'General Inquiry',
            self::HOW_TO => 'How To',
            self::INCIDENT => 'Incident',
            self::PERFORMANCE => 'Performance',
            self::OTHER => 'Other',
        };
    }
}
