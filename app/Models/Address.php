<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Address
 *
 * @property int $id
 * @property int|null $owner_id
 * @property string|null $owner_type
 * @property string|null $address1
 * @property string|null $address2
 * @property int|null $country_id
 * @property int|null $state_id
 * @property int|null $city_id
 * @property string|null $postal_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|\Eloquent $owner
 * @method static \Illuminate\Database\Eloquent\Builder|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address query()
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereOwnerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Address extends Model
{
    use HasFactory;

    protected $table = 'addresses';

    protected $fillable = [
        'address1',
        'address2',
        'country_id',
        'state_id',
        'city_id',
        'barangay_id',
        'postal_code',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['full_address'];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the barangay associated with the address.
     */
    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    /**
     * Get the city associated with the address.
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Get the state/province associated with the address.
     */
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    /**
     * Get the full formatted address.
     * Format: Barangay {name}, {City name} {Province}, {postal_code}
     */
    public function getFullAddressAttribute(): string
    {
        $parts = [];

        // Add barangay if available
        if ($this->barangay) {
            $parts[] = 'Barangay ' . $this->barangay->name;
        }

        // Add city if available
        if ($this->city) {
            $parts[] = $this->city->name;
        }

        // Add state/province if available
        if ($this->state) {
            $parts[] = $this->state->name;
        }

        // Add postal code if available
        if ($this->postal_code) {
            $parts[] = $this->postal_code;
        }

        // If we have parts, join them; otherwise fall back to address1
        if (!empty($parts)) {
            return implode(', ', $parts);
        }

        return $this->address1 ?? '';
    }
}
