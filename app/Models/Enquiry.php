<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Enquiry
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string $subject
 * @property string $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $view
 * @property string|null $country_code
 * @property-read string $view_name
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry query()
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereView($value)
 * @mixin \Eloquent
 */
class Enquiry extends Model
{
    use HasFactory;

    protected $table = 'enquiries';

    public $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'phone',
        'view',
        'country_code',
    ];

    protected $casts = [
        'name' => 'string',
        'email' => 'string',
        'subject' => 'string',
        'message' => 'string',
        'phone' => 'string',
        'view' => 'boolean',
        'country_code' => 'string',
    ];

    protected $appends = ['view_name'];

    const ALL = 2;

    const READ = 1;

    const UNREAD = 0;

    const VIEW_NAME = [
        self::ALL => 'All',
        self::READ => 'Read',
        self::UNREAD => 'Unread',
    ];

    public static $rules = [
        'name' => 'required',
        'email' => 'required|email|max:255|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
        'message' => 'required',
        'subject' => 'required',
    ];

    public function getViewNameAttribute(): string
    {
        return self::VIEW_NAME[$this->view];
    }
}
