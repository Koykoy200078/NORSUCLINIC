<?php

namespace Tests\Feature\Regression;

use App\Models\ConsultationMedicine;
use App\Models\DocumentIssuance;
use App\Models\MedicineTransaction;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

class DocumentIssuanceBoundaryTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        Storage::fake('consultation_images');
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'document_type'=>'consultation_form', 'name'=>'Boundary Patient Example', 'date_of_birth'=>'2003-05-01',
            'gender'=>'Male', 'requested_at'=>now()->toDateString(), 'consult_mode'=>'physical', 'complaints'=>'Test complaint',
        ], $extra);
    }

    public function test_failed_stock_deduction_rolls_back_walk_in_patient_document_and_photos(): void
    {
        $doctor = $this->makeDoctor();
        $medicine = $this->makeMedicine();
        $this->stockIn($medicine, 1, now()->addYear()->toDateString());
        $before = [User::count(), Patient::count(), DocumentIssuance::count(), ConsultationMedicine::count(), MedicineTransaction::count()];

        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->payload([
            'consultation_images'=>[UploadedFile::fake()->image('boundary.png',20,20)],
            'medicines'=>['plan'=>[['medicine_id'=>$medicine->id,'dosage'=>'500mg','quantity'=>2]]],
        ]))->assertRedirect()->assertSessionHas('error');

        $this->assertSame($before, [User::count(), Patient::count(), DocumentIssuance::count(), ConsultationMedicine::count(), MedicineTransaction::count()]);
        $this->assertSame([], Storage::disk('consultation_images')->allFiles());
        $this->assertSame(1, $medicine->fresh()->available_quantity);
    }

    public function test_failed_edit_keeps_the_old_photo_and_stock_and_discards_the_new_photo(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient();
        $medicine = $this->makeMedicine();
        $this->stockIn($medicine, 1, now()->addYear()->toDateString());
        $base = $this->payload(['user_id'=>$patient->user_id]);
        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), array_merge($base,[
            'consultation_images'=>[UploadedFile::fake()->image('before.png',20,20)],
            'medicines'=>['plan'=>[['medicine_id'=>$medicine->id,'dosage'=>'500mg','quantity'=>1]]],
        ]))->assertSessionMissing('error');
        $document = DocumentIssuance::firstOrFail();
        $row = $document->consultationMedicines()->firstOrFail();
        $images = $document->consultationImageList();
        $ledger = MedicineTransaction::count();

        $this->put(route('doctors.document-issuances.update',$document),array_merge($base,[
            'removed_images'=>'[0]', 'consultation_images'=>[UploadedFile::fake()->image('after.png',20,20)],
            'medicines'=>['plan'=>[['id'=>$row->id,'medicine_id'=>$medicine->id,'dosage'=>'500mg','quantity'=>2]]],
        ]))->assertRedirect()->assertSessionHas('error');

        $this->assertSame($images,$document->fresh()->consultationImageList());
        $this->assertSame([$images[0]['path']],Storage::disk('consultation_images')->allFiles());
        $this->assertSame(1,$row->fresh()->quantity);
        $this->assertSame(0,$medicine->fresh()->available_quantity);
        $this->assertSame($ledger,MedicineTransaction::count());
    }

    public function test_success_redirects_keep_patient_history_panel_flash_and_module_override(): void
    {
        foreach ([$this->makeAdmin(),$this->makeNurse(),$this->makeDoctor()] as $actor) {
            $patient = $this->makePatient();
            $this->actingAs($actor);
            $payload = $this->payload(['user_id'=>$patient->user_id,'redirect_to_patient'=>1]);
            $this->post(getRouteByRole('document-issuances.store'),$payload)
                ->assertRedirect(getRouteByRole('patients.showMyHistory',[$patient]))
                ->assertSessionHas('success','Consultation form created successfully.');
            $document = DocumentIssuance::where('user_id',$patient->user_id)->firstOrFail();
            $this->put(getRouteByRole('document-issuances.update',[$document]),array_merge($payload,['redirect_patient_id'=>$patient->id]))
                ->assertRedirect(getRouteByRole('patients.showMyHistory',[$patient]))
                ->assertSessionHas('success','Request document updated successfully.');
            $this->delete(getRouteByRole('document-issuances.destroy',[$document]),['redirect_module'=>'certificate'])
                ->assertRedirect(getRouteByRole('document-issuances.index',['module'=>'certificate']))
                ->assertSessionHas('success');
        }
    }
}
