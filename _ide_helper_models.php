<?php

// @formatter:off
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
 * @property string|null $postal_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|\Eloquent $owner
 * @method static \Illuminate\Database\Eloquent\Builder|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address query()
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereOwnerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class Address extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Appointment
 *
 * @property int $id
 * @property int $doctor_id
 * @property int $patient_id
 * @property string $date
 * @property string $from_time
 * @property string $from_time_type
 * @property string $to_time
 * @property string $to_time_type
 * @property int $status
 * @property string|null $description
 * @property int $service_id
 * @property string $payable_amount
 * @property int $payment_type
 * @property int $payment_method
 * @property string $appointment_unique_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Doctor $doctor
 * @property-read mixed $status_name
 * @property-read \App\Models\Patient $patient
 * @property-read \App\Models\Service $services
 * @property-read \App\Models\Transaction|null $transaction
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\AppointmentFactory factory($count = null, $state = [])
 * @method static Builder|Appointment newModelQuery()
 * @method static Builder|Appointment newQuery()
 * @method static Builder|Appointment query()
 * @method static Builder|Appointment whereAppointmentUniqueId($value)
 * @method static Builder|Appointment whereCreatedAt($value)
 * @method static Builder|Appointment whereDate($value)
 * @method static Builder|Appointment whereDescription($value)
 * @method static Builder|Appointment whereDoctorId($value)
 * @method static Builder|Appointment whereFromTime($value)
 * @method static Builder|Appointment whereFromTimeType($value)
 * @method static Builder|Appointment whereId($value)
 * @method static Builder|Appointment wherePatientId($value)
 * @method static Builder|Appointment wherePayableAmount($value)
 * @method static Builder|Appointment wherePaymentMethod($value)
 * @method static Builder|Appointment wherePaymentType($value)
 * @method static Builder|Appointment whereServiceId($value)
 * @method static Builder|Appointment whereStatus($value)
 * @method static Builder|Appointment whereToTime($value)
 * @method static Builder|Appointment whereToTimeType($value)
 * @method static Builder|Appointment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Appointment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Brand
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Medicine> $medicines
 * @property-read int|null $medicines_count
 * @method static \Illuminate\Database\Eloquent\Builder|Brand newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand query()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Brand extends \Eloquent {}
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
 * @mixin \Eloquent
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Brand|null $brand
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
 * @mixin \Eloquent
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\City
 *
 * @property int $id
 * @property string $name
 * @property string $state_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\State $state
 * @method static \Database\Factories\CityFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|City newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|City newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|City query()
 * @method static \Illuminate\Database\Eloquent\Builder|City whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class City extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ClinicSchedule
 *
 * @property int $id
 * @property string $day_of_week
 * @property string $start_time
 * @property string $end_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicSchedule query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicSchedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicSchedule whereDayOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicSchedule whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicSchedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicSchedule whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClinicSchedule whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class ClinicSchedule extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\College
 *
 * @property int $id
 * @property string $college_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|College newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|College newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|College query()
 * @method static \Illuminate\Database\Eloquent\Builder|College whereCollegeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|College whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|College whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|College whereUpdatedAt($value)
 * @mixin \Eloquent
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
 * @property int $quantity
 * @property string|null $used_for
 * @property string|null $dosage_instructions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\RequestDocuments $requestDocument
 * @property-read \App\Models\Medicine $medicine
 * @method static \Illuminate\Database\Eloquent\Builder|ConsultationMedicine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConsultationMedicine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConsultationMedicine query()
 * @method static \Illuminate\Database\Eloquent\Builder|ConsultationMedicine whereCreatedAt($value)
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
 * @mixin \Eloquent
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
 * @mixin \Eloquent
 */
	class Course extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Currency
 *
 * @property int $id
 * @property string $currency_name
 * @property string $currency_icon
 * @property string $currency_code
 * @property int $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static \Database\Factories\CurrencyFactory factory($count = null, $state = [])
 * @method static Builder|Currency newModelQuery()
 * @method static Builder|Currency newQuery()
 * @method static Builder|Currency query()
 * @method static Builder|Currency whereCreatedAt($value)
 * @method static Builder|Currency whereCurrencyCode($value)
 * @method static Builder|Currency whereCurrencyIcon($value)
 * @method static Builder|Currency whereCurrencyName($value)
 * @method static Builder|Currency whereId($value)
 * @method static Builder|Currency whereIsDefault($value)
 * @method static Builder|Currency whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Currency extends \Eloquent {}
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
 * @mixin \Eloquent
 */
	class Diagnose extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Doctor
 *
 * @property int $id
 * @property int $user_id
 * @property float|null $experience
 * @property string|null $twitter_url
 * @property string|null $linkedin_url
 * @property string|null $instagram_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Address|null $address
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Appointment> $appointments
 * @property-read int|null $appointments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DoctorSession> $doctorSession
 * @property-read int|null $doctor_session_count
 * @property-read \App\Models\User $doctorUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Specialization> $specializations
 * @property-read int|null $specializations_count
 * @property-read \App\Models\User $testUser
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor query()
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereExperience($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereInstagramUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereLinkedinUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereTwitterUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Doctor whereUserId($value)
 * @mixin \Eloquent
 */
	class Doctor extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DoctorHoliday
 *
 * @property int $id
 * @property string|null $name
 * @property int $doctor_id
 * @property string $date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Doctor $doctor
 * @method static \Illuminate\Database\Eloquent\Builder|DoctorHoliday newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DoctorHoliday newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DoctorHoliday query()
 * @method static \Illuminate\Database\Eloquent\Builder|DoctorHoliday whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DoctorHoliday whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DoctorHoliday whereDoctorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DoctorHoliday whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DoctorHoliday whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DoctorHoliday whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class DoctorHoliday extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DoctorSession
 *
 * @property int $id
 * @property int $doctor_id
 * @property int $session_meeting_time
 * @property string $session_gap
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Doctor $doctor
 * @property-read Collection<int, \App\Models\WeekDay> $sessionWeekDays
 * @property-read int|null $session_week_days_count
 * @method static \Database\Factories\DoctorSessionFactory factory($count = null, $state = [])
 * @method static Builder|DoctorSession newModelQuery()
 * @method static Builder|DoctorSession newQuery()
 * @method static Builder|DoctorSession query()
 * @method static Builder|DoctorSession whereCreatedAt($value)
 * @method static Builder|DoctorSession whereDoctorId($value)
 * @method static Builder|DoctorSession whereId($value)
 * @method static Builder|DoctorSession whereSessionGap($value)
 * @method static Builder|DoctorSession whereSessionMeetingTime($value)
 * @method static Builder|DoctorSession whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class DoctorSession extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Enquiry
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string $subject
 * @property string $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $view
 * @property string|null $country_code
 * @property-read string $view_name
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry query()
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereView($value)
 * @mixin \Eloquent
 */
	class Enquiry extends \Eloquent {}
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
 * App\Models\Medicine
 *
 * @property int $id
 * @property int|null $category_id
 * @property int|null $brand_id
 * @property string $name
 * @property float $selling_price
 * @property float $buying_price
 * @property int $quantity
 * @property int $available_quantity
 * @property string $salt_composition
 * @property string|null $description
 * @property string|null $side_effects
 * @property string|null $currency_symbol
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Brand|null $brand
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\PrescriptionMedicineModal|null $prescriptionMedicines
 * @property-read \App\Models\PurchasedMedicine|null $purchasedMedicine
 * @property-read \App\Models\UsedMedicine|null $usedMedicines
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine query()
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereAvailableQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereBuyingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereCurrencySymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereSaltComposition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereSellingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereSideEffects($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $minimum_stock_alert Minimum quantity threshold for stock alert
 * @property string|null $stock_alert_percentage Percentage threshold for stock alert (e.g., 20 for 20%)
 * @property-read mixed $earliest_expiry_date
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereMinimumStockAlert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereStockAlertPercentage($value)
 */
	class Medicine extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MedicineBill
 *
 * @property int $id
 * @property string $history_number
 * @property int $patient_id
 * @property int|null $doctor_id
 * @property string $model_type
 * @property string $model_id
 * @property float $discount
 * @property float $net_amount
 * @property float $total
 * @property float $tax_amount
 * @property int $payment_status
 * @property int $payment_type
 * @property string|null $note
 * @property string $bill_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Doctor|null $doctor
 * @property-read \App\Models\Patient $patient
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SaleMedicine> $saleMedicine
 * @property-read int|null $sale_medicine_count
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill query()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereBillDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereHistoryNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereDoctorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereNetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineBill whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class MedicineBill extends \Eloquent {}
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
 * @mixin \Eloquent
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Address|null $address
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Appointment> $appointments
 * @property-read int|null $appointments_count
 * @property-read string $profile
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\User $patientUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RequestDocuments> $requestDocuments
 * @property-read int|null $request_documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\PatientFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Patient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Patient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Patient permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient query()
 * @method static \Illuminate\Database\Eloquent\Builder|Patient role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient wherePatientUniqueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patient whereUserId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\PatientQueue|null $currentQueue
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PatientQueue> $queueEntries
 * @property-read int|null $queue_entries_count
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
 * @property \Illuminate\Support\Carbon|null $called_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $addedBy
 * @property-read mixed $queue_number
 * @property-read \App\Models\Patient $patient
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue orderByQueue()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue priority()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue query()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue waiting()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereCalledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereIsPriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereRoomNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientQueue whereUpdatedAt($value)
 */
	class PatientQueue extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PaymentGateway
 *
 * @property int $id
 * @property int $payment_gateway_id
 * @property string $payment_gateway
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGateway newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGateway newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGateway query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGateway whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGateway whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGateway wherePaymentGateway($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGateway wherePaymentGatewayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGateway whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class PaymentGateway extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Permission
 *
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Permission extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Prescription
 *
 * @property int $id
 * @property int $appointment_id
 * @property int $patient_id
 * @property int|null $doctor_id
 * @property string|null $food_allergies
 * @property string|null $tendency_bleed
 * @property string|null $heart_disease
 * @property string|null $high_blood_pressure
 * @property string|null $diabetic
 * @property string|null $surgery
 * @property string|null $accident
 * @property string|null $others
 * @property string|null $medical_history
 * @property string|null $current_medication
 * @property string|null $female_pregnancy
 * @property string|null $breast_feeding
 * @property string|null $health_insurance
 * @property string|null $low_income
 * @property string|null $reference
 * @property bool|null $status
 * @property string|null $plus_rate
 * @property string|null $temperature
 * @property string|null $problem_description
 * @property string|null $test
 * @property string|null $advice
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Doctor|null $doctor
 * @property-read \App\Models\Patient $patient
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription query()
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereAccident($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereAdvice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereAppointmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereBreastFeeding($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereCurrentMedication($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereDiabetic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereDoctorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereFemalePregnancy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereFoodAllergies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereHealthInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereHeartDisease($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereHighBloodPressure($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereLowIncome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereMedicalHistory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereOthers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription wherePlusRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereProblemDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereSurgery($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereTemperature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereTendencyBleed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereTest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prescription whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\Models\Appointment|null $appointment
 */
	class Prescription extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PrescriptionMedicineModal
 *
 * @property int $id
 * @property int $prescription_id
 * @property int $medicine
 * @property string|null $dosage
 * @property string|null $day
 * @property int $dose_interval
 * @property string|null $time
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Medicine|null $medicines
 * @property-read \App\Models\Prescription $prescription
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicineModal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicineModal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicineModal query()
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicineModal whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicineModal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicineModal whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicineModal whereDosage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicineModal whereDoseInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicineModal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicineModal whereMedicine($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicineModal wherePrescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicineModal whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrescriptionMedicineModal whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class PrescriptionMedicineModal extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PurchaseMedicine
 *
 * @property int $id
 * @property string $purchase_no
 * @property float $tax
 * @property float $total
 * @property float $net_amount
 * @property int $payment_type
 * @property float $discount
 * @property string|null $note
 * @property string|null $payment_note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchasedMedicine> $purchasedMedcines
 * @property-read int|null $purchased_medcines_count
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine whereNetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine wherePaymentNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine wherePurchaseNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class PurchaseMedicine extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PurchasedMedicine
 *
 * @property int $id
 * @property int $purchase_medicines_id
 * @property int|null $medicine_id
 * @property string|null $dosage
 * @property string|null $expiry_date
 * @property string $manufacturing_date
 * @property float $tax
 * @property int $quantity
 * @property float $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Medicine|null $medicines
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereDosage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereManufacturingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereMedicineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine wherePurchaseMedicinesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasedMedicine whereUpdatedAt($value)
 * @mixin \Eloquent
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
 * @mixin \Eloquent
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
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments query()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereAdmissionsSurgeries($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereAllergies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereAssessment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereCampus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereCollege($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereComorbidities($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereComplaints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereComplaintsDiagnosis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereConsultMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereCourse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereCovidVaccination($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereDateOfBirth($value)
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
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereNursingInchargedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereNursingIntervention($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments wherePatientContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments wherePertinentExam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments wherePlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments wherePregnancyStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereReligion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereRequestOf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereStatus($value)
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
 * @mixin \Eloquent
 * @property array|null $consultation_images
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ConsultationMedicine> $consultationMedicines
 * @property-read int|null $consultation_medicines_count
 * @method static \Illuminate\Database\Eloquent\Builder|RequestDocuments whereConsultationImages($value)
 */
	class RequestDocuments extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\RoleFactory factory($count = null, $state = [])
 * @method static Builder|Role newModelQuery()
 * @method static Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role permission($permissions)
 * @method static Builder|Role query()
 * @method static Builder|Role whereCreatedAt($value)
 * @method static Builder|Role whereDisplayName($value)
 * @method static Builder|Role whereGuardName($value)
 * @method static Builder|Role whereId($value)
 * @method static Builder|Role whereIsDefault($value)
 * @method static Builder|Role whereName($value)
 * @method static Builder|Role whereUpdatedAt($value)
 * @mixin Model
 */
	class Role extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SaleMedicine
 *
 * @property int $id
 * @property int $medicine_bill_id
 * @property int $medicine_id
 * @property int $sale_quantity
 * @property float $sale_price
 * @property float $tax
 * @property string $expiry_date
 * @property float $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Medicine $medicine
 * @property-read \App\Models\MedicineBill|null $medicineBill
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine query()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereMedicineBillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereMedicineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereSalePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereSaleQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class SaleMedicine extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Service
 *
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string|null $charges
 * @property bool $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $short_description
 * @property-read string $icon
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\ServiceCategory $serviceCategory
 * @property-read Collection<int, \App\Models\Doctor> $serviceDoctors
 * @property-read int|null $service_doctors_count
 * @method static Builder|Service newModelQuery()
 * @method static Builder|Service newQuery()
 * @method static Builder|Service query()
 * @method static Builder|Service whereCategoryId($value)
 * @method static Builder|Service whereCharges($value)
 * @method static Builder|Service whereCreatedAt($value)
 * @method static Builder|Service whereId($value)
 * @method static Builder|Service whereName($value)
 * @method static Builder|Service whereShortDescription($value)
 * @method static Builder|Service whereStatus($value)
 * @method static Builder|Service whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Service extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * App\Models\ServiceCategory
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Service> $activatedServices
 * @property-read int|null $activated_services_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Service> $services
 * @property-read int|null $services_count
 * @method static \Database\Factories\ServiceCategoryFactory factory($count = null, $state = [])
 * @method static Builder|ServiceCategory newModelQuery()
 * @method static Builder|ServiceCategory newQuery()
 * @method static Builder|ServiceCategory query()
 * @method static Builder|ServiceCategory whereCreatedAt($value)
 * @method static Builder|ServiceCategory whereId($value)
 * @method static Builder|ServiceCategory whereName($value)
 * @method static Builder|ServiceCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class ServiceCategory extends \Eloquent {}
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
 * @property-read \App\Models\Country $country
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
 * @mixin \Eloquent
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $slider_image
 * @property-read MediaCollection<int, Media> $media
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
 * @mixin \Eloquent
 */
	class Slider extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * App\Models\Specialization
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
 * @mixin \Eloquent
 */
	class Specialization extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Staff
 *
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\StaffFactory factory($count = null, $state = [])
 * @method static Builder|Staff newModelQuery()
 * @method static Builder|Staff newQuery()
 * @method static Builder|Staff permission($permissions)
 * @method static Builder|Staff query()
 * @method static Builder|Staff role($roles, $guard = null)
 * @mixin \Eloquent
 */
	class Staff extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * App\Models\State
 *
 * @property int $id
 * @property string $name
 * @property int $country_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Country $country
 * @method static \Illuminate\Database\Eloquent\Builder|State newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|State newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|State query()
 * @method static \Illuminate\Database\Eloquent\Builder|State whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class State extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Subscribe
 *
 * @property int $id
 * @property string $email
 * @property bool $subscribe
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Subscribe newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscribe newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscribe query()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscribe whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscribe whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscribe whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscribe whereSubscribe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscribe whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Subscribe extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Transaction
 *
 * @property int $id
 * @property int $user_id
 * @property string $transaction_id
 * @property string $appointment_id
 * @property float $amount
 * @property int $type
 * @property bool|null $status
 * @property int|null $accepted_by
 * @property array|null $meta
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\User|null $acceptedPaymentUser
 * @property-read \App\Models\Appointment|null $appointment
 * @property-read \App\Models\User $user
 * @method static Builder|Transaction newModelQuery()
 * @method static Builder|Transaction newQuery()
 * @method static Builder|Transaction query()
 * @method static Builder|Transaction whereAcceptedBy($value)
 * @method static Builder|Transaction whereAmount($value)
 * @method static Builder|Transaction whereAppointmentId($value)
 * @method static Builder|Transaction whereCreatedAt($value)
 * @method static Builder|Transaction whereId($value)
 * @method static Builder|Transaction whereMeta($value)
 * @method static Builder|Transaction whereStatus($value)
 * @method static Builder|Transaction whereTransactionId($value)
 * @method static Builder|Transaction whereType($value)
 * @method static Builder|Transaction whereUpdatedAt($value)
 * @method static Builder|Transaction whereUserId($value)
 * @mixin \Eloquent
 */
	class Transaction extends \Eloquent {}
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
 * @mixin \Eloquent
 */
	class UsedMedicine extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UsedMedicineView
 *
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
 * @property string|null $dob
 * @property int|null $gender
 * @property bool $status
 * @property string|null $language
 * @property Carbon|null $email_verified_at
 * @property string|null $password
 * @property int|null $type
 * @property string|null $blood_type
 * @property string|null $country_code
 * @property int|null $campus_id
 * @property int|null $college_id
 * @property int|null $course_id
 * @property int|null $year_level_id
 * @property int|null $vaccination_id
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property bool $email_notification
 * @property string $time_zone
 * @property bool $dark_mode
 * @property-read \App\Models\Address|null $address
 * @property-read \App\Models\Campus|null $campus
 * @property-read \App\Models\College|null $college
 * @property-read \App\Models\Course|null $course
 * @property-read \App\Models\Doctor|null $doctor
 * @property-read string $full_name
 * @property-read string $profile_image
 * @property-read mixed $role_display_name
 * @property-read mixed $role_name
 * @property-read MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Patient|null $patient
 * @property-read Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, \App\Models\Qualification> $qualifications
 * @property-read int|null $qualifications_count
 * @property-read Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\Staff|null $staff
 * @property-read \App\Models\Vaccination|null $vaccination
 * @property-read \App\Models\YearLevel|null $yearLevel
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User permission($permissions)
 * @method static Builder|User query()
 * @method static Builder|User role($roles, $guard = null)
 * @method static Builder|User whereBloodType($value)
 * @method static Builder|User whereCampusId($value)
 * @method static Builder|User whereCollegeId($value)
 * @method static Builder|User whereContact($value)
 * @method static Builder|User whereCountryCode($value)
 * @method static Builder|User whereCourseId($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereDarkMode($value)
 * @method static Builder|User whereDob($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailNotification($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereEmergencyContactName($value)
 * @method static Builder|User whereEmergencyContactNo($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereGender($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLanguage($value)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User whereMiddleName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereStatus($value)
 * @method static Builder|User whereTimeZone($value)
 * @method static Builder|User whereType($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereVaccinationId($value)
 * @method static Builder|User whereYearLevelId($value)
 * @mixin \Eloquent
 * @property string|null $emergency_relationship
 * @property int|null $office_id
 * @property int|null $department_id
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmergencyRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereOfficeId($value)
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
 * @mixin \Eloquent
 */
	class Vaccination extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Visit
 *
 * @property int $id
 * @property string $visit_date
 * @property int $doctor_id
 * @property int $patient_id
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Doctor $doctor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VisitNote> $notes
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VisitObservation> $observations
 * @property-read int|null $observations_count
 * @property-read \App\Models\Patient $patient
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VisitPrescription> $prescriptions
 * @property-read int|null $prescriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VisitProblem> $problems
 * @property-read int|null $problems_count
 * @property-read \App\Models\Doctor $visitDoctor
 * @property-read \App\Models\Patient $visitPatient
 * @method static \Illuminate\Database\Eloquent\Builder|Visit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Visit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Visit query()
 * @method static \Illuminate\Database\Eloquent\Builder|Visit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Visit whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Visit whereDoctorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Visit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Visit wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Visit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Visit whereVisitDate($value)
 * @mixin \Eloquent
 */
	class Visit extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\VisitNote
 *
 * @property int $id
 * @property string $note_name
 * @property int $visit_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Visit $visit
 * @method static \Illuminate\Database\Eloquent\Builder|VisitNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisitNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisitNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|VisitNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitNote whereNoteName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitNote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitNote whereVisitId($value)
 * @mixin \Eloquent
 */
	class VisitNote extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\VisitObservation
 *
 * @property int $id
 * @property string $observation_name
 * @property int $visit_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Visit $visit
 * @method static \Illuminate\Database\Eloquent\Builder|VisitObservation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisitObservation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisitObservation query()
 * @method static \Illuminate\Database\Eloquent\Builder|VisitObservation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitObservation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitObservation whereObservationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitObservation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitObservation whereVisitId($value)
 * @mixin \Eloquent
 */
	class VisitObservation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\VisitPrescription
 *
 * @property int $id
 * @property int $visit_id
 * @property string $prescription_name
 * @property string $frequency
 * @property string $duration
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription query()
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription whereFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription wherePrescriptionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitPrescription whereVisitId($value)
 * @mixin \Eloquent
 */
	class VisitPrescription extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\VisitProblem
 *
 * @property int $id
 * @property string $problem_name
 * @property int $visit_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Visit $visit
 * @method static \Illuminate\Database\Eloquent\Builder|VisitProblem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisitProblem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisitProblem query()
 * @method static \Illuminate\Database\Eloquent\Builder|VisitProblem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitProblem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitProblem whereProblemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitProblem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisitProblem whereVisitId($value)
 * @mixin \Eloquent
 */
	class VisitProblem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\WeekDay
 *
 * @property int $id
 * @property int $doctor_id
 * @property int $doctor_session_id
 * @property string $day_of_week
 * @property string $start_time
 * @property string $end_time
 * @property string $start_time_type
 * @property string $end_time_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\DoctorSession $doctorSession
 * @property-read mixed $full_end_time
 * @property-read mixed $full_start_time
 * @method static \Illuminate\Database\Eloquent\Builder|WeekDay newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeekDay newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeekDay query()
 * @method static \Illuminate\Database\Eloquent\Builder|WeekDay whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeekDay whereDayOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeekDay whereDoctorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeekDay whereDoctorSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeekDay whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeekDay whereEndTimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeekDay whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeekDay whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeekDay whereStartTimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeekDay whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class WeekDay extends \Eloquent {}
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
 * @mixin \Eloquent
 */
	class YearLevel extends \Eloquent {}
}

