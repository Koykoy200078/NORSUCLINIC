<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Notification
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $type
 * @property int|null $read_at
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUserId($value)
 * @mixin \Eloquent
 */
class Notification extends Model
{
    use HasFactory;

    public $fillable = [
        'title',
        'type',
        'description',
        'read_at',
        'user_id',
        'event_key', 'module', 'target_id', 'resolved_at', 'alert_details',
    ];

    protected $casts = [
        'title' => 'string',
        'type' => 'string',
        'description' => 'string',
        'read_at' => 'timestamp',
        'user_id' => 'integer',
        'resolved_at' => 'datetime',
    ];

    const BOOKED = 'booked';

    const CHECKOUT = 'checkout';

    const CANCELED = 'canceled';

    const REVIEW = 'review';

    const LIVE_CONSULTATION = 'live_consultation';

    public function scopeVisibleTo($query, User $user)
    {
        if (! static::supportsClinicAlerts()) {
            return $query->where('user_id', $user->id);
        }
        $modules = array_values(array_filter(['inventory', 'queue', 'consultations'], fn ($module) => canUseModule($module, $user)));

        return $query->where('user_id', $user->id)->where(function ($q) use ($modules) {
            $q->whereNull('module')->orWhereIn('module', $modules);
        });
    }

    public static function supportsClinicAlerts(): bool
    {
        $key = 'notifications.schema.'.\Illuminate\Support\Facades\DB::connection()->getDatabaseName();
        if (! app()->bound($key)) {
            app()->instance($key, \Illuminate\Support\Facades\Schema::hasColumn('notifications', 'event_key'));
        }

        return app($key);
    }

    public function scopeUnresolved($query)
    {
        return static::supportsClinicAlerts() ? $query->whereNull('resolved_at') : $query;
    }

    public function getDestinationAttribute(): ?string
    {
        if (! $this->module || ! canUseModule($this->module)) {
            return null;
        }

        return match ($this->module) {
            'inventory' => getRouteByRole('medicines.index'),
            'queue' => getRouteByRole('patient-queue.index'),
            'consultations' => getRouteByRole('document-issuances.show', [$this->target_id]),
            default => null,
        };
    }
}
