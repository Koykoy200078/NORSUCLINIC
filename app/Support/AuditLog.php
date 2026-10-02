<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Traits\LogsActivity;

/**
 * Writes one row of the append-only activity trail from anywhere (controllers, middleware, event listeners) without
 * having to `use LogsActivity` first. The row carries who did it, from which address, and - while the administrator
 * is acting as another account - who the real person is (properties.impersonated_by).
 */
final class AuditLog
{
    use LogsActivity;

    /**
     * @param  array<string, mixed>  $details  see LogsActivity::logActivity()
     */
    public static function record(string $action, string $description, array $details = []): ?ActivityLog
    {
        return static::logActivity($action, $description, $details);
    }
}
