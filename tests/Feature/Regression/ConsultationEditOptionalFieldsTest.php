<?php

namespace Tests\Feature\Regression;

use App\Models\DocumentIssuance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Found by the Phase 9.3 browser walk-through (2026-10-22): the server treats "allergies" and "maintenance" of a
 * consultation as optional (`nullable`) on create and on update, and the create form leaves them optional, but the EDIT
 * form marked both `required`. A doctor opening a nurse's consultation where they were left blank could not save the
 * assessment until something was typed, although the server would have accepted it.
 */
class ConsultationEditOptionalFieldsTest extends TestCase
{
    use RefreshDatabase, BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function editPage(User $actor): string
    {
        $document = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $actor->id,
            'user_id' => $this->makePatient()->user_id, 'name' => 'Optional Fields Patient', 'age' => 23,
            'gender' => 'Male', 'address' => 'Test Address', 'requested_at' => now()->toDateString(),
        ]);

        return $this->actingAs($actor)->get(getRouteByRole('document-issuances.edit', [$document]))->assertOk()->getContent();
    }

    /** @return array<string, array{0: string}> */
    public static function roles(): array
    {
        return ['clinic admin' => ['makeAdmin'], 'staff (nurse)' => ['makeNurse'], 'doctor' => ['makeDoctor']];
    }

    #[DataProvider('roles')]
    public function test_allergies_and_maintenance_are_optional_on_the_edit_form(string $maker): void
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($this->editPage($this->{$maker}()));
        $xpath = new \DOMXPath($dom);

        foreach (['allergies', 'maintenance'] as $name) {
            $inputs = $xpath->query('//input[@name="'.$name.'"]');
            $this->assertGreaterThan(0, $inputs->length, "the edit form has a {$name} box");
            foreach ($inputs as $input) {
                $this->assertFalse($input->hasAttribute('required'), "{$name} is optional on the server, so the browser must not insist on it");
            }
        }
    }
}
