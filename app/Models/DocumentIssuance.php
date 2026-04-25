<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    use HasFactory, InteractsWithMedia, HasRoles;

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

        // When a request document is being deleted, delete all associated media files
        static::deleting(function ($requestDocument) {
            // Delete all uploaded images from storage
            // This will delete files from: storage/app/public/consultation_images/[PatientName]/[Timestamp]/

            // Get consultation images and ensure it's an array
            $consultationImages = $requestDocument->consultation_images;

            // If it's a string (JSON), decode it
            if (is_string($consultationImages)) {
                $consultationImages = json_decode($consultationImages, true);
            }

            // If it's null or empty, skip
            if (!$consultationImages || !is_array($consultationImages)) {
                return;
            }

            // Now safely iterate through images
            foreach ($consultationImages as $imageData) {
                // Delete the physical file
                if (isset($imageData['path'])) {
                    Storage::disk('public')->delete($imageData['path']);
                }
            }

            // Try to delete the empty folders (optional)
            // Extract folder path from first image
            if (count($consultationImages) > 0) {
                $firstImagePath = $consultationImages[0]['path'] ?? null;
                if ($firstImagePath) {
                    $folderPath = dirname($firstImagePath);
                    // Delete folder if empty
                    $files = Storage::disk('public')->files($folderPath);
                    if (empty($files)) {
                        Storage::disk('public')->deleteDirectory($folderPath);

                        // Try to delete parent folder (PatientName) if empty
                        $parentFolder = dirname($folderPath);
                        $parentFiles = Storage::disk('public')->allFiles($parentFolder);
                        if (empty($parentFiles)) {
                            Storage::disk('public')->deleteDirectory($parentFolder);
                        }
                    }
                }
            }

            // Also clear media library collection (if any media was added there)
            $requestDocument->clearMediaCollection('consultation_images');
        });
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
     * Get the medicines used in this consultation.
     */
    public function consultationMedicines()
    {
        return $this->hasMany(ConsultationMedicine::class, 'request_document_id');
    }
}
