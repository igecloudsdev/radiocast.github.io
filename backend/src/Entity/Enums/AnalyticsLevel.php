<?php

declare(strict_types=1);

namespace App\Entity\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(type: 'string')]
enum AnalyticsLevel: string
{
    case All = 'all'; // Log all analytics data across the system.
    case NoIp = 'no_ip'; // Suppress any IP-based logging and use aggregate logging only.
    case None = 'none'; // No analytics data collected of any sort.

    public static function default(): self
    {
        return self::All;
    }
}
