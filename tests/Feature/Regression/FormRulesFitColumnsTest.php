<?php

namespace Tests\Feature\Regression;

use App\Models\DispenseRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 5, Pass A item 3: validation matches the database columns (MySQL strict mode).
 *
 * A text longer than its column is not cut off in strict mode: the INSERT fails. Most of the forms catch that and answer
 * with an error page (HTTP 422 / 500) instead of a message next to the field, and the person loses what was typed.
 * (Reproduced on the patient form: a 300-character first name; and the dispense record note was allowed 2,000 characters
 * by its rule but its column held 191.) So every free-text field of every form request must carry a `max` that fits the
 * column it is saved in.
 *
 * Fields that are bounded in another way are listed in BOUNDED_OTHERWISE and are not checked here.
 */
class FormRulesFitColumnsTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    /** form request (without Create / Update / Store and "Request") => the tables its fields are saved in */
    private const TABLES = [
        'Barangay' => ['barangays'], 'Category' => ['categories'], 'City' => ['cities'], 'Country' => ['countries'], 'Generic' => ['generics'],
        'DispenseRecord' => ['medicine_bills', 'sale_medicines'], 'MedicineBill' => ['medicine_bills', 'sale_medicines'],
        'MedicineAvailability' => ['medicine_availabilities', 'purchased_medicines'], 'Medicine' => ['medicines', 'medicine_batches'],
        'Patient' => ['users', 'patients', 'addresses'], 'Prescription' => ['prescriptions', 'prescriptions_medicines'], 'Qualification' => ['qualifications'],
        'Role' => ['roles'], 'Slider' => ['sliders'], 'Specialization' => ['specializations'], 'Staff' => ['users', 'staff_profiles', 'addresses'],
        'State' => ['states'], 'User' => ['users', 'doctors', 'addresses'], 'PatientQueue' => ['patient_queues'], 'FrontCms' => ['settings', 'sliders'],
        'Setting' => ['settings'], 'UserProfile' => ['users', 'addresses'],
    ];

    /**
     * Not checked by `max`: the password is saved as a 60-character hash whatever its length; phone numbers pass through
     * the Philippine phone rule (digits only, normalised); the stock-in dates are tied to a YYYY-MM[-DD] pattern.
     */
    private const BOUNDED_OTHERWISE = ['password', 'password_confirmation', 'contact', 'emergency_contact_no', 'manufacturing_date', 'expiry_date'];

    /** @return array<string, array<int, array{0: string, 1: int}>> column name => [[table, length], ...] of text-like columns */
    private function textColumns(): array
    {
        $columns = [];
        $rows = DB::select(
            "select c.table_name t, c.column_name n, c.character_maximum_length l
             from information_schema.columns c join information_schema.tables x on x.table_schema = c.table_schema and x.table_name = c.table_name
             where c.table_schema = ? and x.table_type = 'BASE TABLE' and c.character_maximum_length is not null",
            [DB::getDatabaseName()]
        );
        foreach ($rows as $row) {
            $columns[$row->n][] = [$row->t, (int) $row->l];
        }

        return $columns;
    }

    /** A route stub: the update requests ask for the record in the address to build their unique rules. */
    private function requestOf(string $class): FormRequest
    {
        $request = $class::createFrom(Request::create('/x', 'POST'));
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));
        $request->setUserResolver(fn () => null);
        $request->setRouteResolver(fn () => new class
        {
            public function parameter($name = null, $default = null)
            {
                return new class
                {
                    public $id = 1;

                    public function getKey() { return 1; }

                    public function __get($name) { return null; }
                };
            }

            public function parameters(): array { return []; }

            public function getName(): string { return 'stub'; }
        });

        return $request;
    }

    public function test_every_free_text_field_of_every_form_has_a_limit_that_fits_the_column_it_is_saved_in(): void
    {
        $columns = $this->textColumns();
        $problems = [];
        $checked = 0;
        $unread = [];

        foreach (glob(app_path('Http/Requests') . '/*.php') as $file) {
            $class = 'App\\Http\\Requests\\' . basename($file, '.php');
            if (! class_exists($class) || ! is_subclass_of($class, FormRequest::class)) {
                continue;
            }

            $base = preg_replace('/^(Create|Update|Store)/', '', preg_replace('/Request$/', '', basename($file, '.php')));
            $tables = self::TABLES[$base] ?? null;
            if ($tables === null) {
                continue; // no column of its own (change password and the like)
            }

            try {
                $rules = $this->requestOf($class)->rules();
            } catch (\Throwable $e) {
                $unread[] = basename($file, '.php') . ': ' . $e->getMessage();
                continue;
            }

            foreach ($rules as $attribute => $rule) {
                $field = preg_replace('/\.\*$/', '', (string) $attribute);
                if (str_contains($field, '.') || in_array($field, self::BOUNDED_OTHERWISE, true) || ! isset($columns[$field])) {
                    continue;
                }

                $parts = is_array($rule) ? $rule : explode('|', (string) $rule);
                $text = implode('|', array_map(fn ($part) => is_string($part) ? $part : (is_object($part) ? get_class($part) : ''), $parts));
                if (preg_match('/\b(integer|numeric|boolean|array|file|image|mimes|date|in:|exists:|digits)\b/', $text) === 1 && ! preg_match('/\bstring\b/', $text)) {
                    continue; // a number, a date, a list choice or a file: not free text
                }

                $max = preg_match('/\bmax:(\d+)/', $text, $found) === 1 ? (int) $found[1] : null;
                foreach ($columns[$field] as [$table, $length]) {
                    if (! in_array($table, $tables, true)) {
                        continue;
                    }
                    $checked++;
                    if ($max === null) {
                        $problems[] = basename($file, '.php') . ": {$field} has no max, {$table}.{$field} holds {$length}";
                    } elseif ($max > $length) {
                        $problems[] = basename($file, '.php') . ": {$field} allows {$max}, {$table}.{$field} holds {$length}";
                    }
                }
            }
        }

        $this->assertGreaterThan(60, $checked, 'the scan found the forms and their columns');
        $this->assertSame([], $problems, "A longer text would fail in strict mode:\n" . implode("\n", $problems));
        $this->assertSame([], $unread, 'every form request could be read: ' . implode('; ', $unread));
    }

    public function test_a_dispense_record_note_of_the_longest_length_its_form_allows_can_be_saved(): void
    {
        $this->seedAccessControl();
        $patient = $this->makePatient();

        // 2,000 characters, each one two bytes: far longer than the 191 characters the column used to hold.
        $record = DispenseRecord::create([
            'history_number' => 'HIS' . generateUniqueHistoryNumber(), 'patient_id' => $patient->id, 'model_type' => DispenseRecord::class,
            'model_id' => (string) $patient->id, 'bill_date' => now(), 'note' => str_repeat('ñ', 2000),
        ]);

        $this->assertSame(2000, mb_strlen($record->fresh()->note));
    }
}
