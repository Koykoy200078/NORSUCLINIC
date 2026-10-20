<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\ActivityLog
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $user_type
 * @property string|null $user_name
 * @property string $action
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $date
 * @property string|null $patient_name
 * @property int|null $patient_age
 * @property string|null $patient_gender
 * @property string|null $college
 * @property string|null $address
 * @property string|null $contact_number
 * @property string|null $complaints
 * @property string|null $diagnosis
 * @property string|null $informant
 * @property string|null $consult_mode
 * @property string|null $course_section
 * @property array|null $properties
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $formatted_action
 * @property-read string $formatted_user_type
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $subject
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog byAction($action)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog bySubjectType($subjectType)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog byUserType($userType)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog dateRange($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereCollege($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereComplaints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereConsultMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereCourseSection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereDiagnosis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereInformant($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog wherePatientAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog wherePatientGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog wherePatientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereUserName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityLog whereUserType($value)
 */
	class ActivityLog extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Address
 *
 * @property int $id
 * @property int|null $owner_id
 * @property string|null $owner_type
 * @property string|null $address1
 * @property string|null $address2
 * @property int|null $country_id
 * @property int|null $state_id
 * @property int|null $city_id
 * @property int|null $barangay_id
 * @property string|null $postal_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Barangay|null $barangay
 * @property-read \App\Models\City|null $city
 * @property-read string $full_address
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $owner
 * @property-read \App\Models\State|null $state
 * @method static \Illuminate\Database\Eloquent\Builder|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address query()
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereBarangayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereOwnerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereUpdatedAt($value)
 */
	class Address extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Barangay
 *
 * @property int $id
 * @property string $name
 * @property int $city_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\City $city
 * @method static \Illuminate\Database\Eloquent\Builder|Barangay newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Barangay newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Barangay query()
 * @method static \Illuminate\Database\Eloquent\Builder|Barangay whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Barangay whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Barangay whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Barangay whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Barangay whereUpdatedAt($value)
 */
	class Barangay extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Campus
 *
 * @property int $id
 * @property string $campus_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Campus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Campus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Campus query()
 * @method static \Illuminate\Database\Eloquent\Builder|Campus whereCampusName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campus whereUpdatedAt($value)
 */
	class Campus extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Category
 *
 * @property int $id
 * @property string $name
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Medicine> $medicines
 * @property-read int|null $medicines_count
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUpdatedAt($value)
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\City
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $state_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Barangay> $barangays
 * @property-read int|null $barangays_count
 * @property-read \App\Models\State $state
 * @method static \Database\Factories\CityFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|City newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|City newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|City query()
 * @method static \Illuminate\Database\Eloquent\Builder|City whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereUpdatedAt($value)
 */
	class City extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ClinicStation
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StaffProfile> $staffProfiles
 * @property-read int|null $staff_profiles_count
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicStation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicStation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicStation query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicStation whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicStation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicStation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicStation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicStation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicStation whereUpdatedAt($value)
 */
	class ClinicStation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\College
 *
 * @property int $id
 * @property string $college_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $abbreviation
 * @method static \Illuminate\Database\Eloquent\Builder|College newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|College newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|College query()
 * @method static \Illuminate\Database\Eloquent\Builder|College whereCollegeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|College whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|College whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|College whereUpdatedAt($value)
 */
	class College extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ConsultationMedicine
 *
 * @property int $id
 * @property int $request_document_id
 * @property int $medicine_id
 * @property string|null $dosage
 * @property int $quantity
 * @property string|null $used_for
 * @property string|null $dosage_instructions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Medicine $medicine
 * @property-read \App\Models\RequestDocuments $requestDocument
 * @method static \Illuminate\Database\Eloquent\Builder|ConsultationMedicine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConsultationMedicine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConsultationMedicine query()
 * @method static \Illuminate\Database\Eloquent\Builder|ConsultationMedicine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsultationMedicine whereDosage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsultationMedicine whereDosageInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsultationMedicine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsultationMedicine whereMedicineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsultationMedicine whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsultationMedicine whereRequestDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsultationMedicine whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsultationMedicine whereUsedFor($value)
 */
	class ConsultationMedicine extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Country
 *
 * @property int $id
 * @property string $name
 * @property string|null $short_code
 * @property string|null $phone_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\CountryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Country newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Country newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Country query()
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country wherePhoneCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereShortCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereUpdatedAt($value)
 */
	class Country extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Course
 *
 * @property int $id
 * @property string $course_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Course newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Course newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Course query()
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereCourseName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereUpdatedAt($value)
 */
	class Course extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Department
 *
 * @property int $id
 * @property string $department_name
 * @property int|null $college_id
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\College|null $college
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereCollegeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereDepartmentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereUpdatedAt($value)
 */
	class Department extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Diagnose
 *
 * @property int $id
 * @property string $diagnoses
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnose newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnose newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnose query()
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnose whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnose whereDiagnoses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnose whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnose whereUpdatedAt($value)
 */
	class Diagnose extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DispenseHistoryEntry
 *
 * @property-read \App\Models\Patient|null $patient
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseHistoryEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseHistoryEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseHistoryEntry query()
 */
	class DispenseHistoryEntry extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DispenseRecord
 *
 * @property int $id
 * @property string $history_number
 * @property int $patient_id
 * @property int|null $doctor_id
 * @property string $model_type
 * @property string|null $model_id
 * @property string|null $note
 * @property string $bill_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DispenseRecordItem> $dispenseItems
 * @property-read int|null $dispense_items_count
 * @property-read \App\Models\Doctor|null $doctor
 * @property-read \App\Models\Patient|null $patient
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DispenseRecordItem> $saleMedicine
 * @property-read int|null $sale_medicine_count
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecord whereBillDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecord whereDoctorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecord whereHistoryNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecord whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecord whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecord whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecord wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecord whereUpdatedAt($value)
 */
	class DispenseRecord extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DispenseRecordItem
 *
 * @property int $id
 * @property int $medicine_bill_id
 * @property int $medicine_id
 * @property string|null $dosage
 * @property int $sale_quantity
 * @property string|null $expiry_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DispenseRecord|null $dispenseRecord
 * @property int|null $dispense_id
 * @property string|null $expires_at
 * @property int|null $quantity
 * @property-read \App\Models\Medicine|null $medicine
 * @property-read \App\Models\DispenseRecord|null $medicineBill
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecordItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecordItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecordItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecordItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecordItem whereDosage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecordItem whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecordItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecordItem whereMedicineBillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecordItem whereMedicineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecordItem whereSaleQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispenseRecordItem whereUpdatedAt($value)
 */
	class DispenseRecordItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Doctor
 *
 * @property int $id
 * @property int $user_id
 * @property float|null $experience
 * @property string|null $prc_license_number
 * @property string|null $ptr_number
 * @property string|null $s2_license_number
 * @property string|null $consultation_hours
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Address|null $address
 * @property-read \App\Models\User $doctorUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Specialization> $specializations
 * @property-read int|null $specializations_count
 * @property-read \App\Models\User $testUser
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor query()
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereConsultationHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereExperience($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor wherePrcLicenseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor wherePtrNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereS2LicenseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereUserId($value)
 */
	class Doctor extends \Eloquent {}
}

namespace App\Models{
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
 * @property int|null $patient_type_id
 * @property int|null $office_id
 * @property int|null $department_id
 * @property int|null $year_level_id
 * @property int|null $course_id
 * @property int|null $college_id
 * @property int|null $campus_id
 * @property string|null $informant
 * @property string|null $emergency_contact
 * @property \Illuminate\Support\Carbon|null $requested_at
 * @property string|null $complaints
 * @property string|null $note
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
 * @property string|null $examined_on
 * @property string|null $request_of
 * @property string|null $complaints_diagnosis
 * @property string|null $medical_cert_remarks
 * @property array|null $subjects
 * @property string|null $doc_lic_no
 * @property string|null $doc_prt_no
 * @property array|null $consultation_images
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ConsultationMedicine> $consultationMedicines
 * @property-read int|null $consultation_medicines_count
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Illness> $illnesses
 * @property-read int|null $illnesses_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\User|null $nursingInCharge
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServiceType> $services
 * @property-read int|null $services_count
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance incompleteConsultations()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance query()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereAdmissionsSurgeries($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereAllergies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereAssessment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereCampus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereCampusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereCollege($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereCollegeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereComorbidities($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereComplaints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereComplaintsDiagnosis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereConsultMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereConsultationImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereCourse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereCovidVaccination($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereDepartmentId($value)
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
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereNursingInchargedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereNursingIntervention($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance wherePatientContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance wherePatientTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance wherePertinentExam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance wherePlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance wherePregnancyStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereReligion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereRequestOf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereSubjects($value)
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
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance whereYearLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance withoutTrashed()
 */
	class DocumentIssuance extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * App\Models\Generic
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Medicine> $medicines
 * @property-read int|null $medicines_count
 * @method static \Illuminate\Database\Eloquent\Builder|Generic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Generic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Generic query()
 * @method static \Illuminate\Database\Eloquent\Builder|Generic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Generic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Generic whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Generic whereUpdatedAt($value)
 */
	class Generic extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Guest
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $contact
 * @property string|null $email
 * @property string|null $purpose
 * @property string|null $address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Guest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Guest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Guest query()
 * @method static \Illuminate\Database\Eloquent\Builder|Guest whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guest whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guest whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guest whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guest whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guest wherePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Guest whereUpdatedAt($value)
 */
	class Guest extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Illness
 *
 * @property int $id
 * @property int $illness_system_id
 * @property string|null $group_label
 * @property string $name
 * @property bool $is_other
 * @property int $sort_order
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\IllnessSystem $system
 * @method static \Illuminate\Database\Eloquent\Builder|Illness active()
 * @method static \Illuminate\Database\Eloquent\Builder|Illness newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Illness newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Illness query()
 * @method static \Illuminate\Database\Eloquent\Builder|Illness whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Illness whereGroupLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Illness whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Illness whereIllnessSystemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Illness whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Illness whereIsOther($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Illness whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Illness whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Illness whereUpdatedAt($value)
 */
	class Illness extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\IllnessSystem
 *
 * @property int $id
 * @property string $name
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Illness> $illnesses
 * @property-read int|null $illnesses_count
 * @method static \Illuminate\Database\Eloquent\Builder|IllnessSystem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IllnessSystem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IllnessSystem ordered()
 * @method static \Illuminate\Database\Eloquent\Builder|IllnessSystem query()
 * @method static \Illuminate\Database\Eloquent\Builder|IllnessSystem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IllnessSystem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IllnessSystem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IllnessSystem whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IllnessSystem whereUpdatedAt($value)
 */
	class IllnessSystem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InsuranceProvider
 *
 * @property int $id
 * @property string $name
 * @property string|null $contact_no
 * @property string|null $email
 * @property string|null $website
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Patient> $patients
 * @property-read int|null $patients_count
 * @method static \Illuminate\Database\Eloquent\Builder|InsuranceProvider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InsuranceProvider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InsuranceProvider query()
 * @method static \Illuminate\Database\Eloquent\Builder|InsuranceProvider whereContactNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InsuranceProvider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InsuranceProvider whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InsuranceProvider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InsuranceProvider whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InsuranceProvider whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InsuranceProvider whereWebsite($value)
 */
	class InsuranceProvider extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LabRequest
 *
 * @property int $id
 * @property string $request_number
 * @property int $document_creator_id
 * @property int $patient_user_id
 * @property string $patient_name
 * @property int|null $patient_age
 * @property string|null $patient_gender
 * @property \Illuminate\Support\Carbon|null $patient_dob
 * @property string|null $patient_contact
 * @property string|null $address
 * @property string|null $campus
 * @property string|null $college
 * @property string|null $course
 * @property string|null $year_level
 * @property string|null $department
 * @property string|null $office
 * @property string|null $status_affiliation
 * @property \Illuminate\Support\Carbon $requested_at
 * @property string|null $clinical_indication
 * @property string|null $remarks
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $collected_at
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $referred_at
 * @property \Illuminate\Support\Carbon|null $rejected_at
 * @property string|null $status_note
 * @property string|null $requesting_physician
 * @property string|null $physician_license_no
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LabRequestItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\User|null $patient
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereCampus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereClinicalIndication($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereCollectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereCollege($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereCourse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereDocumentCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereOffice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest wherePatientAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest wherePatientContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest wherePatientDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest wherePatientGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest wherePatientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest wherePatientUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest wherePhysicianLicenseNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereReferredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereRejectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereRequestNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereRequestingPhysician($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereStatusAffiliation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereStatusNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest whereYearLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequest withoutTrashed()
 */
	class LabRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LabRequestItem
 *
 * @property int $id
 * @property int $lab_request_id
 * @property int|null $lab_test_id
 * @property string $test_name
 * @property string|null $test_category
 * @property string|null $unit
 * @property string|null $normal_range
 * @property string|null $result_value
 * @property string $result_status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\LabRequest $labRequest
 * @property-read \App\Models\LabTest|null $labTest
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequestItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequestItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequestItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequestItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequestItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequestItem whereLabRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequestItem whereLabTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequestItem whereNormalRange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequestItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequestItem whereResultStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequestItem whereResultValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequestItem whereTestCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequestItem whereTestName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequestItem whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabRequestItem whereUpdatedAt($value)
 */
	class LabRequestItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LabTest
 *
 * @property int $id
 * @property string $name
 * @property string $category
 * @property string|null $unit
 * @property string|null $normal_range
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LabRequestItem> $requestItems
 * @property-read int|null $request_items_count
 * @method static \Illuminate\Database\Eloquent\Builder|LabTest active()
 * @method static \Illuminate\Database\Eloquent\Builder|LabTest byCategory(string $category)
 * @method static \Illuminate\Database\Eloquent\Builder|LabTest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LabTest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LabTest query()
 * @method static \Illuminate\Database\Eloquent\Builder|LabTest whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabTest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabTest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabTest whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabTest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabTest whereNormalRange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabTest whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LabTest whereUpdatedAt($value)
 */
	class LabTest extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Medicine
 *
 * @property int $id
 * @property int|null $category_id
 * @property int|null $generic_id
 * @property string|null $generic_name
 * @property string|null $brand_name
 * @property \App\Models\Category|null $category
 * @property string|null $category_name
 * @property string|null $dosage
 * @property string|null $uom
 * @property string|null $sku
 * @property int $reorder_level
 * @property int $baseline_quantity
 * @property string $name
 * @property int $quantity
 * @property int $available_quantity
 * @property int|null $minimum_stock_alert Minimum quantity threshold for stock alert
 * @property string|null $stock_alert_percentage Percentage threshold for stock alert (e.g., 20 for 20%)
 * @property string|null $salt_composition
 * @property string|null $description
 * @property string|null $side_effects
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MedicineBatch> $batches
 * @property-read int|null $batches_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ConsultationMedicine> $consultationItems
 * @property-read int|null $consultation_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DispenseRecordItem> $dispenseItems
 * @property-read int|null $dispense_items_count
 * @property-read \App\Models\Generic|null $generic
 * @property-read string $category_label
 * @property-read string $display_name
 * @property-read mixed $earliest_expiry_date
 * @property-read string $generic_label
 * @property-read \App\Models\Category|null $medicineCategory
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PrescriptionMedicine> $prescriptionItems
 * @property-read int|null $prescription_items_count
 * @property-read \App\Models\PrescriptionMedicine|null $prescriptionMedicines
 * @property-read \App\Models\PurchasedMedicine|null $purchasedMedicine
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchasedMedicine> $stockInItems
 * @property-read int|null $stock_in_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MedicineTransaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read \App\Models\UsedMedicine|null $usedMedicines
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine lowStock()
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine query()
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereAvailableQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereBaselineQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereBrandName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereDosage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereGenericId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereGenericName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereMinimumStockAlert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereReorderLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereSaltComposition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereSideEffects($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereStockAlertPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereUom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereUpdatedAt($value)
 */
	class Medicine extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MedicineAvailability
 *
 * @property int $id
 * @property string $availability_no
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchasedMedicine> $purchasedMedcines
 * @property-read int|null $purchased_medcines_count
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineAvailability newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineAvailability newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineAvailability query()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineAvailability whereAvailabilityNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineAvailability whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineAvailability whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineAvailability whereUpdatedAt($value)
 */
	class MedicineAvailability extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MedicineBatch
 *
 * @property int $id
 * @property int $medicine_id
 * @property string $batch_number
 * @property string|null $dosage
 * @property int $quantity
 * @property \Illuminate\Support\Carbon|null $manufacturing_date
 * @property \Illuminate\Support\Carbon $expiration_date
 * @property string|null $supplier_name
 * @property string|null $unit_cost
 * @property \Illuminate\Support\Carbon $date_received
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Medicine $medicine
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MedicineTransaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBatch whereBatchNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBatch whereDateReceived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBatch whereDosage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBatch whereExpirationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBatch whereManufacturingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBatch whereMedicineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBatch whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBatch whereSupplierName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBatch whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBatch whereUpdatedAt($value)
 */
	class MedicineBatch extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MedicineTransaction
 *
 * @property int $id
 * @property int $batch_id
 * @property int|null $user_id
 * @property string $transaction_type
 * @property int $quantity
 * @property int $balance_after
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property string|null $remarks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MedicineBatch $batch
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $reference
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineTransaction whereBalanceAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineTransaction whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineTransaction whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineTransaction whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineTransaction whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineTransaction whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineTransaction whereTransactionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineTransaction whereUserId($value)
 */
	class MedicineTransaction extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Notification
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $type
 * @property int|null $read_at
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUserId($value)
 */
	class Notification extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Office
 *
 * @property int $id
 * @property string $office_name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Office newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Office newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Office query()
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereOfficeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereUpdatedAt($value)
 */
	class Office extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Patient
 *
 * @property int $id
 * @property string $patient_unique_id
 * @property int $user_id
 * @property int|null $patient_type_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $allergies
 * @property string|null $comorbidities
 * @property string|null $admissions_surgeries
 * @property string|null $maintenance
 * @property string|null $covid_vaccination
 * @property string|null $immunization_record
 * @property int|null $insurance_provider_id
 * @property string|null $insurance_policy_number
 * @property string|null $primary_care_physician_name
 * @property string|null $primary_care_physician_contact
 * @property string|null $primary_care_physician_email
 * @property \Illuminate\Support\Carbon|null $archived_at
 * @property-read \App\Models\Address|null $address
 * @property-read \App\Models\PatientQueue|null $currentQueue
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DispenseRecord> $dispenseRecords
 * @property-read int|null $dispense_records_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DocumentIssuance> $documentIssuances
 * @property-read int|null $document_issuances_count
 * @property-read string $profile
 * @property-read \App\Models\InsuranceProvider|null $insuranceProvider
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DispenseRecord> $medicineBills
 * @property-read int|null $medicine_bills_count
 * @property-read \App\Models\PatientType|null $patientType
 * @property-read \App\Models\User $patientUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Prescription> $prescriptions
 * @property-read int|null $prescriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PatientQueue> $queueEntries
 * @property-read int|null $queue_entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DocumentIssuance> $requestDocuments
 * @property-read int|null $request_documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\PatientFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Patient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Patient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Patient onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Patient permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient query()
 * @method static \Illuminate\Database\Eloquent\Builder|Patient role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereAdmissionsSurgeries($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereAllergies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereArchivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereComorbidities($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereCovidVaccination($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereImmunizationRecord($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereInsurancePolicyNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereInsuranceProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereMaintenance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient wherePatientTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient wherePatientUniqueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient wherePrimaryCarePhysicianContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient wherePrimaryCarePhysicianEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient wherePrimaryCarePhysicianName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Patient withoutTrashed()
 */
	class Patient extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * App\Models\PatientQueue
 *
 * @property int $id
 * @property int $patient_id
 * @property int $added_by
 * @property string|null $room_number
 * @property bool $is_priority
 * @property string $status
 * @property string|null $notes
 * @property int|null $latest_consultation_id
 * @property int $has_consultation_attachment
 * @property \Illuminate\Support\Carbon|null $called_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $scheduled_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $addedBy
 * @property-read bool $attached_form_is_new
 * @property-read mixed $queue_number
 * @property-read \App\Models\RequestDocuments|null $latestConsultation
 * @property-read \App\Models\Patient $patient
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue openToday()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue orderByQueue()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue priority()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue query()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue waiting()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereCalledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereHasConsultationAttachment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereIsPriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereLatestConsultationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereRoomNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue withLivePatient()
 */
	class PatientQueue extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PatientType
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Patient> $patients
 * @property-read int|null $patients_count
 * @method static \Illuminate\Database\Eloquent\Builder|PatientType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientType query()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientType whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientType whereUpdatedAt($value)
 */
	class PatientType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Permission
 *
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereUpdatedAt($value)
 */
	class Permission extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Prescription
 *
 * @property int $id
 * @property int $patient_id
 * @property int|null $doctor_id
 * @property string|null $doctor_license_s2_number
 * @property \Illuminate\Support\Carbon|null $consultation_date
 * @property int|null $icd10_diagnosis_id
 * @property string|null $food_allergies
 * @property string|null $tendency_bleed
 * @property string|null $heart_disease
 * @property string|null $high_blood_pressure
 * @property string|null $diabetic
 * @property string|null $current_medication
 * @property string|null $female_pregnancy
 * @property string|null $breast_feeding
 * @property string|null $health_insurance
 * @property string|null $low_income
 * @property string|null $reference
 * @property bool $is_active
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $dispensed_at
 * @property int|null $dispensed_by
 * @property string|null $plus_rate
 * @property string|null $temperature
 * @property string|null $problem_description
 * @property string|null $test
 * @property string|null $advice
 * @property int|null $next_visit_days
 * @property string|null $weight_kg
 * @property string|null $pulse_rate
 * @property string|null $body_temperature
 * @property string|null $blood_pressure
 * @property string|null $height_cm
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Diagnose|null $diagnosis
 * @property-read \App\Models\User|null $dispensedBy
 * @property-read \App\Models\Doctor|null $doctor
 * @property-read \App\Models\Patient $patient
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription query()
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereAdvice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereBloodPressure($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereBodyTemperature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereBreastFeeding($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereConsultationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereCurrentMedication($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereDiabetic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereDispensedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereDispensedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereDoctorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereDoctorLicenseS2Number($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereFemalePregnancy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereFoodAllergies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereHealthInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereHeartDisease($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereHeightCm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereHighBloodPressure($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereIcd10DiagnosisId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereLowIncome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereNextVisitDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription wherePlusRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereProblemDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription wherePulseRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereTemperature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereTendencyBleed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereTest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereWeightKg($value)
 */
	class Prescription extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PrescriptionMedicine
 *
 * @property int $id
 * @property int $prescription_id
 * @property int $medicine
 * @property string|null $dosage
 * @property string|null $route_of_administration
 * @property int|null $frequency
 * @property int|null $duration_value
 * @property string|null $duration_unit
 * @property int|null $total_quantity
 * @property string|null $instructions
 * @property string|null $day
 * @property int $dose_interval
 * @property string|null $time
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Medicine $medicines
 * @property-read \App\Models\Prescription $prescription
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine query()
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine whereDosage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine whereDoseInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine whereDurationUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine whereDurationValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine whereFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine whereInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine whereMedicine($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine wherePrescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine whereRouteOfAdministration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine whereTotalQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicine whereUpdatedAt($value)
 */
	class PrescriptionMedicine extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Province
 *
 * @property int $id
 * @property string $name
 * @property int $country_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\City> $cities
 * @property-read int|null $cities_count
 * @property-read \App\Models\Country $country
 * @method static \Illuminate\Database\Eloquent\Builder|Province newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Province newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Province query()
 * @method static \Illuminate\Database\Eloquent\Builder|Province whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Province whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Province whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Province whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Province whereUpdatedAt($value)
 */
	class Province extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PurchasedMedicine
 *
 * @property int $id
 * @property int $medicine_availabilities_id
 * @property int|null $medicine_id
 * @property int|null $batch_id
 * @property string|null $dosage
 * @property string|null $expiry_date
 * @property string|null $manufacturing_date
 * @property int $quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MedicineAvailability $medicineAvailability
 * @property-read \App\Models\Medicine|null $medicines
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereDosage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereManufacturingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereMedicineAvailabilitiesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereMedicineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereUpdatedAt($value)
 */
	class PurchasedMedicine extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Qualification
 *
 * @property int $id
 * @property int $user_id
 * @property string $degree
 * @property string $university
 * @property string $year
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Qualification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Qualification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Qualification query()
 * @method static \Illuminate\Database\Eloquent\Builder|Qualification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Qualification whereDegree($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Qualification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Qualification whereUniversity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Qualification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Qualification whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Qualification whereYear($value)
 */
	class Qualification extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\RequestDocuments
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
 * @property int|null $patient_type_id
 * @property int|null $office_id
 * @property int|null $department_id
 * @property int|null $year_level_id
 * @property int|null $course_id
 * @property int|null $college_id
 * @property int|null $campus_id
 * @property string|null $informant
 * @property string|null $emergency_contact
 * @property \Illuminate\Support\Carbon|null $requested_at
 * @property string|null $complaints
 * @property string|null $note
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
 * @property string|null $examined_on
 * @property string|null $request_of
 * @property string|null $complaints_diagnosis
 * @property string|null $medical_cert_remarks
 * @property array|null $subjects
 * @property string|null $doc_lic_no
 * @property string|null $doc_prt_no
 * @property array|null $consultation_images
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ConsultationMedicine> $consultationMedicines
 * @property-read int|null $consultation_medicines_count
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Illness> $illnesses
 * @property-read int|null $illnesses_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\User|null $nursingInCharge
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServiceType> $services
 * @property-read int|null $services_count
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance incompleteConsultations()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments query()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentIssuance role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereAdmissionsSurgeries($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereAllergies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereAssessment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereCampus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereCampusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereCollege($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereCollegeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereComorbidities($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereComplaints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereComplaintsDiagnosis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereConsultMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereConsultationImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereCourse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereCovidVaccination($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereDocLicNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereDocPrtNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereDocumentCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereEmergencyContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereExaminedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereInformant($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereLmpAog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereMaintenance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereMedicalCertRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereNursingInchargedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereNursingIntervention($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments wherePatientContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments wherePatientTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments wherePertinentExam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments wherePlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments wherePregnancyStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereReligion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereRequestOf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereSubjects($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereVitalSignsBp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereVitalSignsHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereVitalSignsO2Sat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereVitalSignsPr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereVitalSignsRr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereVitalSignsTemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereVitalSignsWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereYearLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereYearLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments withoutTrashed()
 */
	class RequestDocuments extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Role
 *
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property int $is_default
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\RoleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereUpdatedAt($value)
 */
	class Role extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ServiceType
 *
 * @property int $id
 * @property string $category
 * @property string $name
 * @property string|null $auto_rule
 * @property bool $is_other
 * @property int $sort_order
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceType active()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceType ordered()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceType query()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceType whereAutoRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceType whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceType whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceType whereIsOther($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceType whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceType whereUpdatedAt($value)
 */
	class ServiceType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Setting
 *
 * @property int $id
 * @property string $key
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Country|null $country
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereValue($value)
 */
	class Setting extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * App\Models\Slider
 *
 * @property int $id
 * @property string $title
 * @property string $short_description
 * @property bool $is_default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $slider_image
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @method static \Illuminate\Database\Eloquent\Builder|Slider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Slider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Slider query()
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereUpdatedAt($value)
 */
	class Slider extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * App\Models\Specialization
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Doctor> $doctors
 * @property-read int|null $doctors_count
 * @method static \Database\Factories\SpecializationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Specialization newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Specialization newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Specialization query()
 * @method static \Illuminate\Database\Eloquent\Builder|Specialization whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Specialization whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Specialization whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Specialization whereUpdatedAt($value)
 */
	class Specialization extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\StaffDesignation
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StaffProfile> $staffProfiles
 * @property-read int|null $staff_profiles_count
 * @method static \Illuminate\Database\Eloquent\Builder|StaffDesignation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffDesignation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffDesignation query()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffDesignation whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffDesignation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffDesignation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffDesignation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffDesignation whereUpdatedAt($value)
 */
	class StaffDesignation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\StaffProfile
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $role_designation_id
 * @property int|null $assigned_station_id
 * @property string|null $shift_schedule
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ClinicStation|null $assignedStation
 * @property-read \App\Models\StaffDesignation|null $roleDesignation
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|StaffProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder|StaffProfile whereAssignedStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffProfile whereRoleDesignationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffProfile whereShiftSchedule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StaffProfile whereUserId($value)
 */
	class StaffProfile extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\State
 *
 * @property int $id
 * @property string $name
 * @property int $country_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Country $country
 * @method static \Illuminate\Database\Eloquent\Builder|State newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|State newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|State query()
 * @method static \Illuminate\Database\Eloquent\Builder|State whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereUpdatedAt($value)
 */
	class State extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\StockIn
 *
 * @property int $id
 * @property string $availability_no
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchasedMedicine> $purchasedMedcines
 * @property-read int|null $purchased_medcines_count
 * @method static \Illuminate\Database\Eloquent\Builder|StockIn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockIn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockIn query()
 * @method static \Illuminate\Database\Eloquent\Builder|StockIn whereAvailabilityNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockIn whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockIn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockIn whereUpdatedAt($value)
 */
	class StockIn extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\StockOutView
 *
 * @property-read \App\Models\Medicine|null $medicine
 * @method static \Illuminate\Database\Eloquent\Builder|StockOutView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockOutView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockOutView query()
 */
	class StockOutView extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UsedMedicine
 *
 * @property int $id
 * @property int $stock_used
 * @property int|null $medicine_id
 * @property int $model_id
 * @property string $model_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Medicine|null $medicine
 * @method static \Illuminate\Database\Eloquent\Builder|UsedMedicine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UsedMedicine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UsedMedicine query()
 * @method static \Illuminate\Database\Eloquent\Builder|UsedMedicine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsedMedicine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsedMedicine whereMedicineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsedMedicine whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsedMedicine whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsedMedicine whereStockUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsedMedicine whereUpdatedAt($value)
 */
	class UsedMedicine extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UsedMedicineView
 *
 * @property-read \App\Models\Medicine|null $medicine
 * @method static \Illuminate\Database\Eloquent\Builder|UsedMedicineView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UsedMedicineView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UsedMedicineView query()
 */
	class UsedMedicineView extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $first_name
 * @property string|null $middle_name
 * @property string $last_name
 * @property string|null $email
 * @property string|null $contact
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_no
 * @property string|null $emergency_relationship
 * @property string|null $dob
 * @property int|null $gender
 * @property string|null $nationality_citizenship
 * @property bool $status
 * @property string|null $language
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed|null $password
 * @property int|null $type
 * @property string|null $blood_type
 * @property string|null $country_code
 * @property string|null $university_id_number
 * @property string|null $employee_id
 * @property int|null $campus_id
 * @property int|null $college_id
 * @property int|null $course_id
 * @property int|null $year_level_id
 * @property int|null $vaccination_id
 * @property int|null $office_id
 * @property int|null $department_id
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $email_notification
 * @property string $time_zone
 * @property bool $dark_mode
 * @property \Illuminate\Support\Carbon|null $archived_at
 * @property-read \App\Models\Address|null $address
 * @property-read \App\Models\Campus|null $campus
 * @property-read \App\Models\College|null $college
 * @property-read \App\Models\Course|null $course
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\Doctor|null $doctor
 * @property-read string $full_name
 * @property-read string $profile_image
 * @property-read mixed $role_display_name
 * @property-read mixed $role_name
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Office|null $office
 * @property-read \App\Models\Patient|null $patient
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Qualification> $qualifications
 * @property-read int|null $qualifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\StaffProfile|null $staffProfile
 * @property-read \App\Models\Vaccination|null $vaccination
 * @property-read \App\Models\YearLevel|null $yearLevel
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereArchivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBloodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCampusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCollegeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDarkMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmergencyContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmergencyContactNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmergencyRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMiddleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNationalityCitizenship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTimeZone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUniversityIdNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereVaccinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereYearLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutTrashed()
 */
	class User extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * App\Models\Vaccination
 *
 * @property int $id
 * @property string|null $vaccination_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Vaccination newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vaccination newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vaccination query()
 * @method static \Illuminate\Database\Eloquent\Builder|Vaccination whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vaccination whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vaccination whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vaccination whereVaccinationStatus($value)
 */
	class Vaccination extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\YearLevel
 *
 * @property int $id
 * @property string $year_level_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|YearLevel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|YearLevel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|YearLevel query()
 * @method static \Illuminate\Database\Eloquent\Builder|YearLevel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|YearLevel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|YearLevel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|YearLevel whereYearLevelName($value)
 */
	class YearLevel extends \Eloquent {}
}

