<?php

namespace App\Http\Controllers;

use App\Exports\MedicineAvailabilityExport;
use App\Http\Requests\CreateMedicineAvailabilityRequest;
use App\Models\Medicine;
use App\Models\StockIn;
use App\Repositories\MedicineRepository;
use App\Repositories\MedicineAvailabilityRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Laracasts\Flash\Flash;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Renamed from MedicineAvailabilityController → StockInController.
 * Handles stock-in (medicine purchase/receiving) CRUD.
 * Route resource: stock-in — route names: stock-in.* / staff.stock-in.* / doctors.stock-in.*
 */
class StockInController extends AppBaseController
{
    private $medicineAvailabilityRepository;
    private $medicineRepository;

    public function __construct(
        MedicineAvailabilityRepository $medicineAvailabilityRepo,
        MedicineRepository $medicineRepository
    ) {
        $this->medicineAvailabilityRepository = $medicineAvailabilityRepo;
        $this->medicineRepository             = $medicineRepository;
    }

    private function getIndexRoute(): string
    {
        if (isRole('clinic_admin')) {
            return route('stock-in.index');
        } elseif (isRole('staff')) {
            return route('staff.stock-in.index');
        } elseif (isRole('doctor')) {
            return route('doctors.stock-in.index');
        }
        return route('stock-in.index');
    }

    public function index(): View
    {
        return view('medicine-availabilities.index');
    }

    public function create(): View
    {
        $data           = $this->medicineRepository->getSyncList();
        $medicines      = $this->medicineAvailabilityRepository->getMedicine();
        $medicineList   = $this->medicineAvailabilityRepository->getMedicineList();
        $categories     = $this->medicineAvailabilityRepository->getCategory();
        $categoriesList = $this->medicineAvailabilityRepository->getCategoryList();

        return view('medicine-availabilities.create', compact('medicines', 'medicineList', 'categories', 'categoriesList'))->with($data);
    }

    public function store(CreateMedicineAvailabilityRequest $request): JsonResponse|RedirectResponse
    {
        $input = $request->all();

        Log::info('Stock-In Input Data:', $input);

        if (empty($input['availability_no'])) {
            $input['availability_no'] = generateUniqueAvailabilityNumber();
        }

        $this->medicineAvailabilityRepository->store($input);

        if ($request->ajax()) {
            return $this->sendSuccess(__('messages.medicine_availability.medicine_availability_success'));
        }

        Flash::success(__('messages.medicine_availability.medicine_availability_success'));

        return redirect($this->getIndexRoute());
    }

    public function show(StockIn $stockIn): View
    {
        $stockIn->load(['purchasedMedcines.medicines']);

        return view('medicine-availabilities.show', ['medicineAvailability' => $stockIn]);
    }

    public function edit(StockIn $stockIn): View
    {
        $stockIn->load(['purchasedMedcines.medicines']);
        $medicines           = $this->medicineAvailabilityRepository->getMedicine();
        $medicineList        = $this->medicineAvailabilityRepository->getMedicineList();
        $categories          = $this->medicineAvailabilityRepository->getCategory();
        $categoriesList      = $this->medicineAvailabilityRepository->getCategoryList();
        $medicineAvailability = $stockIn; // keep view-compat variable name

        return view('medicine-availabilities.edit', compact('medicineAvailability', 'medicines', 'medicineList', 'categories', 'categoriesList'));
    }

    public function update(CreateMedicineAvailabilityRequest $request, StockIn $stockIn): RedirectResponse
    {
        $input = $request->all();

        $this->medicineAvailabilityRepository->updatePurchaseMedicine($input, $stockIn->id);
        Flash::success(__('messages.medicine_availability.purchased_medicine_updated'));

        return redirect($this->getIndexRoute());
    }

    public function getMedicine(Medicine $medicine): JsonResponse
    {
        return $this->sendResponse($medicine, 'retrieved');
    }

    public function purchaseMedicineExport()
    {
        $items = StockIn::with('purchasedMedcines')->get();
        if ($items->isEmpty()) {
            Flash::error(__('messages.no_data_available'));
            return redirect($this->getIndexRoute());
        }
        $response = Excel::download(new MedicineAvailabilityExport, 'stock-in-' . time() . '.xlsx');
        ob_end_clean();
        return $response;
    }

    public function destroy(StockIn $stockIn)
    {
        $stockIn->delete();
        return $this->sendSuccess(__('messages.flash.medicine_deleted'));
    }
}
