<?php

namespace App\Repositories;

use App\Models\Campus;
use App\Models\City;
use App\Models\College;
use App\Models\Course;
use App\Models\RequestDocuments;
use App\Models\User;
use App\Models\Vaccination;
use App\Models\YearLevel;

class RequestRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
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
        'course_year',
        'informant',
        'emergency_contact',
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
        'vital_signs_weight',
        'pertinent_exam',
        'assessment',
        'plan'
    ];

    /**
     * Return searchable fields
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function getData(): array
    {
        $data['campuses'] = Campus::toBase()->pluck('campus_name', 'id');
        $data['colleges'] = College::toBase()->pluck('college_name', 'id');
        $data['courses'] = Course::toBase()->pluck('course_name', 'id');
        $data['year_levels'] = YearLevel::toBase()->pluck('year_level_name', 'id');
        $data['vaccination_data'] = Vaccination::toBase()->pluck('vaccination_status', 'id');

        return $data;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return RequestDocuments::class;
    }
}
