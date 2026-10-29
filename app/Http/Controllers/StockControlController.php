<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockControlRequest;
use App\Models\InventoryCountLine;
use App\Models\Medicine;
use App\Repositories\StockControlRepository;
use App\Services\InventoryCountService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockControlController extends Controller
{
    public function __construct(private StockControlRepository $stock)
    {
    }

    public function index(Request $request): View
    {
        $data = $request->validate(['medicine_id' => ['nullable', 'integer', 'min:1'], 'count_line_id' => ['nullable', 'integer', 'min:1']]);
        $medicines = Medicine::orderBy('name')->get(['id', 'name', 'dosage', 'uom']);
        $medicine = empty($data['medicine_id']) ? null : Medicine::with('batches')->findOrFail($data['medicine_id']);

        // Opened from a count sheet's "Correct stock": the line, why it cannot be used (if it cannot), and the units to fill in.
        $countLine = $medicine && ! empty($data['count_line_id'])
            ? InventoryCountLine::with('sheet')->where('medicine_id', $medicine->id)->find($data['count_line_id'])
            : null;
        $countBlocked = $countLine ? app(InventoryCountService::class)->correctionBlockedReason($countLine) : null;

        return view('medicines.stock-control', [
            'medicines' => $medicines, 'medicine' => $medicine, 'preview' => null,
            'countLine' => $countLine, 'countBlocked' => $countBlocked,
            'prefill' => $countLine && ! $countBlocked && $medicine->batches->count() === 1 ? [$medicine->batches->first()->id => $countLine->counted_quantity] : [],
        ]);
    }

    public function preview(StockControlRequest $request): View
    {
        $preview = $this->stock->preview($request->validated(), $request->user());

        return view('medicines.stock-control', ['medicines' => collect(), 'medicine' => $preview['medicine'], 'preview' => $preview,
            'countLine' => null, 'countBlocked' => null, 'prefill' => []]);
    }

    public function store(StockControlRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $medicine = $this->stock->confirm($request->validated()['preview_token'], $request->user());
        } catch (ValidationException $error) {
            return $this->rejected($request, $error);
        } catch (\RuntimeException $error) {
            return $this->rejected($request, ValidationException::withMessages(['preview_token' => 'The stock operation failed. No stock was changed. Preview again.']));
        }
        $redirect = getRouteByRole('stock-control.index', ['medicine_id' => $medicine->id]);

        return $request->expectsJson() ? response()->json(['success' => true, 'redirect' => $redirect])
            : redirect($redirect)->with('success', 'Batch stock and audit recorded successfully.');
    }

    private function rejected(Request $request, ValidationException $error): RedirectResponse
    {
        if ($request->expectsJson()) {
            throw $error;
        }

        return redirect(getRouteByRole('stock-control.index'))->withErrors($error->errors());
    }
}
