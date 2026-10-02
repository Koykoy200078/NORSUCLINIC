<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\DocumentIssuance
 *
 * @property int $id
 * @property int $document_creator_id
 * @property int $user_id
 * @property string $name
 * @property int $age
 * @property string $gender
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $date_of_birth
 * @property string $address
 * @property string|null $religion
 * @property string|null $patient_contact
 * @property string|null $campus
 * @property string|null $college
 * @property string|null $course
 * @property string|null $year_level
 * @property string|null $informant
 * @property string|null $emergency_contact
 * @property \Illuminate\Support\Carbon|null $requested_at
 * @property string|null $complaints
 * @property string|null $covid_vaccination
 * @property string|null $comorbidities
 * @property string|null $allergies
 * @property string|null $admissions_surgeries
 * @property string|null $maintenance
 * @property string|null $pregnancy_status
 * @property string|null $lmp_aog
 * @property string|null $vital_signs_bp
 * @property string|null $vital_signs_pr
 * @property string|null $vital_signs_temp
 * @property string|null $vital_signs_rr
 * @property string|null $vital_signs_o2_sat
 * @property string|null $vital_signs_height
 * @property string|null $vital_signs_weight
 * @property string|null $pertinent_exam
 * @property string|null $assessment
 * @property string|null $plan
 * @property string $document_type
 * @property string|null $consult_mode
 * @property string|null $nursing_intervention
 * @property string|null $nursing_incharged_id
 * @property \Illuminate\Support\Carbon|null $examined_on
 * @property string|null $request_of
 * @property string|null $complaints_diagnosis
 * @property string|null $medical_cert_remarks
 * @property string|null $doc_lic_no
 * @property string|null $doc_prt_no
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance query()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereAdmissionsSurgeries($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereAllergies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereAssessment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereCampus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereCollege($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereComorbidities($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereComplaints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereComplaintsDiagnosis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereConsultMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereCourse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereCovidVaccination($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereDocLicNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereDocPrtNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereDocumentCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereEmergencyContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereExaminedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereInformant($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereLmpAog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereMaintenance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereMedicalCertRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereNursingInchargedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereNursingIntervention($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance wherePatientContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance wherePertinentExam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance wherePlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance wherePregnancyStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereReligion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereRequestOf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereVitalSignsBp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereVitalSignsHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereVitalSignsO2Sat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereVitalSignsPr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereVitalSignsRr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereVitalSignsTemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereVitalSignsWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereYearLevel($value)
 * @mixin \Eloquent
 */
class DocumentIssuance extends Model implements HasMedia
{
    // Consultations, medical certificates and excuse slips are clinical records: "deleting" one takes it off every
    // screen and report but keeps the row (deleted_at) and its photos, so it can still be recovered. R3-M6.
    use HasFactory, InteractsWithMedia, HasRoles, SoftDeletes;

    protected $table = 'document_issuances';

    public $timestamps = true;

    protected $fillable = [
        'document_type',
        'document_creator_id',
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
        'campus_id',
        'college_id',
        'course_id',
        'year_level_id',
        'department_id',
        'office_id',
        'patient_type_id',
        'informant',
        'emergency_contact',
        'requested_at',
        'complaints',
        'note',
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
        'request_of',
        'complaints_diagnosis',
        'medical_cert_remarks',
        'doc_lic_no',
        'doc_prt_no',
        'consultation_images',
        'subjects',
    ];

    protected $casts = [
        'requested_at' => 'date',
        // 'examined_on' => 'date', // Removed: Now supports date ranges and multiple dates as string
        'date_of_birth' => 'date',
        'consultation_images' => 'array',
        'subjects' => 'array',
    ];

    /**
     * Boot the model and set up event listeners for cascade delete
     */
    protected static function boot()
    {
        parent::boot();

        // When a request document is being deleted, delete all associated image files
        // (private consultation_images disk for new uploads, public/uploads for legacy ones).
        static::deleting(function ($requestDocument) {
            // A normal delete only hides the record: its photos stay with it. They are removed only if the row is
            // ever removed for good (forceDelete).
            if (! $requestDocument->isForceDeleting()) {
                return;
            }

            // The files go only once the surrounding transaction has committed: when the delete (or the
            // stock return that runs with it) is rolled back, the record must not lose its photos. R3-M6.
            $images = $requestDocument->consultationImageList();

            DB::afterCommit(function () use ($requestDocument, $images) {
                foreach ($images as $imageData) {
                    $requestDocument->deleteConsultationImageFile($imageData);
                }

                // Also clear media library collection (if any media was added there)
                $requestDocument->clearMediaCollection('consultation_images');
            });
        });
    }

    /**
     * A consultation can be deleted only by the clinic admin or by the person who recorded it.
     * Certificates and excuse slips are governed by the module access of the route, as before.
     */
    public function canBeDeletedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->document_type !== 'consultation_form') {
            return true;
        }

        return $user->hasRole('clinic_admin') || (int) $this->document_creator_id === (int) $user->id;
    }

    /**
     * Register media collections for consultation images
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('consultation_images')
            ->useDisk('public');
    }

    /**
     * Get custom path for media storage using patient name and timestamp
     */
    public function getMediaPath(string $conversion = ''): string
    {
        // Create folder structure: consultation_images/PatientName/YYYY-MM-DD_HH-MM-SS/
        $patientName = str_replace(' ', '_', $this->name);
        $timestamp = now()->format('Y-m-d_H-i-s');

        return "consultation_images/{$patientName}/{$timestamp}";
    }

    /**
     * Get the user who created this document.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'document_creator_id');
    }

    /**
     * The nurse in charge of the visit (chosen on the consultation form).
     */
    public function nursingInCharge()
    {
        return $this->belongsTo(User::class, 'nursing_incharged_id');
    }

    /**
     * Get the medicines used in this consultation.
     */
    public function consultationMedicines()
    {
        return $this->hasMany(ConsultationMedicine::class, 'request_document_id');
    }

    /**
     * The illnesses picked from the clinic list for this consultation (pivot: other_text for an "Others" line).
     */
    public function illnesses()
    {
        return $this->belongsToMany(Illness::class, 'consultation_illnesses', 'document_issuance_id', 'illness_id')
            ->withPivot('other_text')
            ->orderBy('illnesses.illness_system_id')
            ->orderBy('illnesses.sort_order');
    }

    /**
     * The services ticked for this consultation.
     */
    public function services()
    {
        return $this->belongsToMany(ServiceType::class, 'consultation_services', 'document_issuance_id', 'service_type_id')
            ->orderBy('service_types.category')
            ->orderBy('service_types.sort_order');
    }

    /**
     * The picked illnesses as readable lines: "Cough/colds", and for an "Others" line "Skeletal System: Lump on right leg".
     *
     * @return array<int, string>
     */
    public function illnessLabels(): array
    {
        $this->loadMissing('illnesses.system');

        return $this->illnesses->map(function (Illness $illness) {
            if (! $illness->is_other) {
                return $illness->name;
            }

            return ($illness->system?->name ?? 'Others') . ': ' . (filled($illness->pivot->other_text) ? $illness->pivot->other_text : 'Others');
        })->values()->all();
    }

    /**
     * The ticked services as readable lines.
     *
     * @return array<int, string>
     */
    public function serviceLabels(): array
    {
        $this->loadMissing('services');

        return $this->services->pluck('name')->values()->all();
    }

    /**
     * Consultation images as a plain list of entries
     * (['disk' => ?string, 'path' => string, 'name' => string, 'size' => int, 'uploaded_at' => string]).
     *
     * Older rows were double JSON-encoded (json_encode() on top of the "array" cast), so the
     * cast returns a string for them; decode that transparently.
     */
    public function consultationImageList(): array
    {
        $images = $this->consultation_images;

        if (is_string($images)) {
            $images = json_decode($images, true);
        }

        if (! is_array($images)) {
            return [];
        }

        return array_values(array_filter($images, function ($image) {
            return is_array($image) && isset($image['path']) && is_string($image['path']) && $image['path'] !== '';
        }));
    }

    /**
     * Absolute path of a stored consultation image, or null when it is missing or its
     * recorded path escapes the storage folder.
     *
     * New uploads live on the private "consultation_images" disk; legacy entries (no "disk"
     * key) are still under public/uploads/consultation_images until they are migrated with
     * `php artisan consultation-images:secure`.
     */
    public function consultationImageAbsolutePath(array $image): ?string
    {
        $relativePath = (string) ($image['path'] ?? '');
        if ($relativePath === '' || str_contains($relativePath, "\0")) {
            return null;
        }

        if (($image['disk'] ?? null) === 'consultation_images') {
            $baseDirectory = Storage::disk('consultation_images')->path('');
            $candidate = Storage::disk('consultation_images')->path($relativePath);
        } else {
            $baseDirectory = public_path('uploads/consultation_images');
            $candidate = public_path('uploads/' . $relativePath);
        }

        $realBase = realpath($baseDirectory);
        $realCandidate = realpath($candidate);

        if ($realBase === false || $realCandidate === false || ! is_file($realCandidate)) {
            return null;
        }

        if (! str_starts_with($realCandidate, rtrim($realBase, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $realCandidate;
    }

    /**
     * Delete the physical file behind a consultation image entry (if it still exists).
     */
    public function deleteConsultationImageFile(array $image): void
    {
        $absolutePath = $this->consultationImageAbsolutePath($image);

        if ($absolutePath !== null) {
            @unlink($absolutePath);
        }
    }
}
