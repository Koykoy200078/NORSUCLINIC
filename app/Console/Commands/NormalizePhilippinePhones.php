<?php

namespace App\Console\Commands;

use App\Support\PhilippinePhone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * The clinic works with Philippine (+63) numbers only. Older screens stored whatever was typed
 * ("09171234567" with no country code, "+63 917...", "63917...") which the new +63 input and the
 * "unique contact" checks can no longer compare reliably.
 *
 * Rewrites, to the convention documented in {@see PhilippinePhone}:
 *   users.contact            -> national digits ("9171234567"), users.country_code -> "63"
 *   users.emergency_contact_no -> "+639171234567"
 *   settings.contact_no      -> national digits, settings.country_code -> "63", default_country_code -> "ph"
 *
 * Values that are not Philippine numbers (e.g. "N/A", a foreign number) are left untouched and listed so
 * somebody can correct them by hand. The command is idempotent; it is a dry run unless --apply is given.
 * Take a database backup before --apply.
 */
class NormalizePhilippinePhones extends Command
{
    protected $signature = 'phone:normalize {--apply : Write the normalised values (default is a dry run)}';

    protected $description = 'Store every phone number in the Philippine (+63) format used by the +63 input';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $changed = 0;
        $unreadable = [];

        DB::table('users')
            ->select(['id', 'contact', 'country_code', 'emergency_contact_no'])
            ->where(function ($q) {
                $q->whereNotNull('contact')->orWhereNotNull('emergency_contact_no');
            })
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($apply, &$changed, &$unreadable) {
                foreach ($users as $user) {
                    $update = [];

                    if ($user->contact !== null && trim($user->contact) !== '') {
                        $national = PhilippinePhone::national($user->contact);

                        if ($national === null) {
                            $unreadable[] = ['users', $user->id, 'contact', $user->contact];
                        } else {
                            if ($user->contact !== $national) {
                                $update['contact'] = $national;
                            }
                            if ($user->country_code !== PhilippinePhone::COUNTRY_CODE) {
                                $update['country_code'] = PhilippinePhone::COUNTRY_CODE;
                            }
                        }
                    }

                    if ($user->emergency_contact_no !== null && trim($user->emergency_contact_no) !== '') {
                        $e164 = PhilippinePhone::e164($user->emergency_contact_no);

                        if ($e164 === null) {
                            $unreadable[] = ['users', $user->id, 'emergency_contact_no', $user->emergency_contact_no];
                        } elseif ($user->emergency_contact_no !== $e164) {
                            $update['emergency_contact_no'] = $e164;
                        }
                    }

                    if ($update !== []) {
                        $changed++;
                        $this->line(sprintf('users #%d: %s', $user->id, json_encode($update)));

                        if ($apply) {
                            DB::table('users')->where('id', $user->id)->update($update);
                        }
                    }
                }
            });

        $changed += $this->normalizeSettings($apply, $unreadable);

        if ($unreadable !== []) {
            $this->newLine();
            $this->warn('Not a Philippine number - left as they are, please correct by hand:');
            $this->table(['table', 'id', 'column', 'value'], $unreadable);
        }

        $this->newLine();
        $this->info($apply
            ? "Updated {$changed} record(s)."
            : "Dry run: {$changed} record(s) would be updated. Re-run with --apply to write them.");

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array<int, mixed>>  $unreadable
     */
    private function normalizeSettings(bool $apply, array &$unreadable): int
    {
        $values = DB::table('settings')
            ->whereIn('key', ['contact_no', 'country_code', 'default_country_code'])
            ->pluck('value', 'key');

        $wanted = [];
        $contact = $values['contact_no'] ?? null;

        if ($contact !== null && trim((string) $contact) !== '') {
            $national = PhilippinePhone::national($contact);

            if ($national === null) {
                $unreadable[] = ['settings', 'contact_no', 'value', $contact];
            } elseif ($contact !== $national) {
                $wanted['contact_no'] = $national;
            }
        }

        if ($values->has('country_code') && $values['country_code'] !== PhilippinePhone::COUNTRY_CODE) {
            $wanted['country_code'] = PhilippinePhone::COUNTRY_CODE;
        }

        if ($values->has('default_country_code') && $values['default_country_code'] !== PhilippinePhone::DEFAULT_COUNTRY_ISO) {
            $wanted['default_country_code'] = PhilippinePhone::DEFAULT_COUNTRY_ISO;
        }

        foreach ($wanted as $key => $value) {
            $this->line(sprintf('settings %s: %s -> %s', $key, $values[$key], $value));

            if ($apply) {
                DB::table('settings')->where('key', $key)->update(['value' => $value]);
            }
        }

        if ($apply && $wanted !== []) {
            \App\Services\SettingsService::clearCache();
        }

        return count($wanted);
    }
}
