<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class StaffRepository
 *
 * @version August 6, 2021, 10:17 am UTC
 */
class StaffRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'gender',
        'role',
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
        return Staff::class;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return Role::pluck('display_name', 'id');
        return $roles;
    }

    public function store($input): bool
    {
        try {
            DB::beginTransaction();

            $input['email'] = setEmailLowerCase($input['email']);
            $input['institutional_email'] = setEmailLowerCase($input['institutional_email']);
            $input['password'] = Hash::make($input['password']);
            $input['type'] = User::STAFF;
            // Set email as verified with Philippine time
            $input['email_verified_at'] = now()->setTimezone('Asia/Manila')->toDateTimeString();

            $staffProfileInput = Arr::only($input, ['role_designation_id', 'assigned_station_id', 'shift_schedule']);
            $staff = User::create(Arr::except($input, ['role_designation_id', 'assigned_station_id', 'shift_schedule']));
            $staff->staffProfile()->updateOrCreate(['user_id' => $staff->id], $staffProfileInput);

            if (isset($input['role']) && ! empty($input['role'])) {
                $staff->assignRole($input['role']);
            }

            if (isset($input['profile']) && ! empty($input['profile'])) {
                $staff->addMedia($input['profile'])->toMediaCollection(Staff::PROFILE, config('app.media_disc'));
            }

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function update($input, $id)
    {
        try {
            DB::beginTransaction();

            $staff = User::find($id);
            $input['email'] = setEmailLowerCase($input['email']);
            $input['institutional_email'] = setEmailLowerCase($input['institutional_email']);
            if (isset($input['password']) && ! empty($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            } else {
                unset($input['password']);
            }

            $input['type'] = User::STAFF;

            $staffProfileInput = Arr::only($input, ['role_designation_id', 'assigned_station_id', 'shift_schedule']);
            $staff->update(Arr::except($input, ['role_designation_id', 'assigned_station_id', 'shift_schedule']));
            $staff->staffProfile()->updateOrCreate(['user_id' => $staff->id], $staffProfileInput);

            if (isset($input['role']) && ! empty($input['role'])) {
                $staff->syncRoles($input['role']);
            }

            if (isset($input['profile']) && ! empty($input['profile'])) {
                $staff->clearMediaCollection(Staff::PROFILE);
                $staff->media()->delete();
                $staff->addMedia($input['profile'])->toMediaCollection(Staff::PROFILE, config('app.media_disc'));
            }

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }
}
