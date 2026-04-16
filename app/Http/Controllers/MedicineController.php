<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMedicineRequest;
use App\Http\Requests\UpdateMedicineRequest;
use App\Models\Medicine;
use App\Models\PurchasedMedicine;
use App\Models\DispenseRecordItem;
use App\Repositories\MedicineRepository;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Response;
use Laracasts\Flash\Flash;

class MedicineController extends AppBaseController
{
    /** @var MedicineRepository */
    private $medicineRepository;

    public function __construct(MedicineRepository $medicineRepo)
    {
        $this->medicineRepository = $medicineRepo;
    }

    /**
     * Get the appropriate medicine index route based on user role
     */
    private function getMedicineIndexRoute(): string
    {
        if (isRole('clinic_admin')) {
            return route('medicines.index');
        } elseif (isRole('staff')) {
            return route('staff.medicines.index');
        } elseif (isRole('doctor')) {
            return route('doctors.medicines.index');
        }

        return route('medicines.index');
    }

    /**
     * Display a listing of the Medicine.
     *
     * @param  Request  $request
     * @return Factory|View|Response
     *
     * @throws Exception
     */
    public function index(): View
    {
        return view('medicines.index');
    }

    /**
     * Show the form for creating a new Medicine.
     *
     * @return Factory|View
     */
    public function create(): View
    {
        $data = $this->medicineRepository->getSyncList();

        return view('medicines.create')->with($data);
    }

    /**
     * Store a newly created Medicine in storage.
     *
     * @return RedirectResponse|Redirector
     */
    public function store(CreateMedicineRequest $request): JsonResponse|RedirectResponse
    {
        $input = $request->all();

        $this->medicineRepository->create($input);

        if ($request->ajax()) {
            return $this->sendSuccess(__('messages.medicine.medicine') . ' ' . __('messages.medicine.saved_successfully'));
        }

        Flash::success(__('messages.medicine.medicine') . ' ' . __('messages.medicine.saved_successfully'));

        return redirect($this->getMedicineIndexRoute());
    }

    /**
     * Display the specified Medicine.
     *
     * @return Factory|View
     */
    public function show(Medicine $medicine): View
    {
        $medicine->generic;
        $medicine->category;

        return view('medicines.show')->with('medicine', $medicine);
    }

    /**
     * Show the form for editing the specified Medicine.
     *
     * @return Factory|View
     */
    public function edit(Medicine $medicine): View
    {
        $data = $this->medicineRepository->getSyncList();
        $data['medicine'] = $medicine;

        return view('medicines.edit')->with($data);
    }

    /**
     * Update the specified Medicine in storage.
     *
     * @return RedirectResponse|Redirector
     */
    public function update(Medicine $medicine, UpdateMedicineRequest $request): RedirectResponse
    {

        $this->medicineRepository->update($request->all(), $medicine->id);

        Flash::success(__('messages.medicine.medicine') . ' ' . __('messages.medicine.updated_successfully'));

        return redirect($this->getMedicineIndexRoute());
    }

    /**
     * Remove the specified Medicine from storage.
     *
     *
     * @throws Exception
     */
    public function destroy(Medicine $medicine): JsonResponse
    {
        if (! canAccessRecord(Medicine::class, $medicine->id)) {
            return $this->sendError(__('messages.flash.medicine_not_found'));
        }
        $purchaseMedicine = PurchasedMedicine::whereMedicineId($medicine->id)->first();
        $saleMedicine = DispenseRecordItem::whereMedicineId($medicine->id)->first();
        if (isset($purchaseMedicine) && ! empty($purchaseMedicine)) {
            $purchaseMedicine->delete();
        }
        if (isset($saleMedicine) && ! empty($saleMedicine)) {
            $saleMedicine->delete();
        }
        $this->medicineRepository->delete($medicine->id);

        return $this->sendSuccess(__('messages.medicine.medicine') . ' ' . __('messages.medicine.deleted_successfully'));
    }

