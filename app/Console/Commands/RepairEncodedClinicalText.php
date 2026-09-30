<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Until the input purifier middleware was removed, every value typed on the staff / doctor /
 * profile pages went through HTMLPurifier, which stored "&" as "&amp;", "<" as "&lt;" and ">" as
 * "&gt;" (and deleted anything that looked like a tag, which cannot be recovered). Blade escapes
 * output again, so users saw the literal text "&gt;" in notes and PDFs.
 *
 * This command reverses that one level of encoding in free-text clinical columns. It is NOT run
 * automatically: run it without options first (a dry run), review the counts, then run it with --apply.
 * Take a database backup before --apply, and do not run --apply repeatedly: each run decodes one more
 * level, so a value that legitimately contains the text "&amp;" would be changed by a second run.
 */
class RepairEncodedClinicalText extends Command
{
    protected $signature = 'text:repair-entities {--apply : Write the decoded values (default is a dry run)}';

    protected $description = 'Decode &amp; / &lt; / &gt; left in clinical free-text columns by the removed input purifier';

    /** table => free-text columns */
    private const COLUMNS = [
        'document_issuances' => [
            'name', 'address', 'status', 'religion', 'emergency_contact', 'complaints', 'note',
            'allergies', 'comorbidities', 'admissions_surgeries', 'maintenance', 'pregnancy_status',
            'lmp_aog', 'pertinent_exam', 'assessment', 'plan', 'nursing_intervention',
            'complaints_diagnosis', 'medical_cert_remarks', 'request_of',
        ],
        'prescriptions' => [
            'problem_description', 'advice', 'test', 'current_medication', 'food_allergies',
        ],
        'prescriptions_medicines' => ['instructions', 'comment'],
        'lab_requests' => ['patient_name', 'address', 'clinical_indication'],
        'lab_request_items' => ['test_name'],
        'patients' => [
            'allergies', 'comorbidities', 'admissions_surgeries', 'maintenance', 'immunization_record',
        ],
        'users' => ['first_name', 'middle_name', 'last_name', 'emergency_contact_name', 'emergency_relationship'],
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $totalRows = 0;

        foreach (self::COLUMNS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                $query = DB::table($table)->where(function ($q) use ($column) {
                    $q->where($column, 'like', '%&lt;%')
                        ->orWhere($column, 'like', '%&gt;%')
                        ->orWhere($column, 'like', '%&amp;%');
                });

                $count = (clone $query)->count();
                if ($count === 0) {
                    continue;
                }

                $totalRows += $count;
                $this->line(sprintf('%s.%s: %d row(s)', $table, $column, $count));

                if ($apply) {
                    // &amp; last, so "&amp;lt;" becomes "&lt;" (one level) rather than "<".
                    $query->update([
                        $column => DB::raw(sprintf(
                            "REPLACE(REPLACE(REPLACE(`%s`, '&lt;', '<'), '&gt;', '>'), '&amp;', '&')",
                            $column
                        )),
                    ]);
                }
            }
        }

        if ($totalRows === 0) {
            $this->info('No encoded clinical text found.');

            return self::SUCCESS;
        }

        $this->info(($apply ? 'Decoded ' : 'Would decode ') . $totalRows . ' value(s).'
            . ($apply ? '' : ' Re-run with --apply (after a database backup) to write the changes.'));

        return self::SUCCESS;
    }
}
