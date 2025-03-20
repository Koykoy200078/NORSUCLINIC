<?php

namespace App\Livewire;

use App\Models\RequestDocuments;
use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class RequestDocumentTable extends Component
{
    public $name;
    public $age;
    public $gender;
    public $status;
    public $date_of_birth;
    public $address;
    public $religion;
    public $patient_contact;
    public $campus;
    public $college;
    public $course_year;
    public $informant;
    public $emergency_contact;
    public $complaints;
    public $covid_vaccination;
    public $comorbidities;
    public $allergies;
    public $admissions_surgeries;
    public $maintenance;
    public $pregnancy_status;
    public $lmp_aog;
    public $vital_signs_bp;
    public $vital_signs_pr;
    public $vital_signs_temp;
    public $vital_signs_rr;
    public $vital_signs_o2_sat;
    public $vital_signs_weight;
    public $pertinent_exam;
    public $assessment;
    public $plan;

    public $requestDocuments;
    public $selectedDocument;

    public function mount()
    {
        $this->requestDocuments = RequestDocuments::all();
    }

    public function selectDocument($id)
    {
        $this->selectedDocument = RequestDocuments::find($id);
    }

    public function render()
    {
        return view('livewire.request-document-table');
    }
}
