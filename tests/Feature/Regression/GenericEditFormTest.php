<?php

namespace Tests\Feature\Regression;

use App\Models\Generic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Re-audit 2026-11-13 (DRY pass): the generic edit page sent its form to the admin route `generics.update`, so a Staff
 * (Nurse), who opens it at /staff/generics/{id}/edit with the medicines permission, could not save a change (the admin
 * route refuses the staff role). The form must go to the address of the panel the page was opened in.
 */
class GenericEditFormTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    public function test_a_nurse_edits_a_generic_through_the_staff_address(): void
    {
        $generic = Generic::create(['name' => 'Paracetamol']);
        $nurse = $this->makeNurse();

        $this->actingAs($nurse)->get(route('staff.generics.edit', $generic))
            ->assertOk()
            ->assertSee('action="' . route('staff.generics.update', $generic) . '"', false)
            ->assertDontSee('action="' . route('generics.update', $generic) . '"', false);

        $this->patch(route('staff.generics.update', $generic), ['name' => 'Paracetamol 500'])
            ->assertRedirect(route('staff.generics.index'));
        $this->assertSame('Paracetamol 500', $generic->fresh()->name);
    }

    public function test_the_admin_edit_page_keeps_the_admin_address(): void
    {
        $generic = Generic::create(['name' => 'Ibuprofen']);

        $this->actingAs($this->makeAdmin())->get(route('generics.edit', $generic))
            ->assertOk()
            ->assertSee('action="' . route('generics.update', $generic) . '"', false);
    }
}
