<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBarangayRequest;
use App\Http\Requests\UpdateBarangayRequest;
use App\Models\Address;
use App\Models\Barangay;
use App\Models\City;
use App\Repositories\BarangayRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class BarangayController extends AppBaseController
{
    /** @var BarangayRepository */
    private $barangayRepository;

    public function __construct(BarangayRepository $barangayRepo)
    {
        $this->barangayRepository = $barangayRepo;
    }

    /**
     * Display a listing of the Barangay.
     *
     * @return Application|Factory|View
     */
    public function index(): \Illuminate\View\View
    {
        $cities = City::orderBy('name', 'ASC')->pluck('name', 'id');

        return view('barangays.index', compact('cities'));
    }

    /**
     * Store a newly created Barangay in storage.
     */
    public function store(CreateBarangayRequest $request): JsonResponse
    {
        $input = $request->all();

        // Check if barangay with same name and city already exists
        $existingBarangay = Barangay::where('name', $input['name'])
            ->where('city_id', $input['city_id'])
            ->first();

        if ($existingBarangay) {
            return $this->sendError(__('messages.barangay.barangay_already_exists'));
        }

        $barangay = $this->barangayRepository->create($input);
        return $this->sendSuccess(__('messages.flash.barangay_create'));
    }

    /**
     * Show the form for editing the specified Barangay.
     */
    public function edit(Barangay $barangay): JsonResponse
    {
        return $this->sendResponse($barangay, __('messages.flash.barangay_retrieved'));
    }

    /**
     * Update the specified Barangay in storage.
     */
    public function update(UpdateBarangayRequest $request, Barangay $barangay): JsonResponse
    {
        $input = $request->all();

        // Check if another barangay with same name and city already exists (excluding current barangay)
        $existingBarangay = Barangay::where('name', $input['name'])
            ->where('city_id', $input['city_id'])
            ->where('id', '!=', $barangay->id)
            ->first();

        if ($existingBarangay) {
            return $this->sendError(__('messages.barangay.barangay_already_exists'));
        }

        $this->barangayRepository->update($input, $barangay->id);
        return $this->sendSuccess(__('messages.flash.barangay_update'));
    }

    public function destroy(Barangay $barangay): JsonResponse
    {
        $checkRecord = Address::whereBarangayId($barangay->id)->exists();

        if ($checkRecord) {
            return $this->sendError(__('messages.flash.barangay_used'));
        }
        $barangay->delete();

        return $this->sendSuccess(__('messages.flash.barangay_delete'));
    }
}
