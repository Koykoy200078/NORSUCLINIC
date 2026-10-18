<?php

namespace Tests\Feature\Regression;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 5, row 5.6 (found live on the medicine form): a browser sends every empty box as "", which Laravel turns
 * into null. A field that its rule lets stay blank (`nullable`) but whose column is NOT NULL with no way out would fail the
 * INSERT in strict mode, and the whole form with it ("Column 'reorder_level' cannot be null"). Each such field must be
 * handled by its controller (blank -> the column's default) and listed in HANDLED, or the rule must ask for a value.
 */
class BlankFieldsFitColumnsTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

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
     * Fields that may stay blank and are still safe, keyed "FormRequest.field" => why.
     */
    private const HANDLED = [
        'CreateMedicineAvailabilityRequest.availability_no' => 'a blank number is drawn by the controller (generateUniqueAvailabilityNumber)',
        'CreateMedicineRequest.reorder_level' => 'a blank is saved as 0 (test_the_medicine_form_saves_when_the_optional_numbers_are_left_blank...)',
        'UpdateMedicineRequest.reorder_level' => 'a blank is saved as 0 (same test)',
        'CreateMedicineRequest.batch_number' => 'a batch is written only when stock is typed, and then the number and the expiry date are required',
        'UpdateMedicineRequest.batch_number' => 'same',
        'CreateMedicineRequest.expiration_date' => 'same',
        'UpdateMedicineRequest.expiration_date' => 'same',
        'CreateUserRequest.status' => 'the controller saves isset(status) ? 1 : 0 (a check box)',
        'UpdateUserRequest.status' => 'same',
    ];

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

    public function test_a_field_that_may_be_blank_never_meets_a_column_that_cannot_be_null(): void
    {
        $notNull = [];
        foreach (DB::select("select c.table_name t, c.column_name n, c.column_default d from information_schema.columns c join information_schema.tables x on x.table_schema = c.table_schema and x.table_name = c.table_name where c.table_schema = ? and x.table_type = 'BASE TABLE' and c.is_nullable = 'NO' and c.extra not like '%auto_increment%'", [DB::getDatabaseName()]) as $row) {
            $notNull[$row->n][] = $row->t;
        }
        $problems = [];
        foreach (glob(app_path('Http/Requests') . '/*.php') as $file) {
            $class = 'App\\Http\\Requests\\' . basename($file, '.php');
            if (! class_exists($class) || ! is_subclass_of($class, FormRequest::class)) {
                continue;
            }
            $base = preg_replace('/^(Create|Update|Store)/', '', preg_replace('/Request$/', '', basename($file, '.php')));
            $tables = self::TABLES[$base] ?? null;
            if ($tables === null) {
                continue;
            }
            try {
                $rules = $this->requestOf($class)->rules();
            } catch (\Throwable $e) {
                continue;
            }
            foreach ($rules as $attribute => $rule) {
                $field = (string) $attribute;
                if (str_contains($field, '.') || ! isset($notNull[$field])) {
                    continue;
                }
                $parts = is_array($rule) ? $rule : explode('|', (string) $rule);
                $text = implode('|', array_map(fn ($p) => is_string($p) ? $p : '', $parts));
                if (! str_contains($text, 'nullable')) {
                    continue;
                }
                foreach ($notNull[$field] as $table) {
                    if (in_array($table, $tables, true) && ! isset(self::HANDLED[basename($file, '.php') . '.' . $field])) {
                        $problems[] = basename($file, '.php') . ": {$field} is nullable but {$table}.{$field} is NOT NULL";
                    }
                }
            }
        }
        $this->assertSame([], $problems, "These rules let a field stay blank, but the column cannot hold null (the INSERT fails in strict mode):
" . implode("
", $problems));
    }

    public function test_a_blank_queue_status_is_refused_with_a_message_not_saved_as_null(): void
    {
        $this->seedAccessControl();
        $nurse = $this->makeNurse();
        $entry = \App\Models\PatientQueue::create([
            'patient_id' => $this->makePatient()->id, 'added_by' => $nurse->id, 'status' => \App\Models\PatientQueue::STATUS_WAITING,
            'is_priority' => false, 'queued_at' => now(),
        ]);

        $this->actingAs($nurse)->put(route('staff.patient-queue.update', $entry), ['status' => '', 'room_number' => '3'])->assertSessionHasErrors();
        $this->assertSame(\App\Models\PatientQueue::STATUS_WAITING, $entry->fresh()->status);
    }
}
