<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\User;
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

    /**
     * A line for something that happens BEFORE anybody is signed in (a failed or blocked sign-in). Only the e-mail
     * that was typed is kept - never the password. $user is the account that e-mail belongs to, if there is one.
     */
    public static function recordAttempt(string $action, string $description, ?string $email, ?User $user = null): ActivityLog
    {
        return ActivityLog::create([
            'action' => $action,
            'user_id' => $user?->id,
            'user_name' => $user?->full_name,
            'description' => $description,
            'date' => now()->toDateString(),
            'properties' => ['email' => $email !== null ? mb_substr(mb_strtolower(trim($email)), 0, 191) : null],
            'ip_address' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 191),
        ]);
    }
}
