<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class RequestDocuments extends Model
{
    use HasFactory, InteractsWithMedia, HasRoles;

    protected $table = 'request_documents';

    public $timestamps = false;

    protected $fillable = [
        'document_type',
        'user_id',
        'name',
        'age',
        'gender',
        'status',
        'date_of_birth',
        'address',
        'religion',
        'patient_contact',
        'campus',
        'college',
        'course',
        'year_level',
        'informant',
        'emergency_contact',
        'requested_at',
        'complaints',
        'covid_vaccination',
        'comorbidities',
        'allergies',
        'admissions_surgeries',
        'maintenance',
        'pregnancy_status',
        'lmp_aog',
        'vital_signs_bp',
        'vital_signs_pr',
        'vital_signs_temp',
        'vital_signs_rr',
        'vital_signs_o2_sat',
        'vital_signs_height',
        'vital_signs_weight',
        'pertinent_exam',
        'assessment',
        'plan',
        'consult_mode',
        'nursing_intervention',
        'nursing_incharged_id',
        'examined_on',
        'complaints_diagnosis',
        'medical_cert_remarks',
        'doc_lic_no',
        'doc_prt_no',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'examined_on' => 'datetime',
        'date_of_birth' => 'date',
    ];
}
