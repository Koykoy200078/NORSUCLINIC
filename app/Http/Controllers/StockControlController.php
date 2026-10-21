<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockControlRequest;
use App\Models\Medicine;
use App\Repositories\StockControlRepository;
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
        $data = $request->validate(['medicine_id' => ['nullable', 'integer', 'min:1']]);
        $medicines = Medicine::orderBy('name')->get(['id', 'name', 'dosage', 'uom']);
        $medicine = empty($data['medicine_id']) ? null : Medicine::with('batches')->findOrFail($data['medicine_id']);

        return view('medicines.stock-control', ['medicines' => $medicines, 'medicine' => $medicine, 'preview' => null]);
    }

    public function preview(StockControlRequest $request): View
    {
        $preview = $this->stock->preview($request->validated(), $request->user());

        return view('medicines.stock-control', ['medicines' => collect(), 'medicine' => $preview['medicine'], 'preview' => $preview]);
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
