<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMedicineRequest;
use App\Http\Requests\UpdateMedicineRequest;
use App\Models\Category;
use App\Models\Generic;
use App\Models\Medicine;
use App\Models\DispenseRecordItem;
use App\Models\MedicineBatch;
use App\Models\PurchasedMedicine;
use App\Repositories\MedicineRepository;
use App\Services\MedicineInventoryService;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Response;
use Laracasts\Flash\Flash;

class MedicineController extends AppBaseController
{
    /** @var MedicineRepository */
    private $medicineRepository;

    private MedicineInventoryService $inventoryService;

    public function __construct(MedicineRepository $medicineRepo, MedicineInventoryService $inventoryService)
    {
        $this->medicineRepository = $medicineRepo;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Get the appropriate medicine index route based on user role
     */
    private function getMedicineIndexRoute(): string
    {
        if (isRole('clinic_admin')) {
            return route('medicine-inventory.index');
        } elseif (isRole('staff')) {
            return route('staff.medicine-inventory.index');
        } elseif (isRole('doctor')) {
            return route('doctors.medicine-inventory.index');
        }

        return route('medicine-inventory.index');
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
        $input = $this->prepareMedicineInput($request->validated());

        DB::beginTransaction();
        try {
            $medicine = $this->medicineRepository->create($this->extractPersistableFields($input));
            $this->recordStockInIfProvided($medicine, $input);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Medicine store failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->ajax()) {
                return $this->sendError($e->getMessage());
            }

            Flash::error($e->getMessage());

            return redirect()->back()->withInput();
        }

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
        $medicine->medicineCategory;

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
        $input = $this->prepareMedicineInput($request->validated(), $medicine);

        DB::beginTransaction();
        try {
            $this->medicineRepository->update($this->extractPersistableFields($input), $medicine->id);
            $medicine->refresh();
            $this->recordStockInIfProvided($medicine, $input);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Medicine update failed', [
                'medicine_id' => $medicine->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Flash::error($e->getMessage());

            return redirect()->back()->withInput();
        }

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

        // Build dosage stock table from batch-ledger so initial-stock entries are included.
        $purchasedMedicines = MedicineBatch::where('medicine_id', $medicine->id)
            ->where('quantity', '>', 0)
            ->orderBy('expiration_date')
            ->get(['dosage', 'quantity', 'expiration_date'])
            ->groupBy(function (MedicineBatch $batch) use ($medicine) {
                $dosage = trim((string) ($batch->dosage ?? ''));
                if ($dosage !== '') {
                    return $dosage;
                }

                $fallbackDosage = trim((string) ($medicine->dosage ?? ''));

                return $fallbackDosage !== '' ? $fallbackDosage : 'N/A';
            })
            ->map(function ($rows, $dosage) {
                $expiryCandidates = collect($rows)
                    ->pluck('expiration_date')
                    ->filter()
                    ->map(function ($date) {
                        try {
                            return \Carbon\Carbon::parse($date)->startOfDay();
                        } catch (\Throwable $e) {
                            return null;
                        }
                    })
                    ->filter()
                    ->sortBy(fn(\Carbon\Carbon $date) => $date->getTimestamp());

                $earliestExpiry = $expiryCandidates->first();
                $remainingDays = null;
                $expiryFormatted = 'N/A';

                if ($earliestExpiry) {
                    $remainingDays = (int) \Carbon\Carbon::now()->startOfDay()->diffInDays($earliestExpiry, false);
                    $expiryFormatted = $earliestExpiry->format('M d, Y');
                }

                return [
                    'dosage' => $dosage,
                    'quantity' => (int) collect($rows)->sum('quantity'),
                    'expiry_date' => $expiryFormatted,
                    'remaining_days' => $remainingDays,
                ];
            })
            ->values();

        $currency = $medicine->currency_symbol ? strtoupper($medicine->currency_symbol) : strtoupper(getCurrentCurrency());
        $genericName = $medicine->generic_name ?: optional($medicine->generic)->name;
        $categoryName = $medicine->category ?: $medicine->category_name ?: optional($medicine->medicineCategory)->name;
        $defaultDosage = trim((string) ($medicine->dosage ?? ''));

        $dosageSummaryParts = $purchasedMedicines
            ->pluck('dosage')
            ->map(fn($value) => trim((string) $value))
            ->filter(fn($value) => $value !== '' && strcasecmp($value, 'N/A') !== 0)
            ->unique()
            ->values();

        if ($defaultDosage !== '' && ! $dosageSummaryParts->contains($defaultDosage)) {
            $dosageSummaryParts = $dosageSummaryParts->prepend($defaultDosage)->values();
        }

        $dosageSummary = $dosageSummaryParts->isNotEmpty()
            ? $dosageSummaryParts->implode(', ')
            : ($defaultDosage !== '' ? $defaultDosage : 'N/A');

        $medicineData = [
            'name' => $medicine->display_name,
            'brand_name' => $medicine->brand_name ?: $medicine->name,
            'generic_name' => $genericName ?: 'N/A',
            'category' => $categoryName ?: 'Uncategorized',
            'category_name' => $categoryName ?: 'Uncategorized',
            'dosage' => $medicine->dosage,
            'dosage_summary' => $dosageSummary,
            'uom' => $medicine->uom,
            'sku' => $medicine->sku,
            'reorder_level' => $medicine->reorder_level,
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
        $categories = Category::with(['medicines' => function ($query) {
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
                    // Dosage availability from batch-ledger to include all stock-in paths.
                    $dosages = MedicineBatch::where('medicine_id', $medicine->id)
                        ->where('quantity', '>', 0)
                        ->get()
                        ->groupBy(function (MedicineBatch $batch) use ($medicine) {
                            $dosage = trim((string) ($batch->dosage ?? ''));
                            if ($dosage !== '') {
                                return $dosage;
                            }

                            $fallbackDosage = trim((string) ($medicine->dosage ?? ''));

                            return $fallbackDosage !== '' ? $fallbackDosage : 'N/A';
                        })
                        ->map(function ($rows, $dosage) {
                            return [
                                'dosage' => $dosage,
                                'available_quantity' => (int) collect($rows)->sum('quantity'),
                            ];
                        })
                        ->values();

                    if ($dosages->isEmpty() && (int) $medicine->available_quantity > 0) {
                        $dosages = collect([[
                            'dosage' => $medicine->dosage ?: 'N/A',
                            'available_quantity' => (int) $medicine->available_quantity,
                        ]]);
                    }

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

    private function prepareMedicineInput(array $input, ?Medicine $existingMedicine = null): array
    {
        $genericName = trim((string) ($input['generic_name'] ?? ''));
        $brandName = trim((string) ($input['brand_name'] ?? ''));
        $categoryName = trim((string) ($input['category'] ?? ''));

        $input['generic_name'] = $genericName;
        $input['brand_name'] = $brandName !== '' ? $brandName : null;
        $input['category'] = $categoryName;
        $input['category_name'] = $categoryName;
        $input['name'] = $brandName !== '' ? $brandName : $genericName;
        $input['minimum_stock_alert'] = $input['reorder_level'] ?? $input['minimum_stock_alert'] ?? null;

        if ($existingMedicine) {
            $input['quantity'] = (int) $existingMedicine->quantity;
            $input['available_quantity'] = (int) $existingMedicine->available_quantity;
        } else {
            $input['quantity'] = (int) ($input['quantity'] ?? 0);
            $input['available_quantity'] = (int) ($input['available_quantity'] ?? 0);
        }

        if ($genericName !== '') {
            $generic = Generic::firstOrCreate(['name' => $genericName]);
            $input['generic_id'] = $generic->id;
        }

        if ($categoryName !== '') {
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['is_active' => Category::ACTIVE]
            );

            if ((int) $category->is_active !== Category::ACTIVE) {
                $category->update(['is_active' => Category::ACTIVE]);
            }

            $input['category_id'] = $category->id;
        }

        return $input;
    }

    private function extractPersistableFields(array $input): array
    {
        return Arr::except($input, [
            'initial_stock_quantity',
            'batch_number',
            'manufacturing_date',
            'expiration_date',
            'supplier_name',
            'unit_cost',
        ]);
    }

    private function recordStockInIfProvided(Medicine $medicine, array $input): void
    {
        $initialQty = (int) ($input['initial_stock_quantity'] ?? 0);
        if ($initialQty <= 0) {
            return;
        }

        $this->inventoryService->recordStockIn([
            'medicine_id' => $medicine->id,
            'quantity' => $initialQty,
            'dosage' => $input['dosage'] ?? null,
            'batch_number' => $input['batch_number'] ?? null,
            'manufacturing_date' => $input['manufacturing_date'] ?? null,
            'expiration_date' => $input['expiration_date'] ?? null,
            'supplier_name' => $input['supplier_name'] ?? null,
            'unit_cost' => $input['unit_cost'] ?? null,
            'date_received' => now()->toDateString(),
            'user_id' => getLogInUserId(),
            'reference' => $medicine,
            'remarks' => 'Initial stock from medicine form',
        ]);
    }
}