    /**
     * @throws \Gerardojbaez\Money\Exceptions\CurrencyException
     */
    public function showModal(Medicine $medicine): JsonResponse
    {
        $medicine->load(['generic', 'category']);

        // Get purchased medicines with dosage information grouped by dosage with earliest expiry date
        $purchasedMedicines = PurchasedMedicine::where('medicine_id', $medicine->id)
            ->where('quantity', '>', 0) // Only show batches with available stock
            ->select(
                'dosage',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('MIN(expiry_date) as earliest_expiry')
            )
            ->groupBy('dosage')
            ->orderBy('dosage')
            ->get()
            ->map(function ($item) {
                $expiryDate = $item->earliest_expiry;
                $remainingDays = null;
                $expiryFormatted = 'N/A';

                if ($expiryDate && $expiryDate !== '' && $expiryDate !== 'N/A') {
                    try {
                        $expiry = \Carbon\Carbon::parse($expiryDate);
                        $now = \Carbon\Carbon::now();
                        $remainingDays = (int) $now->diffInDays($expiry, false); // false to get negative for past dates
                        $expiryFormatted = $expiry->format('M d, Y');
                    } catch (\Exception $e) {
                        // If parsing fails, keep N/A
                        Log::info('Failed to parse expiry date: ' . $expiryDate . ' - ' . $e->getMessage());
                        $remainingDays = null;
                    }
                }

                return [
                    'dosage' => $item->dosage ?? 'N/A',
                    'quantity' => $item->total_quantity,
                    'expiry_date' => $expiryFormatted,
                    'remaining_days' => $remainingDays
                ];
            });

        $currency = $medicine->currency_symbol ? strtoupper($medicine->currency_symbol) : strtoupper(getCurrentCurrency());
        $medicineData = [
            'name' => $medicine->name,
            'generic_name' => $medicine->generic->name,
            'category_name' => $medicine->category->name,
            'salt_composition' => $medicine->salt_composition,
            'side_effects' => $medicine->side_effects,
            'created_at' => $medicine->created_at,
            'updated_at' => $medicine->updated_at,
            'description' => $medicine->description,
            'quantity' => $medicine->quantity,
            'available_quantity' => $medicine->available_quantity,
            'minimum_stock_alert' => $medicine->minimum_stock_alert,
            'stock_alert_percentage' => $medicine->stock_alert_percentage,
            'purchased_medicines' => $purchasedMedicines,
        ];

        return $this->sendResponse($medicineData, __('messages.medicine.medicine_retrieved_successfully'));
    }

    public function checkUseOfMedicine(Medicine $medicine)
    {

        $SaleModel = [
            DispenseRecordItem::class,
            PurchasedMedicine::class,
        ];
        $result['result'] = canDelete($SaleModel, 'medicine_id', $medicine->id);
        $result['id'] = $medicine->id;

        if ($result) {

            return $this->sendResponse($result, __('messages.medicine_bills.the_medicine_already_in_use'));
        }

        return $this->sendResponse($result, __('messages.medicine.no_use'));
    }

    /**
     * Get medicines grouped by category with dosage information
     */
    public function getMedicinesByCategory(): JsonResponse
    {
        $categories = \App\Models\Category::with(['medicines' => function ($query) {
            $query->where('available_quantity', '>', 0)
                ->orderBy('name');
        }])->whereHas('medicines', function ($query) {
            $query->where('available_quantity', '>', 0);
        })->orderBy('name')->get();

        $result = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'medicines' => $category->medicines->map(function ($medicine) {
                    // Get dosage information for this medicine
                    $dosages = PurchasedMedicine::where('medicine_id', $medicine->id)
                        ->where('quantity', '>', 0)
                        ->select('dosage', DB::raw('SUM(quantity) as available_quantity'))
                        ->groupBy('dosage')
                        ->orderBy('dosage')
                        ->get()
                        ->map(function ($item) {
                            return [
                                'dosage' => $item->dosage ?? 'N/A',
                                'available_quantity' => (int) $item->available_quantity
                            ];
                        });

                    return [
                        'id' => $medicine->id,
                        'name' => $medicine->name,
                        'available_quantity' => $medicine->available_quantity,
                        'dosages' => $dosages
                    ];
                })
            ];
        });

        return $this->sendResponse($result, 'Medicines retrieved successfully');
    }
}
