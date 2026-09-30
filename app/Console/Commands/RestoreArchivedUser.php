<?php

namespace App\Console\Commands;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Accounts are archived (soft-deleted), never erased, so a deleted staff member, doctor or patient
 * can come back with their designation/station, prescriptions and history intact.
 *
 *   php artisan user:restore someone@example.com
 *   php artisan user:restore --id=42
 */
class RestoreArchivedUser extends Command
{
    protected $signature = 'user:restore {email? : E-mail address of the archived account} {--id= : User id (when the account has no e-mail)}';

    protected $description = 'Restore an archived (soft-deleted) user account';

    public function handle(): int
    {
        $email = $this->argument('email');
        $id = $this->option('id');

        if (! $email && ! $id) {
            $this->error('Give an e-mail address or --id.');

            return self::INVALID;
        }

        $query = User::onlyTrashed();
        $email ? $query->where('email', mb_strtolower(trim($email))) : $query->whereKey($id);

        $user = $query->first();

        if (! $user) {
            $this->error('No archived account matches. (Active accounts do not need restoring.)');

            return self::FAILURE;
        }

        DB::transaction(function () use ($user) {
            $user->restore();

            // A patient record is archived together with its user.
            Patient::onlyTrashed()->where('user_id', $user->id)->get()->each->restore();
        });

        $this->info(sprintf(
            'Restored %s %s (id %d).',
            $user->first_name,
            $user->last_name,
            $user->id
        ));

        return self::SUCCESS;
    }
}
