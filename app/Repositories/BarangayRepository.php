<?php

namespace App\Repositories;

use App\Models\Barangay;

/**
 * Class BarangayRepository
 */
class BarangayRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'city_id',
    ];

    /**
     * Return searchable fields
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Barangay::class;
    }
}
