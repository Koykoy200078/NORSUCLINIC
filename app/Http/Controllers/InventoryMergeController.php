<?php

namespace App\Http\Controllers;

use App\Http\Requests\MergeInventoryRequest;
use App\Models\ActivityLog;
use App\Repositories\InventoryMergeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryMergeController extends Controller
{
    public function __construct(private InventoryMergeRepository $merges)
    {
    }

    public function index(Request $request): View
    {
        $data = $request->validate(['kind' => ['nullable', 'in:medicine,category,generic'],
            'source_id' => ['nullable', 'integer', 'min:1'], 'target_id' => ['nullable', 'integer', 'min:1']]);
        $kind = $data['kind'] ?? 'medicine';
        $model = InventoryMergeRepository::MODELS[$kind];
        $ready = $model::supportsInventoryMerges();
        $records = $ready ? $model::orderBy('name')->get() : collect();
        $preview = null;
        $previewError = null;
        if ($ready && ! empty($data['source_id']) && ! empty($data['target_id'])) {
            try {
                $preview = $this->merges->preview($kind, (int) $data['source_id'], (int) $data['target_id'], $request->user());
            } catch (ValidationException $error) {
                if ($request->expectsJson()) {
                    throw $error;
                }
                // An old preview URL must render an explanation, not redirect back to itself after archival.
                $previewError = collect($error->errors())->flatten()->first();
            }
        }
        $history = ActivityLog::where('action', 'inventory_merged')->where('properties->kind', $kind)->latest('id')->limit(10)->get();

        return view('medicines.merge', compact('kind', 'ready', 'records', 'preview', 'previewError', 'history'));
    }

    public function store(MergeInventoryRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        try {
            $this->merges->merge($data['kind'], (int) $data['source_id'], (int) $data['target_id'], $data['preview_token'], $request->user());
        } catch (\RuntimeException $error) {
            Log::warning('Inventory merge rolled back', ['error_type' => get_class($error)]);
            throw ValidationException::withMessages(['preview_token' => 'The merge could not be completed. No records were changed. Preview it again.']);
        }
        $redirect = route('inventory-merge.index', ['kind' => $data['kind']]);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'redirect' => $redirect]);
        }

        return redirect($redirect)->with('success', 'The records were merged and the duplicate was archived.');
    }
}
