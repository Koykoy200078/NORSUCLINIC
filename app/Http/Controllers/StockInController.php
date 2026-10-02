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

        $numberWasDrawn = empty($input['availability_no']);

        try {
            if ($numberWasDrawn) {
                // Two stock-ins saved at the same moment can draw the same number; the unique index refuses the
                // second one and it is saved again with a new number. pass-1 L-05.
                retryOnDuplicateKey(function () use (&$input) {
                    $input['availability_no'] = generateUniqueAvailabilityNumber();
                    $this->medicineAvailabilityRepository->store($input);
                });
            } else {
                $this->medicineAvailabilityRepository->store($input);
            }
        } catch (\Throwable $e) {
            Log::error('Stock-in store failed: ' . $e->getMessage());
            $message = $this->stockInErrorMessage($e, 'The stock-in could not be saved.');

            if ($request->ajax()) {
                return $this->sendError($message);
            }

            return redirect()->back()->withInput()->with('error', $message);
        }

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

        try {
            $this->medicineAvailabilityRepository->updatePurchaseMedicine($input, $stockIn->id);
        } catch (\Throwable $e) {
            // The repository rolls everything back, so a refused edit leaves inventory untouched.
            Log::error('Stock-in update failed: ' . $e->getMessage());

            return redirect()->back()->withInput()
                ->with('error', $this->stockInErrorMessage($e, 'The stock-in could not be updated.'));
        }

        Flash::success(__('messages.medicine_availability.purchased_medicine_updated'));

        return redirect($this->getIndexRoute());
    }

    /**
     * The repository wraps failures in an HTTP exception carrying the original message. Inventory
     * rule violations (RuntimeException text such as "only 3 remain, the rest were already
     * dispensed") are meant for the user; database errors are not.
     */
    private function stockInErrorMessage(\Throwable $e, string $fallback): string
    {
        $message = $e->getMessage();

        $isDatabaseError = $e instanceof \Illuminate\Database\QueryException
            || str_contains($message, 'SQLSTATE')
            || str_contains($message, 'Integrity constraint');

        return ($message !== '' && ! $isDatabaseError) ? $message : $fallback;
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
        // Reverse exactly the stock this stock-in ADDED (from the batch each line created) before
        // deleting; otherwise the added quantity stays in inventory as phantom stock, and with FEFO
        // the wrong batch used to be emptied (expired stock looked valid). If some of it has
        // already been dispensed the reversal throws and the whole delete rolls back. H-05.
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($stockIn) {
                $inventoryService = app(\App\Services\MedicineInventoryService::class);
                foreach ($stockIn->purchasedMedcines as $line) {
                    if (! $line->medicine_id) {
                        continue;
                    }

                    $inventoryService->reverseStockIn(
                        (int) $line->medicine_id,
                        (int) $line->quantity,
                        $line->batch_id ? (int) $line->batch_id : null,
                        $line->dosage,
                        $line->expiry_date,
                        getLogInUserId(),
                        $stockIn,
                        'Reversed: stock-in #' . $stockIn->id . ' deleted'
                    );
                }
                $stockIn->delete();
            });
        } catch (\Throwable $e) {
            Log::error('Stock-in delete refused/failed: ' . $e->getMessage());

            return $this->sendError(
                'Cannot delete this stock-in: ' . $this->stockInErrorMessage($e, 'its stock has already been dispensed/consumed, so removing it would create negative inventory.')
            );
        }

        return $this->sendSuccess(__('messages.flash.medicine_deleted'));
    }
}
