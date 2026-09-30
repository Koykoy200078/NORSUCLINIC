<?php

namespace App\Http\Requests\Concerns;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\Rule;

/**
 * Users are soft-deleted (archived_at), but the e-mail / employee ID / university ID / contact
 * columns keep their UNIQUE constraint. The plain `unique:users,x` rule therefore rejected a new
 * account with a bare "has already been taken" even though the only holder is an archived person
 * nobody can see, and the same person could never be registered again (M-13).
 *
 * Requests that use this trait
 *  - apply the unique rule to ACTIVE accounts only (uniqueAmongActiveUsers()), and
 *  - then explain, per field, when the value belongs to an archived account (validateArchivedAccountConflicts()).
 */
trait ChecksArchivedAccounts
{
    protected function uniqueAmongActiveUsers(string $column, ?int $ignoreUserId = null): Unique
    {
        $rule = Rule::unique('users', $column)->whereNull('archived_at');

        return $ignoreUserId !== null ? $rule->ignore($ignoreUserId) : $rule;
    }

    /**
     * @param  array<string, string>  $columns  request field => users column
     */
    protected function validateArchivedAccountConflicts(Validator $validator, array $columns, ?int $ignoreUserId = null): void
    {
        $validator->after(function (Validator $validator) use ($columns, $ignoreUserId) {
            foreach ($columns as $field => $column) {
                $value = $this->input($field);

                if ($value === null || trim((string) $value) === '' || $validator->errors()->has($field)) {
                    continue;
                }

                $value = $column === 'email' ? mb_strtolower(trim((string) $value)) : trim((string) $value);

                $archived = User::onlyTrashed()
                    ->where($column, $value)
                    ->when($ignoreUserId !== null, fn ($query) => $query->where('id', '!=', $ignoreUserId))
                    ->first();

                if ($archived) {
                    $validator->errors()->add(
                        $field,
                        'This ' . str_replace('_', ' ', $column) . ' belongs to an archived account ('
                        . trim($archived->first_name . ' ' . $archived->last_name)
                        . '). Ask the system administrator to restore that account instead of creating a new one.'
                    );
                }
            }
        });
    }
}
