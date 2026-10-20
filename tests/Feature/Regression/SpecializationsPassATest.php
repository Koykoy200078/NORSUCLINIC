<?php

namespace Tests\Feature\Regression;

use App\Livewire\SpecializationTable;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\Specialization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

class SpecializationsPassATest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    public function test_specializations_validate_and_search_names_and_preserve_archived_doctors_links(): void
    {
        $this->seedAccessControl();
        $this->actingAs($this->makeAdmin());
        $this->postJson(route('specializations.store'), ['name' => str_repeat('a', 192)])->assertUnprocessable();
        $this->postJson(route('specializations.store'), ['name' => '<b>Cardiology</b>', 'id' => 999999])->assertOk();
        $specialty = Specialization::whereName('<b>Cardiology</b>')->firstOrFail();
        $this->postJson(route('specializations.store'), ['name' => '<b>CARDIOLOGY</b>'])->assertUnprocessable();
        $this->putJson(route('specializations.update', $specialty), ['name' => $specialty->name])->assertOk();
        Livewire::withoutLazyLoading()->test(SpecializationTable::class)->set('search', 'Cardiology')
            ->assertSee('&lt;b&gt;Cardiology&lt;/b&gt;', false)->assertDontSeeHtml('<b>Cardiology</b>');
        $doctor = $this->makeDoctor();
        $doctor->doctor->specializations()->attach($specialty);
        $doctor->delete();
        $this->deleteJson(route('specializations.destroy', $specialty))->assertUnprocessable();
        $this->assertNotNull($specialty->fresh());
        $doctor->doctor->specializations()->detach($specialty);
        $this->deleteJson(route('specializations.destroy', $specialty))->assertOk();
        $this->assertSame(3, ActivityLog::where('action', 'master_data_changed')->count());
    }

    public function test_every_specialization_endpoint_obeys_the_roles_permission(): void
    {
        $this->seedAccessControl();
        $specialty = Specialization::create(['name' => 'Test Specialty']);
        foreach (['staff' => $this->makeNurse(), 'doctors' => $this->makeDoctor()] as $prefix => $user) {
            $role = Role::findByName($prefix === 'staff' ? 'staff' : 'doctor');
            $role->revokePermissionTo('manage_specialties');
            $this->actingAs($user->fresh());
            $this->get(route($prefix.'.specializations.index'))->assertForbidden();
            $this->getJson(route($prefix.'.specializations.edit', $specialty))->assertForbidden();
            $this->postJson(route($prefix.'.specializations.store'), ['name' => 'Blocked'])->assertForbidden();
            $this->putJson(route($prefix.'.specializations.update', $specialty), ['name' => 'Blocked'])->assertForbidden();
            $this->deleteJson(route($prefix.'.specializations.destroy', $specialty))->assertForbidden();
        }
        $this->assertSame('Test Specialty', $specialty->fresh()->name);
    }
}
