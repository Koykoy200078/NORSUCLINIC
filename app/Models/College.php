<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\College
 *
 * @property int $id
 * @property string $college_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|College newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|College newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|College query()
 * @method static \Illuminate\Database\Eloquent\Builder|College whereCollegeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|College whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|College whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|College whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class College extends Model
{
    use HasFactory;
    public $table = 'colleges';
    protected $fillable = [
        'college_name',
    ];

    /**
     * The short name used as a column heading in reports: "CAS" from "College of Arts and Sciences (CAS)".
     * A name without brackets falls back to the first letters of its main words.
     */
    public function getAbbreviationAttribute(): string
    {
        $name = trim((string) $this->college_name);

        if (preg_match('/\(([^()]+)\)\s*$/', $name, $matches)) {
            return trim($matches[1]);
        }

        $initials = collect(preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY))
            ->reject(fn (string $word) => in_array(strtolower($word), ['of', 'and', 'the', 'for', 'in'], true))
            ->map(fn (string $word) => strtoupper(mb_substr($word, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : $name;
    }
}
