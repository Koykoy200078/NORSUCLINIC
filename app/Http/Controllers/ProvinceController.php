<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStateRequest;
use App\Http\Requests\UpdateStateRequest;
use App\Models\Address;
use App\Models\Country;
use App\Models\Province;
use App\Repositories\StateRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

/**
 * Renamed from StateController → ProvinceController.
 * Uses Province model (table: states — DB not yet migrated).
 * Route binding key is `state` for backward compat with existing routes.
 */
class ProvinceController extends AppBaseController
{
    /** @var StateRepository */
    private $stateRepository;

    public function __construct(StateRepository $stateRepo)
    {
        $this->stateRepository = $stateRepo;
    }

    public function index(): \Illuminate\View\View
    {
        $countries = Country::orderBy('name', 'ASC')->pluck('name', 'id');

        return view('states.index', compact('countries'));
    }

    public function store(CreateStateRequest $request): JsonResponse
    {
        $input = $request->all();
        $country = Country::where('id', $input['country_id'])->pluck('name')->first();

        $isdata = 0;
        foreach (Province::STATE_ARRAY as $key => $value) {
            if ($value == $country) {
                $isdata = 1;
            }
        }
        if ($isdata == 1) {
            $this->stateRepository->create($input);
            return $this->sendSuccess(__('messages.flash.state_create'));
        } else {
            return $this->sendError(__('messages.common.province_not_avl'));
        }
    }

    public function edit(Province $state): JsonResponse
    {
        return $this->sendResponse($state, __('messages.flash.states_retrieve'));
    }

    public function update(UpdateStateRequest $request, Province $state): JsonResponse
    {
        $input = $request->all();
        $this->stateRepository->update($input, $state->id);
        return $this->sendSuccess(__('messages.flash.state_update'));
    }

    public function destroy(Province $state): JsonResponse
    {
        $checkRecord = Address::whereStateId($state->id)->exists();
        if ($checkRecord) {
            return $this->sendError(__('messages.flash.state_use'));
        }

        // Block when child cities exist — ON DELETE CASCADE would silently wipe the
        // cities/barangays beneath this province. E-DL-6.
        if (\App\Models\City::where('state_id', $state->id)->exists()) {
            return $this->sendError(__('messages.flash.state_use'));
        }

        $state->delete();

        return $this->sendSuccess(__('messages.flash.state_delete'));
    }
}
