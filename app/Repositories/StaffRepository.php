<?php

namespace App\Repositories;

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
        return User::class;
    }

    public function store($input): bool
    {
        try {
            DB::beginTransaction();

            $input['email'] = setEmailLowerCase($input['email']);
            $input['password'] = Hash::make($input['password']);
            $input['type'] = User::STAFF;
            // Set email as verified with Philippine time
            $input['email_verified_at'] = now()->setTimezone('Asia/Manila')->toDateTimeString();

            $staff = User::create(Arr::only($input, ['first_name', 'last_name', 'email', 'employee_id', 'contact', 'country_code', 'password', 'gender', 'type', 'email_verified_at']));
            $staff->assignRole('staff');

            if (isset($input['profile']) && ! empty($input['profile'])) {
                $staff->addMedia($input['profile'])->toMediaCollection(User::PROFILE, config('app.media_disc'));
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
            if (isset($input['password']) && ! empty($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            } else {
                unset($input['password']);
            }

            $input['type'] = User::STAFF;

            $staff->update(Arr::only($input, ['first_name', 'last_name', 'email', 'employee_id', 'contact', 'country_code', 'password', 'gender', 'type']));
            $staff->syncRoles('staff');

            if (isset($input['profile']) && ! empty($input['profile'])) {
                $staff->clearMediaCollection(User::PROFILE);
                $staff->addMedia($input['profile'])->toMediaCollection(User::PROFILE, config('app.media_disc'));
            }

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $staff = User::findOrFail($id);

            // Archive only. The staff profile (designation / station) and the profile picture
            // are kept so an archived account can be restored intact; deleting them here meant
            // a restored user came back without any module access. M-13.
            $staff->delete();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }
}
