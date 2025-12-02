<?php

namespace App\Http\Controllers;

use App\Exports\MedicineAvailabilityExport;
use App\Http\Requests\CreateMedicineAvailabilityRequest;
use App\Models\Medicine;
use App\Models\MedicineAvailability;
use App\Repositories\MedicineRepository;
use App\Repositories\MedicineAvailabilityRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Laracasts\Flash\Flash;
use Maatwebsite\Excel\Facades\Excel;

class MedicineAvailabilityController extends AppBaseController
{
    /** @var MedicineAvailabilityRepository */
    /** @var MedicineRepository */
    private $medicineAvailabilityRepository;

    private $medicineRepository;

    public function __construct(MedicineAvailabilityRepository $medicineAvailabilityRepo, MedicineRepository $medicineRepository)
    {
        $this->medicineAvailabilityRepository = $medicineAvailabilityRepo;
        $this->medicineRepository = $medicineRepository;
    }

    public function index(): View
    {

        return view('medicine-availabilities.index');
    }

    public function create(): View
    {

        $data = $this->medicineRepository->getSyncList();
        $medicines = $this->medicineAvailabilityRepository->getMedicine();
        $medicineList = $this->medicineAvailabilityRepository->getMedicineList();
        $categories = $this->medicineAvailabilityRepository->getCategory();
        $categoriesList = $this->medicineAvailabilityRepository->getCategoryList();

        return view('medicine-availabilities.create', compact('medicines', 'medicineList', 'categories', 'categoriesList'))->with($data);
    }

    public function store(CreateMedicineAvailabilityRequest $request): RedirectResponse
    {

        $input = $request->all();

        // Temporary debug logging
        \Illuminate\Support\Facades\Log::info('Medicine Availability Input Data:', $input);

        // Generate unique availability number if not provided
        if (empty($input['availability_no'])) {
            $input['availability_no'] = generateUniqueAvailabilityNumber();
        }

        $this->medicineAvailabilityRepository->store($input);
        flash::success(__('messages.medicine_availability.medicine_availability_success'));

        // Redirect based on user role
        if (isRole('clinic_admin')) {
            return redirect(route('medicine-availability.index'));
        } elseif (isRole('staff')) {
            return redirect(route('staff.medicine-availability.index'));
        } elseif (isRole('doctor')) {
            return redirect(route('doctors.medicine-availability.index'));
        }

        return redirect(route('medicine-availability.index'));
    }

    /**
     * @param  MedicineAvailability  $medicineAvailability
     */
    public function show(MedicineAvailability $medicineAvailability): View
    {
        $medicineAvailability->load(['purchasedMedcines.medicines']);

        return view('medicine-availabilities.show', compact('medicineAvailability'));
    }

    /**
     * @param  MedicineAvailability  $medicineAvailability
     */
    public function edit(MedicineAvailability $medicineAvailability): View
    {
        $medicineAvailability->load(['purchasedMedcines.medicines']);
        $medicines = $this->medicineAvailabilityRepository->getMedicine();
        $medicineList = $this->medicineAvailabilityRepository->getMedicineList();
        $categories = $this->medicineAvailabilityRepository->getCategory();
        $categoriesList = $this->medicineAvailabilityRepository->getCategoryList();

        return view('medicine-availabilities.edit', compact('medicineAvailability', 'medicines', 'medicineList', 'categories', 'categoriesList'));
    }

    /**
     * @param  CreateMedicineAvailabilityRequest  $request
     * @param  MedicineAvailability  $medicineAvailability
     */
    public function update(CreateMedicineAvailabilityRequest $request, MedicineAvailability $medicineAvailability): RedirectResponse
    {
        $input = $request->all();

        $this->medicineAvailabilityRepository->updatePurchaseMedicine($input, $medicineAvailability->id);

        flash::success(__('messages.medicine_availability.purchased_medicine_updated'));

        // Redirect based on user role
        if (isRole('clinic_admin')) {
            return redirect(route('medicine-availability.index'));
        } elseif (isRole('staff')) {
            return redirect(route('staff.medicine-availability.index'));
        } elseif (isRole('doctor')) {
            return redirect(route('doctors.medicine-availability.index'));
        }

        return redirect(route('medicine-availability.index'));
    }

    public function getMedicine(Medicine $medicine): JsonResponse
    {

        return $this->sendResponse($medicine, 'retrieved');
    }

    public function purchaseMedicineExport()
    {
        $medicineAvailabilities = MedicineAvailability::with('purchasedMedcines')->get();
        if ($medicineAvailabilities->isEmpty()) {
            Flash::error(__('messages.no_data_available'));
            return redirect(route('medicine-availability.index'));
        }
        $response = Excel::download(new MedicineAvailabilityExport, 'medicine-availability-' . time() . '.xlsx');

        ob_end_clean();

        return $response;
    }

    /**
     * [Description for usedMedicine]
     *
     * @return [type]
     */
    public function usedMedicine(): View
    {

        return view('used-medicine.index');
    }

    public function destroy(MedicineAvailability $medicineAvailability)
    {
        $medicineAvailability->delete();

        return $this->sendSuccess(__('messages.flash.medicine_deleted'));
    }
}
